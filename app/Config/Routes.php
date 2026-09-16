<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Auth
$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::attemptLogin');
$routes->get('logout', 'AuthController::logout');

// Dashboard
$routes->group('', ['filter' => ['auth']], function ($routes) {
    $routes->get('/','DashboardController::index',['filter' => 'role:admin,operator,ketua']);
    $routes->get('dashboard','DashboardController::index',['filter' => 'role:admin,operator,ketua']);
    $routes->get('dashboard/data','DashboardController::data',['filter' => 'role:admin,operator,ketua']);
    $routes->get('dashboard/debug', 'DashboardController::debug', ['filter' => 'role:admin,operator,ketua']);
});

// Global Context
$routes->group('global-context', ['filter' => ['auth']], function ($routes) {
    $routes->post('set','GlobalContextController::set',['filter' => 'role:admin,operator,ketua']);
    $routes->get('kegiatan','GlobalContextController::getKegiatanByTim',['filter' => 'role:admin,operator,ketua']);
    $routes->post('reset','GlobalContextController::reset',['filter' => 'role:admin,operator,ketua']);
});

// Manajemen User (admin)
$routes->group('manajemen-user', ['filter' => ['auth', 'ronle:admin']], function ($routes) {
    $routes->get('/', 'UserController::index');
    $routes->post('store', 'UserController::store');
    $routes->post('update/(:num)', 'UserController::update/$1');
    $routes->post('delete/(:num)', 'UserController::delete/$1');
    $routes->get('table', 'UserController::table');
    $routes->get('detail/(:num)', 'UserController::detail/$1');
    $routes->get('roles', 'UserController::roles');
    $routes->get('tim-kerja', 'UserController::timKerja');
});

// RBAC (Role & Permission)
$routes->group('rbac', ['filter' => ['auth', 'role:admin']], function ($routes) {
    $routes->get('role', 'RBAC\RoleController::index');
    $routes->post('role/store', 'RBAC\RoleController::store');
    $routes->post('role/update/(:num)', 'RBAC\RoleController::update/$1');
    $routes->post('role/delete/(:num)', 'RBAC\RoleController::delete/$1');
    $routes->get('role/permissions/(:num)', 'RBAC\RoleController::permissions/$1');
    $routes->post('role/update-permissions/(:num)', 'RBAC\RoleController::updatePermissions/$1');
    $routes->get('unauthorized', 'RBAC\ErrorController::unauthorized');
    $routes->get('permission', 'RBAC\PermissionController::index');
    $routes->post('permission/store', 'RBAC\PermissionController::store');
    $routes->post('permission/update/(:num)', 'RBAC\PermissionController::update/$1');
    $routes->post('permission/delete/(:num)', 'RBAC\PermissionController::delete/$1');
});

// Bank Risiko
$routes->group('bank-risiko', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'BankRisikoController::index');
    $routes->get('table', 'BankRisikoController::ajaxTable');
    $routes->get('list', 'BankRisikoController::list');

    $routes->group('', function ($routes) {
        $routes->post('store', 'BankRisikoController::store');
        $routes->post('update/(:num)', 'BankRisikoController::update/$1');
        $routes->post('delete/(:num)', 'BankRisikoController::delete/$1');
    });
});

// MR Instansi
$routes->group('mr-instansi', ['filter' => ['auth']], function ($routes) {
    $routes->get('/', 'MrInstansiController::index');
    $routes->get('data', 'MrInstansiController::getData');
    $routes->post('sync', 'MrInstansiController::sync');
});

