
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


-- =====================================================
-- DATA DUMMY GLOSARIUM KERIS
-- Total target: 25 istilah (termasuk 3 data awal)
-- Aman dijalankan ulang: istilah yang sudah ada dilewati
-- =====================================================

INSERT INTO public.glosarium (istilah, definisi)
SELECT v.istilah, v.definisi
FROM (VALUES
    ('Identifikasi Risiko',
     'Proses menemukan, mengenali, dan mendeskripsikan risiko yang dapat memengaruhi pencapaian tujuan organisasi.'),
    ('Analisis Risiko',
     'Proses memahami karakteristik risiko serta menentukan tingkat risiko berdasarkan kemungkinan dan dampaknya.'),
    ('Evaluasi Risiko',
     'Proses membandingkan hasil analisis risiko dengan kriteria risiko untuk menentukan apakah risiko dapat diterima atau memerlukan penanganan.'),
    ('Penanganan Risiko',
     'Proses memilih dan menerapkan tindakan untuk mengubah atau mengendalikan risiko.'),
    ('Pengendalian Risiko',
     'Tindakan atau langkah yang dilakukan untuk mengurangi kemungkinan terjadinya risiko dan/atau dampaknya.'),
    ('Peta Risiko',
     'Representasi visual posisi risiko berdasarkan tingkat kemungkinan terjadinya dan besarnya dampak.'),
    ('Level Risiko',
     'Tingkat besaran risiko yang ditentukan berdasarkan kombinasi kemungkinan dan dampak risiko.'),
    ('Kemungkinan',
     'Peluang atau frekuensi suatu peristiwa risiko terjadi dalam periode tertentu.'),
    ('Dampak',
     'Konsekuensi atau akibat yang ditimbulkan apabila suatu peristiwa risiko terjadi.'),
    ('Pemilik Risiko',
     'Pihak yang memiliki akuntabilitas dan kewenangan untuk mengelola risiko tertentu.'),
    ('Manajemen Risiko',
     'Kegiatan terkoordinasi untuk mengarahkan dan mengendalikan organisasi terkait risiko.'),
    ('Selera Risiko',
     'Besaran dan jenis risiko yang bersedia diambil atau dipertahankan organisasi dalam mencapai tujuannya.'),
    ('Kriteria Risiko',
     'Parameter yang digunakan untuk menilai tingkat signifikansi suatu risiko.'),
    ('Risiko Inheren',
     'Tingkat risiko yang melekat sebelum mempertimbangkan efektivitas pengendalian yang diterapkan.'),
    ('Risiko Residual',
     'Tingkat risiko yang tersisa setelah pengendalian atau penanganan risiko diterapkan.'),
    ('Rencana Tindak Pengendalian (RTP)',
     'Rencana tindakan untuk menangani risiko, termasuk kegiatan, penanggung jawab, dan target pelaksanaannya.'),
    ('Realisasi RTP',
     'Pelaksanaan tindakan pengendalian yang telah dilakukan dibandingkan dengan rencana yang ditetapkan.'),
    ('Indikator Risiko',
     'Ukuran atau tanda yang digunakan untuk memantau perubahan tingkat risiko atau kemungkinan terjadinya risiko.'),
    ('Pemantauan Risiko',
     'Kegiatan mengamati dan meninjau kondisi risiko serta efektivitas penanganannya secara berkala.'),
    ('Pelaporan Risiko',
     'Penyampaian informasi mengenai risiko, tingkat risiko, dan tindak pengendaliannya kepada pihak terkait.'),
    ('Peristiwa Risiko',
     'Kejadian atau keadaan yang berpotensi memengaruhi pencapaian tujuan organisasi.'),
    ('Penyebab Risiko',
     'Kondisi, tindakan, atau faktor yang dapat memicu terjadinya suatu peristiwa risiko.')
) AS v(istilah, definisi)
WHERE NOT EXISTS (
    SELECT 1
    FROM public.glosarium g
    WHERE LOWER(TRIM(g.istilah)) = LOWER(TRIM(v.istilah))
);


-- =====================================================
-- DATA DUMMY TAMBAHAN GLOSARIUM KERIS
-- Target total: 50 istilah
-- =====================================================

