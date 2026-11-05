<?php
// b2-status-serapan.php — status serapan anggaran per bidang (Rp juta)

// Array: bidang => [anggaran, realisasi]
$data = [
    "Keuangan & Umum"     => [180, 142],
    "SDM"                 => [120, 101],
    "Pelayanan Pelanggan" => [240, 226],
    "Jaringan"            => [310, 248],
    "Konstruksi"          => [260, 209],
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Status Serapan</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 24px; }
    td, th { padding: 6px 12px; border-bottom: 1px solid #ddd; }

    /* Tiga warna status */
    .merah { color: #b3261e; font-weight: bold; }
    .hijau { color: #137a6b; }
    .abu { color: #6b7385; }
  </style>
</head>

<body>
  <h1>Status Serapan Anggaran s.d. Oktober 2026</h1>

  <table>
    <tr>
      <th>Bidang</th>
      <th>Serapan</th>
      <th>Status</th>
    </tr>

    <?php foreach ($data as $bidang => $nilai): ?>
      <?php
      // $nilai[0] = anggaran, $nilai[1] = realisasi
      $serapan = $nilai[1] / $nilai[0] * 100;

      if ($serapan >= 90) {
          $status = "Perlu perhatian";
          $kelas  = "merah";
      } elseif ($serapan >= 80) {
          $status = "Sesuai rencana";
          $kelas  = "hijau";
      } else {
          $status = "Serapan rendah";
          $kelas  = "abu";
      }
      ?>
      <tr>
        <td><?= $bidang ?></td>
        <td><?= number_format($serapan, 1, ",", ".") ?>%</td>
        <td class="<?= $kelas ?>"><?= $status ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>

</html>
