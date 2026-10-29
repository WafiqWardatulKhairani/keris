<?php

namespace App\Models;

use CodeIgniter\Model;

class AnalisisPeriodeModel extends Model
{
    protected $table            = 'analisis_periode';
    protected $primaryKey       = 'id_periode';
    protected $returnType       = 'array';

    protected $allowedFields = [
        'id_konteks',
        'id_tim',
        'tahun',
        'triwulan',
        'dibuka_kembali',
        'dibuka_oleh',
        'dibuka_pada',
        'alasan_buka',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Mengambil konfigurasi periode berdasarkan tim,
     * tahun, dan triwulan.
     */
    public function getPeriode(
        int $idTim,
        int $tahun,
        int $triwulan
    ): ?array {
        return $this->where('id_tim', $idTim)
            ->where('tahun', $tahun)
            ->where('triwulan', $triwulan)
            ->first();
    }
}
