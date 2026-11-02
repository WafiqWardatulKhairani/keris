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

    private function validateTimAccessByRisiko($idIdentifikasi): bool
    {
        $row = $this->db->table('identifikasi_risiko ir')
            ->select('k.id_tim')
            ->join(
                'konteks_proses_bisnis kpb',
                'kpb.id_konteks_proses = ir.id_konteks_proses'
            )
            ->join(
                'konteks k',
                'k.id_konteks = kpb.id_konteks'
            )
            ->where('ir.id_identifikasi', $idIdentifikasi)
            ->get()
            ->getRow();

        if (!$row) {
            return false;
        }

        $role = session('user_role');

        // Admin boleh mengubah semua risiko
        if ($role === 'admin') {
            return true;
        }

        // Hanya operator yang boleh mengubah selain admin
        if ($role !== 'operator') {
            return false;
        }

        // Ambil seluruh tim yang dimiliki operator
        $userTim = session('user_tim') ?? [];

        // Fallback untuk akun yang masih menggunakan relasi tim lama
        if (empty($userTim) && session('id_tim')) {
            $userTim = [session('id_tim')];
        }

        return in_array((string) $row->id_tim, array_map('strval', $userTim), true);
    }

    public function updateKeterjadian()
{
    $idIdentifikasi = $this->request->getPost('id_identifikasi');
    $keterjadian    = $this->request->getPost('keterjadian_risiko');
    $jumlah         = $this->request->getPost('jumlah_keterjadian');

    // Validasi ID risiko
    if (
        !$idIdentifikasi ||
        !ctype_digit((string) $idIdentifikasi)
    ) {
        return $this->response->setStatusCode(400)->setJSON([
            'success' => false,
            'message' => 'ID risiko tidak valid.'
        ]);
    }

    // Validasi status keterjadian
    if (!in_array($keterjadian, ['T', 'TT', 'BT'], true)) {
        return $this->response->setStatusCode(400)->setJSON([
            'success' => false,
            'message' => 'Status keterjadian tidak valid.'
        ]);
    }

    // Validasi jumlah
    if (
        $jumlah === null ||
        filter_var($jumlah, FILTER_VALIDATE_INT) === false ||
        (int) $jumlah < 0
    ) {
        return $this->response->setStatusCode(400)->setJSON([
            'success' => false,
            'message' => 'Jumlah keterjadian harus berupa bilangan bulat minimal 0.'
        ]);
    }

    // Pastikan operator hanya mengubah risiko timnya
    if (!$this->validateTimAccessByRisiko($idIdentifikasi)) {
        return $this->response->setStatusCode(403)->setJSON([
            'success' => false,
            'message' => 'Kamu tidak memiliki akses untuk mengubah risiko ini.'
        ]);
    }

    // Jumlah hanya berlaku jika risiko berstatus Terjadi
    $jumlah = $keterjadian === 'T' ? (int) $jumlah : 0;

    $updated = $this->db->table('identifikasi_risiko')
        ->where('id_identifikasi', $idIdentifikasi)
        ->update([
            'keterjadian_risiko' => $keterjadian,
            'jumlah_keterjadian' => $jumlah,
        ]);

    if (!$updated) {
        return $this->response->setStatusCode(500)->setJSON([
            'success' => false,
            'message' => 'Gagal menyimpan keterjadian risiko.'
        ]);
    }

    return $this->response->setJSON([
        'success' => true,
        'message' => 'Keterjadian risiko berhasil disimpan.',
        'data' => [
            'keterjadian_risiko' => $keterjadian,
            'jumlah_keterjadian' => $jumlah,
        ],
        'csrfHash' => csrf_hash(),
    ]);
}

    public function index()
    {
        $globalTahun    = session('global_tahun');
        $globalTim      = session('global_id_tim');
        $globalKegiatan = session('global_id_kegiatan');

        $perPage = max(1, (int) ($this->request->getGet('perPage') ?? 10));
        $page    = max(1, (int) ($this->request->getGet('page') ?? 1));
        $offset  = ($page - 1) * $perPage;
        $filter  = $this->request->getGet('filter');

        // Status pemantauan RTP
        $statusCase = "
        CASE
            WHEN pm.realisasi_waktu IS NOT NULL
                AND DATE_TRUNC('month', pm.realisasi_waktu)
                    > DATE_TRUNC('month', rtp.target_waktu)
            THEN 'Terlambat'

            WHEN pm.realisasi_output IS NOT NULL
                AND pm.realisasi_waktu IS NOT NULL
                AND EXISTS (
                    SELECT 1
                    FROM bukti_pemantauan bp
                    WHERE bp.id_pemantauan = pm.id_pemantauan
                )
            THEN 'Selesai'

            WHEN DATE_TRUNC('month', CURRENT_DATE)
                > DATE_TRUNC('month', rtp.target_waktu)
            THEN 'Terlambat'

            WHEN pm.realisasi_output IS NULL
                AND pm.realisasi_waktu IS NULL
                AND NOT EXISTS (
                    SELECT 1
                    FROM bukti_pemantauan bp
                    WHERE bp.id_pemantauan = pm.id_pemantauan
                )
            THEN 'Belum Dilaksanakan'

            ELSE 'Dalam Proses'
        END
    ";

        // Filter status yang diizinkan
        $statusValid = [
            'Belum Dilaksanakan',
            'Dalam Proses',
            'Selesai',
            'Terlambat'
        ];

        if (!in_array($filter, $statusValid, true)) {
            $filter = 'semua';
        }

        /*
    |--------------------------------------------------------------------------
    | QUERY DASAR RISIKO
    |--------------------------------------------------------------------------
    | Mulai dari identifikasi_risiko.
    | LEFT JOIN memungkinkan risiko tanpa RTP tetap muncul.
    */
        $buildQuery = function () use (
            $globalTahun,
            $globalTim,
            $globalKegiatan
        ) {
            $builder = $this->db->table('identifikasi_risiko ir')
                ->join(
                    'konteks_proses_bisnis kpb',
                    'kpb.id_konteks_proses = ir.id_konteks_proses',
                    'left'
                )
                ->join(
                    'konteks k',
                    'k.id_konteks = kpb.id_konteks',
                    'left'
                )
                ->join(
                    'evaluasi_risiko er',
                    'er.id_identifikasi = ir.id_identifikasi',
                    'left'
                )
                ->join(
                    'rencana_penanganan_risiko rtp',
                    'rtp.id_penilaian_awal = er.id_evaluasi',
                    'left'
                )
                ->join(
                    'pemantauan_risiko pm',
                    'pm.id_rtp = rtp.id_rtp',
                    'left'
                );

            if ($globalTahun) {
                $builder->where('k.tahun', $globalTahun);
            }

            if ($globalTim) {
                $builder->where('k.id_tim', $globalTim);
            }

            if ($globalKegiatan) {
                $builder->where('k.id_kegiatan', $globalKegiatan);
            }

            return $builder;
        };

        /*
    |--------------------------------------------------------------------------
    | HITUNG TOTAL RISIKO
    |--------------------------------------------------------------------------
    */
        $qCount = $buildQuery()
            ->select('COUNT(DISTINCT ir.id_identifikasi) AS total');

        if ($filter !== 'semua') {
            $qCount->where("($statusCase) =", $filter, false);
        }

        $total = (int) (
            $qCount->get()->getRowArray()['total'] ?? 0
        );

        /*
    |--------------------------------------------------------------------------
    | AMBIL ID RISIKO UNTUK PAGINATION
    |--------------------------------------------------------------------------
    | Pagination berdasarkan risiko, bukan jumlah RTP.
    */
        $qIds = $buildQuery()
            ->select('
            ir.id_identifikasi,
            MAX(pr.nilai_risiko) AS nilai_risiko
        ')
            ->join(
                'penilaian_risiko pr',
                'pr.id_penilaian = er.id_penilaian',
                'left'
            )
            ->groupBy('ir.id_identifikasi')
            ->orderBy('MAX(pr.nilai_risiko)', 'DESC')
            ->orderBy('ir.id_identifikasi', 'ASC');

        if ($filter !== 'semua') {
            $qIds->where("($statusCase) =", $filter, false);
        }

        $idRows = $qIds
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        $riskIds = array_column($idRows, 'id_identifikasi');

        /*
    |--------------------------------------------------------------------------
    | AMBIL DETAIL RISIKO DAN SELURUH RTP-NYA
    |--------------------------------------------------------------------------
    */
        $grouped = [];

        if (!empty($riskIds)) {
            $rows = $this->db->table('identifikasi_risiko ir')
                ->select("
                ir.id_identifikasi,
                ir.keterjadian_risiko,
                ir.jumlah_keterjadian,
                ir.pernyataan_risiko,
                ir.penyebab_risiko,
                ir.dampak_risiko,

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

                km.level AS level_kemungkinan,
                kd.level AS level_dampak,

                sl.nama_level AS nama_selera,
                sl.warna AS warna_selera,

                pm.id_pemantauan,
                pm.realisasi_output,
                pm.realisasi_waktu,
                pm.catatan,
                pm.updated_at AS pemantauan_updated_at,

                $statusCase AS status
            ")
                ->join(
                    'konteks_proses_bisnis kpb',
                    'kpb.id_konteks_proses = ir.id_konteks_proses',
                    'left'
                )
                ->join(
                    'konteks k',
                    'k.id_konteks = kpb.id_konteks',
                    'left'
                )
                ->join(
                    'proses_bisnis pb',
                    'pb.id_proses = kpb.id_proses',
                    'left'
                )
                ->join(
                    'tim_kerja sk',
                    'sk.id_tim = k.id_tim',
                    'left'
                )
                ->join(
                    'evaluasi_risiko er',
                    'er.id_identifikasi = ir.id_identifikasi',
                    'left'
                )
                ->join(
                    'penilaian_risiko pr',
                    'pr.id_penilaian = er.id_penilaian',
                    'left'
                )
                ->join(
                    'kriteria_kemungkinan km',
                    'km.id_kriteria = pr.id_kemungkinan',
                    'left'
                )
                ->join(
                    'kriteria_dampak kd',
                    'kd.id_kriteria = pr.id_dampak',
                    'left'
                )
                ->join(
                    'selera_risiko sl',
                    'sl.id_selera = pr.id_selera',
                    'left'
                )
                ->join(
                    'rencana_penanganan_risiko rtp',
                    'rtp.id_penilaian_awal = er.id_evaluasi',
                    'left'
                )
                ->join(
                    'pemantauan_risiko pm',
                    'pm.id_rtp = rtp.id_rtp',
                    'left'
                )
                ->whereIn('ir.id_identifikasi', $riskIds)
                ->orderBy('pb.kode_proses', 'ASC')
                ->orderBy('ir.id_identifikasi', 'ASC')
                ->orderBy('rtp.id_rtp', 'ASC')
                ->get()
                ->getResultArray();

            /*
        |--------------------------------------------------------------------------
        | KELOMPOKKAN BERDASARKAN IDENTIFIKASI RISIKO
        |--------------------------------------------------------------------------
        */
            foreach ($rows as $row) {
                $id = $row['id_identifikasi'];

                if (!isset($grouped[$id])) {
                    $grouped[$id] = [
                        'id_identifikasi'   => $id,
                        'id_tim'            => $row['id_tim'],
                        'keterjadian_risiko' => $row['keterjadian_risiko'] ?? 'TT',
                        'jumlah_keterjadian' => $row['jumlah_keterjadian'] ?? 0,
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

                // Tambahkan RTP jika memang tersedia
                if (!empty($row['id_rtp'])) {
                    $grouped[$id]['pemantauan_list'][] = [
                        'id_rtp'           => $row['id_rtp'],
                        'uraian_rtp'       => $row['uraian_rtp'],
                        'target_output'    => $row['target_output'],
                        'target_waktu'     => $row['target_waktu'],
                        'id_pemantauan'    => $row['id_pemantauan'],
                        'realisasi_output' => $row['realisasi_output'],
                        'realisasi_waktu'  => $row['realisasi_waktu'],
                        'status'           => $row['status'],
                        'catatan'          => $row['catatan'],
                        'updated_at'       => $row['pemantauan_updated_at'],
                    ];
                }
            }

            // Risiko tanpa RTP tetap mendapat satu baris tampilan
            foreach ($grouped as &$item) {
                if (empty($item['pemantauan_list'])) {
                    $item['pemantauan_list'][] = [
                        'id_rtp'           => null,
                        'uraian_rtp'       => null,
                        'target_output'    => null,
                        'target_waktu'     => null,
                        'id_pemantauan'    => null,
                        'realisasi_output' => null,
                        'realisasi_waktu'  => null,
                        'status'           => null,
                        'catatan'          => null,
                        'updated_at'       => null,
                    ];
                }
            }
            unset($item);
        }

        /*
    |--------------------------------------------------------------------------
    | NOMOR PRIORITAS
    |--------------------------------------------------------------------------
    */
        $i = $offset + 1;

        foreach ($grouped as &$item) {
            $item['no_prioritas'] = $i++;
        }

        unset($item);

        $from       = $total > 0 ? $offset + 1 : 0;
        $to         = min($offset + $perPage, $total);
        $totalPages = (int) ceil($total / $perPage);

        $summary = $this->getSummary();

        return view('pemantauan_risiko/index', [
            'grouped'            => $grouped,
            'total'              => $total,
            'from'               => $from,
            'to'                 => $to,
            'perPage'             => $perPage,
            'filter'              => $filter,
            'pager'               => [
                'currentPage' => $page,
                'totalPages'  => $totalPages,
                'perPage'     => $perPage,
                'total'       => $total,
            ],
            'totalRtp'            => $summary['totalRtp'],
            'totalSudahDipantau'  => $summary['totalSudahDipantau'],
            'distribusi'          => $summary['distribusi'],
            'statusList'          => [
                'Belum Dilaksanakan',
                'Dalam Proses',
                'Selesai',
                'Terlambat'
            ],
            'role'                => session('user_role'),
            'id_tim_user'         => session('id_tim'),
        ]);
    }


    public function ajaxTable()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $db = $this->db;

        // Konteks aktif
        $globalTahun    = session('global_tahun');
        $globalTim      = session('global_id_tim');
        $globalKegiatan = session('global_id_kegiatan');

        // Pagination berdasarkan risiko
        $perPage = max(1, (int) ($this->request->getGet('perPage') ?? 10));
        $page    = max(1, (int) ($this->request->getGet('page') ?? 1));
        $offset  = ($page - 1) * $perPage;

        $filter = $this->request->getGet('filter') ?? 'semua';

        $statusValid = [
            'Belum Dilaksanakan',
            'Dalam Proses',
            'Selesai',
            'Terlambat'
        ];

        if (!in_array($filter, $statusValid, true)) {
            $filter = 'semua';
        }

        // Status RTP
        $statusCase = "
        CASE
            WHEN pm.realisasi_waktu IS NOT NULL
                AND DATE_TRUNC('month', pm.realisasi_waktu)
                    > DATE_TRUNC('month', rtp.target_waktu)
            THEN 'Terlambat'

            WHEN pm.realisasi_output IS NOT NULL
                AND pm.realisasi_waktu IS NOT NULL
                AND EXISTS (
                    SELECT 1
                    FROM bukti_pemantauan bp
                    WHERE bp.id_pemantauan = pm.id_pemantauan
                )
            THEN 'Selesai'

            WHEN DATE_TRUNC('month', CURRENT_DATE)
                > DATE_TRUNC('month', rtp.target_waktu)
            THEN 'Terlambat'

            WHEN pm.realisasi_output IS NULL
                AND pm.realisasi_waktu IS NULL
                AND NOT EXISTS (
                    SELECT 1
                    FROM bukti_pemantauan bp
                    WHERE bp.id_pemantauan = pm.id_pemantauan
                )
            THEN 'Belum Dilaksanakan'

            ELSE 'Dalam Proses'
        END
    ";

        // Query dasar: mulai dari identifikasi risiko
        $buildQuery = function () use (
            $db,
            $globalTahun,
            $globalTim,
            $globalKegiatan
        ) {
            $builder = $db->table('identifikasi_risiko ir')
                ->join(
                    'konteks_proses_bisnis kpb',
                    'kpb.id_konteks_proses = ir.id_konteks_proses',
                    'left'
                )
                ->join(
                    'konteks k',
                    'k.id_konteks = kpb.id_konteks',
                    'left'
                )
                ->join(
                    'evaluasi_risiko er',
                    'er.id_identifikasi = ir.id_identifikasi',
                    'left'
                )
                ->join(
                    'rencana_penanganan_risiko rtp',
                    'rtp.id_penilaian_awal = er.id_evaluasi',
                    'left'
                )
                ->join(
                    'pemantauan_risiko pm',
                    'pm.id_rtp = rtp.id_rtp',
                    'left'
                );

            if ($globalTahun) {
                $builder->where('k.tahun', $globalTahun);
            }

            if ($globalTim) {
                $builder->where('k.id_tim', $globalTim);
            }

            if ($globalKegiatan) {
                $builder->where('k.id_kegiatan', $globalKegiatan);
            }

            return $builder;
        };

        // Filter status
        $applyStatusFilter = function ($builder) use ($filter, $statusCase) {
            if ($filter !== 'semua') {
                $builder->where(
                    "($statusCase) = " . $this->db->escape($filter),
                    null,
                    false
                );
            }

            return $builder;
        };

        // Hitung jumlah risiko
        $qCount = $buildQuery()
            ->select('COUNT(DISTINCT ir.id_identifikasi) AS total');

        $applyStatusFilter($qCount);

        $total = (int) (
            $qCount->get()->getRowArray()['total'] ?? 0
        );

        // Ambil ID risiko sesuai halaman
        $qIds = $buildQuery()
            ->select('
            ir.id_identifikasi,
            MAX(pr.nilai_risiko) AS nilai_risiko
        ')
            ->join(
                'penilaian_risiko pr',
                'pr.id_penilaian = er.id_penilaian',
                'left'
            )
            ->groupBy('ir.id_identifikasi')
            ->orderBy('MAX(pr.nilai_risiko)', 'DESC')
            ->orderBy('ir.id_identifikasi', 'ASC');

        $applyStatusFilter($qIds);

        $idRows = $qIds
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        $riskIds = array_column($idRows, 'id_identifikasi');

        $grouped = [];

        // Ambil seluruh RTP dari risiko pada halaman aktif
        if (!empty($riskIds)) {
            $rows = $db->table('identifikasi_risiko ir')
                ->select("
                ir.id_identifikasi,
                ir.keterjadian_risiko,
                ir.jumlah_keterjadian,
                ir.pernyataan_risiko,
                ir.penyebab_risiko,
                ir.dampak_risiko,

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

                km.level AS level_kemungkinan,
                kd.level AS level_dampak,

                sl.nama_level AS nama_selera,
                sl.warna AS warna_selera,

                pm.id_pemantauan,
                pm.realisasi_output,
                pm.realisasi_waktu,
                pm.catatan,
                pm.updated_at AS pemantauan_updated_at,

                $statusCase AS status
            ")
                ->join(
                    'konteks_proses_bisnis kpb',
                    'kpb.id_konteks_proses = ir.id_konteks_proses',
                    'left'
                )
                ->join(
                    'konteks k',
                    'k.id_konteks = kpb.id_konteks',
                    'left'
                )
                ->join(
                    'proses_bisnis pb',
                    'pb.id_proses = kpb.id_proses',
                    'left'
                )
                ->join(
                    'tim_kerja sk',
                    'sk.id_tim = k.id_tim',
                    'left'
                )
                ->join(
                    'evaluasi_risiko er',
                    'er.id_identifikasi = ir.id_identifikasi',
                    'left'
                )
                ->join(
                    'penilaian_risiko pr',
                    'pr.id_penilaian = er.id_penilaian',
                    'left'
                )
                ->join(
                    'kriteria_kemungkinan km',
                    'km.id_kriteria = pr.id_kemungkinan',
                    'left'
                )
                ->join(
                    'kriteria_dampak kd',
                    'kd.id_kriteria = pr.id_dampak',
                    'left'
                )
                ->join(
                    'selera_risiko sl',
                    'sl.id_selera = pr.id_selera',
                    'left'
                )
                ->join(
                    'rencana_penanganan_risiko rtp',
                    'rtp.id_penilaian_awal = er.id_evaluasi',
                    'left'
                )
                ->join(
                    'pemantauan_risiko pm',
                    'pm.id_rtp = rtp.id_rtp',
                    'left'
                )
                ->whereIn('ir.id_identifikasi', $riskIds)
                ->orderBy('pb.kode_proses', 'ASC')
                ->orderBy('ir.id_identifikasi', 'ASC')
                ->orderBy('rtp.id_rtp', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                $id = $row['id_identifikasi'];

                if (!isset($grouped[$id])) {
                    $grouped[$id] = [
                        'id_identifikasi'    => $id,
                        'id_tim'             => $row['id_tim'],
                        'keterjadian_risiko' => $row['keterjadian_risiko'] ?? 'TT',
                        'jumlah_keterjadian' => $row['jumlah_keterjadian'] ?? 0,
                        'pernyataan_risiko'  => $row['pernyataan_risiko'],
                        'penyebab_risiko'    => $row['penyebab_risiko'],
                        'dampak_risiko'      => $row['dampak_risiko'],
                        'kode_proses'        => $row['kode_proses'],
                        'uraian_proses'      => $row['uraian_proses'],
                        'nama_tim'           => $row['nama_tim'],
                        'nilai_risiko'       => $row['nilai_risiko'],
                        'warna_risiko'       => $row['warna_risiko'],
                        'level_kemungkinan'  => $row['level_kemungkinan'],
                        'level_dampak'       => $row['level_dampak'],
                        'nama_selera'        => $row['nama_selera'],
                        'warna_selera'       => $row['warna_selera'],
                        'pemantauan_list'    => [],
                    ];
                }

                if (!empty($row['id_rtp'])) {
                    $grouped[$id]['pemantauan_list'][] = [
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
            }

            // Risiko tanpa RTP tetap memiliki satu baris
            foreach ($grouped as &$item) {
                if (empty($item['pemantauan_list'])) {
                    $item['pemantauan_list'][] = [
                        'id_rtp'            => null,
                        'uraian_rtp'        => null,
                        'target_output'     => null,
                        'target_waktu'      => null,
                        'id_pemantauan'     => null,
                        'realisasi_output'  => null,
                        'realisasi_waktu'   => null,
                        'status'            => null,
                        'catatan'           => null,
                        'updated_at'        => null,
                    ];
                }
            }

            unset($item);
        }

        // Nomor prioritas
        $i = 1;

        foreach ($grouped as &$item) {
            $item['no_prioritas'] = $offset + $i++;
        }

        unset($item);

        $from = $total > 0 ? $offset + 1 : 0;
        $to   = min($offset + $perPage, $total);

        return view('pemantauan_risiko/_table_section', [
            'grouped' => $grouped,
            'total'   => $total,
            'from'    => $from,
            'to'      => $to,
            'perPage' => $perPage,
            'pager'   => [
                'currentPage' => $page,
                'totalPages'  => (int) ceil($total / $perPage),
                'perPage'     => $perPage,
                'total'       => $total,
            ],
            'filter' => $filter,
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
