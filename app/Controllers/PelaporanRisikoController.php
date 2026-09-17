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
        $periode = $this->request->getPost('periode');

        if ($id) {
            session()->set('id_konteks_pl', $id);
        }

        if ($periode) {
            [$tahun, $bulan] = explode('-', $periode);
            session()->set('pl_periode', [
                'bulan' => $bulan,
                'tahun' => $tahun
            ]);
        }

        return redirect()->to('/pelaporan-risiko');
    }

    public function index()
    {
        $userRole  = session('user_role');
        $idKonteks = session('id_konteks_pl');
        $periodeInput = $this->request->getGet('periode');

        if ($periodeInput) {

            [$tahun, $bulan] = explode('-', $periodeInput);

            $periode = [
                'bulan' => $bulan,
                'tahun' => $tahun
            ];

            // Simpan periode aktif supaya approve/reject
            // menggunakan tahun yang sama dengan halaman
            session()->set('pl_periode', $periode);
        } else {

            $periode = $this->getPeriode();

            $bulan = $periode['bulan'];
            $tahun = $periode['tahun'];
        }
        $type       = $this->request->getGet('tipe_periode') ?? 'bulanan';
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

        if ($userRole === 'operator') {

            $idTim = session('id_tim');

            if ($idTim) {

                // filter data operator
                $builder->where('k.id_tim', $idTim);

                // ambil info tim + ketua
                $ketuaInfo = $this->db->table('tim_kerja sk')
                    ->select('sk.nama_tim, g.nama')
                    ->join(
                        'penugasan_pengelola p',
                        'p.tim_kerja_id = sk.id_tim',
                        'left'
                    )
                    ->join('pengelola_risiko g', 'g.id = p.pengelola_id', 'left')
                    ->where('sk.id_tim', $idTim)
                    ->where('p.is_ketua_tim', true)
                    ->get()
                    ->getRowArray();
            }
        } elseif ($userRole === 'ketua') {

            $pengelola_id = session('pengelola_id');

            $penugasan = $this->db->table('penugasan_pengelola')
                ->where('pengelola_id', $pengelola_id)
                ->get()
                ->getRow();

            if ($penugasan) {

                $idTim = $penugasan->tim_kerja_id;

                // filter data ketua
                $builder->where('k.id_tim', $idTim);

                // info dirinya sendiri
                $ketuaInfo = $this->db->table('tim_kerja sk')
                    ->select('sk.nama_tim, g.nama')
                    ->join(
                        'penugasan_pengelola p',
                        'p.tim_kerja_id = sk.id_tim',
                        'left'
                    )
                    ->join('pengelola_risiko g', 'g.id = p.pengelola_id', 'left')
                    ->where('sk.id_tim', $idTim)
                    ->where('p.is_ketua_tim', true)
                    ->get()
                    ->getRowArray();
            }
        } else {
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

        // Filter konteks berdasarkan tahun periode
        if (!empty($tahun)) {
            $builder->where('k.tahun', $tahun);
        }

        if ($type === 'range') {
            $start = $this->request->getGet('start_periode');
            $end   = $this->request->getGet('end_periode');

            if ($start && $end) {
                $startDate = date('Y-m-01', strtotime($start));
                $endDate   = date('Y-m-t', strtotime($end));
            } else {
                [$startDate, $endDate] = $this->getDateRange($bulan, $tahun, 'bulanan');
            }
        } else {
            [$startDate, $endDate] = $this->getDateRange($bulan, $tahun, $type);
        }

        $builder->groupStart()

            ->groupStart()
            ->where('pm.status IS NULL', null, false)
            ->orWhereIn('pm.status', [
                'Dalam Proses',
                'Belum Dilaksanakan',
                'Terlambat'
            ])
            ->groupEnd()

            ->orGroupStart()
            ->where('pm.realisasi_waktu >=', $startDate)
            ->where('pm.realisasi_waktu <=', $endDate)
            ->groupEnd()

            ->orGroupStart()
            ->where('pm.updated_at >=', $startDate)
            ->where('pm.updated_at <=', $endDate)
            ->groupEnd()

            ->groupEnd();

        $builder->orderBy('kegiatan.nama_kegiatan', 'ASC');
        $builder->orderBy('rtp.id_rtp', 'ASC');

        $allData = $builder->get()->getResultArray();

        // =====================================================
        // STATUS VALIDASI PER KEGIATAN
        // =====================================================

        $statusKegiatan = [];

        foreach ($allData as $row) {

            $idKegiatanRow = $row['id_kegiatan'] ?? null;

            if (!$idKegiatanRow) {
                continue;
            }

            if (!isset($statusKegiatan[$idKegiatanRow])) {
                $statusKegiatan[$idKegiatanRow] = [
                    'statuses' => [],
                    'catatan'  => null,
                ];
            }

            $status = $row['status_validasi'] ?? 'Draft';

            $statusKegiatan[$idKegiatanRow]['statuses'][] = $status;

            if (
                $status === 'Ditolak'
                && !empty($row['catatan_validasi'])
            ) {
                $statusKegiatan[$idKegiatanRow]['catatan']
                    = $row['catatan_validasi'];
            }
        }

        // Tentukan status final tiap kegiatan
        foreach ($statusKegiatan as $id => &$item) {

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

        $listKegiatan = $this->db->table('kegiatan')
            ->select('id_kegiatan, nama_kegiatan, id_tim')
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
            'total' => $total,
            'from' => $from,
            'to' => $to,
            'ketuaInfo' => $ketuaInfo,
            'statusKegiatan' => $statusKegiatan,
            'tipe_periode' => $type,
            'activeKonteks' => [
                'id_tim' => $this->request->getGet('id_tim'),
                'pengelola_risiko_id' => $this->request->getGet('pengelola_risiko_id'),
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
        $idTim      = session('id_tim');

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
        $idTim      = session('id_tim');

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
            ->where('k.id_kegiatan', $idKegiatan);

        if (!empty($tahun)) {
            $rtpList->where('k.tahun', $tahun);
        }

        $rtpList = $rtpList
            ->get()
            ->getResultArray();

        if (empty($rtpList)) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => 'Data tidak ditemukan']);
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
            ->where('k.id_kegiatan', $idKegiatan);

        if (!empty($tahun)) {
            $rtpList->where('k.tahun', $tahun);
        }

        $rtpList = $rtpList
            ->get()
            ->getResultArray();

        if (empty($rtpList)) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => 'Data tidak ditemukan']);
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

    public function print()
    {
        $periodeInput = $this->request->getGet('periode');

        if ($periodeInput) {
            [$tahun, $bulan] = explode('-', $periodeInput);
        } else {
            $periode = session('pl_periode') ?? [
                'bulan' => date('m'),
                'tahun' => date('Y')
            ];

            $bulan = $periode['bulan'];
            $tahun = $periode['tahun'];
        }

        $idKegiatan = $this->request->getGet('id_kegiatan');
        $form = $this->request->getGet('form') ?? 'form4';

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
        pr.efektivitas,
        pr.uraian_pengendalian,

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

            ->join('pemantauan_risiko pm', 'pm.id_rtp = rtp.id_rtp', 'left')
            ->where('pm.status_validasi', 'Disetujui');

        // FILTER TIM LOGIN
        if (session('user_role') === 'operator') {
            $builder->where('k.id_tim', session('id_tim'));
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
                $form1Builder->where('k.id_tim', session('id_tim'));
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
                );

            // FILTER TIM LOGIN
            if (session('user_role') === 'operator') {
                $form2Builder->where(
                    'k.id_tim',
                    session('id_tim')
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
                    session('id_tim')
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

            // FILTER TAHUN
            $form3Builder->where('k.tahun', $tahun);

            // FILTER KEGIATAN
            if (!empty($idKegiatan)) {
                $form3Builder->where(
                    'k.id_kegiatan',
                    $idKegiatan
                );
            }

            $form3Data = $form3Builder
                ->orderBy('pr.nilai_risiko', 'DESC')
                ->orderBy('er.id_evaluasi', 'ASC')
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

        // =====================================================
        // DATA KHUSUS FORM 4 - PELAPORAN RISIKO
        // =====================================================

        $form4Data = [];

        if ($form === 'form4' || $form === 'all') {


            $form4Builder = $this->db->table('pemantauan_risiko pm')
                ->select('
            pm.id_pemantauan,
            pm.realisasi_output,
            pm.realisasi_waktu,
            pm.status_validasi,

            rtp.id_rtp,
            rtp.uraian_rtp,
            rtp.target_output,
            rtp.target_waktu,

            er.id_evaluasi,

            ir.id_identifikasi,
            ir.pernyataan_risiko,

            pr.nilai_risiko,

            k.id_konteks,
            k.tahun,
            k.id_tim,
            k.id_kegiatan,

            tk.nama_tim,
            keg.nama_kegiatan
        ')
                ->join(
                    'rencana_penanganan_risiko rtp',
                    'rtp.id_rtp = pm.id_rtp'
                )
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
                    'penilaian_risiko pr',
                    'pr.id_penilaian = er.id_penilaian',
                    'left'
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
                ->where('pm.status_validasi', 'Disetujui');

            // FILTER OPERATOR
            if (session('user_role') === 'operator') {
                $form4Builder->where(
                    'k.id_tim',
                    session('id_tim')
                );
            }

            // FILTER KETUA
            if (session('user_role') === 'ketua') {

                $pengelolaId = session('pengelola_id');

                $penugasanForm4 = $this->db
                    ->table('penugasan_pengelola')
                    ->where('pengelola_id', $pengelolaId)
                    ->get()
                    ->getRowArray();

                if ($penugasanForm4) {
                    $form4Builder->where(
                        'k.id_tim',
                        $penugasanForm4['tim_kerja_id']
                    );
                }
            }

            // FILTER TAHUN
            $form4Builder->where('k.tahun', $tahun);

            // FILTER KEGIATAN
            if (!empty($idKegiatan)) {
                $form4Builder->where(
                    'k.id_kegiatan',
                    $idKegiatan
                );
            }

            $form4Data = $form4Builder
                ->orderBy('pr.nilai_risiko', 'DESC')
                ->orderBy('er.id_evaluasi', 'ASC')
                ->orderBy('rtp.id_rtp', 'ASC')
                ->get()
                ->getResultArray();

            // =================================================
            // NOMOR PRIORITAS RISIKO
            // sama dengan Form 3
            // =================================================

            $prioritasMapForm4 = [];
            $nomorPrioritasForm4 = 1;

            foreach ($form4Data as &$row) {

                $idEvaluasi = $row['id_evaluasi'];

                if (!isset($prioritasMapForm4[$idEvaluasi])) {
                    $prioritasMapForm4[$idEvaluasi]
                        = $nomorPrioritasForm4++;
                }

                $row['prioritas_risiko']
                    = $prioritasMapForm4[$idEvaluasi];
            }

            unset($row);
        }
        // =====================================================
        // META PDF: TIM KERJA & KEGIATAN
        // =====================================================

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
        $dompdf->setPaper('A4', 'landscape');

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
        $dompdf->stream($filename, [
            'Attachment' => false
        ]);

        exit;
    }
}
