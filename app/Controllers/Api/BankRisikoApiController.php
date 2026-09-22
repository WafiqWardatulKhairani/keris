<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\BankRisikoModel;

class BankRisikoApiController extends BaseController
{
    protected $bankRisikoModel;

    public function __construct()
    {
        $this->bankRisikoModel = new BankRisikoModel();
    }

    public function index()
    {
        // Ambil API Key dari header request
        $apiKey = $this->request->getHeaderLine('X-API-Key');

        // Ambil API Key yang valid dari .env
        $validApiKey = env('CAPKIN_API_KEY');

        // Validasi API Key
        if (empty($apiKey) || empty($validApiKey) || !hash_equals($validApiKey, $apiKey)) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Unauthorized. API key tidak valid.',
                ]);
        }

        // Ambil data Bank Risiko
        $data = $this->bankRisikoModel
            ->select('id_bank_risiko, pernyataan_risiko')
            ->orderBy('id_bank_risiko', 'ASC')
            ->findAll();

        return $this->response
            ->setStatusCode(200)
            ->setJSON([
                'status' => true,
                'total'  => count($data),
                'data'   => $data,
            ]);
    }
}