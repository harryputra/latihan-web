<?php
// b3-perjadin.php — kalkulator perjalanan dinas diproses di server (tarif contoh)

// Ambil isian formulir; jika belum ada, pakai nilai awal. (int) = bilangan bulat
$hari      = (int) ($_POST["hari"] ?? 3);
$harian    = (int) ($_POST["harian"] ?? 400000);
$transport = (int) ($_POST["transport"] ?? 1200000);
$tujuan    = $_POST["tujuan"] ?? "";

$total = null;  // null = belum ada hasil

// Hitung hanya jika formulir dikirim (POST)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $total = $hari * $harian + $transport;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Perjalanan Dinas</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 24px; }
    label { display: inline-block; width: 200px; }
    input { padding: 5px; width: 160px; }
    .hasil { margin-top: 14px; padding: 12px; background: #e2f0fd; border-radius: 8px; }
  </style>
</head>

<body>
  <h1>Kalkulator Biaya Perjalanan Dinas</h1>

  <!-- method=post: data dikirim tersembunyi ke halaman ini -->
  <form method="post">
    <p>
      <label>Kota tujuan</label>
      <!-- htmlspecialchars mengamankan teks yang diketik pengguna -->
      <input name="tujuan" value="<?= htmlspecialchars($tujuan) ?>" required>
    </p>
    <p>
      <label>Lama (hari)</label>
      <input type="number" name="hari" value="<?= $hari ?>" min="1">
    </p>
    <p>
      <label>Uang harian (Rp)</label>
      <input type="number" name="harian" value="<?= $harian ?>">
    </p>
    <p>
      <label>Transportasi PP (Rp)</label>
      <input type="number" name="transport" value="<?= $transport ?>">
    </p>
    <button type="submit">Hitung</button>
  </form>

  <?php if ($total !== null): ?>
    <div class="hasil">
      Perjalanan ke <b><?= htmlspecialchars($tujuan) ?></b> selama <?= $hari ?> hari:
      total <b>Rp <?= number_format($total, 0, ",", ".") ?></b>
    </div>
  <?php endif; ?>
</body>

</html>