// Master Data
$routes->group('master', ['filter' => ['auth']], function ($routes) {
    // Tim Kerja
    $routes->get('tim-kerja', 'Master\TimKerjaController::index');
    $routes->get('tim-kerja/table', 'Master\TimKerjaController::table');
    $routes->post('tim-kerja/store', 'Master\TimKerjaController::store');
    $routes->post('tim-kerja/update/(:num)', 'Master\TimKerjaController::update/$1');
    $routes->post('tim-kerja/delete/(:num)', 'Master\TimKerjaController::delete/$1');
    $routes->get('tim-kerja/detail/(:num)','Master\TimKerjaController::detail/$1');

    // Kegiatan
    $routes->get('kegiatan', 'Master\KegiatanController::index');
    $routes->get('kegiatan/table', 'Master\KegiatanController::table');
    $routes->post('kegiatan/store', 'Master\KegiatanController::store');
    $routes->post('kegiatan/update/(:num)', 'Master\KegiatanController::update/$1');
    $routes->post('kegiatan/delete/(:num)', 'Master\KegiatanController::delete/$1');

    // Sasaran Strategis
    $routes->get('sasaran-strategis', 'Master\SasaranStrategisController::index');
    $routes->get('sasaran-strategis/table', 'Master\SasaranStrategisController::table');
    $routes->post('sasaran-strategis/store', 'Master\SasaranStrategisController::store');
    $routes->post('sasaran-strategis/update/(:num)', 'Master\SasaranStrategisController::update/$1');
    $routes->post('sasaran-strategis/delete/(:num)', 'Master\SasaranStrategisController::delete/$1');

    // Pengelola Risiko
    $routes->get('pengelola-risiko', 'Master\PengelolaRisikoController::index');
    $routes->get('pengelola-risiko/table', 'Master\PengelolaRisikoController::table');
    $routes->post('pengelola-risiko/store', 'Master\PengelolaRisikoController::store');
    $routes->post('pengelola-risiko/update/(:num)', 'Master\PengelolaRisikoController::update/$1');
    $routes->post('pengelola-risiko/delete/(:num)', 'Master\PengelolaRisikoController::delete/$1');
    $routes->get('pengelola-risiko/wilayah', 'Master\PengelolaRisikoController::wilayah');
    $routes->get('wilayah/table', 'Master\PengelolaRisikoController::wilayahTable');
    $routes->get('pengelola-risiko/active-table','Master\PengelolaRisikoController::activeTable');

    // Penugasan Tim
    $routes->get('penugasan-tim', 'Master\PenugasanTimController::index');
    $routes->get('penugasan-tim/table', 'Master\PenugasanTimController::table');
    $routes->get('pengelola/table', 'Master\PengelolaController::table');
    $routes->post('penugasan-tim/store', 'Master\PenugasanTimController::store');
    $routes->post('penugasan-tim/update/(:num)','Master\PenugasanTimController::update/$1');
    $routes->post('penugasan-tim/delete/(:num)', 'Master\PenugasanTimController::delete/$1');

    // Bank Risiko
    $routes->get('bank-risiko', 'Master\BankRisikoController::index');
    $routes->get('bank-risiko/table', 'Master\BankRisikoController::table');
    $routes->post('bank-risiko/store', 'Master\BankRisikoController::store');
    $routes->post('bank-risiko/update/(:num)', 'Master\BankRisikoController::update/$1');
    $routes->post('bank-risiko/delete/(:num)', 'Master\BankRisikoController::delete/$1');
    $routes->post('bank-risiko/approve/(:num)', 'Master\BankRisikoController::approve/$1');
    $routes->post('bank-risiko/reject/(:num)', 'Master\BankRisikoController::reject/$1');
});

