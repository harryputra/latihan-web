<?php
// b1-halo.php — halaman PHP pertama

// Variabel diawali $; tanda = artinya menyimpan nilai
$unit    = 'Kantor Unit Contoh';      // nama unit
$periode = 'November 2026';           // periode laporan
$petugas = 'Bidang Keuangan & Umum';  // penyusun
?>
<!DOCTYPE html>
<!-- Mulai bagian HTML biasa -->
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Halo PHP</title>
</head>

<body style="font-family: Arial, sans-serif; margin: 24px;">
  <!-- Tag cetak PHP (tanda kurung-sudut + =) menampilkan isi variabel di halaman -->
  <h1>Selamat datang di <?= $unit ?></h1>
  <p>Periode laporan: <strong><?= $periode ?></strong></p>
  <p>Disusun oleh: <?= $petugas ?></p>

  <!-- date() = tanggal & jam server saat halaman dibuat -->
  <p>Halaman dibuat oleh server pada <?= date('d-m-Y H:i') ?></p>
</body>

</html>
