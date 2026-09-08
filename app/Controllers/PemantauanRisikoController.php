<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PemantauanRisikoModel;
use App\Models\BuktiPemantauanModel;
use App\Models\KonteksModel;

class PemantauanRisikoController extends BaseController
{
    protected $pemantauanModel;
    protected $buktiModel;
    protected $db;

    public function __construct()
    {
        $this->pemantauanModel = new PemantauanRisikoModel();
        $this->buktiModel      = new BuktiPemantauanModel();
        $this->db              = \Config\Database::connect();
    }

    private function getSummary(): array
    {
        $globalTahun    = session('global_tahun');
        $globalTim      = session('global_id_tim');
        $globalKegiatan = session('global_id_kegiatan');

        $qTotal = $this->db->table('rencana_penanganan_risiko rtp')
            ->join('evaluasi_risiko er',        'er.id_evaluasi = rtp.id_penilaian_awal')
            ->join('identifikasi_risiko ir',    'ir.id_identifikasi = er.id_identifikasi')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('konteks k',                 'k.id_konteks = kpb.id_konteks');

        if ($globalTahun) {
            $qTotal->where('k.tahun', $globalTahun);
        }

        if ($globalTim) {
            $qTotal->where('k.id_tim', $globalTim);
        }

        if ($globalKegiatan) {
            $qTotal->where('k.id_kegiatan', $globalKegiatan);
        }

        $totalRtp = $qTotal->countAllResults();

        $distribusi = $this->pemantauanModel->getDistribusiStatus(
            $globalTahun,
            $globalTim,
            $globalKegiatan
        );

        $totalSudahDipantau = $totalRtp - ($distribusi['Belum Dilaksanakan'] ?? 0);

        return compact('totalRtp', 'totalSudahDipantau', 'distribusi');
    }

    private function validateTimAccessByRtp($idRtp): bool
    {
        $row = $this->db->table('rencana_penanganan_risiko rtp')
            ->select('k.id_tim')
            ->join('evaluasi_risiko er', 'er.id_evaluasi = rtp.id_penilaian_awal')
            ->join('identifikasi_risiko ir', 'ir.id_identifikasi = er.id_identifikasi')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks')
            ->where('rtp.id_rtp', $idRtp)
            ->get()
            ->getRow();

        if (!$row) return false;

        $role = session('role');

        if ($role === 'admin') return true;
        if ($role === 'ketua') return true;

        return (string)session('id_tim') === (string)$row->id_tim;
    }

