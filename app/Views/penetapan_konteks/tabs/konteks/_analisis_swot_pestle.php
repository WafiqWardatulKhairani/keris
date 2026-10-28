
<?php
/**
 * KERIS - Analisis SWOT dan PESTLE.
 *
 * SWOT ditampilkan di atas, PESTLE di bawah.
 * Setiap kategori ditampilkan sebagai baris yang dapat dibuka.
 * Pengelolaan data mengikuti hak akses pengguna.
 */

$idKonteks  = (int) ($activeKonteks['id_konteks'] ?? 0);
$tahun      = (int) ($activeKonteks['tahun'] ?? date('Y'));
$userRole   = session('user_role');
$canManage  = !empty($canEdit)
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
        margin-top: 1.5rem;
    }

    .sp-analysis-heading {
        font-size: 1.05rem;
        font-weight: 700;
        margin-bottom: .35rem;
    }

    .sp-analysis-description {
        color: #6c757d;
        font-size: .875rem;
        margin-bottom: 1rem;
    }

    .sp-category-list {
        border-top: 1px solid #e5e7eb;
    }

    .sp-category-row {
        display: flex;
        align-items: center;
        gap: 1rem;
        width: 100%;
        padding: 1rem .5rem;
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
        display: flex;
        flex: 0 0 42px;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: #eef2ff;
        color: #4338ca;
        font-size: 1.1rem;
        font-weight: 700;
    }

    .sp-category-content {
        flex: 1;
        min-width: 0;
    }

    .sp-category-name {
        display: block;
        font-weight: 600;
    }

    .sp-category-meaning {
        display: block;
        margin-top: .15rem;
        color: #6b7280;
        font-size: .875rem;
    }

    .sp-category-count {
        display: block;
        margin-top: .35rem;
        color: #6b7280;
        font-size: .8rem;
    }

    .sp-category-action {
        flex: 0 0 auto;
        color: #64748b;
    }

    .sp-period-info {
        font-size: .9rem;
    }

    @media (max-width: 576px) {
        .sp-category-row {
            gap: .75rem;
            padding: .85rem .25rem;
        }

        .sp-category-letter {
            flex-basis: 36px;
            width: 36px;
            height: 36px;
        }
    }
</style>

<section
    class="pk-section sp-analysis"
    id="swotPestleSection"
    data-id-konteks="<?= $idKonteks ?>"
    data-tahun="<?= $tahun ?>"
    data-can-manage="<?= $canManage ? '1' : '0' ?>"
>
    <div class="pk-section-title">Analisis SWOT dan PESTLE</div>

    <div class="pk-context-panel">
        <!-- Filter periode dipakai bersama oleh SWOT dan PESTLE -->
        <div class="row g-3 align-items-end mb-3">
            <div class="col-md-4">
                <label for="swotPestleTahun" class="form-label">
                    Tahun
                </label>
                <input
                    type="number"
                    id="swotPestleTahun"
                    class="form-control"
                    value="<?= $tahun ?>"
                    readonly
                >
            </div>

            <div class="col-md-5">
                <label for="swotPestleTriwulan" class="form-label">
                    Triwulan
                </label>
                <select id="swotPestleTriwulan" class="form-select">
                    <?php foreach ($triwulanOptions as $nomor => $label): ?>
                        <option value="<?= $nomor ?>">
                            <?= esc($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <button
                    type="button"
                    class="btn btn-outline-primary w-100"
                    id="btnMuatAnalisis"
                >
                    Muat Periode
                </button>
            </div>
        </div>

        <div
            id="swotPestlePeriodInfo"
            class="alert alert-info sp-period-info"
        >
            Pilih triwulan untuk melihat analisis.
        </div>

        <!-- ANALISIS SWOT -->
        <div class="mb-4">
            <div class="sp-analysis-heading">Analisis SWOT</div>
            <div class="sp-analysis-description">
                Identifikasi kekuatan, kelemahan, peluang, dan ancaman.
            </div>

            <div class="sp-category-list">
                <?php foreach ($swot as $kode => $item): ?>
                    <button
                        type="button"
                        class="sp-category-row swot-pestle-category"
                        data-jenis="SWOT"
                        data-kategori="<?= esc($kode) ?>"
                    >
                        <span class="sp-category-letter">
                            <?= esc($kode) ?>
                        </span>

                        <span class="sp-category-content">
                            <span class="sp-category-name">
                                <?= esc($item['nama']) ?>
                            </span>
                            <span class="sp-category-meaning">
                                <?= esc($item['arti']) ?>
                            </span>
                            <span
                                class="sp-category-count"
                                id="count-SWOT-<?= esc($kode) ?>"
                            >
                                Memuat data...
                            </span>
                        </span>

                        <span class="sp-category-action" aria-hidden="true">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ANALISIS PESTLE -->
        <div>
            <div class="sp-analysis-heading">Analisis PESTLE</div>
            <div class="sp-analysis-description">
                Identifikasi faktor eksternal yang memengaruhi organisasi.
            </div>

            <div class="sp-category-list">
                <?php foreach ($pestle as $kode => $item): ?>
                    <button
                        type="button"
                        class="sp-category-row swot-pestle-category"
                        data-jenis="PESTLE"
                        data-kategori="<?= esc($kode) ?>"
                    >
                        <span class="sp-category-letter">
                            <?= $kode === 'ENV' ? 'E' : esc($kode) ?>
                        </span>

                        <span class="sp-category-content">
                            <span class="sp-category-name">
                                <?= esc($item['nama']) ?>
                            </span>
                            <span class="sp-category-meaning">
                                <?= esc($item['arti']) ?>
                            </span>
                            <span
                                class="sp-category-count"
                                id="count-PESTLE-<?= esc($kode) ?>"
                            >
                                Memuat data...
                            </span>
                        </span>

                        <span class="sp-category-action" aria-hidden="true">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($userRole === 'admin'): ?>
            <hr class="my-4">

            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="me-auto">
                    <div class="fw-semibold">Pengelolaan Periode</div>
                    <small class="text-muted">
                        Hanya admin yang dapat membuka kembali periode.
                    </small>
                </div>

                <button
                    type="button"
                    class="btn btn-outline-warning"
                    id="btnBukaPeriode"
                >
                    Buka Kembali
                </button>

                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    id="btnTutupPeriode"
                >
                    Kunci Kembali
                </button>
            </div>
        <?php endif; ?>
    </div>
</section>
