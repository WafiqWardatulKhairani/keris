<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<script>
    window.MU_CONFIG = {
        url: {
            table: '<?= site_url('manajemen-user/table') ?>',
            detail: '<?= site_url('manajemen-user/detail') ?>',
            store: '<?= site_url('manajemen-user/store') ?>',
            update: '<?= site_url('manajemen-user/update') ?>',
            delete: '<?= site_url('manajemen-user/delete') ?>',
            roles: '<?= site_url('manajemen-user/roles') ?>',
            timKerja: '<?= site_url('manajemen-user/tim-kerja') ?>'
        }
    }
</script>

<div class="mu-page">

    <div class="page-header pk-header mb-3">
        <div class="page-block">
            <div class="row align-items-center">

                <div class="col-lg-8">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item">Administrasi</li>
                        <li class="breadcrumb-item active">Manajemen User</li>
                    </ol>

                    <h2 class="page-title mb-0">
                        Manajemen User 
                    </h2>
                </div>

                <div class="col-lg-4 text-lg-end">
                    <button id="btnTambahUser"
                        class="btn btn-primary">
                        <i class="ti ti-plus"></i>
                        User
                    </button>
                </div>

            </div>
        </div>
    </div>

    <?= view('user/_table_section') ?>
    <?= view('user/_offcanvas_form') ?>

</div>

<script src="<?= base_url('assets/js/modules/master/manajemen-user.js') ?>"></script>

<?= $this->endSection() ?>