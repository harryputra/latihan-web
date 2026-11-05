<?php
// ==========================================================================
// index.php — DASBOR persediaan: ringkasan stok, barang yang perlu diisi ulang,
// permintaan menunggu, dan grafik nilai pemakaian per bidang bulan berjalan.
// ==========================================================================
require "config.php";

// 1. Angka ringkasan (masing-masing satu nilai, diambil dengan fetchColumn).
$jenisBarang  = $db->query("SELECT COUNT(*) FROM barang")->fetchColumn();
$nilaiStok    = $db->query("SELECT COALESCE(SUM(stok * harga), 0) FROM barang")->fetchColumn();  // stok x harga, dijumlahkan
$jumlahMenipis = $db->query("SELECT COUNT(*) FROM barang WHERE stok <= stok_minimum")->fetchColumn();
$menunggu     = $db->query("SELECT COUNT(*) FROM permintaan WHERE status = 'Menunggu'")->fetchColumn();

// 2. Daftar barang yang stoknya sudah di batas minimum atau di bawahnya.
$menipis = $db->query("SELECT *, stok_minimum * 2 - stok AS saran_beli FROM barang
                       WHERE stok <= stok_minimum ORDER BY stok / GREATEST(stok_minimum, 1)")->fetchAll();  // saran: isi sampai 2x minimum

// 3. Bulan yang dianalisis = bulan permintaan terakhir yang diserahkan.
$bulan = $db->query("SELECT DATE_FORMAT(MAX(tanggal), '%Y-%m') FROM permintaan WHERE status = 'Diserahkan'")->fetchColumn() ?: date("Y-m");
$st = $db->prepare("SELECT p.bidang, SUM(p.jumlah * b.harga) AS nilai
                    FROM permintaan p JOIN barang b ON b.id = p.barang_id
                    WHERE p.status = 'Diserahkan' AND DATE_FORMAT(p.tanggal, '%Y-%m') = ?
                    GROUP BY p.bidang ORDER BY nilai DESC");
$st->execute([$bulan]);
$perBidang = $st->fetchAll();

// 4. Lima permintaan menunggu yang paling lama.
$antre = $db->query("SELECT p.*, b.nama, b.satuan, b.stok FROM permintaan p JOIN barang b ON b.id = p.barang_id
                     WHERE p.status = 'Menunggu' ORDER BY p.tanggal, p.id LIMIT 5")->fetchAll();

$judul = "Dasbor";
$aktif = "index.php";
require "inc/header.php";
?>

<h1>Dasbor Persediaan ATK</h1>
<p class="sub">Posisi per <?= tgl(date("Y-m-d")) ?> · nilai persediaan dihitung dari stok × harga satuan terakhir</p>

<div class="kartu-baris">
  <div class="kartu"><div class="label">Jenis barang</div><div class="nilai"><?= $jenisBarang ?></div></div>
  <div class="kartu"><div class="label">Nilai persediaan</div><div class="nilai">Rp <?= rp($nilaiStok) ?></div></div>
  <div class="kartu <?= $jumlahMenipis ? "bahaya" : "baik" ?>"><div class="label">Stok menipis</div><div class="nilai"><?= $jumlahMenipis ?></div>
    <div class="ket"><?= $jumlahMenipis ? "Perlu segera diisi ulang" : "Semua stok aman" ?></div></div>
  <div class="kartu <?= $menunggu ? "waspada" : "" ?>"><div class="label">Permintaan menunggu</div><div class="nilai"><?= $menunggu ?></div>
    <div class="ket"><a href="permintaan.php?status=Menunggu">Proses sekarang</a></div></div>
</div>

<div class="dua-kolom">
  <div class="panel">
    <h2>Barang Perlu Diisi Ulang</h2>
    <div class="tabel-wadah"><table>
      <tr><th>Barang</th><th class="n">Stok</th><th class="n">Min.</th><th class="n">Saran beli</th></tr>
      <?php foreach ($menipis as $b): ?>
        <tr><td><?= e($b["nama"]) ?><br><small><?= e($b["kode"]) ?></small></td>
            <td class="n" style="color:var(--merah);font-weight:700"><?= $b["stok"] ?></td>
            <td class="n"><?= $b["stok_minimum"] ?></td>
            <td class="n"><?= max(1, $b["saran_beli"]) ?> <?= e($b["satuan"]) ?></td></tr>
      <?php endforeach; ?>
      <?php if (!$menipis): ?><tr><td colspan="4" class="kosong">Tidak ada stok yang menipis.</td></tr><?php endif; ?>
    </table></div>
  </div>
  <div class="panel">
    <h2>Nilai Pemakaian per Bidang · <?= date("m/Y", strtotime($bulan . "-01")) ?></h2>
    <canvas id="grafikBidang" height="220"></canvas>
  </div>
</div>

<div class="panel">
  <h2>Antrean Permintaan</h2>
  <div class="tabel-wadah"><table>
    <tr><th>Tanggal</th><th>Pemohon</th><th>Barang</th><th class="n">Jumlah</th><th class="n">Stok</th><th>Keterangan</th></tr>
    <?php foreach ($antre as $p): ?>
      <tr><td><?= tgl($p["tanggal"]) ?></td><td><?= e($p["pemohon"]) ?><br><small><?= e($p["bidang"]) ?></small></td>
          <td><?= e($p["nama"]) ?></td><td class="n"><?= $p["jumlah"] ?> <?= e($p["satuan"]) ?></td><td class="n"><?= $p["stok"] ?></td>
          <td><?= $p["stok"] >= $p["jumlah"] ? '<span class="lencana setuju">Stok cukup</span>' : '<span class="lencana tolak">Stok kurang</span>' ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$antre): ?><tr><td colspan="6" class="kosong">Tidak ada permintaan yang menunggu.</td></tr><?php endif; ?>
  </table></div>
</div>

<script src="assets/chart.umd.min.js"></script>
<script>
  const data = <?= json_encode($perBidang) ?>;                    // data dari PHP -> JavaScript
  new Chart(document.getElementById("grafikBidang"), {
    type: "bar",
    data: { labels: data.map(d => d.bidang),                      // nama bidang di sumbu mendatar
            datasets: [{ label: "Rp", data: data.map(d => Number(d.nilai)), backgroundColor: "#4a3f8f" }] },
    options: { plugins: { legend: { display: false } }, animation: false,
               scales: { y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString("id-ID") } } } }  // angka bertitik ribuan
  });
</script>

<?php require "inc/footer.php"; ?>
