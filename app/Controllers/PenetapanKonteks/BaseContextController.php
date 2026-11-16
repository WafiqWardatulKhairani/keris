<?php

namespace App\Controllers\PenetapanKonteks;

use App\Controllers\BaseController;
use App\Models\KonteksModel;

class BaseContextController extends BaseController
{
    protected function getActiveKonteks($id = null)
    {
        $builder = (new KonteksModel())
            ->select('
            konteks.*,
            kegiatan.nama_kegiatan,
            tim_kerja.nama_tim,
            sasaran_strategis.uraian_sasaran,
            p.nama as nama_pemilik,
            g.nama as nama_pengelola
        ')
            ->join('kegiatan', 'kegiatan.id_kegiatan = konteks.id_kegiatan')
            ->join('tim_kerja', 'tim_kerja.id_tim = konteks.id_tim')
            ->join('sasaran_strategis', 'sasaran_strategis.id_sasaran_strategis = konteks.id_sasaran_strategis', 'left')
            ->join('pengelola_risiko p', 'p.id = konteks.pemilik_risiko_id', 'left')
            ->join('pengelola_risiko g', 'g.id = konteks.pengelola_risiko_id', 'left');

        /*
    |--------------------------------------------------------------------------
    | PRIORITAS 1
    | explicit context id dari URL
    |--------------------------------------------------------------------------
    */
        if ($id) {
            return $builder
                ->where('konteks.id_konteks', $id)
                ->first();
        }

        /*
    |--------------------------------------------------------------------------
    | PRIORITAS 2
    | global selector
    |--------------------------------------------------------------------------
    */
        $tahun      = session('global_tahun');
        $idTim      = session('global_id_tim');
        $idKegiatan = session('global_id_kegiatan');

        if ($tahun) {
            $builder->where('konteks.tahun', $tahun);
        }

        if ($idTim) {
            $builder->where('konteks.id_tim', $idTim);
        }

        if (!empty($idKegiatan) && $idKegiatan !== 'all') {
            $builder->where('konteks.id_kegiatan', $idKegiatan);
        }

        return $builder
            ->orderBy('konteks.created_at', 'DESC')
            ->first();
    }

    protected function getListKonteks()
    {
        return (new KonteksModel())
            ->select('
            konteks.id_konteks,
            konteks.tahun,
            konteks.id_tim,
            konteks.pengelola_risiko_id,
            konteks.id_kegiatan,
            konteks.id_sasaran_strategis,
            kegiatan.nama_kegiatan,
            tim_kerja.nama_tim,
            sasaran_strategis.uraian_sasaran,
            p.nama as nama_pemilik,
            g.nama as nama_pengelola
        ')
            ->join('tim_kerja', 'tim_kerja.id_tim = konteks.id_tim')
            ->join('sasaran_strategis', 'sasaran_strategis.id_sasaran_strategis = konteks.id_sasaran_strategis')
            ->join('kegiatan', 'kegiatan.id_kegiatan = konteks.id_kegiatan')
            ->join('pengelola_risiko p', 'p.id = konteks.pemilik_risiko_id', 'left')
            ->join('pengelola_risiko g', 'g.id = konteks.pengelola_risiko_id', 'left')
            ->orderBy('konteks.created_at', 'DESC')
            ->findAll();
    }

    protected function contextData($id = null): array
    {
        return [
            'activeKonteks' => $this->getActiveKonteks($id),
            'listKonteks'   => $this->getListKonteks(),
        ];
    }
}