// Penetapan Konteks
$routes->group('penetapan-konteks', ['namespace' => 'App\Controllers\PenetapanKonteks', 'filter' => ['auth']
], function ($routes) {
    $routes->get('/', 'KonteksController::index');
    $routes->get('konteks','KonteksController::redirectToActive',['filter' => 'role:admin,operator,ketua']);
    $routes->get('konteks/(:num)','KonteksController::show/$1',['filter' => 'role:admin,operator,ketua']);
    $routes->post('konteks/create-draft', 'KonteksController::createDraft', ['filter' => 'role:admin,operator']);
    //$routes->get('konteks/(:num)', 'KonteksController::index/$1', ['filter' => 'role:admin,operator,ketua']);

    //$routes->get('penetapan-konteks/konteks/(:num)/edit','PenetapanKonteks\KonteksController::edit/$1');
    $routes->get('konteks/(:num)/edit','KonteksController::edit/$1',['filter' => 'role:admin,operator']);


    $routes->post('konteks/store', 'KonteksController::store', ['filter' => 'role:admin,operator']);
    $routes->post('konteks/update', 'KonteksController::update', ['filter' => 'role:admin,operator']);
    $routes->post('konteks/delete', 'KonteksController::delete', ['filter' => 'role:admin,operator']);
    $routes->get('konteks/detail/(:num)', 'KonteksController::detail/$1');

    $routes->get('konteks/table', 'KonteksController::ajaxTable');
    $routes->get('konteks/get-pemilik-provinsi', 'KonteksController::getPemilikProvinsi');
    $routes->get('konteks/get-pengelola-list', 'KonteksController::getPengelolaList');
    $routes->get('konteks/get-kegiatan/(:num)', 'KonteksController::getKegiatanByTim/$1');

    // Proses Bisnis
    $routes->get('proses-bisnis', 'ProsesBisnisController::index', ['filter' => 'role:admin,operator,ketua']);
    $routes->post('proses-bisnis/store','ProsesBisnisController::store',['filter' => 'role:admin,operator']);
    $routes->post('proses-bisnis/update/(:num)','ProsesBisnisController::update/$1',['filter' => 'role:admin,operator']);
    $routes->post('proses-bisnis/delete/(:num)','ProsesBisnisController::delete/$1',['filter' => 'role:admin,operator']);
    $routes->get('proses-bisnis/detail/(:num)','ProsesBisnisController::detail/$1',['filter' => 'role:admin,operator,ketua']);
    $routes->get('proses-bisnis/ajax-table', 'ProsesBisnisController::ajaxTable');

    // Sasaran Kinerja
    $routes->get('sasaran-kinerja', 'SasaranKinerjaController::index', ['filter' => 'role:admin,operator,ketua']);
    $routes->post('sasaran-kinerja/store', 'SasaranKinerjaController::store', ['filter' => 'role:admin,operator']);
    $routes->post('sasaran-kinerja/update/(:num)', 'SasaranKinerjaController::update/$1', ['filter' => 'role:admin,operator']);
    $routes->post('sasaran-kinerja/delete/(:num)', 'SasaranKinerjaController::delete/$1', ['filter' => 'role:admin,operator']);
    $routes->get('sasaran-kinerja/detail/(:num)', 'SasaranKinerjaController::detail/$1');
    $routes->get('sasaran-kinerja/table', 'SasaranKinerjaController::ajaxTable');

    $routes->get('pemangku', 'PemangkuController::index');
    $routes->get('pemangku/table', 'PemangkuController::ajaxTable');
    $routes->get('pemangku/detail/(:num)', 'PemangkuController::detail/$1');
    $routes->post('pemangku/store', 'PemangkuController::store');
    $routes->post('pemangku/update/(:num)', 'PemangkuController::update/$1');
    $routes->post('pemangku/delete/(:num)', 'PemangkuController::delete/$1');

    $routes->get('peraturan', 'PeraturanController::index');
    $routes->post('peraturan/store', 'PeraturanController::store');
    $routes->post('peraturan/update/(:num)', 'PeraturanController::update/$1');
    $routes->post('peraturan/delete/(:num)', 'PeraturanController::delete/$1');
    $routes->get('peraturan/detail/(:num)', 'PeraturanController::detail/$1');

    $routes->get('kriteria', 'MasterDataController::kriteria');
    $routes->get('matriks', 'MasterDataController::matriks');
    $routes->get('selera', 'MasterDataController::selera');
    $routes->get('sasaran-strategis', 'MasterDataController::sasaranStrategis');
});

