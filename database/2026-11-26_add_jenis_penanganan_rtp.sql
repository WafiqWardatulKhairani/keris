ALTER TABLE public.rencana_penanganan_risiko
ADD COLUMN jenis_penanganan VARCHAR(20) NULL;

ALTER TABLE public.rencana_penanganan_risiko
ADD CONSTRAINT chk_jenis_penanganan
CHECK (
    jenis_penanganan IS NULL
    OR jenis_penanganan IN (
        'Preventif',
        'Korektif',
        'Direktif',
        'Detektif'
    )
);