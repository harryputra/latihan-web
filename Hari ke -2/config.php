<?php
// config.php — pengaturan koneksi database (bawaan XAMPP: user root tanpa kata sandi)
// Berkas murni PHP, jadi tidak perlu tanda penutup PHP di akhir

// Buat koneksi MySQL dengan PDO; hasilnya tersimpan di variabel $db
$db = new PDO(
    "mysql:host=localhost;dbname=kantor_latihan;charset=utf8mb4",  // server, nama database, pengodean huruf
    "root",  // pengguna MySQL bawaan XAMPP
    "",      // kata sandi kosong (bawaan XAMPP)
    [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // galat ditampilkan jelas
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // hasil kueri = array bernama kolom
    ]
);

// Fungsi bantu: rp(1500000) menghasilkan "1.500.000"
function rp($n)
{
    return number_format($n, 0, ",", ".");
}
