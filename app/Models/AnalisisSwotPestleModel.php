<?php

namespace App\Models;

use CodeIgniter\Model;

class AnalisisSwotPestleModel extends Model
{
    protected $table            = 'analisis_swot_pestle';
    protected $primaryKey       = 'id_analisis';
    protected $returnType       = 'array';

    protected $allowedFields = [
        'id_konteks',
        'id_tim',
        'jenis_analisis',
        'kategori',
        'uraian',
        'tahun',
        'triwulan',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
