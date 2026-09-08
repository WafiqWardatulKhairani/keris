<?php $this->extend('layout/main'); ?>
<?php $this->section('content'); ?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Bank Risiko</h4>
            <small class="text-muted">Daftar pernyataan risiko sebagai referensi identifikasi risiko</small>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div id="bankRisikoTableWrapper">
                <?= view('bank_risiko/_table_section', [
                    'data'    => $data,
                    'pager'   => $pager,
                    'perPage' => $perPage,
                    'from'    => $from,
                    'to'      => $to,
                    'total'   => $total,
                ]) ?> </div>
        </div>
    </div>

</div>

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script src="<?= base_url('assets/js/modules/bank_risiko.js') ?>"></script>
<?php $this->endSection(); ?>