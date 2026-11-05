-- kantor_latihan.sql : database latihan Kantor Unit Contoh (DATA FIKTIF untuk pelatihan)
DROP DATABASE IF EXISTS kantor_latihan;                       -- Hapus database lama (jika ada) agar bisa dipasang ulang
CREATE DATABASE kantor_latihan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;  -- Buat database baru; utf8mb4 mendukung semua huruf
USE kantor_latihan;                                           -- Pilih database yang akan dipakai

CREATE TABLE kas_kecil (                                      -- Tabel buku kas kecil
  id INT AUTO_INCREMENT PRIMARY KEY,                          -- Nomor urut otomatis, kunci utama
  tanggal DATE NOT NULL,                                      -- Tanggal transaksi, wajib
  no_bukti VARCHAR(10) NOT NULL UNIQUE,                       -- Nomor bukti, tidak boleh kembar
  uraian VARCHAR(100) NOT NULL,                               -- Uraian hingga 100 karakter
  bidang VARCHAR(40) NOT NULL,                                -- Nama bidang
  jenis VARCHAR(40) NOT NULL,                                 -- Jenis biaya
  jumlah DECIMAL(12,2) NOT NULL CHECK (jumlah > 0)            -- Angka 2 desimal, harus lebih dari nol
);                                                            -- Akhir definisi tabel

CREATE TABLE anggaran (                                       -- Tabel anggaran per bidang
  id INT AUTO_INCREMENT PRIMARY KEY,                          -- Nomor urut otomatis
  bidang VARCHAR(40) NOT NULL UNIQUE,                         -- Nama bidang, tidak boleh kembar
  anggaran DECIMAL(12,2) NOT NULL,   -- Rp juta, tahun 2026
  realisasi DECIMAL(12,2) NOT NULL   -- Rp juta, s.d. Oktober 2026
);                                                            -- Akhir definisi tabel

INSERT INTO anggaran (bidang, anggaran, realisasi) VALUES     -- Isi tabel anggaran
  ('Keuangan & Umum', 180, 142), ('SDM', 120, 101), ('Pelayanan Pelanggan', 240, 226),  -- Tiga bidang pertama
  ('Jaringan', 310, 248), ('Konstruksi', 260, 209);           -- Dua bidang berikutnya

