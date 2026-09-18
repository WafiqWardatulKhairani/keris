<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\RencanaPenangananRisikoModel;
use App\Models\KonteksModel;
use App\Models\KriteriaKemungkinanModel;
use App\Models\KriteriaDampakModel;
use App\Models\MatriksRisikoModel;
use App\Models\SeleraRisikoModel;

class RencanaPenangananController extends BaseController
{
    protected $rtpModel;
    protected $db;

    public function __construct()
    {
        $this->rtpModel = new RencanaPenangananRisikoModel();
        $this->db       = \Config\Database::connect();
    }

    /* KRITERIA (dipakai index() dan offcanvas) */
    private function getKriteriaList(): array
    {
        return [
            'kriteriaKemungkinan' => $this->db->table('kriteria_kemungkinan')
                ->select('id_kriteria, level, nama_level')
                ->orderBy('level', 'ASC')
                ->get()->getResultArray(),

            'kriteriaDampak' => $this->db->table('kriteria_dampak')
                ->select('id_kriteria, level, nama_level')
                ->orderBy('level', 'ASC')
                ->get()->getResultArray(),
        ];
    }

    /* SUMMARY STATS */
    private function getSummary(
        ?int $idKonteks,
        $idTim = null,
        $idKegiatan = null,
        $tahun = null
    ): array {
        /* TOTAL risiko ber-opsi Mengurangi */
        $qTotal = $this->db->table('evaluasi_risiko er')
            ->join(
                'identifikasi_risiko ir',
                'ir.id_identifikasi = er.id_identifikasi'
            )
            ->join(
                'konteks_proses_bisnis kpb',
                'kpb.id_konteks_proses = ir.id_konteks_proses'
            )
            ->join(
                'konteks k',
                'k.id_konteks = kpb.id_konteks'
            )
            ->where('er.opsi_tindakan', 'Mengurangi');
        if ($idKonteks) {
            $qTotal->where('kpb.id_konteks', $idKonteks);
        }

        if ($idTim) {
            $qTotal->where('k.id_tim', $idTim);
        }

        if ($idKegiatan) {
            $qTotal->where('k.id_kegiatan', $idKegiatan);
        }

        if ($tahun) {
            $qTotal->where('k.tahun', $tahun);
        }

        $totalDitangani = $qTotal->countAllResults();

        /* SUDAH ada RTP */
        $qSudah = $this->db->table('evaluasi_risiko er')
            ->select('COUNT(DISTINCT er.id_evaluasi) AS total')
            ->join('identifikasi_risiko ir', 'ir.id_identifikasi = er.id_identifikasi')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks')
            ->join('rencana_penanganan_risiko rtp', 'rtp.id_penilaian_awal = er.id_evaluasi')
            ->where('er.opsi_tindakan', 'Mengurangi');

        if ($idKonteks) {
            $qSudah->where('kpb.id_konteks', $idKonteks);
        }

        if ($idTim) {
            $qSudah->where('k.id_tim', $idTim);
        }

        if ($idKegiatan) {
            $qSudah->where('k.id_kegiatan', $idKegiatan);
        }

        if ($tahun) {
            $qSudah->where('k.tahun', $tahun);
        }

        $totalSudah = (int) (
            $qSudah->get()->getRowArray()['total'] ?? 0
        );

        $totalBelum = max(0, $totalDitangani - $totalSudah);

        /* DISTRIBUSI LEVEL RISIKO */
        $levelRisiko = [
            'Sangat Rendah' => [
                'jumlah' => 0,
                'warna'  => 'biru'
            ],
            'Rendah' => [
                'jumlah' => 0,
                'warna'  => 'hijau'
            ],
            'Sedang' => [
                'jumlah' => 0,
                'warna'  => 'kuning'
            ],
            'Tinggi' => [
                'jumlah' => 0,
                'warna'  => 'oranye'
            ],
            'Sangat Tinggi' => [
                'jumlah' => 0,
                'warna'  => 'merah'
            ],
        ];
        $qDistribusi = $this->db->table('evaluasi_risiko er')
            ->select('sr_r.nama_level, sr_r.warna, COUNT(DISTINCT er.id_evaluasi) as total')
            ->join('identifikasi_risiko ir', 'ir.id_identifikasi = er.id_identifikasi')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('rencana_penanganan_risiko rtp', 'rtp.id_penilaian_awal = er.id_evaluasi')
            ->join('kriteria_kemungkinan km_r', 'km_r.id_kriteria = rtp.id_kemungkinan_residu', 'left')
            ->join('kriteria_dampak kd_r', 'kd_r.id_kriteria = rtp.id_dampak_residu', 'left')
            ->join('matriks_risiko mr_r', 'mr_r.level_kemungkinan = km_r.level AND mr_r.level_dampak = kd_r.level', 'left')
            ->join('selera_risiko sr_r', 'mr_r.nilai_risiko BETWEEN sr_r.nilai_min AND sr_r.nilai_max', 'left')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks')
            ->where('er.opsi_tindakan', 'Mengurangi')
            ->where('rtp.id_rtp IS NOT NULL', null, false)
            ->groupBy('sr_r.nama_level, sr_r.warna');
        if ($idKonteks) {
            $qDistribusi->where('kpb.id_konteks', $idKonteks);
        }

        if ($idTim) {
            $qDistribusi->where('k.id_tim', $idTim);
        }

        if ($idKegiatan) {
            $qDistribusi->where('k.id_kegiatan', $idKegiatan);
        }

        if ($tahun) {
            $qDistribusi->where('k.tahun', $tahun);
        }

        foreach ($qDistribusi->get()->getResultArray() as $row) {
            if (isset($levelRisiko[$row['nama_level']])) {

                $levelRisiko[$row['nama_level']]['jumlah']
                    = (int)$row['total'];

                $levelRisiko[$row['nama_level']]['warna']
                    = $row['warna'];
            }
        }

        return compact(
            'totalDitangani',
            'totalSudah',
            'totalBelum',
            'levelRisiko'
        );
    }

