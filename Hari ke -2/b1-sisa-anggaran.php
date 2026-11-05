<?php
// b1-sisa-anggaran.php — jawaban latihan mandiri Blok 1

// Teks diapit tanda petik; angka ditulis tanpa titik pemisah ribuan
$bidang    = "Pelayanan Pelanggan";
$anggaran  = 240000000;  // Rp
$realisasi = 226000000;  // Rp

// Rumus pengurangan dan persentase serapan
$sisa    = $anggaran - $realisasi;
$serapan = $realisasi / $anggaran * 100;
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Sisa Anggaran</title>
</head>

<body style="font-family: Arial, sans-serif; margin: 24px;">
  <h1>Sisa Anggaran Bidang <?= $bidang ?></h1>

  <!-- number_format(angka, jumlah desimal, pemisah desimal, pemisah ribuan) -->
  <p>Anggaran 2026: Rp <?= number_format($anggaran, 0, ",", ".") ?></p>
  <p>Realisasi s.d. Oktober: Rp <?= number_format($realisasi, 0, ",", ".") ?></p>
  <p><strong>Sisa: Rp <?= number_format($sisa, 0, ",", ".") ?></strong></p>

  <!-- Satu angka desimal, misal 94,2 -->
  <p>Serapan: <?= number_format($serapan, 1, ",", ".") ?>%</p>
</body>

</html>
