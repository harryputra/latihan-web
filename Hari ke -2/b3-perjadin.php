<?php
// b3-perjadin.php — kalkulator perjalanan dinas diproses di server (tarif contoh)


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
  <form method="">
    <p>
      <label>Kota tujuan</label>
      <!-- htmlspecialchars mengamankan teks yang diketik pengguna -->
      <input name="tujuan" value="<?=  ?>" required>
    </p>
    <p>
      <label>Lama (hari)</label>
      <input type="number" name="hari" value="<?=  ?>" min="1">
    </p>
    <p>
      <label>Uang harian (Rp)</label>
      <input type="number" name="harian" value="<?=  ?>">
    </p>
    <p>
      <label>Transportasi PP (Rp)</label>
      <input type="number" name="transport" value="<?=  ?>">
    </p>
    <button type="submit">Hitung</button>
  </form>

  <?php if ($total !== null): ?>
    <div class="hasil">
      Perjalanan ke <b><?=  ?></b> selama <?=  ?> hari:
      total <b>Rp <?=  ?></b>
    </div>
  <?php endif; ?>
</body>

</html>
