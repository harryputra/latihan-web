<?php
// ==========================================================================
// ajukan.php — FORMULIR PENGAJUAN peminjaman ruang.
// Server memeriksa: isian lengkap, jam dalam jam layanan, peserta <= kapasitas, dan TIDAK bentrok.
// ==========================================================================
require "config.php";

$daftarRuang = $db->query("SELECT * FROM ruang ORDER BY lantai, nama")->fetchAll();
$ruangById = array_column($daftarRuang, null, "id");              // susun ulang: $ruangById[3] = data ruang id 3

// 1. Nilai awal (ruang & tanggal bisa dikirim dari tombol "+ Ajukan" di halaman jadwal).
$data = [
    "ruang_id" => (int) ($_GET["ruang"] ?? 0),
    "tanggal" => $_GET["tanggal"] ?? date("Y-m-d"),
    "jam_mulai" => "09:00", "jam_selesai" => "10:00",
    "kegiatan" => "", "pemohon" => "", "bidang" => "", "peserta" => "",
];
$galat = [];

// 2. Proses saat formulir dikirim.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    foreach ($data as $kunci => $awal) {                          // ambil semua isian dengan nama yang sama
        $data[$kunci] = trim($_POST[$kunci] ?? "");
    }
    $data["ruang_id"] = (int) $data["ruang_id"];
    $ruang = $ruangById[$data["ruang_id"]] ?? null;               // null bila ruang tidak dikenal

    if (!$ruang) $galat[] = "Pilih ruang.";
    $t = DateTime::createFromFormat("Y-m-d", $data["tanggal"]);
    if (!$t || $t->format("Y-m-d") !== $data["tanggal"]) {
        $galat[] = "Tanggal tidak valid.";
    } elseif ($data["tanggal"] < date("Y-m-d")) {
        $galat[] = "Tanggal tidak boleh sebelum hari ini.";
    } elseif (in_array($t->format("N"), ["6", "7"])) {            // N: 6 = Sabtu, 7 = Minggu
        $galat[] = "Ruang rapat hanya dilayani pada hari kerja (Senin–Jumat).";
    }
    if (!preg_match('/^\d{2}:\d{2}$/', $data["jam_mulai"]) || !preg_match('/^\d{2}:\d{2}$/', $data["jam_selesai"])) {
        $galat[] = "Format jam tidak valid.";
    } elseif ($data["jam_mulai"] < JAM_BUKA || $data["jam_selesai"] > JAM_TUTUP) {  // teks "HH:MM" bisa dibandingkan langsung
        $galat[] = "Jam pemakaian harus di antara " . JAM_BUKA . " dan " . JAM_TUTUP . ".";
    } elseif ($data["jam_selesai"] <= $data["jam_mulai"]) {
        $galat[] = "Jam selesai harus setelah jam mulai.";
    }
    if ($data["kegiatan"] === "" || mb_strlen($data["kegiatan"]) > 100) $galat[] = "Nama kegiatan wajib diisi (maks. 100 karakter).";
    if ($data["pemohon"] === "" || mb_strlen($data["pemohon"]) > 60) $galat[] = "Nama pemohon wajib diisi (maks. 60 karakter).";
    if (!in_array($data["bidang"], DAFTAR_BIDANG, true)) $galat[] = "Pilih bidang.";
    if (!ctype_digit($data["peserta"]) || $data["peserta"] < 1) {  // ctype_digit: hanya angka 0-9
        $galat[] = "Jumlah peserta minimal 1.";
    } elseif ($ruang && $data["peserta"] > $ruang["kapasitas"]) {
        $galat[] = "Peserta (" . $data["peserta"] . ") melebihi kapasitas " . $ruang["nama"] . " (" . $ruang["kapasitas"] . " orang).";
    }

    // 3. Aturan terpenting: cek bentrok jadwal hanya bila isian dasar sudah benar.
    if (!$galat) {
        $bentrok = cariBentrok($db, $data["ruang_id"], $data["tanggal"], $data["jam_mulai"], $data["jam_selesai"]);
        if ($bentrok) {
            $galat[] = "Bentrok dengan \"" . $bentrok["kegiatan"] . "\" pukul " . jam($bentrok["jam_mulai"]) . "–" . jam($bentrok["jam_selesai"])
                     . " (" . $bentrok["status"] . "). Pilih jam atau ruang lain.";
        }
    }

    // 4. Simpan dengan status awal "Menunggu".
    if (!$galat) {
        $st = $db->prepare("INSERT INTO peminjaman (ruang_id, tanggal, jam_mulai, jam_selesai, kegiatan, pemohon, bidang, peserta)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $st->execute([$data["ruang_id"], $data["tanggal"], $data["jam_mulai"], $data["jam_selesai"],
                      $data["kegiatan"], $data["pemohon"], $data["bidang"], $data["peserta"]]);
        setPesan("Pengajuan \"" . $data["kegiatan"] . "\" terkirim dan menunggu persetujuan.");
        arahkan("index.php?tanggal=" . $data["tanggal"]);         // tampilkan jadwal pada tanggal tersebut
    }
}

$judul = "Ajukan Peminjaman";
$aktif = "ajukan.php";
require "inc/header.php";
?>

