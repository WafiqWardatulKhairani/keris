<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="pk-page">

    <div class="page-header pk-header mb-3"> 
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-12 col-lg-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item">Manajemen Risiko</li>
                            <li class="breadcrumb-item active">Pelaporan Risiko</li>
                        </ol>
                    </nav>
                    <h2 class="page-title mb-0">Pelaporan Risiko</h2>
                </div>
                <div class="col-12 col-lg-4 text-lg-end mt-2 mt-lg-0">
                    <div class="dropdown d-inline-block">
                        <button
                            class="btn btn-sm btn-outline-primary dropdown-toggle"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">

                            <i class="ti ti-download me-1"></i>
                            Export
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end">

                            <li>
                                <button
                                    type="button"
                                    class="dropdown-item"
                                    onclick="plPrintReport('form1')">

                                    <i class="ti ti-file-text me-2"></i>
                                    Export Form 1
                                </button>
                            </li>

                            <li>
                                <button
                                    type="button"
                                    class="dropdown-item"
                                    onclick="plPrintReport('form2')">

                                    <i class="ti ti-file-text me-2"></i>
                                    Export Form 2
                                </button>
                            </li>

                            <li>
                                <button
                                    type="button"
                                    class="dropdown-item"
                                    onclick="plPrintReport('form3')">

                                    <i class="ti ti-file-text me-2"></i>
                                    Export Form 3
                                </button>
                            </li>

                            <li>
                                <button
                                    type="button"
                                    class="dropdown-item"
                                    onclick="plPrintReport('form4')">

                                    <i class="ti ti-file-text me-2"></i>
                                    Export Form 4
                                </button>
                            </li>

                            <!-- <li>
                                <button
                                    type="button"
                                    class="dropdown-item"
                                    onclick="plPrintReport('all')">

                                    <i class="ti ti-stack-2 me-2"></i>
                                    Export Semua Form
                                </button>
                            </li> -->

                            <!-- <li>
                                <hr class="dropdown-divider">
                            </li>

                            <li>
                                <button
                                    type="button"
                                    class="dropdown-item"
                                    onclick="plExportExcel()">

                                    <i class="ti ti-file-export me-2"></i>
                                    Export Excel
                                </button>
                            </li> -->

                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <?= view('pelaporan_risiko/_context_selector', [
        'listKonteks' => $listKonteks,
        'periode'     => $periode,
        'userRole'    => $userRole,
        'ketuaInfo'   => $ketuaInfo ?? null,
        'statusValidasi' => $statusValidasi ?? '',
    ]) ?>

    <?= view('pelaporan_risiko/_summary_cards', [
        'summary' => $summary,
    ]) ?>

    <?= view('pelaporan_risiko/_table_section', [
        'data'    => $data,
        'pager'   => $pager   ?? null,
        'perPage' => $perPage ?? 10,
        'total'   => $total   ?? count($data),
        'from'    => $from    ?? 1,
        'to'      => $to      ?? count($data),
        'userRole' => $userRole,
    ]) ?>

    <?= view('pelaporan_risiko/_offcanvas_form') ?>

    <div class="card mt-3">
        <div class="card-body small text-muted">
            <strong>Catatan:</strong><br>
            Laporan ini dihasilkan otomatis dari data Pemantauan Risiko
            dan digunakan sebagai bahan evaluasi manajemen risiko.
        </div>
    </div>

</div>

<link rel="stylesheet" href="<?= base_url('assets/css/pelaporan-risiko.css') ?>">
<script>
    window.PL_CONFIG = {
        csrf: {
            name: '<?= csrf_token() ?>',
            token: '<?= csrf_hash() ?>',
        },

        url: {
            detail: (id) => `<?= site_url('pelaporan-risiko/detail') ?>/${id}`,
            ajukan: '<?= site_url('pelaporan-risiko/ajukan') ?>',
            approveKegiatan: (id) => `<?= site_url('pelaporan-risiko/approve-kegiatan') ?>/${id}`,
            rejectKegiatan: (id) => `<?= site_url('pelaporan-risiko/reject-kegiatan') ?>/${id}`,
            print: '<?= site_url('pelaporan-risiko/print') ?>',
            export: '<?= site_url('pelaporan-risiko/export') ?>',
            batalAjukan: '<?= site_url('pelaporan-risiko/batal-ajukan') ?>',
        }
    };

    window.APP_USER = {
        role: '<?= session('user_role') ?>',
        id_tim: '<?= session('id_tim') ?>'
    };
</script>
<script src="<?= base_url('assets/js/modules/pelaporan_risiko/context-selector.js') ?>"></script>
<script src="<?= base_url('assets/js/modules/pelaporan_risiko/pelaporan.js') ?>"></script>

<?= $this->endSection() ?>