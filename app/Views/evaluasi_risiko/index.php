<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<script>
    window.ER_CONFIG = {
        csrf: {
            name: '<?= csrf_token() ?>',
            token: '<?= csrf_hash() ?>',
        },
        url: {
            store: '<?= site_url('evaluasi-risiko/store') ?>',
            update: (id) => `<?= site_url('evaluasi-risiko/update') ?>/${id}`,
            delete: (id) => `<?= site_url('evaluasi-risiko/delete') ?>/${id}`,
            detail: (id) => `<?= site_url('evaluasi-risiko/detail') ?>/${id}`,
            detailAnalisis: (id) => `<?= site_url('evaluasi-risiko/detail-analisis') ?>/${id}`,
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
                            <li class="breadcrumb-item active">Evaluasi Risiko</li>
                        </ol>
                    </nav>

                    <h2 class="page-title mb-0">
                        Evaluasi Risiko
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <!-- SUMMARY / FILTER -->
    <?= view('evaluasi_risiko/_summary_cards', [
        'totalRisiko' => $totalRisiko ?? 0,
        'totalSudah'  => $totalSudah ?? 0,
        'totalBelum'  => $totalBelum ?? 0,
        'levelRisiko' => $levelRisiko ?? [],
        'filter'      => $filter ?? '',
    ]) ?>

    <!-- TABLE -->
    <?= view('evaluasi_risiko/_table_section', [
        'data'    => $data,
        'total'   => $total ?? 0,
        'from'    => $from ?? 1,
        'to'      => $to ?? count($data),
        'perPage' => $perPage ?? 10,
        'filter'  => $filter ?? '',
        'pager'   => $pager ?? null,
    ]) ?>

    <!-- OFFCANVAS FORM -->
    <?= view('evaluasi_risiko/_offcanvas_form') ?>

</div>

<!-- CSS -->
<link rel="stylesheet" href="<?= base_url('assets/css/evaluasi-risiko.css') ?>">

<!-- JS MODULE -->
<script src="<?= base_url('assets/js/modules/evaluasi_risiko/evaluasi.js') ?>"></script>

<?= $this->endSection() ?>