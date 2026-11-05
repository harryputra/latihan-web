-- Blok 5 mandiri: pengeluaran Rp 500.000 ke atas, dari yang terbesar
SELECT tanggal, uraian, bidang, jumlah                        -- Kolom yang ditampilkan
FROM kas_kecil                                                -- Dari tabel kas_kecil
WHERE jumlah >= 500000                                        -- Syarat: jumlah minimal 500 ribu
ORDER BY jumlah DESC;                                         -- DESC = urut dari terbesar
