<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PemantauanRisikoModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class PelaporanRisikoController extends BaseController
{
    protected $db;
    protected $pemantauanModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->pemantauanModel = new PemantauanRisikoModel();
    }

    private function getListKonteks()
    {
        return $this->db->table('konteks k')
            ->select('k.id_konteks,k.tahun,k.id_tim,k.id_kegiatan,k.pengelola_risiko_id,g.nama as nama_pengelola,sk.nama_tim,kegiatan.nama_kegiatan')
            ->join('tim_kerja sk', 'sk.id_tim=k.id_tim', 'left')
            ->join('kegiatan', 'kegiatan.id_kegiatan=k.id_kegiatan', 'left')
            ->join('pengelola_risiko g', 'g.id=k.pengelola_risiko_id', 'left')
            ->orderBy('k.created_at', 'DESC')
            ->get()->getResultArray();
    }

    private function getPeriode()
    {
        $periode = session('pl_periode');
        if (!$periode) {
            $periode = ['bulan' => date('m'), 'tahun' => date('Y')];
        }
        return $periode;
    }

    private function getDateRange($bulan, $tahun, $type = 'bulanan')
    {
        $startDate = date('Y-m-01', strtotime("$tahun-$bulan-01"));
        if ($type === '3bulan') {
            $endDate = date('Y-m-t', strtotime("+2 months", strtotime($startDate)));
        } else {
            $endDate = date('Y-m-t', strtotime($startDate));
        }
        return [$startDate, $endDate];
    }

    public function setActive()
    {
        $id = $this->request->getPost('id_konteks');
        $tahun = $this->request->getPost('tahun');
        $triwulan = $this->request->getPost('triwulan');

        if ($id) {
            session()->set('id_konteks_pl', $id);
        }

        if ($tahun) {
            session()->set('pl_periode', [
                'tahun' => $tahun,
                'triwulan' => $triwulan
            ]);
        }

        return redirect()->to('/pelaporan-risiko');
    }

    public function index()
    {
        $userRole  = session('user_role');
        $idKonteks = session('id_konteks_pl');


        $tahun = (int) ($this->request->getGet('tahun') ?? date('Y'));
        $triwulan = $this->request->getGet('triwulan') ?? '';

        $rentangTriwulan = [
            1 => ['01-01', '03-31'],
            2 => ['04-01', '06-30'],
            3 => ['07-01', '09-30'],
            4 => ['10-01', '12-31'],
        ];

        if ($triwulan !== '' && isset($rentangTriwulan[(int) $triwulan])) {
            $triwulan = (int) $triwulan;

            $startDate = $tahun . '-' . $rentangTriwulan[$triwulan][0];
            $endDate   = $tahun . '-' . $rentangTriwulan[$triwulan][1];
        } else {
            $triwulan = '';
            $startDate = null;
            $endDate = null;
        }

        $periode = [
            'tahun' => $tahun,
            'triwulan' => $triwulan,
        ];

        $type = 'triwulan';

        $idKegiatan = $this->request->getGet('id_kegiatan');
        $statusValidasi = $this->request->getGet('status_validasi');

        $builder = $this->db->table('rencana_penanganan_risiko rtp')
            ->select('
                rtp.id_rtp,
                rtp.uraian_rtp,
                rtp.target_output,
                rtp.target_waktu,
                ir.pernyataan_risiko,
                sk.nama_tim,
                kegiatan.id_kegiatan,
                kegiatan.nama_kegiatan,
                pm.realisasi_output,
                pm.realisasi_waktu,
                COALESCE(pm.status, \'Belum Dilaksanakan\') as status,
                pm.status_validasi,
                pm.catatan_validasi,
            ')
            ->join('evaluasi_risiko er', 'er.id_evaluasi = rtp.id_penilaian_awal')
            ->join('identifikasi_risiko ir', 'ir.id_identifikasi = er.id_identifikasi')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks')
            ->join('tim_kerja sk', 'sk.id_tim = k.id_tim', 'left')
            ->join('kegiatan', 'kegiatan.id_kegiatan = k.id_kegiatan', 'left')
            ->join('pemantauan_risiko pm', 'pm.id_rtp = rtp.id_rtp', 'left');

        $ketuaInfo = null;
        $idTim = null;

        if (in_array($userRole, ['operator', 'ketua'], true)) {

            // Semua tim yang memang dimiliki user
            $userTim = array_map('intval', session('user_tim') ?? []);

            $globalTim = session('global_id_tim') ?: session('id_tim');

            // Fallback legacy jika user_tim belum tersedia
            if (empty($userTim) && session('id_tim')) {
                $userTim = [(int) session('id_tim')];
            }

            // Gunakan global tim hanya jika memang dimiliki user
            if (
                $globalTim
                && in_array((int) $globalTim, $userTim, true)
            ) {
                $idTim = (int) $globalTim;
            } elseif (!empty($userTim)) {
                // Kalau global tim kosong/tidak valid,
                // gunakan tim pertama yang dimiliki user
                $idTim = $userTim[0];
            }

            if ($idTim) {

                // Data Pelaporan hanya untuk tim aktif
                $builder->where('k.id_tim', $idTim);

                // Informasi tim + ketua tim aktif
                $ketuaInfo = $this->db->table('tim_kerja sk')
                    ->select('sk.nama_tim, g.nama')
                    ->join(
                        'penugasan_pengelola p',
                        'p.tim_kerja_id = sk.id_tim',
                        'left'
                    )
                    ->join(
                        'pengelola_risiko g',
                        'g.id = p.pengelola_id',
                        'left'
                    )
                    ->where('sk.id_tim', $idTim)
                    ->where('p.is_ketua_tim', true)
                    ->get()
                    ->getRowArray();
            }
        } else {

            // Admin
            if ($idKonteks) {
                $builder->where('kpb.id_konteks', $idKonteks);
            }
        }

        if (!empty($idKegiatan)) {
            $builder->where('k.id_kegiatan', $idKegiatan);
        }

        if (!empty($statusValidasi)) {
            $builder->where('pm.status_validasi', $statusValidasi);
        }


        $builder->where('k.tahun', $tahun);

        if ($triwulan !== '') {
            $builder
                ->where('rtp.target_waktu >=', $startDate)
                ->where('rtp.target_waktu <', date('Y-m-d', strtotime($endDate . ' +1 day')));
        }

        $builder->orderBy('kegiatan.nama_kegiatan', 'ASC');
        $builder->orderBy('rtp.id_rtp', 'ASC');

        $allData = $builder->get()->getResultArray();

        // =====================================================
        // STATUS VALIDASI PER KEGIATAN
        // DIHITUNG BERDASARKAN SELURUH RTP DALAM 1 TAHUN
        // =====================================================

        $statusBuilder = $this->db->table('rencana_penanganan_risiko rtp')
            ->select('
        k.id_kegiatan,
        pm.status_validasi,
        pm.catatan_validasi
    ')
            ->join('evaluasi_risiko er', 'er.id_evaluasi = rtp.id_penilaian_awal')
            ->join('identifikasi_risiko ir', 'ir.id_identifikasi = er.id_identifikasi')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks')
            ->join('pemantauan_risiko pm', 'pm.id_rtp = rtp.id_rtp', 'left')
            ->where('k.tahun', $tahun);

        if ($triwulan !== '') {
            $statusBuilder
                ->where('rtp.target_waktu >=', $startDate)
                ->where('rtp.target_waktu <', date('Y-m-d', strtotime($endDate . ' +1 day')));
        }

        // Batasi tim sesuai tim aktif
        if ($idTim) {
            $statusBuilder->where('k.id_tim', $idTim);
        }

        // Jika kegiatan tertentu dipilih
        if (!empty($idKegiatan)) {
            $statusBuilder->where('k.id_kegiatan', $idKegiatan);
        }

        $statusRows = $statusBuilder->get()->getResultArray();

        $statusKegiatan = [];

        foreach ($statusRows as $row) {

            $idKegiatanRow = $row['id_kegiatan'];

            if (!isset($statusKegiatan[$idKegiatanRow])) {
                $statusKegiatan[$idKegiatanRow] = [
                    'statuses' => [],
                    'catatan' => null,
                ];
            }

            $status = $row['status_validasi'] ?? 'Draft';

            $statusKegiatan[$idKegiatanRow]['statuses'][] = $status;

            if (
                $status === 'Ditolak'
                && !empty($row['catatan_validasi'])
            ) {
                $statusKegiatan[$idKegiatanRow]['catatan'] =
                    $row['catatan_validasi'];
            }
        }

        foreach ($statusKegiatan as &$item) {

            $statuses = array_unique($item['statuses']);

            if (in_array('Ditolak', $statuses, true)) {
                $item['status'] = 'Ditolak';
            } elseif (in_array('Diajukan', $statuses, true)) {
                $item['status'] = 'Diajukan';
            } elseif (
                !empty($statuses)
                && count(array_filter(
                    $statuses,
                    fn($s) => $s === 'Disetujui'
                )) === count($statuses)
            ) {
                $item['status'] = 'Disetujui';
            } else {
                $item['status'] = 'Draft';
            }
        }

        unset($item);

        $page    = (int)($this->request->getGet('page') ?? 1);
        $perPage = (int)($this->request->getGet('perPage') ?? 10);

        $total  = count($allData);
        $offset = ($page - 1) * $perPage;
        $data   = array_slice($allData, $offset, $perPage);

        $pager = [
            'currentPage' => $page,
            'totalPages' => (int)ceil($total / $perPage),
        ];

        $from = $total > 0 ? $offset + 1 : 0;
        $to   = min($offset + $perPage, $total);

        $summary = [
            'total' => $total,
            'selesai' => 0,
            'dalam_proses' => 0,
            'belum' => 0,
            'terlambat' => 0,
        ];

        foreach ($allData as $row) {
            switch ($row['status']) {
                case 'Selesai':
                    $summary['selesai']++;
                    break;
                case 'Dalam Proses':
                    $summary['dalam_proses']++;
                    break;
                case 'Terlambat':
                    $summary['terlambat']++;
                    break;
                default:
                    $summary['belum']++;
                    break;
            }
        }

        $kegiatanBuilder = $this->db->table('kegiatan')
            ->select('id_kegiatan, nama_kegiatan, id_tim')
            ->whereIn(
                'id_kegiatan',
                function ($subquery) use ($tahun) {
                    $subquery->select('id_kegiatan')
                        ->from('konteks')
                        ->where('tahun', $tahun);
                }
            );

        if ($idTim) {
            $kegiatanBuilder->where('id_tim', $idTim);
        }

        $listKegiatan = $kegiatanBuilder
            ->orderBy('nama_kegiatan', 'ASC')
            ->get()
            ->getResultArray();


        return view('pelaporan_risiko/index', [
            'data' => $data,
            'summary' => $summary,
            'listKonteks' => $this->getListKonteks(),
            'periode' => $periode,
            'userRole' => $userRole,
            'pager' => $pager,
            'perPage' => $perPage,
            'idKegiatan'  => $idKegiatan,
            'total' => $total,
            'from' => $from,
            'to' => $to,
            'ketuaInfo' => $ketuaInfo,
            'statusKegiatan' => $statusKegiatan,
            'tipe_periode' => $type,
            'tahun' => $tahun,
            'triwulan' => $triwulan,
            'activeKonteks' => [
                'id_tim' => $idTim
                    ?? $this->request->getGet('id_tim')
                    ?? session('global_id_tim')
                    ?? session('id_tim'),

                'pengelola_risiko_id' =>
                $this->request->getGet('pengelola_risiko_id'),
            ],
            'listKegiatan' => $listKegiatan,
            'selectedKegiatan' => $idKegiatan,
            'statusValidasi' => $statusValidasi,
            'hideGlobalContext' => true,
        ]);
    }

    public function detail($id)
    {
        $data = $this->db->table('rencana_penanganan_risiko rtp')
            ->select('
            rtp.id_rtp,
            rtp.uraian_rtp,
            rtp.target_output,
            rtp.target_waktu,
            ir.pernyataan_risiko,
            ir.penyebab_risiko,
            ir.dampak_risiko,
            sk.nama_tim,
            kegiatan.nama_kegiatan,
            g.nama as nama_pengelola,
            k.tahun,
            ss.uraian_sasaran as sasaran_strategis,
            pb.kode_proses,
            pb.uraian_proses,
            sk_kinerja.uraian_sasaran as sasaran_kinerja,
            pr.nilai_risiko,
            pr.warna_risiko,
            pr.tindakan as tindakan_selera,
            pr.efektivitas,
            pr.uraian_pengendalian,
            sl.nama_level as nama_selera,
            km.level as level_kemungkinan,
            kd.level as level_dampak,
            pm.realisasi_output,
            pm.realisasi_waktu,
            COALESCE(pm.status, \'Belum Dilaksanakan\') as status,
            pm.status_validasi,
            pm.catatan_validasi,
            km_res.level as level_kemungkinan_residu,
            kd_res.level as level_dampak_residu,
            bp.url_link as link_bukti')
            ->join('evaluasi_risiko er', 'er.id_evaluasi = rtp.id_penilaian_awal')
            ->join('identifikasi_risiko ir', 'ir.id_identifikasi = er.id_identifikasi')
            ->join('penilaian_risiko pr', 'pr.id_penilaian = er.id_penilaian', 'left')
            ->join('kriteria_kemungkinan km', 'km.id_kriteria = pr.id_kemungkinan', 'left')
            ->join('kriteria_dampak kd', 'kd.id_kriteria = pr.id_dampak', 'left')
            ->join('selera_risiko sl', 'sl.id_selera = pr.id_selera', 'left')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('proses_bisnis pb', 'pb.id_proses = kpb.id_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks')
            ->join('tim_kerja sk', 'sk.id_tim = k.id_tim', 'left')
            ->join('kegiatan', 'kegiatan.id_kegiatan = k.id_kegiatan', 'left')
            ->join('sasaran_strategis ss', 'ss.id_sasaran_strategis = k.id_sasaran_strategis', 'left')
            ->join('pengelola_risiko g', 'g.id = k.pengelola_risiko_id', 'left')
            ->join('sasaran_kinerja sk_kinerja', 'sk_kinerja.id_konteks_proses = ir.id_konteks_proses', 'left')
            ->join('pemantauan_risiko pm', 'pm.id_rtp = rtp.id_rtp', 'left')
            ->join('bukti_pemantauan bp', 'bp.id_pemantauan = pm.id_pemantauan', 'left')
            ->join('kriteria_kemungkinan km_res', 'km_res.id_kriteria = rtp.id_kemungkinan_residu', 'left')
            ->join('kriteria_dampak kd_res', 'kd_res.id_kriteria = rtp.id_dampak_residu', 'left')
            ->where('rtp.id_rtp', $id)
            ->get()->getRowArray();

        helper('selera_risiko');

        $probResidu = (int) ($data['level_kemungkinan_residu'] ?? 0);
        $dampakResidu = (int) ($data['level_dampak_residu'] ?? 0);

        $nilaiResidu = $probResidu * $dampakResidu;

        $data['nilai_residu'] = $nilaiResidu;

        // ambil master selera risiko
        $seleraList = $this->db->table('selera_risiko')
            ->get()
            ->getResultArray();

        // mapping berdasarkan nilai residu
        $seleraResidu = selera_risiko_by_nilai(
            $nilaiResidu,
            $seleraList
        );

        $data['nama_selera_residu'] =
            $seleraResidu['nama_level'] ?? '-';

        $data['warna_residu'] =
            $seleraResidu['warna'] ?? 'secondary';

        $data['tindakan_residu'] =
            $seleraResidu['tindakan'] ?? '-';

        return $this->response->setJSON($data);
    }

    public function ajukan()
    {
        $payload = $this->request->getJSON(true);

        $idKegiatan = $payload['id_kegiatan'] ?? null;
        $idTim      = session('global_id_tim') ?? session('id_tim');

        $periode = $this->getPeriode();
        $tahun   = $periode['tahun'] ?? null;

        if (!$idKegiatan) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'ID kegiatan wajib']);
        }

        // Ambil seluruh RTP pada kegiatan + tahun aktif
        $rtpList = $this->db->table('rencana_penanganan_risiko rtp')
            ->select('
            rtp.id_rtp,
            pm.id_pemantauan
        ')
            ->join(
                'evaluasi_risiko er',
                'er.id_evaluasi = rtp.id_penilaian_awal'
            )
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
            ->join(
                'pemantauan_risiko pm',
                'pm.id_rtp = rtp.id_rtp',
                'left'
            )
            ->where('k.id_kegiatan', $idKegiatan)
            ->where('k.id_tim', $idTim);

        if (!empty($tahun)) {
            $rtpList->where('k.tahun', $tahun);
        }

        $rtpList = $rtpList
            ->get()
            ->getResultArray();

        if (empty($rtpList)) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'error' => 'Data RTP tidak ditemukan'
                ]);
        }

        $this->db->transStart();

        foreach ($rtpList as $rtp) {

            // Belum mempunyai record pemantauan
            if (empty($rtp['id_pemantauan'])) {

                $this->db->table('pemantauan_risiko')->insert([
                    'id_rtp'            => $rtp['id_rtp'],
                    'status_validasi'    => 'Diajukan',
                    'created_at'         => date('Y-m-d H:i:s'),
                    'updated_at'         => date('Y-m-d H:i:s'),
                ]);
            } else {

                // Sudah mempunyai record pemantauan
                $this->db->table('pemantauan_risiko')
                    ->where(
                        'id_pemantauan',
                        $rtp['id_pemantauan']
                    )
                    ->update([
                        'status_validasi' => 'Diajukan',
                        'updated_at'      => date('Y-m-d H:i:s'),
                    ]);
            }
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'error' => 'Gagal mengajukan laporan'
                ]);
        }

        return $this->response->setJSON([
            'success' => true
        ]);
    }

    public function batalAjukan()
    {
        if (session('user_role') !== 'operator') {
            return $this->response
                ->setStatusCode(403)
                ->setJSON(['error' => 'Akses ditolak']);
        }

        $payload = $this->request->getJSON(true);

        $idKegiatan = $payload['id_kegiatan'] ?? null;
        $idTim      = session('global_id_tim') ?? session('id_tim');

        if (!$idKegiatan) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'ID kegiatan wajib']);
        }

        $rtpList = $this->db->table('rencana_penanganan_risiko rtp')
            ->select('pm.id_pemantauan')
            ->join('evaluasi_risiko er', 'er.id_evaluasi = rtp.id_penilaian_awal')
            ->join('identifikasi_risiko ir', 'ir.id_identifikasi = er.id_identifikasi')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks')
            ->join('pemantauan_risiko pm', 'pm.id_rtp = rtp.id_rtp')
            ->where('k.id_kegiatan', $idKegiatan)
            ->where('k.id_tim', $idTim)
            ->where('pm.status_validasi', 'Diajukan')
            ->get()
            ->getResultArray();

        if (empty($rtpList)) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => 'Pengajuan tidak ditemukan']);
        }

        $ids = array_column($rtpList, 'id_pemantauan');

        $this->db->table('pemantauan_risiko')
            ->whereIn('id_pemantauan', $ids)
            ->update([
                'status_validasi'  => 'Draft',
                'catatan_validasi' => null,
                'validated_by'     => null,
                'validated_at'     => null,
                'updated_at'       => date('Y-m-d H:i:s'),
            ]);

        return $this->response->setJSON([
            'success' => true
        ]);
    }

    public function approveKegiatan($idKegiatan)
    {
        if (session('user_role') !== 'ketua') {
            return $this->response
                ->setStatusCode(403)
                ->setJSON(['error' => 'Akses ditolak']);
        }

        // Tim yang boleh diakses user
        $userTim = array_map('intval', session('user_tim') ?? []);

        // Fallback legacy
        if (empty($userTim) && session('id_tim')) {
            $userTim = [(int) session('id_tim')];
        }

        // Tim aktif
        $idTim = (int) (
            $this->request->getGet('id_tim')
            ?? session('global_id_tim')
            ?? 0
        );

        // Pastikan user memang punya akses ke tim tersebut
        if (!$idTim || !in_array($idTim, $userTim, true)) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'error' => 'Tim kerja tidak valid atau tidak dapat diakses'
                ]);
        }

        $periode = $this->getPeriode();
        $tahun = $periode['tahun'] ?? null;

        $rtpList = $this->db->table('rencana_penanganan_risiko rtp')
            ->select('pm.id_pemantauan')
            ->join(
                'evaluasi_risiko er',
                'er.id_evaluasi = rtp.id_penilaian_awal'
            )
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
            ->join(
                'pemantauan_risiko pm',
                'pm.id_rtp = rtp.id_rtp'
            )
            ->where('k.id_kegiatan', $idKegiatan)
            ->where('k.id_tim', $idTim)
            ->where('pm.status_validasi', 'Diajukan');

        // Approval mengikuti kegiatan dan tim yang dipilih.
        // Tidak membatasi tahun dari filter periode halaman.

        log_message('debug', 'PERIODE APPROVE: ' . json_encode([
            'pl_periode' => session('pl_periode'),
            'global_tahun' => session('global_tahun'),
            'tahun_dipakai' => $tahun,
        ]));

        log_message('debug', 'APPROVE KEGIATAN: ' . json_encode([
            'id_kegiatan' => $idKegiatan,
            'id_tim'      => $idTim,
            'tahun'       => $tahun,
        ]));

        $rtpList = $rtpList
            ->get()
            ->getResultArray();

        log_message('debug', 'HASIL APPROVE: ' . json_encode([
            'jumlah' => count($rtpList),
            'data'   => $rtpList,
        ]));

        if (empty($rtpList)) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'error' => 'Data pengajuan tidak ditemukan'
                ]);
        }

        $ids = array_column($rtpList, 'id_pemantauan');

        $this->db->table('pemantauan_risiko')
            ->whereIn('id_pemantauan', $ids)
            ->update([
                'status_validasi' => 'Disetujui',
                'validated_by'    => session('user_id'),
                'validated_at'    => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ]);

        return $this->response->setJSON([
            'success' => true
        ]);
    }

    public function rejectKegiatan($idKegiatan)
    {
        if (session('user_role') !== 'ketua') {
            return $this->response
                ->setStatusCode(403)
                ->setJSON(['error' => 'Akses ditolak']);
        }

        $payload = $this->request->getJSON(true);

        // Tim yang boleh diakses user
        $userTim = array_map('intval', session('user_tim') ?? []);

        // Fallback legacy
        if (empty($userTim) && session('id_tim')) {
            $userTim = [(int) session('id_tim')];
        }

        // Tim aktif
        $idTim = (int) (
            $this->request->getGet('id_tim')
            ?? session('global_id_tim')
            ?? 0
        );

        // Pastikan user memang punya akses ke tim tersebut
        if (!$idTim || !in_array($idTim, $userTim, true)) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'error' => 'Tim kerja tidak valid atau tidak dapat diakses'
                ]);
        }

        $periode = $this->getPeriode();
        $tahun = $periode['tahun'] ?? null;

        $rtpList = $this->db->table('rencana_penanganan_risiko rtp')
            ->select('pm.id_pemantauan')
            ->join(
                'evaluasi_risiko er',
                'er.id_evaluasi = rtp.id_penilaian_awal'
            )
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
            ->join(
                'pemantauan_risiko pm',
                'pm.id_rtp = rtp.id_rtp'
            )
            ->where('k.id_kegiatan', $idKegiatan)
            ->where('k.id_tim', $idTim)
            ->where('pm.status_validasi', 'Diajukan');

        if (!empty($tahun)) {
            $rtpList->where('k.tahun', $tahun);
        }

        $rtpList = $rtpList
            ->get()
            ->getResultArray();

        if (empty($rtpList)) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'error' => 'Data pengajuan tidak ditemukan'
                ]);
        }

        $ids = array_column($rtpList, 'id_pemantauan');

        $this->db->table('pemantauan_risiko')
            ->whereIn('id_pemantauan', $ids)
            ->update([
                'status_validasi'  => 'Ditolak',
                'catatan_validasi' => $payload['alasan'] ?? null,
                'validated_by'     => session('user_id'),
                'validated_at'     => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ]);

        return $this->response->setJSON([
            'success' => true
        ]);
    }

    public function checkForm4()
    {
        $idKegiatan = $this->request->getGet('id_kegiatan');
        $tahun = (int) ($this->request->getGet('tahun') ?? date('Y'));

        if (empty($idKegiatan) || !ctype_digit((string) $idKegiatan)) {
            return $this->response->setJSON([
                'success' => false
            ]);
        }

        $builder = $this->db->table('rencana_penanganan_risiko rtp')
            ->select('rtp.id_rtp, pm.id_pemantauan, pm.status_validasi')
            ->join('evaluasi_risiko er', 'er.id_evaluasi = rtp.id_penilaian_awal')
            ->join('identifikasi_risiko ir', 'ir.id_identifikasi = er.id_identifikasi')
            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('konteks k', 'k.id_konteks = kpb.id_konteks')
            ->join('pemantauan_risiko pm', 'pm.id_rtp = rtp.id_rtp', 'left')
            ->where('k.id_kegiatan', $idKegiatan)
            ->where('k.tahun', $tahun);

        // Batasi sesuai tim aktif untuk operator dan ketua
        if (in_array(session('user_role'), ['operator', 'ketua'], true)) {
            $userTim = array_map('intval', session('user_tim') ?? []);

            if (empty($userTim) && session('id_tim')) {
                $userTim = [(int) session('id_tim')];
            }

            $idTim = (int) (
                session('global_id_tim')
                ?? session('id_tim')
                ?? 0
            );

            if (!$idTim || !in_array($idTim, $userTim, true)) {
                return $this->response->setJSON([
                    'success' => false
                ]);
            }

            $builder->where('k.id_tim', $idTim);
        }

        $rows = $builder->get()->getResultArray();

        $lolos = !empty($rows);

        foreach ($rows as $row) {
            if (
                empty($row['id_pemantauan']) ||
                ($row['status_validasi'] ?? '') !== 'Disetujui'
            ) {
                $lolos = false;
                break;
            }
        }

        return $this->response->setJSON([
            'success' => $lolos
        ]);
    }

    public function print()
    {

        $tahun = (int) ($this->request->getGet('tahun') ?? date('Y'));
        $triwulan = $this->request->getGet('triwulan');

        $rentangTriwulan = [
            1 => ['01-01', '03-31'],
            2 => ['04-01', '06-30'],
            3 => ['07-01', '09-30'],
            4 => ['10-01', '12-31'],
        ];

        if (
            $triwulan !== null &&
            $triwulan !== '' &&
            isset($rentangTriwulan[(int) $triwulan])
        ) {
            $triwulan = (int) $triwulan;

            $startDate = $tahun . '-' . $rentangTriwulan[$triwulan][0];
            $endDate   = $tahun . '-' . $rentangTriwulan[$triwulan][1];

            $periodeLabel = 'Triwulan ' . $triwulan;
        } else {
            $triwulan = '';

            $startDate = $tahun . '-01-01';
            $endDate   = $tahun . '-12-31';

            $periodeLabel = 'Semua Periode';
        }

        $type = 'triwulan';
        $endExclusive = date('Y-m-d', strtotime($endDate . ' +1 day'));

        // Dipakai untuk kompatibilitas dengan view PDF yang masih
        // menggunakan variabel bulan.
        $bulan = $periodeLabel;

        $idKegiatan = $this->request->getGet('id_kegiatan');
        $statusValidasi = $this->request->getGet('status_validasi');
        $form = $this->request->getGet('form') ?? 'form4';
        $idTimAktif = session('global_id_tim') ?? session('id_tim');


        // =====================================================
        // VALIDASI EXPORT FORM 4: SELURUH RTP HARUS DISETUJUI
        // =====================================================
        if ($form === 'form4') {

            $pesanBlokir = null;

            // Form 4 wajib menggunakan satu kegiatan
            if (empty($idKegiatan) || !ctype_digit((string) $idKegiatan)) {
                $pesanBlokir = 'Pilih satu kegiatan terlebih dahulu untuk export Form Pemantauan Risiko.';
            } else {

                $validasiBuilder = $this->db->table('rencana_penanganan_risiko rtp')
                    ->select('
                rtp.id_rtp,
                pm.id_pemantauan,
                pm.status_validasi
            ')
                    ->join(
                        'evaluasi_risiko er',
                        'er.id_evaluasi = rtp.id_penilaian_awal'
                    )
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
                    ->join(
                        'pemantauan_risiko pm',
                        'pm.id_rtp = rtp.id_rtp',
                        'left'
                    )
                    ->where('k.id_kegiatan', $idKegiatan)
                    ->where('k.tahun', $tahun);

                // Batasi sesuai tim operator
                if (session('user_role') === 'operator') {

                    if (empty($idTimAktif)) {
                        $pesanBlokir = 'Tim aktif tidak ditemukan. Silakan pilih tim terlebih dahulu.';
                    } else {
                        $validasiBuilder->where('k.id_tim', $idTimAktif);
                    }
                }

                // Batasi sesuai tim ketua
                if (session('user_role') === 'ketua') {

                    $pengelolaId = session('pengelola_id');

                    $penugasanValidasi = $this->db
                        ->table('penugasan_pengelola')
                        ->where('pengelola_id', $pengelolaId)
                        ->where('is_ketua_tim', true)
                        ->get()
                        ->getRowArray();

                    if (!$penugasanValidasi) {
                        $pesanBlokir = 'Data penugasan Ketua Tim tidak ditemukan.';
                    } else {
                        $validasiBuilder->where(
                            'k.id_tim',
                            $penugasanValidasi['tim_kerja_id']
                        );
                    }
                }

                if ($pesanBlokir === null) {

                    $daftarValidasi = $validasiBuilder
                        ->get()
                        ->getResultArray();

                    if (empty($daftarValidasi)) {

                        $pesanBlokir = 'Tidak ditemukan data RTP pada kegiatan dan tahun yang dipilih.';
                    } else {

                        foreach ($daftarValidasi as $item) {

                            if (
                                empty($item['id_pemantauan']) ||
                                ($item['status_validasi'] ?? '') !== 'Disetujui'
                            ) {
                                $pesanBlokir = 'Export Form Pemantauan Risiko belum dapat dilakukan. Pastikan seluruh RTP pada kegiatan ini sudah diisi dan disetujui oleh Ketua Tim.';
                                break;
                            }
                        }
                    }
                }
            }

            if ($pesanBlokir !== null) {
                return redirect()
                    ->to(site_url('pelaporan-risiko'))
                    ->with('warning_export', $pesanBlokir);
            }
        }

        $bulanNama = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];

        $builder = $this->db->table('rencana_penanganan_risiko rtp')
            ->select("
        rtp.id_rtp,
        rtp.uraian_rtp,
        rtp.target_output,
        rtp.target_waktu,

        ir.id_identifikasi,
        ir.pernyataan_risiko,
        ir.penyebab_risiko,
        ir.dampak_risiko,
        ir.sumber_risiko,

        er.opsi_tindakan,
        er.prioritas,

      pr.nilai_risiko,
pr.uraian_pengendalian,
pr.efektivitas,

        kk.level AS kemungkinan,
        kd.level AS dampak,

        kk_residu.level AS kemungkinan_residu,
        kd_residu.level AS dampak_residu,
        mr_residu.nilai_risiko AS skor_residu,

        kr.nama_kategori,

        pb.kode_proses,
        pb.uraian_proses,

        pm.realisasi_output,
        pm.realisasi_waktu,
        COALESCE(pm.status, 'Belum Dilaksanakan') as status,

        sk.nama_tim,
        kegiatan.id_kegiatan,
        kegiatan.nama_kegiatan
    ")
            ->join('evaluasi_risiko er', 'er.id_evaluasi = rtp.id_penilaian_awal')
            ->join('identifikasi_risiko ir', 'ir.id_identifikasi = er.id_identifikasi')
            ->join('penilaian_risiko pr', 'pr.id_penilaian = er.id_penilaian')

            ->join('kriteria_kemungkinan kk', 'kk.id_kriteria = pr.id_kemungkinan', 'left')
            ->join('kriteria_dampak kd', 'kd.id_kriteria = pr.id_dampak', 'left')
            ->join(
                'kriteria_kemungkinan kk_residu',
                'kk_residu.id_kriteria = rtp.id_kemungkinan_residu',
                'left'
            )
            ->join(
                'kriteria_dampak kd_residu',
                'kd_residu.id_kriteria = rtp.id_dampak_residu',
                'left'
            )
            ->join(
                'matriks_risiko mr_residu',
                'mr_residu.level_kemungkinan = kk_residu.level AND mr_residu.level_dampak = kd_residu.level',
                'left',
                false
            )

            ->join(
                'kategori_risiko kr',
                'kr.id_kategori_risiko = ir.id_kategori_risiko',
                'left'
            )

            ->join('konteks_proses_bisnis kpb', 'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('proses_bisnis pb', 'pb.id_proses = kpb.id_proses', 'left')

            ->join('konteks k', 'k.id_konteks = kpb.id_konteks')
            ->join('tim_kerja sk', 'sk.id_tim = k.id_tim', 'left')
            ->join('kegiatan', 'kegiatan.id_kegiatan = k.id_kegiatan', 'left')

            ->join('pemantauan_risiko pm', 'pm.id_rtp = rtp.id_rtp', 'left');

        // FILTER TIM AKTIF OPERATOR
        if (session('user_role') === 'operator') {
            $idTim = session('global_id_tim') ?? session('id_tim');

            $builder->where('k.id_tim', $idTim);
        }

        // FILTER KETUA
        if (session('user_role') === 'ketua') {
            $pengelolaId = session('pengelola_id');
            $penugasan = $this->db->table('penugasan_pengelola')
                ->where('pengelola_id', $pengelolaId)
                ->get()
                ->getRowArray();
            if ($penugasan) {
                $builder->where('k.id_tim', $penugasan['tim_kerja_id']);
            }
        }

        // FILTER KEGIATAN
        if (!empty($idKegiatan)) {
            $builder->where('k.id_kegiatan', $idKegiatan);
        }

        $builder->orderBy('rtp.id_rtp', 'ASC');
        $data = $builder->get()->getResultArray();

        // =====================================================
        // DATA KHUSUS FORM 1 - PENETAPAN KONTEKS
        // =====================================================

        $form1 = null;
        $form1Proses = [];
        $form1Pemangku = [];
        $form1Peraturan = [];
        $form1Pestle = [];
        $form1Swot = [];

        if ($form === 'form1' || $form === 'all') {

            $form1Builder = $this->db->table('konteks k')
                ->select('
            k.id_konteks,
            k.tahun,
            k.id_tim,
            k.id_kegiatan,
            k.id_sasaran_strategis,
            sk.nama_tim,
            keg.nama_kegiatan,
            ss.uraian_sasaran AS sasaran_strategis
        ')
                ->join(
                    'tim_kerja sk',
                    'sk.id_tim = k.id_tim',
                    'left'
                )
                ->join(
                    'kegiatan keg',
                    'keg.id_kegiatan = k.id_kegiatan',
                    'left'
                )
                ->join(
                    'sasaran_strategis ss',
                    'ss.id_sasaran_strategis = k.id_sasaran_strategis',
                    'left'
                );

            // Filter tim sesuai user login
            if (session('user_role') === 'operator') {
                $form1Builder->where('k.id_tim', $idTimAktif);
            }

            if (session('user_role') === 'ketua') {

                $pengelolaId = session('pengelola_id');

                $penugasan = $this->db
                    ->table('penugasan_pengelola')
                    ->where('pengelola_id', $pengelolaId)
                    ->get()
                    ->getRowArray();

                if ($penugasan) {
                    $form1Builder->where(
                        'k.id_tim',
                        $penugasan['tim_kerja_id']
                    );
                }
            }

            // Filter tahun sesuai periode export
            $form1Builder->where('k.tahun', $tahun);

            // Filter kegiatan yang dipilih
            if (!empty($idKegiatan)) {
                $form1Builder->where('k.id_kegiatan', $idKegiatan);
            }

            $form1 = $form1Builder
                ->orderBy('k.id_konteks', 'DESC')
                ->get()
                ->getRowArray();

            if ($form1) {

                $idKonteksForm1 = $form1['id_konteks'];

                // PROSES BISNIS + SASARAN KINERJA
                $form1Proses = $this->db
                    ->table('konteks_proses_bisnis kpb')
                    ->select('
                pb.kode_proses,
                pb.uraian_proses,
                pb.jenis_proses,
                kpb.deskripsi_proses,
                sk.uraian_sasaran AS sasaran_kinerja
            ')
                    ->join(
                        'proses_bisnis pb',
                        'pb.id_proses = kpb.id_proses',
                        'left'
                    )
                    ->join(
                        'sasaran_kinerja sk',
                        'sk.id_konteks_proses = kpb.id_konteks_proses',
                        'left'
                    )
                    ->where('kpb.id_konteks', $idKonteksForm1)
                    ->orderBy('pb.kode_proses', 'ASC')
                    ->get()
                    ->getResultArray();

                // PEMANGKU KEPENTINGAN
                $form1Pemangku = $this->db
                    ->table('konteks_pemangku kp')
                    ->select('
                pk.nama_instansi,
                pk.hubungan
            ')
                    ->join(
                        'pemangku_kepentingan pk',
                        'pk.id_pemangku = kp.id_pemangku',
                        'left'
                    )
                    ->where('kp.id_konteks', $idKonteksForm1)
                    ->get()
                    ->getResultArray();

                // PERATURAN TERKAIT
                $form1Peraturan = $this->db
                    ->table('konteks_peraturan kp')
                    ->select('
                pt.nama_peraturan
            ')
                    ->join(
                        'peraturan_terkait pt',
                        'pt.id_peraturan = kp.id_peraturan',
                        'left'
                    )
                    ->where('kp.id_konteks', $idKonteksForm1)
                    ->get()
                    ->getResultArray();

                // =============================
                // ANALISIS SWOT & PESTLE
                // =============================

                $analisisModel = new \App\Models\AnalisisSwotPestleModel();

                $analisisData = $analisisModel
                    ->where('id_tim', $form1['id_tim'])
                    ->where('tahun', $tahun)
                    ->orderBy('triwulan', 'ASC')
                    ->orderBy('id_analisis', 'ASC')
                    ->findAll();

                // Inisialisasi kategori
                $form1Pestle = [
                    'political'     => [],
                    'economic'      => [],
                    'social'        => [],
                    'technological' => [],
                    'legal'         => [],
                    'environmental' => [],
                ];

                $form1Swot = [
                    'strength'    => [],
                    'weakness'    => [],
                    'opportunity' => [],
                    'threat'      => [],
                ];

                // Mapping kategori database ke kategori view
                foreach ($analisisData as $row) {

                    $jenis = strtoupper($row['jenis_analisis'] ?? '');
                    $kategori = strtoupper($row['kategori'] ?? '');
                    $uraian = trim($row['uraian'] ?? '');

                    if ($uraian === '') {
                        continue;
                    }

                    if ($jenis === 'PESTLE') {

                        $mapPestle = [
                            'P'   => 'political',
                            'E'   => 'economic',
                            'S'   => 'social',
                            'T'   => 'technological',
                            'L'   => 'legal',
                            'ENV' => 'environmental',
                        ];

                        if (isset($mapPestle[$kategori])) {
                            $form1Pestle[$mapPestle[$kategori]][] = $uraian;
                        }
                    } elseif ($jenis === 'SWOT') {

                        $mapSwot = [
                            'S' => 'strength',
                            'W' => 'weakness',
                            'O' => 'opportunity',
                            'T' => 'threat',
                        ];

                        if (isset($mapSwot[$kategori])) {
                            $form1Swot[$mapSwot[$kategori]][] = $uraian;
                        }
                    }
                }
            }
        }

        // =====================================================
        // DATA KHUSUS FORM 2 - IDENTIFIKASI RISIKO
        // =====================================================

        $form2Data = [];

        if ($form === 'form2' || $form === 'all') {

            $form2Builder = $this->db->table('identifikasi_risiko ir')
                ->select("
            ir.id_identifikasi,
            ir.pernyataan_risiko,
            ir.penyebab_risiko,
            ir.dampak_risiko,
            ir.sumber_risiko,

            pb.kode_proses,
            pb.uraian_proses,

            kr.nama_kategori,

            STRING_AGG(
                DISTINCT ad.nama_area_dampak,
                ', '
            ) AS area_dampak_list,

            pr.nilai_risiko,
pr.uraian_pengendalian,
pr.efektivitas,
sl.level AS nilai_selera,
sl.nama_level AS nama_selera,

            kk.level AS kemungkinan,
            kd.level AS dampak,

            er.opsi_tindakan,
            er.prioritas,

            k.id_konteks,
            k.tahun,
            k.id_tim,
            k.id_kegiatan,

            tk.nama_tim,
            keg.nama_kegiatan
        ")
                ->join(
                    'konteks_proses_bisnis kpb',
                    'kpb.id_konteks_proses = ir.id_konteks_proses'
                )
                ->join(
                    'proses_bisnis pb',
                    'pb.id_proses = kpb.id_proses',
                    'left'
                )
                ->join(
                    'konteks k',
                    'k.id_konteks = kpb.id_konteks'
                )
                ->join(
                    'tim_kerja tk',
                    'tk.id_tim = k.id_tim',
                    'left'
                )
                ->join(
                    'kegiatan keg',
                    'keg.id_kegiatan = k.id_kegiatan',
                    'left'
                )
                ->join(
                    'kategori_risiko kr',
                    'kr.id_kategori_risiko = ir.id_kategori_risiko',
                    'left'
                )
                ->join(
                    'identifikasi_area_dampak iad',
                    'iad.id_identifikasi = ir.id_identifikasi',
                    'left'
                )
                ->join(
                    'area_dampak ad',
                    'ad.id_area_dampak = iad.id_area_dampak',
                    'left'
                )
                ->join(
                    'penilaian_risiko pr',
                    'pr.id_identifikasi = ir.id_identifikasi',
                    'left'
                )
                ->join(
                    'kriteria_kemungkinan kk',
                    'kk.id_kriteria = pr.id_kemungkinan',
                    'left'
                )
                ->join(
                    'kriteria_dampak kd',
                    'kd.id_kriteria = pr.id_dampak',
                    'left'
                )
                ->join(
                    'evaluasi_risiko er',
                    'er.id_penilaian = pr.id_penilaian',
                    'left'
                )
                ->join(
                    'selera_risiko sl',
                    'sl.id_selera = pr.id_selera',
                    'left'
                );

            // FILTER TIM LOGIN
            if (session('user_role') === 'operator') {
                $form2Builder->where(
                    'k.id_tim',
                    $idTimAktif
                );
            }

            // FILTER KETUA
            if (session('user_role') === 'ketua') {

                $pengelolaId = session('pengelola_id');

                $penugasanForm2 = $this->db
                    ->table('penugasan_pengelola')
                    ->where('pengelola_id', $pengelolaId)
                    ->get()
                    ->getRowArray();

                if ($penugasanForm2) {
                    $form2Builder->where(
                        'k.id_tim',
                        $penugasanForm2['tim_kerja_id']
                    );
                }
            }

            // FILTER TAHUN
            $form2Builder->where('k.tahun', $tahun);

            // FILTER KEGIATAN
            if (!empty($idKegiatan)) {
                $form2Builder->where(
                    'k.id_kegiatan',
                    $idKegiatan
                );
            }

            $form2Builder->groupBy('
        ir.id_identifikasi,
        ir.pernyataan_risiko,
        ir.penyebab_risiko,
        ir.dampak_risiko,
        ir.sumber_risiko,
        sl.level,
sl.nama_level,
pb.kode_proses,
        pb.uraian_proses,
        kr.nama_kategori,
        pr.id_penilaian,
        pr.nilai_risiko,
pr.uraian_pengendalian,
pr.efektivitas,
        kk.level,
        kd.level,   
        er.id_evaluasi,
        er.opsi_tindakan,
        er.prioritas,
        k.id_konteks,
        k.tahun,
        k.id_tim,
        k.id_kegiatan,
        tk.nama_tim,
        keg.nama_kegiatan
    ');

            $form2Data = $form2Builder
                ->orderBy('pb.kode_proses', 'ASC')
                ->orderBy('ir.id_identifikasi', 'ASC')
                ->get()
                ->getResultArray();
        }

        // =====================================================
        // DATA KHUSUS FORM 3 - RENCANA PENANGANAN
        // =====================================================

        $form3Data = [];

        if ($form === 'form3' || $form === 'all') {

            $form3Builder = $this->db->table('evaluasi_risiko er')
                ->select('
            er.id_evaluasi,
            er.opsi_tindakan,

            ir.id_identifikasi,
            ir.pernyataan_risiko,

            pb.kode_proses,
            pb.uraian_proses,

            pr.nilai_risiko,

            rtp.id_rtp,
            rtp.uraian_rtp,
            rtp.target_output,
            rtp.target_waktu,

            kk_residu.level AS kemungkinan_residu,
            kd_residu.level AS dampak_residu,
            mr_residu.nilai_risiko AS skor_residu,
            sr_residu.nama_level AS level_residu,

            k.id_konteks,
            k.tahun,
            k.id_tim,
            k.id_kegiatan,

            tk.nama_tim,
            keg.nama_kegiatan
        ')
                ->join(
                    'identifikasi_risiko ir',
                    'ir.id_identifikasi = er.id_identifikasi'
                )
                ->join(
                    'konteks_proses_bisnis kpb',
                    'kpb.id_konteks_proses = ir.id_konteks_proses'
                )
                ->join(
                    'proses_bisnis pb',
                    'pb.id_proses = kpb.id_proses',
                    'left'
                )
                ->join(
                    'konteks k',
                    'k.id_konteks = kpb.id_konteks'
                )
                ->join(
                    'tim_kerja tk',
                    'tk.id_tim = k.id_tim',
                    'left'
                )
                ->join(
                    'kegiatan keg',
                    'keg.id_kegiatan = k.id_kegiatan',
                    'left'
                )
                ->join(
                    'penilaian_risiko pr',
                    'pr.id_penilaian = er.id_penilaian',
                    'left'
                )
                ->join(
                    'rencana_penanganan_risiko rtp',
                    'rtp.id_penilaian_awal = er.id_evaluasi'
                )
                ->join(
                    'kriteria_kemungkinan kk_residu',
                    'kk_residu.id_kriteria = rtp.id_kemungkinan_residu',
                    'left'
                )
                ->join(
                    'kriteria_dampak kd_residu',
                    'kd_residu.id_kriteria = rtp.id_dampak_residu',
                    'left'
                )
                ->join(
                    'matriks_risiko mr_residu',
                    'mr_residu.level_kemungkinan = kk_residu.level
             AND mr_residu.level_dampak = kd_residu.level',
                    'left',
                    false
                )
                ->join(
                    'selera_risiko sr_residu',
                    'mr_residu.nilai_risiko BETWEEN sr_residu.nilai_min
             AND sr_residu.nilai_max',
                    'left',
                    false
                )
                ->where('er.opsi_tindakan', 'Mengurangi');

            // FILTER OPERATOR
            if (session('user_role') === 'operator') {
                $form3Builder->where(
                    'k.id_tim',
                    $idTimAktif
                );
            }

            // FILTER KETUA
            if (session('user_role') === 'ketua') {

                $pengelolaId = session('pengelola_id');

                $penugasanForm3 = $this->db
                    ->table('penugasan_pengelola')
                    ->where('pengelola_id', $pengelolaId)
                    ->get()
                    ->getRowArray();

                if ($penugasanForm3) {
                    $form3Builder->where(
                        'k.id_tim',
                        $penugasanForm3['tim_kerja_id']
                    );
                }
            }


            // FILTER KEGIATAN
            if (!empty($idKegiatan)) {
                $form3Builder->where(
                    'k.id_kegiatan',
                    $idKegiatan
                );
            }

            // FILTER TAHUN KONTEKS
            $form3Builder->where('k.tahun', $tahun);

            // FILTER TRIWULAN BERDASARKAN TARGET WAKTU RTP
            $form3Builder
                ->where('rtp.target_waktu >=', $startDate)
                ->where('rtp.target_waktu <', $endExclusive);

            $form3Data = $form3Builder
                ->orderBy('pb.kode_proses', 'ASC')
                ->orderBy('rtp.id_rtp', 'ASC')
                ->get()
                ->getResultArray();

            // =================================================
            // NOMOR PRIORITAS RISIKO
            // 1 risiko/evaluasi = 1 nomor prioritas
            // walaupun punya beberapa RTP
            // =================================================

            $prioritasMap = [];
            $nomorPrioritas = 1;

            foreach ($form3Data as &$row) {

                $idEvaluasi = $row['id_evaluasi'];

                if (!isset($prioritasMap[$idEvaluasi])) {
                    $prioritasMap[$idEvaluasi] = $nomorPrioritas++;
                }

                $row['prioritas_risiko'] =
                    $prioritasMap[$idEvaluasi];
            }

            unset($row);
        }

        //batas form 3

        // =====================================================
        // DATA KHUSUS FORM 4 - PELAPORAN RISIKO
        // =====================================================

        $form4Data = [];

        if ($form === 'form4' || $form === 'all') {

            $form4Builder = $this->db->table('identifikasi_risiko ir')
                ->select('
            ir.id_identifikasi,
            ir.pernyataan_risiko,

           pkr.id AS id_pemantauan_keterjadian,
pkr.keterjadian_risiko,
pkr.jumlah_keterjadian,

            pm.id_pemantauan,
            pm.realisasi_output,
            pm.realisasi_waktu,
            pm.status,
            pm.status_validasi,
            pm.catatan,
            pm.efektivitas_pengendalian,
            pm.id_kemungkinan_residu,
            pm.id_dampak_residu,

            rtp.id_rtp,
            rtp.uraian_rtp,
            rtp.target_output,
            rtp.target_waktu,

            er.id_evaluasi,
            er.id_penilaian,
            er.opsi_tindakan AS respon_risiko,

            pr.nilai_risiko,

            km_res.level AS kemungkinan_residu,
            kd_res.level AS dampak_residu,
            mr_res.nilai_risiko AS skor_risiko_residu,

            bp.url_link AS link_bukti,

            k.id_konteks,
            k.tahun,
            k.id_tim,
            k.id_kegiatan,

            tk.nama_tim,
            keg.nama_kegiatan
        ')

                // KONTEKS RISIKO
                ->join(
                    'konteks_proses_bisnis kpb',
                    'kpb.id_konteks_proses = ir.id_konteks_proses'
                )
                ->join(
                    'konteks k',
                    'k.id_konteks = kpb.id_konteks'
                )

                // PEMANTAUAN KETERJADIAN RISIKO PER TRIWULAN
                ->join(
                    'pemantauan_keterjadian_risiko pkr',
                    'pkr.id_identifikasi = ir.id_identifikasi
     AND pkr.tahun = ' . (int) $tahun . '
     AND pkr.triwulan = ' . (int) $triwulan,
                    'left',
                    false
                )

                // EVALUASI RISIKO
                ->join(
                    'evaluasi_risiko er',
                    'er.id_identifikasi = ir.id_identifikasi',
                    'left'
                )

                // RENCANA PENANGANAN RISIKO
                ->join(
                    'rencana_penanganan_risiko rtp',
                    'rtp.id_penilaian_awal = er.id_evaluasi',
                    'left'
                )

                // PEMANTAUAN RTP
                ->join(
                    'pemantauan_risiko pm',
                    'pm.id_rtp = rtp.id_rtp',
                    'left'
                )

                // PENILAIAN RISIKO
                ->join(
                    'penilaian_risiko pr',
                    'pr.id_penilaian = er.id_penilaian',
                    'left'
                )

                // RISIKO RESIDUAL
                ->join(
                    'kriteria_kemungkinan km_res',
                    'km_res.id_kriteria = pm.id_kemungkinan_residu',
                    'left'
                )
                ->join(
                    'kriteria_dampak kd_res',
                    'kd_res.id_kriteria = pm.id_dampak_residu',
                    'left'
                )
                ->join(
                    'matriks_risiko mr_res',
                    'mr_res.level_kemungkinan = km_res.level
             AND mr_res.level_dampak = kd_res.level',
                    'left',
                    false
                )

                // BUKTI DUKUNG
                ->join(
                    'bukti_pemantauan bp',
                    'bp.id_pemantauan = pm.id_pemantauan',
                    'left'
                )

                // TIM DAN KEGIATAN
                ->join(
                    'tim_kerja tk',
                    'tk.id_tim = k.id_tim',
                    'left'
                )
                ->join(
                    'kegiatan keg',
                    'keg.id_kegiatan = k.id_kegiatan',
                    'left'
                )

                // FILTER TAHUN DAN KEGIATAN
                ->where('k.tahun', $tahun)
                ->where('k.id_kegiatan', $idKegiatan);

            // FILTER OPERATOR
            if (session('user_role') === 'operator') {
                $form4Builder->where('k.id_tim', $idTimAktif);
            }

            // FILTER KETUA
            if (session('user_role') === 'ketua') {

                $pengelolaId = session('pengelola_id');

                $penugasanForm4 = $this->db
                    ->table('penugasan_pengelola')
                    ->where('pengelola_id', $pengelolaId)
                    ->where('is_ketua_tim', true)
                    ->get()
                    ->getRowArray();

                if ($penugasanForm4) {
                    $form4Builder->where(
                        'k.id_tim',
                        $penugasanForm4['tim_kerja_id']
                    );
                }
            }

            // AMBIL SEMUA RISIKO, TERMASUK YANG BELUM MEMILIKI RTP
            $form4Data = $form4Builder
                ->orderBy('ir.id_identifikasi', 'ASC')
                ->orderBy('rtp.id_rtp', 'ASC')
                ->get()
                ->getResultArray();


            // =================================================
            // NOMOR PRIORITAS RISIKO
            // BERDASARKAN IDENTIFIKASI RISIKO
            // =================================================

            $prioritasMapForm4 = [];
            $nomorPrioritasForm4 = 1;

            foreach ($form4Data as &$row) {

                $idIdentifikasi = $row['id_identifikasi'];

                if (!isset($prioritasMapForm4[$idIdentifikasi])) {
                    $prioritasMapForm4[$idIdentifikasi] = $nomorPrioritasForm4++;
                }

                $row['prioritas_risiko'] =
                    $prioritasMapForm4[$idIdentifikasi];
            }

            unset($row);
        }

        // =====================================================
        // META PDF: TIM KERJA & KEGIATAN

        $timkerja = '-';
        $kegiatan = '-';

        if ($form === 'form1' && !empty($form1)) {

            $timkerja = $form1['nama_tim'] ?? '-';
            $kegiatan = $form1['nama_kegiatan'] ?? '-';
        } elseif ($form === 'form2' && !empty($form2Data)) {

            $timkerja = $form2Data[0]['nama_tim'] ?? '-';
            $kegiatan = $form2Data[0]['nama_kegiatan'] ?? '-';
        } elseif ($form === 'form3' && !empty($form3Data)) {

            $timkerja = $form3Data[0]['nama_tim'] ?? '-';
            $kegiatan = $form3Data[0]['nama_kegiatan'] ?? '-';
        } elseif ($form === 'form4' && !empty($form4Data)) {

            $timkerja = $form4Data[0]['nama_tim'] ?? '-';
            $kegiatan = $form4Data[0]['nama_kegiatan'] ?? '-';
        } elseif (!empty($data)) {

            // fallback
            $timkerja = $data[0]['nama_tim'] ?? '-';
            $kegiatan = $data[0]['nama_kegiatan'] ?? '-';
        }

        // KETUA TIM
        $ketua = $this->db->table('pengelola_risiko g')
            ->select('g.nama,g.nip,sk.nama_tim')
            ->join('penugasan_pengelola p', 'p.pengelola_id = g.id')
            ->join('tim_kerja sk', 'sk.id_tim = p.tim_kerja_id')
            ->where('p.is_ketua_tim', true)
            ->where('sk.nama_tim', $timkerja)
            ->get()
            ->getRowArray();

        // PEMILIK RISIKO
        $pemilik = $this->db->table('pengelola_risiko')
            ->where('is_pemilik', true)
            ->get()
            ->getRowArray();

        $viewData = [
            'data' => $data,
            //FORM1
            // Data Penetapan Konteks
            'form1' => $form1,
            'form1Proses' => $form1Proses,
            'form1Pemangku' => $form1Pemangku,
            'form1Peraturan' => $form1Peraturan,
            'form1Pestle' => $form1Pestle,
            'form1Swot'   => $form1Swot,

            //FORM2
            'form2Data' => $form2Data,

            // FORM 3
            'form3Data' => $form3Data,

            // FORM 4
            'form4Data' => $form4Data,

            'bulan' => $bulanNama[$bulan] ?? $bulan,
            'tahun' => $tahun,
            'timkerja' => $timkerja,
            'kegiatan' => $kegiatan,
            'nama_ketua' => $ketua['nama'] ?? '-',
            'nip_ketua' => $ketua['nip'] ?? '-',
            'nama_pemilik' => $pemilik['nama'] ?? '-',
            'nip_pemilik' => $pemilik['nip'] ?? '-',
            'jabatan_pemilik' => $pemilik['jabatan'] ?? '-',
            'form' => $form,
        ];

        // RENDER HTML VIEW
        $html = view('pelaporan_risiko/pdf/print', $viewData);

        // DOMPDF OPTIONS
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        // INIT DOMPDF
        $dompdf = new Dompdf($options);

        // =====================================================
        // SIMPAN POSISI ROW TERAKHIR FORM 2 / FORM 3 / FORM 4
        // DI SETIAP HALAMAN
        // =====================================================

        $lastTableRowPerPage = [];

        if (
            $form === 'form2'
            || $form === 'form3'
            || $form === 'form4'
            || $form === 'all'
        ) {

            $dompdf->setCallbacks([
                [
                    'event' => 'end_frame',

                    'f' => function (
                        $frame,
                        $canvas,
                        $fontMetrics
                    ) use (&$lastTableRowPerPage) {

                        $node = $frame->get_node();

                        if (
                            !$node
                            || !$node->hasAttributes()
                            || $node->nodeName !== 'tr'
                        ) {
                            return;
                        }

                        $classAttr = $node->attributes
                            ->getNamedItem('class');

                        if (!$classAttr) {
                            return;
                        }

                        $classes = preg_split(
                            '/\s+/',
                            trim($classAttr->nodeValue)
                        );

                        // ==============================
                        // DETEKSI ROW FORM 2 / 3 / 4
                        // ==============================

                        $isForm2Row = in_array(
                            'form2-data-row',
                            $classes,
                            true
                        );

                        $isForm3Row = in_array(
                            'form3-data-row',
                            $classes,
                            true
                        );

                        $isForm4Row = in_array(
                            'form4-data-row',
                            $classes,
                            true
                        );

                        if (
                            !$isForm2Row
                            && !$isForm3Row
                            && !$isForm4Row
                        ) {
                            return;
                        }

                        // Halaman tempat row dirender
                        $pageNumber =
                            $canvas->get_page_number();

                        // Posisi fisik row
                        $box =
                            $frame->get_border_box();

                        $bottom =
                            $box['y'] + $box['h'];

                        // Simpan row yang paling bawah
                        // pada halaman tersebut
                        if (
                            !isset(
                                $lastTableRowPerPage[$pageNumber]
                            )
                            || $bottom >
                            $lastTableRowPerPage[$pageNumber]['bottom']
                        ) {

                            $lastTableRowPerPage[$pageNumber] = [
                                'x'      => $box['x'],
                                'width'  => $box['w'],
                                'bottom' => $bottom,
                            ];
                        }
                    }
                ]
            ]);
        }

        // LOAD HTML
        $dompdf->loadHtml($html);

        // PAPER
        $orientation = ($form === 'form1') ? 'portrait' : 'landscape';
        $dompdf->setPaper('A4', $orientation);

        // =====================================================
        // RENDER PDF
        // =====================================================
        $dompdf->render();

        // =====================================================
        // GARIS PENUTUP ROW TERAKHIR FORM 2 / FORM 3 / FORM 4
        // =====================================================

        if (
            in_array(
                $form,
                ['form2', 'form3', 'form4', 'all'],
                true
            )
            && !empty($lastTableRowPerPage)
        ) {

            $canvas = $dompdf->getCanvas();

            $canvas->page_script(
                function (
                    $pageNumber,
                    $pageCount,
                    $canvas,
                    $fontMetrics
                ) use (&$lastTableRowPerPage) {

                    if (
                        !isset(
                            $lastTableRowPerPage[$pageNumber]
                        )
                    ) {
                        return;
                    }

                    $row =
                        $lastTableRowPerPage[$pageNumber];

                    $x1 =
                        $row['x'];

                    $x2 =
                        $row['x']
                        + $row['width'];

                    $y =
                        $row['bottom'];

                    $canvas->line(
                        $x1,
                        $y,
                        $x2,
                        $y,
                        [0, 0, 0],
                        0.75
                    );
                }
            );
        }

        // FILENAME
        $formLabel = match ($form) {
            'form1' => 'Form-1-Penetapan-Konteks',
            'form2' => 'Form-2-Identifikasi-Risiko',
            'form3' => 'Form-3-Rencana-Penanganan',
            'form4' => 'Form-4-Pelaporan-Risiko',
            default => 'Semua-Form'
        };

        $namaKegiatan = preg_replace(
            '/[^A-Za-z0-9\-]/',
            '_',
            $kegiatan ?? 'Kegiatan'
        );

        $filename = 'Laporan-Risiko-' .
            $formLabel .
            '-' .
            $namaKegiatan .
            '.pdf';

        // STREAM PDF
        // STREAM PDF
        $dompdf->stream($filename, [
            'Attachment' => false
        ]);

        exit;
    }
}
