<?php

namespace App\Models;

use CodeIgniter\Model;

class PemantauanRisikoModel extends Model
{
    protected $table          = 'pemantauan_risiko';
    protected $primaryKey     = 'id_pemantauan';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'id_rtp',
        'realisasi_output',
        'realisasi_waktu',
        'status',
        'catatan',
        'status_validasi',
        'catatan_validasi',
        'validated_by',
        'validated_at',
        'created_at',
        'updated_at',        
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $validationRules = [
        'id_rtp' => [
            'label' => 'RTP',
            'rules' => 'required|integer',
        ],
        'status' => [
            'label' => 'Status',
            'rules' => 'required|in_list[Belum Dilaksanakan,Dalam Proses,Selesai,Terlambat]',
        ],
        // permit_empty — input type="month" kirim YYYY-MM bukan YYYY-MM-DD
        'realisasi_waktu' => [
            'label' => 'Realisasi Waktu',
            'rules' => 'permit_empty',
        ],
    ];

    protected $validationMessages = [
        'id_rtp' => [
            'required' => 'ID RTP wajib diisi.',
            'integer'  => 'ID RTP harus berupa angka.',
        ],
        'status' => [
            'required' => 'Status pemantauan wajib dipilih.',
            'in_list'  => 'Status tidak valid.',
        ],
    ];

    /* getByRtp — ambil data lengkap saat pemantauan sudah ada */
    public function getByRtp(int $idRtp): ?array
    {
        return $this->db->table('pemantauan_risiko pm')
            ->select("
                pm.*,
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
                g.nama as nama_pengelola,
                pr.nilai_risiko,
                pr.warna_risiko,
                sl.nama_level as nama_selera,
                sl.warna as warna_selera,
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
                    THEN 'Belum Dilaksanakan'

                    ELSE 'Dalam Proses'
                END as status
            ")
            ->join('rencana_penanganan_risiko rtp', 'rtp.id_rtp = pm.id_rtp')
            ->join('evaluasi_risiko er',             'er.id_evaluasi = rtp.id_penilaian_awal')
            ->join('identifikasi_risiko ir',         'ir.id_identifikasi = er.id_identifikasi')
            ->join('konteks_proses_bisnis kpb',      'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('proses_bisnis pb',               'pb.id_proses = kpb.id_proses')
            ->join('konteks k',                      'k.id_konteks = kpb.id_konteks')
            ->join('tim_kerja sk',                'sk.id_tim = k.id_tim',              'left')
            ->join('sasaran_strategis ss',           'ss.id_sasaran_strategis = k.id_sasaran_strategis',   'left')
            ->join('sasaran_kinerja sk_kinerja',     'sk_kinerja.id_konteks_proses = ir.id_konteks_proses', 'left')
            ->join('pengelola_risiko g',             'g.id = k.pengelola_risiko_id',                       'left')
            ->join('penilaian_risiko pr',            'pr.id_penilaian = er.id_penilaian',                  'left')
            ->join('selera_risiko sl',               'sl.id_selera = pr.id_selera',                        'left')
            ->where('pm.id_rtp', $idRtp)
            ->get()->getRowArray() ?: null;
    }

    /* getLengkap */
    public function getLengkap(int $idPemantauan): ?array
    {
        return $this->db->table('pemantauan_risiko pm')
            ->select('
                pm.*,
                rtp.uraian_rtp,
                rtp.target_output,
                rtp.target_waktu,
                er.id_evaluasi,
                ir.pernyataan_risiko,
                pb.kode_proses,
                pb.uraian_proses,
                sk.nama_tim,
                k.tahun,
                ss.uraian_sasaran,
                sk_kinerja.uraian_sasaran as uraian_sasaran_kinerja,
                g.nama as nama_pengelola
            ')
            ->join('rencana_penanganan_risiko rtp', 'rtp.id_rtp = pm.id_rtp')
            ->join('evaluasi_risiko er',             'er.id_evaluasi = rtp.id_penilaian_awal')
            ->join('identifikasi_risiko ir',         'ir.id_identifikasi = er.id_identifikasi')
            ->join('konteks_proses_bisnis kpb',      'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('proses_bisnis pb',               'pb.id_proses = kpb.id_proses')
            ->join('konteks k',                      'k.id_konteks = kpb.id_konteks')
            ->join('tim_kerja sk',                'sk.id_tim = k.id_tim', 'left')
            ->join('sasaran_strategis ss',           'ss.id_sasaran_strategis = k.id_sasaran_strategis', 'left')
            ->join('sasaran_kinerja sk_kinerja',     'sk_kinerja.id_konteks_proses = ir.id_konteks_proses', 'left')
            ->join('pengelola_risiko g',             'g.id = k.pengelola_risiko_id', 'left')
            ->where('pm.id_pemantauan', $idPemantauan)
            ->get()->getRowArray() ?: null;
    }

    public function upsertByRtp(int $idRtp, array $data): int
    {
        $existing = $this->where('id_rtp', $idRtp)->first();

        if ($existing) {
            $this->skipValidation(true)->update($existing['id_pemantauan'], $data);
            return (int) $existing['id_pemantauan'];
        }

        // INSERT BARU
        $data['id_rtp'] = $idRtp;

        // TAMBAHAN BENAR DI SINI
        $data['status_validasi'] = 'Draft';

        $this->skipValidation(true)->insert($data);
        return (int) $this->getInsertID();
    }

    /* DISTRIBUSI STATUS*/
    public function getDistribusiStatus(
    $tahun = null,
    $idTim = null,
    $idKegiatan = null
): array {
    $statusList = [
        'Belum Dilaksanakan',
        'Dalam Proses',
        'Selesai',
        'Terlambat'
    ];

    $result = array_fill_keys($statusList, 0);

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
            THEN 'Belum Dilaksanakan'

            ELSE 'Dalam Proses'
        END
    ";

    $builder = $this->db
        ->table('rencana_penanganan_risiko rtp')
        ->select("
            {$statusCase} AS status,
            COUNT(DISTINCT rtp.id_rtp) AS total
        ")
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
        );

    // GLOBAL FILTER
    if (!empty($tahun)) {
        $builder->where('k.tahun', $tahun);
    }

    if (!empty($idTim)) {
        $builder->where('k.id_tim', $idTim);
    }

    if (!empty($idKegiatan)) {
        $builder->where('k.id_kegiatan', $idKegiatan);
    }

    $builder->groupBy($statusCase, false);

    $rows = $builder->get()->getResultArray();

    foreach ($rows as $row) {
        if (array_key_exists($row['status'], $result)) {
            $result[$row['status']] = (int) $row['total'];
        }
    }

    return $result;
}
    public function validasi(int $idPemantauan, string $status, ?string $catatan = null): bool
    {
        return $this->update($idPemantauan, [
            'status_validasi'  => $status,
            'catatan_validasi' => $catatan,
            'validated_by'     => session('user_id'),
            'validated_at'     => date('Y-m-d H:i:s'),
        ]);
    }
}
