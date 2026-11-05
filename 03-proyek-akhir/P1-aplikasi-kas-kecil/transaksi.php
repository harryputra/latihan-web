<?php
// ==========================================================================
// transaksi.php — DAFTAR TRANSAKSI dengan filter bulan, bidang, dan kata kunci.
// Dari sini pengguna dapat mengubah, menghapus, atau mengekspor data ke CSV (Excel).
// ==========================================================================
require "config.php";                                             // koneksi $db dan fungsi bantu
require "inc/filter.php";                                         // menghasilkan $filter, $sqlWhere, $nilaiWhere

$st = $db->prepare("SELECT * FROM kas_kecil" . $sqlWhere . " ORDER BY tanggal DESC, id DESC");  // gabungkan syarat filter
$st->execute($nilaiWhere);                                        // isi semua tanda ? sesuai urutan
$data = $st->fetchAll();                                          // seluruh baris hasil
$total = array_sum(array_column($data, "jumlah"));                // jumlahkan kolom "jumlah"

$daftarBulan = $db->query("SELECT DISTINCT DATE_FORMAT(tanggal, '%Y-%m') FROM kas_kecil ORDER BY 1 DESC")->fetchAll(PDO::FETCH_COLUMN);

$judul = "Transaksi";
$aktif = "transaksi.php";
require "inc/header.php";
?>

<div class="baris-judul">
  <div>
    <h1>Daftar Transaksi</h1>
    <p class="sub"><?= count($data) ?> transaksi · total Rp <?= rp($total) ?></p>
  </div>
  <div class="aksi">
    <a class="tombol" href="form.php">+ Catat Baru</a>
    <a class="tombol sekunder" href="ekspor.php?<?= e(http_build_query($filter)) ?>">Unduh CSV</a>  <!-- ekspor memakai filter yang sama -->
  </div>
</div>

<form method="get" class="panel filter">                          <!-- method=get: filter tampil di alamat, bisa disimpan sebagai bookmark -->
  <div class="isian">
    <label for="bulan">Bulan</label>
    <select id="bulan" name="bulan">
      <option value="">Semua bulan</option>
      <?php foreach ($daftarBulan as $b): ?>
        <option value="<?= $b ?>" <?= $b === $filter["bulan"] ? "selected" : "" ?>><?= namaBulan($b) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="isian">
    <label for="bidang">Bidang</label>
    <select id="bidang" name="bidang">
      <option value="">Semua bidang</option>
      <?php foreach (DAFTAR_BIDANG as $b): ?>
        <option <?= $b === $filter["bidang"] ? "selected" : "" ?>><?= e($b) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="isian">
    <label for="kata">Kata kunci</label>
    <input id="kata" name="kata" value="<?= e($filter["kata"]) ?>" placeholder="uraian atau no. bukti">
  </div>
  <button class="tombol" type="submit">Terapkan</button>
  <a class="tombol sekunder" href="transaksi.php">Atur ulang</a>  <!-- kembali tanpa filter -->
</form>

<div class="tabel-wadah">
  <table>
    <tr><th>Tanggal</th><th>No. Bukti</th><th>Uraian</th><th>Bidang</th><th>Jenis</th><th class="n">Jumlah (Rp)</th><th class="tidak-dicetak">Aksi</th></tr>
    <?php foreach ($data as $t): ?>
      <tr>
        <td><?= tgl($t["tanggal"]) ?></td>
        <td><?= e($t["no_bukti"]) ?></td>
        <td><?= e($t["uraian"]) ?></td>
        <td><?= e($t["bidang"]) ?></td>
        <td><?= e($t["jenis"]) ?></td>
        <td class="n"><?= rp($t["jumlah"]) ?></td>
        <td class="aksi tidak-dicetak">
          <a class="tombol kecil sekunder" href="form.php?id=<?= $t["id"] ?>">Ubah</a>  <!-- buka formulir dengan data terisi -->
          <form method="post" action="hapus.php" data-konfirmasi="Hapus transaksi <?= e($t["no_bukti"]) ?>?">  <!-- hapus wajib lewat POST + konfirmasi -->
            <input type="hidden" name="id" value="<?= $t["id"] ?>">
            <button class="tombol kecil bahaya" type="submit">Hapus</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$data): ?>
      <tr><td colspan="7" class="kosong">Tidak ada transaksi yang cocok dengan filter.</td></tr>
    <?php else: ?>
      <tr class="total"><td colspan="5">Total</td><td class="n"><?= rp($total) ?></td><td class="tidak-dicetak"></td></tr>
    <?php endif; ?>
  </table>
</div>

<?php require "inc/footer.php"; ?>
