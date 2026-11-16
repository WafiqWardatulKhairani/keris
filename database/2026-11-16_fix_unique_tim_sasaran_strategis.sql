
-- ============================================================
-- KERIS
-- Perbaikan relasi Tim Kerja dengan Sasaran Strategis
-- Tanggal: 16 November 2026
--
-- Tujuan:
-- Memungkinkan satu Tim Kerja memilih banyak Indikator
-- Sasaran Strategis.
--
-- Aturan:
-- 1. Satu Tim Kerja dapat memiliki banyak indikator.
-- 2. Indikator yang sama tidak boleh didaftarkan dua kali
--    pada Tim Kerja yang sama.
-- 3. Tim Kerja berbeda dapat memilih indikator yang sama.
-- ============================================================

BEGIN;

-- Hapus constraint lama yang hanya membatasi satu indikator
-- untuk setiap Tim Kerja.
ALTER TABLE public.tim_sasaran_strategis
DROP CONSTRAINT IF EXISTS uq_tim_sasaran_strategis_tim;

-- Tambahkan constraint unik berdasarkan kombinasi
-- Tim Kerja dan Indikator.
ALTER TABLE public.tim_sasaran_strategis
ADD CONSTRAINT uq_tim_sasaran_strategis_tim_indikator
UNIQUE (id_tim, id_sasaran_strategis);

COMMIT;