<h1>Ajukan Peminjaman Ruang</h1>
<p class="sub">Jam layanan <?= JAM_BUKA ?>–<?= JAM_TUTUP ?>, Senin–Jumat. Pengajuan baru berstatus <b>Menunggu</b> sampai diproses petugas.</p>

<?php if ($galat): ?>
  <div class="pesan galat"><b>Pengajuan belum dapat dikirim:</b>
    <ul><?php foreach ($galat as $g): ?><li><?= e($g) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="post" class="panel" id="formAjukan">
  <div class="form-grid">
    <div class="isian">
      <label for="ruang_id">Ruang *</label>
      <select id="ruang_id" name="ruang_id" required>
        <option value="">— pilih ruang —</option>
        <?php foreach ($daftarRuang as $r): ?>                    <!-- data-*: informasi tambahan untuk JavaScript -->
          <option value="<?= $r["id"] ?>" data-kapasitas="<?= $r["kapasitas"] ?>" data-fasilitas="<?= e($r["fasilitas"]) ?>"
                  <?= $r["id"] == $data["ruang_id"] ? "selected" : "" ?>><?= e($r["nama"]) ?> (<?= $r["kapasitas"] ?> orang)</option>
        <?php endforeach; ?>
      </select>
      <span class="bantuan" id="infoRuang"></span>                 <!-- diisi JavaScript: fasilitas ruang -->
    </div>
    <div class="isian">
      <label for="tanggal">Tanggal *</label>
      <input type="date" id="tanggal" name="tanggal" value="<?= e($data["tanggal"]) ?>" min="<?= date("Y-m-d") ?>" required>
    </div>
    <div class="isian">
      <label for="jam_mulai">Jam mulai *</label>
      <input type="time" id="jam_mulai" name="jam_mulai" value="<?= e($data["jam_mulai"]) ?>" min="<?= JAM_BUKA ?>" max="<?= JAM_TUTUP ?>" step="900" required>  <!-- step 900 detik = per 15 menit -->
    </div>
    <div class="isian">
      <label for="jam_selesai">Jam selesai *</label>
      <input type="time" id="jam_selesai" name="jam_selesai" value="<?= e($data["jam_selesai"]) ?>" min="<?= JAM_BUKA ?>" max="<?= JAM_TUTUP ?>" step="900" required>
    </div>
    <div class="isian" style="grid-column: 1 / -1">
      <label for="kegiatan">Nama kegiatan *</label>
      <input id="kegiatan" name="kegiatan" value="<?= e($data["kegiatan"]) ?>" maxlength="100" required>
    </div>
    <div class="isian">
      <label for="pemohon">Nama pemohon *</label>
      <input id="pemohon" name="pemohon" value="<?= e($data["pemohon"]) ?>" maxlength="60" required>
    </div>
    <div class="isian">
      <label for="bidang">Bidang *</label>
      <select id="bidang" name="bidang" required>
        <option value="">— pilih —</option>
        <?php foreach (DAFTAR_BIDANG as $b): ?><option <?= $b === $data["bidang"] ? "selected" : "" ?>><?= e($b) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="isian">
      <label for="peserta">Jumlah peserta *</label>
      <input type="number" id="peserta" name="peserta" value="<?= e($data["peserta"]) ?>" min="1" required>
      <span class="bantuan" id="infoPeserta"></span>               <!-- diisi JavaScript: peringatan kapasitas -->
    </div>
  </div>
  <p class="aksi" style="margin-top:18px"><button class="tombol" type="submit">Kirim Pengajuan</button>
    <a class="tombol sekunder" href="index.php">Batal</a></p>
</form>

<script>
  // Pemeriksaan di peramban: memberi umpan balik SEBELUM dikirim. Server tetap memeriksa ulang.
  const pilihRuang = document.getElementById("ruang_id");
  const isianPeserta = document.getElementById("peserta");
  const mulai = document.getElementById("jam_mulai");
  const selesai = document.getElementById("jam_selesai");

  function periksa() {
    const opsi = pilihRuang.selectedOptions[0];                    // ruang yang sedang dipilih
    const kapasitas = Number(opsi.dataset.kapasitas || 0);         // baca data-kapasitas
    document.getElementById("infoRuang").textContent = opsi.dataset.fasilitas ? "Fasilitas: " + opsi.dataset.fasilitas : "";
    const peserta = Number(isianPeserta.value);
    const lebih = kapasitas && peserta > kapasitas;                // true bila peserta melebihi kapasitas
    document.getElementById("infoPeserta").textContent = lebih ? "Melebihi kapasitas ruang (" + kapasitas + " orang)." : "";
    isianPeserta.setCustomValidity(lebih ? "Peserta melebihi kapasitas" : "");  // pesan kustom: formulir tidak bisa dikirim
    selesai.setCustomValidity(selesai.value && selesai.value <= mulai.value ? "Jam selesai harus setelah jam mulai" : "");
  }
  [pilihRuang, isianPeserta, mulai, selesai].forEach(el => el.addEventListener("input", periksa));  // periksa setiap kali berubah
  periksa();                                                       // periksa sekali saat halaman dibuka
</script>

<?php require "inc/footer.php"; ?>
