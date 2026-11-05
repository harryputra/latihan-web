<?php
// ==========================================================================
// barang-form.php — TAMBAH atau UBAH data induk barang.
// Stok hanya diisi saat barang baru (stok awal). Setelah itu stok berubah HANYA melalui
// Barang Masuk (bertambah) dan Penyerahan permintaan (berkurang) agar setiap perubahan tercatat.
// ==========================================================================
require "config.php";

$id = (int) ($_GET["id"] ?? 0);
$modeUbah = $id > 0;
$data = ["kode" => "", "nama" => "", "satuan" => "", "harga" => "", "stok" => "0", "stok_minimum" => "5"];

if ($modeUbah) {
    $st = $db->prepare("SELECT * FROM barang WHERE id = ?");
    $st->execute([$id]);
    $lama = $st->fetch();
    if (!$lama) { setPesan("Barang tidak ditemukan.", "galat"); arahkan("barang.php"); }
    $data = array_intersect_key($lama, $data);                    // ambil kolom yang dipakai formulir
}

$galat = [];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    foreach (["kode", "nama", "satuan", "harga", "stok_minimum"] as $k) $data[$k] = trim($_POST[$k] ?? "");
    if (!$modeUbah) $data["stok"] = trim($_POST["stok"] ?? "0");  // stok awal hanya untuk barang baru
    $data["kode"] = strtoupper($data["kode"]);

    if (!preg_match('/^[A-Z]{3}-\d{3}$/', $data["kode"])) {
        $galat[] = "Kode berformat tiga huruf, tanda -, tiga angka (contoh ATK-011).";
    } else {
        $st = $db->prepare("SELECT COUNT(*) FROM barang WHERE kode = ? AND id <> ?");  // kode tidak boleh kembar
        $st->execute([$data["kode"], $id]);
        if ($st->fetchColumn()) $galat[] = "Kode " . $data["kode"] . " sudah dipakai.";
    }
    if ($data["nama"] === "" || mb_strlen($data["nama"]) > 60) $galat[] = "Nama barang wajib diisi (maks. 60 karakter).";
    if (!in_array($data["satuan"], DAFTAR_SATUAN, true)) $galat[] = "Pilih satuan.";
    if (!is_numeric($data["harga"]) || $data["harga"] < 0) $galat[] = "Harga harus angka 0 atau lebih.";
    if (!ctype_digit((string) $data["stok"])) $galat[] = "Stok awal harus bilangan bulat 0 atau lebih.";
    if (!ctype_digit((string) $data["stok_minimum"])) $galat[] = "Stok minimum harus bilangan bulat 0 atau lebih.";

    if (!$galat) {
        if ($modeUbah) {
            $st = $db->prepare("UPDATE barang SET kode = ?, nama = ?, satuan = ?, harga = ?, stok_minimum = ? WHERE id = ?");
            $st->execute([$data["kode"], $data["nama"], $data["satuan"], $data["harga"], $data["stok_minimum"], $id]);  // stok tidak diubah
            setPesan("Data " . $data["nama"] . " diperbarui.");
        } else {
            $st = $db->prepare("INSERT INTO barang (kode, nama, satuan, harga, stok, stok_minimum) VALUES (?, ?, ?, ?, ?, ?)");
            $st->execute([$data["kode"], $data["nama"], $data["satuan"], $data["harga"], $data["stok"], $data["stok_minimum"]]);
            setPesan("Barang " . $data["nama"] . " ditambahkan.");
        }
        arahkan("barang.php");
    }
}

// Usulan kode berikutnya untuk barang baru, misal ATK-010 -> ATK-011.
if (!$modeUbah && $data["kode"] === "") {
    $terakhir = $db->query("SELECT MAX(kode) FROM barang WHERE kode LIKE 'ATK-%'")->fetchColumn();
    $data["kode"] = "ATK-" . str_pad(((int) substr((string) $terakhir, 4)) + 1, 3, "0", STR_PAD_LEFT);
}

$judul = $modeUbah ? "Ubah Barang" : "Barang Baru";
$aktif = "barang.php";
require "inc/header.php";
?>

<h1><?= $judul ?></h1>
<p class="sub"><?= $modeUbah ? "Stok tidak diubah di sini — gunakan menu Barang Masuk atau proses permintaan." : "Isi stok awal sesuai hasil hitung fisik." ?></p>

<?php if ($galat): ?>
  <div class="pesan galat"><b>Data belum dapat disimpan:</b><ul><?php foreach ($galat as $g): ?><li><?= e($g) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="post" class="panel">
  <div class="form-grid">
    <div class="isian"><label for="kode">Kode *</label><input id="kode" name="kode" value="<?= e($data["kode"]) ?>" maxlength="7" required></div>
    <div class="isian" style="grid-column: span 2"><label for="nama">Nama barang *</label><input id="nama" name="nama" value="<?= e($data["nama"]) ?>" maxlength="60" required></div>
    <div class="isian"><label for="satuan">Satuan *</label>
      <select id="satuan" name="satuan" required><option value="">— pilih —</option>
        <?php foreach (DAFTAR_SATUAN as $s): ?><option <?= $s === $data["satuan"] ? "selected" : "" ?>><?= $s ?></option><?php endforeach; ?>
      </select></div>
    <div class="isian"><label for="harga">Harga satuan (Rp) *</label><input type="number" id="harga" name="harga" value="<?= e($data["harga"] === "" ? "" : (float) $data["harga"]) ?>" min="0" required data-rupiah></div>
    <div class="isian"><label for="stok">Stok <?= $modeUbah ? "saat ini" : "awal *" ?></label>
      <input type="number" id="stok" name="stok" value="<?= e($data["stok"]) ?>" min="0" <?= $modeUbah ? "disabled" : "required" ?>></div>  <!-- disabled: tidak dapat diedit & tidak ikut terkirim -->
    <div class="isian"><label for="stok_minimum">Stok minimum *</label><input type="number" id="stok_minimum" name="stok_minimum" value="<?= e($data["stok_minimum"]) ?>" min="0" required>
      <span class="bantuan">Di bawah angka ini barang ditandai "menipis".</span></div>
  </div>
  <p class="aksi" style="margin-top:16px"><button class="tombol" type="submit">Simpan</button><a class="tombol sekunder" href="barang.php">Batal</a></p>
</form>

<?php require "inc/footer.php"; ?>
