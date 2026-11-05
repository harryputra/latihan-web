<?php
// ==========================================================================
// ruang.php — DAFTAR RUANG dan TINGKAT PEMAKAIAN per bulan.
// Tingkat pemakaian = jam terpakai (disetujui) ÷ jam layanan tersedia pada hari kerja bulan itu.
// ==========================================================================
require "config.php";

$bulan = $_GET["bulan"] ?? date("Y-m");
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan)) $bulan = date("Y-m");

// 1. Hitung jumlah hari kerja (Senin–Jumat) pada bulan terpilih.
$hariKerja = 0;
$jumlahHari = (int) date("t", strtotime($bulan . "-01"));         // t = banyak hari dalam bulan (28–31)
for ($h = 1; $h <= $jumlahHari; $h++) {
    $n = date("N", strtotime("$bulan-$h"));                       // 1 = Senin ... 7 = Minggu
    if ($n <= 5) $hariKerja++;
}
$jamLayanan = (strtotime(JAM_TUTUP) - strtotime(JAM_BUKA)) / 3600;  // 07:30–17:00 = 9,5 jam per hari
$jamTersedia = $hariKerja * $jamLayanan;                          // kapasitas jam per ruang dalam sebulan

// 2. Rekap per ruang. LEFT JOIN agar ruang yang belum pernah dipakai tetap muncul (nilai 0).
$st = $db->prepare("SELECT r.*, COUNT(p.id) AS kali,
                           COALESCE(SUM(TIME_TO_SEC(TIMEDIFF(p.jam_selesai, p.jam_mulai))) / 3600, 0) AS jam,
                           COALESCE(AVG(p.peserta), 0) AS rata_peserta
                    FROM ruang r
                    LEFT JOIN peminjaman p ON p.ruang_id = r.id AND p.status = 'Disetujui'
                                           AND DATE_FORMAT(p.tanggal, '%Y-%m') = ?
                    GROUP BY r.id ORDER BY r.lantai, r.nama");
$st->execute([$bulan]);
$data = $st->fetchAll();

$judul = "Ruang";
$aktif = "ruang.php";
require "inc/header.php";
?>

<div class="baris-judul">
  <div>
    <h1>Ruang dan Tingkat Pemakaian</h1>
    <p class="sub"><?= $hariKerja ?> hari kerja × <?= str_replace(".", ",", $jamLayanan) ?> jam = <?= str_replace(".", ",", $jamTersedia) ?> jam tersedia per ruang</p>
  </div>
  <form method="get" class="filter">
    <div class="isian"><label for="bulan">Bulan</label>
      <input type="month" id="bulan" name="bulan" value="<?= $bulan ?>" onchange="this.form.submit()"></div>  <!-- type=month: pilih bulan & tahun -->
  </form>
</div>

<div class="tabel-wadah">
  <table>
    <tr><th>Ruang</th><th>Fasilitas</th><th class="n">Kapasitas</th><th class="n">Dipakai</th><th class="n">Jam</th><th class="n">Rata-rata peserta</th><th style="width:22%">Tingkat pemakaian</th></tr>
    <?php foreach ($data as $r):
        $persen = $jamTersedia ? $r["jam"] / $jamTersedia * 100 : 0;          // persentase pemakaian
        $isi = $r["kapasitas"] ? $r["rata_peserta"] / $r["kapasitas"] * 100 : 0;  // keterisian kursi rata-rata
    ?>
      <tr>
        <td><b><?= e($r["nama"]) ?></b><br><small>Lantai <?= $r["lantai"] ?></small></td>
        <td><?= e($r["fasilitas"]) ?></td>
        <td class="n"><?= $r["kapasitas"] ?></td>
        <td class="n"><?= $r["kali"] ?>×</td>
        <td class="n"><?= number_format($r["jam"], 1, ",", ".") ?></td>
        <td class="n"><?= number_format($r["rata_peserta"], 0) ?> <small>(<?= number_format($isi, 0) ?>% kursi)</small></td>
        <td><div class="batang"><span style="width: <?= min(100, round($persen)) ?>%"></span></div><small><?= number_format($persen, 1, ",", ".") ?>%</small></td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
<p class="sub" style="margin-top:12px">Catatan: hanya peminjaman berstatus <b>Disetujui</b> yang dihitung. Keterisian kursi yang rendah pada ruang besar menandakan kegiatan sebenarnya cukup memakai ruang yang lebih kecil.</p>

<?php require "inc/footer.php"; ?>
