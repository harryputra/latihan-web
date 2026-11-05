<?php
// b8-tambah-validasi.php — jawaban mandiri Blok 8: validasi sebelum menyimpan

require "config.php";

$galat = [];  // daftar galat, mula-mula kosong
$pesan = "";  // pesan berhasil

// Proses hanya saat formulir dikirim
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // trim = buang spasi di awal/akhir; (float) = ubah menjadi angka
    $uraian  = trim($_POST["uraian"] ?? "");
    $jumlah  = (float) ($_POST["jumlah"] ?? 0);
    $noBukti = trim($_POST["no_bukti"] ?? "");

    // Aturan 1-3: uraian wajib, jumlah > 0, batas kas kecil
   

    // Aturan 4: nomor bukti belum pernah dipakai
    $cek = $db->prepare("SELECT COUNT(*) FROM kas_kecil WHERE no_bukti = ?");
    $cek->execute([$noBukti]);
    if ($cek->fetchColumn() > 0) {
        $galat[] = "No. bukti $noBukti sudah dipakai.";
    }

    // Simpan hanya jika tidak ada galat
    if (!$galat) {
        $st = $db->prepare(
            "INSERT INTO kas_kecil (tanggal, no_bukti, uraian, bidang, jenis, jumlah)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $st->execute([
            $_POST["tanggal"],
            $noBukti,
            $uraian,
            $_POST["bidang"],
            $_POST["jenis"],
            $jumlah,
        ]);
        $pesan = "Transaksi $noBukti berhasil disimpan.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Tambah Kas Kecil</title>
  <link rel="stylesheet" href="gaya.css">
</head>

<body>
  <h1>Catat Pengeluaran Kas Kecil</h1>

  <?php if ($galat): ?>
    <div class="galat">
      <b>Data belum dapat disimpan:</b>
      <ul>
        <?php foreach ($galat as $g): ?>
          <li><?= htmlspecialchars($g) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if ($pesan): ?>
    <p class="pesan"><?= htmlspecialchars($pesan) ?></p>
  <?php endif; ?>

  <!-- Isian diisi ulang (value) agar pengguna tidak mengetik dari awal -->
  <form method="post">
    <label>Tanggal</label>
    <input type="date" name="tanggal" value="<?= htmlspecialchars($_POST["tanggal"] ?? "") ?>" required><br>

    <label>No. bukti</label>
    <input name="no_bukti" value="<?= htmlspecialchars($_POST["no_bukti"] ?? "") ?>" required><br>

    <!-- Uraian sengaja tanpa required agar validasi server terlihat -->
    <label>Uraian</label>
    <input name="uraian" size="40" value="<?= htmlspecialchars($_POST["uraian"] ?? "") ?>"><br>

    <label>Bidang</label>
    <select name="bidang">
      <option>Keuangan &amp; Umum</option>
      <option>SDM</option>
      <option>Pelayanan Pelanggan</option>
      <option>Jaringan</option>
      <option>Konstruksi</option>
    </select><br>

    <label>Jenis biaya</label>
    <select name="jenis">
      <option>ATK &amp; Cetak</option>
      <option>Konsumsi Rapat</option>
      <option>BBM Kendaraan</option>
      <option>Perlengkapan K3</option>
      <option>Lain-lain</option>
    </select><br>

    <label>Jumlah (Rp)</label>
    <input type="number" name="jumlah" value="<?= htmlspecialchars($_POST["jumlah"] ?? "") ?>"><br>

    <button type="submit">Simpan</button>
  </form>
</body>

</html>
