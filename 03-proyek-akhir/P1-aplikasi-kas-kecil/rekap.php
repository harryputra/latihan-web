<?php
// ==========================================================================
// rekap.php — REKAP BULANAN berbentuk tabel silang (pivot): bidang x jenis biaya.
// Dilengkapi total baris, total kolom, dan tombol cetak.
// ==========================================================================
require "config.php";

$daftarBulan = $db->query("SELECT DISTINCT DATE_FORMAT(tanggal, '%Y-%m') FROM kas_kecil ORDER BY 1 DESC")->fetchAll(PDO::FETCH_COLUMN);
$bulan = $_GET["bulan"] ?? ($daftarBulan[0] ?? date("Y-m"));      // bawaan: bulan terbaru
if (!bulanValid($bulan)) {
    $bulan = date("Y-m");
}

// 1. Ambil total per pasangan bidang-jenis (GROUP BY dua kolom).
$st = $db->prepare("SELECT bidang, jenis, SUM(jumlah) AS total FROM kas_kecil
                    WHERE DATE_FORMAT(tanggal, '%Y-%m') = ? GROUP BY bidang, jenis");
$st->execute([$bulan]);

// 2. Susun menjadi tabel silang: $silang["Jaringan"]["ATK & Cetak"] = 1275000
$silang = [];
$totalBidang = array_fill_keys(DAFTAR_BIDANG, 0);                 // total per baris, mulai 0
$totalJenis  = array_fill_keys(DAFTAR_JENIS, 0);                  // total per kolom, mulai 0
foreach ($st->fetchAll() as $r) {
    $silang[$r["bidang"]][$r["jenis"]] = $r["total"];             // isi sel
    $totalBidang[$r["bidang"]] = ($totalBidang[$r["bidang"]] ?? 0) + $r["total"];  // tambah ke total baris
    $totalJenis[$r["jenis"]] = ($totalJenis[$r["jenis"]] ?? 0) + $r["total"];      // tambah ke total kolom
}
$totalSemua = array_sum($totalBidang);

// 3. Plafon untuk informasi serapan.
$st = $db->prepare("SELECT nilai FROM plafon WHERE bulan = ?");
$st->execute([$bulan]);
$plafon = (float) $st->fetchColumn();

$judul = "Rekap Bulanan";
$aktif = "rekap.php";
require "inc/header.php";
?>

<div class="baris-judul">
  <div>
    <h1>Rekap Kas Kecil <?= namaBulan($bulan) ?></h1>
    <p class="sub">Total Rp <?= rp($totalSemua) ?><?= $plafon ? " dari plafon Rp " . rp($plafon) . " (" . number_format($totalSemua / $plafon * 100, 1, ",", ".") . "%)" : "" ?></p>
  </div>
  <div class="aksi">
    <form method="get" class="filter">
      <div class="isian">
        <label for="bulan">Bulan</label>
        <select id="bulan" name="bulan" data-kirim-otomatis>
          <?php foreach ($daftarBulan as $b): ?>
            <option value="<?= $b ?>" <?= $b === $bulan ? "selected" : "" ?>><?= namaBulan($b) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </form>
    <button class="tombol" onclick="window.print()">Cetak</button>  <!-- buka dialog cetak peramban (bisa simpan PDF) -->
  </div>
</div>

<div class="panel tabel-wadah">
  <table>
    <tr>
      <th>Bidang</th>
      <?php foreach (DAFTAR_JENIS as $j): ?><th class="n"><?= e($j) ?></th><?php endforeach; ?>  <!-- satu kolom per jenis -->
      <th class="n">Total</th>
      <th>Porsi</th>
    </tr>
    <?php foreach (DAFTAR_BIDANG as $b): ?>                        <!-- satu baris per bidang -->
      <?php $porsi = $totalSemua > 0 ? $totalBidang[$b] / $totalSemua * 100 : 0; ?>
      <tr>
        <td><?= e($b) ?></td>
        <?php foreach (DAFTAR_JENIS as $j): ?>
          <td class="n"><?= isset($silang[$b][$j]) ? rp($silang[$b][$j]) : "–" ?></td>  <!-- tanda – bila tidak ada transaksi -->
        <?php endforeach; ?>
        <td class="n"><b><?= rp($totalBidang[$b]) ?></b></td>
        <td><div class="batang"><span style="width: <?= round($porsi) ?>%"></span></div><small><?= number_format($porsi, 1, ",", ".") ?>%</small></td>
      </tr>
    <?php endforeach; ?>
    <tr class="total">
      <td>Total</td>
      <?php foreach (DAFTAR_JENIS as $j): ?><td class="n"><?= rp($totalJenis[$j]) ?></td><?php endforeach; ?>
      <td class="n"><?= rp($totalSemua) ?></td>
      <td>100%</td>
    </tr>
  </table>
</div>

<?php require "inc/footer.php"; ?>
