<?php
// ==========================================================================
// masuk.php — MENCATAT BARANG MASUK (pembelian/pengisian ulang) dan riwayatnya.
// Pencatatan memakai transaksi: simpan riwayat + tambah stok + perbarui harga terakhir.
// ==========================================================================
require "config.php";

$barang = daftarBarang($db);
$barangById = array_column($barang, null, "id");
$data = ["tanggal" => date("Y-m-d"), "barang_id" => (string) ($_GET["barang"] ?? ""), "jumlah" => "", "harga_satuan" => "", "keterangan" => ""];
$galat = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    foreach ($data as $k => $v) $data[$k] = trim($_POST[$k] ?? "");
    $b = $barangById[(int) $data["barang_id"]] ?? null;

    if (!tanggalValid($data["tanggal"]) || $data["tanggal"] > date("Y-m-d")) $galat[] = "Tanggal tidak valid atau melewati hari ini.";
    if (!$b) $galat[] = "Pilih barang.";
    if (!ctype_digit($data["jumlah"]) || $data["jumlah"] < 1) $galat[] = "Jumlah minimal 1.";
    if (!is_numeric($data["harga_satuan"]) || $data["harga_satuan"] < 0) $galat[] = "Harga satuan harus angka 0 atau lebih.";

    if (!$galat) {
        try {
            $db->beginTransaction();                              // tiga perubahan berikut harus berhasil bersama
            $st = $db->prepare("INSERT INTO barang_masuk (tanggal, barang_id, jumlah, harga_satuan, keterangan) VALUES (?, ?, ?, ?, ?)");
            $st->execute([$data["tanggal"], $b["id"], $data["jumlah"], $data["harga_satuan"], mb_substr($data["keterangan"], 0, 150)]);
            $st = $db->prepare("UPDATE barang SET stok = stok + ?, harga = ? WHERE id = ?");  // tambah stok & simpan harga terbaru
            $st->execute([$data["jumlah"], $data["harga_satuan"], $b["id"]]);
            $db->commit();                                        // simpan permanen
            setPesan("Stok " . $b["nama"] . " bertambah " . $data["jumlah"] . " " . $b["satuan"] . " menjadi " . ($b["stok"] + $data["jumlah"]) . ".");
            arahkan("masuk.php");
        } catch (Exception $ex) {
            $db->rollBack();                                      // batalkan semua bila ada yang gagal
            $galat[] = "Gagal menyimpan: " . $ex->getMessage();
        }
    }
}

$riwayat = $db->query("SELECT m.*, b.kode, b.nama, b.satuan FROM barang_masuk m JOIN barang b ON b.id = m.barang_id
                       ORDER BY m.tanggal DESC, m.id DESC LIMIT 15")->fetchAll();  // 15 catatan terbaru

$judul = "Barang Masuk";
$aktif = "masuk.php";
require "inc/header.php";
?>

<h1>Barang Masuk</h1>
<p class="sub">Catat setiap barang yang diterima. Stok dan harga satuan terakhir diperbarui otomatis.</p>

<?php if ($galat): ?>
  <div class="pesan galat"><b>Data belum dapat disimpan:</b><ul><?php foreach ($galat as $g): ?><li><?= e($g) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="post" class="panel">
  <div class="form-grid">
    <div class="isian"><label for="tanggal">Tanggal terima *</label><input type="date" id="tanggal" name="tanggal" value="<?= e($data["tanggal"]) ?>" max="<?= date("Y-m-d") ?>" required></div>
    <div class="isian" style="grid-column: span 2"><label for="barang_id">Barang *</label>
      <select id="barang_id" name="barang_id" required><option value="">— pilih —</option>
        <?php foreach ($barang as $b): ?>                         <!-- data-harga dipakai JavaScript untuk mengisi harga otomatis -->
          <option value="<?= $b["id"] ?>" data-harga="<?= (float) $b["harga"] ?>" <?= (string) $b["id"] === $data["barang_id"] ? "selected" : "" ?>>
            <?= e($b["kode"]) ?> · <?= e($b["nama"]) ?> (stok <?= $b["stok"] ?> <?= e($b["satuan"]) ?>)</option>
        <?php endforeach; ?>
      </select></div>
    <div class="isian"><label for="jumlah">Jumlah *</label><input type="number" id="jumlah" name="jumlah" value="<?= e($data["jumlah"]) ?>" min="1" required></div>
    <div class="isian"><label for="harga_satuan">Harga satuan (Rp) *</label><input type="number" id="harga_satuan" name="harga_satuan" value="<?= e($data["harga_satuan"]) ?>" min="0" required data-rupiah></div>
    <div class="isian" style="grid-column: span 2"><label for="keterangan">Keterangan</label><input id="keterangan" name="keterangan" value="<?= e($data["keterangan"]) ?>" maxlength="150" placeholder="misal: pengadaan awal bulan"></div>
  </div>
  <p style="margin-top:16px"><button class="tombol" type="submit">Simpan</button> <b id="totalMasuk" style="margin-left:12px"></b></p>  <!-- total = jumlah x harga, dihitung JavaScript -->
</form>

<div class="panel"><h2>Riwayat Terbaru</h2>
<div class="tabel-wadah"><table>
  <tr><th>Tanggal</th><th>Barang</th><th class="n">Jumlah</th><th class="n">Harga (Rp)</th><th class="n">Nilai (Rp)</th><th>Keterangan</th></tr>
  <?php foreach ($riwayat as $m): ?>
    <tr><td><?= tgl($m["tanggal"]) ?></td><td><?= e($m["nama"]) ?><br><small><?= e($m["kode"]) ?></small></td>
        <td class="n"><?= $m["jumlah"] ?> <?= e($m["satuan"]) ?></td><td class="n"><?= rp($m["harga_satuan"]) ?></td>
        <td class="n"><?= rp($m["jumlah"] * $m["harga_satuan"]) ?></td><td><?= e($m["keterangan"]) ?></td></tr>
  <?php endforeach; ?>
</table></div></div>

<script>
  const pilih = document.getElementById("barang_id");
  const jumlah = document.getElementById("jumlah");
  const harga = document.getElementById("harga_satuan");
  pilih.addEventListener("change", function () {                   // saat barang dipilih...
    const h = pilih.selectedOptions[0].dataset.harga;               // ...ambil harga terakhirnya
    if (h && !harga.value) { harga.value = h; harga.dispatchEvent(new Event("input")); }  // isi bila masih kosong
    hitungTotal();
  });
  function hitungTotal() {                                          // tampilkan perkiraan nilai pembelian
    const t = Number(jumlah.value) * Number(harga.value);
    document.getElementById("totalMasuk").textContent = t > 0 ? "Nilai: Rp " + t.toLocaleString("id-ID") : "";
  }
  [jumlah, harga].forEach(el => el.addEventListener("input", hitungTotal));
</script>

<?php require "inc/footer.php"; ?>
