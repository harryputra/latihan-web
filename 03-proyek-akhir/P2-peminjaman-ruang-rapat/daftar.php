<?php
// ==========================================================================
// daftar.php — DAFTAR PENGAJUAN dengan filter status & tanggal, serta tombol Setujui / Tolak.
// Memakai JOIN untuk mengambil nama ruang dari tabel ruang.
// ==========================================================================
require "config.php";

$status  = $_GET["status"] ?? "Menunggu";                         // bawaan: yang perlu diproses
$tanggal = $_GET["tanggal"] ?? "";                                // kosong = semua tanggal
$daftarStatus = ["Menunggu", "Disetujui", "Ditolak", "Semua"];
if (!in_array($status, $daftarStatus, true)) $status = "Menunggu";

$syarat = [];
$nilai = [];
if ($status !== "Semua") { $syarat[] = "p.status = ?"; $nilai[] = $status; }
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) { $syarat[] = "p.tanggal = ?"; $nilai[] = $tanggal; }

// JOIN: gabungkan baris peminjaman (alias p) dengan ruang (alias r) yang id-nya sama dengan p.ruang_id.
$sql = "SELECT p.*, r.nama AS nama_ruang, r.kapasitas
        FROM peminjaman p JOIN ruang r ON r.id = p.ruang_id"
     . ($syarat ? " WHERE " . implode(" AND ", $syarat) : "")
     . " ORDER BY p.tanggal, p.jam_mulai";
$st = $db->prepare($sql);
$st->execute($nilai);
$data = $st->fetchAll();

$judul = "Daftar Pengajuan";
$aktif = "daftar.php";
require "inc/header.php";
?>

<div class="baris-judul">
  <div>
    <h1>Daftar Pengajuan</h1>
    <p class="sub"><?= count($data) ?> pengajuan · status <?= e($status) ?><?= $tanggal ? " · " . tanggalPanjang($tanggal) : "" ?></p>
  </div>
  <form method="get" class="filter">
    <div class="isian">
      <label for="status">Status</label>
      <select id="status" name="status" data-kirim-otomatis>
        <?php foreach ($daftarStatus as $s): ?><option <?= $s === $status ? "selected" : "" ?>><?= $s ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="isian">
      <label for="tanggal">Tanggal</label>
      <input type="date" id="tanggal" name="tanggal" value="<?= e($tanggal) ?>" onchange="this.form.submit()">
    </div>
    <a class="tombol sekunder" href="daftar.php?status=Semua">Semua data</a>
  </form>
</div>

<div class="tabel-wadah">
  <table>
    <tr><th>Tanggal &amp; jam</th><th>Ruang</th><th>Kegiatan</th><th>Pemohon</th><th class="n">Peserta</th><th>Status</th><th>Tindakan</th></tr>
    <?php foreach ($data as $p): ?>
      <tr>
        <td><?= date("d-m-Y", strtotime($p["tanggal"])) ?><br><small><?= jam($p["jam_mulai"]) ?>–<?= jam($p["jam_selesai"]) ?></small></td>
        <td><?= e($p["nama_ruang"]) ?></td>
        <td><?= e($p["kegiatan"]) ?></td>
        <td><?= e($p["pemohon"]) ?><br><small><?= e($p["bidang"]) ?></small></td>
        <td class="n"><?= $p["peserta"] ?> / <?= $p["kapasitas"] ?></td>   <!-- peserta / kapasitas ruang -->
        <td><?= lencana($p["status"]) ?><?= $p["catatan"] ? "<br><small>" . e($p["catatan"]) . "</small>" : "" ?></td>
        <td>
          <?php if ($p["status"] === "Menunggu"): ?>              <!-- tombol hanya untuk pengajuan yang belum diproses -->
            <div class="aksi">
              <form method="post" action="proses.php">
                <input type="hidden" name="id" value="<?= $p["id"] ?>">
                <input type="hidden" name="aksi" value="setujui">
                <button class="tombol kecil hijau" type="submit">Setujui</button>
              </form>
              <form method="post" action="proses.php" data-konfirmasi="Tolak pengajuan ini?">
                <input type="hidden" name="id" value="<?= $p["id"] ?>">
                <input type="hidden" name="aksi" value="tolak">
                <input name="catatan" placeholder="Alasan penolakan" required style="padding:5px;width:150px">  <!-- alasan wajib -->
                <button class="tombol kecil bahaya" type="submit">Tolak</button>
              </form>
            </div>
          <?php else: ?>
            <small>Selesai diproses</small>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$data): ?><tr><td colspan="7" class="kosong">Tidak ada pengajuan dengan filter ini.</td></tr><?php endif; ?>
  </table>
</div>

<?php require "inc/footer.php"; ?>
