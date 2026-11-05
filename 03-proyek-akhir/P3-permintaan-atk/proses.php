<?php
// ==========================================================================
// proses.php — MENYERAHKAN atau MENOLAK permintaan. Hanya menerima POST.
// Penyerahan memakai TRANSAKSI DATABASE: pengurangan stok dan perubahan status
// berhasil bersama-sama, atau dibatalkan bersama-sama bila ada yang gagal.
// ==========================================================================
require "config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") arahkan("permintaan.php");

$id      = (int) ($_POST["id"] ?? 0);
$aksi    = $_POST["aksi"] ?? "";
$catatan = trim($_POST["catatan"] ?? "");
$kembali = $_SERVER["HTTP_REFERER"] ?? "permintaan.php";

try {
    $db->beginTransaction();                                      // mulai transaksi: perubahan belum permanen

    // FOR UPDATE mengunci baris agar dua petugas tidak memproses permintaan yang sama bersamaan.
    $st = $db->prepare("SELECT p.*, b.nama, b.satuan, b.stok FROM permintaan p JOIN barang b ON b.id = p.barang_id
                        WHERE p.id = ? FOR UPDATE");
    $st->execute([$id]);
    $p = $st->fetch();

    if (!$p || $p["status"] !== "Menunggu") {
        throw new Exception("Permintaan tidak ditemukan atau sudah diproses.");  // lompat ke bagian catch
    }

    if ($aksi === "serahkan") {
        if ($p["stok"] < $p["jumlah"]) {
            throw new Exception("Stok " . $p["nama"] . " hanya " . $p["stok"] . " " . $p["satuan"] . "; tidak cukup untuk " . $p["jumlah"] . ".");
        }
        $db->prepare("UPDATE barang SET stok = stok - ? WHERE id = ?")->execute([$p["jumlah"], $p["barang_id"]]);  // kurangi stok
        $db->prepare("UPDATE permintaan SET status = 'Diserahkan' WHERE id = ?")->execute([$id]);                 // ubah status
        $pesan = "Diserahkan: " . $p["jumlah"] . " " . $p["satuan"] . " " . $p["nama"] . " kepada " . $p["pemohon"] . ".";
    } elseif ($aksi === "tolak") {
        if ($catatan === "") throw new Exception("Alasan penolakan wajib diisi.");
        $db->prepare("UPDATE permintaan SET status = 'Ditolak', catatan = ? WHERE id = ?")->execute([mb_substr($catatan, 0, 200), $id]);
        $pesan = "Permintaan dari " . $p["pemohon"] . " ditolak.";
    } else {
        throw new Exception("Tindakan tidak dikenal.");
    }

    $db->commit();                                                // semua langkah berhasil: simpan permanen
    setPesan($pesan);
} catch (Exception $galat) {
    if ($db->inTransaction()) $db->rollBack();                    // ada yang gagal: batalkan semua perubahan
    setPesan($galat->getMessage(), "galat");
}
arahkan($kembali);
