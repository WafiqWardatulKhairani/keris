<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php if ($activeTab === 'ruang_lingkup'): ?>
    <script>
        window.KONTEKS_CONFIG = {
            csrf: {
                name: '<?= csrf_token() ?>',
                token: '<?= csrf_hash() ?>',
            },
            url: {
                createDraft: '<?= site_url('penetapan-konteks/konteks/create-draft') ?>',
                store: '<?= site_url('penetapan-konteks/konteks/store') ?>',
                update: '<?= site_url('penetapan-konteks/konteks/update') ?>',
                delete: '<?= site_url('penetapan-konteks/konteks/delete') ?>',
                setActive: '<?= site_url('penetapan-konteks/konteks/set-active') ?>',
                resetActive: '<?= site_url('penetapan-konteks/konteks/reset-active') ?>',

                show: (id) => `<?= site_url('penetapan-konteks/konteks') ?>/${id}`,

                detail: (id) => `<?= site_url('penetapan-konteks/konteks/detail') ?>/${id}`,
                table: '<?= site_url('penetapan-konteks/konteks/table') ?>',

                getPemilik: '<?= site_url('penetapan-konteks/konteks/get-pemilik-provinsi') ?>',
                getPengelola: '<?= site_url('penetapan-konteks/konteks/get-pengelola-list') ?>',
                getKegiatan: (id) => `<?= site_url('penetapan-konteks/konteks/get-kegiatan') ?>/${id}`,
            }
        };
    </script>
<?php endif; ?>

<?php if ($activeTab === 'konteks'): ?>
    <script>
        window.PROSES_CONFIG = {
            url: {
                store: '<?= site_url('penetapan-konteks/proses-bisnis/store') ?>',
                update: (id) => `<?= site_url('penetapan-konteks/proses-bisnis/update') ?>/${id}`,
                delete: (id) => `<?= site_url('penetapan-konteks/proses-bisnis/delete') ?>/${id}`,
                detail: (id) => `<?= site_url('penetapan-konteks/proses-bisnis/detail') ?>/${id}`,
                table: '<?= site_url('penetapan-konteks/proses-bisnis/ajax-table') ?>',
            }
        };

        window.PEMANGKU_QUICK_CONFIG = {
            store: '<?= site_url('penetapan-konteks/pemangku/store') ?>'
        };

        window.PERATURAN_CONFIG = {
            storeQuick: '<?= site_url('penetapan-konteks/peraturan/store') ?>'
        };

        window.KONTEKS_CONFIG = {
            csrf: {
                name: '<?= csrf_token() ?>',
                token: '<?= csrf_hash() ?>',
            },
            url: {
                store: '<?= site_url('penetapan-konteks/konteks/store') ?>',
                update: '<?= site_url('penetapan-konteks/konteks/update') ?>',
                delete: '<?= site_url('penetapan-konteks/konteks/delete') ?>',

                detail: (id) =>
                    `<?= site_url('penetapan-konteks/konteks/detail') ?>/${id}`,

                table: '<?= site_url('penetapan-konteks/konteks/table') ?>',

                getPemilik: '<?= site_url('penetapan-konteks/konteks/get-pemilik-provinsi') ?>',

                getPengelola: '<?= site_url('penetapan-konteks/konteks/get-pengelola-list') ?>'
            }
        };
    </script>

    <script src="<?= base_url('assets/js/modules/penetapan_konteks/proses_bisnis.js') ?>"></script>
<?php endif; ?>

<?php if ($activeTab === 'pemangku'): ?>
    <script>
        window.PEMANGKU_CONFIG = {
            url: {
                store: '<?= site_url('penetapan-konteks/pemangku/store') ?>',
                update: (id) => `<?= site_url('penetapan-konteks/pemangku/update') ?>/${id}`,
                delete: (id) => `<?= site_url('penetapan-konteks/pemangku/delete') ?>/${id}`,
                detail: (id) => `<?= site_url('penetapan-konteks/pemangku/detail') ?>/${id}`,
                table: '<?= site_url('penetapan-konteks/pemangku/table') ?>',
            }
        };
    </script>
<?php endif; ?>

<?php if ($activeTab === 'peraturan'): ?>
    <script>
        window.PERATURAN_CONFIG = {
            url: {
                store: '<?= site_url('penetapan-konteks/peraturan/store') ?>',
                update: (id) => `<?= site_url('penetapan-konteks/peraturan/update') ?>/${id}`,
                delete: (id) => `<?= site_url('penetapan-konteks/peraturan/delete') ?>/${id}`,
                detail: (id) => `<?= site_url('penetapan-konteks/peraturan/detail') ?>/${id}`,

                storeQuick: '<?= site_url('penetapan-konteks/peraturan/store') ?>',
            }
        };
    </script>
<?php endif; ?>

<?php
$user = session('user');
$canManageContext = in_array(
    $user['role'] ?? '',
    ['admin', 'operator']
);
?>

<div class="pk-page">

    <div class="page-header pk-header mb-3">
        <div class="page-block">
            <div class="row align-items-center">

                <div class="col-12 col-lg-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item">
                                <a>Manajemen Risiko</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a>MR Kegiatan</a>
                            </li>
                            <li class="breadcrumb-item active">
                                Penetapan Konteks
                            </li>
                        </ol>
                    </nav>
                    <h2 class="page-title mb-0">Penetapan Konteks</h2>
                </div>

                <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <?php if (
                        $activeTab === 'ruang_lingkup'
                        &&
                        $canManageContext
                    ): ?>
                        <button
                            class="btn btn-primary"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasRuangLingkup">
                            <i class="ti ti-plus"></i>Ruang Lingkup
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?= view('penetapan_konteks/shared/_tabs', ['activeTab' => $activeTab]) ?>

    <?php if (in_array($activeTab, ['kriteria', 'matriks', 'selera'])): ?>
        <?= view('penetapan_konteks/shared/_subtabs_kriteria', [
            'activeTab' => $activeTab
        ]) ?>
    <?php endif; ?>

    <div class="pk-content-container mt-3">
        <?php if ($activeTab === 'konteks'): ?>
            <?= view('penetapan_konteks/tabs/konteks/konteks_workspace') ?>
        <?php else: ?>
            <?= view('penetapan_konteks/tabs/' . $activeTab . '/content') ?>
        <?php endif; ?>
    </div>
</div>

<?php if ($activeTab === 'ruang_lingkup'): ?>
    <?= view('penetapan_konteks/tabs/ruang_lingkup/_offcanvas_form') ?>
<?php endif; ?>

<?php if ($activeTab === 'konteks'): ?>
    <?= view('penetapan_konteks/tabs/pemangku/_modal_pemangku_create') ?>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.APP_USER = <?= json_encode(session('user')) ?>;
    window.APP_KONTEKS = <?= json_encode($activeKonteks ?? null) ?>;
</script>
<?php if (($activeTab ?? '') === 'konteks'): ?>
    <script>
        window.BASE_URL = <?= json_encode(rtrim(site_url(), '/')) ?>;
    </script>
    <script src="<?= base_url('assets/js/modules/penetapan_konteks/analisis_swot_pestle.js') ?>"></script>
<?php endif; ?>
<script src="<?= base_url('assets/js/modules/penetapan_konteks/' . $activeTab . '.js') ?>"></script>
<?= $this->endSection() ?>