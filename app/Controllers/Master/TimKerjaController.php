<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class TimKerjaController extends BaseController
{
    protected $db;
    protected $table = 'tim_kerja';

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        return view('master/tim_kerja/index', [
            'title' => 'Tim Kerja',
            'hideGlobalContext' => true,
        ]);
    }

    public function table()
    {
        $data = $this->db->query("
            SELECT
                tk.id_tim AS id,
                tk.nama_tim,
                COUNT(k.id_kegiatan) AS jumlah_kegiatan,
                STRING_AGG(k.nama_kegiatan, '||') AS kegiatan
            FROM tim_kerja tk
            LEFT JOIN kegiatan k
                ON k.id_tim = tk.id_tim
            GROUP BY tk.id_tim, tk.nama_tim
            ORDER BY tk.id_tim DESC
        ")->getResultArray();

        return $this->response->setJSON($data);
    }

    public function detail($id)
    {
        $tim = $this->db->table('tim_kerja')
            ->where('id_tim', $id)
            ->get()
            ->getRowArray();

        $kegiatan = $this->db->table('kegiatan')
            ->select('id_kegiatan,nama_kegiatan')
            ->where('id_tim', $id)
            ->orderBy('nama_kegiatan')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'tim' => $tim,
            'kegiatan' => $kegiatan
        ]);
    }

    public function store()
    {
        $data = $this->request->getJSON(true);

        $this->db->transStart();

        $this->db->table('tim_kerja')->insert([
            'nama_tim'   => $data['nama'],
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $idTim = $this->db->insertID();

        foreach ($data['kegiatan'] as $kegiatan) {

            if (trim($kegiatan) === '') {
                continue;
            }

            $this->db->table('kegiatan')->insert([
                'id_tim'         => $idTim,
                'nama_kegiatan'  => $kegiatan,
                'created_at'     => date('Y-m-d H:i:s')
            ]);
        }

        $this->db->transComplete();

        return $this->response->setJSON([
            'status' => true
        ]);
    }

    public function update($id)
    {
        $data = $this->request->getJSON(true);
    
        $this->db->transStart();
    
        // ==========================
        // Update nama tim
        // ==========================
        $this->db->table('tim_kerja')
            ->where('id_tim', $id)
            ->update([
                'nama_tim'   => $data['nama'],
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    
        // Menyimpan id kegiatan yang masih ada
        $existingIds = [];
    
        // ==========================
        // Update / Insert kegiatan
        // ==========================
        foreach ($data['kegiatan'] as $item) {
    
            if (trim($item['nama']) == '') {
                continue;
            }
    
            // =====================
            // UPDATE
            // =====================
            if (!empty($item['id'])) {
    
                $existingIds[] = $item['id'];
    
                $this->db->table('kegiatan')
                    ->where('id_kegiatan', $item['id'])
                    ->update([
                        'nama_kegiatan' => $item['nama'],
                        'updated_at'    => date('Y-m-d H:i:s')
                    ]);
    
            }
            // =====================
            // INSERT
            // =====================
            else {
    
                $this->db->table('kegiatan')
                    ->insert([
                        'id_tim'         => $id,
                        'nama_kegiatan'  => $item['nama'],
                        'created_at'     => date('Y-m-d H:i:s')
                    ]);
    
            }
        }
    
        // ==========================
        // Cari kegiatan yang dihapus dari UI
        // ==========================
        $builder = $this->db->table('kegiatan')
            ->where('id_tim', $id);
    
        if (!empty($existingIds)) {
            $builder->whereNotIn('id_kegiatan', $existingIds);
        }
    
        $deleted = $builder->get()->getResultArray();
    
        foreach ($deleted as $row) {
    
            // cek apakah kegiatan sudah dipakai pada konteks
            $dipakai = $this->db->table('konteks')
                ->where('id_kegiatan', $row['id_kegiatan'])
                ->countAllResults();
    
            if ($dipakai == 0) {
    
                // aman dihapus
                $this->db->table('kegiatan')
                    ->where('id_kegiatan', $row['id_kegiatan'])
                    ->delete();
    
            } else {
    
                // batalkan transaksi
                $this->db->transRollback();
    
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Kegiatan "' . $row['nama_kegiatan'] . '" tidak dapat dihapus karena sudah digunakan pada Penetapan Konteks.'
                ]);
            }
        }
    
        $this->db->transComplete();
    
        return $this->response->setJSON([
            'status' => true
        ]);
    }

    public function delete($id)
    {
        $this->db->table($this->table)->delete(['id_tim' => $id]);
        return $this->response->setJSON(['status' => true]);
    }
}
