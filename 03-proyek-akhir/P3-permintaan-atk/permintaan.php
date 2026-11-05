<?php
// ==========================================================================
// permintaan.php — FORMULIR PERMINTAAN (bagian atas) dan DAFTAR PERMINTAAN (bagian bawah).
// Petugas menyerahkan atau menolak permintaan dari daftar ini (diproses oleh proses.php).
// ==========================================================================
require "config.php";

$barang = daftarBarang($db);
$barangById = array_column($barang, null, "id");                  // $barangById[3] = data barang id 3

// ---- A. Proses formulir permintaan baru ----
$data = ["pemohon" => "", "bidang" => "", "barang_id" => "", "jumlah" => "", "keperluan" => ""];
$galat = [];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    foreach ($data as $k => $v) $data[$k] = trim($_POST[$k] ?? "");  // ambil & rapikan semua isian
    $b = $barangById[(int) $data["barang_id"]] ?? null;          // null bila barang tidak dikenal

    if ($data["pemohon"] === "" || mb_strlen($data["pemohon"]) > 60) $galat[] = "Nama pemohon wajib diisi (maks. 60 karakter).";
    if (!in_array($data["bidang"], DAFTAR_BIDANG, true)) $galat[] = "Pilih bidang.";
    if (!$b) $galat[] = "Pilih barang.";
    if (!ctype_digit($data["jumlah"]) || $data["jumlah"] < 1 || $data["jumlah"] > BATAS_PERMINTAAN) {
        $galat[] = "Jumlah harus bilangan bulat 1 sampai " . BATAS_PERMINTAAN . ".";
    }
    if ($data["keperluan"] === "") $galat[] = "Keperluan wajib diisi.";

    if (!$galat) {
        $st = $db->prepare("INSERT INTO permintaan (tanggal, pemohon, bidang, barang_id, jumlah, keperluan) VALUES (CURDATE(), ?, ?, ?, ?, ?)");
        $st->execute([$data["pemohon"], $data["bidang"], $b["id"], $data["jumlah"], mb_substr($data["keperluan"], 0, 150)]);  // CURDATE() = tanggal hari ini menurut server
        $info = $b["stok"] >= $data["jumlah"] ? "" : " Catatan: stok saat ini hanya " . $b["stok"] . " " . $b["satuan"] . ".";
        setPesan("Permintaan " . $data["jumlah"] . " " . $b["satuan"] . " " . $b["nama"] . " tercatat dan menunggu diproses." . $info);
        arahkan("permintaan.php");                                // pola PRG: hindari kirim ganda saat refresh
    }
}

// ---- B. Daftar permintaan dengan filter status ----
$status = $_GET["status"] ?? "Menunggu";
$pilihanStatus = ["Menunggu", "Diserahkan", "Ditolak", "Semua"];
if (!in_array($status, $pilihanStatus, true)) $status = "Menunggu";
$sql = "SELECT p.*, b.kode, b.nama, b.satuan, b.stok FROM permintaan p JOIN barang b ON b.id = p.barang_id"
     . ($status === "Semua" ? "" : " WHERE p.status = ?")
     . " ORDER BY p.tanggal DESC, p.id DESC LIMIT 100";              // batasi 100 baris terbaru
$st = $db->prepare($sql);
$st->execute($status === "Semua" ? [] : [$status]);
$daftar = $st->fetchAll();

$judul = "Permintaan";
$aktif = "permintaan.php";
require "inc/header.php";
?>

<h1>Permintaan ATK</h1>
<p class="sub">Isi formulir untuk meminta barang. Maksimal <?= BATAS_PERMINTAAN ?> satuan per permintaan.</p>

