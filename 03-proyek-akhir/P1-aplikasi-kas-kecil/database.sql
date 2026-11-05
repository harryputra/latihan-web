-- ==========================================================================
-- database.sql — struktur dan data contoh Aplikasi Kas Kecil
-- Cara memakai: phpMyAdmin > tab Import > pilih berkas ini > Kirim (Go).
-- Seluruh data bersifat FIKTIF untuk keperluan pelatihan.
-- ==========================================================================

DROP DATABASE IF EXISTS pa_kas_kecil;                         -- hapus versi lama agar bisa dipasang ulang
CREATE DATABASE pa_kas_kecil CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;  -- buat database baru
USE pa_kas_kecil;                                             -- pakai database tersebut

-- Tabel 1: plafon (batas dana) kas kecil per bulan
CREATE TABLE plafon (
  bulan CHAR(7) PRIMARY KEY,                                  -- format TTTT-BB, misal 2026-10
  nilai DECIMAL(14,2) NOT NULL CHECK (nilai > 0)              -- plafon dalam rupiah, wajib lebih dari nol
);

-- Tabel 2: transaksi pengeluaran kas kecil
CREATE TABLE kas_kecil (
  id INT AUTO_INCREMENT PRIMARY KEY,                          -- nomor urut otomatis
  tanggal DATE NOT NULL,                                      -- tanggal transaksi
  no_bukti VARCHAR(10) NOT NULL UNIQUE,                       -- nomor bukti, tidak boleh kembar
  uraian VARCHAR(100) NOT NULL,                               -- keterangan pengeluaran
  bidang VARCHAR(40) NOT NULL,                                -- bidang pemakai
  jenis VARCHAR(40) NOT NULL,                                 -- jenis biaya
  jumlah DECIMAL(12,2) NOT NULL CHECK (jumlah > 0),           -- nilai rupiah, wajib lebih dari nol
  dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,            -- waktu pencatatan (otomatis)
  INDEX idx_tanggal (tanggal)                                 -- indeks agar pencarian per bulan cepat
);

-- Data plafon contoh: Rp 10 juta per bulan
INSERT INTO plafon (bulan, nilai) VALUES
  ('2026-09', 10000000), ('2026-10', 10000000), ('2026-11', 10000000);

-- Data transaksi contoh (September s.d. awal November 2026)
INSERT INTO kas_kecil (tanggal, no_bukti, uraian, bidang, jenis, jumlah) VALUES
  ('2026-09-02', 'KK-S01', 'Pembelian ATK', 'Keuangan & Umum', 'ATK & Cetak', 400000),
  ('2026-09-03', 'KK-S02', 'BBM kendaraan dinas', 'Jaringan', 'BBM Kendaraan', 450000),
  ('2026-09-07', 'KK-S03', 'Konsumsi rapat koordinasi', 'SDM', 'Konsumsi Rapat', 550000),
  ('2026-09-09', 'KK-S04', 'Fotokopi dan jilid laporan', 'Pelayanan Pelanggan', 'ATK & Cetak', 150000),
  ('2026-09-11', 'KK-S05', 'Sepatu keselamatan', 'Konstruksi', 'Perlengkapan K3', 900000),
  ('2026-09-15', 'KK-S06', 'Galon air minum kantor', 'Keuangan & Umum', 'Lain-lain', 90000),
  ('2026-09-17', 'KK-S07', 'Tol dan parkir kunjungan lapangan', 'Jaringan', 'BBM Kendaraan', 175000),
  ('2026-09-21', 'KK-S08', 'Pembelian ATK', 'SDM', 'ATK & Cetak', 350000),
  ('2026-09-24', 'KK-S09', 'BBM kendaraan dinas', 'Konstruksi', 'BBM Kendaraan', 500000),
  ('2026-09-28', 'KK-S10', 'Cetak spanduk kegiatan', 'Pelayanan Pelanggan', 'ATK & Cetak', 650000),
  ('2026-09-30', 'KK-S11', 'Sarung tangan kerja', 'Jaringan', 'Perlengkapan K3', 420000),
