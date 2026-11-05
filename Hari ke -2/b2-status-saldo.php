<?php
// b2-status-saldo.php — jawaban mandiri Blok 2: status saldo kas kecil per pemegang

// Array: pemegang kas => saldo (Rp)
$saldo = [
    "Keuangan & Umum"     => 2600000,
    "SDM"                 => 900000,
    "Pelayanan Pelanggan" => 1800000,
    "Jaringan"            => 3100000,
    "Konstruksi"          => 450000,
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Status Saldo Kas Kecil</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 24px; }
    td, th { padding: 6px 12px; border-bottom: 1px solid #ddd; }

    /* .n = angka rata kanan; tiga warna status */
    .n { text-align: right; }
    .merah { color: #b3261e; font-weight: bold; }
    .kuning { color: #a86b00; font-weight: bold; }
    .hijau { color: #137a6b; }
  </style>
</head>

<body>
  <h1>Status Saldo Kas Kecil per 6 November 2026</h1>

  <table>
    <tr>
      <th>Pemegang kas</th>
      <th>Saldo (Rp)</th>
      <th>Status</th>
    </tr>

    <?php foreach ($saldo as $bidang => $nilai): ?>
      <?php
      // Tentukan status dan warna berdasarkan besar saldo
      if ($nilai < 1000000) {
          $status = "Segera isi ulang";
          $kelas  = "merah";
      } elseif ($nilai < 2000000) {
          $status = "Menipis";
          $kelas  = "kuning";
      } else {
          $status = "Aman";
          $kelas  = "hijau";
      }
      ?>
      <tr>
        <td><?= $bidang ?></td>
        <td class="n"><?= number_format($nilai, 0, ",", ".") ?></td>
        <td class="<?= $kelas ?>"><?= $status ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>

</html>
