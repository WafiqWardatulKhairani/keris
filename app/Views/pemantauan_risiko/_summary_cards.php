<?php
$statusConfig = [
    'Belum Dilaksanakan' => '#6c757d',
    'Dalam Proses'       => '#0d6efd',
    'Selesai'            => '#198754',
    'Terlambat'          => '#dc3545',
];

$totalDistribusi = array_sum($distribusi ?? []);
?>

<div class="er-summary-panel mb-3">

    <!-- HEADER -->
    <div class="er-summary-header">

        <div>
            <div class="er-summary-title">
                Ringkasan Pemantauan Risiko
            </div>

            <div class="er-summary-subtitle">
                Pilih status untuk memfilter data pemantauan risiko
            </div>
        </div>

        <?php if (!empty($filter)): ?>
            <a href="<?= site_url('pemantauan-risiko') ?>"
               class="er-summary-reset">
                Reset Filter
            </a>
        <?php endif; ?>

    </div>

    <!-- DISTRIBUSI STATUS -->
    <?php if (!empty($distribusi)): ?>

        <div class="er-summary-distribution">

            <div class="er-summary-dist-title">
                Distribusi Status Pemantauan
            </div>

            <div class="er-summary-dist-grid pr-summary-dist-grid">

                <?php foreach ($statusConfig as $status => $warna): ?>

                    <?php
                    $jumlah = (int) ($distribusi[$status] ?? 0);

                    $percent = $totalDistribusi > 0
                        ? ($jumlah / $totalDistribusi) * 100
                        : 0;
                    ?>

                    <div class="er-summary-dist-item">

                        <div class="er-summary-dist-top">

                            <span>
                                <?= esc($status) ?>
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

        <!-- TOTAL RTP -->
        <a href="<?= site_url('pemantauan-risiko') ?>"
           class="er-summary-filter <?= empty($filter) ? 'is-active is-total' : '' ?>">

            <span class="er-summary-filter-label">
                Total RTP
            </span>

            <strong>
                <?= $totalRtp ?>
            </strong>
        </a>

        <!-- SELESAI -->
        <a href="<?= site_url('pemantauan-risiko?filter=Selesai') ?>"
           class="er-summary-filter <?= $filter === 'Selesai' ? 'is-active pr-active-selesai' : '' ?>">

            <span class="er-summary-dot pr-dot-selesai"></span>

            <span class="er-summary-filter-label">
                Selesai
            </span>

            <strong class="text-success">
                <?= $distribusi['Selesai'] ?? 0 ?>
            </strong>
        </a>

        <!-- DALAM PROSES -->
        <a href="<?= site_url('pemantauan-risiko?filter=Dalam+Proses') ?>"
           class="er-summary-filter <?= $filter === 'Dalam Proses' ? 'is-active pr-active-proses' : '' ?>">

            <span class="er-summary-dot pr-dot-proses"></span>

            <span class="er-summary-filter-label">
                Dalam Proses
            </span>

            <strong class="text-primary">
                <?= $distribusi['Dalam Proses'] ?? 0 ?>
            </strong>
        </a>

        <!-- BELUM DILAKSANAKAN -->
        <a href="<?= site_url('pemantauan-risiko?filter=Belum+Dilaksanakan') ?>"
           class="er-summary-filter <?= $filter === 'Belum Dilaksanakan' ? 'is-active pr-active-belum' : '' ?>">

            <span class="er-summary-dot pr-dot-belum"></span>

            <span class="er-summary-filter-label">
                Belum Dilaksanakan
            </span>

            <strong class="text-secondary">
                <?= $distribusi['Belum Dilaksanakan'] ?? 0 ?>
            </strong>
        </a>

        <!-- TERLAMBAT -->
        <a href="<?= site_url('pemantauan-risiko?filter=Terlambat') ?>"
           class="er-summary-filter <?= $filter === 'Terlambat' ? 'is-active pr-active-terlambat' : '' ?>">

            <span class="er-summary-dot pr-dot-terlambat"></span>

            <span class="er-summary-filter-label">
                Terlambat
            </span>

            <strong class="text-danger">
                <?= $distribusi['Terlambat'] ?? 0 ?>
            </strong>
        </a>

    </div>

</div>