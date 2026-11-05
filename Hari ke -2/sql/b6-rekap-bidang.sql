-- Blok 6 terbimbing: rekap kas kecil Oktober per bidang
SELECT bidang,                                                -- Kolom pengelompok
       COUNT(*)    AS jumlah_transaksi,                       -- COUNT = banyaknya transaksi
       SUM(jumlah) AS total                                   -- SUM = total jumlah
FROM kas_kecil                                                -- Dari tabel kas_kecil
WHERE MONTH(tanggal) = 10                                     -- Hanya Oktober
GROUP BY bidang                                               -- Satu baris per bidang (seperti pivot)
ORDER BY total DESC;                                          -- Total terbesar di atas
