<?php
// inc/header.php — bagian atas yang sama di semua halaman. Halaman wajib mengisi $judul dan $aktif.
$menu = ["index.php" => "Dasbor", "permintaan.php" => "Permintaan", "barang.php" => "Barang & Stok", "masuk.php" => "Barang Masuk", "laporan.php" => "Laporan"];
$jumlahMenunggu = $db->query("SELECT COUNT(*) FROM permintaan WHERE status = 'Menunggu'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($judul) ?> · Persediaan ATK</title>
  <link rel="stylesheet" href="assets/gaya.css">
</head>
<body>
<header class="kepala">
  <div class="merek">Permintaan &amp; Persediaan ATK <small>Kantor Unit Contoh · data fiktif pelatihan</small></div>
  <nav>
    <?php foreach ($menu as $berkas => $label): ?>
      <a href="<?= $berkas ?>" class="<?= $aktif === $berkas ? "aktif" : "" ?>"><?= $label ?><?= $berkas === "permintaan.php" && $jumlahMenunggu ? " ($jumlahMenunggu)" : "" ?></a>
    <?php endforeach; ?>
  </nav>
</header>
<main>
<?php if ($pesan = ambilPesan()): ?>
  <div class="pesan <?= e($pesan[1]) ?>"><?= e($pesan[0]) ?></div>  <!-- pesan dari halaman sebelumnya -->
<?php endif; ?>
