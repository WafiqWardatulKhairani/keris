
-- =====================================================
-- Membuat relasi Sasaran Strategis per Tim Kerja
-- KERIS - 16 November 2026
-- =====================================================

BEGIN;

-- 1. Membuat tabel relasi
CREATE TABLE IF NOT EXISTS public.tim_sasaran_strategis (
    id_tim INTEGER NOT NULL,
    id_sasaran_strategis INTEGER NOT NULL,

    PRIMARY KEY (id_tim, id_sasaran_strategis),

    CONSTRAINT fk_tim_sasaran_tim
        FOREIGN KEY (id_tim)
        REFERENCES public.tim_kerja (id_tim)
        ON DELETE CASCADE,

    CONSTRAINT fk_tim_sasaran_indikator
        FOREIGN KEY (id_sasaran_strategis)
        REFERENCES public.sasaran_strategis (id_sasaran_strategis)
        ON DELETE CASCADE
);

-- 2. Migrasi pilihan Indikator dari tabel konteks
INSERT INTO public.tim_sasaran_strategis (
    id_tim,
    id_sasaran_strategis
)
SELECT DISTINCT
    id_tim,
    id_sasaran_strategis
FROM public.konteks
WHERE id_tim IS NOT NULL
  AND id_sasaran_strategis IS NOT NULL
ON CONFLICT DO NOTHING;

COMMIT;