INSERT INTO kas_kecil (tanggal, no_bukti, uraian, bidang, jenis, jumlah) VALUES  -- Isi tabel kas_kecil (35 transaksi fiktif)
  ('2026-10-01', 'KK-001', 'Galon air minum kantor', 'SDM', 'Lain-lain', 85000),  -- Transaksi fiktif KK-001
  ('2026-10-01', 'KK-002', 'BBM kendaraan dinas', 'Konstruksi', 'BBM Kendaraan', 500000),  -- Transaksi fiktif KK-002
  ('2026-10-02', 'KK-003', 'Tol dan parkir kunjungan lapangan', 'Konstruksi', 'BBM Kendaraan', 150000),  -- Transaksi fiktif KK-003
  ('2026-10-02', 'KK-004', 'Fotokopi dan jilid laporan', 'Konstruksi', 'ATK & Cetak', 200000),  -- Transaksi fiktif KK-004
  ('2026-10-05', 'KK-005', 'Pembelian ATK', 'SDM', 'ATK & Cetak', 525000),  -- Transaksi fiktif KK-005
  ('2026-10-05', 'KK-006', 'Fotokopi dan jilid laporan', 'Jaringan', 'ATK & Cetak', 75000),  -- Transaksi fiktif KK-006
  ('2026-10-06', 'KK-007', 'Pembelian ATK', 'Konstruksi', 'ATK & Cetak', 550000),  -- Transaksi fiktif KK-007
  ('2026-10-06', 'KK-008', 'Tol dan parkir kunjungan lapangan', 'Jaringan', 'BBM Kendaraan', 150000),  -- Transaksi fiktif KK-008
  ('2026-10-07', 'KK-009', 'Cetak spanduk sosialisasi K3', 'Jaringan', 'ATK & Cetak', 325000),  -- Transaksi fiktif KK-009
  ('2026-10-07', 'KK-010', 'Fotokopi dan jilid laporan', 'Keuangan & Umum', 'ATK & Cetak', 200000),  -- Transaksi fiktif KK-010
  ('2026-10-08', 'KK-011', 'Galon air minum kantor', 'Jaringan', 'Lain-lain', 85000),  -- Transaksi fiktif KK-011
  ('2026-10-08', 'KK-012', 'Fotokopi dan jilid laporan', 'SDM', 'ATK & Cetak', 100000),  -- Transaksi fiktif KK-012
  ('2026-10-09', 'KK-013', 'Galon air minum kantor', 'Keuangan & Umum', 'Lain-lain', 85000),  -- Transaksi fiktif KK-013
  ('2026-10-09', 'KK-014', 'Cetak spanduk sosialisasi K3', 'Pelayanan Pelanggan', 'ATK & Cetak', 375000),  -- Transaksi fiktif KK-014
  ('2026-10-12', 'KK-015', 'Cetak spanduk sosialisasi K3', 'SDM', 'ATK & Cetak', 325000),  -- Transaksi fiktif KK-015
  ('2026-10-12', 'KK-016', 'Rompi reflektor', 'Jaringan', 'Perlengkapan K3', 525000),  -- Transaksi fiktif KK-016
  ('2026-10-13', 'KK-017', 'Rompi reflektor', 'Jaringan', 'Perlengkapan K3', 550000),  -- Transaksi fiktif KK-017
  ('2026-10-14', 'KK-018', 'BBM kendaraan dinas', 'SDM', 'BBM Kendaraan', 425000),  -- Transaksi fiktif KK-018
  ('2026-10-15', 'KK-019', 'Pembelian ATK', 'Keuangan & Umum', 'ATK & Cetak', 375000),  -- Transaksi fiktif KK-019
  ('2026-10-16', 'KK-020', 'Sarung tangan dan helm K3', 'Konstruksi', 'Perlengkapan K3', 375000),  -- Transaksi fiktif KK-020
  ('2026-10-19', 'KK-021', 'Sarung tangan dan helm K3', 'Keuangan & Umum', 'Perlengkapan K3', 800000),  -- Transaksi fiktif KK-021
  ('2026-10-20', 'KK-022', 'Pembelian ATK', 'Jaringan', 'ATK & Cetak', 575000),  -- Transaksi fiktif KK-022
  ('2026-10-21', 'KK-023', 'BBM kendaraan dinas', 'Pelayanan Pelanggan', 'BBM Kendaraan', 450000),  -- Transaksi fiktif KK-023
  ('2026-10-22', 'KK-024', 'Tol dan parkir kunjungan lapangan', 'Jaringan', 'BBM Kendaraan', 200000),  -- Transaksi fiktif KK-024
  ('2026-10-23', 'KK-025', 'Galon air minum kantor', 'SDM', 'Lain-lain', 60000),  -- Transaksi fiktif KK-025
  ('2026-10-26', 'KK-026', 'Konsumsi rapat evaluasi bulanan', 'SDM', 'Konsumsi Rapat', 650000),  -- Transaksi fiktif KK-026
  ('2026-10-27', 'KK-027', 'Tol dan parkir kunjungan lapangan', 'Konstruksi', 'BBM Kendaraan', 150000),  -- Transaksi fiktif KK-027
  ('2026-10-28', 'KK-028', 'Cetak spanduk sosialisasi K3', 'Keuangan & Umum', 'ATK & Cetak', 275000),  -- Transaksi fiktif KK-028
  ('2026-10-29', 'KK-029', 'Fotokopi dan jilid laporan', 'SDM', 'ATK & Cetak', 75000),  -- Transaksi fiktif KK-029
  ('2026-10-30', 'KK-030', 'Pembelian ATK', 'Jaringan', 'ATK & Cetak', 300000),  -- Transaksi fiktif KK-030
  ('2026-11-03', 'KK-031', 'Pembelian ATK', 'Keuangan & Umum', 'ATK & Cetak', 450000),  -- Transaksi fiktif KK-031
  ('2026-11-03', 'KK-032', 'Konsumsi rapat koordinasi', 'Pelayanan Pelanggan', 'Konsumsi Rapat', 600000),  -- Transaksi fiktif KK-032
  ('2026-11-04', 'KK-033', 'BBM kendaraan dinas', 'Jaringan', 'BBM Kendaraan', 350000),  -- Transaksi fiktif KK-033
  ('2026-11-05', 'KK-034', 'Fotokopi dan jilid laporan', 'SDM', 'ATK & Cetak', 125000),  -- Transaksi fiktif KK-034
  ('2026-11-06', 'KK-035', 'Sarung tangan dan helm K3', 'Konstruksi', 'Perlengkapan K3', 875000);  -- Transaksi fiktif KK-035 (akhir data)