// Identifikasi
$routes->group('identifikasi-risiko', ['namespace' => 'App\Controllers','filter' => ['auth']], function ($routes) {
    $routes->get('/', 'IdentifikasiRisikoController::index', ['filter' => 'role:admin,operator,ketua']);
    $routes->get('detail/(:num)', 'IdentifikasiRisikoController::detail/$1', ['filter' => 'role:admin,operator,ketua']);
    $routes->get('detail-area/(:num)', 'IdentifikasiRisikoController::detailArea/$1', ['filter' => 'role:admin,operator,ketua']);
    $routes->get('table', 'IdentifikasiRisikoController::ajaxTable');
    $routes->get('bank-risiko', 'IdentifikasiRisikoController::getBankRisiko');
    $routes->post('set-active', 'IdentifikasiRisikoController::setActive', ['filter' => 'role:admin,operator,ketua']);
    $routes->post('reset-active', 'IdentifikasiRisikoController::resetActive', ['filter' => 'role:admin,operator,ketua']);
    $routes->group('', ['filter' => 'role:admin,operator'], function ($routes) {
        $routes->post('store', 'IdentifikasiRisikoController::store');
        $routes->post('update/(:num)', 'IdentifikasiRisikoController::update/$1');
        $routes->post('delete/(:num)', 'IdentifikasiRisikoController::delete/$1');
        $routes->post('request-bank-risiko', 'IdentifikasiRisikoController::requestBankRisiko');
    });
});

// Analisis
$routes->group('analisis-risiko', ['namespace' => 'App\Controllers', 'filter' => ['auth']], function ($routes) {

    $routes->get('/', 'AnalisisRisikoController::index', ['filter' => 'role:admin,operator,ketua']);

    $routes->get('detail/(:num)', 'AnalisisRisikoController::detail/$1', ['filter' => 'role:admin,operator,ketua']);
    $routes->get('detail-identifikasi/(:num)', 'AnalisisRisikoController::detailIdentifikasi/$1', ['filter' => 'role:admin,operator,ketua']);

    $routes->get('table', 'AnalisisRisikoController::ajaxTable');

    $routes->post('set-active', 'AnalisisRisikoController::setActive', ['filter' => 'role:admin,operator,ketua']);
    $routes->post('reset-active', 'AnalisisRisikoController::resetActive', ['filter' => 'role:admin,operator,ketua']);

    $routes->group('', ['filter' => 'role:admin,operator'], function ($routes) {
        $routes->post('store', 'AnalisisRisikoController::store');
        $routes->post('update/(:num)', 'AnalisisRisikoController::update/$1');
        $routes->post('delete/(:num)', 'AnalisisRisikoController::delete/$1');
        $routes->post('preview', 'AnalisisRisikoController::preview');
    });
});

// Evaluasi
$routes->group('evaluasi-risiko', ['namespace' => 'App\Controllers', 'filter' => ['auth']], function ($routes) {

    $routes->get('/', 'EvaluasiRisikoController::index', ['filter' => 'role:admin,operator,ketua']);

    $routes->get('detail/(:num)', 'EvaluasiRisikoController::detail/$1', ['filter' => 'role:admin,operator,ketua']);
    $routes->get('detail-analisis/(:num)', 'EvaluasiRisikoController::detailAnalisis/$1', ['filter' => 'role:admin,operator,ketua']);

    $routes->get('table', 'EvaluasiRisikoController::ajaxTable');

    $routes->post('set-active', 'EvaluasiRisikoController::setActive', ['filter' => 'role:admin,operator,ketua']);
    $routes->post('reset-active', 'EvaluasiRisikoController::resetActive', ['filter' => 'role:admin,operator,ketua']);

    $routes->group('', ['filter' => 'role:admin,operator'], function ($routes) {
        $routes->post('store', 'EvaluasiRisikoController::store');
        $routes->post('update/(:num)', 'EvaluasiRisikoController::update/$1');
        $routes->post('delete/(:num)', 'EvaluasiRisikoController::delete/$1');
    });
});

