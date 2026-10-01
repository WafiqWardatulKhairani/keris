<?php

namespace App\Controllers;

use App\Services\AuthService;

class AuthController extends BaseController
{
    protected $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function login()
    {
        return view('auth/login');
    }

    public function attemptLogin()
{
    $email    = $this->request->getPost('email');
    $password = $this->request->getPost('password');

    // Autentikasi user terlebih dahulu
    if (!$this->authService->loginWithPassword($email, $password)) {
        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'Email atau password salah');
    }

    // Setelah user terautentikasi, ambil semua role miliknya
    $roles = session('user_roles') ?? [];

    // Kalau tidak punya role
    if (empty($roles)) {
        $this->authService->logout();

        return redirect()
            ->to('/login')
            ->with('error', 'Akun tidak memiliki role');
    }

    // Kalau hanya punya satu role, langsung gunakan role tersebut
    if (count($roles) === 1) {
        $this->authService->selectRole($roles[0]);

        return redirect()->to('/dashboard');
    }

    // Kalau punya lebih dari satu role, pilih role dulu
    return redirect()->to('/select-role');
}

    public function selectRole()
{
    $roles = session('user_roles') ?? [];

    if (empty($roles)) {
        return redirect()->to('/login');
    }

    return view('auth/select_role', [
        'roles' => $roles,
        'userName' => session('user_name'),
    ]);
}

public function setRole()
{
    $role = $this->request->getPost('role');

    if (!$role || !$this->authService->selectRole($role)) {
        return redirect()
            ->to('/select-role')
            ->with('error', 'Role tidak valid');
    }

    return redirect()->to('/dashboard');
}

    public function logout()
    {
        $this->authService->logout();
        return redirect()->to('/login');
    }
}