<?php if ($galat): ?>
  <div class="pesan galat"><b>Permintaan belum dapat dikirim:</b><ul><?php foreach ($galat as $g): ?><li><?= e($g) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="post" class="panel">
  <h2>Permintaan Baru</h2>
  <div class="form-grid">
    <div class="isian"><label for="pemohon">Nama pemohon *</label>
      <input id="pemohon" name="pemohon" value="<?= e($data["pemohon"]) ?>" maxlength="60" required></div>
    <div class="isian"><label for="bidang">Bidang *</label>
      <select id="bidang" name="bidang" required><option value="">— pilih —</option>
        <?php foreach (DAFTAR_BIDANG as $bd): ?><option <?= $bd === $data["bidang"] ? "selected" : "" ?>><?= e($bd) ?></option><?php endforeach; ?>
      </select></div>
    <div class="isian"><label for="barang_id">Barang *</label>
      <select id="barang_id" name="barang_id" required><option value="">— pilih —</option>
        <?php foreach ($barang as $b): ?>                         <!-- stok ikut ditampilkan sebagai informasi -->
          <option value="<?= $b["id"] ?>" <?= $b["id"] == $data["barang_id"] ? "selected" : "" ?>><?= e($b["nama"]) ?> — stok <?= $b["stok"] ?> <?= e($b["satuan"]) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="isian"><label for="jumlah">Jumlah *</label>
      <input type="number" id="jumlah" name="jumlah" value="<?= e($data["jumlah"]) ?>" min="1" max="<?= BATAS_PERMINTAAN ?>" required></div>
    <div class="isian" style="grid-column: 1 / -1"><label for="keperluan">Keperluan *</label>
      <input id="keperluan" name="keperluan" value="<?= e($data["keperluan"]) ?>" maxlength="150" required placeholder="misal: penyusunan laporan bulanan"></div>
  </div>
  <p style="margin-top:16px"><button class="tombol" type="submit">Kirim Permintaan</button></p>
</form>

<div class="baris-judul">
  <h2 style="margin:0">Daftar Permintaan</h2>
  <form method="get" class="filter"><div class="isian"><label for="status">Status</label>
    <select id="status" name="status" data-kirim-otomatis>
      <?php foreach ($pilihanStatus as $s): ?><option <?= $s === $status ? "selected" : "" ?>><?= $s ?></option><?php endforeach; ?>
    </select></div></form>
</div>

<div class="tabel-wadah"><table>
  <tr><th>Tanggal</th><th>Pemohon</th><th>Barang</th><th class="n">Jumlah</th><th>Keperluan</th><th>Status</th><th>Tindakan</th></tr>
  <?php foreach ($daftar as $p): ?>
    <tr>
      <td><?= tgl($p["tanggal"]) ?></td>
      <td><?= e($p["pemohon"]) ?><br><small><?= e($p["bidang"]) ?></small></td>
      <td><?= e($p["nama"]) ?><br><small><?= e($p["kode"]) ?> · stok <?= $p["stok"] ?></small></td>
      <td class="n"><?= $p["jumlah"] ?> <?= e($p["satuan"]) ?></td>
      <td><?= e($p["keperluan"]) ?></td>
      <td><?= lencana($p["status"]) ?><?= $p["catatan"] ? "<br><small>" . e($p["catatan"]) . "</small>" : "" ?></td>
      <td>
        <?php if ($p["status"] === "Menunggu"): ?>
          <div class="aksi">
            <form method="post" action="proses.php" data-konfirmasi="Serahkan <?= $p["jumlah"] ?> <?= e($p["satuan"]) ?> <?= e($p["nama"]) ?>? Stok akan berkurang.">
              <input type="hidden" name="id" value="<?= $p["id"] ?>"><input type="hidden" name="aksi" value="serahkan">
              <button class="tombol kecil hijau" type="submit" <?= $p["stok"] < $p["jumlah"] ? "disabled title=\"Stok kurang\" style=\"opacity:.45;cursor:not-allowed\"" : "" ?>>Serahkan</button>  <!-- dinonaktifkan bila stok kurang -->
            </form>
            <form method="post" action="proses.php" data-konfirmasi="Tolak permintaan ini?">
              <input type="hidden" name="id" value="<?= $p["id"] ?>"><input type="hidden" name="aksi" value="tolak">
              <input name="catatan" placeholder="Alasan" required style="padding:5px;width:120px">
              <button class="tombol kecil bahaya" type="submit">Tolak</button>
            </form>
          </div>
        <?php else: ?><small>—</small><?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$daftar): ?><tr><td colspan="7" class="kosong">Tidak ada permintaan dengan status ini.</td></tr><?php endif; ?>
</table></div>

<?php require "inc/footer.php"; ?>
