<?php
// b7-filter-bidang.php — jawaban mandiri Blok 7: filter per bidang dengan prepared statement

require "config.php";

// Bidang dari alamat (?bidang=...), awalnya Jaringan
$bidang = $_GET["bidang"] ?? "Jaringan";

// Daftar nama bidang untuk pilihan; FETCH_COLUMN = ambil satu kolom saja
$daftar = $db->query("SELECT bidang FROM anggaran ORDER BY bidang")
             ->fetchAll(PDO::FETCH_COLUMN);

// Prepared statement: tanda ? diisi belakangan dengan aman
$st = $db->prepare("SELECT * FROM kas_kecil WHERE bidang = ? ORDER BY tanggal");
$st->execute([$bidang]);
$data = $st->fetchAll();

// Jumlahkan kolom jumlah
$total = array_sum(array_column($data, "jumlah"));
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Kas Kecil per Bidang</title>
  <link rel="stylesheet" href="gaya.css">
</head>

<body>
  <h1>Kas Kecil per Bidang</h1>

  <!-- method=get: pilihan tampil di alamat -->
  <form method="get">
    <label>Pilih bidang</label>
    <select name="bidang">
      <?php foreach ($daftar as $d): ?>
        <!-- Pilihan yang sedang aktif diberi selected -->
        <option <?= $d === $bidang ? "selected" : "" ?>><?= htmlspecialchars($d) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit">Tampilkan</button>
  </form>

  <p><?= count($data) ?> transaksi &middot; total Rp <?= rp($total) ?></p>

  <table>
    <tr>
      <th>Tanggal</th>
      <th>No. Bukti</th>
      <th>Uraian</th>
      <th class="n">Jumlah (Rp)</th>
    </tr>

    <?php foreach ($data as $b): ?>
      <tr>
        <td><?= date("d-m-Y", strtotime($b["tanggal"])) ?></td>
        <td><?= $b["no_bukti"] ?></td>
        <td><?= htmlspecialchars($b["uraian"]) ?></td>
        <td class="n"><?= rp($b["jumlah"]) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>

</html>
