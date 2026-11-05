<?php
// ==========================================================================
// index.php — JADWAL HARIAN: siapa memakai ruang apa, jam berapa, pada tanggal terpilih.
// Setiap ruang digambar sebagai lintasan jam 07:30–17:00; setiap peminjaman menjadi balok.
// ==========================================================================
require "config.php";

// 1. Tanggal yang ditampilkan (bawaan: hari ini). Tanggal yang tidak valid diganti hari ini.
$tanggal = $_GET["tanggal"] ?? date("Y-m-d");
$cek = DateTime::createFromFormat("Y-m-d", $tanggal);
if (!$cek || $cek->format("Y-m-d") !== $tanggal) {
    $tanggal = date("Y-m-d");
}
$kemarin = date("Y-m-d", strtotime($tanggal . " -1 day"));        // untuk tombol ‹ sebelumnya
$besok   = date("Y-m-d", strtotime($tanggal . " +1 day"));        // untuk tombol berikutnya ›

// 2. Data ruang dan peminjaman pada tanggal tersebut (yang ditolak tidak ditampilkan).
$daftarRuang = $db->query("SELECT * FROM ruang ORDER BY lantai, nama")->fetchAll();
$st = $db->prepare("SELECT * FROM peminjaman WHERE tanggal = ? AND status <> 'Ditolak' ORDER BY jam_mulai");
$st->execute([$tanggal]);
$jadwal = [];                                                     // dikelompokkan per ruang: $jadwal[ruang_id] = [...]
foreach ($st->fetchAll() as $p) {
    $jadwal[$p["ruang_id"]][] = $p;
}

// 3. Angka ringkasan.
$menunggu = $db->query("SELECT COUNT(*) FROM peminjaman WHERE status = 'Menunggu'")->fetchColumn();
$disetujuiHariIni = 0;
$menitTerpakai = 0;
foreach ($jadwal as $daftar) {
    foreach ($daftar as $p) {
        if ($p["status"] === "Disetujui") {
            $disetujuiHariIni++;
            $menitTerpakai += (strtotime($p["jam_selesai"]) - strtotime($p["jam_mulai"])) / 60;  // selisih detik / 60 = menit
        }
    }
}

// 4. Fungsi posisi balok: ubah jam menjadi persen dari lebar lintasan.
$menitBuka  = menitDari(JAM_BUKA);                                // 07:30 = 450 menit sejak tengah malam
$menitTutup = menitDari(JAM_TUTUP);                               // 17:00 = 1020 menit
function menitDari($waktu) { [$j, $m] = explode(":", $waktu); return $j * 60 + $m; }  // "09:30" -> 570
function persen($waktu, $buka, $tutup) { return max(0, min(100, (menitDari($waktu) - $buka) / ($tutup - $buka) * 100)); }

