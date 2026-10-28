<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model pengaturan periode analisis SWOT dan PESTLE.
 *
 * Menyimpan status pembukaan kembali periode yang sudah berakhir.
 * Penguncian otomatis berdasarkan kalender akan divalidasi
 * oleh controller sebelum operasi tambah, edit, dan hapus.
 */
class AnalisisPeriodeModel extends Model
{
    protected $table            = 'analisis_periode';
    protected $primaryKey       = 'id_periode';
    protected $returnType       = 'array';

    protected $allowedFields = [
        'id_konteks',
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
     * Mengambil konfigurasi periode tertentu.
     */
    public function getPeriode(
        int $idKonteks,
        int $tahun,
        int $triwulan
    ): ?array {
        return $this->where('id_konteks', $idKonteks)
            ->where('tahun', $tahun)
            ->where('triwulan', $triwulan)
            ->first();
    }
}
