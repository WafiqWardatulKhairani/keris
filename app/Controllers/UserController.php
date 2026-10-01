<?php

namespace App\Controllers;

use App\Models\UserModel;

class UserController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new UserModel();
    }

    public function index()
    {
        $db = \Config\Database::connect();

        $users = $db->table('users u')
            ->select('u.*, r.name as role_name, t.nama_tim as tim_name')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->join('tim_kerja t', 't.id_tim = u.id_tim', 'left')
            ->get()
            ->getResultArray();

        $roles = $db->table('roles')->get()->getResultArray();
        $timKerja = $db->table('tim_kerja')->get()->getResultArray();

        return view('user/index', [
            'users' => $users,
            'roles' => $roles,
            'timKerja' => $timKerja,
            'hideGlobalContext' => true,
        ]);
    }

public function table()
{
    $db = \Config\Database::connect();

    $users = $this->model
        ->select('users.id, users.name, users.email')
        ->orderBy('users.name', 'ASC')
        ->findAll();

    foreach ($users as &$user) {

        // Ambil semua role user
        $roleRows = $db->table('user_roles ur')
            ->select('r.name')
            ->join('roles r', 'r.id = ur.role_id')
            ->where('ur.user_id', $user['id'])
            ->orderBy('r.name', 'ASC')
            ->get()
            ->getResultArray();

        $user['roles'] = array_column($roleRows, 'name');

        // Ambil semua tim kerja user
        $timRows = $db->table('user_tim_kerja utk')
            ->select('tk.nama_tim')
            ->join('tim_kerja tk', 'tk.id_tim = utk.id_tim')
            ->where('utk.user_id', $user['id'])
            ->orderBy('tk.nama_tim', 'ASC')
            ->get()
            ->getResultArray();

        $user['tim_kerja'] = array_column($timRows, 'nama_tim');
    }

    unset($user);

    return $this->response->setJSON($users);
}

    public function store()
{
    $db = \Config\Database::connect();

    $name     = trim((string) $this->request->getPost('name'));
    $email    = trim((string) $this->request->getPost('email'));
    $password = (string) $this->request->getPost('password');

    $roleIds = $this->request->getPost('role_ids') ?? [];
    $timIds  = $this->request->getPost('tim_ids') ?? [];

    $roleIds = array_values(array_unique(array_filter(array_map('intval', (array) $roleIds))));
    $timIds  = array_values(array_unique(array_filter(array_map('intval', (array) $timIds))));

    if ($name === '' || $email === '' || $password === '') {
        return $this->response->setStatusCode(400)->setJSON([
            'status'  => false,
            'message' => 'Nama, email, dan password wajib diisi'
        ]);
    }

    if (empty($roleIds)) {
        return $this->response->setStatusCode(400)->setJSON([
            'status'  => false,
            'message' => 'Pilih minimal satu role'
        ]);
    }

    if (empty($timIds)) {
        return $this->response->setStatusCode(400)->setJSON([
            'status'  => false,
            'message' => 'Pilih minimal satu tim kerja'
        ]);
    }

    $db->transStart();

    // Tetap isi kolom lama untuk backward compatibility
    $userId = $this->model->insert([
        'name'     => $name,
        'email'    => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'role_id'  => $roleIds[0],
        'id_tim'   => $timIds[0],
    ], true);

    foreach ($roleIds as $roleId) {
        $db->table('user_roles')->insert([
            'user_id' => $userId,
            'role_id' => $roleId,
        ]);
    }

    foreach ($timIds as $timId) {
        $db->table('user_tim_kerja')->insert([
            'user_id' => $userId,
            'id_tim'  => $timId,
        ]);
    }

    $db->transComplete();

    if (!$db->transStatus()) {
        return $this->response->setStatusCode(500)->setJSON([
            'status'  => false,
            'message' => 'Gagal menyimpan user'
        ]);
    }

    return $this->response->setJSON([
        'status'  => true,
        'message' => 'User berhasil ditambahkan'
    ]);
}

