<?php
// ==========================================================================
// barang.php — DAFTAR BARANG & STOK dengan pencarian, status stok, dan nilai persediaan.
// ==========================================================================
require "config.php";

$kata = trim($_GET["kata"] ?? "");                                // kata kunci pencarian
$hanyaMenipis = isset($_GET["menipis"]);                          // kotak centang "hanya stok menipis"

$syarat = [];
$nilai = [];
if ($kata !== "") { $syarat[] = "(nama LIKE ? OR kode LIKE ?)"; $nilai[] = "%$kata%"; $nilai[] = "%$kata%"; }
if ($hanyaMenipis) { $syarat[] = "stok <= stok_minimum"; }
$st = $db->prepare("SELECT *, stok * harga AS nilai FROM barang" . ($syarat ? " WHERE " . implode(" AND ", $syarat) : "") . " ORDER BY kode");
$st->execute($nilai);
$data = $st->fetchAll();
$totalNilai = array_sum(array_column($data, "nilai"));

$judul = "Barang & Stok";
$aktif = "barang.php";
require "inc/header.php";
?>

<div class="baris-judul">
  <div><h1>Barang &amp; Stok</h1><p class="sub"><?= count($data) ?> barang · nilai persediaan Rp <?= rp($totalNilai) ?></p></div>
  <a class="tombol" href="barang-form.php">+ Barang Baru</a>
</div>

<form method="get" class="panel filter">
  <div class="isian"><label for="kata">Cari</label><input id="kata" name="kata" value="<?= e($kata) ?>" placeholder="nama atau kode"></div>
  <label style="display:flex;gap:6px;align-items:center;margin-bottom:8px"><input type="checkbox" name="menipis" <?= $hanyaMenipis ? "checked" : "" ?>> Hanya stok menipis</label>
  <button class="tombol" type="submit">Terapkan</button>
  <a class="tombol sekunder" href="barang.php">Atur ulang</a>
</form>

<div class="tabel-wadah"><table>
  <tr><th>Kode</th><th>Nama barang</th><th>Satuan</th><th class="n">Harga (Rp)</th><th class="n">Stok</th><th class="n">Min.</th><th>Kondisi</th><th class="n">Nilai (Rp)</th><th></th></tr>
  <?php foreach ($data as $b):
      $rasio = $b["stok_minimum"] > 0 ? $b["stok"] / ($b["stok_minimum"] * 2) * 100 : 100;  // 100% = stok dua kali minimum
      $kelas = $b["stok"] <= $b["stok_minimum"] ? "bahaya" : ($b["stok"] <= $b["stok_minimum"] * 1.5 ? "waspada" : "");
  ?>
    <tr>
      <td><?= e($b["kode"]) ?></td>
      <td><?= e($b["nama"]) ?></td>
      <td><?= e($b["satuan"]) ?></td>
      <td class="n"><?= rp($b["harga"]) ?></td>
      <td class="n"><b><?= $b["stok"] ?></b></td>
      <td class="n"><?= $b["stok_minimum"] ?></td>
      <td style="min-width:110px"><div class="batang"><span class="<?= $kelas ?>" style="width: <?= min(100, round($rasio)) ?>%"></span></div>
          <small><?= $kelas === "bahaya" ? "Menipis" : ($kelas === "waspada" ? "Mendekati batas" : "Aman") ?></small></td>
      <td class="n"><?= rp($b["nilai"]) ?></td>
      <td><a class="tombol kecil sekunder" href="barang-form.php?id=<?= $b["id"] ?>">Ubah</a></td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$data): ?><tr><td colspan="9" class="kosong">Barang tidak ditemukan.</td></tr>
  <?php else: ?><tr class="total"><td colspan="7">Total nilai persediaan</td><td class="n"><?= rp($totalNilai) ?></td><td></td></tr><?php endif; ?>
</table></div>

<?php require "inc/footer.php"; ?>
