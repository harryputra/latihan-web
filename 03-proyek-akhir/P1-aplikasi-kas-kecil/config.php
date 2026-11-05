<?php
// ==========================================================================
// config.php — pengaturan bersama seluruh halaman.
// Setiap halaman memanggil berkas ini di baris paling atas: require "config.php";
// Isinya: (1) sesi, (2) koneksi database, (3) daftar pilihan, (4) fungsi bantu.
// ==========================================================================

session_start();                                                  // (1) sesi dipakai untuk menyimpan pesan antarhalaman

// (2) Koneksi database. Sesuaikan "root" dan "" bila pengguna/kata sandi MySQL Anda berbeda.
$db = new PDO(
    "mysql:host=localhost;dbname=pa_kas_kecil;charset=utf8mb4",   // alamat server, nama database, pengodean huruf
    "root",                                                       // nama pengguna MySQL (bawaan XAMPP)
    "",                                                           // kata sandi MySQL (bawaan XAMPP: kosong)
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,              // galat SQL ditampilkan sebagai pesan yang jelas
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // hasil kueri berupa array bernama kolom
    ]
);

// (3) Daftar pilihan. Dipakai untuk mengisi <select> sekaligus memvalidasi isian.
const DAFTAR_BIDANG = ["Keuangan & Umum", "SDM", "Pelayanan Pelanggan", "Jaringan", "Konstruksi"];
const DAFTAR_JENIS  = ["ATK & Cetak", "Konsumsi Rapat", "BBM Kendaraan", "Perlengkapan K3", "Lain-lain"];
const BATAS_TRANSAKSI = 2000000;                                  // batas nilai satu transaksi kas kecil (angka contoh)

// (4) Fungsi bantu — dipanggil di banyak halaman agar kode tidak ditulis berulang.

function e($teks)                                                 // amankan teks sebelum dicetak ke HTML
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, "UTF-8"); // < > " & diubah menjadi kode aman
}

function rp($angka)                                               // format rupiah tanpa desimal
{
    return number_format((float) $angka, 0, ",", ".");            // 1500000 menjadi 1.500.000
}

function tgl($tanggal)                                            // format tanggal Indonesia
{
    return date("d-m-Y", strtotime($tanggal));                    // 2026-10-05 menjadi 05-10-2026
}

function namaBulan($ttttbb)                                       // "2026-10" menjadi "Oktober 2026"
{
    $nama = ["01" => "Januari", "02" => "Februari", "03" => "Maret", "04" => "April", "05" => "Mei", "06" => "Juni",
             "07" => "Juli", "08" => "Agustus", "09" => "September", "10" => "Oktober", "11" => "November", "12" => "Desember"];
    [$tahun, $bulan] = explode("-", $ttttbb);                     // pecah "2026-10" menjadi "2026" dan "10"
    return ($nama[$bulan] ?? $bulan) . " " . $tahun;              // gabungkan nama bulan dan tahun
}

function arahkan($alamat)                                         // pindah ke halaman lain lalu hentikan skrip
{
    header("Location: " . $alamat);                               // perintah ke peramban untuk membuka alamat baru
    exit;                                                         // kode setelah baris ini tidak dijalankan
}

function setPesan($teks, $jenis = "sukses")                       // simpan pesan untuk ditampilkan di halaman berikutnya
{
    $_SESSION["pesan"] = [$teks, $jenis];                         // jenis: "sukses" (hijau) atau "galat" (merah)
}

function ambilPesan()                                             // ambil pesan sekali pakai, lalu hapus
{
    $pesan = $_SESSION["pesan"] ?? null;                          // null jika tidak ada pesan
    unset($_SESSION["pesan"]);                                    // hapus agar tidak muncul lagi saat halaman dimuat ulang
    return $pesan;
}

function bulanValid($teks)                                        // periksa format TTTT-BB, misal 2026-10
{
    return (bool) preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $teks); // true jika formatnya benar
}
