BEGIN;

DO $$
DECLARE
    v_id_tim INTEGER := 10;
    v_tahun INTEGER := 2026;
    v_id_konteks INTEGER;
    v_tw INTEGER;
    v_item RECORD;
    v_uraian TEXT;
BEGIN
    -- Cari satu konteks milik tim 10 untuk tahun 2026.
    SELECT id_konteks
    INTO v_id_konteks
    FROM konteks
    WHERE id_tim = v_id_tim
      AND tahun = v_tahun
    ORDER BY id_konteks
    LIMIT 1;

    IF v_id_konteks IS NULL THEN
        RAISE EXCEPTION
            'Tidak ditemukan konteks untuk tim % tahun %.',
            v_id_tim, v_tahun;
    END IF;

    FOR v_tw IN 1..4 LOOP
        FOR v_item IN
            SELECT *
            FROM (VALUES
                ('SWOT',  'S',   'Tim memiliki kompetensi teknis dalam pengelolaan infrastruktur TI.'),
                ('SWOT',  'W',   'Dokumentasi konfigurasi infrastruktur belum sepenuhnya terstandardisasi.'),
                ('SWOT',  'O',   'Otomasi berpotensi meningkatkan efisiensi pengelolaan sistem.'),
                ('SWOT',  'T',   'Gangguan keamanan siber dapat menghambat layanan TI.'),
                ('PESTLE','P',   'Perubahan kebijakan pemerintah dapat memengaruhi prioritas pengembangan TI.'),
                ('PESTLE','E',   'Keterbatasan anggaran dapat menunda pembaruan perangkat.'),
                ('PESTLE','S',   'Perbedaan literasi digital memengaruhi pemanfaatan sistem.'),
                ('PESTLE','T',   'Perkembangan teknologi menuntut pembaruan infrastruktur berkala.'),
                ('PESTLE','L',   'Pengelolaan data perlu mengikuti ketentuan yang berlaku.'),
                ('PESTLE','ENV', 'Efisiensi energi perangkat menjadi pertimbangan pengelolaan pusat data.')
            ) AS x(jenis, kategori, uraian)
        LOOP
            v_uraian := '[DUMMY TEST] ' || v_item.uraian;

            IF NOT EXISTS (
                SELECT 1
                FROM analisis_swot_pestle a
                WHERE a.id_tim = v_id_tim
                  AND a.tahun = v_tahun
                  AND a.triwulan = v_tw
                  AND a.jenis_analisis = v_item.jenis
                  AND a.kategori = v_item.kategori
                  AND a.uraian = v_uraian
            ) THEN
                INSERT INTO analisis_swot_pestle (
                    id_konteks,
                    id_tim,
                    jenis_analisis,
                    kategori,
                    uraian,
                    tahun,
                    triwulan
                )
                VALUES (
                    v_id_konteks,
                    v_id_tim,
                    v_item.jenis,
                    v_item.kategori,
                    v_uraian,
                    v_tahun,
                    v_tw
                );
            END IF;
        END LOOP;
    END LOOP;
END $$;

COMMIT;