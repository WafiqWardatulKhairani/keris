
-- =====================================================
-- KERIS - Tabel Glosarium
-- Tanggal: 2026-10-23
-- Deskripsi: Menyimpan istilah dan definisi manajemen risiko
-- =====================================================

CREATE TABLE IF NOT EXISTS public.glosarium (
    id_glosarium SERIAL PRIMARY KEY,
    istilah VARCHAR(255) NOT NULL,
    definisi TEXT NOT NULL,
    created_by INTEGER,
    updated_by INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