    private function validateTimAccessByIdentifikasi($idIdentifikasi): bool
    {
        $db = \Config\Database::connect();

        $row = $db->table('identifikasi_risiko ir')
            ->select('k.id_tim')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks')
            ->where('ir.id_identifikasi', $idIdentifikasi)
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
        $idKonteks = $this->request->getGet('id_konteks')
            ?? session('global_id_konteks');

        $idTim = $this->request->getGet('sk')
            ?? session('global_id_tim');

        $idKegiatan = $this->request->getGet('kg')
            ?? session('global_id_kegiatan');

        $tahun = $this->request->getGet('th')
            ?? session('global_tahun');

        $idPengelola = $this->request->getGet('pg');

        $activeKonteks = null;

        if ($idKonteks) {
            $activeKonteks = (new KonteksModel())->find($idKonteks);
        }

        if (!$activeKonteks && $idTim) {
            $activeKonteks = [
                'id_tim' => $idTim
            ];
        }

        /* ================= PAGINATION ================= */
        $perPage = (int) ($this->request->getGet('perPage') ?? 10);
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $offset  = ($page - 1) * $perPage;
        $filter  = $this->request->getGet('filter');

        /* ================= QUERY UTAMA (BALIK KE VERSI STABIL) ================= */
        $builder = $db->table('evaluasi_risiko er')
            ->select('
            er.id_evaluasi,
            er.opsi_tindakan,
            ir.id_identifikasi,
            ir.pernyataan_risiko,
            ir.penyebab_risiko,
            ir.dampak_risiko,
            k.id_tim,
            pb.kode_proses,
            pb.uraian_proses,
            tk.nama_tim,
            pr.id_penilaian,
            pr.nilai_risiko,
            pr.warna_risiko,
            km.level      as level_kemungkinan,
            kd.level      as level_dampak,
            mr_r.nilai_risiko as nilai_sr_residu,
            sr_r.nama_level as nama_selera_residu,
            sr_r.warna as warna_selera_residu,
            sl.nama_level as nama_selera,
            sl.warna      as warna_selera,
            rtp.id_rtp,
            rtp.uraian_rtp,
            rtp.target_output,
            rtp.target_waktu,
            km_r.level      as level_kemungkinan_residu,
            kd_r.level      as level_dampak_residu
        ')
            ->join('identifikasi_risiko ir', 'ir.id_identifikasi = er.id_identifikasi')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('proses_bisnis pb', 'pb.id_proses = kpb.id_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks')
            ->join('tim_kerja tk', 'tk.id_tim = k.id_tim', 'left')
            ->join('penilaian_risiko pr', 'pr.id_penilaian = er.id_penilaian', 'left')
            ->join('kriteria_kemungkinan km', 'km.id_kriteria = pr.id_kemungkinan', 'left')
            ->join('kriteria_dampak kd', 'kd.id_kriteria = pr.id_dampak', 'left')
            ->join('selera_risiko sl', 'sl.id_selera = pr.id_selera', 'left')
            ->join('rencana_penanganan_risiko rtp', 'rtp.id_penilaian_awal = er.id_evaluasi', 'left')
            ->join('kriteria_kemungkinan km_r', 'km_r.id_kriteria = rtp.id_kemungkinan_residu', 'left')
            ->join('kriteria_dampak kd_r', 'kd_r.id_kriteria = rtp.id_dampak_residu', 'left')
            ->join('matriks_risiko mr_r', 'mr_r.level_kemungkinan = km_r.level AND mr_r.level_dampak = kd_r.level', 'left')
            ->join('selera_risiko sr_r', 'mr_r.nilai_risiko BETWEEN sr_r.nilai_min AND sr_r.nilai_max', 'left')
            ->where('er.opsi_tindakan', 'Mengurangi')
            ->orderBy('pb.kode_proses', 'ASC')
            ->orderBy('rtp.id_rtp', 'ASC');

        if ($idKonteks) {
            $builder->where('kpb.id_konteks', $idKonteks);
        }

        if ($idTim) {
            $builder->where('k.id_tim', $idTim);
        }

        if ($idKegiatan) {
            $builder->where('k.id_kegiatan', $idKegiatan);
        }

        if ($tahun) {
            $builder->where('k.tahun', $tahun);
        }

        if ($filter === 'sudah') {
            $builder->where('rtp.id_rtp IS NOT NULL', null, false);
        } elseif ($filter === 'belum') {
            $builder->where('rtp.id_rtp IS NULL', null, false);
        }

        /* ================= TOTAL (FIX KRITIS) ================= */
        $qCount = $db->table('evaluasi_risiko er')
            ->select('COUNT(DISTINCT er.id_evaluasi) as total')
            ->join('identifikasi_risiko ir', 'ir.id_identifikasi = er.id_identifikasi')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks')
            ->join('rencana_penanganan_risiko rtp', 'rtp.id_penilaian_awal = er.id_evaluasi', 'left')
            ->where('er.opsi_tindakan', 'Mengurangi');

        if ($idKonteks) {
            $qCount->where('kpb.id_konteks', $idKonteks);
        }

        if ($idTim) {
            $qCount->where('k.id_tim', $idTim);
        }

        if ($idKegiatan) {
            $qCount->where('k.id_kegiatan', $idKegiatan);
        }

        if ($tahun) {
            $qCount->where('k.tahun', $tahun);
        }

        if ($filter === 'sudah') {
            $qCount->where('rtp.id_rtp IS NOT NULL', null, false);
        } elseif ($filter === 'belum') {
            $qCount->where('rtp.id_rtp IS NULL', null, false);
        }

        $total = (int) ($qCount->get()->getRowArray()['total'] ?? 0);

        $rows = $builder->limit($perPage, $offset)->get()->getResultArray();

        /* ================= GROUPING (BALIK KE VERSI BENAR) ================= */
        $grouped = [];

        foreach ($rows as $row) {
            $idEval = $row['id_evaluasi'];

            if (!isset($grouped[$idEval])) {
                $grouped[$idEval] = [
                    'id_evaluasi'       => $row['id_evaluasi'],
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
                    'rtp_list'          => [],
                ];
            }

            if (!empty($row['id_rtp'])) {
                $levelP = (int) ($row['level_kemungkinan_residu'] ?? 0);
                $levelD = (int) ($row['level_dampak_residu'] ?? 0);

                $skorSR = $row['nilai_sr_residu'] ?? null;

                $grouped[$idEval]['rtp_list'][] = [
                    'id_rtp'        => $row['id_rtp'],
                    'uraian_rtp'    => $row['uraian_rtp'],
                    'target_output' => $row['target_output'],
                    'target_waktu'  => $row['target_waktu'],
                    'level_kemungkinan_residu' => $levelP ?: null,
                    'level_dampak_residu'      => $levelD ?: null,
                    'nilai_sr_residu' => $row['nilai_sr_residu'],
                    'nama_selera_residu' => $row['nama_selera_residu'],
                    'warna_selera_residu' => $row['warna_selera_residu'],
                ];
            }
        }

        /* ================= PRIORITAS ================= */
        uasort($grouped, fn($a, $b) => ($b['nilai_risiko'] ?? 0) <=> ($a['nilai_risiko'] ?? 0));

        $i = 1;
        foreach ($grouped as &$item) {
            $item['no_prioritas'] = $i++;
        }

        $kriteria = $this->getKriteriaList();
        $summary = $this->getSummary(
            $idKonteks,
            $idTim,
            $idKegiatan,
            $tahun
        );

        return view('rencana_penanganan/index', [
            'grouped'       => $grouped,
            'activeKonteks' => $activeKonteks,
            'total'         => $total,
            'from'          => $total > 0 ? $offset + 1 : 0,
            'to'            => min($offset + $perPage, $total),
            'perPage'       => $perPage,
            'filter'        => $filter,
            'pager'         => [
                'currentPage' => $page,
                'totalPages'  => (int) ceil($total / $perPage),
            ],
            'kriteriaKemungkinan' => $kriteria['kriteriaKemungkinan'],
            'kriteriaDampak'      => $kriteria['kriteriaDampak'],
            'totalDitangani' => $summary['totalDitangani'],
            'totalSudah'     => $summary['totalSudah'],
            'totalBelum'     => $summary['totalBelum'],
            'levelRisiko' => $summary['levelRisiko'],
        ]);
    }

    public function ajaxTable()
    {
        if (!$this->request->isAJAX()) return redirect()->back();

        $db = $this->db;

        $idKonteks = $this->request->getGet('id_konteks')
            ?? session('global_id_konteks');

        $idTim = $this->request->getGet('sk')
            ?? session('global_id_tim');

        $idKegiatan = $this->request->getGet('kg')
            ?? session('global_id_kegiatan');

        $tahun = $this->request->getGet('th')
            ?? session('global_tahun');

        $filter = $this->request->getGet('filter');

        $perPage = (int) ($this->request->getGet('perPage') ?? 10);
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $offset  = ($page - 1) * $perPage;

        $builder = $db->table('evaluasi_risiko er')
            ->select('
            er.id_evaluasi,
            ir.id_identifikasi,
            ir.pernyataan_risiko,
            ir.penyebab_risiko,
            ir.dampak_risiko,
            k.id_tim,
            pb.kode_proses,
            pb.uraian_proses,
            tk.nama_tim,
            pr.nilai_risiko,
            pr.warna_risiko,
            sl.nama_level as nama_selera,
            sl.warna as warna_selera,
            rtp.id_rtp,
            rtp.uraian_rtp,
            rtp.target_output,
            rtp.target_waktu,
            km_r.level as level_kemungkinan_residu,
            kd_r.level as level_dampak_residu,
            mr_r.nilai_risiko as nilai_sr_residu,
            sr_r.nama_level as nama_selera_residu,
            sr_r.warna as warna_selera_residu
        ')
            ->join('identifikasi_risiko ir', 'ir.id_identifikasi = er.id_identifikasi')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks')
            ->join('proses_bisnis pb', 'pb.id_proses = kpb.id_proses')
            ->join('tim_kerja tk', 'tk.id_tim = k.id_tim', 'left')
            ->join('penilaian_risiko pr', 'pr.id_penilaian = er.id_penilaian', 'left')
            ->join('selera_risiko sl', 'sl.id_selera = pr.id_selera', 'left')

            ->join(
                'rencana_penanganan_risiko rtp',
                'rtp.id_penilaian_awal = er.id_evaluasi',
                'left'
            )
            ->join(
                'kriteria_kemungkinan km_r',
                'km_r.id_kriteria = rtp.id_kemungkinan_residu',
                'left'
            )
            ->join(
                'kriteria_dampak kd_r',
                'kd_r.id_kriteria = rtp.id_dampak_residu',
                'left'
            )
            ->join(
                'matriks_risiko mr_r',
                'mr_r.level_kemungkinan = km_r.level AND mr_r.level_dampak = kd_r.level',
                'left'
            )
            ->join(
                'selera_risiko sr_r',
                'mr_r.nilai_risiko BETWEEN sr_r.nilai_min AND sr_r.nilai_max',
                'left'
            )
            ->where('er.opsi_tindakan', 'Mengurangi');

        if ($idKonteks) {
            $builder->where('kpb.id_konteks', $idKonteks);
        }
        if ($idTim) {
            $builder->where('k.id_tim', $idTim);
        }

        if ($idKegiatan) {
            $builder->where('k.id_kegiatan', $idKegiatan);
        }

        if ($tahun) {
            $builder->where('k.tahun', $tahun);
        }

        if ($filter === 'sudah') {
            $builder->where('rtp.id_rtp IS NOT NULL', null, false);
        } elseif ($filter === 'belum') {
            $builder->where('rtp.id_rtp IS NULL', null, false);
        }

        $qCount = $db->table('evaluasi_risiko er')
            ->select('COUNT(DISTINCT er.id_evaluasi) as total')
            ->join('identifikasi_risiko ir', 'ir.id_identifikasi = er.id_identifikasi')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks')
            ->join('rencana_penanganan_risiko rtp', 'rtp.id_penilaian_awal = er.id_evaluasi', 'left')
            ->where('er.opsi_tindakan', 'Mengurangi');

        if ($idKonteks) {
            $qCount->where('kpb.id_konteks', $idKonteks);
        }
        if ($idTim) {
            $qCount->where('k.id_tim', $idTim);
        }

        if ($idKegiatan) {
            $qCount->where('k.id_kegiatan', $idKegiatan);
        }

        if ($tahun) {
            $qCount->where('k.tahun', $tahun);
        }

        if ($filter === 'sudah') {
            $qCount->where('rtp.id_rtp IS NOT NULL', null, false);
        } elseif ($filter === 'belum') {
            $qCount->where('rtp.id_rtp IS NULL', null, false);
        }

        $total = (int) ($qCount->get()->getRowArray()['total'] ?? 0);
        $rows  = $builder->limit($perPage, $offset)->get()->getResultArray();

        $grouped = [];

        foreach ($rows as $row) {
            $idEval = $row['id_evaluasi'];

            if (!isset($grouped[$idEval])) {
                $grouped[$idEval] = [
                    'id_evaluasi'       => $row['id_evaluasi'],
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
                    'nama_selera'       => $row['nama_selera'],
                    'warna_selera'      => $row['warna_selera'],
                    'rtp_list'          => [],
                ];
            }

            if (!empty($row['id_rtp'])) {
                $grouped[$idEval]['rtp_list'][] = [
                    'id_rtp'        => $row['id_rtp'],
                    'uraian_rtp'    => $row['uraian_rtp'],
                    'target_output' => $row['target_output'],
                    'target_waktu'  => $row['target_waktu'],
                    'level_kemungkinan_residu' => $row['level_kemungkinan_residu'],
                    'level_dampak_residu'      => $row['level_dampak_residu'],
                    'nilai_sr_residu'          => $row['nilai_sr_residu'],
                    'nama_selera_residu'       => $row['nama_selera_residu'],
                    'warna_selera_residu'      => $row['warna_selera_residu'],
                ];
            }
        }

        $kriteria = $this->getKriteriaList();

        return view('rencana_penanganan/_table_section', [
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
            'kriteriaKemungkinan' => $kriteria['kriteriaKemungkinan'],
            'kriteriaDampak'      => $kriteria['kriteriaDampak'],
        ]);
    }

    /* DETAIL RTP (AJAX — view/edit mode) */
    public function detail($id)
    {
        $db = \Config\Database::connect();

        $data = $db->table('rencana_penanganan_risiko rtp')
            ->select('
            rtp.id_rtp,
            rtp.id_penilaian_awal as id_evaluasi,
            rtp.uraian_rtp,
            rtp.target_output,
            rtp.target_waktu,
            k.id_tim,
            er.id_identifikasi,
            ir.pernyataan_risiko,
            ir.penyebab_risiko,
            ir.dampak_risiko,
            pb.kode_proses,
            pb.uraian_proses,
            k.tahun,
            kegiatan.nama_kegiatan,
            tk.nama_tim,
            g.nama as nama_pengelola,
            ss.uraian_sasaran as sasaran_strategis,
            pr.nilai_risiko,
            pr.warna_risiko,
            km.level as level_kemungkinan,
            kd.level as level_dampak,
            sl.nama_level as nama_selera,
            sl.warna as warna_selera,
            sl.tindakan as tindakan_selera
        ')
            ->join('evaluasi_risiko er', 'er.id_evaluasi = rtp.id_penilaian_awal')
            ->join('identifikasi_risiko ir', 'ir.id_identifikasi = er.id_identifikasi')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('proses_bisnis pb', 'pb.id_proses = kpb.id_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks')
            ->join('kegiatan', 'kegiatan.id_kegiatan = k.id_kegiatan', 'left')
            ->join('tim_kerja tk', 'tk.id_tim = k.id_tim', 'left')
            ->join('sasaran_strategis ss', 'ss.id_sasaran_strategis = k.id_sasaran_strategis', 'left')
            ->join('pengelola_risiko g', 'g.id = k.pengelola_risiko_id', 'left')
            ->join('penilaian_risiko pr', 'pr.id_penilaian = er.id_penilaian', 'left')
            ->join('selera_risiko sl', 'sl.id_selera = pr.id_selera', 'left')
            ->join('kriteria_kemungkinan km', 'km.id_kriteria = pr.id_kemungkinan', 'left')
            ->join('kriteria_dampak kd', 'kd.id_kriteria = pr.id_dampak', 'left')
            ->where('rtp.id_rtp', $id)
            ->get()
            ->getRowArray();

        if (!$data) {
            return $this->response->setJSON(null);
        }

        // ambil list RTP
        $rtpList = $db->table('rencana_penanganan_risiko rtp')
            ->select('
        rtp.id_rtp,
        rtp.uraian_rtp,
        rtp.target_output,
        rtp.target_waktu,
        rtp.id_kemungkinan_residu,
        rtp.id_dampak_residu
    ')
            ->where('rtp.id_penilaian_awal', $data['id_evaluasi'])
            ->orderBy('rtp.id_rtp', 'ASC')
            ->get()->getResultArray();

        $data['rtp_list'] = $rtpList;

        return $this->response->setJSON($data);
    }

    /* DETAIL EVALUASI (AJAX — create mode) */
    public function detailEvaluasi($id)
    {
        $data = $this->db->table('evaluasi_risiko er')
            ->select('
                er.id_evaluasi,
                er.id_identifikasi,
                er.id_penilaian,
                er.opsi_tindakan,
                er.keterangan,
                ir.pernyataan_risiko,
                ir.penyebab_risiko,
                ir.dampak_risiko,
                pb.kode_proses,
                pb.uraian_proses,
                k.tahun,
                k.id_tim,
                kegiatan.nama_kegiatan,
                sk.nama_tim,
                ss.uraian_sasaran as sasaran_strategis,
                g.nama as nama_pengelola,
                pr.nilai_risiko,
                pr.warna_risiko,
                km.level      as level_kemungkinan,
                km.nama_level as nama_kemungkinan,
                kd.level      as level_dampak,
                kd.nama_level as nama_dampak,
                sl.nama_level as nama_selera,
                sl.warna as warna_selera,
                sl.tindakan as tindakan_selera
            ')
            ->join('identifikasi_risiko ir',    'ir.id_identifikasi = er.id_identifikasi')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('proses_bisnis pb',          'pb.id_proses = kpb.id_proses')
            ->join('konteks k',                 'k.id_konteks = kpb.id_konteks')
            ->join('kegiatan', 'kegiatan.id_kegiatan = k.id_kegiatan', 'left')
            ->join('tim_kerja sk',           'sk.id_tim = k.id_tim', 'left')
            ->join('sasaran_strategis ss',      'ss.id_sasaran_strategis = k.id_sasaran_strategis', 'left')
            ->join('pengelola_risiko g',        'g.id = k.pengelola_risiko_id', 'left')
            ->join('penilaian_risiko pr',       'pr.id_penilaian = er.id_penilaian', 'left')
            ->join('kriteria_kemungkinan km',   'km.id_kriteria = pr.id_kemungkinan', 'left')
            ->join('kriteria_dampak kd',        'kd.id_kriteria = pr.id_dampak', 'left')
            ->join('selera_risiko sl',          'sl.id_selera = pr.id_selera', 'left')
            ->where('er.id_evaluasi', $id)
            ->get()->getRowArray();

        return $this->response->setJSON($data);
    }

    public function store()
    {
        log_message('error', 'POST DATA: ' . json_encode($this->request->getPost()));
        try {
            $idPenilaian = $this->request->getPost('id_penilaian_awal');
            $uraianList  = $this->request->getPost('uraian_rtp');
            $outputs     = $this->request->getPost('target_output');
            $waktus      = $this->request->getPost('target_waktu');
            $kemungkinan = $this->request->getPost('id_kemungkinan_residu') ?: null;
            $dampak      = $this->request->getPost('id_dampak_residu') ?: null;

            if (!is_array($uraianList)) $uraianList = [$uraianList];
            if (!is_array($outputs))    $outputs    = [$outputs];
            if (!is_array($waktus))     $waktus     = [$waktus];

            if (!$idPenilaian || empty($outputs)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => 'Data RTP tidak lengkap',
                ]);
            }

            $this->db->transStart();

            foreach ($outputs as $i => $output) {
                if (!$output) continue;

                $targetWaktu = $waktus[$i] ?? null;
                if ($targetWaktu && strlen($targetWaktu) === 7) {
                    $targetWaktu = $targetWaktu . '-01';
                }

                $this->rtpModel->insert([
                    'id_penilaian_awal'     => $idPenilaian,
                    'uraian_rtp'            => $uraianList[$i] ?? null,
                    'target_output'         => $output,
                    'target_waktu'          => $targetWaktu,
                    'id_kemungkinan_residu' => $kemungkinan,
                    'id_dampak_residu'      => $dampak,
                ]);
            }

            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                log_message('error', 'TRANSACTION FAILED (STORE RTP)');

                return $this->response->setStatusCode(500)->setJSON([
                    'status'  => 'error',
                    'message' => 'Gagal menyimpan ke database',
                ]);
            }

            return $this->response->setJSON([
                'status'     => 'success',
                'message'    => 'RTP berhasil disimpan',
                'csrf_token' => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'RTP Store Error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function update($id)
    {
        try {
            $db = $this->db;

            // Ambil id_evaluasi dari RTP yang diedit
            $existing = $db->table('rencana_penanganan_risiko')
                ->where('id_rtp', $id)
                ->get()->getRowArray();

            if (!$existing) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status'  => 'error',
                    'message' => 'Data RTP tidak ditemukan.',
                ]);
            }

            $idEvaluasi          = $existing['id_penilaian_awal'];
            $kemungkinanResidu   = $this->request->getPost('id_kemungkinan_residu') ?: null;
            $dampakResidu        = $this->request->getPost('id_dampak_residu') ?: null;

            $uraianList  = $this->request->getPost('uraian_rtp');
            $outputList  = $this->request->getPost('target_output');
            $waktuList   = $this->request->getPost('target_waktu');

            if (!is_array($uraianList)) $uraianList = [$uraianList];
            if (!is_array($outputList)) $outputList = [$outputList];
            if (!is_array($waktuList))  $waktuList  = [$waktuList];

            $db->transStart();

            // Hapus semua RTP lama milik evaluasi ini
            $db->table('rencana_penanganan_risiko')
                ->where('id_penilaian_awal', $idEvaluasi)
                ->delete();

            // Insert ulang semua RTP dari form
            foreach ($outputList as $i => $output) {
                if (!$output) continue;

                $targetWaktu = $waktuList[$i] ?? null;
                if ($targetWaktu && strlen($targetWaktu) === 7) {
                    $targetWaktu = $targetWaktu . '-01';
                }

                $db->table('rencana_penanganan_risiko')->insert([
                    'id_penilaian_awal'     => $idEvaluasi,
                    'uraian_rtp'            => $uraianList[$i] ?? null,
                    'target_output'         => $output,
                    'target_waktu'          => $targetWaktu,
                    'id_kemungkinan_residu' => $kemungkinanResidu,
                    'id_dampak_residu'      => $dampakResidu,
                ]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal.');
            }

            return $this->response->setJSON([
                'status'     => 'success',
                'message'    => 'RTP berhasil diperbarui',
                'csrf_token' => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'RTP Update Error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $this->rtpModel->delete($id);

            return $this->response->setJSON([
                'status'     => 'success',
                'message'    => 'RTP berhasil dihapus',
                'csrf_token' => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'RTP Delete Error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /* GET KRITERIA (AJAX — endpoint publik jika diperlukan) */
    public function getKriteriaKemungkinan()
    {
        return $this->response->setJSON(
            $this->getKriteriaList()['kriteriaKemungkinan']
        );
    }

    public function getKriteriaDampak()
    {
        return $this->response->setJSON(
            $this->getKriteriaList()['kriteriaDampak']
        );
    }

    public function preview()
    {
        $idKemungkinan = $this->request->getPost('id_kemungkinan');
        $idDampak      = $this->request->getPost('id_dampak');

        // validasi kosong
        if (!$idKemungkinan || !$idDampak) {
            return $this->response->setJSON(['status' => 'empty']);
        }

        $kemungkinan = (new \App\Models\KriteriaKemungkinanModel())->find($idKemungkinan);
        $dampak      = (new \App\Models\KriteriaDampakModel())->find($idDampak);

        // validasi tidak ditemukan
        if (!$kemungkinan || !$dampak) {
            return $this->response->setJSON(['status' => 'invalid']);
        }

        $matriks = (new \App\Models\MatriksRisikoModel())
            ->where('level_kemungkinan', $kemungkinan['level'])
            ->where('level_dampak', $dampak['level'])
            ->first();

        if (!$matriks) {
            return $this->response->setJSON(['status' => 'not_found']);
        }

        $selera = (new \App\Models\SeleraRisikoModel())
            ->where('nilai_min <=', $matriks['nilai_risiko'])
            ->where('nilai_max >=', $matriks['nilai_risiko'])
            ->first();

        return $this->response->setJSON([
            'status'       => 'success',
            'nilai_risiko' => $matriks['nilai_risiko'],
            'warna'        => $matriks['warna'],
            'nama_selera'  => $selera['nama_level'] ?? '-',
            'warna_selera' => $selera['warna'] ?? '#6c757d',
            'tindakan'     => $selera['tindakan'] ?? '-',
        ]);
    }
}
