
-- =========================================================
-- KERIS
-- Migrasi Analisis SWOT dan PESTLE berdasarkan Tim Kerja
-- Tanggal: 2026-10-29
--
-- Tujuan:
-- 1. Menambahkan id_tim pada tabel analisis dan periode.
-- 2. Memetakan data lama dari id_konteks ke id_tim.
-- 3. Mempertahankan id_konteks dan data analisis lama.
-- 4. Memastikan pemetaan data berhasil dan periode tidak ganda.
-- =========================================================

BEGIN;

-- 1. Tambahkan kolom id_tim jika belum tersedia.
ALTER TABLE analisis_swot_pestle
ADD COLUMN IF NOT EXISTS id_tim INTEGER;

ALTER TABLE analisis_periode
ADD COLUMN IF NOT EXISTS id_tim INTEGER;

-- 2. Petakan data analisis lama berdasarkan konteks.
UPDATE analisis_swot_pestle AS a
SET id_tim = k.id_tim
FROM konteks AS k
WHERE k.id_konteks = a.id_konteks
  AND a.id_tim IS NULL;

-- 3. Petakan pengaturan periode lama berdasarkan konteks.
UPDATE analisis_periode AS p
SET id_tim = k.id_tim
FROM konteks AS k
WHERE k.id_konteks = p.id_konteks
  AND p.id_tim IS NULL;

-- 4. Validasi pemetaan dan keunikan periode.
DO $$
BEGIN
    IF EXISTS (
        SELECT 1
        FROM analisis_swot_pestle
        WHERE id_tim IS NULL
    ) THEN
        RAISE EXCEPTION
            'Migrasi dibatalkan: ada analisis tanpa id_tim.';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM analisis_periode
        WHERE id_tim IS NULL
    ) THEN
        RAISE EXCEPTION
            'Migrasi dibatalkan: ada periode tanpa id_tim.';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM analisis_periode
        GROUP BY id_tim, tahun, triwulan
        HAVING COUNT(*) > 1
    ) THEN
        RAISE EXCEPTION
            'Migrasi dibatalkan: ditemukan periode ganda.';
    END IF;
END $$;

COMMIT;

-- 5. Verifikasi jumlah analisis per tim dan periode.
SELECT
    id_tim,
    tahun,
    triwulan,
    COUNT(*) AS jumlah_analisis
FROM analisis_swot_pestle
GROUP BY id_tim, tahun, triwulan
ORDER BY id_tim, tahun, triwulan;

-- 6. Verifikasi pengaturan periode.
SELECT
    id_periode,
    id_konteks,
    id_tim,
    tahun,
    triwulan,
    dibuka_kembali,
    alasan_buka
FROM analisis_periode
ORDER BY id_tim, tahun, triwulan;
