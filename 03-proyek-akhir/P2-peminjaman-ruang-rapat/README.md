# Proyek Akhir 2 — Aplikasi Peminjaman Ruang Rapat

Aplikasi web untuk melihat jadwal ruang rapat secara visual, mengajukan peminjaman dengan pengecekan kapasitas dan jadwal bentrok otomatis, menyetujui atau menolak pengajuan, serta memantau tingkat pemakaian ruang. Kebutuhan lengkapnya ada di [`PRD.md`](PRD.md).

**Tingkat kesulitan:** ★★★ (memperkenalkan `JOIN` dan logika irisan waktu) · **Perkiraan waktu:** 3–4 jam

## Cara memasang

1. Salin folder `P2-peminjaman-ruang-rapat` ke `C:\xampp\htdocs\`.
2. Nyalakan **Apache** dan **MySQL** di XAMPP Control Panel.
3. phpMyAdmin → **Import** → `database.sql` → **Kirim**. Database `pa_ruang_rapat` berisi 4 ruang dan 18 peminjaman contoh (2–13 November 2026).
4. Buka `http://localhost/P2-peminjaman-ruang-rapat/`. Untuk melihat contoh jadwal yang padat, pilih tanggal **3 atau 4 November 2026**.

## Struktur berkas

| Berkas | Fungsi | Konsep yang dilatih |
|---|---|---|
| `config.php` | Koneksi, aturan jam layanan, fungsi bantu, fungsi `cariBentrok()` | Fungsi dengan parameter, logika irisan |
| `inc/header.php`, `inc/footer.php` | Kepala & kaki halaman, angka pengajuan menunggu di menu | `include` bersama |
| `index.php` | Jadwal harian visual | Mengelompokkan data per ruang, menghitung posisi balok (%) |
| `ajukan.php` | Formulir pengajuan | Validasi tanggal/jam/kapasitas, cek bentrok, JavaScript `setCustomValidity` |
| `daftar.php` | Daftar pengajuan + Setujui/Tolak | `JOIN`, filter dinamis |
| `proses.php` | Mengubah status pengajuan | Perubahan status yang terkendali, cek ulang bentrok |
| `ruang.php` | Tingkat pemakaian per ruang | `LEFT JOIN`, `TIMEDIFF`, menghitung hari kerja |
| `assets/gaya.css`, `assets/app.js` | Tampilan dan perilaku bersama | — |

## Konsep baru: JOIN

Tabel `peminjaman` hanya menyimpan `ruang_id` (angka). Untuk menampilkan nama ruang, kedua tabel digabungkan:

```sql
SELECT p.*, r.nama AS nama_ruang
FROM peminjaman p
JOIN ruang r ON r.id = p.ruang_id;
```

Artinya: untuk setiap baris peminjaman (diberi nama pendek `p`), ambil baris ruang (`r`) yang `id`-nya sama dengan `p.ruang_id`. Cara kerjanya mirip `VLOOKUP` di Excel: kolom `ruang_id` menjadi kunci pencarian ke tabel ruang. `LEFT JOIN` di `ruang.php` dipakai agar ruang yang belum pernah dipinjam tetap tampil.

## Logika bentrok dalam satu kalimat

Dua jadwal bentrok bila **mulai A < selesai B** *dan* **selesai A > mulai B**. Coba buktikan dengan contoh 09:00–10:30 vs 10:00–11:00 (bentrok) dan 09:00–10:30 vs 10:30–11:30 (tidak bentrok).

## Cara menguji

| Uji | Langkah | Hasil yang diharapkan |
|---|---|---|
| Bentrok | Ajukan Ruang Rapat Utama, 4 Nov 2026, 10:00–11:00 | Ditolak: bentrok dengan "Rapat koordinasi lapangan" |
| Batas tepat | Ajukan ruang yang sama 10:30–11:30 | Diterima |
| Kapasitas | Ruang Rapat Kecil A, peserta 12 | Peringatan di peramban; ditolak server |
| Akhir pekan | Tanggal Sabtu | Ditolak: hanya hari kerja |
| Proses | Daftar → Setujui salah satu pengajuan | Status berubah, balok jadwal menjadi hijau |

Untuk mengembalikan data ke kondisi awal, impor ulang `database.sql`.

## Tantangan pengembangan (opsional)

1. Saat sebuah pengajuan disetujui, tolak otomatis pengajuan *Menunggu* lain yang bentrok dengannya.
2. Tambahkan status **Dibatalkan** yang dapat dipilih pemohon.
3. Tampilkan **jadwal mingguan** (Senin–Jumat) untuk satu ruang.

> Seluruh data dalam aplikasi ini fiktif dan hanya untuk latihan.
