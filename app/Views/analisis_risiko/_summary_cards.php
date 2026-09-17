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
    'Sangat Tinggi' => '#dc3545'
];

$totalLevel = array_sum($levelRisiko ?? []);
?>

<div class="ar-summary-panel mb-3">

    <!-- ================= HEADER ================= -->
    <div class="ar-summary-header">
        <div>
            <div class="ar-summary-title">
                Ringkasan Analisis Risiko
            </div>

            <div class="ar-summary-subtitle">
                Pilih status untuk memfilter data risiko
            </div>
        </div>

        <?php if ($filter): ?>
            <a href="<?= site_url('analisis-risiko') ?>"
               class="ar-reset-filter">

                <i class="ti ti-x"></i>
                Hapus Filter

            </a>
        <?php endif; ?>
    </div>


    <div class="ar-summary-content">

        <!-- ================= DISTRIBUSI LEVEL ================= -->
        <?php if (!empty($levelRisiko)): ?>

            <div class="ar-distribution-section">

                <div class="ar-distribution-head">

                    <div>
                        <div class="ar-filter-label">
                            Distribusi Level Risiko
                        </div>

                        <div class="ar-distribution-subtitle">
                            Berdasarkan tingkat risiko
                        </div>
                    </div>

                    <span class="ar-distribution-total">
                        <?= $totalLevel ?> dianalisis
                    </span>

                </div>


                <!-- LEVEL RISIKO -->
                <div class="ar-level-summary">

                    <?php foreach ($order as $lvl):

                        $jumlah = $levelRisiko[$lvl] ?? 0;
                        $warna  = $colorMap[$lvl];
                    ?>

                        <div class="ar-level-summary-item">

                            <!-- GARIS WARNA -->
                            <div
                                class="ar-level-color"
                                style="background-color: <?= $warna ?>;">
                            </div>


                            <!-- INFORMASI -->
                            <div class="ar-level-summary-content">

                                <div class="ar-level-summary-name">

                                    <span
                                        class="ar-level-dot"
                                        style="background-color: <?= $warna ?>;">
                                    </span>

                                    <span>
                                        <?= esc($lvl) ?>
                                    </span>

                                </div>

                                <strong class="ar-level-summary-value">
                                    <?= $jumlah ?>
                                </strong>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

        <?php endif; ?>


        <!-- ================= STATUS ANALISIS ================= -->
        <div class="ar-filter-section">

            <div class="ar-filter-label">
                Status Analisis
            </div>


            <div class="ar-filter-grid">

                <!-- ================= TOTAL ================= -->
                <a href="<?= site_url('analisis-risiko') ?>"
                   class="ar-filter-card
                   <?= !$filter ? 'active active-total' : '' ?>">

                    <div class="ar-filter-icon ar-filter-icon-total">
                        <i class="ti ti-list-check"></i>
                    </div>


                    <div class="ar-filter-info">

                        <span class="ar-filter-name">
                            Total Risiko
                        </span>

                        <strong>
                            <?= $totalRisiko ?>
                        </strong>

                        <small>
                            Seluruh data risiko
                        </small>

                    </div>


                    <div class="ar-filter-action">

                        <?= !$filter ? 'Ditampilkan' : 'Lihat Data' ?>

                        <i class="ti ti-chevron-right"></i>

                    </div>

                </a>


                <!-- ================= SUDAH ================= -->
                <a href="<?= site_url('analisis-risiko?filter=sudah') ?>"
                   class="ar-filter-card
                   <?= $filter === 'sudah'
                        ? 'active active-sudah'
                        : '' ?>">

                    <div class="ar-filter-icon ar-filter-icon-sudah">
                        <i class="ti ti-circle-check"></i>
                    </div>


                    <div class="ar-filter-info">

                        <span class="ar-filter-name">
                            Sudah Dianalisis
                        </span>

                        <strong>
                            <?= $totalSudah ?>
                        </strong>

                        <small>
                            Risiko yang telah dinilai
                        </small>

                    </div>


                    <div class="ar-filter-action">

                        <?= $filter === 'sudah'
                            ? 'Ditampilkan'
                            : 'Lihat Data' ?>

                        <i class="ti ti-chevron-right"></i>

                    </div>

                </a>


                <!-- ================= BELUM ================= -->
                <a href="<?= site_url('analisis-risiko?filter=belum') ?>"
                   class="ar-filter-card
                   <?= $filter === 'belum'
                        ? 'active active-belum'
                        : '' ?>">

                    <div class="ar-filter-icon ar-filter-icon-belum">
                        <i class="ti ti-clock"></i>
                    </div>


                    <div class="ar-filter-info">

                        <span class="ar-filter-name">
                            Belum Dianalisis
                        </span>

                        <strong>
                            <?= $totalBelum ?>
                        </strong>

                        <small>
                            Risiko yang belum dinilai
                        </small>

                    </div>


                    <div class="ar-filter-action">

                        <?= $filter === 'belum'
                            ? 'Ditampilkan'
                            : 'Lihat Data' ?>

                        <i class="ti ti-chevron-right"></i>

                    </div>

                </a>

            </div>

        </div>

    </div>

</div>