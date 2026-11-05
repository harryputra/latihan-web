-- Blok 5 terbimbing: pengeluaran Bidang Jaringan bulan Oktober
SELECT tanggal, no_bukti, uraian, jumlah                      -- Kolom yang ditampilkan
FROM kas_kecil                                                -- Dari tabel kas_kecil
WHERE bidang = 'Jaringan'                                     -- Syarat 1: bidang Jaringan
  AND tanggal BETWEEN '2026-10-01' AND '2026-10-31'           -- Syarat 2: tanggal dalam bulan Oktober
ORDER BY tanggal;                                             -- Urut dari tanggal terlama
