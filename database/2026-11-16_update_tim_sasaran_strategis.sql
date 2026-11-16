
BEGIN;

-- Kosongkan pilihan lama agar setiap tim bisa
-- menentukan ulang satu indikator.
DELETE FROM public.tim_sasaran_strategis;

-- Hapus primary key gabungan sebelumnya.
ALTER TABLE public.tim_sasaran_strategis
DROP CONSTRAINT IF EXISTS tim_sasaran_strategis_pkey;

-- Satu Tim Kerja hanya boleh memiliki satu pilihan.
ALTER TABLE public.tim_sasaran_strategis
ADD CONSTRAINT uq_tim_sasaran_strategis_tim
UNIQUE (id_tim);

COMMIT;
