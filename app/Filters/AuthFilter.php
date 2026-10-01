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
}

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // nothing
    }
}
