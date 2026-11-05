<?php
// ==========================================================================
// ekspor.php — mengunduh daftar transaksi (sesuai filter) sebagai berkas CSV
// yang langsung dapat dibuka di Microsoft Excel.
// ==========================================================================
require "config.php";
require "inc/filter.php";                                         // filter yang sama dengan transaksi.php

$st = $db->prepare("SELECT tanggal, no_bukti, uraian, bidang, jenis, jumlah FROM kas_kecil" . $sqlWhere . " ORDER BY tanggal, id");
$st->execute($nilaiWhere);

$namaBerkas = "kas-kecil-" . ($filter["bulan"] ?: "semua") . ".csv";  // contoh: kas-kecil-2026-10.csv
header("Content-Type: text/csv; charset=UTF-8");                  // beri tahu peramban bahwa ini berkas CSV
header("Content-Disposition: attachment; filename=\"$namaBerkas\"");  // paksa diunduh, bukan ditampilkan

$keluar = fopen("php://output", "w");                             // tulis langsung ke respons
fwrite($keluar, "\xEF\xBB\xBF");                                  // penanda UTF-8 (BOM) agar huruf tampil benar di Excel
fputcsv($keluar, ["Tanggal", "No. Bukti", "Uraian", "Bidang", "Jenis", "Jumlah"], ";");  // baris judul; pemisah titik koma (Excel Indonesia)
while ($b = $st->fetch()) {                                       // ambil baris satu per satu (hemat memori)
    fputcsv($keluar, [tgl($b["tanggal"]), $b["no_bukti"], $b["uraian"], $b["bidang"], $b["jenis"], (int) $b["jumlah"]], ";");
}
fclose($keluar);
