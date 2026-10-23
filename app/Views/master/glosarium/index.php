
<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<script>
    window.GLOSARIUM_CONFIG = {
        role: '<?= esc(session()->get('user_role'), 'js') ?>',
        url: {
            table: '<?= site_url('glosarium/table') ?>',
            store: '<?= site_url('glosarium/store') ?>',
            update: (id) => `<?= site_url('glosarium/update') ?>/${id}`,
            delete: (id) => `<?= site_url('glosarium/delete') ?>/${id}`
        }
    };
</script>

<div class="pk-page">

    <div class="page-header pk-header mb-3">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-12 col-lg-8">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item">Referensi</li>
                        <li class="breadcrumb-item active">Glosarium</li>
                    </ol>

                    <h2 class="page-title mb-0">Glosarium</h2>
                    <small class="text-muted">
                        Kamus istilah dan definisi manajemen risiko
                    </small>
                </div>

                <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <?php if (session()->get('user_role') === 'admin'): ?>
                        <button class="btn btn-primary" id="btnTambahGlosarium">
                            <i class="ti ti-plus me-1"></i> Tambah Istilah
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?= view('master/glosarium/_table_section') ?>
    <?= view('master/glosarium/_offcanvas_form') ?>

</div>

<script src="<?= base_url('assets/js/modules/master/glosarium.js') ?>"></script>

<?= $this->endSection() ?>
