<?php

$idKonteks = (int) ($activeKonteks['id_konteks'] ?? 0);

$idTim = (int) (
    session('global_id_tim')
    ?? $activeKonteks['id_tim']
    ?? 0
);

$tahun = (int) (
    session('global_tahun')
    ?? $activeKonteks['tahun']
    ?? date('Y')
);
$userRole  = session('user_role');

$canManage = !empty($canEdit)
    && in_array($userRole, ['admin', 'operator'], true);

$triwulanOptions = [
    1 => 'Triwulan I (Jan–Mar)',
    2 => 'Triwulan II (Apr–Jun)',
    3 => 'Triwulan III (Jul–Sep)',
    4 => 'Triwulan IV (Okt–Des)',
];

$swot = [
    'S' => ['nama' => 'Strengths', 'arti' => 'Kekuatan'],
    'W' => ['nama' => 'Weaknesses', 'arti' => 'Kelemahan'],
    'O' => ['nama' => 'Opportunities', 'arti' => 'Peluang'],
    'T' => ['nama' => 'Threats', 'arti' => 'Ancaman'],
];

$pestle = [
    'P'   => ['nama' => 'Political', 'arti' => 'Politik'],
    'E'   => ['nama' => 'Economic', 'arti' => 'Ekonomi'],
    'S'   => ['nama' => 'Social', 'arti' => 'Sosial'],
    'T'   => ['nama' => 'Technological', 'arti' => 'Teknologi'],
    'L'   => ['nama' => 'Legal', 'arti' => 'Hukum'],
    'ENV' => ['nama' => 'Environmental', 'arti' => 'Lingkungan'],
];
?>

