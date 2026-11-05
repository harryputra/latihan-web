<?php
// b3-konsumsi-rapat.php — jawaban mandiri Blok 3: estimasi konsumsi rapat

// Ambil isian formulir; jika belum ada, pakai nilai awal
$peserta    =
$snack      =
$makan      =
$pakaiMakan =   // true jika kotak dicentang

$galat = "";    // wadah pesan galat
$total = null;  // null = belum ada hasil

// Proses hanya saat formulir dikirim
if ($_SERVER["REQUEST_METHOD"] === "POST") {

}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Konsumsi Rapat</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 24px; }
    label { display: inline-block; width: 200px; }
    input[type=number] { padding: 5px; width: 140px; }
    .hasil { margin-top: 14px; padding: 12px; background: #def2e6; border-radius: 8px; }
    .galat { color: #b3261e; font-weight: bold; }
  </style>
</head>

<body>
  <h1>Estimasi Konsumsi Rapat</h1>

  <form method="post">
    <p>
      <label>Jumlah peserta</label>
      <input type="number" name="peserta" value="<?=  ?>">
    </p>
    <p>
      <label>Snack per orang (Rp)</label>
      <input type="number" name="snack" value="<?=  ?>">
    </p>
    <p>
      <label>Makan siang per orang (Rp)</label>
      <input type="number" name="makan" value="<?=  ?>">
    </p>
    <p>
      <label>&nbsp;</label>
      <!-- Kotak tetap tercentang setelah formulir dikirim -->
      <input type="checkbox" name="pakai_makan" <?=  ? "checked" : "" ?>> dengan makan siang
    </p>
    <button type="submit">Hitung</button>
  </form>

  <?php if ($galat): ?>
    <p class="galat"><?= $galat ?></p>
  <?php endif; ?>

  <?php if ($total !== null): ?>
    <div class="hasil">
      <?=  ?>
      = <b>Rp <?=  ?></b>
    </div>
  <?php endif; ?>
</body>

</html>
