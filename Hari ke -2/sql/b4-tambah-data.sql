-- Blok 4 mandiri: menambah dua transaksi kas kecil
INSERT INTO kas_kecil (tanggal, no_bukti, uraian, bidang, jenis, jumlah)  -- Tambah data: sebutkan tabel dan urutan kolom
VALUES ('2026-11-09', 'KK-036', 'Konsumsi rapat koordinasi anggaran', 'Keuangan & Umum', 'Konsumsi Rapat', 540000),  -- Baris data pertama (teks dalam petik tunggal)
       ('2026-11-09', 'KK-037', 'Rompi reflektor', 'Jaringan', 'Perlengkapan K3', 480000);  -- Baris data kedua, diakhiri titik koma
SELECT * FROM kas_kecil WHERE no_bukti IN ('KK-036', 'KK-037');  -- Periksa: tampilkan dua data yang baru
