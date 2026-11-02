
-- =====================================================
-- KERIS
-- Add Keterjadian Risiko
-- Date: 2026-10-30
-- =====================================================

BEGIN;

-- Tambahkan kolom keterjadian pada identifikasi risiko
ALTER TABLE public.identifikasi_risiko
ADD COLUMN keterjadian_risiko VARCHAR(2)
NOT NULL DEFAULT 'TT';

-- Batasi pilihan status keterjadian
ALTER TABLE public.identifikasi_risiko
ADD CONSTRAINT chk_keterjadian_risiko
CHECK (keterjadian_risiko IN ('T', 'TT', 'BT'));

-- Tambahkan kolom jumlah keterjadian
ALTER TABLE public.identifikasi_risiko
ADD COLUMN jumlah_keterjadian INTEGER NOT NULL DEFAULT 0;

-- Batasi jumlah keterjadian agar tidak negatif
ALTER TABLE public.identifikasi_risiko
ADD CONSTRAINT chk_jumlah_keterjadian
CHECK (jumlah_keterjadian >= 0);

COMMIT;
