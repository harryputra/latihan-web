<?php
// ==========================================================================
// proses.php — MENYETUJUI atau MENOLAK pengajuan. Hanya menerima POST.
// Sebelum menyetujui, sistem memastikan tidak ada pengajuan lain yang sudah disetujui pada jam yang beririsan.
// ==========================================================================
require "config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") arahkan("daftar.php"); // tolak akses langsung

$id      = (int) ($_POST["id"] ?? 0);
$aksi    = $_POST["aksi"] ?? "";
$catatan = trim($_POST["catatan"] ?? "");
$kembali = $_SERVER["HTTP_REFERER"] ?? "daftar.php";              // halaman asal, agar filter tetap

$st = $db->prepare("SELECT * FROM peminjaman WHERE id = ?");
$st->execute([$id]);
$p = $st->fetch();

if (!$p || $p["status"] !== "Menunggu") {                         // hanya status Menunggu yang boleh diproses
    setPesan("Pengajuan tidak ditemukan atau sudah diproses.", "galat");
    arahkan($kembali);
}

if ($aksi === "setujui") {
    // Cek irisan jam KHUSUS dengan peminjaman yang sudah disetujui (kecuali dirinya sendiri).
    $st = $db->prepare("SELECT kegiatan FROM peminjaman WHERE ruang_id = ? AND tanggal = ? AND status = 'Disetujui'
                        AND jam_mulai < ? AND jam_selesai > ? AND id <> ? LIMIT 1");
    $st->execute([$p["ruang_id"], $p["tanggal"], $p["jam_selesai"], $p["jam_mulai"], $id]);
    $bentrok = $st->fetchColumn();
    if ($bentrok) {
        setPesan("Tidak dapat disetujui: bentrok dengan \"$bentrok\" yang sudah disetujui.", "galat");
    } else {
        $db->prepare("UPDATE peminjaman SET status = 'Disetujui' WHERE id = ?")->execute([$id]);
        setPesan("Pengajuan \"" . $p["kegiatan"] . "\" disetujui.");
    }
} elseif ($aksi === "tolak") {
    if ($catatan === "") {
        setPesan("Alasan penolakan wajib diisi.", "galat");
    } else {
        $db->prepare("UPDATE peminjaman SET status = 'Ditolak', catatan = ? WHERE id = ?")->execute([mb_substr($catatan, 0, 200), $id]);
        setPesan("Pengajuan \"" . $p["kegiatan"] . "\" ditolak.");
    }
} else {
    setPesan("Tindakan tidak dikenal.", "galat");
}
arahkan($kembali);
