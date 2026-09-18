<?php
$order = [
    'Sangat Rendah',
    'Rendah',
    'Sedang',
    'Tinggi',
    'Sangat Tinggi'
];

$colorMap = [
    'Sangat Rendah' => '#0d6efd',
    'Rendah'        => '#198754',
    'Sedang'        => '#ffc107',
    'Tinggi'        => '#fd7e14',
    'Sangat Tinggi' => '#dc3545',
];

$totalLevel = 0;

foreach ($levelRisiko ?? [] as $info) {
    $totalLevel += (int) ($info['jumlah'] ?? 0);
}
?>

<div class="er-summary-panel mb-3">

    <!-- HEADER -->
    <div class="er-summary-header">

        <div>
            <div class="er-summary-title">
                Ringkasan Evaluasi Risiko
            </div>

            <div class="er-summary-subtitle">
                Pilih status untuk memfilter data evaluasi risiko
            </div>
        </div>

        <?php if (!empty($filter)): ?>
            <a href="<?= site_url('evaluasi-risiko') ?>"
               class="er-summary-reset">
                Reset Filter
            </a>
        <?php endif; ?>

    </div>

    <!-- DISTRIBUSI LEVEL RISIKO -->
    <?php if (!empty($levelRisiko)): ?>

        <div class="er-summary-distribution">

            <div class="er-summary-dist-title">
                Distribusi Level Risiko
            </div>

            <div class="er-summary-dist-grid">

                <?php foreach ($order as $level): ?>

                    <?php
                    $info = $levelRisiko[$level] ?? [];

                    $jumlah = (int) ($info['jumlah'] ?? 0);

                    $warna = !empty($info['warna'])
                        ? hex_warna_selera_risiko($info['warna'])
                        : ($colorMap[$level] ?? '#94a3b8');

                    $percent = $totalLevel > 0
                        ? ($jumlah / $totalLevel) * 100
                        : 0;
                    ?>

                    <div class="er-summary-dist-item">

                        <div class="er-summary-dist-top">

                            <span>
                                <?= esc($level) ?>
                            </span>

                            <strong>
                                <?= $jumlah ?>
                            </strong>

                        </div>

                        <div class="er-summary-dist-bar">
                            <div
                                class="er-summary-dist-fill"
                                style="width: <?= $percent ?>%; background: <?= esc($warna) ?>;">
                            </div>
                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        </div>

    <?php endif; ?>

    <!-- FILTER STATUS -->
    <div class="er-summary-filters">

        <!-- TOTAL -->
        <a href="<?= site_url('evaluasi-risiko') ?>"
           class="er-summary-filter <?= empty($filter) ? 'is-active is-total' : '' ?>">

            <span class="er-summary-filter-label">
                Total Risiko
            </span>

            <strong>
                <?= $totalRisiko ?>
            </strong>
        </a>

        <!-- SUDAH -->
        <a href="<?= site_url('evaluasi-risiko?filter=sudah') ?>"
           class="er-summary-filter <?= $filter === 'sudah' ? 'is-active is-sudah' : '' ?>">

            <span class="er-summary-dot er-dot-sudah"></span>

            <span class="er-summary-filter-label">
                Sudah Dievaluasi
            </span>

            <strong class="text-success">
                <?= $totalSudah ?>
            </strong>
        </a>

        <!-- BELUM -->
        <a href="<?= site_url('evaluasi-risiko?filter=belum') ?>"
           class="er-summary-filter <?= $filter === 'belum' ? 'is-active is-belum' : '' ?>">

            <span class="er-summary-dot er-dot-belum"></span>

            <span class="er-summary-filter-label">
                Belum Dievaluasi
            </span>

            <strong class="text-warning">
                <?= $totalBelum ?>
            </strong>
        </a>
    </div>
</div>