public function update($id)
{
    $db = \Config\Database::connect();

    $user = $this->model->find($id);

    if (!$user) {
        return $this->response->setStatusCode(404)->setJSON([
            'status'  => false,
            'message' => 'User tidak ditemukan'
        ]);
    }

    $name     = trim((string) $this->request->getPost('name'));
    $email    = trim((string) $this->request->getPost('email'));
    $password = (string) $this->request->getPost('password');

    $roleIds = $this->request->getPost('role_ids') ?? [];
    $timIds  = $this->request->getPost('tim_ids') ?? [];

    $roleIds = array_values(array_unique(array_filter(array_map('intval', (array) $roleIds))));
    $timIds  = array_values(array_unique(array_filter(array_map('intval', (array) $timIds))));

    if ($name === '' || $email === '') {
        return $this->response->setStatusCode(400)->setJSON([
            'status'  => false,
            'message' => 'Nama dan email wajib diisi'
        ]);
    }

    if (empty($roleIds)) {
        return $this->response->setStatusCode(400)->setJSON([
            'status'  => false,
            'message' => 'Pilih minimal satu role'
        ]);
    }

    if (empty($timIds)) {
        return $this->response->setStatusCode(400)->setJSON([
            'status'  => false,
            'message' => 'Pilih minimal satu tim kerja'
        ]);
    }

    $data = [
        'name'    => $name,
        'email'   => $email,

        // Legacy tetap disinkronkan
        'role_id' => $roleIds[0],
        'id_tim'  => $timIds[0],
    ];

    if ($password !== '') {
        $data['password'] = password_hash($password, PASSWORD_DEFAULT);
    }

    $db->transStart();

    $this->model->update($id, $data);

    // Sinkronisasi semua role
    $db->table('user_roles')
        ->where('user_id', $id)
        ->delete();

    foreach ($roleIds as $roleId) {
        $db->table('user_roles')->insert([
            'user_id' => $id,
            'role_id' => $roleId,
        ]);
    }

    // Sinkronisasi semua tim
    $db->table('user_tim_kerja')
        ->where('user_id', $id)
        ->delete();

    foreach ($timIds as $timId) {
        $db->table('user_tim_kerja')->insert([
            'user_id' => $id,
            'id_tim'  => $timId,
        ]);
    }

    $db->transComplete();

    if (!$db->transStatus()) {
        return $this->response->setStatusCode(500)->setJSON([
            'status'  => false,
            'message' => 'Gagal memperbarui user'
        ]);
    }

    return $this->response->setJSON([
        'status'  => true,
        'message' => 'User berhasil diperbarui'
    ]);
}

    public function detail($id)
{
    $db = \Config\Database::connect();

    $user = $this->model
        ->select('
            users.id,
            users.name,
            users.email,
            users.role_id,
            users.id_tim
        ')
        ->where('users.id', $id)
        ->first();

    if (!$user) {
        return $this->response->setStatusCode(404)
            ->setJSON([
                'status' => false,
                'message' => 'User tidak ditemukan'
            ]);
    }

    // Semua role milik user
    $roleRows = $db->table('user_roles')
        ->select('role_id')
        ->where('user_id', $id)
        ->get()
        ->getResultArray();

    $user['role_ids'] = array_map(
        'intval',
        array_column($roleRows, 'role_id')
    );

    // Fallback ke data lama
    if (empty($user['role_ids']) && !empty($user['role_id'])) {
        $user['role_ids'] = [(int) $user['role_id']];
    }

    // Semua tim kerja milik user
    $timRows = $db->table('user_tim_kerja')
        ->select('id_tim')
        ->where('user_id', $id)
        ->get()
        ->getResultArray();

    $user['tim_ids'] = array_map(
        'intval',
        array_column($timRows, 'id_tim')
    );

    // Fallback ke data lama
    if (empty($user['tim_ids']) && !empty($user['id_tim'])) {
        $user['tim_ids'] = [(int) $user['id_tim']];
    }

    return $this->response->setJSON([
        'status' => true,
        'data' => $user
    ]);
}

public function delete($id)
{
    $db = \Config\Database::connect();

    // User tidak boleh menghapus akun sendiri
    if ((int) $id === (int) session('user_id')) {
        return $this->response
            ->setStatusCode(400)
            ->setJSON([
                'status'  => false,
                'message' => 'Tidak dapat menghapus akun yang sedang digunakan'
            ]);
    }

    $user = $this->model->find($id);

    if (!$user) {
        return $this->response
            ->setStatusCode(404)
            ->setJSON([
                'status'  => false,
                'message' => 'User tidak ditemukan'
            ]);
    }

    $db->transStart();

    // Hapus relasi role
    $db->table('user_roles')
        ->where('user_id', $id)
        ->delete();

    // Hapus relasi tim kerja
    $db->table('user_tim_kerja')
        ->where('user_id', $id)
        ->delete();

    // Baru hapus user
    $this->model->delete($id);

    $db->transComplete();

    if (!$db->transStatus()) {
        return $this->response
            ->setStatusCode(500)
            ->setJSON([
                'status'  => false,
                'message' => 'Gagal menghapus user'
            ]);
    }

    return $this->response->setJSON([
        'status'  => true,
        'message' => 'User berhasil dihapus'
    ]);
}

    public function roles()
    {
        $data = db_connect()
            ->table('roles')
            ->get()
            ->getResultArray();

        return $this->response->setJSON($data);
    }

    public function timKerja()
    {
        $data = db_connect()
            ->table('tim_kerja')
            ->select('id_tim,nama_tim')
            ->orderBy('nama_tim', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON($data);
    }
}
