<?php
// inc/header.php — bagian atas yang sama di semua halaman (kepala, menu, pesan).
// Sebelum memanggil berkas ini, halaman wajib mengisi $judul (judul tab) dan $aktif (nama berkas menu aktif).
$menu = [                                                         // daftar menu: nama berkas => tulisan di menu
    "index.php"     => "Dasbor",
    "transaksi.php" => "Transaksi",
    "form.php"      => "Catat Baru",
    "rekap.php"     => "Rekap Bulanan",
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">                                          <!-- pengodean huruf -->
  <meta name="viewport" content="width=device-width, initial-scale=1">  <!-- agar rapi di ponsel -->
  <title><?= e($judul) ?> · Aplikasi Kas Kecil</title>            <!-- judul tab -->
  <link rel="stylesheet" href="assets/gaya.css">                  <!-- tampilan bersama -->
</head>
<body>
<header class="kepala">
  <div class="merek">Aplikasi Kas Kecil <small>Kantor Unit Contoh · data fiktif pelatihan</small></div>
  <nav>
    <?php foreach ($menu as $berkas => $label): ?>                <!-- buat satu tautan per menu -->
      <a href="<?= $berkas ?>" class="<?= $aktif === $berkas ? "aktif" : "" ?>"><?= $label ?></a>
    <?php endforeach; ?>
  </nav>
</header>
<main>
<?php if ($pesan = ambilPesan()): ?>                              <!-- tampilkan pesan dari halaman sebelumnya, jika ada -->
  <div class="pesan <?= e($pesan[1]) ?>"><?= e($pesan[0]) ?></div>
<?php endif; ?>
