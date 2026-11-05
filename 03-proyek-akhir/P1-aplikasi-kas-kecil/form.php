<?php
// ==========================================================================
// form.php — FORMULIR CATAT BARU sekaligus UBAH transaksi.
//   form.php        -> mode tambah (formulir kosong)
//   form.php?id=12  -> mode ubah (formulir terisi data id 12)
// Alur: tampilkan formulir -> pengguna kirim (POST) -> validasi -> simpan -> kembali ke daftar.
// ==========================================================================
require "config.php";

$id = (int) ($_GET["id"] ?? 0);                                   // 0 berarti mode tambah
$modeUbah = $id > 0;

// 1. Nilai awal formulir.
$data = [
    "tanggal"  => date("Y-m-d"),                                  // tanggal hari ini
    "no_bukti" => nomorBerikutnya($db),                           // usulan nomor bukti berikutnya
    "uraian"   => "",
    "bidang"   => "",
    "jenis"    => "",
    "jumlah"   => "",
];
if ($modeUbah) {                                                  // mode ubah: ambil data lama dari database
    $st = $db->prepare("SELECT * FROM kas_kecil WHERE id = ?");
    $st->execute([$id]);
    $lama = $st->fetch();                                         // false bila id tidak ditemukan
    if (!$lama) {
        setPesan("Transaksi tidak ditemukan.", "galat");
        arahkan("transaksi.php");
    }
    $data = array_intersect_key($lama, $data);                    // ambil kolom yang dipakai formulir saja
}

$galat = [];                                                      // daftar pesan galat validasi (bisa lebih dari satu)

// 2. Proses saat formulir dikirim.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 2a. Ambil isian dan rapikan (trim = buang spasi di awal/akhir).
    $data = [
        "tanggal"  => trim($_POST["tanggal"] ?? ""),
        "no_bukti" => strtoupper(trim($_POST["no_bukti"] ?? "")), // huruf besar semua: kk-041 -> KK-041
        "uraian"   => trim($_POST["uraian"] ?? ""),
        "bidang"   => $_POST["bidang"] ?? "",
        "jenis"    => $_POST["jenis"] ?? "",
        "jumlah"   => trim($_POST["jumlah"] ?? ""),
    ];

    // 2b. Validasi satu per satu. Setiap pelanggaran ditambahkan ke $galat dengan kunci nama isian.
    $tgl = DateTime::createFromFormat("Y-m-d", $data["tanggal"]);  // coba baca tanggal
    if (!$tgl || $tgl->format("Y-m-d") !== $data["tanggal"]) {
        $galat["tanggal"] = "Tanggal tidak valid.";
    } elseif ($data["tanggal"] > date("Y-m-d")) {                  // teks TTTT-BB-HH bisa dibandingkan langsung
        $galat["tanggal"] = "Tanggal tidak boleh melewati hari ini.";
    }
    if (!preg_match('/^[A-Z]{2}-[0-9A-Z]{3,7}$/', $data["no_bukti"])) {  // pola: dua huruf, tanda -, 3-7 karakter
        $galat["no_bukti"] = "Format nomor bukti seperti KK-041.";
    } else {                                                      // cek nomor kembar (kecuali milik transaksi ini sendiri)
        $st = $db->prepare("SELECT COUNT(*) FROM kas_kecil WHERE no_bukti = ? AND id <> ?");
        $st->execute([$data["no_bukti"], $id]);
        if ($st->fetchColumn() > 0) {
            $galat["no_bukti"] = "Nomor bukti " . $data["no_bukti"] . " sudah dipakai.";
        }
    }
    if ($data["uraian"] === "" || mb_strlen($data["uraian"]) > 100) {
        $galat["uraian"] = "Uraian wajib diisi, maksimal 100 karakter.";
    }
    if (!in_array($data["bidang"], DAFTAR_BIDANG, true)) {        // harus salah satu bidang resmi
        $galat["bidang"] = "Pilih bidang.";
    }
    if (!in_array($data["jenis"], DAFTAR_JENIS, true)) {
        $galat["jenis"] = "Pilih jenis biaya.";
    }
    if (!is_numeric($data["jumlah"]) || $data["jumlah"] <= 0) {   // is_numeric: hanya angka
        $galat["jumlah"] = "Jumlah harus angka lebih dari nol.";
    } elseif ($data["jumlah"] > BATAS_TRANSAKSI) {
        $galat["jumlah"] = "Satu transaksi kas kecil maksimal Rp " . rp(BATAS_TRANSAKSI) . ".";
    }

    // 2c. Aturan bisnis: total bulan tersebut tidak boleh melampaui plafon.
    if (!$galat) {
        $bulan = substr($data["tanggal"], 0, 7);                  // "2026-11-05" -> "2026-11"
        $st = $db->prepare("SELECT nilai FROM plafon WHERE bulan = ?");
        $st->execute([$bulan]);
        $plafon = $st->fetchColumn();                             // false bila plafon belum diatur
        $st = $db->prepare("SELECT COALESCE(SUM(jumlah), 0) FROM kas_kecil WHERE DATE_FORMAT(tanggal, '%Y-%m') = ? AND id <> ?");
        $st->execute([$bulan, $id]);                              // id <> ? : saat ubah, nilai lama tidak ikut dihitung
        $sudahTerpakai = (float) $st->fetchColumn();
        if ($plafon === false) {
            $galat["tanggal"] = "Plafon bulan " . namaBulan($bulan) . " belum diatur.";
        } elseif ($sudahTerpakai + $data["jumlah"] > $plafon) {
            $galat["jumlah"] = "Melebihi plafon. Sisa plafon " . namaBulan($bulan) . ": Rp " . rp($plafon - $sudahTerpakai) . ".";
        }
    }

    // 2d. Simpan bila tidak ada galat.
    if (!$galat) {
        $nilai = [$data["tanggal"], $data["no_bukti"], $data["uraian"], $data["bidang"], $data["jenis"], $data["jumlah"]];
        if ($modeUbah) {
            $st = $db->prepare("UPDATE kas_kecil SET tanggal = ?, no_bukti = ?, uraian = ?, bidang = ?, jenis = ?, jumlah = ? WHERE id = ?");
            $st->execute([...$nilai, $id]);                       // ... = sebarkan isi array, lalu tambahkan $id di akhir
            setPesan("Transaksi " . $data["no_bukti"] . " berhasil diubah.");
        } else {
            $st = $db->prepare("INSERT INTO kas_kecil (tanggal, no_bukti, uraian, bidang, jenis, jumlah) VALUES (?, ?, ?, ?, ?, ?)");
            $st->execute($nilai);
            setPesan("Transaksi " . $data["no_bukti"] . " berhasil dicatat.");
        }
        arahkan("transaksi.php");                                 // pola PRG: setelah POST, pindah halaman agar tidak tersimpan ganda saat refresh
    }
}

