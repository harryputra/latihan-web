# Proyek Akhir 1 — Aplikasi Kas Kecil

Aplikasi web untuk mencatat pengeluaran kas kecil, memantau sisa plafon bulanan, dan menyusun rekap per bidang × jenis biaya. Kebutuhan lengkapnya ada di [`PRD.md`](PRD.md).

**Tingkat kesulitan:** ★★☆ (paling dekat dengan latihan Hari 2) · **Perkiraan waktu:** 3–4 jam

## Cara memasang

1. Salin folder `P1-aplikasi-kas-kecil` ke `C:\xampp\htdocs\`.
2. Nyalakan **Apache** dan **MySQL** di XAMPP Control Panel.
3. Buka `http://localhost/phpmyadmin` → tab **Import** → pilih `database.sql` → **Kirim**. Database `pa_kas_kecil` beserta 51 transaksi contoh akan terbentuk.
4. Buka `http://localhost/P1-aplikasi-kas-kecil/`.

Jika MySQL Anda memakai kata sandi, ubah baris koneksi di `config.php`.

## Struktur berkas

| Berkas | Fungsi | Konsep yang dilatih |
|---|---|---|
| `config.php` | Koneksi database, daftar pilihan, fungsi bantu | `require`, PDO, fungsi, konstanta |
| `inc/header.php`, `inc/footer.php` | Kepala, menu, dan kaki halaman bersama | Memecah tampilan agar tidak ditulis berulang |
| `inc/filter.php` | Menyusun syarat `WHERE` dari isian filter | Kueri dinamis yang tetap aman |
| `index.php` | Dasbor: kartu ringkasan + grafik | `GROUP BY`, `json_encode`, Chart.js |
| `transaksi.php` | Daftar + filter + total | `LIKE`, `array_sum`, formulir GET |
| `form.php` | Catat baru dan ubah transaksi | Validasi server, cek plafon, `INSERT`/`UPDATE`, pola PRG |
| `hapus.php` | Hapus transaksi (POST saja) | `DELETE ... WHERE`, konfirmasi JavaScript |
| `rekap.php` | Rekap silang bidang × jenis, siap cetak | Menyusun array dua dimensi, `@media print` |
| `ekspor.php` | Unduh CSV untuk Excel | `header()`, `fputcsv` |
| `assets/gaya.css` | Tampilan seluruh halaman | Variabel CSS, grid, media query |
| `assets/app.js` | Konfirmasi, pratinjau rupiah, pesan memudar | Event, `dataset`, `toLocaleString` |
| `assets/chart.umd.min.js` | Pustaka grafik (lokal, tanpa internet) | — |

## Urutan membaca kode yang disarankan

`config.php` → `inc/header.php` → `transaksi.php` → `form.php` → `hapus.php` → `index.php` → `rekap.php` → `ekspor.php`.
Setiap baris penting diberi komentar berbahasa Indonesia.

## Cara menguji (ringkas dari PRD Bagian 11)

| Uji | Langkah | Hasil yang diharapkan |
|---|---|---|
| Plafon | Catat transaksi Oktober 2026 Rp 600.000 | Ditolak: sisa plafon Rp 485.000 |
| Nomor ganda | Catat dengan nomor KK-001 | Ditolak: nomor sudah dipakai |
| Filter | Transaksi → Oktober 2026 + Jaringan | 9 transaksi, total Rp 2.785.000 |
| Dasbor | Pilih Oktober 2026 | Terpakai 95,2% (kartu merah) |
| Rekap | Rekap Bulanan → Oktober 2026 | Total Rp 9.515.000 |
| Keamanan | Simpan uraian `<b>uji</b>` | Tampil sebagai teks, tidak menjadi tebal |

## Tantangan pengembangan (opsional)

1. Tambahkan halaman **Pengaturan Plafon** untuk menambah/mengubah plafon per bulan.
2. Tambahkan kolom **keterangan penerima** dan tampilkan di daftar.
3. Tampilkan grafik garis **tren 6 bulan terakhir** di dasbor.

> Seluruh data dalam aplikasi ini fiktif. Gunakan aplikasi latihan ini hanya dengan data fiktif; data perusahaan diolah di sistem resmi sesuai kebijakan TI.
