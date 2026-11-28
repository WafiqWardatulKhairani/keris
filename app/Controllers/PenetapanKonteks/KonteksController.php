<?php

namespace App\Controllers\PenetapanKonteks;

use App\Models\KonteksModel;
use App\Models\KonteksPemangkuModel;
use App\Models\KonteksPeraturanModel;
use App\Models\ProsesBisnisModel;
use App\Models\KonteksProsesBisnisModel;
use App\Models\PemangkuKepentinganModel;
use App\Models\PeraturanTerkaitModel;
use App\Models\TimKerjaModel;
use App\Models\SasaranStrategisModel;
use App\Models\PengelolaRisikoModel;
use App\Models\PenugasanPengelolaModel;
use App\Models\KegiatanModel;
use App\Models\WilayahModel;

class KonteksController extends BaseContextController
{
    protected $model;

    public function __construct()
    {
        $this->model = new KonteksModel();
    }

    private function validateKonteksAccess($idKonteks): bool
    {
        $row = $this->model
            ->select('id_tim')
            ->where('id_konteks', $idKonteks)
            ->first();

        if (!$row) {
            return false;
        }

        $role = session('user_role');

        // Admin boleh akses semua tim
        if ($role === 'admin') {
            return true;
        }

        // Ketua tetap mengikuti rule existing
        if ($role === 'ketua') {
            return false;
        }

        // Ambil seluruh tim yang dimiliki user
        $userTim = session('user_tim') ?? [];

        // Fallback untuk kompatibilitas akun/data lama
        if (empty($userTim) && session('id_tim')) {
            $userTim = [session('id_tim')];
        }

        $userTim = array_map('strval', $userTim);

        return in_array(
            (string) $row['id_tim'],
            $userTim,
            true
        );
    }