// Fungsi bantu khusus halaman ini: usulan nomor bukti berikutnya, misal KK-040 -> KK-041.
function nomorBerikutnya($db)
{
    $terakhir = $db->query("SELECT no_bukti FROM kas_kecil WHERE no_bukti REGEXP '^KK-[0-9]+$' ORDER BY id DESC LIMIT 1")->fetchColumn();
    $angka = $terakhir ? (int) substr($terakhir, 3) + 1 : 1;      // ambil angka setelah "KK-", tambah 1
    return "KK-" . str_pad($angka, 3, "0", STR_PAD_LEFT);         // 41 -> "041"
}

// Fungsi bantu tampilan: class "salah" untuk isian yang galat.
function kelas($galat, $nama) { return isset($galat[$nama]) ? "isian salah" : "isian"; }

$judul = $modeUbah ? "Ubah Transaksi" : "Catat Transaksi";
$aktif = $modeUbah ? "transaksi.php" : "form.php";
require "inc/header.php";
?>

<h1><?= $judul ?></h1>
<p class="sub">Kolom bertanda * wajib diisi. Batas satu transaksi Rp <?= rp(BATAS_TRANSAKSI) ?>.</p>

<?php if ($galat): ?>                                             <!-- ringkasan galat di atas formulir -->
  <div class="pesan galat"><b>Data belum dapat disimpan:</b>
    <ul><?php foreach ($galat as $g): ?><li><?= e($g) ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<form method="post" class="panel">                                <!-- tanpa action: dikirim ke alamat yang sama (termasuk ?id=) -->
  <div class="form-grid">
    <div class="<?= kelas($galat, "tanggal") ?>">
      <label for="tanggal">Tanggal *</label>
      <input type="date" id="tanggal" name="tanggal" value="<?= e($data["tanggal"]) ?>" max="<?= date("Y-m-d") ?>" required>
    </div>
    <div class="<?= kelas($galat, "no_bukti") ?>">
      <label for="no_bukti">No. bukti *</label>
      <input id="no_bukti" name="no_bukti" value="<?= e($data["no_bukti"]) ?>" maxlength="10" required>
      <span class="bantuan">Contoh: KK-041</span>
    </div>
    <div class="<?= kelas($galat, "bidang") ?>">
      <label for="bidang">Bidang *</label>
      <select id="bidang" name="bidang" required>
        <option value="">— pilih —</option>
        <?php foreach (DAFTAR_BIDANG as $b): ?>
          <option <?= $b === $data["bidang"] ? "selected" : "" ?>><?= e($b) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="<?= kelas($galat, "jenis") ?>">
      <label for="jenis">Jenis biaya *</label>
      <select id="jenis" name="jenis" required>
        <option value="">— pilih —</option>
        <?php foreach (DAFTAR_JENIS as $j): ?>
          <option <?= $j === $data["jenis"] ? "selected" : "" ?>><?= e($j) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="<?= kelas($galat, "uraian") ?>" style="grid-column: 1 / -1">  <!-- isian uraian selebar formulir -->
      <label for="uraian">Uraian *</label>
      <input id="uraian" name="uraian" value="<?= e($data["uraian"]) ?>" maxlength="100" required>
    </div>
    <div class="<?= kelas($galat, "jumlah") ?>">
      <label for="jumlah">Jumlah (Rp) *</label>
      <input type="number" id="jumlah" name="jumlah" value="<?= e($data["jumlah"] === "" ? "" : (float) $data["jumlah"]) ?>" min="1" step="1" required data-rupiah>  <!-- data-rupiah: pratinjau format oleh app.js -->
    </div>
  </div>
  <p class="aksi" style="margin-top:18px">
    <button class="tombol" type="submit"><?= $modeUbah ? "Simpan Perubahan" : "Simpan" ?></button>
    <a class="tombol sekunder" href="transaksi.php">Batal</a>
  </p>
</form>

<?php require "inc/footer.php"; ?>
