<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\KonteksModel;
use App\Models\KonteksProsesModel;
use App\Models\KategoriRisikoModel;
use App\Models\AreaDampakModel;
use App\Models\IdentifikasiAreaDampakModel;
use App\Models\IdentifikasiRisikoModel;
use App\Models\BankRisikoModel;

class IdentifikasiRisikoController extends BaseController
{
    private function getListKonteks(): array
    {
        $builder = (new KonteksModel())
            ->select('
            konteks.id_konteks,
            konteks.tahun,
            konteks.id_tim,
            konteks.id_kegiatan,
            konteks.pengelola_risiko_id,
            tim_kerja.nama_tim,
            sasaran_strategis.uraian_sasaran,
            kegiatan.nama_kegiatan,
            p.nama as nama_pemilik,
            g.nama as nama_pengelola
        ')
            ->join('tim_kerja', 'tim_kerja.id_tim = konteks.id_tim', 'left')
            ->join('sasaran_strategis', 'sasaran_strategis.id_sasaran_strategis = konteks.id_sasaran_strategis', 'left')
            ->join('kegiatan', 'kegiatan.id_kegiatan = konteks.id_kegiatan', 'left')
            ->join('pengelola_risiko p', 'p.id = konteks.pemilik_risiko_id', 'left')
            ->join('pengelola_risiko g', 'g.id = konteks.pengelola_risiko_id', 'left')
            ->orderBy('konteks.created_at', 'DESC');

        return $builder->findAll();
    }

    public function index()
    {
        $risikoModel   = new IdentifikasiRisikoModel();
        $kategoriModel = new KategoriRisikoModel();
        $areaModel     = new AreaDampakModel();

        $globalTahun    = session('global_tahun');
        $globalTim      = session('global_id_tim');
        $globalKegiatan = session('global_id_kegiatan');

        $idKonteks = null;
        $activeKonteks = null;

        if ($globalTim && $globalTahun) {
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
                ->where('konteks.id_tim', $globalTim)
                ->where('konteks.tahun', $globalTahun)
                ->when($globalKegiatan, function ($q) use ($globalKegiatan) {
                    $q->where('konteks.id_kegiatan', $globalKegiatan);
                })
                ->first();

            $idKonteks = $activeKonteks['id_konteks'] ?? null;
        }

        /* QUERY DATA IDENTIFIKASI RISIKO
           JOIN: identifikasi_risiko → konteks_proses_bisnis → proses_bisnis */
        $builder = $risikoModel
            ->select('
                identifikasi_risiko.*,
                konteks_proses_bisnis.id_konteks,
                k.id_tim,
                proses_bisnis.kode_proses,
                proses_bisnis.uraian_proses,
                proses_bisnis.jenis_proses,
                kategori_risiko.nama_kategori,
                STRING_AGG(area_dampak.nama_area_dampak, \', \') as area_dampak_list
            ')
            ->join('konteks_proses_bisnis', 'konteks_proses_bisnis.id_konteks_proses = identifikasi_risiko.id_konteks_proses')
            ->join('konteks k', 'k.id_konteks = konteks_proses_bisnis.id_konteks')
            ->join('proses_bisnis', 'proses_bisnis.id_proses = konteks_proses_bisnis.id_proses')
            ->join('kategori_risiko', 'kategori_risiko.id_kategori_risiko = identifikasi_risiko.id_kategori_risiko', 'left')
            ->join('identifikasi_area_dampak', 'identifikasi_area_dampak.id_identifikasi = identifikasi_risiko.id_identifikasi', 'left')
            ->join('area_dampak', 'area_dampak.id_area_dampak = identifikasi_area_dampak.id_area_dampak', 'left')
            ->groupBy('
                identifikasi_risiko.id_identifikasi,
                konteks_proses_bisnis.id_konteks,
                k.id_tim,
                proses_bisnis.kode_proses,
                proses_bisnis.uraian_proses,
                proses_bisnis.jenis_proses,
                kategori_risiko.nama_kategori
            ');

        $idTim = $globalTim;
        $idKegiatan = $globalKegiatan;
        $tahun = $globalTahun;
        $idPengelola = null;

        // PRIORITAS: kalau ada filter → pakai filter
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

        $idKategori = $this->request->getGet('filter_kategori');
        if ($idKategori) {
            $builder->where('identifikasi_risiko.id_kategori_risiko', $idKategori);
        }

        $perPage = (int) ($this->request->getGet('perPage') ?? 10);
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $offset  = ($page - 1) * $perPage;

        $builder->orderBy('proses_bisnis.kode_proses', 'ASC');

        $total = $builder->countAllResults(false);
        $data  = $builder->limit($perPage, $offset)->get()->getResultArray();

        $from = $total > 0 ? $offset + 1 : 0;
        $to   = min($offset + $perPage, $total);

        $totalPages = (int) ceil($total / $perPage);

        $pager = [
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'perPage'     => $perPage,
            'total'       => $total, 
        ];

        /* LIST PROSES BISNIS UNTUK KONTEKS AKTIF (untuk dropdown di form tambah risiko) */
        $listKonteksProses = [];

        $db = \Config\Database::connect();
        $query = $db->table('konteks_proses_bisnis kpb')
            ->select('
        kpb.id_konteks_proses,
        kpb.deskripsi_proses,
        pb.kode_proses,
        pb.uraian_proses,
        pb.jenis_proses
    ')->join('proses_bisnis pb', 'pb.id_proses = kpb.id_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks');

        // PRIORITAS 1 → kalau pakai id_konteks
        if ($idKonteks) {
            $query->where('kpb.id_konteks', $idKonteks);
        }

        // PRIORITAS 2 → kalau pakai filter
        else {
            if ($idTim) {
                $query->where('k.id_tim', $idTim);
            }
            if ($idPengelola) {
                $query->where('k.pengelola_risiko_id', $idPengelola);
            }
            if ($idKegiatan) {
                $query->where('k.id_kegiatan', $idKegiatan);
            }
            if ($tahun) {
                $query->where('k.tahun', $tahun);
            }
        }

        $listKonteksProses = $query
            ->orderBy('pb.kode_proses', 'ASC')
            ->orderBy('kpb.id_konteks_proses', 'ASC')
            ->get()
            ->getResultArray();

        $kategoriList   = $kategoriModel->orderBy('nama_kategori', 'ASC')->findAll();
        $areaDampakList = $areaModel->orderBy('nama_area_dampak', 'ASC')->findAll();
        $filterKategori = $this->request->getGet('filter_kategori');

        return view('identifikasi_risiko/index', [
            'data'              => $data,
            'listKonteks'       => $this->getListKonteks(),
            'activeKonteks'     => $activeKonteks,
            'listKonteksProses' => $listKonteksProses,
            'kategoriList'      => $kategoriList,
            'areaDampakList'    => $areaDampakList,
            'filterKategori'    => $filterKategori,
            'from'    => $from,
            'to'      => $to,
            'total'   => $total,
            'perPage' => $perPage,
            'pager'   => $pager,
        ]);
    }

    /* VALIDASI AKSES TIM */
    private function validateTimAccess($idKonteksProses): bool
    {
        $db = \Config\Database::connect();
        $row = $db->table('konteks_proses_bisnis kpb')
            ->select('k.id_tim')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks')
            ->where('kpb.id_konteks_proses', $idKonteksProses)
            ->get()
            ->getRow();

        if (!$row) return false;
        $role = session('role');
        $idTimUser = session('id_tim');
        if ($role === 'admin') return true;
        if ($role === 'ketua') return true;

        return (string)$idTimUser === (string)$row->id_tim;
    }
    private function validateKonteksAccess($idKonteks): bool
    {
        $db = \Config\Database::connect();

        $row = $db->table('konteks')
            ->select('id_tim')
            ->where('id_konteks', $idKonteks)
            ->get()
            ->getRow();

        if (!$row) return false;

        $role = session('role');
        if ($role === 'admin') return true;
        if ($role === 'ketua') return true;

        return (string)session('id_tim') === (string)$row->id_tim;
    }

    /* STORE */
    public function store()
    {
        $db = \Config\Database::connect();
        $idKonteksProses = $this->request->getPost('id_konteks_proses');

        if (!$this->validateTimAccess($idKonteksProses)) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Tidak punya akses ke data ini'
            ]);
        }
        $result = $db->query("
        INSERT INTO identifikasi_risiko 
            (id_konteks_proses, pernyataan_risiko, penyebab_risiko, dampak_risiko, id_kategori_risiko, sumber_risiko)
        VALUES (?, ?, ?, ?, ?, ?)
        RETURNING id_identifikasi
    ", [
            $this->request->getPost('id_konteks_proses'),
            $this->request->getPost('pernyataan_risiko'),
            $this->request->getPost('penyebab_risiko'),
            $this->request->getPost('dampak_risiko'),
            $this->request->getPost('id_kategori_risiko') ?: null,
            $this->request->getPost('sumber_risiko'),
        ]);

        $idIdentifikasi = $result->getRow()->id_identifikasi ?? null;


        $area = $this->request->getPost('area_dampak');

        if ($area && $idIdentifikasi) {
            $db->table('identifikasi_area_dampak')->insert([
                'id_identifikasi' => $idIdentifikasi,
                'id_area_dampak'  => $area,
            ]);
        }

        return $this->response->setJSON([
            'status'     => 'success',
            'csrf_token' => csrf_hash(),
        ]);
    }

    /* UPDATE */
    public function update($id)
    {
        $db = \Config\Database::connect();
        $data = $db->table('identifikasi_risiko')
            ->select('id_konteks_proses')
            ->where('id_identifikasi', $id)
            ->get()
            ->getRow();

        if (!$data || !$this->validateTimAccess($data->id_konteks_proses)) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Tidak punya akses'
            ]);
        }

        $newIdKonteksProses = $this->request->getPost('id_konteks_proses');

        if (!$this->validateTimAccess($newIdKonteksProses)) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Tidak punya akses ke target data'
            ]);
        }

        try {
            $db->table('identifikasi_risiko')
                ->where('id_identifikasi', $id)
                ->update([
                    'id_konteks_proses'  => $this->request->getPost('id_konteks_proses'),
                    'pernyataan_risiko'  => $this->request->getPost('pernyataan_risiko'),
                    'penyebab_risiko'    => $this->request->getPost('penyebab_risiko'),
                    'dampak_risiko'      => $this->request->getPost('dampak_risiko'),
                    'id_kategori_risiko' => $this->request->getPost('id_kategori_risiko') ?: null,
                    'sumber_risiko'      => $this->request->getPost('sumber_risiko'),
                ]);

            $db->table('identifikasi_area_dampak')
                ->where('id_identifikasi', $id)
                ->delete();

            $area = $this->request->getPost('area_dampak');

            if ($area) {
                $db->table('identifikasi_area_dampak')->insert([
                    'id_identifikasi' => $id,
                    'id_area_dampak'  => $area,
                ]);
            }

            return $this->response->setJSON([
                'status'     => 'success',
                'csrf_token' => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'IR Update Error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /* DELETE */
    public function delete($id)
    {
        $db = \Config\Database::connect();
        $data = $db->table('identifikasi_risiko')
            ->select('id_konteks_proses')
            ->where('id_identifikasi', $id)
            ->get()
            ->getRow();

        if (!$data || !$this->validateTimAccess($data->id_konteks_proses)) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Tidak punya akses'
            ]);
        }

        try {
            $db->transStart();

            // Ambil semua id_penilaian yang terkait identifikasi ini
            $penilaianList = $db->table('penilaian_risiko')
                ->select('id_penilaian')
                ->where('id_identifikasi', $id)
                ->get()->getResultArray();

            $idPenilaianList = array_column($penilaianList, 'id_penilaian');

            if (!empty($idPenilaianList)) {
                // Ambil semua id_evaluasi yang terkait penilaian ini
                $evaluasiList = $db->table('evaluasi_risiko')
                    ->select('id_evaluasi')
                    ->whereIn('id_penilaian', $idPenilaianList)
                    ->get()->getResultArray();

                $idEvaluasiList = array_column($evaluasiList, 'id_evaluasi');

                if (!empty($idEvaluasiList)) {
                    // Hapus RTP (child paling bawah)
                    $db->table('rencana_penanganan_risiko')
                        ->whereIn('id_penilaian_awal', $idEvaluasiList)
                        ->delete();

                    // Hapus Evaluasi
                    $db->table('evaluasi_risiko')
                        ->whereIn('id_evaluasi', $idEvaluasiList)
                        ->delete();
                }

                // Hapus Penilaian
                $db->table('penilaian_risiko')
                    ->whereIn('id_penilaian', $idPenilaianList)
                    ->delete();
            }

            // Hapus area dampak pivot
            $db->table('identifikasi_area_dampak')
                ->where('id_identifikasi', $id)
                ->delete();

            // Hapus Identifikasi Risiko
            $db->table('identifikasi_risiko')
                ->where('id_identifikasi', $id)
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
            log_message('error', 'IR Delete Error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /* DETAIL (untuk load form edit) */
    public function detail($id)
    {
        $model = new IdentifikasiRisikoModel();
        $data = $model
            ->select('
            identifikasi_risiko.*,
            konteks_proses_bisnis.id_konteks,
            proses_bisnis.kode_proses,
            proses_bisnis.uraian_proses,
            kategori_risiko.nama_kategori
        ')
            ->join('konteks_proses_bisnis', 'konteks_proses_bisnis.id_konteks_proses = identifikasi_risiko.id_konteks_proses')
            ->join('proses_bisnis', 'proses_bisnis.id_proses = konteks_proses_bisnis.id_proses')
            ->join('kategori_risiko', 'kategori_risiko.id_kategori_risiko = identifikasi_risiko.id_kategori_risiko', 'left')
            ->where('identifikasi_risiko.id_identifikasi', $id)
            ->first();

        if (!$data) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ]);
        }

        return $this->response->setJSON($data);
    }

    /* DETAIL AREA DAMPAK */
    public function detailArea($id)
    {
        $pivot = new IdentifikasiAreaDampakModel();
        $data  = $pivot->where('id_identifikasi', $id)->findColumn('id_area_dampak');
        return $this->response->setJSON($data ?? []);
    }

    /* AJAX TABLE — refresh tabel tanpa reload halaman penuh */
    public function ajaxTable()
    {
        if (!$this->request->isAJAX()) return redirect()->back();

        $risikoModel   = new IdentifikasiRisikoModel();

        $builder = $risikoModel
            ->select('
                identifikasi_risiko.*,
                konteks_proses_bisnis.id_konteks,
                k.id_tim,
                proses_bisnis.kode_proses,
                proses_bisnis.uraian_proses,
                proses_bisnis.jenis_proses,
                kategori_risiko.nama_kategori,
                STRING_AGG(area_dampak.nama_area_dampak, \', \') as area_dampak_list
            ')
            ->join('konteks_proses_bisnis', 'konteks_proses_bisnis.id_konteks_proses = identifikasi_risiko.id_konteks_proses')
            ->join('konteks k', 'k.id_konteks = konteks_proses_bisnis.id_konteks')
            ->join('proses_bisnis', 'proses_bisnis.id_proses = konteks_proses_bisnis.id_proses')
            ->join('kategori_risiko', 'kategori_risiko.id_kategori_risiko = identifikasi_risiko.id_kategori_risiko', 'left')
            ->join('identifikasi_area_dampak', 'identifikasi_area_dampak.id_identifikasi = identifikasi_risiko.id_identifikasi', 'left')
            ->join('area_dampak', 'area_dampak.id_area_dampak = identifikasi_area_dampak.id_area_dampak', 'left')
            ->groupBy('
                identifikasi_risiko.id_identifikasi,
                konteks_proses_bisnis.id_konteks,
                k.id_tim,
                proses_bisnis.kode_proses,
                proses_bisnis.uraian_proses,
                proses_bisnis.jenis_proses,
                kategori_risiko.nama_kategori
            ');

        $globalTahun    = session('global_tahun');
        $globalTim      = session('global_id_tim');
        $globalKegiatan = session('global_id_kegiatan');

        $idTim = $globalTim;
        $idKegiatan = $globalKegiatan;
        $tahun = $globalTahun;
        $idPengelola = null;

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

        $idKategori = $this->request->getGet('filter_kategori');
        if ($idKategori) {
            $builder->where('identifikasi_risiko.id_kategori_risiko', $idKategori);
        }

        $perPage = (int) ($this->request->getGet('perPage') ?? 10);
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $offset  = ($page - 1) * $perPage;

        $builder->orderBy('proses_bisnis.kode_proses', 'ASC');

        $total = $builder->countAllResults(false);
        $data  = $builder->limit($perPage, $offset)->get()->getResultArray();

        $from = $total > 0 ? $offset + 1 : 0;
        $to   = min($offset + $perPage, $total);

        $totalPages = (int) ceil($total / $perPage);

        $pager = [
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'perPage'     => $perPage,
            'total'       => $total,
        ];

        return view('identifikasi_risiko/_table_section', [
            'data'          => $data,
            'from'    => $from,
            'to'      => $to,
            'total'   => $total,
            'perPage' => $perPage,
            'pager'   => $pager,
            'activeKonteks' => [
                'id_tim' => $idTim
            ],
        ]);
    }

    /* GET BANK RISIKO — autocomplete pernyataan risiko */
    public function getBankRisiko()
    {
        $model = new BankRisikoModel();
        $data  = $model->getForDropdown();
        return $this->response->setJSON($data);
    }

    public function requestBankRisiko()
    {
        $model = new \App\Models\BankRisikoModel();

        $model->insert([
            'pernyataan_risiko' => $this->request->getPost('pernyataan_risiko'),
            'status' => 'pending',
            'created_by' => session()->get('user_id'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON([
            'status' => 'success'
        ]);
    }
}