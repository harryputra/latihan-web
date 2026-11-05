# 03 — Proyek Akhir Hari 2

Tiga studi kasus aplikasi administrasi kantor yang lengkap dan dapat langsung dijalankan di XAMPP. Masing-masing memadukan **HTML, CSS, JavaScript, PHP, dan MySQL**, serta dilengkapi dokumen kebutuhan produk (`PRD.md`) yang dapat dijadikan contoh saat menyusun usulan aplikasi ke tim TI.

| Proyek | Masalah yang diselesaikan | Fitur utama | Konsep baru | Kesulitan |
|---|---|---|---|---|
| **P1 — Aplikasi Kas Kecil** | Rekap kas kecil manual, plafon tidak terpantau | Catat/ubah/hapus, cek plafon, dasbor grafik, rekap silang, ekspor CSV | Pola PRG, ekspor CSV | ★★☆ |
| **P2 — Peminjaman Ruang Rapat** | Jadwal ruang bentrok, ketersediaan tidak terlihat | Jadwal visual, cek bentrok & kapasitas, setujui/tolak, tingkat pemakaian | `JOIN`, logika irisan waktu | ★★★ |
| **P3 — Permintaan & Persediaan ATK** | Stok tidak akurat, barang habis tanpa peringatan | Permintaan, serahkan (stok otomatis), barang masuk, stok menipis, laporan mutasi | Transaksi database, subkueri | ★★★ |

## Cara memakai di kelas

1. **Pilih satu proyek per kelompok** (2–3 orang). P1 cocok untuk kelompok yang ingin mengulang materi; P2 dan P3 untuk yang ingin tantangan lebih.
2. **Baca `PRD.md` lebih dahulu** (15 menit), khususnya Bagian 5 (ruang lingkup), 6 (kebutuhan), dan 11 (kriteria penerimaan).
3. **Jalankan aplikasi jadi** untuk melihat target akhirnya, lalu pelajari kodenya berurutan sesuai `README.md` proyek.
4. **Kerjakan salah satu "Tantangan pengembangan"** di bagian akhir `README.md` proyek sebagai karya kelompok.
5. **Demo** dengan menjalankan tabel uji di `README.md` (setara Bagian 11 PRD).

Ketiga proyek memakai database terpisah (`pa_kas_kecil`, `pa_ruang_rapat`, `pa_atk`) sehingga dapat dipasang bersamaan tanpa saling mengganggu.

## Struktur yang sama di setiap proyek

```
PRD.md           dokumen kebutuhan produk (apa dan mengapa)
README.md        cara memasang, struktur berkas, cara menguji
database.sql     struktur tabel + data contoh fiktif
config.php       koneksi database dan fungsi bantu
inc/             header.php dan footer.php yang dipakai bersama
assets/          gaya.css, app.js, chart.umd.min.js
*.php            satu berkas per halaman
```
