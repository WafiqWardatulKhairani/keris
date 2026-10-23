<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;

{
    protected $db;
    protected $table = 'bank_risiko';

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        return view('master/bank_risiko/index', ['title' => 'Bank Risiko', 'hideGlobalContext' => true,]);
    }

    public function table()
    {
        $data = $this->db->table($this->table)
            ->select('id_bank_risiko as id,pernyataan_risiko,status,notes')
            ->orderBy('id_bank_risiko', 'DESC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON($data);
    }

    public function store()
    {
        $this->db->table($this->table)->insert([
            'pernyataan_risiko' => $this->request->getPost('pernyataan'),
            'status' => 'approved',
            'created_by' => session()->get('user_id'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON(['status' => true]);
    }

    public function update($id)
    {
        $this->db->table($this->table)
            ->where('id_bank_risiko', $id)
            ->update([
                'pernyataan_risiko' => $this->request->getPost('pernyataan'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        return $this->response->setJSON(['status' => true]);
    }

    public function delete($id)
    {
        $this->db->table($this->table)->delete(['id_bank_risiko' => $id]);
        return $this->response->setJSON(['status' => true]);
    }

    public function approve($id)
    {
        $this->db->table($this->table)
            ->where('id_bank_risiko', $id)
            ->update([
                'status' => 'approved',
                'approved_by' => session()->get('user_id'),
                'approved_at' => date('Y-m-d H:i:s'),
                'notes' => null
            ]);

        return $this->response->setJSON(['status' => true]);
    }

    public function reject($id)
    {
        $this->db->table($this->table)
            ->where('id_bank_risiko', $id)
            ->update([
                'status' => 'rejected',
                'notes' => $this->request->getPost('notes')
            ]);

        return $this->response->setJSON(['status' => true]);
    }
}
