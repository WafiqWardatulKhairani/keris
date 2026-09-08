<?php

namespace App\Controllers;

class DashboardController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        $tahunList = $db->query(
            "SELECT DISTINCT tahun FROM konteks WHERE tahun IS NOT NULL ORDER BY tahun DESC"
        )->getResultArray();

        $timKerjaList = $db->table('tim_kerja')
            ->select('id_tim, nama_tim')
            ->orderBy('nama_tim')
            ->get()->getResultArray();

        $kategoriList = $db->table('kategori_risiko')
            ->select('id_kategori_risiko, nama_kategori')
            ->orderBy('nama_kategori')
            ->get()->getResultArray();

        $matriks = $db->table('matriks_risiko')
            ->orderBy('level_kemungkinan', 'DESC')
            ->orderBy('level_dampak', 'ASC')
            ->get()
            ->getResultArray();

        return view('dashboard/index', [
            'tahunList'         => $tahunList,
            'timKerjaList'      => $timKerjaList,
            'kategoriList'      => $kategoriList,
            'matriks' => $matriks,
            'heatmap' => [],
            'hideGlobalContext' => true,
        ]);
    }

    // Endpoint khusus debug – panggil /dashboard/debug di browser
    public function debug()
    {
        $db  = \Config\Database::connect();
        $out = [];

        // 1. Cek kolom tabel konteks
        $out['konteks_columns'] = $db->query("SHOW COLUMNS FROM konteks")->getResultArray();

        // 2. Cek kolom tabel identifikasi_risiko
        $out['ir_columns'] = $db->query("SHOW COLUMNS FROM identifikasi_risiko")->getResultArray();

        // 3. Sample data konteks (5 baris)
        $out['konteks_sample'] = $db->query("SELECT * FROM konteks LIMIT 5")->getResultArray();

        // 4. Sample data identifikasi_risiko (5 baris)
        $out['ir_sample'] = $db->query("SELECT * FROM identifikasi_risiko LIMIT 5")->getResultArray();

        // 5. Cek apakah tabel konteks_proses_bisnis ada
        $out['kpb_exists'] = $db->query("SHOW TABLES LIKE 'konteks_proses_bisnis'")->getResultArray();

        // 6. Sample konteks_proses_bisnis kalau ada
        if (!empty($out['kpb_exists'])) {
            $out['kpb_sample'] = $db->query("SELECT * FROM konteks_proses_bisnis LIMIT 5")->getResultArray();
        }

        // 7. Cek kolom tim_kerja
        $out['tim_kerja_columns'] = $db->query("SHOW COLUMNS FROM tim_kerja")->getResultArray();

        // 8. Sample tim_kerja
        $out['tim_kerja_sample'] = $db->query("SELECT * FROM tim_kerja LIMIT 5")->getResultArray();

        // 9. Count semua tabel utama
        $tables = [
            'identifikasi_risiko',
            'penilaian_risiko',
            'evaluasi_risiko',
            'rencana_penanganan_risiko',
            'pemantauan_risiko',
            'konteks',
            'tim_kerja',
            'matriks_risiko',
            'kategori_risiko'
        ];
        foreach ($tables as $t) {
            try {
                $row = $db->query("SELECT COUNT(*) AS c FROM $t")->getRowArray();
                $out['counts'][$t] = $row['c'];
            } catch (\Throwable $e) {
                $out['counts'][$t] = 'ERROR: ' . $e->getMessage();
            }
        }

        // 10. Cek join ir → konteks langsung (tanpa kpb)
        $out['join_direct_test'] = $db->query("
            SELECT ir.id_identifikasi, ir.id_konteks_proses, k.id_konteks, k.tahun
            FROM identifikasi_risiko ir
            LEFT JOIN konteks k ON k.id_konteks = ir.id_konteks_proses
            LIMIT 5
        ")->getResultArray();

        // 11. Distinct nilai id_konteks_proses dari ir
        $out['ir_konteks_proses_vals'] = $db->query(
            "SELECT DISTINCT id_konteks_proses FROM identifikasi_risiko LIMIT 10"
        )->getResultArray();

        // 12. Distinct id_konteks dari konteks
        $out['konteks_id_vals'] = $db->query(
            "SELECT id_konteks, tahun FROM konteks LIMIT 10"
        )->getResultArray();

        return $this->response->setJSON($out);
    }

    public function data()
    {
        $db    = \Config\Database::connect();

        $tahun = $this->request->getGet('tahun');
        $timId = $this->request->getGet('tim');
        $katId = $this->request->getGet('kategori');

        try {
            // ── Deteksi join path yang benar ─────────────────────────────────
            // Cek apakah id_konteks_proses di ir langsung cocok dengan id_konteks di konteks
            // atau butuh lewat konteks_proses_bisnis
            // $testJoin = $db->query("
            //     SELECT COUNT(*) AS c
            //     FROM identifikasi_risiko ir
            //     JOIN konteks k ON k.id_konteks = ir.id_konteks_proses
            // ")->getRowArray();
            // $directJoinWorks = ((int)$testJoin['c']) > 0;

            // Cek apakah konteks punya kolom id_tim langsung
            $konteksColumns = array_column(
                $db->query("
                    SELECT column_name
                    FROM information_schema.columns
                    WHERE table_name = 'konteks'
                ")->getResultArray(),
                'column_name'
            );
            $konteksHasIdTim = in_array('id_tim', $konteksColumns);

            // ── Helper: bangun JOIN + WHERE berdasarkan struktur aktual ───────
            $buildJoin = function (string $irAlias = 'ir') use (
                $db,
                $tahun,
                $timId,
                $katId,

                $konteksHasIdTim,
                $konteksColumns
            ): array {
                $join   = "";
                $where  = [];
                $params = [];

                $needKonteks = $tahun || $timId;
                $needKat     = (bool)$katId;

                if ($needKonteks) {

                    $join .= "
        LEFT JOIN konteks_proses_bisnis kpb
            ON kpb.id_konteks_proses = {$irAlias}.id_konteks_proses

        LEFT JOIN konteks k
            ON k.id_konteks = kpb.id_konteks
    ";

                    if ($tahun) {
                        $where[]  = "k.tahun = ?";
                        $params[] = $tahun;
                    }

                    if ($timId) {
                        $where[]  = "k.id_tim = ?";
                        $params[] = $timId;
                    }
                }

                if ($needKat) {
                    $where[]  = "{$irAlias}.id_kategori_risiko = ?";
                    $params[] = $katId;
                }

                $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
                return [$join, $whereSql, $params];
            };

            // ── KPI 1: Total Risiko ──────────────────────────────────────────
            [$j, $w, $p] = $buildJoin('ir');
            if (empty($p)) {
                $totalRisiko = (int)$db->query(
                    "SELECT COUNT(*) AS total FROM identifikasi_risiko"
                )->getRowArray()['total'];
            } else {
                $totalRisiko = (int)$db->query(
                    "SELECT COUNT(DISTINCT ir.id_identifikasi) AS total
                     FROM identifikasi_risiko ir $j $w",
                    $p
                )->getRowArray()['total'];
            }

            // ── KPI 2: Total RTP ─────────────────────────────────────────────
            if (empty($p)) {

                $totalRtp = (int)$db->query("
        SELECT COUNT(*) AS total
        FROM rencana_penanganan_risiko
    ")->getRowArray()['total'];
            } else {

                [$j2, $w2, $p2] = $buildJoin('ir');

                $totalRtp = (int)$db->query("
        SELECT COUNT(DISTINCT rp.id_rtp) AS total

        FROM rencana_penanganan_risiko rp

        JOIN evaluasi_risiko er
            ON er.id_evaluasi = rp.id_penilaian_awal

        JOIN identifikasi_risiko ir
            ON ir.id_identifikasi = er.id_identifikasi

        $j2
        $w2
    ", $p2)->getRowArray()['total'];
            }

            // ── KPI 3: Realisasi ─────────────────────────────────────────────

            [$j3, $w3, $p3] = $buildJoin('ir');

            $rtpSelesai = (int)$db->query(
                "
                SELECT COUNT(DISTINCT rp.id_rtp) AS total

                FROM rencana_penanganan_risiko rp

                LEFT JOIN pemantauan_risiko pm
                    ON pm.id_rtp = rp.id_rtp

                LEFT JOIN bukti_pemantauan bp
                    ON bp.id_pemantauan = pm.id_pemantauan

                JOIN evaluasi_risiko er
                    ON er.id_evaluasi = rp.id_penilaian_awal

                JOIN identifikasi_risiko ir
                    ON ir.id_identifikasi = er.id_identifikasi

                $j3

                " . ($w3
                    ? "$w3 AND pm.realisasi_output IS NOT NULL
                    AND pm.realisasi_waktu IS NOT NULL
                    AND bp.id_bukti IS NOT NULL"
                    : "WHERE pm.realisasi_output IS NOT NULL
                    AND pm.realisasi_waktu IS NOT NULL
                    AND bp.id_bukti IS NOT NULL"),
                $p3
            )->getRowArray()['total'];

            $realisasi = $totalRtp > 0
                ? round(($rtpSelesai / $totalRtp) * 100)
                : 0;

            // ── HEATMAP ───────────────────────────────────────────────────────
            $matriksGrid = [];
            foreach (
                $db->query(
                    "SELECT level_kemungkinan, level_dampak, nilai_risiko, warna FROM matriks_risiko"
                )->getResultArray() as $m
            ) {
                $matriksGrid[(int)$m['level_dampak']][(int)$m['level_kemungkinan']] = [
                    'nilai_risiko' => $m['nilai_risiko'],
                    'warna'        => $m['warna'],
                ];
            }

            [$jh, $wh, $ph] = $buildJoin('ir');
            //             dd([
            //     'join'   => $jh,
            //     'where'  => $wh,
            //     'params' => $ph,
            // ]);
            $hmRows = $db->query("
                SELECT mr.level_kemungkinan, mr.level_dampak, COUNT(*) AS total
                FROM penilaian_risiko pr
                JOIN matriks_risiko mr      ON mr.id_matriks = pr.id_matriks
                JOIN identifikasi_risiko ir ON ir.id_identifikasi = pr.id_identifikasi
                $jh $wh
                GROUP BY mr.level_kemungkinan, mr.level_dampak
            ", $ph)->getResultArray();

            $countGrid = [];
            for ($d = 1; $d <= 5; $d++)
                for ($k = 1; $k <= 5; $k++)
                    $countGrid[$d][$k] = 0;
            foreach ($hmRows as $r)
                $countGrid[(int)$r['level_dampak']][(int)$r['level_kemungkinan']] = (int)$r['total'];

            $heatmap = [];
            for ($d = 1; $d <= 5; $d++)
                for ($k = 1; $k <= 5; $k++)
                    $heatmap[$d][$k] = [
                        'total'        => $countGrid[$d][$k],
                        'warna'        => $matriksGrid[$d][$k]['warna']        ?? null,
                        'nilai_risiko' => $matriksGrid[$d][$k]['nilai_risiko'] ?? null,
                    ];

            // ── PIE ───────────────────────────────────────────────────────────
            // ── PIE: DIHITUNG DARI PETA RISIKO ───────────────────────────────

            $pieMap = [
                'biru'   => 0,
                'hijau'  => 0,
                'kuning' => 0,
                'oranye' => 0,
                'merah'  => 0,
            ];

            foreach ($heatmap as $row) {
                foreach ($row as $cell) {

                    $warna = strtolower(trim($cell['warna'] ?? ''));

                    if (isset($pieMap[$warna])) {
                        $pieMap[$warna] += (int) ($cell['total'] ?? 0);
                    }
                }
            }

            $pieLabels = [
                'Sangat Rendah',
                'Rendah',
                'Sedang',
                'Tinggi',
                'Sangat Tinggi',
            ];

            $pieValues = [
                $pieMap['biru'],
                $pieMap['hijau'],
                $pieMap['kuning'],
                $pieMap['oranye'],
                $pieMap['merah'],
            ];

            $pieColors = [
                '#bfdbfe',
                '#bbf7d0',
                '#fde68a',
                '#fed7aa',
                '#fca5a5',
            ];
            // ── KATEGORI ──────────────────────────────────────────────────────
            [$jk, $wk, $pk] = $buildJoin('ir');
            $katRows = $db->query("
                SELECT kr.nama_kategori, COUNT(DISTINCT ir.id_identifikasi) AS total
                FROM kategori_risiko kr
                LEFT JOIN identifikasi_risiko ir ON ir.id_kategori_risiko = kr.id_kategori_risiko
                $jk $wk
                GROUP BY kr.id_kategori_risiko, kr.nama_kategori
                ORDER BY kr.nama_kategori
            ", $pk)->getResultArray();
            $kategoriLabels = array_column($katRows, 'nama_kategori');
            $kategoriValues = array_map('intval', array_column($katRows, 'total'));

            // ── STATUS RTP ────────────────────────────────────────────────────
            [$js, $ws, $ps] = $buildJoin('ir');

            $statusRows = $db->query("
                SELECT
                    CASE
                        WHEN pm.realisasi_waktu IS NOT NULL
                            AND DATE_TRUNC('month', pm.realisasi_waktu)
                                > DATE_TRUNC('month', rp.target_waktu)
                        THEN 'Terlambat'

                        WHEN pm.realisasi_output IS NOT NULL
                            AND pm.realisasi_waktu IS NOT NULL
                            AND bp.id_bukti IS NOT NULL
                        THEN 'Selesai'

                        WHEN DATE_TRUNC('month', CURRENT_DATE)
                            > DATE_TRUNC('month', rp.target_waktu)
                        THEN 'Terlambat'

                        WHEN pm.realisasi_output IS NULL
                            AND pm.realisasi_waktu IS NULL
                            AND bp.id_bukti IS NULL
                        THEN 'Belum Dilaksanakan'

                        ELSE 'Dalam Proses'
                    END AS status

                FROM rencana_penanganan_risiko rp
                LEFT JOIN pemantauan_risiko pm
                    ON pm.id_rtp = rp.id_rtp
                LEFT JOIN bukti_pemantauan bp
                    ON bp.id_pemantauan = pm.id_pemantauan

                JOIN evaluasi_risiko er
                    ON er.id_evaluasi = rp.id_penilaian_awal
                JOIN identifikasi_risiko ir
                    ON ir.id_identifikasi = er.id_identifikasi

                $js $ws
            ", $ps)->getResultArray();

            $statusRtp = [
                'selesai'   => 0,
                'proses'    => 0,
                'belum'     => 0,
                'terlambat' => 0,
            ];

            foreach ($statusRows as $row) {
                switch ($row['status']) {
                    case 'Selesai':
                        $statusRtp['selesai']++;
                        break;

                    case 'Dalam Proses':
                        $statusRtp['proses']++;
                        break;

                    case 'Belum Dilaksanakan':
                        $statusRtp['belum']++;
                        break;

                    case 'Terlambat':
                        $statusRtp['terlambat']++;
                        break;
                }
            }

            // ── PROGRESS PER TIM KERJA ────────────────────────────────────────
            // Deteksi join path tim_kerja → konteks
            // Coba: konteks punya id_tim langsung?
            $pgFromTim = "";
            if ($konteksHasIdTim) {
                // tim_kerja → konteks.id_tim
                $pgFromTim = "LEFT JOIN konteks k ON k.id_tim = tk.id_tim";
            } else {
                // tim_kerja → kegiatan → konteks
                $pgFromTim = "LEFT JOIN kegiatan kg ON kg.id_tim = tk.id_tim"
                    . " LEFT JOIN konteks k  ON k.id_kegiatan = kg.id_kegiatan";
            }

            // konteks → identifikasi_risiko
            $pgToIr = "
                LEFT JOIN konteks_proses_bisnis kpb ON kpb.id_konteks = k.id_konteks
                LEFT JOIN identifikasi_risiko ir ON ir.id_konteks_proses = kpb.id_konteks_proses
            ";

            $pgWhere  = "WHERE 1=1";
            $pgParams = [];
            if ($tahun) {
                $pgWhere .= " AND k.tahun = ?";
                $pgParams[] = $tahun;
            }
            if ($timId) {
                $pgWhere .= " AND tk.id_tim = ?";
                $pgParams[] = $timId;
            }

            $progressRows = $db->query("
    SELECT
        tk.id_tim,
        tk.nama_tim,

        COUNT(DISTINCT k.id_konteks) AS f1,

        COUNT(
    DISTINCT CASE
        WHEN ev.id_evaluasi IS NOT NULL
        THEN k.id_konteks
    END
) AS f2,

        COUNT(
            DISTINCT CASE
                WHEN rp.id_rtp IS NOT NULL
                THEN k.id_konteks
            END
        ) AS f3,

        COUNT(
            DISTINCT CASE
                WHEN pm.id_pemantauan IS NOT NULL
                THEN k.id_konteks
            END
        ) AS f4

    FROM tim_kerja tk

    $pgFromTim

    $pgToIr

    LEFT JOIN evaluasi_risiko ev
        ON ev.id_identifikasi = ir.id_identifikasi

    LEFT JOIN rencana_penanganan_risiko rp
        ON rp.id_penilaian_awal = ev.id_evaluasi

    LEFT JOIN pemantauan_risiko pm
        ON pm.id_rtp = rp.id_rtp

    $pgWhere

    GROUP BY tk.id_tim, tk.nama_tim
    ORDER BY tk.nama_tim
", $pgParams)->getResultArray();

            // return $this->response->setJSON([
            //     'debug_kategori' => $katRows
            // ]);

            return $this->response->setJSON([
                '_debug' => [
                    'konteksHasIdTim' => $konteksHasIdTim,
                    'totalRisiko'     => $totalRisiko,
                    'totalRtp'        => $totalRtp,

                    'filter_tahun'    => $tahun,
                    'filter_tim'      => $timId,
                    'filter_kategori' => $katId,
                ],
                'kpi' => [
                    'totalRisiko' => $totalRisiko,
                    'totalRtp'    => $totalRtp,
                    'realisasi'   => $realisasi,
                ],
                'heatmap'        => $heatmap,
                'pieLabels'      => $pieLabels,
                'pieValues'      => $pieValues,
                'pieColors'      => $pieColors,
                'kategoriLabels' => $kategoriLabels,
                'kategoriValues' => $kategoriValues,
                'statusRtp'      => $statusRtp,
                'progress'       => $progressRows,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(200)->setJSON([
                'error'   => true,
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'kpi'     => ['totalRisiko' => 0, 'totalRtp' => 0, 'realisasi' => 0],
                'heatmap' => [],
                'pieLabels' => [],
                'pieValues' => [],
                'pieColors' => [],
                'kategoriLabels' => [],
                'kategoriValues' => [],
                'statusRtp'      => ['selesai' => 0, 'proses' => 0, 'belum' => 0, 'terlambat' => 0],
                'progress'       => [],
            ]);
        }
    }
}
