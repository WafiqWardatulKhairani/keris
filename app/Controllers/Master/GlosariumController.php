<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;

class GlosariumController extends BaseController
{
    protected $db;
    protected $table = 'glosarium';

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        return view('master/glosarium/index', [
            'title' => 'Glosarium',
            'hideGlobalContext' => true,
        ]);
    }

    public function table()
    {
        $data = $this->db->table($this->table)
            ->select('id_glosarium, istilah, definisi')
            ->orderBy('istilah', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON($data);
    }

    public function store()
    {
        $role = session()->get('user_role');

        if ($role !== 'admin') {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'status' => false,
                    'message' => 'Kamu tidak memiliki akses untuk menambah istilah.',
                ]);
        }

        $istilah = trim((string) $this->request->getPost('istilah'));
        $definisi = trim((string) $this->request->getPost('definisi'));

        if ($istilah === '' || $definisi === '') {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status' => false,
                    'message' => 'Istilah dan definisi wajib diisi.',
                ]);
        }

        $this->db->table($this->table)->insert([
            'istilah' => $istilah,
            'definisi' => $definisi,
            'created_by' => session()->get('user_id'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['status' => true]);
    }

    public function update($id)
    {
        if (session()->get('user_role') !== 'admin') {
            return $this->response->setStatusCode(403)
                ->setJSON(['status' => false, 'message' => 'Akses ditolak.']);
        }

        $istilah = trim((string) $this->request->getPost('istilah'));
        $definisi = trim((string) $this->request->getPost('definisi'));

        if ($istilah === '' || $definisi === '') {
            return $this->response->setStatusCode(422)
                ->setJSON([
                    'status' => false,
                    'message' => 'Istilah dan definisi wajib diisi.',
                ]);
        }

        $builder = $this->db->table($this->table);

        if (!$builder->where('id_glosarium', $id)->countAllResults()) {
            return $this->response->setStatusCode(404)
                ->setJSON(['status' => false, 'message' => 'Istilah tidak ditemukan.']);
        }

        $this->db->table($this->table)
            ->where('id_glosarium', $id)
            ->update([
                'istilah' => $istilah,
                'definisi' => $definisi,
                'updated_by' => session()->get('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return $this->response->setJSON(['status' => true]);
    }

    public function delete($id)
    {
        if (session()->get('user_role') !== 'admin') {
            return $this->response->setStatusCode(403)
                ->setJSON(['status' => false, 'message' => 'Akses ditolak.']);
        }

        $this->db->table($this->table)
            ->where('id_glosarium', $id)
            ->delete();

        return $this->response->setJSON(['status' => true]);
    }
}