    public function show($id)
    {
        if ((int)$id === 0) {
            return view(
                'penetapan_konteks/index',
                [
                    'activeKonteks'      => null,
                    'listKonteks'        => $this->getListKonteks(),
                    'activeTab'          => 'konteks',
                    'mode'               => 'create',
                    'hasKegiatan'        => true,
                    'hasScope'           => false,
                    'hideGlobalContext'  => false,
                ]
            );
        }

        $activeKonteks = $this->getActiveKonteks($id);
        log_message(
            'error',
            'SHOW KONTEKS => ID=' . $id .
                ' STATUS=' . ($activeKonteks['status'] ?? 'NULL')
        );

        if (!$activeKonteks) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $requestedTahun    = session('global_tahun');
        $requestedTim      = session('global_id_tim');
        $requestedKegiatan = session('global_id_kegiatan');

        $hasTim = !empty($requestedTim);

        $hasSpecificKegiatan = !empty($requestedKegiatan)
            && $requestedKegiatan !== 'all';

        $hasKegiatan = $hasTim;

        // Kalau user memilih kegiatan tertentu
        if ($hasTim) {
            $isMatch =
                (int) $activeKonteks['tahun'] === (int) $requestedTahun
                && (int) $activeKonteks['id_tim'] === (int) $requestedTim;

            if ($hasSpecificKegiatan) {
                $isMatch = $isMatch
                    && (int) $activeKonteks['id_kegiatan'] === (int) $requestedKegiatan;
            }

            if (!$isMatch) {
                return redirect()->to(site_url('penetapan-konteks/konteks'));
            }
        }

        $hasScope = true;

        $pemilikRisiko = (new PengelolaRisikoModel())
            ->getPemilikByWilayah(1);

        $pengelolaRisiko = (new PenugasanPengelolaModel())
            ->getKetuaTimWithFallback(
                (int)$activeKonteks['id_tim'],
                (int)$activeKonteks['tahun']
            );

        $listPemangku = (new PemangkuKepentinganModel())->findAll();
        $listPeraturan = (new PeraturanTerkaitModel())->findAll();
        $listTimKerja = (new TimKerjaModel())->findAll();
        $listSasaran = (new SasaranStrategisModel())->findAll();

        $db = \Config\Database::connect();

        $idTimAktif = (int) $activeKonteks['id_tim'];

        // Ambil indikator yang dipilih oleh Tim Kerja
        $selectedSasaranStrategis = $db->table('tim_sasaran_strategis tss')
            ->select('
        ss.id_sasaran_strategis,
        ss.id_sasaran,
        s.id_tujuan
    ')
            ->join(
                'sasaran_strategis ss',
                'ss.id_sasaran_strategis = tss.id_sasaran_strategis'
            )
            ->join(
                'sasaran s',
                's.id_sasaran = ss.id_sasaran'
            )
            ->where('tss.id_tim', $idTimAktif)
            ->orderBy('ss.id_sasaran_strategis', 'ASC')
            ->get()
            ->getRowArray();

        $selectedHierarki = $selectedSasaranStrategis ?? [];
        $listWilayah = (new WilayahModel())
            ->orderBy('nama_wilayah', 'ASC')
            ->findAll();

        $selectedProsesData = (new KonteksProsesBisnisModel())
            ->getByKonteks($id);

        $allProses = (new ProsesBisnisModel())
            ->orderBy('kode_proses', 'ASC')
            ->findAll();

        $sasaranOrganisasi = \Config\Database::connect()
            ->table('konteks_proses_bisnis kpb')
            ->select('
        kpb.id_konteks_proses,
        kpb.deskripsi_proses,
        pb.kode_proses,
        pb.jenis_proses,
        pb.uraian_proses,
        sk.id_sasaran,
        sk.uraian_sasaran
    ')
            ->join('proses_bisnis pb', 'pb.id_proses = kpb.id_proses')
            ->join('sasaran_kinerja sk', 'sk.id_konteks_proses = kpb.id_konteks_proses', 'left')
            ->where('kpb.id_konteks', $id)
            ->orderBy('pb.kode_proses', 'ASC')
            ->get()
            ->getResultArray();

        $user = session('user');

        $canManageContext = in_array(
            $user['role'] ?? '',
            ['admin', 'operator']
        );

        // Saat membuka konteks yang sudah ada, tampilkan mode view terlebih dahulu
        $mode = 'view';

        // Berubah ke mode edit hanya jika user menekan tombol Edit
        if (session('konteks_mode') === 'edit' && $canManageContext) {
            $mode = 'edit';
            session()->remove('konteks_mode');
        }

        $selectedPemangku = (new KonteksPemangkuModel())
            ->select('pemangku_kepentingan.*')
            ->join(
                'pemangku_kepentingan',
                'pemangku_kepentingan.id_pemangku = konteks_pemangku.id_pemangku'
            )
            ->where('konteks_pemangku.id_konteks', $id)
            ->findAll();

        $selectedPeraturan = (new KonteksPeraturanModel())
            ->select('peraturan_terkait.*')
            ->join(
                'peraturan_terkait',
                'peraturan_terkait.id_peraturan = konteks_peraturan.id_peraturan'
            )
            ->where('konteks_peraturan.id_konteks', $id)
            ->findAll();

        log_message('error', 'JUMLAH PEMANGKU: ' . count($selectedPemangku));
        log_message('error', 'JUMLAH PERATURAN: ' . count($selectedPeraturan));

        log_message('error', 'DATA PEMANGKU: ' . json_encode($selectedPemangku));
        log_message('error', 'DATA PERATURAN: ' . json_encode($selectedPeraturan));

        return view(
            'penetapan_konteks/index',
            array_merge(
                $this->contextData($id),
                [
                    'activeTab'         => 'konteks',
                    'mode' => $mode,
                    'hasKegiatan' => $hasTim,
                    'hasScope' => $hasScope,
                    'hideGlobalContext' => false,
                    'listPemangku'      => $listPemangku,
                    'listPeraturan'     => $listPeraturan,
                    'listTimKerja'      => $listTimKerja,
                    'listSasaran'       => $listSasaran,
                    'selectedSasaranStrategis' => $selectedSasaranStrategis,
                    'selectedHierarki' => $selectedHierarki,
                    'listWilayah'       => $listWilayah,
                    'pemilikRisiko'   => $pemilikRisiko,
                    'pengelolaRisiko' => $pengelolaRisiko,
                    'allProses'         => $allProses,
                    'sasaranOrganisasi' => $sasaranOrganisasi,
                    'selectedPemangku'  => $selectedPemangku,
                    'selectedPeraturan' => $selectedPeraturan,
                ]
            )
        );
    }

    public function redirectToActive()
    {
        $tahun      = session('global_tahun') ?? date('Y');
        $idTim      = session('global_id_tim') ?? session('id_tim');
        $idKegiatan = session('global_id_kegiatan');

        // PRIORITAS 1: Kegiatan tertentu dipilih
        if (!empty($idKegiatan)) {
            $konteks = $this->model
                ->where('tahun', $tahun)
                ->where('id_tim', $idTim)
                ->where('id_kegiatan', $idKegiatan)
                ->first();

            if ($konteks) {
                return redirect()->to(
                    site_url('penetapan-konteks/konteks/' . $konteks['id_konteks'])
                );
            }

            return redirect()->to(
                site_url('penetapan-konteks/konteks/0')
            );
        }

        // PRIORITAS 2: Semua Kegiatan
        $konteks = $this->model
            ->where('tahun', $tahun)
            ->where('id_tim', $idTim)
            ->orderBy('id_kegiatan', 'ASC')
            ->first();

        if (!$konteks) {
            return redirect()->to(
                site_url('penetapan-konteks/konteks/0')
            );
        }

        // PENTING:
        // Jangan mengubah global_id_kegiatan menjadi kegiatan pertama.
        // Biarkan tetap NULL agar Semua Kegiatan tetap terpilih.

        return redirect()->to(
            site_url('penetapan-konteks/konteks/' . $konteks['id_konteks'])
        );
    }


    public function edit($id)
    {
        if (!$this->validateKonteksAccess($id)) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Anda tidak memiliki akses untuk mengubah konteks ini.'
                );
        }
        $activeKonteks = $this->getActiveKonteks($id);

        if (!$activeKonteks) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (!$this->validateKonteksAccess($id)) {
            return redirect()->to(
                site_url('penetapan-konteks/konteks/' . $id)
            );
        }

        session()->set('konteks_mode', 'edit');

        return redirect()->to(
            site_url('penetapan-konteks/konteks/' . $id)
        );
    }

