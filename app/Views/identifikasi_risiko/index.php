<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="pk-page">

    <!-- PAGE HEADER -->
    <div class="page-header pk-header mb-3">
        <div class="page-block">
            <div class="row align-items-center">

                <div class="col-12 col-lg-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item">Manajemen Risiko</li>
                            <li class="breadcrumb-item active">Identifikasi Risiko</li>
                        </ol>
                    </nav>
                    <h2 class="page-title mb-0">Identifikasi Risiko</h2>
                </div>

                <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <?php
                    $user = session('user');
                    $role = $user['role'] ?? '';

                    $hasFilter =
                        session('global_id_tim') ||
                        session('global_id_kegiatan') ||
                        session('global_tahun');
                    ?>

                    <?php if ($role !== 'ketua'): ?>
                        <button id="irBtnAdd" class="btn btn-primary"
                            <?= !$hasFilter ? 'disabled' : '' ?>>
                            <i class="ti ti-plus"></i> Risiko
                        </button>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <!-- Content: filter + table -->
    <?= view('identifikasi_risiko/content', [
        'data'           => $data, 
        'pager'          => $pager,
        'activeKonteks'  => $activeKonteks,
        'kategoriList'   => $kategoriList,
        'filterKategori' => $filterKategori,
    ]) ?>

</div>

<!-- Offcanvas Form -->
<?= view('identifikasi_risiko/_offcanvas_form', [
    'listKonteksProses' => $listKonteksProses,
    'activeKonteks'     => $activeKonteks,
    'areaDampakList'    => $areaDampakList,
    'kategoriList'      => $kategoriList,
]) ?>

<!-- JS Modules -->
<script src="<?= base_url('assets/js/modules/identifikasi_risiko/risiko.js') ?>"></script>
<script>
    window.APP_USER = <?= json_encode(session('user')) ?>;
    window.APP_KONTEKS = <?= json_encode($activeKonteks ?? null) ?>;
</script>
<?= $this->endSection() ?>