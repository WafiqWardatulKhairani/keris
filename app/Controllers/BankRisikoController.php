<?php

namespace App\Controllers;

use App\Models\BankRisikoModel;
use CodeIgniter\Controller;

class BankRisikoController extends Controller
{
    protected $model;

    public function __construct()
    {
        $this->model = new BankRisikoModel();
    }

    public function index()
{
    $perPage = (int) ($this->request->getGet('perPage') ?? 10);
    $page    = (int) ($this->request->getGet('page') ?? 1);

    $offset = ($page - 1) * $perPage;

    $total = $this->model->countAllResults();

    $data = $this->model
        ->orderBy('id_bank_risiko', 'ASC')
        ->findAll($perPage, $offset);

    $from = $total > 0 ? $offset + 1 : 0;
    $to   = min($offset + $perPage, $total);

    $totalPages = (int) ceil($total / $perPage);

    $pager = [
        'currentPage' => $page,
        'totalPages'  => $totalPages,
        'perPage'     => $perPage,
        'total'       => $total,
    ];

    return view('bank_risiko/index', [
        'data'              => $data,
        'pager'             => $pager,
        'perPage'           => $perPage,
        'from'              => $from,
        'to'                => $to,
        'total'             => $total,
        'hideGlobalContext' => true,
    ]);
}

    public function store()
    {
        if (!$this->request->isAJAX())
            return redirect()->back();

        $this->model->insert([
            'pernyataan_risiko' => $this->request->getPost('pernyataan_risiko'),
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Bank Risiko berhasil disimpan.',
        ]);
    }

    public function update($id)
    {
        if (!$this->request->isAJAX())
            return redirect()->back();

        $this->model->update($id, [
            'pernyataan_risiko' => $this->request->getPost('pernyataan_risiko'),
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Bank Risiko berhasil diperbarui.',
        ]);
    }

    public function delete($id)
    {
        if (!$this->request->isAJAX())
            return redirect()->back();

        $this->model->delete($id);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Bank Risiko berhasil dihapus.',
        ]);
    }

    public function ajaxTable()
{
    if (!$this->request->isAJAX()) {
        return redirect()->back();
    }

    $perPage = (int) ($this->request->getGet('per_page') ?? 10);
    $page    = (int) ($this->request->getGet('page') ?? 1);

    $offset = ($page - 1) * $perPage;

    $total = $this->model->countAllResults();

    $data = $this->model
        ->orderBy('id_bank_risiko', 'ASC')
        ->findAll($perPage, $offset);

    $from = $total > 0 ? $offset + 1 : 0;
    $to   = min($offset + $perPage, $total);

    $totalPages = (int) ceil($total / $perPage);

    $pager = [
        'currentPage' => $page,
        'totalPages'  => $totalPages,
        'perPage'     => $perPage,
        'total'       => $total,
    ];

    return view('bank_risiko/_table_section', [
        'data'    => $data,
        'pager'   => $pager,
        'perPage' => $perPage,
        'from'    => $from,
        'to'      => $to,
        'total'   => $total,
    ]);
}

    /**
     * Endpoint untuk dropdown Pernyataan Risiko
     * di form Identifikasi Risiko
     */
    public function list()
    {
        if (!$this->request->isAJAX())
            return redirect()->back();

        $data = $this->model->getForDropdown();

        return $this->response->setJSON($data);
    }
}
