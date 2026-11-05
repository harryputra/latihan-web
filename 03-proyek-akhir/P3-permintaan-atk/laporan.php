<?php
// ==========================================================================
// laporan.php — LAPORAN BULANAN: pemakaian per bidang dan mutasi per barang
// (stok awal, masuk, keluar, stok akhir) untuk bulan terpilih. Siap dicetak.
// ==========================================================================
require "config.php";

$bulan = $_GET["bulan"] ?? ($db->query("SELECT DATE_FORMAT(MAX(tanggal), '%Y-%m') FROM permintaan WHERE status = 'Diserahkan'")->fetchColumn() ?: date("Y-m"));
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan)) $bulan = date("Y-m");
$awalBulan  = $bulan . "-01";                                     // tanggal pertama bulan terpilih
$akhirBulan = date("Y-m-t", strtotime($awalBulan));               // t = tanggal terakhir (28–31)

// 1. Pemakaian per bidang (hanya yang sudah diserahkan).
$st = $db->prepare("SELECT p.bidang, COUNT(*) AS kali, SUM(p.jumlah * b.harga) AS nilai
                    FROM permintaan p JOIN barang b ON b.id = p.barang_id
                    WHERE p.status = 'Diserahkan' AND p.tanggal BETWEEN ? AND ?
                    GROUP BY p.bidang ORDER BY nilai DESC");
$st->execute([$awalBulan, $akhirBulan]);
$perBidang = $st->fetchAll();
$totalPakai = array_sum(array_column($perBidang, "nilai"));

// 2. Mutasi per barang. Stok akhir bulan dihitung mundur dari stok saat ini:
//    stok akhir bulan = stok sekarang - masuk setelah bulan itu + keluar setelah bulan itu.
//    Stok awal bulan  = stok akhir bulan - masuk bulan itu + keluar bulan itu.
$st = $db->prepare("SELECT b.kode, b.nama, b.satuan, b.stok,
      (SELECT COALESCE(SUM(jumlah), 0) FROM barang_masuk WHERE barang_id = b.id AND tanggal BETWEEN ? AND ?) AS masuk,
      (SELECT COALESCE(SUM(jumlah), 0) FROM permintaan WHERE barang_id = b.id AND status = 'Diserahkan' AND tanggal BETWEEN ? AND ?) AS keluar,
      (SELECT COALESCE(SUM(jumlah), 0) FROM barang_masuk WHERE barang_id = b.id AND tanggal > ?) AS masuk_sesudah,
      (SELECT COALESCE(SUM(jumlah), 0) FROM permintaan WHERE barang_id = b.id AND status = 'Diserahkan' AND tanggal > ?) AS keluar_sesudah
    FROM barang b ORDER BY b.kode");                              // subkueri: kueri kecil di dalam kueri utama
$st->execute([$awalBulan, $akhirBulan, $awalBulan, $akhirBulan, $akhirBulan, $akhirBulan]);
$mutasi = $st->fetchAll();

$judul = "Laporan";
$aktif = "laporan.php";
require "inc/header.php";
?>

<div class="baris-judul">
  <div><h1>Laporan Persediaan ATK</h1><p class="sub">Periode <?= tgl($awalBulan) ?> s.d. <?= tgl($akhirBulan) ?></p></div>
  <div class="aksi">
    <form method="get" class="filter"><div class="isian"><label for="bulan">Bulan</label>
      <input type="month" id="bulan" name="bulan" value="<?= $bulan ?>" onchange="this.form.submit()"></div></form>
    <button class="tombol" onclick="window.print()">Cetak</button>
  </div>
</div>

<div class="panel">
  <h2>Pemakaian per Bidang</h2>
  <div class="tabel-wadah"><table>
    <tr><th>Bidang</th><th class="n">Permintaan diserahkan</th><th class="n">Nilai (Rp)</th><th style="width:30%">Porsi</th></tr>
    <?php foreach ($perBidang as $r): $porsi = $totalPakai ? $r["nilai"] / $totalPakai * 100 : 0; ?>
      <tr><td><?= e($r["bidang"]) ?></td><td class="n"><?= $r["kali"] ?></td><td class="n"><?= rp($r["nilai"]) ?></td>
          <td><div class="batang"><span style="width: <?= round($porsi) ?>%"></span></div><small><?= number_format($porsi, 1, ",", ".") ?>%</small></td></tr>
    <?php endforeach; ?>
    <?php if (!$perBidang): ?><tr><td colspan="4" class="kosong">Belum ada pemakaian pada bulan ini.</td></tr>
    <?php else: ?><tr class="total"><td>Total</td><td class="n"><?= array_sum(array_column($perBidang, "kali")) ?></td><td class="n"><?= rp($totalPakai) ?></td><td></td></tr><?php endif; ?>
  </table></div>
</div>

<div class="panel">
  <h2>Mutasi Stok per Barang</h2>
  <div class="tabel-wadah"><table>
    <tr><th>Kode</th><th>Barang</th><th class="n">Stok awal</th><th class="n">Masuk</th><th class="n">Keluar</th><th class="n">Stok akhir</th></tr>
    <?php foreach ($mutasi as $m):
        $akhir = $m["stok"] - $m["masuk_sesudah"] + $m["keluar_sesudah"];   // stok pada akhir bulan terpilih
        $awal  = $akhir - $m["masuk"] + $m["keluar"];                       // stok pada awal bulan terpilih
    ?>
      <tr><td><?= e($m["kode"]) ?></td><td><?= e($m["nama"]) ?> <small>(<?= e($m["satuan"]) ?>)</small></td>
          <td class="n"><?= $awal ?></td><td class="n"><?= $m["masuk"] ? "+" . $m["masuk"] : "–" ?></td>
          <td class="n"><?= $m["keluar"] ? "−" . $m["keluar"] : "–" ?></td><td class="n"><b><?= $akhir ?></b></td></tr>
    <?php endforeach; ?>
  </table></div>
  <p class="sub" style="margin:10px 0 0">Rumus: stok akhir = stok awal + masuk − keluar.</p>
</div>

<?php require "inc/footer.php"; ?>