$judul = "Jadwal";
$aktif = "index.php";
require "inc/header.php";
?>
<style>
  /* Gaya khusus halaman jadwal */
  .lintasan { position: relative; height: 54px; background: #f4f7fa; border-radius: 8px; min-width: 520px; }
  .garis-jam { position: absolute; top: 0; bottom: 0; border-left: 1px dashed #cdd6e0; }       /* garis bantu setiap jam */
  .balok { position: absolute; top: 5px; bottom: 5px; border-radius: 6px; padding: 4px 6px; font-size: 12px; color: #fff; text-decoration: none; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; background: var(--utama); }
  .balok.menunggu { background: repeating-linear-gradient(45deg, #c99a2e, #c99a2e 6px, #d8ad48 6px, #d8ad48 12px); }  /* arsir = belum pasti */
  .skala { position: relative; height: 16px; font-size: 11px; color: var(--redup); min-width: 520px; }
  .skala span { position: absolute; transform: translateX(-50%); }                             /* angka jam tepat di atas garisnya */
  .ruang-nama { font-weight: 700; } .ruang-ket { font-size: 12px; color: var(--redup); }
  .navigasi { display: flex; gap: 6px; align-items: flex-end; flex-wrap: wrap; }
</style>

<div class="baris-judul">
  <div>
    <h1>Jadwal Ruang Rapat</h1>
    <p class="sub"><?= tanggalPanjang($tanggal) ?> · jam layanan <?= JAM_BUKA ?>–<?= JAM_TUTUP ?></p>
  </div>
  <form method="get" class="navigasi">
    <a class="tombol sekunder" href="?tanggal=<?= $kemarin ?>">‹ Sebelumnya</a>
    <div class="isian"><input type="date" name="tanggal" value="<?= $tanggal ?>" onchange="this.form.submit()"></div>  <!-- ganti tanggal langsung memuat ulang -->
    <a class="tombol sekunder" href="?tanggal=<?= $besok ?>">Berikutnya ›</a>
    <a class="tombol" href="?">Hari ini</a>
  </form>
</div>

<div class="kartu-baris">
  <div class="kartu"><div class="label">Ruang tersedia</div><div class="nilai"><?= count($daftarRuang) ?></div></div>
  <div class="kartu baik"><div class="label">Disetujui pada tanggal ini</div><div class="nilai"><?= $disetujuiHariIni ?></div>
    <div class="ket">Total <?= round($menitTerpakai / 60, 1) ?> jam pemakaian</div></div>
  <div class="kartu <?= $menunggu ? "waspada" : "" ?>"><div class="label">Pengajuan menunggu (semua tanggal)</div><div class="nilai"><?= $menunggu ?></div>
    <div class="ket"><a href="daftar.php?status=Menunggu">Lihat dan proses</a></div></div>
</div>

<div class="panel">
  <p class="ruang-ket">Balok hijau = disetujui · balok kuning berarsir = menunggu persetujuan. Klik balok untuk melihat rincian di daftar pengajuan.</p>
  <div class="tabel-wadah">
    <table>
      <tr><th style="width:200px">Ruang</th><th>Pemakaian</th></tr>
      <?php foreach ($daftarRuang as $r): ?>                      <!-- satu baris per ruang -->
        <tr>
          <td>
            <div class="ruang-nama"><?= e($r["nama"]) ?></div>
            <div class="ruang-ket">Lt. <?= $r["lantai"] ?> · <?= $r["kapasitas"] ?> orang</div>
            <a class="tombol kecil sekunder" href="ajukan.php?ruang=<?= $r["id"] ?>&tanggal=<?= $tanggal ?>">+ Ajukan</a>
          </td>
          <td>
            <div class="skala"><?php for ($j = 8; $j <= 17; $j++): ?><span style="left: <?= persen(sprintf("%02d:00", $j), $menitBuka, $menitTutup) ?>%"><?= sprintf("%02d", $j) ?></span><?php endfor; ?></div>  <!-- penanda jam 08–17 di posisi yang tepat -->
            <div class="lintasan">
              <?php for ($j = 8; $j <= 17; $j++): ?><div class="garis-jam" style="left: <?= persen(sprintf("%02d:00", $j), $menitBuka, $menitTutup) ?>%"></div><?php endfor; ?>
              <?php foreach ($jadwal[$r["id"]] ?? [] as $p):      // ?? [] : ruang tanpa jadwal menghasilkan daftar kosong
                  $kiri  = persen(jam($p["jam_mulai"]), $menitBuka, $menitTutup);     // posisi awal balok (%)
                  $lebar = persen(jam($p["jam_selesai"]), $menitBuka, $menitTutup) - $kiri;  // panjang balok (%)
              ?>
                <a class="balok <?= $p["status"] === "Menunggu" ? "menunggu" : "" ?>" style="left: <?= $kiri ?>%; width: <?= $lebar ?>%"
                   href="daftar.php?status=<?= $p["status"] ?>&tanggal=<?= $tanggal ?>"
                   title="<?= jam($p["jam_mulai"]) ?>–<?= jam($p["jam_selesai"]) ?> · <?= e($p["kegiatan"]) ?> · <?= e($p["pemohon"]) ?> (<?= $p["status"] ?>)">
                  <?= jam($p["jam_mulai"]) ?>–<?= jam($p["jam_selesai"]) ?> <?= e($p["kegiatan"]) ?>
                </a>
              <?php endforeach; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>

<?php require "inc/footer.php"; ?>
