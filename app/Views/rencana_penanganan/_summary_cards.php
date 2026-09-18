<div class="d-flex flex-wrap gap-2 mb-3 er-summary-row">
    <!-- DISTRIBUSI LEVEL -->
    <?php if (!empty($levelRisiko)): ?>
        <div class="er-stat-card er-stat-dist">
 
            <div class="er-stat-label mb-2">
                Distribusi Level
            </div>

            <?php
            $totalLevel = array_sum(array_column($levelRisiko, 'jumlah'));
            ?>
 
            <?php foreach ($levelRisiko as $level => $info): ?>

                <?php
                $jumlah = $info['jumlah'] ?? 0;
                $warna  = hex_warna_selera_risiko($info['warna'] ?? null);

                $percent = $totalLevel > 0
                    ? ($jumlah / $totalLevel) * 100
                    : 0;
                ?>

                <div class="er-dist-bar-row">

                    <span class="er-dist-label">
                        <?= esc($level) ?>
                    </span>

                    

                    <div class="er-dist-bar">
                        <div class="er-dist-fill"
                            style="width:<?= $percent ?>%; background:<?= $warna ?>">
                        </div>
                    </div>

                    <span class="er-dist-value">
                        <?= $jumlah ?>
                    </span>

                </div>

            <?php endforeach; ?>

        </div>
    <?php endif; ?>

    <!-- TOTAL -->
    <a href="<?= site_url('rencana-penanganan') ?>" class="er-stat-link">
        <div class="er-stat-card <?= !$filter ? 'er-stat-active' : '' ?>">
            <div class="er-stat-label">Total Risiko Ditangani</div>
            <div class="er-stat-value"><?= $totalDitangani ?></div>
        </div>
    </a>

    <!-- SUDAH -->
    <a href="<?= site_url('rencana-penanganan?filter=sudah') ?>" class="er-stat-link">
        <div class="er-stat-card <?= $filter === 'sudah' ? 'er-stat-active-sudah' : '' ?>">
            <div class="er-stat-label">Sudah Ada RTP</div>
            <div class="er-stat-value text-success"><?= $totalSudah ?></div>
        </div>
    </a>

    <!-- BELUM -->
    <a href="<?= site_url('rencana-penanganan?filter=belum') ?>" class="er-stat-link">
        <div class="er-stat-card <?= $filter === 'belum' ? 'er-stat-active-belum' : '' ?>">
            <div class="er-stat-label">Belum Ada RTP</div>
            <div class="er-stat-value text-warning"><?= $totalBelum ?></div>
        </div>
    </a>
</div>