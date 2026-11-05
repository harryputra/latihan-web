-- ==========================================================================
-- database.sql — struktur dan data contoh Aplikasi Peminjaman Ruang Rapat
-- Cara memakai: phpMyAdmin > tab Import > pilih berkas ini > Kirim (Go).
-- Seluruh data bersifat FIKTIF untuk keperluan pelatihan.
-- ==========================================================================

DROP DATABASE IF EXISTS pa_ruang_rapat;                       -- hapus versi lama agar bisa dipasang ulang
CREATE DATABASE pa_ruang_rapat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pa_ruang_rapat;

-- Tabel 1: daftar ruang rapat (data induk)
CREATE TABLE ruang (
  id INT AUTO_INCREMENT PRIMARY KEY,                          -- nomor urut otomatis
  nama VARCHAR(50) NOT NULL UNIQUE,                           -- nama ruang, tidak boleh kembar
  lantai TINYINT NOT NULL,                                    -- letak lantai
  kapasitas INT NOT NULL CHECK (kapasitas > 0),               -- jumlah kursi
  fasilitas VARCHAR(150) NOT NULL DEFAULT ''                  -- keterangan fasilitas
);

-- Tabel 2: pengajuan peminjaman. Kolom ruang_id menunjuk ke ruang.id (relasi).
CREATE TABLE peminjaman (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ruang_id INT NOT NULL,                                      -- ruang yang dipinjam
  tanggal DATE NOT NULL,                                      -- tanggal pemakaian
  jam_mulai TIME NOT NULL,                                    -- misal 09:00
  jam_selesai TIME NOT NULL,                                  -- misal 11:00
  kegiatan VARCHAR(100) NOT NULL,                             -- nama kegiatan
  pemohon VARCHAR(60) NOT NULL,                               -- nama pemohon
  bidang VARCHAR(40) NOT NULL,                                -- bidang pemohon
  peserta INT NOT NULL CHECK (peserta > 0),                   -- perkiraan jumlah peserta
  status ENUM('Menunggu', 'Disetujui', 'Ditolak') NOT NULL DEFAULT 'Menunggu',  -- hanya tiga nilai yang diizinkan
  catatan VARCHAR(200) NOT NULL DEFAULT '',                   -- catatan petugas (misal alasan ditolak)
  dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,            -- waktu pengajuan (otomatis)
  CHECK (jam_selesai > jam_mulai),                            -- jam selesai harus setelah jam mulai
  FOREIGN KEY (ruang_id) REFERENCES ruang(id),                -- ruang_id wajib ada di tabel ruang
  INDEX idx_jadwal (ruang_id, tanggal)                        -- mempercepat pengecekan jadwal bentrok
);

INSERT INTO ruang (nama, lantai, kapasitas, fasilitas) VALUES
  ('Ruang Rapat Utama', 2, 30, 'Proyektor, layar, konferensi video'),
  ('Ruang Rapat Kecil A', 1, 8, 'Televisi, papan tulis'),
  ('Ruang Rapat Kecil B', 1, 10, 'Televisi, papan tulis'),
  ('Aula', 3, 80, 'Pengeras suara, proyektor, panggung');

INSERT INTO peminjaman (ruang_id, tanggal, jam_mulai, jam_selesai, kegiatan, pemohon, bidang, peserta, status, catatan) VALUES
  (1, '2026-11-02', '09:00', '11:00', 'Rapat koordinasi mingguan', 'Andi Pratama', 'Keuangan & Umum', 18, 'Disetujui', ''),
  (2, '2026-11-02', '13:00', '14:30', 'Wawancara calon pegawai magang', 'Sari Wulandari', 'SDM', 4, 'Disetujui', ''),
  (4, '2026-11-02', '08:00', '12:00', 'Sosialisasi keselamatan kerja', 'Budi Santoso', 'Konstruksi', 65, 'Disetujui', ''),
  (1, '2026-11-03', '08:30', '10:00', 'Pembahasan rekap anggaran triwulan', 'Rina Kartika', 'Keuangan & Umum', 12, 'Disetujui', ''),
  (3, '2026-11-03', '10:00', '11:30', 'Evaluasi layanan pelanggan', 'Dewi Lestari', 'Pelayanan Pelanggan', 9, 'Disetujui', ''),
  (1, '2026-11-03', '13:00', '15:00', 'Rapat persiapan pelatihan', 'Sari Wulandari', 'SDM', 20, 'Disetujui', ''),
  (2, '2026-11-03', '14:00', '15:00', 'Diskusi jadwal pemeliharaan kendaraan', 'Hendra Wijaya', 'Jaringan', 5, 'Disetujui', ''),
  (4, '2026-11-04', '08:00', '16:00', 'Pelatihan aplikasi perkantoran', 'Sari Wulandari', 'SDM', 60, 'Disetujui', ''),
  (1, '2026-11-04', '09:00', '10:30', 'Rapat koordinasi lapangan', 'Hendra Wijaya', 'Jaringan', 22, 'Menunggu', ''),
  (2, '2026-11-04', '10:00', '11:00', 'Verifikasi dokumen pertanggungjawaban', 'Rina Kartika', 'Keuangan & Umum', 3, 'Menunggu', ''),
  (3, '2026-11-05', '09:00', '12:00', 'Penyusunan laporan bulanan', 'Dewi Lestari', 'Pelayanan Pelanggan', 6, 'Menunggu', ''),
  (1, '2026-11-05', '13:00', '16:00', 'Rapat evaluasi proyek', 'Budi Santoso', 'Konstruksi', 25, 'Menunggu', ''),
  (2, '2026-11-05', '08:00', '09:00', 'Rapat kecil tim arsip', 'Andi Pratama', 'Keuangan & Umum', 12, 'Ditolak', 'Peserta melebihi kapasitas ruang (8 orang).'),
  (4, '2026-11-06', '13:00', '16:00', 'Rapat kerja tahunan bidang', 'Andi Pratama', 'Keuangan & Umum', 50, 'Ditolak', 'Aula dipakai kegiatan kantor pada jam yang sama.'),
  (1, '2026-11-09', '09:00', '11:00', 'Rapat koordinasi mingguan', 'Andi Pratama', 'Keuangan & Umum', 18, 'Disetujui', ''),
  (3, '2026-11-10', '13:00', '15:00', 'Diskusi rencana kerja SDM', 'Sari Wulandari', 'SDM', 8, 'Menunggu', ''),
  (1, '2026-11-12', '08:30', '11:30', 'Penyusunan rencana anggaran', 'Rina Kartika', 'Keuangan & Umum', 15, 'Menunggu', ''),
  (4, '2026-11-13', '08:00', '12:00', 'Apel dan pengarahan bulanan', 'Budi Santoso', 'Konstruksi', 75, 'Disetujui', '');