    /* INDEX (LOAD FULL PAGE) */
    public function index()
    {
        $listPemangku = (new PemangkuKepentinganModel())->findAll();
        $listPeraturan = (new PeraturanTerkaitModel())->findAll();
        $listTimKerja = (new TimKerjaModel())->findAll();
        $listSasaran = (new SasaranStrategisModel())->findAll();

        // FILTER
        $filterTahun  = $this->request->getGet('tahun');
        $filterTim    = $this->request->getGet('id_tim');
        $filterStatus = $this->request->getGet('status');

        $builder = $this->model
            ->select('
                konteks.*,
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
            ->join('pengelola_risiko g', 'g.id = konteks.pengelola_risiko_id', 'left');

        if (!empty($filterTahun)) {
            $builder->where('konteks.tahun', $filterTahun);
        }

        if (!empty($filterTim)) {
            $builder->where('konteks.id_tim', $filterTim);
        }

        if (!empty($filterStatus)) {

            if ($filterStatus === 'lengkap') {
                $builder->where('konteks.status', 'lengkap');
            }

            if ($filterStatus === 'draft') {
                $builder->groupStart()
                    ->where('konteks.status IS NULL', null, false)
                    ->orWhere('konteks.status !=', 'lengkap')
                    ->groupEnd();
            }
        }

       $perPage = (int) ($this->request->getGet('perPage') ?? 5);

        $data  = $builder->orderBy('tahun', 'DESC')->paginate($perPage);
        $pager = $this->model->pager;

        $total       = $pager->getTotal();
        $currentPage = $pager->getCurrentPage();
        $from        = ($currentPage - 1) * $perPage + 1;
        $to          = $from + count($data) - 1;

        if ($total == 0) {
            $from = 0;
            $to   = 0;
        }

        $listWilayah = (new WilayahModel())
            ->orderBy('nama_wilayah', 'ASC')
            ->findAll();

        $listTahun = $this->model
            ->select('tahun')
            ->distinct()
            ->orderBy('tahun', 'DESC')
            ->findAll();

        return view(
            'penetapan_konteks/index',
            array_merge(
                $this->contextData(),
                [
                    'activeTab'       => 'ruang_lingkup',
                    'data'            => $data,
                    'pager'           => $pager,
                    'from'            => $from,
                    'to'              => $to,
                    'total'           => $total,
                    'perPage'         => $perPage,
                    'listPemangku'    => $listPemangku,
                    'listPeraturan'   => $listPeraturan,
                    'listTimKerja' => $listTimKerja,
                    'listSasaran'     => $listSasaran,
                    'listWilayah'     => $listWilayah,
                    'listTahun' => $listTahun,
                    'filters' => [
                        'tahun'  => $filterTahun,
                        'id_tim' => $filterTim,
                        'status' => $filterStatus,
                    ],
                    'hideGlobalContext' => true,
                ]
            )
        );
    }

    public function createDraft()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $toInt = fn($v) => $v !== '' && $v !== null ? (int) $v : null;

        $data = [
            'tahun'       => (int) $this->request->getPost('tahun'),
            'id_tim'      => $toInt($this->request->getPost('id_tim')),
            'id_kegiatan' => $toInt($this->request->getPost('id_kegiatan')),
            'status'      => 'draft',
        ];
        $exists = $this->model
            ->where('tahun', $data['tahun'])
            ->where('id_kegiatan', $data['id_kegiatan'])
            ->first();

        if ($exists) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Kegiatan tersebut sudah memiliki ruang lingkup pada tahun yang sama.'
            ]);
        }

        $this->model->insert($data);

        $id = $this->model->getInsertID();
        session()->set([
            'global_tahun'       => $data['tahun'],
            'global_id_tim'      => $data['id_tim'],
            'global_id_kegiatan' => $data['id_kegiatan'],
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'id'     => $id,
        ]);
    }

    /* STORE (AJAX CREATE) */
    public function store()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $db = \Config\Database::connect();

        $idKonteks = (int) $this->request->getPost('id_konteks');

        $idIndikator = $this->request->getPost('id_sasaran_strategis');
        $idIndikator = ($idIndikator !== '' && $idIndikator !== null)
            ? (int) $idIndikator
            : null;

        // Validasi indikator jika dipilih
        if ($idIndikator !== null) {
            $indikatorValid = $db->table('sasaran_strategis')
                ->where('id_sasaran_strategis', $idIndikator)
                ->countAllResults();

            if (!$indikatorValid) {
                return $this->response
                    ->setStatusCode(422)
                    ->setJSON([
                        'status' => 'error',
                        'message' => 'Indikator Sasaran Strategis tidak valid.'
                    ]);
            }
        }

        // Ambil Tim Kerja dari konteks
        $konteks = $db->table('konteks')
            ->select('id_tim')
            ->where('id_konteks', $idKonteks)
            ->get()
            ->getRowArray();

        if (!$konteks) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Konteks tidak ditemukan.'
                ]);
        }

        $idTim = (int) $konteks['id_tim'];

        $db->transStart();

        // Update status konteks saja.
        // Indikator tidak lagi disimpan di tabel konteks.
        $db->table('konteks')
            ->where('id_konteks', $idKonteks)
            ->update([
                'status' => 'lengkap'
            ]);

        // Ganti pilihan indikator milik Tim Kerja.
        $db->table('tim_sasaran_strategis')
            ->where('id_tim', $idTim)
            ->delete();

        if ($idIndikator !== null) {
            $db->table('tim_sasaran_strategis')->insert([
                'id_tim' => $idTim,
                'id_sasaran_strategis' => $idIndikator
            ]);
        }

        // Simpan pemangku
        $pemangkuModel = new KonteksPemangkuModel();

        $pemangkuModel
            ->where('id_konteks', $idKonteks)
            ->delete();

        foreach ($this->request->getPost('pemangku') ?? [] as $idPemangku) {
            $pemangkuModel->insert([
                'id_konteks' => $idKonteks,
                'id_pemangku' => $idPemangku
            ]);
        }

        // Simpan peraturan
        $peraturanModel = new KonteksPeraturanModel();

        $peraturanModel
            ->where('id_konteks', $idKonteks)
            ->delete();

        foreach ($this->request->getPost('peraturan') ?? [] as $idPeraturan) {
            $peraturanModel->insert([
                'id_konteks' => $idKonteks,
                'id_peraturan' => $idPeraturan
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal menyimpan data.'
                ]);
        }

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => 'Data berhasil disimpan',
            'redirect' => site_url('identifikasi-risiko')
        ]);
    }

    /* UPDATE (AJAX EDIT) */
    public function update()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $id = (int) $this->request->getPost('id_konteks');

        if (!$this->validateKonteksAccess($id)) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Tidak punya akses'
                ]);
        }

        $db = \Config\Database::connect();

        $idIndikator = $this->request->getPost('id_sasaran_strategis');
        $idIndikator = ($idIndikator !== '' && $idIndikator !== null)
            ? (int) $idIndikator
            : null;

        // Validasi indikator jika dipilih
        if ($idIndikator !== null) {
            $indikatorValid = $db->table('sasaran_strategis')
                ->where('id_sasaran_strategis', $idIndikator)
                ->countAllResults();

            if (!$indikatorValid) {
                return $this->response
                    ->setStatusCode(422)
                    ->setJSON([
                        'status' => 'error',
                        'message' => 'Indikator Sasaran Strategis tidak valid.'
                    ]);
            }
        }

        // Ambil Tim Kerja dari konteks
        $konteks = $db->table('konteks')
            ->select('id_tim')
            ->where('id_konteks', $id)
            ->get()
            ->getRowArray();

        if (!$konteks) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Konteks tidak ditemukan.'
                ]);
        }

        $idTim = (int) $konteks['id_tim'];

        $db->transStart();

        // Ganti pilihan indikator milik Tim Kerja.
        $db->table('tim_sasaran_strategis')
            ->where('id_tim', $idTim)
            ->delete();

        if ($idIndikator !== null) {
            $db->table('tim_sasaran_strategis')->insert([
                'id_tim' => $idTim,
                'id_sasaran_strategis' => $idIndikator
            ]);
        }

        // UPDATE PEMANGKU
        $pemangkuModel = new KonteksPemangkuModel();

        $pemangkuModel
            ->where('id_konteks', $id)
            ->delete();

        foreach ($this->request->getPost('pemangku') ?? [] as $idPemangku) {
            $pemangkuModel->insert([
                'id_konteks' => $id,
                'id_pemangku' => $idPemangku
            ]);
        }

        // UPDATE PERATURAN
        $peraturanModel = new KonteksPeraturanModel();

        $peraturanModel
            ->where('id_konteks', $id)
            ->delete();

        foreach ($this->request->getPost('peraturan') ?? [] as $idPeraturan) {
            $peraturanModel->insert([
                'id_konteks' => $id,
                'id_peraturan' => $idPeraturan
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal memperbarui data.'
                ]);
        }

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => 'Data berhasil diubah',
            'redirect' => site_url(
                'penetapan-konteks/konteks/' . $id
            )
        ]);
    }

    /* DELETE (AJAX) */
    public function delete()
    {
        $id = $this->request->getPost('id_konteks');

        if (!$this->validateKonteksAccess($id)) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Tidak punya akses'
                ]);
        }
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $this->model->delete($this->request->getPost('id_konteks'));

        return $this->response->setJSON(['status' => 'success', 'message' => 'Data berhasil dihapus']);
    }

    /**
     * Mengambil seluruh data Tujuan
     */
    public function getTujuan()
    {
        $db = \Config\Database::connect();

        $data = $db->table('tujuan')
            ->orderBy('id_tujuan', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON($data);
    }

    /**
     * Mengambil Sasaran berdasarkan Tujuan
     */
    public function getSasaran($idTujuan)
    {
        $idTujuan = (int) $idTujuan;

        if ($idTujuan <= 0) {
            return $this->response->setJSON([]);
        }

        $db = \Config\Database::connect();

        $data = $db->table('sasaran')
            ->where('id_tujuan', $idTujuan)
            ->orderBy('id_sasaran', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON($data);
    }

    /**
     * Mengambil Indikator berdasarkan Sasaran
     */
    public function getIndikator($idSasaran)
    {
        $idSasaran = (int) $idSasaran;

        if ($idSasaran <= 0) {
            return $this->response->setJSON([]);
        }

        $db = \Config\Database::connect();

        $data = $db->table('sasaran_strategis')
            ->where('id_sasaran', $idSasaran)
            ->orderBy('id_sasaran_strategis', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON($data);
    }

    /**
     * Mengambil hierarki Tujuan, Sasaran, dan Indikator
     * berdasarkan ID indikator yang sudah tersimpan.
     */
    public function getHierarkiIndikator($idIndikator)
    {
        $db = \Config\Database::connect();

        $data = $db->table('sasaran_strategis ss')
            ->select('
            ss.id_sasaran_strategis,
            s.id_sasaran,
            s.uraian_sasaran,
            t.id_tujuan,
            t.uraian_tujuan
        ')
            ->join('sasaran s', 's.id_sasaran = ss.id_sasaran')
            ->join('tujuan t', 't.id_tujuan = s.id_tujuan')
            ->where('ss.id_sasaran_strategis', (int) $idIndikator)
            ->get()
            ->getRowArray();

        return $this->response->setJSON($data ?? []);
    }

    /* AJAX TABLE REFRESH */
    public function ajaxTable()
    {
        $data = $this->model
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
            ->orderBy('tahun', 'DESC')
            ->findAll();

        return view('penetapan_konteks/tabs/ruang_lingkup/_table_section', [
            'data'    => $data,
            'from'    => 1,
            'to'      => count($data),
            'total'   => count($data),
            'filters' => [],
            'perPage' => 5,
        ]);
    }

    /* SET ACTIVE */
    public function setActive()
    {
        $id = $this->request->getPost('id_konteks');
        if (!$id) return redirect()->back();
        session()->set('id_konteks_aktif', $id);
        $redirect = $this->request->getPost('redirect') ?? site_url('penetapan-konteks');
        return redirect()->to($redirect);
    }

    public function detail($id)
    {
        if (!$this->request->isAJAX()) return redirect()->back();

        $konteks = $this->model
            ->select('konteks.*, kegiatan.nama_kegiatan, tim_kerja.nama_tim, sasaran_strategis.uraian_sasaran, wilayah.nama_wilayah, wilayah.tipe')
            ->join('kegiatan', 'kegiatan.id_kegiatan = konteks.id_kegiatan', 'left')
            ->join('tim_kerja', 'tim_kerja.id_tim = konteks.id_tim', 'left')
            ->join('sasaran_strategis', 'sasaran_strategis.id_sasaran_strategis = konteks.id_sasaran_strategis', 'left')
            ->join('pengelola_risiko p', 'p.id = konteks.pemilik_risiko_id', 'left')
            ->join('pengelola_risiko g', 'g.id = konteks.pengelola_risiko_id', 'left')
            ->join('wilayah', 'wilayah.id = p.wilayah_id', 'left')
            ->where('konteks.id_konteks', $id)
            ->first();

        $pemangku  = (new KonteksPemangkuModel())->select('id_pemangku')->where('id_konteks', $id)->findAll();
        $peraturan = (new KonteksPeraturanModel())->select('id_peraturan')->where('id_konteks', $id)->findAll();

        // Ambil sasaran kinerja untuk konteks ini
        $db = \Config\Database::connect();
        $sasaranKinerja = $db->table('sasaran_kinerja sk')
            ->select('sk.uraian_sasaran, pb.kode_proses, pb.jenis_proses, pb.uraian_proses')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = sk.id_konteks_proses')
            ->join('proses_bisnis pb', 'pb.id_proses = kpb.id_proses')
            ->where('kpb.id_konteks', $id)
            ->orderBy('pb.kode_proses', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'konteks'        => $konteks,
            'pemangku'       => array_column($pemangku, 'id_pemangku'),
            'peraturan'      => array_column($peraturan, 'id_peraturan'),
            'sasaranKinerja' => $sasaranKinerja,
        ]);
    }

    /* GET PEMILIK PROVINSI (AJAX) */
    public function getPemilikProvinsi()
    {
        $data = (new PengelolaRisikoModel())
            ->where('is_pemilik', true)
            ->first();

        return $this->response->setJSON($data);
    }
    /* GET PENGELOLA LIST BY SATUAN KERJA & TAHUN (AJAX)
       → sekarang pakai penugasan_pengelola */
    public function getPengelolaList()
    {
        $id_tim = (int) $this->request->getGet('tim');
        $tahun  = (int) ($this->request->getGet('tahun') ?? date('Y'));

        if (!$id_tim) {
            return $this->response->setJSON([]);
        }

        $db = \Config\Database::connect();

        // ambil ketua tim sesuai tahun
        $data = $db->table('penugasan_pengelola pp')
            ->select('
            pr.id,
            pr.nama,
            pr.nip,
            pr.jabatan,
            pp.tahun
        ')
            ->join('pengelola_risiko pr', 'pr.id = pp.pengelola_id')
            ->where('pp.tim_kerja_id', $id_tim)
            ->where('pp.tahun', $tahun)
            ->where('pp.is_ketua_tim', true)
            ->orderBy('pp.id', 'DESC')
            ->get()
            ->getRowArray();

        // fallback tahun lain
        if (!$data) {
            $data = $db->table('penugasan_pengelola pp')
                ->select('
                pr.id,
                pr.nama,
                pr.nip,
                pr.jabatan,
                pp.tahun
            ')
                ->join('pengelola_risiko pr', 'pr.id = pp.pengelola_id')
                ->where('pp.tim_kerja_id', $id_tim)
                ->where('pp.is_ketua_tim', true)
                ->orderBy('pp.tahun', 'DESC')
                ->get()
                ->getRowArray();

            if ($data) {
                $data['is_fallback'] = true;
            }
        }

        return $this->response->setJSON($data ?? []);
    }
    /* GET KEGIATAN BY TIM KERJA (AJAX) */
    public function getKegiatanByTim($id)
    {
        // if (!$this->request->isAJAX()) {
        //     return redirect()->back();
        // }

        $tahun = $this->request->getGet('tahun');

        $usedKegiatan = [];

        $usedKegiatan = $this->model
            ->where('tahun', $tahun)
            ->findColumn('id_kegiatan');

        $usedKegiatan = array_filter(
            $usedKegiatan,
            fn($v) => !empty($v)
        );

        $builder = (new KegiatanModel())
            ->where('id_tim', $id);

        if (!empty($usedKegiatan)) {
            $builder->whereNotIn('id_kegiatan', $usedKegiatan);
        }

        $data = $builder
            ->orderBy('nama_kegiatan', 'ASC')
            ->findAll();

        // DEBUG
        log_message('error', json_encode([
            'tahun' => $tahun,
            'id_tim' => $id,
            'usedKegiatan' => $usedKegiatan,
            'result' => $data,
        ]));

        return $this->response->setJSON($data);
    }

    public function resetActive()
    {
        session()->remove('id_konteks_aktif');
        $redirect = $this->request->getPost('redirect') ?? site_url('penetapan-konteks/proses-bisnis');
        return redirect()->to($redirect);
    }
}
