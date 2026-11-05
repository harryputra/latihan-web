<?php
// b7-daftar-kas.php — menampilkan kas kecil Oktober dari database

// Pakai koneksi $db dan fungsi rp() dari config.php
require "config.php";

// Jalankan kueri; fetchAll = ambil semua baris
$data = $db->query(
    "SELECT * FROM kas_kecil
     WHERE MONTH(tanggal) = 10
     ORDER BY tanggal"
)->fetchAll();

$total = 0;  // penampung total, mulai dari nol
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Kas Kecil Oktober</title>
  <link rel="stylesheet" href="gaya.css">
</head>

<body>
  <!-- count() = banyaknya baris data -->
  <h1>Kas Kecil Oktober 2026 (<?= count($data) ?> transaksi)</h1>

  <table>
    <tr>
      <th>Tanggal</th>
      <th>No. Bukti</th>
      <th>Uraian</th>
      <th>Bidang</th>
      <th class="n">Jumlah (Rp)</th>
    </tr>

    <?php foreach ($data as $b): ?>
      <?php $total += $b["jumlah"]; // tambahkan ke total ?>
      <tr>
        <td><?= date("d-m-Y", strtotime($b["tanggal"])) ?></td>
        <td><?= $b["no_bukti"] ?></td>
        <td><?= htmlspecialchars($b["uraian"]) ?></td>
        <td><?= $b["bidang"] ?></td>
        <td class="n"><?= rp($b["jumlah"]) ?></td>
      </tr>
    <?php endforeach; ?>

    <tr class="total">
      <td colspan="4">Total</td>
      <td class="n"><?= rp($total) ?></td>
    </tr>
  </table>
</body>

</html>
