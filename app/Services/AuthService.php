<?php

namespace App\Services;

use App\Models\UserModel;

class AuthService
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /*
    |--------------------------------------------------------------------------
    | LOGIN DENGAN EMAIL + PASSWORD (SEKARANG)
    |--------------------------------------------------------------------------
    */
    public function loginWithPassword(string $email, string $password): bool
    {
        $user = $this->userModel
            ->select('
        users.*,
        pengelola_risiko.nama as nama_pengelola,
        pengelola_risiko.nip,
        pengelola_risiko.jabatan,
        tim_kerja.nama_tim
    ')
            ->join(
                'pengelola_risiko',
                'pengelola_risiko.id = users.pengelola_id',
                'left'
            )
            ->join(
                'tim_kerja',
                'tim_kerja.id_tim = users.id_tim',
                'left'
            )
            ->where('users.email', $email)
            ->first();

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        $this->setUserSession($user);

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | LOGIN DENGAN SSO (NANTI)
    |--------------------------------------------------------------------------
    */
    public function loginWithSSO(array $ssoData): bool
    {
        // Contoh ssoData:
        // [
        //   'email' => 'pegawai@bps.go.id',
        //   'name'  => 'Nama Pegawai'
        // ]

        $user = $this->userModel->where('email', $ssoData['email'])->first();

        // Jika belum ada → auto create
        if (!$user) {
            $userId = $this->userModel->insert([
                'name'     => $ssoData['name'],
                'email'    => $ssoData['email'],
                'password' => null,
                'role'     => 'operator',
            ]);

            $user = $this->userModel->find($userId);
        }

        $this->setUserSession($user);

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | SET SESSION
    |--------------------------------------------------------------------------
    */
    protected function setUserSession(array $user)
    {
        // Role utama lama tetap dipertahankan untuk kompatibilitas
        $roleId = $user['role_id'] ?? null;

        switch ($roleId) {
            case 1:
                $finalRole = 'admin';
                break;
            case 2:
                $finalRole = 'operator';
                break;
            case 3:
                $finalRole = 'ketua';
                break;
            default:
                $finalRole = 'operator';
                break;
        }

        // Ambil semua role yang dimiliki user dari tabel user_roles
        $db = \Config\Database::connect();

        $roleRows = $db->table('user_roles ur')
            ->select('r.id, r.name')
            ->join('roles r', 'r.id = ur.role_id')
            ->where('ur.user_id', $user['id'])
            ->get()
            ->getResultArray();

        $userRoles = array_column($roleRows, 'name');

        // Fallback ke role lama jika relasi belum tersedia
        if (empty($userRoles)) {
            $userRoles = [$finalRole];
        }

        // Ambil semua tim kerja yang dimiliki user dari tabel user_tim_kerja
        $timRows = $db->table('user_tim_kerja utk')
            ->select('tk.id_tim, tk.nama_tim')
            ->join('tim_kerja tk', 'tk.id_tim = utk.id_tim')
            ->where('utk.user_id', $user['id'])
            ->orderBy('tk.nama_tim', 'ASC')
            ->get()
            ->getResultArray();

        $userTim = array_column($timRows, 'id_tim');

        // Fallback ke id_tim lama jika relasi belum tersedia
        if (empty($userTim) && !empty($user['id_tim'])) {
            $userTim = [$user['id_tim']];
        }

        session()->set([
            'user_id'      => $user['id'],
            'user_name'    => $user['name'],

            // Role lama untuk kompatibilitas kode existing
            'user_role'    => $finalRole,

            // Semua role user untuk fitur multi-role
            'user_roles'   => $userRoles,

            'pengelola_id' => $user['pengelola_id'] ?? null,
            'id_tim'       => $user['id_tim'] ?? null,

            // Semua tim user untuk fitur multi-tim
            'user_tim' => $userTim,

            'nip'          => $user['nip'] ?? '-',
            'jabatan'      => $user['jabatan'] ?? '-',
            'nama_tim'     => $user['nama_tim'] ?? '-',
            'email'        => $user['email'] ?? '-',
            'isLoggedIn'   => true,

            'user' => [
                'id'       => $user['id'],
                'name'     => $user['name'],
                'role'     => $finalRole,
                'roles'    => $userRoles,
                'id_tim'   => $user['id_tim'] ?? null,
                'tim'      => $userTim,
            ]
        ]);
    }

    public function selectRole(string $role): bool
{
    $userId = session('user_id');
    $userRoles = session('user_roles') ?? [];

    if (!$userId || !in_array($role, $userRoles, true)) {
        return false;
    }

    // Set role aktif
    session()->set('user_role', $role);

    // Sinkronkan juga object user lama
    $user = session('user') ?? [];
    $user['role'] = $role;

    session()->set('user', $user);

    return true;
}

    public function logout()
    {
        session()->destroy();
    }
}