INSERT INTO public.glosarium (istilah, definisi)
SELECT v.istilah, v.definisi
FROM (VALUES
    ('Tujuan Organisasi',
     'Hasil atau kondisi yang ingin dicapai organisasi melalui pelaksanaan tugas dan fungsinya.'),
    ('Sasaran Kegiatan',
     'Hasil spesifik yang ditetapkan untuk dicapai melalui pelaksanaan suatu kegiatan.'),
    ('Risiko Strategis',
     'Risiko yang dapat memengaruhi pencapaian tujuan strategis organisasi.'),
    ('Risiko Operasional',
     'Risiko yang timbul akibat ketidakcukupan atau kegagalan proses internal, sumber daya manusia, sistem, atau kejadian eksternal.'),
    ('Risiko Kepatuhan',
     'Risiko akibat ketidakpatuhan terhadap peraturan, kebijakan, standar, atau ketentuan yang berlaku.'),
    ('Risiko Reputasi',
     'Risiko yang dapat menurunkan kepercayaan atau citra organisasi di mata pemangku kepentingan.'),
    ('Risiko Keuangan',
     'Risiko yang berkaitan dengan pengelolaan anggaran, pembiayaan, atau sumber daya keuangan.'),
    ('Risiko Teknologi Informasi',
     'Risiko yang berkaitan dengan sistem informasi, infrastruktur teknologi, keamanan data, atau gangguan layanan digital.'),
    ('Faktor Internal',
     'Kondisi dari dalam organisasi yang dapat memengaruhi pencapaian tujuan dan pengelolaan risiko.'),
    ('Faktor Eksternal',
     'Kondisi dari luar organisasi yang dapat memengaruhi pencapaian tujuan dan pengelolaan risiko.'),
    ('Pengendalian yang Ada',
     'Kebijakan, prosedur, atau tindakan yang telah diterapkan untuk mengelola risiko.'),
    ('Efektivitas Pengendalian',
     'Tingkat keberhasilan pengendalian dalam mengurangi kemungkinan terjadinya risiko atau dampaknya.'),
    ('Mitigasi Risiko',
     'Upaya untuk mengurangi kemungkinan terjadinya risiko dan/atau besarnya dampak yang ditimbulkan.'),
    ('Toleransi Risiko',
     'Batas variasi tingkat risiko yang dapat diterima dalam pelaksanaan kegiatan atau pencapaian tujuan.'),
    ('Prioritas Risiko',
     'Urutan penanganan risiko berdasarkan tingkat signifikansi dan kebutuhan organisasi.'),
    ('Matriks Risiko',
     'Matriks yang memetakan tingkat risiko berdasarkan kombinasi kemungkinan dan dampaknya.'),
    ('Peluang',
     'Kemungkinan munculnya kondisi yang dapat memberikan manfaat atau mendukung pencapaian tujuan organisasi.'),
    ('Indikator Kinerja',
     'Ukuran yang digunakan untuk menilai pencapaian kinerja terhadap target yang telah ditetapkan.'),
    ('Penanggung Jawab RTP',
     'Pihak yang ditetapkan untuk melaksanakan dan melaporkan realisasi rencana tindak pengendalian.'),
    ('Target Penyelesaian',
     'Batas waktu yang ditetapkan untuk menyelesaikan tindakan atau kegiatan pengendalian risiko.'),
    ('Evaluasi Pengendalian',
     'Penilaian terhadap pelaksanaan dan efektivitas tindakan pengendalian risiko.'),
    ('Dokumentasi Risiko',
     'Pencatatan informasi risiko beserta analisis, evaluasi, dan tindakan penanganannya.'),
    ('Komunikasi Risiko',
     'Pertukaran informasi mengenai risiko antara pihak-pihak yang berkepentingan.'),
    ('Pemangku Kepentingan',
     'Pihak yang dapat memengaruhi, dipengaruhi, atau merasa terdampak oleh keputusan dan kegiatan organisasi.'),
    ('Periode Pemantauan',
     'Rentang waktu yang ditetapkan untuk memantau perkembangan risiko dan pelaksanaan pengendaliannya.')
) AS v(istilah, definisi)
WHERE NOT EXISTS (
    SELECT 1
    FROM public.glosarium g
    WHERE LOWER(TRIM(g.istilah)) = LOWER(TRIM(v.istilah))
);
