# Proyek Akhir 3 — Aplikasi Permintaan & Persediaan ATK

Aplikasi web untuk mengajukan permintaan ATK, menyerahkan barang dengan pengurangan stok otomatis, mencatat barang masuk, memantau barang yang menipis, dan menyusun laporan mutasi stok bulanan. Kebutuhan lengkapnya ada di [`PRD.md`](PRD.md).

**Tingkat kesulitan:** ★★★ (memperkenalkan `JOIN`, subkueri, dan transaksi database) · **Perkiraan waktu:** 3,5–4 jam

## Cara memasang

1. Salin folder `P3-permintaan-atk` ke `C:\xampp\htdocs\`.
2. Nyalakan **Apache** dan **MySQL** di XAMPP Control Panel.
3. phpMyAdmin → **Import** → `database.sql` → **Kirim**. Database `pa_atk` berisi 10 barang, 33 permintaan, dan 6 catatan barang masuk.
4. Buka `http://localhost/P3-permintaan-atk/`.

## Struktur berkas

| Berkas | Fungsi | Konsep yang dilatih |
|---|---|---|
| `config.php` | Koneksi, daftar pilihan, fungsi bantu | Fungsi, konstanta |
| `inc/header.php`, `inc/footer.php` | Kepala & kaki halaman bersama | `include` bersama |
| `index.php` | Dasbor persediaan | Agregasi `SUM(stok * harga)`, Chart.js |
| `permintaan.php` | Formulir + daftar permintaan | Validasi, `JOIN`, filter |
| `proses.php` | Serahkan / tolak | **Transaksi database**, `FOR UPDATE`, `try/catch` |
| `barang.php` | Daftar barang & stok | Pencarian `LIKE`, indikator kondisi |
| `barang-form.php` | Tambah / ubah barang | Mode tambah vs ubah, kode unik |
| `masuk.php` | Barang masuk + riwayat | Transaksi database, JavaScript isi otomatis |
| `laporan.php` | Pemakaian per bidang & mutasi stok | Subkueri, `BETWEEN`, rumus mutasi |
| `assets/` | Gaya, perilaku, pustaka grafik | — |

## Konsep baru: transaksi database

Saat barang diserahkan, **dua** tabel berubah: stok di `barang` berkurang dan status di `permintaan` berganti. Keduanya harus berhasil bersama. Bila salah satu gagal, keduanya dibatalkan.

```php
$db->beginTransaction();   // mulai: perubahan belum permanen
// ... UPDATE barang ...
// ... UPDATE permintaan ...
$db->commit();             // semua berhasil: simpan permanen
// bila terjadi galat: $db->rollBack();  // batalkan semuanya
```

Analogi kantornya: jurnal yang hanya dibukukan bila sisi debit dan kredit sama-sama tercatat.

## Cara menguji

| Uji | Langkah | Hasil yang diharapkan |
|---|---|---|
| Stok kurang | Permintaan → permintaan spidol (3 kotak, stok 2) | Tombol Serahkan redup |
| Serahkan | Serahkan permintaan 6 rim kertas A4 | Stok 42 → 36 |
| Proses ganda | Muat ulang lalu kirim ulang formulir serahkan | Pesan "sudah diproses" |
| Barang masuk | Spidol 6 kotak @ Rp 47.000 | Stok 2 → 8; spidol hilang dari daftar menipis |
| Ubah barang | Ubah salah satu barang | Isian stok tidak dapat diedit |
| Laporan | Laporan → Oktober 2026 | Stok awal + masuk − keluar = stok akhir di setiap baris |

Untuk mengembalikan data ke kondisi awal, impor ulang `database.sql`.

## Tantangan pengembangan (opsional)

1. Tambahkan menu **Stock Opname** untuk mencatat selisih hitung fisik sebagai mutasi penyesuaian.
2. Izinkan satu permintaan berisi **beberapa barang** (tabel `permintaan_rinci`).
3. Tambahkan tombol **Unduh CSV** pada laporan bulanan (lihat `ekspor.php` di Proyek 1).

> Seluruh data dalam aplikasi ini fiktif dan hanya untuk latihan.