('2026-10-01', 'KK-001', 'Galon air minum kantor', 'SDM', 'Lain-lain', 85000),
('2026-10-01', 'KK-002', 'BBM kendaraan dinas', 'Konstruksi', 'BBM Kendaraan', 500000),
('2026-10-02', 'KK-003', 'Tol dan parkir kunjungan lapangan', 'Konstruksi', 'BBM Kendaraan', 150000),
('2026-10-02', 'KK-004', 'Fotokopi dan jilid laporan', 'Konstruksi', 'ATK & Cetak', 200000),
('2026-10-05', 'KK-005', 'Pembelian ATK', 'SDM', 'ATK & Cetak', 525000),
('2026-10-05', 'KK-006', 'Fotokopi dan jilid laporan', 'Jaringan', 'ATK & Cetak', 75000),
('2026-10-06', 'KK-007', 'Pembelian ATK', 'Konstruksi', 'ATK & Cetak', 550000),
('2026-10-06', 'KK-008', 'Tol dan parkir kunjungan lapangan', 'Jaringan', 'BBM Kendaraan', 150000),
('2026-10-07', 'KK-009', 'Cetak spanduk sosialisasi K3', 'Jaringan', 'ATK & Cetak', 325000),
('2026-10-07', 'KK-010', 'Fotokopi dan jilid laporan', 'Keuangan & Umum', 'ATK & Cetak', 200000),
('2026-10-08', 'KK-011', 'Galon air minum kantor', 'Jaringan', 'Lain-lain', 85000),
('2026-10-08', 'KK-012', 'Fotokopi dan jilid laporan', 'SDM', 'ATK & Cetak', 100000),
('2026-10-09', 'KK-013', 'Galon air minum kantor', 'Keuangan & Umum', 'Lain-lain', 85000),
('2026-10-09', 'KK-014', 'Cetak spanduk sosialisasi K3', 'Pelayanan Pelanggan', 'ATK & Cetak', 375000),
('2026-10-12', 'KK-015', 'Cetak spanduk sosialisasi K3', 'SDM', 'ATK & Cetak', 325000),
('2026-10-12', 'KK-016', 'Rompi reflektor', 'Jaringan', 'Perlengkapan K3', 525000),
('2026-10-13', 'KK-017', 'Rompi reflektor', 'Jaringan', 'Perlengkapan K3', 550000),
('2026-10-14', 'KK-018', 'BBM kendaraan dinas', 'SDM', 'BBM Kendaraan', 425000),
('2026-10-15', 'KK-019', 'Pembelian ATK', 'Keuangan & Umum', 'ATK & Cetak', 375000),
('2026-10-16', 'KK-020', 'Sarung tangan dan helm K3', 'Konstruksi', 'Perlengkapan K3', 375000),
('2026-10-19', 'KK-021', 'Sarung tangan dan helm K3', 'Keuangan & Umum', 'Perlengkapan K3', 800000),
('2026-10-20', 'KK-022', 'Pembelian ATK', 'Jaringan', 'ATK & Cetak', 575000),
('2026-10-21', 'KK-023', 'BBM kendaraan dinas', 'Pelayanan Pelanggan', 'BBM Kendaraan', 450000),
('2026-10-22', 'KK-024', 'Tol dan parkir kunjungan lapangan', 'Jaringan', 'BBM Kendaraan', 200000),
('2026-10-23', 'KK-025', 'Galon air minum kantor', 'SDM', 'Lain-lain', 60000),
('2026-10-26', 'KK-026', 'Konsumsi rapat evaluasi bulanan', 'SDM', 'Konsumsi Rapat', 650000),
('2026-10-27', 'KK-027', 'Tol dan parkir kunjungan lapangan', 'Konstruksi', 'BBM Kendaraan', 150000),
('2026-10-28', 'KK-028', 'Cetak spanduk sosialisasi K3', 'Keuangan & Umum', 'ATK & Cetak', 275000),
('2026-10-29', 'KK-029', 'Fotokopi dan jilid laporan', 'SDM', 'ATK & Cetak', 75000),
('2026-10-30', 'KK-030', 'Pembelian ATK', 'Jaringan', 'ATK & Cetak', 300000),
  ('2026-11-02', 'KK-036', 'Galon air minum kantor', 'Keuangan & Umum', 'Lain-lain', 90000),
  ('2026-11-02', 'KK-037', 'Tol dan parkir kunjungan lapangan', 'Konstruksi', 'BBM Kendaraan', 160000),
('2026-11-03', 'KK-031', 'Pembelian ATK', 'Keuangan & Umum', 'ATK & Cetak', 450000),
('2026-11-03', 'KK-032', 'Konsumsi rapat koordinasi', 'Pelayanan Pelanggan', 'Konsumsi Rapat', 600000),
('2026-11-03', 'KK-033', 'BBM kendaraan dinas', 'Jaringan', 'BBM Kendaraan', 350000),
  ('2026-11-03', 'KK-038', 'Konsumsi rapat koordinasi anggaran', 'Keuangan & Umum', 'Konsumsi Rapat', 540000),
('2026-11-03', 'KK-034', 'Fotokopi dan jilid laporan', 'SDM', 'ATK & Cetak', 125000),
  ('2026-11-03', 'KK-039', 'Rompi reflektor', 'Jaringan', 'Perlengkapan K3', 480000),
('2026-11-03', 'KK-035', 'Sarung tangan dan helm K3', 'Konstruksi', 'Perlengkapan K3', 875000),
  ('2026-11-03', 'KK-040', 'Pembelian ATK', 'Pelayanan Pelanggan', 'ATK & Cetak', 310000);