// Rencana Penanganan
$routes->group('rencana-penanganan', ['namespace' => 'App\Controllers', 'filter' => ['auth']], function ($routes) {

    $routes->get('/', 'RencanaPenangananController::index', ['filter' => 'role:admin,operator,ketua']);

    $routes->get('detail/(:num)', 'RencanaPenangananController::detail/$1', ['filter' => 'role:admin,operator,ketua']);
    $routes->get('detail-evaluasi/(:num)', 'RencanaPenangananController::detailEvaluasi/$1', ['filter' => 'role:admin,operator,ketua']);

    // WAJIB biar sama seperti Analisis & Evaluasi
    $routes->get('table', 'RencanaPenangananController::ajaxTable');
                            
    $routes->post('set-active', 'RencanaPenangananController::setActive', ['filter' => 'role:admin,operator,ketua']);
    $routes->post('reset-active', 'RencanaPenangananController::resetActive', ['filter' => 'role:admin,operator,ketua']);

    // WRITE ACCESS
    $routes->group('', ['filter' => 'role:admin,operator'], function ($routes) {
        $routes->post('store', 'RencanaPenangananController::store');
        $routes->post('update/(:num)', 'RencanaPenangananController::update/$1');
        $routes->post('delete/(:num)', 'RencanaPenangananController::delete/$1');
        $routes->post('preview', 'RencanaPenangananController::preview');
    });
});

// Pemantauan
$routes->group('pemantauan-risiko', ['filter' => ['auth']], function ($routes) {

    // READ ACCESS (semua role terkait)
    $routes->get('/', 'PemantauanRisikoController::index', ['filter' => 'role:admin,operator,ketua']);
    $routes->get('detail/(:num)', 'PemantauanRisikoController::detail/$1', ['filter' => 'role:admin,operator,ketua']);

    $routes->post('set-active', 'PemantauanRisikoController::setActive', ['filter' => 'role:admin,operator,ketua']);
    $routes->post('reset-active', 'PemantauanRisikoController::resetActive', ['filter' => 'role:admin,operator,ketua']);
    $routes->get('table', 'PemantauanRisikoController::ajaxTable');

    // WRITE ACCESS (terbatas)
    $routes->group('', ['filter' => 'role:admin,operator'], function ($routes) {
        $routes->post('store', 'PemantauanRisikoController::store');
        $routes->post('delete/(:num)', 'PemantauanRisikoController::delete/$1');

        $routes->delete('bukti/(:num)', 'PemantauanRisikoController::deleteBukti/$1');
    });

    // OPTIONAL (view file tetap boleh semua)
    $routes->get('bukti/view/(:num)', 'PemantauanRisikoController::viewBukti/$1', ['filter' => 'role:admin,operator,ketua']);
    $routes->get('bukti/download/(:num)', 'PemantauanRisikoController::downloadBukti/$1', ['filter' => 'role:admin,operator,ketua']);
});

// Pelaporan Risiko
$routes->group('pelaporan-risiko', ['filter' => ['auth']], function ($routes) {

    // READ ACCESS
    $routes->get('/', 'PelaporanRisikoController::index', ['filter' => 'role:admin,operator,ketua']);
    $routes->get('detail/(:num)', 'PelaporanRisikoController::detail/$1', ['filter' => 'role:admin,operator,ketua']);
    $routes->get('export', 'PelaporanRisikoController::export', ['filter' => 'role:admin,operator,ketua']);
    $routes->get('print', 'PelaporanRisikoController::print', ['filter' => 'role:admin,operator,ketua']);

    // OPERATOR
    $routes->group('', ['filter' => 'role:operator'], function ($routes) {

        $routes->post('set-active', 'PelaporanRisikoController::setActive');
        $routes->post('set-periode', 'PelaporanRisikoController::setPeriode');
        $routes->post('ajukan', 'PelaporanRisikoController::ajukan');
        $routes->post('batal-ajukan', 'PelaporanRisikoController::batalAjukan');
    });

    // KETUA
    $routes->group('', ['filter' => 'role:ketua'], function ($routes) {
        $routes->post('approve-kegiatan/(:num)','PelaporanRisikoController::approveKegiatan/$1');
        $routes->post('reject-kegiatan/(:num)','PelaporanRisikoController::rejectKegiatan/$1');
    });
});