<section
    class="pk-section sp-analysis"
    id="swotPestleSection"
    data-id-konteks="<?= $idKonteks ?>"
    data-id-tim="<?= $idTim ?>"
    data-tahun="<?= $tahun ?>"
    data-can-manage="<?= $canManage ? '1' : '0' ?>">
    <div class="pk-section-title">Analisis SWOT dan PESTLE</div>

    <div class="pk-context-panel">
        <!-- Filter periode bersama -->
        <div class="row g-2 align-items-end mb-3">
            <div class="col-4 col-md-3">
                <label for="swotPestleTahun" class="form-label small mb-1">
                    Tahun
                </label>
                <input
                    type="number"
                    id="swotPestleTahun"
                    class="form-control form-control-sm"
                    value="<?= $tahun ?>"
                    readonly>
            </div>

            <div class="col-8 col-md-5">
                <label for="swotPestleTriwulan" class="form-label small mb-1">
                    Triwulan
                </label>
                <select
                    id="swotPestleTriwulan"
                    class="form-select form-select-sm">
                    <?php foreach ($triwulanOptions as $nomor => $label): ?>
                        <option value="<?= $nomor ?>">
                            <?= esc($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-md-4">
                <button
                    type="button"
                    class="btn btn-sm btn-outline-primary w-100"
                    id="btnMuatAnalisis">
                    Tampilkan
                </button>
            </div>
        </div>

        <div
            id="swotPestlePeriodInfo"
            class="alert alert-info sp-period-info">
            Pilih triwulan untuk melihat analisis.
        </div>

        <!-- ANALISIS SWOT -->
        <div class="sp-category-group">
            <button
                type="button"
                class="sp-group-toggle"
                aria-expanded="false"
                aria-controls="spSwotContent"
                data-target="spSwotContent">

                <span class="sp-group-label">
                    <i class="fas fa-chevron-right sp-toggle-icon"></i>
                    <span>Analisis SWOT</span>
                </span>

                <span class="sp-group-count" id="spSwotTotal">
                    0 poin
                </span>
            </button>

            <div class="sp-group-content" id="spSwotContent" hidden>
                <div class="sp-category-list">
                    <?php foreach ($swot as $kode => $item): ?>
                        <button
                            type="button"
                            class="sp-category-row swot-pestle-category"
                            data-jenis="SWOT"
                            data-kategori="<?= esc($kode) ?>"
                            aria-label="Buka <?= esc($item['nama']) ?>">

                            <span class="sp-category-letter">
                                <?= esc($kode) ?>
                            </span>

                            <span class="sp-category-content">
                                <span class="sp-category-name">
                                    <?= esc($item['nama']) ?>
                                    (<?= esc($item['arti']) ?>)
                                </span>

                                <span
                                    class="sp-category-count"
                                    id="count-SWOT-<?= esc($kode) ?>">
                                    Memuat data...
                                </span>
                            </span>

                            <span class="sp-category-meta">
                                <span
                                    class="sp-category-count-visible"
                                    data-count-for="count-SWOT-<?= esc($kode) ?>">
                                    — poin
                                </span>

                                <i class="fas fa-chevron-right sp-category-action"></i>
                            </span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- ANALISIS PESTLE -->
        <div class="sp-category-group">
            <button
                type="button"
                class="sp-group-toggle"
                aria-expanded="false"
                aria-controls="spPestleContent"
                data-target="spPestleContent">

                <span class="sp-group-label">
                    <i class="fas fa-chevron-right sp-toggle-icon"></i>
                    <span>Analisis PESTLE</span>
                </span>

                <span class="sp-group-count" id="spPestleTotal">
                    0 poin
                </span>
            </button>

            <div class="sp-group-content" id="spPestleContent" hidden>
                <div class="sp-category-list">
                    <?php foreach ($pestle as $kode => $item): ?>
                        <button
                            type="button"
                            class="sp-category-row swot-pestle-category"
                            data-jenis="PESTLE"
                            data-kategori="<?= esc($kode) ?>"
                            aria-label="Buka <?= esc($item['nama']) ?>">

                            <span class="sp-category-letter">
                                <?= $kode === 'ENV' ? 'E' : esc($kode) ?>
                            </span>

                            <span class="sp-category-content">
                                <span class="sp-category-name">
                                    <?= esc($item['nama']) ?>
                                    (<?= esc($item['arti']) ?>)
                                </span>

                                <span
                                    class="sp-category-count"
                                    id="count-PESTLE-<?= esc($kode) ?>">
                                    Memuat data...
                                </span>
                            </span>

                            <span class="sp-category-meta">
                                <span
                                    class="sp-category-count-visible"
                                    data-count-for="count-PESTLE-<?= esc($kode) ?>">
                                    — poin
                                </span>

                                <i class="fas fa-chevron-right sp-category-action"></i>
                            </span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php if ($userRole === 'admin'): ?>
            <hr class="my-3">

            <div class="d-flex align-items-center justify-content-between gap-2">
                <div>
                    <div class="fw-semibold small">Pengelolaan Periode</div>
                    <small class="text-muted" id="swotPestleStatusPeriode">
                        Memeriksa status periode...
                    </small>
                </div>

                <button
                    type="button"
                    class="btn btn-sm btn-outline-warning"
                    id="btnTogglePeriode"
                    hidden>
                    Buka Kembali
                </button>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="sp-history" id="swotPestleHistory">
    <div class="sp-history-header">
        <div class="sp-history-icon">
            <i class="fas fa-history"></i>
        </div>

        <div>
            <h5 class="mb-1 fw-semibold">
                Riwayat Analisis SWOT & PESTLE
            </h5>

            <p class="text-muted small mb-0">
                Pilih periode untuk melihat analisis sebelumnya.
                Riwayat hanya dapat dibaca.
            </p>
        </div>
    </div>

    <div class="pk-context-panel">
        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <label for="spHistoryYear" class="form-label small">
                    Tahun
                </label>

                <select
                    id="spHistoryYear"
                    class="form-select form-select-sm">
                    <option value="">Semua Tahun</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="spHistoryQuarter" class="form-label small">
                    Triwulan
                </label>

                <select
                    id="spHistoryQuarter"
                    class="form-select form-select-sm">
                    <option value="">Semua Triwulan</option>
                    <option value="1">Triwulan I</option>
                    <option value="2">Triwulan II</option>
                    <option value="3">Triwulan III</option>
                    <option value="4">Triwulan IV</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="spHistorySearch" class="form-label small">
                    Cari periode
                </label>

                <input
                    type="search"
                    id="spHistorySearch"
                    class="form-control form-control-sm"
                    placeholder="Cari tahun atau triwulan...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover sp-history-table mb-0">
                <thead>
                    <tr>
                        <th>Tahun</th>
                        <th>Triwulan</th>
                        <th>Jumlah Poin</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody id="spHistoryTableBody">
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            Riwayat akan dimuat dari server.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<script>
    // Sinkronkan jumlah poin di sisi kanan dengan count yang diperbarui JS.
    document.querySelectorAll(
        '#swotPestleSection .sp-category-count-visible'
    ).forEach(function(visibleCount) {
        const sourceId = visibleCount.dataset.countFor;
        const source = document.getElementById(sourceId);

        if (!source) return;

        const observer = new MutationObserver(function() {
            const value = source.textContent.trim();
            visibleCount.textContent = value || '0 poin';
        });

        observer.observe(source, {
            childList: true,
            characterData: true,
            subtree: true
        });

        visibleCount.textContent =
            source.textContent.trim() || 'Memuat data...';
    });
</script>

<script>
    document.querySelectorAll(
        '#swotPestleSection .sp-group-toggle'
    ).forEach(function(button) {
        button.addEventListener('click', function() {
            const target = document.getElementById(
                button.dataset.target
            );

            if (!target) return;

            const isExpanded =
                button.getAttribute('aria-expanded') === 'true';

            button.setAttribute(
                'aria-expanded',
                String(!isExpanded)
            );

            target.hidden = isExpanded;

            const icon = button.querySelector('.sp-toggle-icon');

            if (icon) {
                icon.classList.toggle('fa-chevron-right', isExpanded);
                icon.classList.toggle('fa-chevron-down', !isExpanded);
            }
        });
    });

    function updateGroupTotal(type, targetId) {
        const counts = document.querySelectorAll(
            '#swotPestleSection [data-count-for^="count-' + type + '-"]'
        );

        let total = 0;

        counts.forEach(function(element) {
            const value = parseInt(element.textContent, 10);

            if (Number.isFinite(value)) {
                total += value;
            }
        });

        const target = document.getElementById(targetId);

        if (target) {
            target.textContent = total + ' poin';
        }
    }

    function observeGroupTotal(type, targetId) {
        const counts = document.querySelectorAll(
            '#swotPestleSection [data-count-for^="count-' + type + '-"]'
        );

        counts.forEach(function(element) {
            const source = document.getElementById(
                element.dataset.countFor
            );

            if (!source) return;

            new MutationObserver(function() {
                updateGroupTotal(type, targetId);
            }).observe(source, {
                childList: true,
                characterData: true,
                subtree: true
            });
        });

        updateGroupTotal(type, targetId);
    }

    observeGroupTotal('SWOT', 'spSwotTotal');
    observeGroupTotal('PESTLE', 'spPestleTotal');
</script>