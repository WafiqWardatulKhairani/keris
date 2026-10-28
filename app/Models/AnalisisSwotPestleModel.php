<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model Analisis SWOT dan PESTLE.
 *
 * Mengelola poin analisis yang terhubung dengan konteks KERIS.
 * Satu konteks dapat memiliki banyak poin dalam setiap kategori.
 */
class AnalisisSwotPestleModel extends Model
{
    protected $table            = 'analisis_swot_pestle';
    protected $primaryKey       = 'id_analisis';
    protected $returnType       = 'array';


    protected $allowedFields = [
        'id_konteks',
        'jenis_analisis',
        'kategori',
        'uraian',
        'tahun',
        'triwulan',
    ];


    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Mengambil poin analisis berdasarkan konteks,
     * jenis analisis, dan kategori.
     */
    public function getByKategori(
        int $idKonteks,
        string $jenis,
        string $kategori
    ): array {
        return $this->where('id_konteks', $idKonteks)
            ->where('jenis_analisis', strtoupper($jenis))
            ->where('kategori', strtoupper($kategori))
            ->orderBy('id_analisis', 'ASC')
            ->findAll();
    }

    /**
     * Mengambil seluruh poin analisis milik suatu konteks.
     */
    public function getByKonteks(int $idKonteks): array
    {
        return $this->where('id_konteks', $idKonteks)
            ->orderBy('jenis_analisis', 'ASC')
            ->orderBy('kategori', 'ASC')
            ->orderBy('id_analisis', 'ASC')
            ->findAll();
    }
}
