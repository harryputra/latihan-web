<?php
// ==========================================================================
// hapus.php — menghapus satu transaksi. Hanya menerima POST (bukan tautan biasa),
// agar data tidak terhapus hanya karena alamat dibuka atau di-bookmark.
// ==========================================================================
require "config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {                      // tolak akses langsung lewat alamat
    arahkan("transaksi.php");
}

$id = (int) ($_POST["id"] ?? 0);                                  // (int) memastikan nilainya angka
$st = $db->prepare("SELECT no_bukti FROM kas_kecil WHERE id = ?");
$st->execute([$id]);
$noBukti = $st->fetchColumn();                                    // false bila data tidak ada

if ($noBukti === false) {
    setPesan("Transaksi tidak ditemukan atau sudah dihapus.", "galat");
} else {
    $db->prepare("DELETE FROM kas_kecil WHERE id = ?")->execute([$id]);  // WHERE wajib: hanya baris ini yang dihapus
    setPesan("Transaksi " . $noBukti . " telah dihapus.");
}
arahkan($_SERVER["HTTP_REFERER"] ?? "transaksi.php");             // kembali ke halaman sebelumnya (filter tetap)
