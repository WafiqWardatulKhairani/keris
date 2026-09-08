<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<script>
    window.PR_CONFIG = {
        csrf: {
            name: '<?= csrf_token() ?>',
            token: '<?= csrf_hash() ?>',
        },
        url: {
            store: '<?= site_url('pemantauan-risiko/store') ?>',
            detail: (id) => `<?= site_url('pemantauan-risiko/detail') ?>/${id}`,
            delete: (id) => `<?= site_url('pemantauan-risiko/delete') ?>/${id}`,
            deleteBukti: (id) => `<?= site_url('pemantauan-risiko/bukti') ?>/${id}`,
            viewBukti: (id) => `<?= site_url('pemantauan-risiko/bukti/view') ?>/${id}`,
            downloadBukti: (id) => `<?= site_url('pemantauan-risiko/bukti/download') ?>/${id}`,
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
                            <li class="breadcrumb-item active">Pemantauan Risiko</li>
                        </ol>
                    </nav>
                    <h2 class="page-title mb-0">Pemantauan Risiko</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <?= view('pemantauan_risiko/_summary_cards', [
        'totalRtp'      => $totalRtp,
        'distribusi'    => $distribusi,
        'filter'        => $filter ?? '',
    ]) ?>

    <?php if ($filter && $filter !== 'semua'): ?>
        <div class="mb-3 d-flex align-items-center gap-2">
            <span class="text-muted small">Menampilkan:</span>

            <span class="badge bg-primary-subtle text-primary border border-primary">
                <?= esc($filter) ?>
            </span>

            <a href="<?= site_url('pemantauan-risiko') ?>"
                class="small text-decoration-none text-danger ms-2">
                ✕ Clear Filter
            </a>
        </div>
    <?php endif; ?>

    <!-- TABLE -->
    <?= view('pemantauan_risiko/_table_section', [
        'grouped' => $grouped,
        'total'         => $total ?? 0,
        'from'          => $from ?? 0,
        'to'            => $to ?? 0,
        'perPage'       => $perPage ?? 10,
        'filter'        => $filter ?? '',
        'pager'         => $pager ?? null,
    ]) ?>

    <!-- Offcanvas Form -->
    <?= view('pemantauan_risiko/_offcanvas_form', [        
    ]) ?>

</div>

<!-- CSS -->
<link rel="stylesheet" href="<?= base_url('assets/css/pemantauan-risiko.css') ?>">

<!-- JS -->
<script src="<?= base_url('assets/js/modules/pemantauan_risiko/pemantauan.js') ?>"></script>

<script>
    // simpan posisi scroll
    document.querySelectorAll('.pr-stat-link').forEach(el => {
        el.addEventListener('click', function() {
            sessionStorage.setItem('prScrollY', window.scrollY);
        });
    });

    // restore scroll
    window.addEventListener('load', function() {
        const y = sessionStorage.getItem('prScrollY');
        if (y !== null) {
            window.scrollTo(0, parseInt(y));
            sessionStorage.removeItem('prScrollY');
        }
    }); 
</script>

<?= $this->endSection() ?>