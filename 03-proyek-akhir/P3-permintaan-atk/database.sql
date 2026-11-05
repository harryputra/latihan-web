-- ==========================================================================
-- database.sql — struktur dan data contoh Aplikasi Permintaan & Persediaan ATK
-- Cara memakai: phpMyAdmin > tab Import > pilih berkas ini > Kirim (Go).
-- Seluruh data bersifat FIKTIF untuk keperluan pelatihan.
-- ==========================================================================

DROP DATABASE IF EXISTS pa_atk;                               -- hapus versi lama agar bisa dipasang ulang
CREATE DATABASE pa_atk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pa_atk;

-- Tabel 1: data induk barang beserta stok saat ini
CREATE TABLE barang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(10) NOT NULL UNIQUE,                           -- kode barang, misal ATK-001
  nama VARCHAR(60) NOT NULL,                                  -- nama barang
  satuan VARCHAR(15) NOT NULL,                                -- rim, kotak, pak, buah
  harga DECIMAL(12,2) NOT NULL CHECK (harga >= 0),            -- harga satuan terakhir (untuk nilai persediaan)
  stok INT NOT NULL DEFAULT 0 CHECK (stok >= 0),              -- stok tidak boleh minus
  stok_minimum INT NOT NULL DEFAULT 0                         -- batas peringatan stok menipis
);

-- Tabel 2: permintaan barang dari bidang
CREATE TABLE permintaan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tanggal DATE NOT NULL,                                      -- tanggal permintaan
  pemohon VARCHAR(60) NOT NULL,
  bidang VARCHAR(40) NOT NULL,
  barang_id INT NOT NULL,                                     -- barang yang diminta
  jumlah INT NOT NULL CHECK (jumlah > 0),
  keperluan VARCHAR(150) NOT NULL,
  status ENUM('Menunggu', 'Diserahkan', 'Ditolak') NOT NULL DEFAULT 'Menunggu',
  catatan VARCHAR(200) NOT NULL DEFAULT '',                   -- alasan ditolak, dsb.
  dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (barang_id) REFERENCES barang(id)               -- barang wajib terdaftar
);

-- Tabel 3: barang masuk (pembelian / pengisian ulang stok)
CREATE TABLE barang_masuk (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tanggal DATE NOT NULL,
  barang_id INT NOT NULL,
  jumlah INT NOT NULL CHECK (jumlah > 0),
  harga_satuan DECIMAL(12,2) NOT NULL CHECK (harga_satuan >= 0),
  keterangan VARCHAR(150) NOT NULL DEFAULT '',
  FOREIGN KEY (barang_id) REFERENCES barang(id)
);

INSERT INTO barang (kode, nama, satuan, harga, stok, stok_minimum) VALUES
  ('ATK-001', 'Kertas HVS A4 80 gram', 'rim', 55000, 42, 20),
  ('ATK-002', 'Kertas HVS F4 80 gram', 'rim', 60000, 8, 10),
  ('ATK-003', 'Pulpen hitam', 'kotak', 30000, 15, 5),
  ('ATK-004', 'Pensil 2B', 'kotak', 25000, 6, 3),
  ('ATK-005', 'Spidol papan tulis', 'kotak', 45000, 2, 3),
  ('ATK-006', 'Map snelhecter', 'pak', 35000, 12, 5),
  ('ATK-007', 'Ordner', 'buah', 25000, 30, 10),
  ('ATK-008', 'Klip kertas', 'kotak', 8000, 25, 10),
  ('ATK-009', 'Isi stapler', 'kotak', 6000, 4, 10),
  ('ATK-010', 'Amplop cokelat folio', 'pak', 28000, 9, 5);

