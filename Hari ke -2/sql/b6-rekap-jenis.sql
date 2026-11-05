-- Blok 6 mandiri: rekap kas kecil Oktober per jenis biaya
SELECT jenis,                                                 -- Kelompok per jenis biaya
       COUNT(*)    AS jumlah_transaksi,                       -- Banyaknya transaksi
       SUM(jumlah) AS total,                                  -- Total
       ROUND(AVG(jumlah)) AS rata_rata                        -- Rata-rata dibulatkan
FROM kas_kecil                                                -- Dari tabel kas_kecil
WHERE MONTH(tanggal) = 10                                     -- Hanya Oktober
GROUP BY jenis                                                -- Satu baris per jenis
ORDER BY total DESC;                                          -- Total terbesar di atas
