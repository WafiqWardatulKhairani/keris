<?php

namespace App\Models;

use CodeIgniter\Model;

class RencanaPenangananRisikoModel extends Model
{
    protected $table            = 'rencana_penanganan_risiko';
    protected $primaryKey       = 'id_rtp';

    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'id_penilaian_awal',
        'uraian_rtp',
        'jenis_penanganan',
        'target_output',
        'target_waktu',
        'id_kemungkinan_residu',
        'id_dampak_residu',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'id_penilaian_awal'     => 'required|integer',
        'uraian_rtp'            => 'required',
        'jenis_penanganan' => 'permit_empty|in_list[Preventif,Korektif,Direktif,Detektif]',
        'target_output'         => 'required',
        'target_waktu'          => 'required|valid_date',
        'id_kemungkinan_residu' => 'permit_empty|integer',
        'id_dampak_residu'      => 'permit_empty|integer',
    ];

    protected $validationMessages = [
        'id_penilaian_awal' => [
            'required' => 'Penilaian risiko harus dipilih.',
            'integer'  => 'ID penilaian tidak valid.',
        ],
        'uraian_rtp' => [
            'required' => 'Uraian RTP harus diisi.',
        ],
        'target_output' => [
            'required' => 'Target output harus diisi.',
        ],
        'target_waktu' => [
            'required'   => 'Target waktu harus diisi.',
            'valid_date' => 'Format tanggal tidak valid.',
        ],
    ];

    protected $skipValidation = false;

    /*
    |--------------------------------------------------------------------------
    | Custom Query
    |--------------------------------------------------------------------------
    */

    /**
     * Ambil semua RTP milik satu evaluasi (id_evaluasi)
     */
    public function getByEvaluasi(int $id_evaluasi): array
    {
        return $this->where('id_penilaian_awal', $id_evaluasi)->findAll();
    }

    /**
     * Ambil RTP lengkap dengan info risiko, konteks, residu
     */
    public function getRtpLengkap(int $id_rtp): ?array
    {
        return $this->db->table('rencana_penanganan_risiko rtp')
            ->select('
                rtp.*,
                er.id_evaluasi,
                er.opsi_tindakan,
                ir.id_identifikasi,
                ir.pernyataan_risiko,
                ir.penyebab_risiko,
                ir.dampak_risiko,
                pb.kode_proses,
                pb.uraian_proses,
                k.tahun,
                sk.id_satuan_kerja,
                sk.nama_satuan_kerja,
                ss.uraian_sasaran as sasaran_strategis,
                g.nama as nama_pengelola,
                pr.nilai_risiko,
                pr.warna_risiko,
                km_a.level  as level_kemungkinan,
                kd_a.level  as level_dampak,
                sl.nama_level as nama_selera,
                sl.warna      as warna_selera,
                km_r.level    as level_kemungkinan_residu,
                km_r.nama_level as nama_kemungkinan_residu,
                kd_r.level    as level_dampak_residu,
                kd_r.nama_level as nama_dampak_residu
            ')
            ->join('evaluasi_risiko er',          'er.id_evaluasi = rtp.id_penilaian_awal')
            ->join('identifikasi_risiko ir',       'ir.id_identifikasi = er.id_identifikasi')
            ->join('penilaian_risiko pr',          'pr.id_penilaian = er.id_penilaian', 'left')
            ->join('kriteria_kemungkinan km_a',    'km_a.id_kriteria = pr.id_kemungkinan', 'left')
            ->join('kriteria_dampak kd_a',         'kd_a.id_kriteria = pr.id_dampak', 'left')
            ->join('selera_risiko sl',             'sl.id_selera = pr.id_selera', 'left')
            ->join('konteks_proses_bisnis kpb',    'kpb.id_konteks_proses = ir.id_konteks_proses')
            ->join('proses_bisnis pb',             'pb.id_proses = kpb.id_proses')
            ->join('konteks k',                   'k.id_konteks = kpb.id_konteks')
            ->join('satuan_kerja sk',              'sk.id_satuan_kerja = k.id_satuan_kerja', 'left')
            ->join('sasaran_strategis ss',         'ss.id_sasaran_strategis = k.id_sasaran_strategis', 'left')
            ->join('pengelola_risiko g',           'g.id = k.pengelola_risiko_id', 'left')
            ->join('kriteria_kemungkinan km_r',    'km_r.id_kriteria = rtp.id_kemungkinan_residu', 'left')
            ->join('kriteria_dampak kd_r',         'kd_r.id_kriteria = rtp.id_dampak_residu', 'left')
            ->where('rtp.id_rtp', $id_rtp)
            ->get()->getRowArray();
    }
}
