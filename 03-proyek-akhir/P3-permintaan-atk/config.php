<?php
// ==========================================================================
// config.php — pengaturan bersama Aplikasi Permintaan & Persediaan ATK.
// Isinya: (1) sesi, (2) koneksi database, (3) daftar pilihan, (4) fungsi bantu.
// ==========================================================================

session_start();                                                  // (1) sesi untuk pesan antarhalaman
date_default_timezone_set("Asia/Jakarta");                        // jam server mengikuti WIB

// (2) Koneksi database
$db = new PDO(
    "mysql:host=localhost;dbname=pa_atk;charset=utf8mb4",         // nama database proyek ini
    "root",                                                       // pengguna MySQL bawaan XAMPP
    "",                                                           // kata sandi bawaan XAMPP (kosong)
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,              // galat SQL tampil jelas
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // hasil berupa array bernama kolom
    ]
);

// (3) Daftar pilihan
const DAFTAR_BIDANG = ["Keuangan & Umum", "SDM", "Pelayanan Pelanggan", "Jaringan", "Konstruksi"];
const DAFTAR_SATUAN = ["rim", "kotak", "pak", "buah", "lusin"];
const BATAS_PERMINTAAN = 20;                                      // maksimal jumlah per satu permintaan (angka contoh)

// (4) Fungsi bantu
function e($teks) { return htmlspecialchars((string) $teks, ENT_QUOTES, "UTF-8"); }  // amankan teks untuk HTML
function rp($angka) { return number_format((float) $angka, 0, ",", "."); }          // 1500000 -> 1.500.000
function tgl($tanggal) { return date("d-m-Y", strtotime($tanggal)); }                // 2026-10-05 -> 05-10-2026
function arahkan($alamat) { header("Location: " . $alamat); exit; }                 // pindah halaman lalu berhenti
function setPesan($teks, $jenis = "sukses") { $_SESSION["pesan"] = [$teks, $jenis]; }
function ambilPesan()
{
    $pesan = $_SESSION["pesan"] ?? null;                          // ambil pesan (jika ada)
    unset($_SESSION["pesan"]);                                    // lalu hapus agar tampil sekali saja
    return $pesan;
}
function lencana($status)                                         // label status berwarna
{
    $kelas = ["Menunggu" => "menunggu", "Diserahkan" => "setuju", "Ditolak" => "tolak"][$status] ?? "";
    return '<span class="lencana ' . $kelas . '">' . e($status) . '</span>';
}
function tanggalValid($teks)                                      // true bila format TTTT-BB-HH dan tanggalnya ada
{
    $t = DateTime::createFromFormat("Y-m-d", $teks);
    return $t && $t->format("Y-m-d") === $teks;
}
function daftarBarang($db)                                        // semua barang, urut kode (untuk pilihan formulir)
{
    return $db->query("SELECT * FROM barang ORDER BY kode")->fetchAll();
}
