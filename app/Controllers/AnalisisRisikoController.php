<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\KonteksModel;
use App\Models\KriteriaKemungkinanModel;
use App\Models\KriteriaDampakModel;
use App\Models\MatriksRisikoModel;
use App\Models\SeleraRisikoModel;

class AnalisisRisikoController extends BaseController
{
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

    $idTimAktif = session('global_id_tim');

    return $idTimAktif !== null
        && (string)$idTimAktif === (string)$row->id_tim;
}

    public function index()
    {
        $idTim       = session('global_id_tim');
        $idKegiatan  = session('global_id_kegiatan');
        $tahun       = session('global_tahun');

        $idPengelola = null;

        $activeKonteks = null;

        if ($idTim && $tahun) {
            $activeKonteks = (new KonteksModel())
                ->select('
            konteks.*,
            kegiatan.nama_kegiatan,
            tim_kerja.nama_tim,
            sasaran_strategis.uraian_sasaran,
            p.nama as nama_pemilik,
            g.nama as nama_pengelola
        ')
                ->join('kegiatan', 'kegiatan.id_kegiatan = konteks.id_kegiatan', 'left')
                ->join('tim_kerja', 'tim_kerja.id_tim = konteks.id_tim', 'left')
                ->join('sasaran_strategis', 'sasaran_strategis.id_sasaran_strategis = konteks.id_sasaran_strategis', 'left')
                ->join('pengelola_risiko p', 'p.id = konteks.pemilik_risiko_id', 'left')
                ->join('pengelola_risiko g', 'g.id = konteks.pengelola_risiko_id', 'left')
                ->where('konteks.id_tim', $idTim)
                ->where('konteks.tahun', $tahun)
                ->when($idKegiatan, function ($q) use ($idKegiatan) {
                    $q->where('konteks.id_kegiatan', $idKegiatan);
                })
                ->first();
        }
        $db            = \Config\Database::connect();

        /* PAGINATION CONFIG */
        $perPage = (int) ($this->request->getGet('perPage') ?? 10);
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $offset  = ($page - 1) * $perPage;

        /* QUERY UTAMA */
        $builder = $db->table('identifikasi_risiko ir')
            ->select('
            ir.id_identifikasi,
            ir.pernyataan_risiko,
            ir.penyebab_risiko,
            ir.dampak_risiko,
            kpb.id_konteks,
            kpb.id_konteks_proses,
            pb.kode_proses,
            pb.uraian_proses,
            pb.jenis_proses,
            sk_kinerja.uraian_sasaran as sasaran_kinerja,
            pr.id_penilaian,
            pr.id_kemungkinan,
            pr.id_dampak,
            pr.nilai_risiko,
            pr.warna_risiko,
            pr.tindakan,
            pr.efektivitas,
            pr.uraian_pengendalian,
            km.level as level_kemungkinan,
            km.nama_level as nama_kemungkinan,
            kd.level as level_dampak,
            kd.nama_level as nama_dampak,
            sl.nama_level as nama_selera,
            sl.warna as warna_selera
        ')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks')
            ->join('proses_bisnis pb', 'pb.id_proses = kpb.id_proses')
            ->join('sasaran_kinerja sk_kinerja', 'sk_kinerja.id_konteks_proses = ir.id_konteks_proses', 'left')
            ->join('penilaian_risiko pr', 'pr.id_identifikasi = ir.id_identifikasi', 'left')
            ->join('kriteria_kemungkinan km', 'km.id_kriteria = pr.id_kemungkinan', 'left')
            ->join('kriteria_dampak kd', 'kd.id_kriteria = pr.id_dampak', 'left')
            ->join('selera_risiko sl', 'sl.id_selera = pr.id_selera', 'left');

        if ($idTim) {
            $builder->where('k.id_tim', $idTim);
        }

        if ($idPengelola) {
            $builder->where('k.pengelola_risiko_id', $idPengelola);
        }

        if ($idKegiatan) {
            $builder->where('k.id_kegiatan', $idKegiatan);
        }

        if ($tahun) {
            $builder->where('k.tahun', $tahun);
        }

        $filter = $this->request->getGet('filter');
        if ($filter === 'sudah') {
            $builder->where('pr.id_penilaian IS NOT NULL', null, false);
        } elseif ($filter === 'belum') {
            $builder->where('pr.id_penilaian IS NULL', null, false);
        }

        $builder->orderBy('pb.kode_proses', 'ASC');

        /* TOTAL & PAGINATED DATA */
        $total = $builder->countAllResults(false); // false = jangan reset query
        $data  = $builder->limit($perPage, $offset)->get()->getResultArray();

        $from = $total > 0 ? $offset + 1 : 0;
        $to   = min($offset + $perPage, $total);

        /* MANUAL PAGER */
        $totalPages = (int) ceil($total / $perPage);
        $pager = [
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'perPage'     => $perPage,
            'total'       => $total,
            'filter'      => $filter,
        ];

        /* SUMMARY */
        $summaryBuilder = $db->table('identifikasi_risiko ir')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks');

        if ($idTim) $summaryBuilder->where('k.id_tim', $idTim);
        if ($idPengelola) $summaryBuilder->where('k.pengelola_risiko_id', $idPengelola);
        if ($idKegiatan) $summaryBuilder->where('k.id_kegiatan', $idKegiatan);
        if ($tahun) $summaryBuilder->where('k.tahun', $tahun);
        

        $totalRisiko = $summaryBuilder->countAllResults();

        // rebuild ulang
        $summaryBuilder = $db->table('identifikasi_risiko ir')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks');

        if ($idTim) $summaryBuilder->where('k.id_tim', $idTim);
        if ($idPengelola) $summaryBuilder->where('k.pengelola_risiko_id', $idPengelola);
        if ($idKegiatan) $summaryBuilder->where('k.id_kegiatan', $idKegiatan);
        if ($tahun) $summaryBuilder->where('k.tahun', $tahun);
       

        $totalSudah = $summaryBuilder
            ->join('penilaian_risiko pr', 'pr.id_identifikasi = ir.id_identifikasi')
            ->countAllResults();

        $totalBelum = $totalRisiko - $totalSudah;

        /* DISTRIBUSI LEVEL */
        $levelRisiko = [
            'Sangat Rendah' => 0,
            'Rendah'        => 0,
            'Sedang'        => 0,
            'Tinggi'        => 0,
            'Sangat Tinggi' => 0,
        ];
        $distribusiBuilder = $db->table('identifikasi_risiko ir')
            ->select('sl.nama_level, COUNT(*) as total')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks')
            ->join('penilaian_risiko pr', 'pr.id_identifikasi = ir.id_identifikasi', 'left')
            ->join('selera_risiko sl', 'sl.id_selera = pr.id_selera', 'left')
            ->where('sl.nama_level IS NOT NULL', null, false)
            ->groupBy('sl.nama_level');

        if ($idTim) $distribusiBuilder->where('k.id_tim', $idTim);
        if ($idPengelola) $distribusiBuilder->where('k.pengelola_risiko_id', $idPengelola);
        if ($idKegiatan) $distribusiBuilder->where('k.id_kegiatan', $idKegiatan);
        if ($tahun) $distribusiBuilder->where('k.tahun', $tahun);

        foreach ($distribusiBuilder->get()->getResultArray() as $row) {
            if (isset($levelRisiko[$row['nama_level']])) {
                $levelRisiko[$row['nama_level']] = (int) $row['total'];
            }
        }

        /* KRITERIA */
        $kemungkinanList = (new KriteriaKemungkinanModel())
            ->select('id_kriteria, level, nama_level, deskripsi_frekuensi')
            ->orderBy('level', 'ASC')
            ->findAll();

        $dampakList = (new KriteriaDampakModel())
            ->select('id_kriteria, level, nama_level, deskripsi')
            ->orderBy('level', 'ASC')
            ->findAll();

        return view('analisis_risiko/index', [
            'data'            => $data,
            'kemungkinanList' => $kemungkinanList,
            'dampakList'      => $dampakList,
            'totalRisiko'     => $totalRisiko,
            'totalSudah'      => $totalSudah,
            'totalBelum'      => $totalBelum,
            'levelRisiko'     => $levelRisiko,
            'filter'          => $filter,
            'total'           => $total,
            'from'            => $from,
            'to'              => $to,
            'perPage'         => $perPage,
            'pager'           => $pager,
        ]);
    }

    public function ajaxTable()
    {
        if (!$this->request->isAJAX()) return redirect()->back();

        $idTim       = session('global_id_tim');
        $idKegiatan  = session('global_id_kegiatan');
        $tahun       = session('global_tahun');
        $idPengelola = null;
        $db            = \Config\Database::connect();

        /* PAGINATION CONFIG */
        $perPage = (int) ($this->request->getGet('perPage') ?? 10);
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $offset  = ($page - 1) * $perPage;

        /* QUERY UTAMA */
        $builder = $db->table('identifikasi_risiko ir')
            ->select('
            ir.id_identifikasi,
            ir.pernyataan_risiko,
            ir.penyebab_risiko,
            ir.dampak_risiko,
            kpb.id_konteks,
            kpb.id_konteks_proses,
            pb.kode_proses,
            pb.uraian_proses,
            pb.jenis_proses,
            sk_kinerja.uraian_sasaran as sasaran_kinerja,
            pr.id_penilaian,
            pr.id_kemungkinan,
            pr.id_dampak,
            pr.nilai_risiko,
            pr.warna_risiko,
            pr.tindakan,
            pr.efektivitas,
            pr.uraian_pengendalian,
            km.level as level_kemungkinan,
            km.nama_level as nama_kemungkinan,
            kd.level as level_dampak,
            kd.nama_level as nama_dampak,
            sl.nama_level as nama_selera,
            sl.warna as warna_selera
        ')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks')
            ->join('proses_bisnis pb', 'pb.id_proses = kpb.id_proses')
            ->join('sasaran_kinerja sk_kinerja', 'sk_kinerja.id_konteks_proses = ir.id_konteks_proses', 'left')
            ->join('penilaian_risiko pr', 'pr.id_identifikasi = ir.id_identifikasi', 'left')
            ->join('kriteria_kemungkinan km', 'km.id_kriteria = pr.id_kemungkinan', 'left')
            ->join('kriteria_dampak kd', 'kd.id_kriteria = pr.id_dampak', 'left')
            ->join('selera_risiko sl', 'sl.id_selera = pr.id_selera', 'left');

        if ($idTim) {
            $builder->where('k.id_tim', $idTim);
        }

        if ($idPengelola) {
            $builder->where('k.pengelola_risiko_id', $idPengelola);
        }

        if ($idKegiatan) {
            $builder->where('k.id_kegiatan', $idKegiatan);
        }

        if ($tahun) {
            $builder->where('k.tahun', $tahun);
        }        

        $filter = $this->request->getGet('filter');
        if ($filter === 'sudah') {
            $builder->where('pr.id_penilaian IS NOT NULL', null, false);
        } elseif ($filter === 'belum') {
            $builder->where('pr.id_penilaian IS NULL', null, false);
        }

        $builder->orderBy('pb.kode_proses', 'ASC');

        /* TOTAL & PAGINATED DATA */
        $total = $builder->countAllResults(false); // false = jangan reset query
        $data  = $builder->limit($perPage, $offset)->get()->getResultArray();

        $from = $total > 0 ? $offset + 1 : 0;
        $to   = min($offset + $perPage, $total);

        /* MANUAL PAGER */
        $totalPages = (int) ceil($total / $perPage);
        $pager = [
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'perPage'     => $perPage,
            'total'       => $total,
            'filter'      => $filter,
        ];

        /* SUMMARY */
        $summaryBuilder = $db->table('identifikasi_risiko ir')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks');

        if ($idTim) $summaryBuilder->where('k.id_tim', $idTim);
        if ($idPengelola) $summaryBuilder->where('k.pengelola_risiko_id', $idPengelola);
        if ($idKegiatan) $summaryBuilder->where('k.id_kegiatan', $idKegiatan);
        if ($tahun) $summaryBuilder->where('k.tahun', $tahun);
        

        $totalRisiko = $summaryBuilder->countAllResults();

        // rebuild ulang
        $summaryBuilder = $db->table('identifikasi_risiko ir')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks');

        if ($idTim) $summaryBuilder->where('k.id_tim', $idTim);
        if ($idPengelola) $summaryBuilder->where('k.pengelola_risiko_id', $idPengelola);
        if ($idKegiatan) $summaryBuilder->where('k.id_kegiatan', $idKegiatan);
        if ($tahun) $summaryBuilder->where('k.tahun', $tahun);

        $totalSudah = $summaryBuilder
            ->join('penilaian_risiko pr', 'pr.id_identifikasi = ir.id_identifikasi')
            ->countAllResults();

        $totalBelum = $totalRisiko - $totalSudah;

        /* DISTRIBUSI LEVEL */
        $levelRisiko = [
            'Sangat Rendah' => 0,
            'Rendah'        => 0,
            'Sedang'        => 0,
            'Tinggi'        => 0,
            'Sangat Tinggi' => 0,
        ];
        $distribusiBuilder = $db->table('identifikasi_risiko ir')
            ->select('sl.nama_level, COUNT(*) as total')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks')
            ->join('penilaian_risiko pr', 'pr.id_identifikasi = ir.id_identifikasi', 'left')
            ->join('selera_risiko sl', 'sl.id_selera = pr.id_selera', 'left')
            ->where('sl.nama_level IS NOT NULL', null, false)
            ->groupBy('sl.nama_level');

        if ($idTim) $distribusiBuilder->where('k.id_tim', $idTim);
        if ($idPengelola) $distribusiBuilder->where('k.pengelola_risiko_id', $idPengelola);
        if ($idKegiatan) $distribusiBuilder->where('k.id_kegiatan', $idKegiatan);
        if ($tahun) $distribusiBuilder->where('k.tahun', $tahun);

        foreach ($distribusiBuilder->get()->getResultArray() as $row) {
            if (isset($levelRisiko[$row['nama_level']])) {
                $levelRisiko[$row['nama_level']] = (int) $row['total'];
            }
        }

        /* KRITERIA */
        $kemungkinanList = (new KriteriaKemungkinanModel())
            ->select('id_kriteria, level, nama_level, deskripsi_frekuensi')
            ->orderBy('level', 'ASC')
            ->findAll();

        $dampakList = (new KriteriaDampakModel())
            ->select('id_kriteria, level, nama_level, deskripsi')
            ->orderBy('level', 'ASC')
            ->findAll();

        return view('analisis_risiko/_table_section', [
            'data'            => $data,
            'kemungkinanList' => $kemungkinanList,
            'dampakList'      => $dampakList,
            'totalRisiko'     => $totalRisiko,
            'totalSudah'      => $totalSudah,
            'totalBelum'      => $totalBelum,
            'levelRisiko'     => $levelRisiko,
            'filter'          => $filter,
            'total'           => $total,
            'from'            => $from,
            'to'              => $to,
            'perPage'         => $perPage,
            'pager'           => $pager,
        ]);
    }

    /* DETAIL PENILAIAN (AJAX — view/edit mode) */
    public function detail($id)
    {
        $db   = \Config\Database::connect();
        $data = $db->table('penilaian_risiko pr')
            ->select('
                pr.*,
                ir.pernyataan_risiko,
                ir.penyebab_risiko,
                ir.dampak_risiko,
                ir.id_konteks_proses,
                kpb.id_konteks,
                pb.kode_proses,
                pb.uraian_proses,
                sk_kinerja.uraian_sasaran as sasaran_kinerja,
                k.tahun,
                k.id_tim,
                kegiatan.nama_kegiatan,
                tim_kerja.nama_tim,
                ss.uraian_sasaran as sasaran_strategis,
                g.nama as nama_pengelola,
                sl.nama_level as nama_selera,
                sl.warna as warna_selera
            ')
            ->join('identifikasi_risiko ir', 'ir.id_identifikasi = pr.id_identifikasi')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('proses_bisnis pb', 'pb.id_proses = kpb.id_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks')
            ->join('kegiatan', 'kegiatan.id_kegiatan = k.id_kegiatan', 'left')
            ->join('tim_kerja', 'tim_kerja.id_tim = k.id_tim', 'left')
            ->join('sasaran_strategis ss', 'ss.id_sasaran_strategis = k.id_sasaran_strategis', 'left')
            ->join('pengelola_risiko g', 'g.id = k.pengelola_risiko_id', 'left')
            ->join('selera_risiko sl', 'sl.id_selera = pr.id_selera', 'left')
            ->join('sasaran_kinerja sk_kinerja', 'sk_kinerja.id_konteks_proses = ir.id_konteks_proses', 'left')
            ->where('pr.id_penilaian', $id)
            ->get()->getRowArray();
        if (!$data) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ]);
        }

        return $this->response->setJSON($data);
    }

    /* DETAIL IDENTIFIKASI (AJAX — create mode) */
    public function detailIdentifikasi($id)
    {
        $db   = \Config\Database::connect();
        $data = $db->table('identifikasi_risiko ir')
            ->select('
                ir.*,
                kpb.id_konteks,
                pb.kode_proses,
                pb.uraian_proses,
                sk_kinerja.uraian_sasaran as sasaran_kinerja,
                k.tahun,
                k.id_tim,
                kegiatan.nama_kegiatan,
                tim_kerja.nama_tim,
                ss.uraian_sasaran as sasaran_strategis,
                g.nama as nama_pengelola
            ')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('proses_bisnis pb', 'pb.id_proses = kpb.id_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks')
            ->join('kegiatan', 'kegiatan.id_kegiatan = k.id_kegiatan', 'left')
            ->join('tim_kerja', 'tim_kerja.id_tim = k.id_tim', 'left')
            ->join('sasaran_strategis ss', 'ss.id_sasaran_strategis = k.id_sasaran_strategis', 'left')
            ->join('pengelola_risiko g', 'g.id = k.pengelola_risiko_id', 'left')
            ->join('sasaran_kinerja sk_kinerja', 'sk_kinerja.id_konteks_proses = ir.id_konteks_proses', 'left')
            ->where('ir.id_identifikasi', $id)
            ->get()->getRowArray();
        if (!$data) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ]);
        }

        return $this->response->setJSON($data);
    }

    public function store()
    {
        $idIdentifikasi = $this->request->getPost('id_identifikasi');

        if (!$this->validateTimAccessByIdentifikasi($idIdentifikasi)) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Tidak punya akses ke data ini'
            ]);
        }
        try {
            $db            = \Config\Database::connect();
            $idKemungkinan = $this->request->getPost('id_kemungkinan');
            $idDampak      = $this->request->getPost('id_dampak');

            $kemungkinan = (new KriteriaKemungkinanModel())->find($idKemungkinan);
            $dampak      = (new KriteriaDampakModel())->find($idDampak);

            $matriks = (new MatriksRisikoModel())
                ->where('level_kemungkinan', $kemungkinan['level'])
                ->where('level_dampak', $dampak['level'])
                ->first();

            $nilaiRisiko = $matriks['nilai_risiko'];

            $selera = (new SeleraRisikoModel())
                ->where('nilai_min <=', $nilaiRisiko)
                ->where('nilai_max >=', $nilaiRisiko)
                ->first();

            $db->table('penilaian_risiko')->insert([
                'id_identifikasi'     => $this->request->getPost('id_identifikasi'),
                'id_kemungkinan'      => $idKemungkinan,
                'id_dampak'           => $idDampak,
                'id_matriks'          => $matriks['id_matriks'],
                'id_selera'           => $selera['id_selera'],
                'nilai_risiko'        => $nilaiRisiko,
                'warna_risiko'        => $matriks['warna'],
                'tindakan'            => $selera['tindakan'],
                'efektivitas'         => $this->request->getPost('efektivitas'),
                'uraian_pengendalian' => $this->request->getPost('uraian_pengendalian'),
            ]);

            return $this->response->setJSON([
                'status'     => 'success',
                'csrf_token' => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'AR Store Error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function update($id)
    {
        try {
            $db            = \Config\Database::connect();
            $data = $db->table('penilaian_risiko')
                ->select('id_identifikasi')
                ->where('id_penilaian', $id)
                ->get()
                ->getRow();

            if (!$data || !$this->validateTimAccessByIdentifikasi($data->id_identifikasi)) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => 'Tidak punya akses'
                ]);
            }
            $idKemungkinan = $this->request->getPost('id_kemungkinan');
            $idDampak      = $this->request->getPost('id_dampak');

            $kemungkinan = (new KriteriaKemungkinanModel())->find($idKemungkinan);
            $dampak      = (new KriteriaDampakModel())->find($idDampak);

            $matriks = (new MatriksRisikoModel())
                ->where('level_kemungkinan', $kemungkinan['level'])
                ->where('level_dampak', $dampak['level'])
                ->first();

            $nilaiRisiko = $matriks['nilai_risiko'];

            $selera = (new SeleraRisikoModel())
                ->where('nilai_min <=', $nilaiRisiko)
                ->where('nilai_max >=', $nilaiRisiko)
                ->first();

            $db->table('penilaian_risiko')
                ->where('id_penilaian', $id)
                ->update([
                    'id_kemungkinan'      => $idKemungkinan,
                    'id_dampak'           => $idDampak,
                    'id_matriks'          => $matriks['id_matriks'],
                    'id_selera'           => $selera['id_selera'],
                    'nilai_risiko'        => $nilaiRisiko,
                    'warna_risiko'        => $matriks['warna'],
                    'tindakan'            => $selera['tindakan'],
                    'efektivitas'         => $this->request->getPost('efektivitas'),
                    'uraian_pengendalian' => $this->request->getPost('uraian_pengendalian'),
                ]);

            return $this->response->setJSON([
                'status'     => 'success',
                'csrf_token' => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'AR Update Error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function delete($id)
    {
        $db = \Config\Database::connect();
        $data = $db->table('penilaian_risiko')
            ->select('id_identifikasi')
            ->where('id_penilaian', $id)
            ->get()
            ->getRow();

        if (!$data || !$this->validateTimAccessByIdentifikasi($data->id_identifikasi)) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Tidak punya akses'
            ]);
        }

        try {
            $db->transStart();

            // Ambil semua id_evaluasi yang terkait penilaian ini
            $evaluasiList = $db->table('evaluasi_risiko')
                ->select('id_evaluasi')
                ->where('id_penilaian', $id)
                ->get()->getResultArray();

            $idEvaluasiList = array_column($evaluasiList, 'id_evaluasi');

            if (!empty($idEvaluasiList)) {
                // Hapus RTP dulu (child paling bawah)
                $db->table('rencana_penanganan_risiko')
                    ->whereIn('id_penilaian_awal', $idEvaluasiList)
                    ->delete();

                // Hapus Evaluasi
                $db->table('evaluasi_risiko')
                    ->whereIn('id_evaluasi', $idEvaluasiList)
                    ->delete();
            }

            // Hapus Penilaian Risiko
            $db->table('penilaian_risiko')
                ->where('id_penilaian', $id)
                ->delete();

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal.');
            }

            return $this->response->setJSON([
                'status'     => 'success',
                'csrf_token' => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'AR Delete Error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /* PREVIEW SKOR (AJAX) */
    public function preview()
    {
        $idKemungkinan = $this->request->getPost('id_kemungkinan');
        $idDampak      = $this->request->getPost('id_dampak');

        if (!$idKemungkinan || !$idDampak) {
            return $this->response->setJSON(['status' => 'empty']);
        }

        $kemungkinan = (new KriteriaKemungkinanModel())->find($idKemungkinan);
        $dampak      = (new KriteriaDampakModel())->find($idDampak);

        if (!$kemungkinan || !$dampak) {
            return $this->response->setJSON(['status' => 'invalid']);
        }

        $matriks = (new MatriksRisikoModel())
            ->where('level_kemungkinan', $kemungkinan['level'])
            ->where('level_dampak', $dampak['level'])
            ->first();

        if (!$matriks) {
            return $this->response->setJSON(['status' => 'not_found']);
        }

        $selera = (new SeleraRisikoModel())
            ->where('nilai_min <=', $matriks['nilai_risiko'])
            ->where('nilai_max >=', $matriks['nilai_risiko'])
            ->first();

        return $this->response->setJSON([
            'status'       => 'success',
            'nilai_risiko' => $matriks['nilai_risiko'],
            'warna'        => $matriks['warna'],
            'nama_selera'  => $selera['nama_level'] ?? '-',
            'tindakan'     => $selera['tindakan'] ?? '-',
        ]);
    }
}
