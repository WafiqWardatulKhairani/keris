<?php

function hasPermission($name)
{
    $session = session();
    $user = $session->get('user');

    if (!$user || empty($user['role'])) {
        return false;
    }

    // Role aktif yang dipilih user saat login
    $roleName = $user['role'];

    static $cache = [];

    if (!isset($cache[$roleName])) {
        $db = \Config\Database::connect();

        $role = $db->table('roles')
            ->select('id')
            ->where('name', $roleName)
            ->get()
            ->getRowArray();

        if (!$role) {
            $cache[$roleName] = [];
            return false;
        }

        $rows = $db->table('role_permissions rp')
            ->select('p.name')
            ->join('permissions p', 'p.id = rp.permission_id')
            ->where('rp.role_id', $role['id'])
            ->get()
            ->getResultArray();

        $cache[$roleName] = array_column($rows, 'name');
    }

    return in_array($name, $cache[$roleName], true);
}

function can($p)
{
    return hasPermission($p);
}