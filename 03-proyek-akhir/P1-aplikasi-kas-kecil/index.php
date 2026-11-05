<?php
// ==========================================================================
// index.php — DASBOR: ringkasan kas kecil satu bulan (kartu, grafik, transaksi terbesar).
// ==========================================================================
require "config.php";                                             // koneksi $db dan fungsi bantu

// 1. Tentukan bulan yang ditampilkan: dari pilihan pengguna, atau bulan transaksi terakhir.
$daftarBulan = $db->query("SELECT bulan FROM plafon ORDER BY bulan DESC")->fetchAll(PDO::FETCH_COLUMN);  // bulan yang punya plafon
$bulanTerakhir = $db->query("SELECT DATE_FORMAT(MAX(tanggal), '%Y-%m') FROM kas_kecil")->fetchColumn();   // misal 2026-11
$bulan = $_GET["bulan"] ?? ($bulanTerakhir ?: date("Y-m"));       // ?? = pakai nilai kanan bila kiri tidak ada
if (!bulanValid($bulan)) {                                        // cegah isian aneh pada alamat
    $bulan = date("Y-m");
}

// 2. Plafon bulan tersebut (0 bila belum diatur).
$st = $db->prepare("SELECT nilai FROM plafon WHERE bulan = ?");   // prepared statement: aman dari SQL injection
$st->execute([$bulan]);                                           // isi tanda ? dengan $bulan
$plafon = (float) $st->fetchColumn();                             // fetchColumn = ambil satu nilai saja

