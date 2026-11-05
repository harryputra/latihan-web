<?php
// inc/header.php — bagian atas yang sama di semua halaman. Halaman wajib mengisi $judul dan $aktif.
$menu = ["index.php" => "Jadwal", "ajukan.php" => "Ajukan", "daftar.php" => "Daftar Pengajuan", "ruang.php" => "Ruang"];
$jumlahMenunggu = $db->query("SELECT COUNT(*) FROM peminjaman WHERE status = 'Menunggu'")->fetchColumn();  // angka di menu Daftar
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($judul) ?> · Peminjaman Ruang Rapat</title>
  <link rel="stylesheet" href="assets/gaya.css">
</head>
<body>
<header class="kepala">
  <div class="merek">Peminjaman Ruang Rapat <small>Kantor Unit Contoh · data fiktif pelatihan</small></div>
  <nav>
    <?php foreach ($menu as $berkas => $label): ?>
      <a href="<?= $berkas ?>" class="<?= $aktif === $berkas ? "aktif" : "" ?>"><?= $label ?><?= $berkas === "daftar.php" && $jumlahMenunggu ? " ($jumlahMenunggu)" : "" ?></a>  <!-- jumlah pengajuan menunggu -->
    <?php endforeach; ?>
  </nav>
</header>
<main>
<?php if ($pesan = ambilPesan()): ?>
  <div class="pesan <?= e($pesan[1]) ?>"><?= e($pesan[0]) ?></div>  <!-- pesan dari halaman sebelumnya -->
<?php endif; ?>
