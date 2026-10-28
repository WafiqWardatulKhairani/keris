
-- ============================================================
-- KERIS - ANALISIS SWOT DAN PESTLE
-- Tanggal     : 28 Oktober 2026
-- Database    : PostgreSQL
-- Tujuan      :
--   1. Menyimpan poin analisis SWOT dan PESTLE per konteks.
--   2. Mendukung filter tahun dan triwulan.
--   3. Mengatur pembukaan kembali periode oleh admin.
-- Catatan:
--   Periode otomatis read-only setelah triwulan berakhir.
--   Admin dapat membuka kembali periode melalui aplikasi.
-- ============================================================


-- ============================================================
-- TABEL 1: analisis_swot_pestle
-- Menyimpan setiap poin analisis SWOT atau PESTLE.
-- Satu kategori dapat memiliki banyak poin analisis.
-- ============================================================

CREATE TABLE IF NOT EXISTS analisis_swot_pestle (
    id_analisis SERIAL PRIMARY KEY,

    -- Konteks yang menjadi induk analisis.
    id_konteks INTEGER NOT NULL,

    -- Jenis analisis: SWOT atau PESTLE.
    jenis_analisis VARCHAR(10) NOT NULL
        CHECK (jenis_analisis IN ('SWOT', 'PESTLE')),

    -- Kategori SWOT: S, W, O, T.
    -- Kategori PESTLE: P, E, S, T, L, ENV.
    -- ENV digunakan untuk membedakan Environmental
    -- dari Economic yang sama-sama berawalan E.
    kategori VARCHAR(3) NOT NULL,

    -- Isi atau uraian poin analisis.
    uraian TEXT NOT NULL,

    -- Informasi waktu pencatatan.
    created_at TIMESTAMP WITHOUT TIME ZONE
        DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at TIMESTAMP WITHOUT TIME ZONE
        DEFAULT CURRENT_TIMESTAMP NOT NULL,

    -- Relasi dengan tabel konteks yang sudah tersedia.
    CONSTRAINT fk_analisis_konteks
        FOREIGN KEY (id_konteks)
        REFERENCES konteks (id_konteks)
        ON DELETE CASCADE,

    -- Validasi kategori sesuai jenis analisis.
    CONSTRAINT chk_analisis_kategori
        CHECK (
            (jenis_analisis = 'SWOT'
                AND kategori IN ('S', 'W', 'O', 'T'))
            OR
            (jenis_analisis = 'PESTLE'
                AND kategori IN ('P', 'E', 'S', 'T', 'L', 'ENV'))
        )
);


-- Mempercepat pengambilan analisis berdasarkan konteks,
-- jenis analisis, dan kategori.
CREATE INDEX IF NOT EXISTS idx_analisis_konteks_jenis_kategori
    ON analisis_swot_pestle
    (id_konteks, jenis_analisis, kategori);


-- ============================================================
-- TABEL 2: analisis_periode
-- Menyimpan status pembukaan kembali periode analisis.
--
-- Penguncian otomatis dihitung berdasarkan tanggal berakhir
-- triwulan di aplikasi. Jika periode sudah berakhir dan tidak
-- dibuka kembali, data menjadi read-only.
-- ============================================================

CREATE TABLE IF NOT EXISTS analisis_periode (
    id_periode SERIAL PRIMARY KEY,

    -- Konteks, tahun, dan triwulan yang diatur.
    id_konteks INTEGER NOT NULL,
    tahun INTEGER NOT NULL,
    triwulan SMALLINT NOT NULL
        CHECK (triwulan BETWEEN 1 AND 4),

    -- FALSE = tidak dibuka kembali.
    -- TRUE  = admin mengizinkan pengeditan kembali.
    dibuka_kembali BOOLEAN NOT NULL DEFAULT FALSE,

    -- Informasi audit pembukaan kembali oleh admin.
    dibuka_oleh INTEGER NULL,
    dibuka_pada TIMESTAMP WITHOUT TIME ZONE NULL,
    alasan_buka TEXT NULL,

    created_at TIMESTAMP WITHOUT TIME ZONE
        DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at TIMESTAMP WITHOUT TIME ZONE
        DEFAULT CURRENT_TIMESTAMP NOT NULL,

    CONSTRAINT fk_analisis_periode_konteks
        FOREIGN KEY (id_konteks)
        REFERENCES konteks (id_konteks)
        ON DELETE CASCADE,

    -- Satu pengaturan untuk setiap konteks, tahun, triwulan.
    CONSTRAINT uq_analisis_periode
        UNIQUE (id_konteks, tahun, triwulan)
);


-- Mempercepat pencarian status periode.
CREATE INDEX IF NOT EXISTS idx_analisis_periode_tahun_triwulan
    ON analisis_periode (tahun, triwulan);


-- ============================================================
-- PENYESUAIAN TABEL ANALISIS SWOT DAN PESTLE
-- Tujuan:
-- Menyimpan tahun dan triwulan pada setiap poin analisis.
-- Dengan demikian, data tiap periode dapat difilter terpisah.
-- ============================================================

ALTER TABLE analisis_swot_pestle
    ADD COLUMN IF NOT EXISTS tahun INTEGER,
    ADD COLUMN IF NOT EXISTS triwulan SMALLINT;

-- Memastikan nilai triwulan berada pada rentang 1-4.
ALTER TABLE analisis_swot_pestle
    ADD CONSTRAINT chk_analisis_triwulan
    CHECK (triwulan BETWEEN 1 AND 4);

-- Mempercepat pencarian berdasarkan konteks dan periode.
CREATE INDEX IF NOT EXISTS idx_analisis_konteks_periode
    ON analisis_swot_pestle
    (id_konteks, tahun, triwulan);