// 3. Jumlah transaksi dan total pengeluaran bulan tersebut.
$st = $db->prepare("SELECT COUNT(*) AS banyak, COALESCE(SUM(jumlah), 0) AS total
                    FROM kas_kecil WHERE DATE_FORMAT(tanggal, '%Y-%m') = ?");
$st->execute([$bulan]);
$ringkas = $st->fetch();                                          // satu baris: ["banyak" => .., "total" => ..]
$terpakai = (float) $ringkas["total"];
$sisa = $plafon - $terpakai;                                      // sisa dana
$persen = $plafon > 0 ? $terpakai / $plafon * 100 : 0;            // persentase terpakai (hindari bagi nol)
$status = $persen >= 90 ? "bahaya" : ($persen >= 75 ? "waspada" : "baik");  // warna kartu mengikuti persentase

// 4. Rekap per bidang dan per jenis untuk grafik.
$st = $db->prepare("SELECT bidang, SUM(jumlah) AS total FROM kas_kecil
                    WHERE DATE_FORMAT(tanggal, '%Y-%m') = ? GROUP BY bidang ORDER BY total DESC");
$st->execute([$bulan]);
$perBidang = $st->fetchAll();                                     // banyak baris: bidang + total

$st = $db->prepare("SELECT jenis, SUM(jumlah) AS total FROM kas_kecil
                    WHERE DATE_FORMAT(tanggal, '%Y-%m') = ? GROUP BY jenis ORDER BY total DESC");
$st->execute([$bulan]);
$perJenis = $st->fetchAll();

// 5. Lima transaksi terbesar bulan tersebut.
$st = $db->prepare("SELECT * FROM kas_kecil WHERE DATE_FORMAT(tanggal, '%Y-%m') = ? ORDER BY jumlah DESC LIMIT 5");
$st->execute([$bulan]);
$terbesar = $st->fetchAll();

$judul = "Dasbor";                                                // dipakai header.php untuk judul tab
$aktif = "index.php";                                             // menu yang disorot
require "inc/header.php";                                         // cetak bagian atas halaman
?>

<div class="baris-judul">
  <div>
    <h1>Dasbor Kas Kecil</h1>
    <p class="sub">Posisi kas kecil bulan <?= namaBulan($bulan) ?></p>
  </div>
  <form method="get" class="filter">                              <!-- pilihan bulan; berpindah otomatis saat diganti -->
    <div class="isian">
      <label for="bulan">Bulan</label>
      <select id="bulan" name="bulan" data-kirim-otomatis>
        <?php foreach ($daftarBulan as $b): ?>
          <option value="<?= $b ?>" <?= $b === $bulan ? "selected" : "" ?>><?= namaBulan($b) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </form>
</div>

<div class="kartu-baris">                                         <!-- empat kartu ringkasan -->
  <div class="kartu">
    <div class="label">Plafon</div>
    <div class="nilai">Rp <?= rp($plafon) ?></div>
    <div class="ket"><?= $plafon > 0 ? "Batas dana bulan ini" : "Plafon belum diatur" ?></div>
  </div>
  <div class="kartu <?= $status ?>">                               <!-- warna mengikuti persentase terpakai -->
    <div class="label">Terpakai</div>
    <div class="nilai">Rp <?= rp($terpakai) ?></div>
    <div class="ket"><?= number_format($persen, 1, ",", ".") ?>% dari plafon</div>
  </div>
  <div class="kartu <?= $sisa < 0 ? "bahaya" : "" ?>">
    <div class="label">Sisa</div>
    <div class="nilai">Rp <?= rp($sisa) ?></div>
    <div class="ket"><?= $sisa < 0 ? "Melebihi plafon!" : "Dana yang masih tersedia" ?></div>
  </div>
  <div class="kartu">
    <div class="label">Jumlah transaksi</div>
    <div class="nilai"><?= $ringkas["banyak"] ?></div>
    <div class="ket">Rata-rata Rp <?= rp($ringkas["banyak"] ? $terpakai / $ringkas["banyak"] : 0) ?></div>
  </div>
</div>

<div class="dua-kolom">
  <div class="panel">
    <h2>Pengeluaran per Bidang</h2>
    <canvas id="grafikBidang" height="230"></canvas>              <!-- kanvas grafik batang -->
  </div>
  <div class="panel">
    <h2>Komposisi per Jenis Biaya</h2>
    <canvas id="grafikJenis" height="230"></canvas>               <!-- kanvas grafik donat -->
  </div>
</div>

<div class="panel">
  <h2>Lima Transaksi Terbesar</h2>
  <div class="tabel-wadah">
    <table>
      <tr><th>Tanggal</th><th>No. Bukti</th><th>Uraian</th><th>Bidang</th><th class="n">Jumlah (Rp)</th></tr>
      <?php foreach ($terbesar as $t): ?>                         <!-- satu baris per transaksi -->
        <tr>
          <td><?= tgl($t["tanggal"]) ?></td>
          <td><?= e($t["no_bukti"]) ?></td>
          <td><?= e($t["uraian"]) ?></td>
          <td><?= e($t["bidang"]) ?></td>
          <td class="n"><?= rp($t["jumlah"]) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$terbesar): ?>                                   <!-- pesan bila bulan ini belum ada transaksi -->
        <tr><td colspan="5" class="kosong">Belum ada transaksi pada bulan ini.</td></tr>
      <?php endif; ?>
    </table>
  </div>
</div>

<script src="assets/chart.umd.min.js"></script>                   <!-- pustaka grafik, disimpan lokal (tanpa internet) -->
<script>
  // Data dari PHP diubah menjadi format JavaScript dengan json_encode.
  const perBidang = <?= json_encode($perBidang) ?>;               // contoh: [{"bidang":"Jaringan","total":"2785000.00"}, ...]
  const perJenis  = <?= json_encode($perJenis) ?>;

  new Chart(document.getElementById("grafikBidang"), {            // grafik batang mendatar
    type: "bar",
    data: {
      labels: perBidang.map(b => b.bidang),                       // ambil nama bidang saja
      datasets: [{ label: "Rp", data: perBidang.map(b => Number(b.total)), backgroundColor: "#0b4a8b" }]  // ambil totalnya
    },
    options: { indexAxis: "y", plugins: { legend: { display: false } }, animation: false,   // indexAxis y = batang mendatar
               scales: { x: { ticks: { callback: v => v.toLocaleString("id-ID") } } } }   // angka sumbu memakai titik ribuan
  });

  new Chart(document.getElementById("grafikJenis"), {             // grafik donat komposisi
    type: "doughnut",
    data: {
      labels: perJenis.map(j => j.jenis),
      datasets: [{ data: perJenis.map(j => Number(j.total)),
                   backgroundColor: ["#0b4a8b", "#137a6b", "#c99a2e", "#7a5ea8", "#9aa5b8"] }]
    },
    options: { plugins: { legend: { position: "right" } }, animation: false }
  });
</script>

<?php require "inc/footer.php"; ?>
