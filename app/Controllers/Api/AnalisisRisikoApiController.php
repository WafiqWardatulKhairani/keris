<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class AnalisisRisikoApiController extends BaseController
{
    public function index()
    {
        // Validasi API Key
        $apiKey = $this->request->getHeaderLine('X-API-Key');
        $validApiKey = env('CAPKIN_API_KEY');

        if (
            empty($apiKey) ||
            empty($validApiKey) ||
            !hash_equals($validApiKey, $apiKey)
        ) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Unauthorized. API key tidak valid.',
                ]);
        }

        $db = \Config\Database::connect();

        $data = $db->table('identifikasi_risiko ir')
            ->select('
                ir.id_identifikasi,
                ir.pernyataan_risiko,
                pb.kode_proses,
                pb.uraian_proses,
                pr.nilai_risiko,
                sl.nama_level AS level_risiko,
                k.id_tim,
                tk.nama_tim,
                k.id_kegiatan,
                keg.nama_kegiatan,
                k.tahun
            ')
            ->join(
                'konteks_proses_bisnis kpb',
                'kpb.id_konteks_proses = ir.id_konteks_proses'
            )
            ->join(
                'konteks k',
                'k.id_konteks = kpb.id_konteks'
            )
            ->join(
                'proses_bisnis pb',
                'pb.id_proses = kpb.id_proses'
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
                'pr.id_identifikasi = ir.id_identifikasi',
                'left'
            )
            ->join(
                'selera_risiko sl',
                'sl.id_selera = pr.id_selera',
                'left'
            )
            ->orderBy('k.tahun', 'DESC')
            ->orderBy('tk.nama_tim', 'ASC')
            ->orderBy('pb.kode_proses', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response
            ->setStatusCode(200)
            ->setJSON([
                'status' => true,
                'total'  => count($data),
                'data'   => $data,
            ]);
    }
}