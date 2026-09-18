<?php
$order = ['Sangat Rendah', 'Rendah', 'Sedang', 'Tinggi', 'Sangat Tinggi'];

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

<div class="rtp-summary-panel mb-3">

    <!-- HEADER -->
    <div class="rtp-summary-header">
        <div>
            <div class="rtp-summary-title">
                Ringkasan Rencana Penanganan
            </div>

            <div class="rtp-summary-subtitle">
                Pilih status untuk memfilter data rencana penanganan
            </div>
        </div>

        <?php if (!empty($filter)): ?>
            <a href="<?= site_url('rencana-penanganan') ?>"
                class="rtp-summary-reset">
                Reset Filter
            </a>
        <?php endif; ?>
    </div>

    <!-- DISTRIBUSI LEVEL -->
    <?php if (!empty($levelRisiko)): ?>

        <div class="rtp-summary-distribution">

            <div class="rtp-summary-dist-title">
                Distribusi Level Risiko
            </div>

            <div class="rtp-summary-dist-grid">

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

                    <div class="rtp-summary-dist-item">

                        <div class="rtp-summary-dist-top">
                            <span>
                                <?= esc($level) ?>
                            </span>

                            <strong>
                                <?= $jumlah ?>
                            </strong>
                        </div>

                        <div class="rtp-summary-dist-bar">
                            <div
                                class="rtp-summary-dist-fill"
                                style="
                                    width: <?= $percent ?>%;
                                    background: <?= esc($warna) ?>;
                                ">
                            </div>
                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        </div>
    <?php endif; ?>

    <!-- FILTER STATUS -->
    <div class="rtp-summary-filters">

        <a href="<?= site_url('rencana-penanganan') ?>"
            class="rtp-summary-filter <?= empty($filter) ? 'is-active is-total' : '' ?>">

            <span class="rtp-summary-filter-label">
                Total Risiko Ditangani
            </span>

            <strong>
                <?= $totalDitangani ?>
            </strong>
        </a>


        <a href="<?= site_url('rencana-penanganan?filter=sudah') ?>"
            class="rtp-summary-filter <?= $filter === 'sudah' ? 'is-active is-sudah' : '' ?>">

            <span class="rtp-summary-dot rtp-dot-sudah"></span>

            <span class="rtp-summary-filter-label">
                Sudah Ada RTP
            </span>

            <strong class="text-success">
                <?= $totalSudah ?>
            </strong>
        </a>


        <a href="<?= site_url('rencana-penanganan?filter=belum') ?>"
            class="rtp-summary-filter <?= $filter === 'belum' ? 'is-active is-belum' : '' ?>">

            <span class="rtp-summary-dot rtp-dot-belum"></span>

            <span class="rtp-summary-filter-label">
                Belum Ada RTP
            </span>

            <strong class="text-warning">
                <?= $totalBelum ?>
            </strong>
        </a>

    </div>

</div>