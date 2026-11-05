<?php
// inc/filter.php — menyusun syarat WHERE dari isian filter (dipakai transaksi.php dan ekspor.php).
// Hasilnya: $filter (nilai isian), $sqlWhere (potongan SQL), $nilaiWhere (nilai untuk tanda ?).

$filter = [
    "bulan"  => $_GET["bulan"] ?? "",                             // contoh: 2026-10, kosong = semua bulan
    "bidang" => $_GET["bidang"] ?? "",                            // kosong = semua bidang
    "kata"   => trim($_GET["kata"] ?? ""),                        // kata kunci uraian / nomor bukti
];

$syarat = [];                                                     // kumpulan syarat, nanti digabung dengan AND
$nilaiWhere = [];                                                 // nilai pengganti tanda ? sesuai urutan syarat

if (bulanValid($filter["bulan"])) {                               // filter bulan hanya dipakai bila formatnya benar
    $syarat[] = "DATE_FORMAT(tanggal, '%Y-%m') = ?";              // ambil bagian tahun-bulan dari tanggal
    $nilaiWhere[] = $filter["bulan"];
}
if (in_array($filter["bidang"], DAFTAR_BIDANG, true)) {           // bidang harus salah satu dari daftar resmi
    $syarat[] = "bidang = ?";
    $nilaiWhere[] = $filter["bidang"];
}
if ($filter["kata"] !== "") {                                     // pencarian kata kunci
    $syarat[] = "(uraian LIKE ? OR no_bukti LIKE ?)";             // cocok di uraian ATAU nomor bukti
    $nilaiWhere[] = "%" . $filter["kata"] . "%";                  // % = boleh diawali/diakhiri teks apa pun
    $nilaiWhere[] = "%" . $filter["kata"] . "%";
}

$sqlWhere = $syarat ? " WHERE " . implode(" AND ", $syarat) : ""; // tanpa syarat = semua data
