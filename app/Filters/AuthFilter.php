<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = $session->get('user_id');

        if (!$userId) {
            return;
        }

        $db = \Config\Database::connect();

        // ==============================
        // REFRESH ROLE USER
        // ==============================
        $roleRows = $db->table('user_roles ur')
            ->select('r.name')
            ->join('roles r', 'r.id = ur.role_id')
            ->where('ur.user_id', $userId)
            ->get()
            ->getResultArray();

        $userRoles = array_column($roleRows, 'name');
        // Fallback untuk akun lama yang belum memiliki relasi user_roles
        if (empty($userRoles)) {
            $legacyRoleId = $db->table('users')
                ->select('role_id')
                ->where('id', $userId)
                ->get()
                ->getRow('role_id');

            if ($legacyRoleId) {
                $legacyRole = $db->table('roles')
                    ->select('name')
                    ->where('id', $legacyRoleId)
                    ->get()
                    ->getRow('name');

                if ($legacyRole) {
                    $userRoles = [$legacyRole];
                }
            }
        }

        // ==============================
        // REFRESH TIM KERJA USER
        // ==============================
        $timRows = $db->table('user_tim_kerja')
            ->select('id_tim')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();

        $userTim = array_map(
            'intval',
            array_column($timRows, 'id_tim')
        );

        // Update daftar akses di session
        $session->set('user_roles', $userRoles);
        $session->set('user_tim', $userTim);

        // Update nested session user
        $user = $session->get('user') ?? [];

        $user['roles'] = $userRoles;
        $user['tim']   = $userTim;

        $session->set('user', $user);
        // =====================================
        // VALIDASI ROLE AKTIF
        // =====================================
        $activeRole = $session->get('user_role');

        if (!$activeRole || !in_array($activeRole, $userRoles, true)) {
            $session->remove('user_role');

            return redirect()->to('/select-role');
        }

        // =====================================
        // AKSES KHUSUS PIMPINAN
        // =====================================
        if ($activeRole === 'pimpinan') {
    // Ambil path URL
    $path = trim($request->getUri()->getPath(), '/');

    // Sesuaikan dengan base URL aplikasi
    // Agar bekerja di lokal dan server /keris/
    $basePath = trim(parse_url(base_url(), PHP_URL_PATH) ?? '', '/');

    if (
        $basePath !== '' &&
        ($path === $basePath || str_starts_with($path, $basePath . '/'))
    ) {
        $path = trim(substr($path, strlen($basePath)), '/');
    }

    // Halaman yang boleh diakses Pimpinan
    $allowedPaths = [
        '',
        'dashboard',
        'dashboard/data',
        'dashboard/risk-detail',
    ];

    // Tolak akses ke halaman lainnya
    if (!in_array($path, $allowedPaths, true)) {
        return service('response')
            ->setStatusCode(403)
            ->setBody('403 Forbidden - Anda tidak memiliki akses ke halaman ini.');
    }
}
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // nothing
    }
}
