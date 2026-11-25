-- PENAMBAHAN STATUS EFEKTIVITAS RTP

ALTER TABLE public.pemantauan_risiko
ADD COLUMN IF NOT EXISTS status_efektivitas VARCHAR(30);

-- Membatasi pilihan status efektivitas
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM pg_constraint
        WHERE conname = 'chk_status_efektivitas'
    ) THEN
        ALTER TABLE public.pemantauan_risiko
        ADD CONSTRAINT chk_status_efektivitas
        CHECK (
            status_efektivitas IS NULL
            OR status_efektivitas IN ('Efektif', 'Kurang Efektif')
        );
    END IF;
END $$;