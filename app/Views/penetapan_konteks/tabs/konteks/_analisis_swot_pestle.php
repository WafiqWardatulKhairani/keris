<?php

$idKonteks = (int) ($activeKonteks['id_konteks'] ?? 0);
$tahun     = (int) ($activeKonteks['tahun'] ?? date('Y'));
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

<style>
    .sp-analysis {
        margin-top: 1rem;
    }

    .sp-analysis .pk-context-panel {
        padding: 1rem;
    }

    .sp-analysis-heading {
        font-size: 1rem;
        font-weight: 700;
        margin: 0 0 .5rem;
    }

    .sp-category-list {
        border-top: 1px solid #e5e7eb;
    }

    .sp-category-row {
        display: flex;
        align-items: center;
        gap: .75rem;
        width: 100%;
        padding: .65rem .25rem;
        text-align: left;
        color: inherit;
        background: transparent;
        border: 0;
        border-bottom: 1px solid #e5e7eb;
        transition: background .15s ease;
    }

    .sp-category-row:hover {
        background: #f8fafc;
    }

    .sp-category-letter {
        flex: 0 0 25px;
        width: 25px;
        font-weight: 700;
        font-size: .95rem;
    }

    .sp-category-content {
        flex: 1;
        min-width: 0;
    }

    .sp-category-name {
        display: block;
        font-size: .9rem;
        font-weight: 500;
        line-height: 1.35;
    }

    .sp-category-meaning,
    .sp-category-count {
        display: none;
    }

    .sp-category-meta {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        gap: .75rem;
        color: #6b7280;
        font-size: .8rem;
    }

    .sp-category-action {
        color: #64748b;
        font-size: .75rem;
    }

    .sp-period-info {
        font-size: .85rem;
        padding: .6rem .85rem;
        margin-bottom: 1rem;
    }

    .sp-analysis-description {
        display: none;
    }

    .sp-category-group+.sp-category-group {
        margin-top: 1.25rem;
    }

    @media (max-width: 576px) {
        .sp-analysis .pk-context-panel {
            padding: .75rem;
        }

        .sp-category-row {
            gap: .5rem;
            padding: .65rem .15rem;
        }

        .sp-category-meta {
            gap: .45rem;
        }

        .sp-category-name {
            font-size: .85rem;
        }
    }
</style>

<section
    class="pk-section sp-analysis"
    id="swotPestleSection"
    data-id-konteks="<?= $idKonteks ?>"
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
            <div class="sp-analysis-heading">Analisis SWOT</div>

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
                                data-count-for="count-SWOT-<?= esc($kode) ?>">— poin</span>

                            <span class="sp-category-action" aria-hidden="true">
                                <i class="fas fa-chevron-right"></i>
                            </span>
                        </span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ANALISIS PESTLE -->
        <div class="sp-category-group">
            <div class="sp-analysis-heading">Analisis PESTLE</div>

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
                                data-count-for="count-PESTLE-<?= esc($kode) ?>">— poin</span>

                            <span class="sp-category-action" aria-hidden="true">
                                <i class="fas fa-chevron-right"></i>
                            </span>
                        </span>
                    </button>
                <?php endforeach; ?>
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