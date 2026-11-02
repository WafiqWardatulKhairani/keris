<?php

namespace App\Models;

use CodeIgniter\Model;

class IdentifikasiRisikoModel extends Model
{
    protected $table      = 'identifikasi_risiko';
    protected $primaryKey = 'id_identifikasi';

    protected $returnType = 'array';


    protected $allowedFields = [
        'id_konteks_proses',
        'pernyataan_risiko',
        'penyebab_risiko',
        'dampak_risiko',
        'id_kategori_risiko',
        'sumber_risiko',
        'keterjadian_risiko',
        'jumlah_keterjadian',
    ];

    protected $useTimestamps = true;
}
