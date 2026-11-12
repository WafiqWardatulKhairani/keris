
-- =========================================================
-- KERIS
-- Penambahan Tujuan, Sasaran, dan Relasi Indikator
-- Tanggal: 2026-11-11
-- =========================================================

BEGIN;

-- =========================================================
-- 1. TABEL TUJUAN
-- =========================================================

CREATE TABLE IF NOT EXISTS public.tujuan (
    id_tujuan SERIAL PRIMARY KEY,
    uraian_tujuan TEXT NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================================================
-- 2. TABEL SASARAN
-- =========================================================

CREATE TABLE IF NOT EXISTS public.sasaran (
    id_sasaran SERIAL PRIMARY KEY,
    id_tujuan INTEGER NOT NULL,
    uraian_sasaran TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_sasaran_tujuan
        FOREIGN KEY (id_tujuan)
        REFERENCES public.tujuan(id_tujuan),

    CONSTRAINT uq_sasaran_tujuan
        UNIQUE (id_tujuan, uraian_sasaran)
);

-- =========================================================
-- 3. TAMBAHKAN RELASI SASARAN KE DATA INDIKATOR LAMA
-- =========================================================

ALTER TABLE public.sasaran_strategis
ADD COLUMN IF NOT EXISTS id_sasaran INTEGER;

-- =========================================================
-- 4. INPUT DATA TUJUAN
-- =========================================================

INSERT INTO public.tujuan (uraian_tujuan)
VALUES
(
    'Terwujudnya Peningkatan Pemanfaatan Data Statistik Berkualitas dan Insight dalam Perumusan Kebijakan dan Pengambilan Keputusan'
),
(
    'Mewujudkan Penyelenggaraan Sistem Statistik Nasional yang Andal, Efektif, dan Efisien'
),
(
    'Mewujudkan Tata Kelola Badan Pusat Statistik yang Berkualitas, Akuntabel, Efektif, dan Efisien dalam Menyelenggarakan Statistik'
)
ON CONFLICT (uraian_tujuan) DO NOTHING;

-- =========================================================
-- 5. INPUT DATA SASARAN
-- =========================================================

INSERT INTO public.sasaran (id_tujuan, uraian_sasaran)
SELECT
    t.id_tujuan,
    v.uraian_sasaran
FROM (
    VALUES

    -- TUJUAN 1
    (
        'Terwujudnya Peningkatan Pemanfaatan Data Statistik Berkualitas dan Insight dalam Perumusan Kebijakan dan Pengambilan Keputusan',
        'Terwujudnya Penyediaan Data dan Insight Statistik Kependudukan dan Ketenagakerjaan yang Berkualitas'
    ),
    (
        'Terwujudnya Peningkatan Pemanfaatan Data Statistik Berkualitas dan Insight dalam Perumusan Kebijakan dan Pengambilan Keputusan',
        'Terwujudnya Penyediaan Data dan Insight Statistik Kesejahteraan Rakyat yang Berkualitas'
    ),
    (
        'Terwujudnya Peningkatan Pemanfaatan Data Statistik Berkualitas dan Insight dalam Perumusan Kebijakan dan Pengambilan Keputusan',
        'Terwujudnya penyediaan Data dan Insight Statistik Ketahanan Sosial yang Berkualitas'
    ),
    (
        'Terwujudnya Peningkatan Pemanfaatan Data Statistik Berkualitas dan Insight dalam Perumusan Kebijakan dan Pengambilan Keputusan',
        'Terwujudnya penyediaan Insight dan Statistik Tanaman Pangan, Hortikultura, dan Perkebunan yang berkualitas'
    ),
    (
        'Terwujudnya Peningkatan Pemanfaatan Data Statistik Berkualitas dan Insight dalam Perumusan Kebijakan dan Pengambilan Keputusan',
        'Terwujudnya Penyediaan Data dan Insight Statistik Sumber Daya Mineral dan Konstruksi yang Berkualitas'
    ),
    (
        'Terwujudnya Peningkatan Pemanfaatan Data Statistik Berkualitas dan Insight dalam Perumusan Kebijakan dan Pengambilan Keputusan',
        'Terwujudnya penyediaan Data dan Insight Statistik Industri yang Berkualitas'
    ),
    (
        'Terwujudnya Peningkatan Pemanfaatan Data Statistik Berkualitas dan Insight dalam Perumusan Kebijakan dan Pengambilan Keputusan',
        'Terwujudnya Penyediaan Data dan Insight Statistik Distribusi yang Berkualitas'
    ),
    (
        'Terwujudnya Peningkatan Pemanfaatan Data Statistik Berkualitas dan Insight dalam Perumusan Kebijakan dan Pengambilan Keputusan',
        'Terwujudnya Penyediaan Data dan Insight Statistik Harga yang Berkualitas'
    ),
    (
        'Terwujudnya Peningkatan Pemanfaatan Data Statistik Berkualitas dan Insight dalam Perumusan Kebijakan dan Pengambilan Keputusan',
        'Terwujudnya Penyediaan Data dan Insight Statistik Jasa yang Berkualitas'
    ),
    (
        'Terwujudnya Peningkatan Pemanfaatan Data Statistik Berkualitas dan Insight dalam Perumusan Kebijakan dan Pengambilan Keputusan',
        'Terwujudnya Penyediaan Data dan Insight Statistik Lintas Sektor yang Berkualitas'
    ),

    -- TUJUAN 2
    (
        'Mewujudkan Penyelenggaraan Sistem Statistik Nasional yang Andal, Efektif, dan Efisien',
        'Tersedianya Data 5 Sasaran Visi Indonesia Emas dan 45 Indikator Utama Pembangunan'
    ),
    (
        'Mewujudkan Penyelenggaraan Sistem Statistik Nasional yang Andal, Efektif, dan Efisien',
        'Terwujudnya Kapasitas Tata Kelola Pemerintah Desa Untuk Menghasilkan Statistik Berkualitas'
    ),
    (
        'Mewujudkan Penyelenggaraan Sistem Statistik Nasional yang Andal, Efektif, dan Efisien',
        'Terwujudnya Penguatan Penyelenggaraan Pembinaan Statistik Sektoral Kementerian/Lembaga/Pemerintah Daerah'
    ),
    (
        'Mewujudkan Penyelenggaraan Sistem Statistik Nasional yang Andal, Efektif, dan Efisien',
        'Terwujudnya Layanan Edukasi dan Promosi Statistik yang Prima'
    ),
    (
        'Mewujudkan Penyelenggaraan Sistem Statistik Nasional yang Andal, Efektif, dan Efisien',
        'Terwujudnya Kemudahan Akses Data BPS'
    ),

    -- TUJUAN 3
    (
        'Mewujudkan Tata Kelola Badan Pusat Statistik yang Berkualitas, Akuntabel, Efektif, dan Efisien dalam Menyelenggarakan Statistik',
        'Tersedianya Dukungan Manajemen pada BPS Provinsi dan BPS Kabupaten/Kota'
    ),
    (
        'Mewujudkan Tata Kelola Badan Pusat Statistik yang Berkualitas, Akuntabel, Efektif, dan Efisien dalam Menyelenggarakan Statistik',
        'Peningkatan Pembangunan Zona Integritas'
    )

) AS v(uraian_tujuan, uraian_sasaran)

JOIN public.tujuan t
    ON t.uraian_tujuan = v.uraian_tujuan

ON CONFLICT (id_tujuan, uraian_sasaran) DO NOTHING;

-- =========================================================
-- 6. HUBUNGKAN 20 INDIKATOR LAMA KE SASARAN
-- ID INDIKATOR MENGIKUTI ID LAMA DI sasaran_strategis
-- =========================================================

UPDATE public.sasaran_strategis AS indikator
SET id_sasaran = sasaran.id_sasaran
FROM (
    VALUES
        (1,  'Terwujudnya Penyediaan Data dan Insight Statistik Kependudukan dan Ketenagakerjaan yang Berkualitas'),
        (2,  'Terwujudnya Penyediaan Data dan Insight Statistik Kesejahteraan Rakyat yang Berkualitas'),
        (3,  'Terwujudnya penyediaan Data dan Insight Statistik Ketahanan Sosial yang Berkualitas'),
        (4,  'Terwujudnya penyediaan Insight dan Statistik Tanaman Pangan, Hortikultura, dan Perkebunan yang berkualitas'),
        (5,  'Terwujudnya Penyediaan Data dan Insight Statistik Sumber Daya Mineral dan Konstruksi yang Berkualitas'),
        (6,  'Terwujudnya penyediaan Data dan Insight Statistik Industri yang Berkualitas'),
        (7,  'Terwujudnya Penyediaan Data dan Insight Statistik Distribusi yang Berkualitas'),
        (8,  'Terwujudnya Penyediaan Data dan Insight Statistik Harga yang Berkualitas'),
        (9,  'Terwujudnya Penyediaan Data dan Insight Statistik Jasa yang Berkualitas'),
        (10, 'Terwujudnya Penyediaan Data dan Insight Statistik Lintas Sektor yang Berkualitas'),
        (11, 'Terwujudnya Penyediaan Data dan Insight Statistik Lintas Sektor yang Berkualitas'),
        (12, 'Terwujudnya Penyediaan Data dan Insight Statistik Lintas Sektor yang Berkualitas'),
        (13, 'Tersedianya Data 5 Sasaran Visi Indonesia Emas dan 45 Indikator Utama Pembangunan'),
        (14, 'Terwujudnya Kapasitas Tata Kelola Pemerintah Desa Untuk Menghasilkan Statistik Berkualitas'),
        (15, 'Terwujudnya Penguatan Penyelenggaraan Pembinaan Statistik Sektoral Kementerian/Lembaga/Pemerintah Daerah'),
        (16, 'Terwujudnya Layanan Edukasi dan Promosi Statistik yang Prima'),
        (17, 'Terwujudnya Kemudahan Akses Data BPS'),
        (18, 'Tersedianya Dukungan Manajemen pada BPS Provinsi dan BPS Kabupaten/Kota'),
        (19, 'Tersedianya Dukungan Manajemen pada BPS Provinsi dan BPS Kabupaten/Kota'),
        (20, 'Peningkatan Pembangunan Zona Integritas')
) AS mapping(id_indikator_lama, uraian_sasaran)

JOIN public.sasaran
    ON sasaran.uraian_sasaran = mapping.uraian_sasaran

WHERE indikator.id_sasaran_strategis = mapping.id_indikator_lama;

-- =========================================================
-- 7. PASTIKAN SEMUA INDIKATOR SUDAH TERHUBUNG
-- =========================================================

DO $$
BEGIN
    IF EXISTS (
        SELECT 1
        FROM public.sasaran_strategis
        WHERE id_sasaran IS NULL
    ) THEN
        RAISE EXCEPTION
            'Masih ada indikator yang belum terhubung dengan sasaran. Transaksi dibatalkan.';
    END IF;
END $$;

-- =========================================================
-- 8. FOREIGN KEY INDIKATOR -> SASARAN
-- =========================================================

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM pg_constraint
        WHERE conname = 'fk_indikator_sasaran'
          AND conrelid = 'public.sasaran_strategis'::regclass
    ) THEN
        ALTER TABLE public.sasaran_strategis
        ADD CONSTRAINT fk_indikator_sasaran
        FOREIGN KEY (id_sasaran)
        REFERENCES public.sasaran(id_sasaran);
    END IF;
END $$;

ALTER TABLE public.sasaran_strategis
ALTER COLUMN id_sasaran SET NOT NULL;

CREATE INDEX IF NOT EXISTS idx_indikator_id_sasaran
ON public.sasaran_strategis(id_sasaran);

COMMIT;