    public function index()
    {
        $db = $this->db;

        $globalTahun    = session('global_tahun');
        $globalTim      = session('global_id_tim');
        $globalKegiatan = session('global_id_kegiatan');

        $activeKonteks = [
            'id_tim' => $globalTim
        ];

        $perPage = (int) ($this->request->getGet('perPage') ?? 10);
        $page    = (int) ($this->request->getGet('page')    ?? 1);
        $offset  = ($page - 1) * $perPage;
        $filter  = $this->request->getGet('filter');

        $statusCase = "
        CASE
            WHEN pm.realisasi_waktu IS NOT NULL
                AND DATE_TRUNC('month', pm.realisasi_waktu)
                    > DATE_TRUNC('month', rtp.target_waktu)
            THEN 'Terlambat'

            WHEN pm.realisasi_output IS NOT NULL
                AND pm.realisasi_waktu IS NOT NULL
                AND bp.id_bukti IS NOT NULL
            THEN 'Selesai'

            WHEN DATE_TRUNC('month', CURRENT_DATE)
                > DATE_TRUNC('month', rtp.target_waktu)
            THEN 'Terlambat'

            WHEN pm.realisasi_output IS NULL
                AND pm.realisasi_waktu IS NULL
                AND bp.id_bukti IS NULL
            THEN 'Belum Dilaksanakan'

            ELSE 'Dalam Proses'
        END
        ";

        $builder = $this->db->table('rencana_penanganan_risiko rtp')
            ->select("
                ir.id_identifikasi,
                rtp.id_rtp,
                rtp.uraian_rtp,
                rtp.target_output,
                rtp.target_waktu,
                er.id_evaluasi,
                ir.pernyataan_risiko,
                ir.penyebab_risiko,
                ir.dampak_risiko,
                k.id_tim,
                pb.kode_proses,
                pb.uraian_proses,
                sk.nama_tim,
                pr.nilai_risiko,
                pr.warna_risiko,
                km.level      as level_kemungkinan,
                kd.level      as level_dampak,
                sl.nama_level as nama_selera,
                sl.warna      as warna_selera,
                pm.id_pemantauan,
                pm.realisasi_output,
                pm.realisasi_waktu,
                CASE
                    WHEN pm.realisasi_waktu IS NOT NULL
                        AND DATE_TRUNC('month', pm.realisasi_waktu)
                            > DATE_TRUNC('month', rtp.target_waktu)
                    THEN 'Terlambat'

                    WHEN pm.realisasi_output IS NOT NULL
                        AND pm.realisasi_waktu IS NOT NULL
                        AND bp.id_bukti IS NOT NULL
                    THEN 'Selesai'

                    WHEN DATE_TRUNC('month', CURRENT_DATE)
                        > DATE_TRUNC('month', rtp.target_waktu)
                    THEN 'Terlambat'

                    WHEN pm.realisasi_output IS NULL
                        AND pm.realisasi_waktu IS NULL
                        AND bp.id_bukti IS NULL
                    THEN 'Belum Dilaksanakan'

                    ELSE 'Dalam Proses'
                END as status,
                pm.catatan,
                pm.updated_at as pemantauan_updated_at
            ")
            ->join('evaluasi_risiko er', 'er.id_evaluasi = rtp.id_penilaian_awal')
            ->join('identifikasi_risiko ir', 'ir.id_identifikasi = er.id_identifikasi')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('proses_bisnis pb', 'pb.id_proses = kpb.id_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks')
            ->join('tim_kerja sk', 'sk.id_tim = k.id_tim', 'left')
            ->join('penilaian_risiko pr', 'pr.id_penilaian = er.id_penilaian', 'left')
            ->join('kriteria_kemungkinan km', 'km.id_kriteria = pr.id_kemungkinan', 'left')
            ->join('kriteria_dampak kd', 'kd.id_kriteria = pr.id_dampak', 'left')
            ->join('selera_risiko sl', 'sl.id_selera = pr.id_selera', 'left')
            ->join('pemantauan_risiko pm', 'pm.id_rtp = rtp.id_rtp', 'left')
            ->join('bukti_pemantauan bp', 'bp.id_pemantauan = pm.id_pemantauan', 'left')
            ->orderBy('pb.kode_proses', 'ASC')
            ->orderBy('rtp.id_rtp', 'ASC');

        if ($globalTahun) {
            $builder->where('k.tahun', $globalTahun);
        }

        if ($globalTim) {
            $builder->where('k.id_tim', $globalTim);
        }

        if ($globalKegiatan) {
            $builder->where('k.id_kegiatan', $globalKegiatan);
        }

        // if ($filter && $filter !== 'semua') {
        //     if ($filter === 'Belum Dilaksanakan') {
        //         $builder->groupStart()
        //             ->where('pm.id_pemantauan IS NULL', null, false)
        //             ->orWhere('pm.status', 'Belum Dilaksanakan')
        //             ->groupEnd();
        //     } else {
        //         $builder->where('pm.status', $filter);
        //     }
        // }
        if ($filter && $filter !== 'semua') {
            $builder->where("($statusCase) = '{$filter}'", null, false);
        }

       $qCount = $this->db->table('rencana_penanganan_risiko rtp')
    ->select('COUNT(DISTINCT rtp.id_rtp) as total')
    ->join('evaluasi_risiko er', 'er.id_evaluasi = rtp.id_penilaian_awal')
    ->join('identifikasi_risiko ir', 'ir.id_identifikasi = er.id_identifikasi')
    ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
    ->join('konteks k', 'k.id_konteks = kpb.id_konteks')
    ->join('pemantauan_risiko pm', 'pm.id_rtp = rtp.id_rtp', 'left')
    ->join('bukti_pemantauan bp', 'bp.id_pemantauan = pm.id_pemantauan', 'left');

        if ($globalTahun) {
            $qCount->where('k.tahun', $globalTahun);
        }

        if ($globalTim) {
            $qCount->where('k.id_tim', $globalTim);
        }

        if ($globalKegiatan) {
            $qCount->where('k.id_kegiatan', $globalKegiatan);
        }

        if ($filter && $filter !== 'semua') {
    $qCount->where("($statusCase) = '{$filter}'", null, false);
}
        // if ($filter && $filter !== 'semua') {
        //     if ($filter === 'Belum Dilaksanakan') {
        //         $qCount->groupStart()
        //             ->where('pm.id_pemantauan IS NULL', null, false)
        //             ->orWhere('pm.status', 'Belum Dilaksanakan')
        //             ->groupEnd();
        //     } else {
        //         $qCount->where('pm.status', $filter);
        //     }
        // }

        $total = (int) ($qCount->get()->getRowArray()['total'] ?? 0);
        $rows  = $builder->limit($perPage, $offset)->get()->getResultArray();

        $grouped = [];

        foreach ($rows as $row) {
            $idIdentifikasi = $row['id_identifikasi'];

            if (!isset($grouped[$idIdentifikasi])) {
                $grouped[$idIdentifikasi] = [
                    'id_identifikasi'   => $row['id_identifikasi'],
                    'id_tim'            => $row['id_tim'],
                    'pernyataan_risiko' => $row['pernyataan_risiko'],
                    'penyebab_risiko'   => $row['penyebab_risiko'],
                    'dampak_risiko'     => $row['dampak_risiko'],
                    'kode_proses'       => $row['kode_proses'],
                    'uraian_proses'     => $row['uraian_proses'],
                    'nama_tim'          => $row['nama_tim'],
                    'nilai_risiko'      => $row['nilai_risiko'],
                    'warna_risiko'      => $row['warna_risiko'],
                    'level_kemungkinan' => $row['level_kemungkinan'],
                    'level_dampak'      => $row['level_dampak'],
                    'nama_selera'       => $row['nama_selera'],
                    'warna_selera'      => $row['warna_selera'],
                    'pemantauan_list'   => [],
                ];
            }

            $grouped[$idIdentifikasi]['pemantauan_list'][] = [
                'id_rtp'            => $row['id_rtp'],
                'uraian_rtp'        => $row['uraian_rtp'],
                'target_output'     => $row['target_output'],
                'target_waktu'      => $row['target_waktu'],
                'id_pemantauan'     => $row['id_pemantauan'],
                'realisasi_output'  => $row['realisasi_output'],
                'realisasi_waktu'   => $row['realisasi_waktu'],
                'status'            => $row['status'],
                'catatan'           => $row['catatan'],
                'updated_at'        => $row['pemantauan_updated_at'],
            ];
        }

        uasort($grouped, fn($a, $b) => ($b['nilai_risiko'] ?? 0) <=> ($a['nilai_risiko'] ?? 0));
        $i = 1;
        foreach ($grouped as &$item) {
            $item['no_prioritas'] = $i++;
        }

        foreach ($rows as &$row) {
            $row['jumlah_bukti'] = !empty($row['id_pemantauan'])
                ? $this->buktiModel->countByPemantauan((int) $row['id_pemantauan'])
                : 0;
        }
        unset($row);

        $from       = $total > 0 ? $offset + 1 : 0;
        $to         = min($offset + $perPage, $total);
        $totalPages = (int) ceil($total / max($perPage, 1));

        $summary = $this->getSummary();

        return view('pemantauan_risiko/index', [
            'grouped' => $grouped,
            'total'              => $total,
            'from'               => $from,
            'to'                 => $to,
            'perPage'            => $perPage,
            'filter'             => $filter,
            'pager'              => [
                'currentPage' => $page,
                'totalPages'  => $totalPages,
                'perPage'     => $perPage,
                'total'       => $total,
            ],
            'totalRtp'           => $summary['totalRtp'],
            'totalSudahDipantau' => $summary['totalSudahDipantau'],
            'distribusi'         => $summary['distribusi'],
            'statusList'         => ['Belum Dilaksanakan', 'Dalam Proses', 'Selesai', 'Terlambat'],
            'role' => session('role'),
            'id_tim_user' => session('id_tim'),
        ]);
    }

    public function ajaxTable()
    {
        if (!$this->request->isAJAX()) return redirect()->back();

        $db = $this->db;

        $globalTahun    = session('global_tahun');
        $globalTim      = session('global_id_tim');
        $globalKegiatan = session('global_id_kegiatan');

        $activeKonteks = [
            'id_tim' => $globalTim
        ];

        $filter  = $this->request->getGet('filter');

        $statusCase = "
        CASE
            WHEN pm.realisasi_waktu IS NOT NULL
                AND DATE_TRUNC('month', pm.realisasi_waktu)
                    > DATE_TRUNC('month', rtp.target_waktu)
            THEN 'Terlambat'

            WHEN pm.realisasi_output IS NOT NULL
                AND pm.realisasi_waktu IS NOT NULL
                AND bp.id_bukti IS NOT NULL
            THEN 'Selesai'

            WHEN DATE_TRUNC('month', CURRENT_DATE)
                > DATE_TRUNC('month', rtp.target_waktu)
            THEN 'Terlambat'

            WHEN pm.realisasi_output IS NULL
                AND pm.realisasi_waktu IS NULL
                AND bp.id_bukti IS NULL
            THEN 'Belum Dilaksanakan'

            ELSE 'Dalam Proses'
        END
        ";
        $perPage = (int) ($this->request->getGet('perPage') ?? 10);
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $offset  = ($page - 1) * $perPage;

        /* ================= QUERY ================= */
        $builder = $db->table('rencana_penanganan_risiko rtp')
            ->select("
                ir.id_identifikasi,
                rtp.id_rtp,
                rtp.uraian_rtp,
                rtp.target_output,
                rtp.target_waktu,
                k.id_tim,
                pb.kode_proses,
                pb.uraian_proses,
                sk.nama_tim,
                pr.nilai_risiko,
                pr.warna_risiko,
                sl.nama_level as nama_selera,
                pm.id_pemantauan,
                pm.realisasi_output,
                pm.realisasi_waktu,
                CASE
                    WHEN pm.realisasi_waktu IS NOT NULL
                        AND DATE_TRUNC('month', pm.realisasi_waktu)
                            > DATE_TRUNC('month', rtp.target_waktu)
                    THEN 'Terlambat'

                    WHEN pm.realisasi_output IS NOT NULL
                        AND pm.realisasi_waktu IS NOT NULL
                        AND bp.id_bukti IS NOT NULL
                    THEN 'Selesai'

                    WHEN DATE_TRUNC('month', CURRENT_DATE)
                        > DATE_TRUNC('month', rtp.target_waktu)
                    THEN 'Terlambat'

                    WHEN pm.realisasi_output IS NULL
                        AND pm.realisasi_waktu IS NULL
                        AND bp.id_bukti IS NULL
                    THEN 'Belum Dilaksanakan'

                    ELSE 'Dalam Proses'
                END as status
            ")
            ->join('evaluasi_risiko er', 'er.id_evaluasi = rtp.id_penilaian_awal')
            ->join('identifikasi_risiko ir', 'ir.id_identifikasi = er.id_identifikasi')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks')
            ->join('proses_bisnis pb', 'pb.id_proses = kpb.id_proses')
            ->join('tim_kerja sk', 'sk.id_tim = k.id_tim', 'left')
            ->join('penilaian_risiko pr', 'pr.id_penilaian = er.id_penilaian', 'left')
            ->join('selera_risiko sl', 'sl.id_selera = pr.id_selera', 'left')
            ->join('pemantauan_risiko pm', 'pm.id_rtp = rtp.id_rtp', 'left')
            ->join('bukti_pemantauan bp', 'bp.id_pemantauan = pm.id_pemantauan', 'left');

        if ($globalTahun) {
            $builder->where('k.tahun', $globalTahun);
        }

        if ($globalTim) {
            $builder->where('k.id_tim', $globalTim);
        }

        if ($globalKegiatan) {
            $builder->where('k.id_kegiatan', $globalKegiatan);
        }

        // if ($filter && $filter !== 'semua') {
        //     if ($filter === 'Belum Dilaksanakan') {
        //         $builder->where('pm.id_pemantauan IS NULL', null, false);
        //     } else {
        //         $builder->where('pm.status', $filter);
        //     }
        // }
        if ($filter && $filter !== 'semua') {
            $builder->where("($statusCase) = '{$filter}'", null, false);
        }

        /* ================= TOTAL ================= */
        $qCount = $db->table('rencana_penanganan_risiko rtp')
    ->select('COUNT(DISTINCT rtp.id_rtp) as total')
            ->join('evaluasi_risiko er', 'er.id_evaluasi = rtp.id_penilaian_awal')
            ->join('identifikasi_risiko ir', 'ir.id_identifikasi = er.id_identifikasi')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks')
            ->join('pemantauan_risiko pm', 'pm.id_rtp = rtp.id_rtp', 'left')
            ->join('bukti_pemantauan bp', 'bp.id_pemantauan = pm.id_pemantauan', 'left');

        if ($globalTahun) {
            $qCount->where('k.tahun', $globalTahun);
        }

        if ($globalTim) {
            $qCount->where('k.id_tim', $globalTim);
        }

        if ($globalKegiatan) {
            $qCount->where('k.id_kegiatan', $globalKegiatan);
        }

        if ($filter && $filter !== 'semua') {
    $qCount->where("($statusCase) = '{$filter}'", null, false);
}
        $total = (int) ($qCount->get()->getRowArray()['total'] ?? 0);

        $rows = $builder->limit($perPage, $offset)->get()->getResultArray();

        /* ================= GROUPING ================= */
        $grouped = [];

        foreach ($rows as $row) {
            $idIdentifikasi = $row['id_identifikasi'];

            if (!isset($grouped[$idIdentifikasi])) {
                $grouped[$idIdentifikasi] = [
                    'id_identifikasi'   => $row['id_identifikasi'],
                    'id_tim'            => $row['id_tim'],
                    'kode_proses'       => $row['kode_proses'],
                    'uraian_proses'     => $row['uraian_proses'],
                    'nama_tim'          => $row['nama_tim'],
                    'nilai_risiko'      => $row['nilai_risiko'],
                    'nama_selera'       => $row['nama_selera'],
                    'pemantauan_list'   => [],
                ];
            }

            $grouped[$idIdentifikasi]['pemantauan_list'][] = [
                'id_rtp'           => $row['id_rtp'],
                'uraian_rtp'       => $row['uraian_rtp'],
                'target_output'    => $row['target_output'],
                'target_waktu'     => $row['target_waktu'],
                'id_pemantauan'    => $row['id_pemantauan'],
                'realisasi_output' => $row['realisasi_output'],
                'realisasi_waktu'  => $row['realisasi_waktu'],
                'status'           => $row['status'],
            ];
        }

        /* ================= RETURN VIEW ================= */
        return view('pemantauan_risiko/_table_section', [
            'grouped' => $grouped,
            'total'   => $total,
            'from'    => $total > 0 ? $offset + 1 : 0,
            'to'      => min($offset + $perPage, $total),
            'perPage' => $perPage,
            'pager'   => [
                'currentPage' => $page,
                'totalPages'  => (int) ceil($total / $perPage),
            ],
            'filter'  => $filter,
        ]);
    }

    /* DETAIL (AJAX) [FIX] Join sasaran_kinerja pakai id_konteks_proses, sama persis dengan EvaluasiRisikoController */
    public function detail($idRtp)
    {
        try {
            // Coba ambil via model jika pemantauan sudah ada
            $data = $this->pemantauanModel->getByRtp((int) $idRtp);

            if (!$data) {
                // Fallback: belum ada record pemantauan, ambil dari RTP saja
                $data = $this->db->table('rencana_penanganan_risiko rtp')
                    ->select('
                    rtp.id_rtp,
                     k.id_tim,
                    rtp.uraian_rtp,
                    rtp.target_output,
                    rtp.target_waktu,
                    er.id_evaluasi,
                    ir.pernyataan_risiko,
                    ir.penyebab_risiko,
                    ir.dampak_risiko,
                    pb.kode_proses,
                    pb.uraian_proses,
                    sk.nama_tim,
                    k.tahun,
                    ss.uraian_sasaran,
                    sk_kinerja.uraian_sasaran as uraian_sasaran_kinerja,
                    g.nama as nama_pengelola
                ')
                    ->join('evaluasi_risiko er',        'er.id_evaluasi = rtp.id_penilaian_awal')
                    ->join('identifikasi_risiko ir',    'ir.id_identifikasi = er.id_identifikasi')
                    ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
                    ->join('proses_bisnis pb',          'pb.id_proses = kpb.id_proses')
                    ->join('konteks k',                 'k.id_konteks = kpb.id_konteks')
                    ->join('tim_kerja sk',           'sk.id_tim = k.id_tim',           'left')
                    ->join('sasaran_strategis ss',      'ss.id_sasaran_strategis = k.id_sasaran_strategis', 'left')
                    // [FIX] Sama dengan EvaluasiRisikoController: join via id_konteks_proses
                    ->join('sasaran_kinerja sk_kinerja', 'sk_kinerja.id_konteks_proses = ir.id_konteks_proses', 'left')
                    ->join('pengelola_risiko g',        'g.id = k.pengelola_risiko_id',                     'left')
                    ->where('rtp.id_rtp', $idRtp)
                    ->get()->getRowArray();

                if (!$data) {
                    return $this->response->setStatusCode(404)->setJSON([
                        'status'  => 'error',
                        'message' => 'Data RTP tidak ditemukan.',
                    ]);
                }

                $data['id_pemantauan']         = null;
                $data['realisasi_output']      = null;
                $data['realisasi_waktu']       = null;
                $data['status']                = 'Belum Dilaksanakan';
                $data['catatan']               = null;
                $data['pemantauan_updated_at'] = null;
            }

            $data['bukti_list'] = !empty($data['id_pemantauan'])
                ? $this->buktiModel->getByPemantauan((int) $data['id_pemantauan'])
                : [];

            return $this->response->setJSON($data);

        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function store()
    {
        log_message('error', 'POST: ' . json_encode($this->request->getPost()));
        try {
            $idRtp           = $this->request->getPost('id_rtp');
            $realisasiOutput = $this->request->getPost('realisasi_output');
            $realisasiWaktuInput = $this->request->getPost('realisasi_waktu');

            $realisasiWaktu = null;
            if (!empty($realisasiWaktuInput)) {
                // convert YYYY-MM → YYYY-MM-01 00:00:00
                $realisasiWaktu = $realisasiWaktuInput . '-01 00:00:00';
            }
            $catatan         = $this->request->getPost('catatan') ?: null;

            if (empty($idRtp)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => 'ID RTP tidak ditemukan.',
                ]);
            }

            if (!$this->validateTimAccessByRtp($idRtp)) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Tidak punya akses',
                ]);
            }

            if (empty($realisasiOutput)) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status'  => 'error',
                    'message' => 'Realisasi output wajib diisi.',
                ]);
            }

            $link = trim($this->request->getPost('bukti_link') ?? '');

            $this->db->transStart();

            $idPemantauan = $this->pemantauanModel->upsertByRtp((int) $idRtp, [
                'realisasi_output' => $realisasiOutput,
                'realisasi_waktu'  => $realisasiWaktu,
                'catatan'          => $catatan,
            ]);

            if (!$idPemantauan) {
                throw new \Exception('Gagal insert pemantauan');
            }

            if (!empty($link)) {

                if (!filter_var($link, FILTER_VALIDATE_URL)) {
                    return $this->response->setStatusCode(422)->setJSON([
                        'status'  => 'error',
                        'message' => 'Format link tidak valid.',
                    ]);
                }

                $this->buktiModel->hapusSemuaByPemantauan((int) $idPemantauan);
                $this->buktiModel->simpanLink((int) $idPemantauan, $link);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                log_message('error', 'DB ERROR: ' . json_encode($this->db->error()));

                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $this->db->error(),
                ]);
            }

            $buktiList = $this->buktiModel->getByPemantauan((int) $idPemantauan);

            return $this->response->setJSON([
                'status'        => 'success',
                'message'       => 'Pemantauan berhasil disimpan.',
                'csrf_token'    => csrf_hash(),
                'bukti_list'    => $buktiList,
                'id_pemantauan' => $idPemantauan,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Pemantauan Store Error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /* DELETE BUKTI */
    public function deleteBukti($idBukti)
    {
        try {
            $bukti = $this->buktiModel->find($idBukti);

            if (!$bukti) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status'  => 'error',
                    'message' => 'Bukti tidak ditemukan',
                ]);
            }

            $pemantauan = $this->pemantauanModel
                ->where('id_pemantauan', $bukti['id_pemantauan'])
                ->first();

            if (!$pemantauan) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status'  => 'error',
                    'message' => 'Pemantauan tidak ditemukan',
                ]);
            }

            if (!$this->validateTimAccessByRtp($pemantauan['id_rtp'])) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Tidak punya akses',
                ]);
            }

            $berhasil = $this->buktiModel->hapus((int) $idBukti);

            if (!$berhasil) {
                return $this->response->setStatusCode(500)->setJSON([
                    'status'  => 'error',
                    'message' => 'Gagal menghapus bukti',
                ]);
            }

            return $this->response->setJSON([
                'status'     => 'success',
                'message'    => 'File bukti berhasil dihapus.',
                'csrf_token' => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Pemantauan DeleteBukti Error: ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /* DELETE PEMANTAUAN */
    public function delete($idRtp)
    {
        try {
            $pemantauan = $this->pemantauanModel->where('id_rtp', $idRtp)->first();

            if (!$pemantauan) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status'  => 'error',
                    'message' => 'Data pemantauan tidak ditemukan.',
                ]);
            }

            if (!$this->validateTimAccessByRtp($idRtp)) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Tidak punya akses',
                ]);
            }

            $this->db->transStart();
            $this->buktiModel->hapusSemuaByPemantauan((int) $pemantauan['id_pemantauan']);
            $this->pemantauanModel->delete($pemantauan['id_pemantauan']);
            $this->db->transComplete();

            return $this->response->setJSON([
                'status'     => 'success',
                'message'    => 'Data pemantauan berhasil dihapus.',
                'csrf_token' => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Pemantauan Delete Error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
