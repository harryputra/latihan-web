<?php
// b8-tambah-kas.php — formulir menyimpan transaksi kas kecil ke database

require "config.php";

$pesan = "";

// Simpan hanya saat formulir dikirim
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Enam tanda ? untuk enam kolom
    $st = $db->prepare(
        "INSERT INTO kas_kecil (tanggal, no_bukti, uraian, bidang, jenis, jumlah)
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    // Isi tanda ? berurutan dari isian formulir
    $st->execute([
        $_POST["tanggal"],
        $_POST["no_bukti"],
        $_POST["uraian"],
        $_POST["bidang"],
        $_POST["jenis"],
        $_POST["jumlah"],
    ]);

    $pesan = "Transaksi " . htmlspecialchars($_POST["no_bukti"]) . " berhasil disimpan.";
}

// Ambil 3 transaksi terakhir (id terbesar)
$terbaru = $db->query("SELECT * FROM kas_kecil ORDER BY id DESC LIMIT 3")->fetchAll();
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

  <?php if ($pesan): ?>
    <p class="pesan"><?= $pesan ?></p>
  <?php endif; ?>

  <form method="post">
    <label>Tanggal</label>
    <input type="date" name="tanggal" required><br>

    <label>No. bukti</label>
    <input name="no_bukti" required><br>

    <label>Uraian</label>
    <input name="uraian" size="40" required><br>

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
    <input type="number" name="jumlah" required><br>

    <button type="submit">Simpan</button>
  </form>

  <h3>3 transaksi terakhir</h3>
  <table>
    <tr>
      <th>No. Bukti</th>
      <th>Uraian</th>
      <th>Bidang</th>
      <th class="n">Jumlah (Rp)</th>
    </tr>

    <?php foreach ($terbaru as $b): ?>
      <tr>
        <td><?= $b["no_bukti"] ?></td>
        <td><?= htmlspecialchars($b["uraian"]) ?></td>
        <td><?= $b["bidang"] ?></td>
        <td class="n"><?= rp($b["jumlah"]) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>

</html>