INSERT INTO permintaan (tanggal, pemohon, bidang, barang_id, jumlah, keperluan, status, catatan) VALUES
  ('2026-10-06', 'Budi Santoso', 'Konstruksi', 2, 5, 'Kebutuhan administrasi harian', 'Diserahkan', ''),
  ('2026-10-06', 'Budi Santoso', 'Konstruksi', 5, 5, 'Penyusunan laporan bulanan', 'Diserahkan', ''),
  ('2026-10-06', 'Budi Santoso', 'Konstruksi', 10, 2, 'Kegiatan rapat koordinasi', 'Diserahkan', ''),
  ('2026-10-07', 'Dewi Lestari', 'Pelayanan Pelanggan', 7, 2, 'Persiapan pelatihan internal', 'Diserahkan', ''),
  ('2026-10-08', 'Budi Santoso', 'Konstruksi', 2, 4, 'Kebutuhan administrasi harian', 'Diserahkan', ''),
  ('2026-10-09', 'Dewi Lestari', 'Pelayanan Pelanggan', 3, 2, 'Pengarsipan dokumen', 'Diserahkan', ''),
  ('2026-10-13', 'Hendra Wijaya', 'Jaringan', 2, 1, 'Kegiatan rapat koordinasi', 'Diserahkan', ''),
  ('2026-10-14', 'Sari Wulandari', 'SDM', 3, 2, 'Kebutuhan administrasi harian', 'Diserahkan', ''),
  ('2026-10-15', 'Hendra Wijaya', 'Jaringan', 10, 4, 'Kegiatan rapat koordinasi', 'Diserahkan', ''),
  ('2026-10-15', 'Sari Wulandari', 'SDM', 7, 1, 'Kebutuhan administrasi harian', 'Diserahkan', ''),
  ('2026-10-19', 'Hendra Wijaya', 'Jaringan', 2, 2, 'Pengarsipan dokumen', 'Diserahkan', ''),
  ('2026-10-19', 'Rina Kartika', 'Keuangan & Umum', 4, 1, 'Persiapan pelatihan internal', 'Diserahkan', ''),
  ('2026-10-20', 'Rina Kartika', 'Keuangan & Umum', 4, 1, 'Persiapan pelatihan internal', 'Diserahkan', ''),
  ('2026-10-20', 'Rina Kartika', 'Keuangan & Umum', 10, 1, 'Penyusunan laporan bulanan', 'Diserahkan', ''),
  ('2026-10-21', 'Dewi Lestari', 'Pelayanan Pelanggan', 10, 1, 'Kebutuhan administrasi harian', 'Diserahkan', ''),
  ('2026-10-21', 'Hendra Wijaya', 'Jaringan', 5, 6, 'Kegiatan rapat koordinasi', 'Ditolak', 'Stok tidak mencukupi; diganti spidol pinjaman ruang rapat.'),
  ('2026-10-22', 'Budi Santoso', 'Konstruksi', 8, 1, 'Kebutuhan administrasi harian', 'Diserahkan', ''),
  ('2026-10-22', 'Hendra Wijaya', 'Jaringan', 1, 1, 'Persiapan pelatihan internal', 'Diserahkan', ''),
  ('2026-10-23', 'Hendra Wijaya', 'Jaringan', 3, 3, 'Penyusunan laporan bulanan', 'Diserahkan', ''),
  ('2026-10-23', 'Sari Wulandari', 'SDM', 1, 1, 'Pengarsipan dokumen', 'Diserahkan', ''),
  ('2026-10-26', 'Rina Kartika', 'Keuangan & Umum', 6, 5, 'Kebutuhan administrasi harian', 'Diserahkan', ''),
  ('2026-10-27', 'Dewi Lestari', 'Pelayanan Pelanggan', 6, 3, 'Persiapan pelatihan internal', 'Diserahkan', ''),
  ('2026-10-27', 'Dewi Lestari', 'Pelayanan Pelanggan', 9, 4, 'Kegiatan rapat koordinasi', 'Diserahkan', ''),
  ('2026-10-28', 'Sari Wulandari', 'SDM', 8, 5, 'Pengarsipan dokumen', 'Diserahkan', ''),
  ('2026-10-29', 'Budi Santoso', 'Konstruksi', 1, 5, 'Persiapan pelatihan internal', 'Diserahkan', ''),
  ('2026-10-29', 'Budi Santoso', 'Konstruksi', 8, 3, 'Pengarsipan dokumen', 'Diserahkan', ''),
  ('2026-10-30', 'Dewi Lestari', 'Pelayanan Pelanggan', 1, 4, 'Kegiatan rapat koordinasi', 'Diserahkan', ''),
  ('2026-11-02', 'Rina Kartika', 'Keuangan & Umum', 1, 5, 'Penyusunan laporan bulanan', 'Diserahkan', ''),
  ('2026-11-02', 'Hendra Wijaya', 'Jaringan', 7, 4, 'Pengarsipan dokumen', 'Diserahkan', ''),
  ('2026-11-03', 'Sari Wulandari', 'SDM', 1, 6, 'Persiapan pelatihan internal', 'Menunggu', ''),
  ('2026-11-03', 'Dewi Lestari', 'Pelayanan Pelanggan', 3, 2, 'Kebutuhan administrasi harian', 'Menunggu', ''),
  ('2026-11-03', 'Budi Santoso', 'Konstruksi', 5, 3, 'Kegiatan rapat koordinasi', 'Menunggu', ''),
  ('2026-11-03', 'Rina Kartika', 'Keuangan & Umum', 9, 2, 'Kebutuhan administrasi harian', 'Menunggu', '');

INSERT INTO barang_masuk (tanggal, barang_id, jumlah, harga_satuan, keterangan) VALUES
  ('2026-10-01', 1, 30, 55000, 'Pengadaan awal bulan'),
  ('2026-10-01', 3, 10, 30000, 'Pengadaan awal bulan'),
  ('2026-10-01', 7, 20, 25000, 'Pengadaan awal bulan'),
  ('2026-10-15', 2, 10, 60000, 'Pengisian ulang'),
  ('2026-10-15', 6, 10, 35000, 'Pengisian ulang'),
  ('2026-11-02', 1, 20, 55000, 'Pengadaan awal bulan');
