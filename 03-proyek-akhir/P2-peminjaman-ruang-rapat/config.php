<?php
// ==========================================================================
// config.php — pengaturan bersama seluruh halaman Aplikasi Peminjaman Ruang Rapat.
// Isinya: (1) sesi, (2) koneksi database, (3) aturan & daftar pilihan, (4) fungsi bantu.
// ==========================================================================

session_start();                                                  // (1) sesi untuk pesan antarhalaman
date_default_timezone_set("Asia/Jakarta");                        // jam server mengikuti WIB

// (2) Koneksi database
$db = new PDO(
    "mysql:host=localhost;dbname=pa_ruang_rapat;charset=utf8mb4", // nama database proyek ini
    "root",                                                       // pengguna MySQL bawaan XAMPP
    "",                                                           // kata sandi bawaan XAMPP (kosong)
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,              // galat SQL tampil jelas
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // hasil berupa array bernama kolom
    ]
);

// (3) Aturan layanan dan daftar pilihan (angka contoh, sesuaikan dengan ketentuan kantor)
const JAM_BUKA  = "07:30";                                        // ruang rapat paling awal dipakai
const JAM_TUTUP = "17:00";                                        // ruang rapat paling akhir dipakai
const DAFTAR_BIDANG = ["Keuangan & Umum", "SDM", "Pelayanan Pelanggan", "Jaringan", "Konstruksi"];

// (4) Fungsi bantu
function e($teks) { return htmlspecialchars((string) $teks, ENT_QUOTES, "UTF-8"); }  // amankan teks untuk HTML
function jam($waktu) { return substr($waktu, 0, 5); }             // "09:00:00" menjadi "09:00"
function arahkan($alamat) { header("Location: " . $alamat); exit; }  // pindah halaman lalu berhenti
function setPesan($teks, $jenis = "sukses") { $_SESSION["pesan"] = [$teks, $jenis]; }  // pesan untuk halaman berikut
function ambilPesan()                                             // ambil pesan sekali pakai
{
    $pesan = $_SESSION["pesan"] ?? null;
    unset($_SESSION["pesan"]);
    return $pesan;
}

function tanggalPanjang($tanggal)                                 // "2026-11-03" menjadi "Selasa, 3 November 2026"
{
    $hari  = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
    $bulan = [1 => "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
    $t = strtotime($tanggal);                                     // ubah teks menjadi cap waktu
    return $hari[date("w", $t)] . ", " . date("j", $t) . " " . $bulan[date("n", $t)] . " " . date("Y", $t);  // w = nomor hari, n = nomor bulan
}

function lencana($status)                                         // label status berwarna
{
    $kelas = ["Menunggu" => "menunggu", "Disetujui" => "setuju", "Ditolak" => "tolak"][$status] ?? "";
    return '<span class="lencana ' . $kelas . '">' . e($status) . '</span>';
}

// Cari peminjaman lain yang BENTROK: ruang & tanggal sama, dan rentang jamnya beririsan.
// Dua rentang beririsan bila: mulai_A < selesai_B DAN selesai_A > mulai_B.
// $kecualiId dipakai agar pengajuan tidak dianggap bentrok dengan dirinya sendiri.
function cariBentrok($db, $ruangId, $tanggal, $mulai, $selesai, $kecualiId = 0)
{
    $st = $db->prepare("SELECT * FROM peminjaman
                        WHERE ruang_id = ? AND tanggal = ? AND status IN ('Menunggu', 'Disetujui')
                          AND jam_mulai < ? AND jam_selesai > ? AND id <> ?
                        ORDER BY jam_mulai LIMIT 1");
    $st->execute([$ruangId, $tanggal, $selesai, $mulai, $kecualiId]);
    return $st->fetch();                                          // false bila tidak ada yang bentrok
}
