<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TimKerjaModel;
use App\Models\KegiatanModel;

class GlobalContextController extends BaseController
{
    public function set()
{
    $tahun      = $this->request->getPost('tahun');
    $idTim      = $this->request->getPost('id_tim');
    $idKegiatan = $this->request->getPost('id_kegiatan');

    $userRole = session('user_role');
    $userTim  = session('user_tim') ?? [];

    // User non-admin hanya boleh memilih tim yang dimilikinya
    if ($userRole !== 'admin') {
        $allowedTim = array_map('strval', $userTim);

        if (!in_array((string) $idTim, $allowedTim, true)) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Tim kerja tidak dapat diakses.'
                ]);
        }
    }

    // Pastikan kegiatan memang milik tim yang dipilih
    if (!empty($idKegiatan)) {
        $kegiatan = (new KegiatanModel())
            ->where('id_kegiatan', $idKegiatan)
            ->where('id_tim', $idTim)
            ->first();

        if (!$kegiatan) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Kegiatan tidak sesuai dengan tim kerja.'
                ]);
        }
    }

    session()->set([
        'global_tahun'       => $tahun,
        'global_id_tim'      => $idTim,
        'global_id_kegiatan' => $idKegiatan,
    ]);

    return $this->response->setJSON([
        'status' => 'success'
    ]);
}

    public function getKegiatanByTim()
{
    $idTim = $this->request->getGet('id_tim');

    $userRole = session('user_role');
    $userTim  = session('user_tim') ?? [];

    // User non-admin hanya boleh mengambil kegiatan
    // dari tim yang dimilikinya
    if ($userRole !== 'admin') {
        $allowedTim = array_map('strval', $userTim);

        if (!in_array((string) $idTim, $allowedTim, true)) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Tim kerja tidak dapat diakses.'
                ]);
        }
    }

    $kegiatan = (new KegiatanModel())
        ->where('id_tim', $idTim)
        ->orderBy('nama_kegiatan', 'ASC')
        ->findAll();

    return $this->response->setJSON($kegiatan);
}

    public function reset()
    {
        session()->remove([
            'global_tahun',
            'global_id_tim',
            'global_id_kegiatan',
        ]);

        return $this->response->setJSON([
            'status' => 'success'
        ]);
    }
}
