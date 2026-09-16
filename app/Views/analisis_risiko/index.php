<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<script>
    window.AR_CONFIG = {
        csrf: {
            name: '<?= csrf_token() ?>',
            token: '<?= csrf_hash() ?>',
        },
        url: {
            store: '<?= site_url('analisis-risiko/store') ?>',
            update: (id) => `<?= site_url('analisis-risiko/update') ?>/${id}`,
            detail: (id) => `<?= site_url('analisis-risiko/detail') ?>/${id}`,
            detailIdentifikasi: (id) => `<?= site_url('analisis-risiko/detail-identifikasi') ?>/${id}`,
            preview: '<?= site_url('analisis-risiko/preview') ?>',
            delete: (id) => `<?= site_url('analisis-risiko/delete') ?>/${id}`,
        }
    };
</script>
<script>
    window.APP_USER = {
        role: '<?= session()->get('user_role') ?>',
        id_tim: '<?= session()->get('id_tim') ?>'
    };
</script>

<div class="pk-page">

    <!-- PAGE HEADER -->
    <div class="page-header pk-header mb-3">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-12 col-lg-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item">Manajemen Risiko</li>
                            <li class="breadcrumb-item active">Analisis Risiko</li>
                        </ol>
                    </nav>
                    <h2 class="page-title mb-0">Analisis Risiko</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards (SELALU MUNCUL) -->
    <?= view('analisis_risiko/_summary_cards', [
        'totalRisiko'   => $totalRisiko,
        'totalSudah'    => $totalSudah,
        'totalBelum'    => $totalBelum,
        'levelRisiko'   => $levelRisiko,
        'filter'        => $filter,
    ]) ?>

    <!-- Table tetap muncul -->
    <?= view('analisis_risiko/_table_section', [
        'data'          => $data,
        'total'         => $total ?? 0,
        'from'          => $from ?? 1,
        'to'            => $to ?? count($data),
        'perPage'       => $perPage ?? 10,
        'filter'        => $filter ?? '',
        'pager'         => $pager ?? null,
    ]) ?>

    <!-- Offcanvas Form -->
    <?= view('analisis_risiko/_offcanvas_form', [
        'kemungkinanList' => $kemungkinanList,
        'dampakList'      => $dampakList,
    ]) ?>

</div>

<!-- CSS -->
<link rel="stylesheet" href="<?= base_url('assets/css/analisis-risiko.css') ?>">

<!-- JS Modules -->
<script src="<?= base_url('assets/js/modules/analisis_risiko/analisis.js') ?>"></script>

<?= $this->endSection() ?>