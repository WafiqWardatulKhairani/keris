<div class="alert alert-info mb-3">
    MODE : <?= esc($mode ?? '-') ?>
</div>

<?php if (empty($hasKegiatan)): ?>
    <div class="alert alert-warning">
        <strong>Pilih Kegiatan Terlebih Dahulu</strong><br>
        Silakan pilih salah satu kegiatan pada Global Context Selector.
    </div>
    <?php return; ?>
<?php endif; ?>

<?php if (!$hasScope): ?>
    <div class="alert alert-warning">
        <strong>Belum Ada Ruang Lingkup</strong><br>
        Ruang Lingkup untuk Tahun, Tim Kerja, dan Kegiatan yang dipilih belum dibuat.
    </div>
    <?php return; ?>
<?php endif; ?>

<?php
$isViewMode = ($mode ?? '') === 'view';
$db = \Config\Database::connect();

$globalTim = null;
$globalKegiatan = null;

if (session('global_id_tim')) {
    $globalTim = $db->table('tim_kerja')
        ->where('id_tim', session('global_id_tim'))
        ->get()
        ->getRowArray();
}

if (session('global_id_kegiatan')) {
    $globalKegiatan = $db->table('kegiatan')
        ->where('id_kegiatan', session('global_id_kegiatan'))
        ->get()
        ->getRowArray();
}
?>

<form id="pkFormKonteks">
    <input type="hidden"
        id="pkMode"
        name="mode"
        value="<?= esc($mode ?? 'create') ?>">

    <input type="hidden"
        id="pkId"
        name="id_konteks"
        value="<?= esc($activeKonteks['id_konteks'] ?? '') ?>">

    <div class="pk-section">
        <div class="pk-section-title">
            Ruang Lingkup Aktif
        </div>

        <div class="pk-context-panel">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="pk-info-card h-100">
                        <div class="pk-info-card-label">
                            Tahun
                        </div>

                        <div class="pk-info-card-value">
                            <?= esc(session('global_tahun') ?? '-') ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="pk-info-card h-100">
                        <div class="pk-info-card-label">
                            Tim Kerja
                        </div>

                        <div class="pk-info-card-value">
                            <?= esc($globalTim['nama_tim'] ?? '-') ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="pk-info-card h-100">
                        <div class="pk-info-card-label">
                            Kegiatan
                        </div>

                        <div class="pk-info-card-value">
                            <?= esc($globalKegiatan['nama_kegiatan'] ?? '-') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="pk-section">

        <div class="pk-section-title">
            Pengelola Risiko
        </div>

        <div class="row g-3 mt-1">
            <div class="col-lg-6">
                <div class="pk-structure-panel h-100">
                    <div class="pk-structure-title">
                        Pemilik Risiko
                    </div>

                    <input type="hidden"
                        name="pemilik_risiko_id"
                        id="pkPemilikId">

                    <div class="pk-info-row">
                        <span class="pk-info-label">Nama</span>
                        <span id="pkPemilikNama" class="pk-info-value">
                            <?= esc($pemilikRisiko['nama'] ?? '-') ?>
                        </span>
                    </div>

                    <div class="pk-info-row">
                        <span class="pk-info-label">NIP</span>
                        <span id="pkPemilikNip" class="pk-info-value">
                            <?= esc($pemilikRisiko['nip'] ?? '-') ?>
                        </span>
                    </div>

                    <div class="pk-info-row">
                        <span class="pk-info-label">Jabatan</span>
                        <span id="pkPemilikJabatan" class="pk-info-value">
                            <?= esc($pemilikRisiko['jabatan'] ?? '-') ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="pk-structure-panel h-100">
                    <div class="pk-structure-title">
                        Pengelola Risiko
                    </div>

                    <input type="hidden"
                        name="pengelola_risiko_id"
                        id="pkPengelolaValue">

                    <div class="pk-info-row">
                        <span class="pk-info-label">Nama</span>
                        <span id="pkPengelolaNama" class="pk-info-value">
                            <?= esc($pengelolaRisiko['nama'] ?? '-') ?>
                        </span>
                    </div>

                    <div class="pk-info-row">
                        <span class="pk-info-label">NIP</span>
                        <span id="pkPengelolaNip" class="pk-info-value">
                            <?= esc($pengelolaRisiko['nip'] ?? '-') ?>
                        </span>
                    </div>

                    <div class="pk-info-row">
                        <span class="pk-info-label">Jabatan</span>
                        <span id="pkPengelolaJabatan" class="pk-info-value">
                            <?= esc($pengelolaRisiko['jabatan'] ?? '-') ?>
                        </span>
                    </div>

                    <small id="pkPengelolaWarning"
                        class="text-warning mt-1"
                        style="display:none;">
                    </small>
                </div>
            </div>
        </div>
    </div>

    <?php

    $selectedSasaran = null;

    if (!empty($activeKonteks['id_sasaran_strategis'])) {

        foreach ($listSasaran as $ss) {

            if (
                (int)$ss['id_sasaran_strategis']
                ===
                (int)$activeKonteks['id_sasaran_strategis']
            ) {
                $selectedSasaran = $ss;
                break;
            }
        }
    }
    ?>

    <div class="pk-section">
        <div class="pk-section-title">
            Sasaran Strategis
        </div>

        <div class="pk-context-panel">
            <div class="pk-field-vertical">
                <div class="pk-field-label">
                    Sasaran Strategis
                </div>

                <div class="pk-combobox" id="pkSasaranBox">
                    <input type="hidden"
                        name="id_sasaran_strategis"
                        id="pkSasaranValue"
                        value="<?= esc($selectedSasaran['id_sasaran_strategis'] ?? '') ?>">

                    <input
                        type="text"
                        class="pk-combobox-input"
                        id="pkSasaranInput"
                        value="<?= esc($selectedSasaran['uraian_sasaran'] ?? '') ?>"
                        placeholder="Pilih Sasaran Strategis"
                        autocomplete="off"
                        <?= $isViewMode ? 'readonly' : '' ?>>

                    <?php if (!$isViewMode): ?>
                        <div class="pk-combobox-dropdown">
                            <div class="pk-combobox-options">
                                <?php foreach ($listSasaran as $ss): ?>
                                    <div class="pk-option"
                                        data-value="<?= $ss['id_sasaran_strategis'] ?>">
                                        <?= esc($ss['uraian_sasaran']) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="pk-section">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="pk-section-title mb-0">
                Proses Bisnis & Sasaran Kinerja
            </div>

            <button type="button" class="btn btn-primary btn-sm" id="pbBtnCreate">
                <i class="ti ti-plus"></i>Proses
            </button>
        </div>

        <div id="pkProsesBisnisTableWrapper">
            <?= view('penetapan_konteks/tabs/proses_bisnis/_table_section', [
                'data' => $sasaranOrganisasi
            ]) ?>
        </div>
    </div>

    <div class="pk-section">
        <div class="pk-section-title">
            Pemangku Kepentingan
        </div>

        <div class="pk-context-panel">
            <div class="pk-field-vertical">
                <?php if (!$isViewMode): ?>
                    <div class="pk-combobox" id="pkPemangkuBox">
                        <input type="text"
                            class="pk-combobox-input"
                            id="pkPemangkuInput"
                            placeholder="Pilih Pemangku Kepentingan"
                            autocomplete="off">

                        <div class="pk-combobox-dropdown">
                            <div class="pk-combobox-options">
                                <?php
                                $groupPemangku = [];
                                foreach ($listPemangku as $p) {
                                    $groupPemangku[$p['hubungan']][] = $p;
                                }
                                ?>

                                <?php foreach ($groupPemangku as $hubungan => $items): ?>
                                    <div class="pk-option-group">
                                        <?= esc($hubungan) ?>
                                    </div>

                                    <?php foreach ($items as $p): ?>
                                        <div class="pk-option pk-option-entity"
                                            data-value="<?= $p['id_pemangku'] ?>"
                                            data-role="<?= esc($p['hubungan'] ?? '') ?>">

                                            <div class="pk-option-title">
                                                <?= esc($p['nama_instansi']) ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <div id="pkPemangkuTags"
                    class="pk-pemangku-list mt-3">

                    <?php
                    $groupPemangkuSelected = [];

                    foreach (($selectedPemangku ?? []) as $p) {
                        $groupPemangkuSelected[$p['hubungan']][] = $p;
                    }
                    ?>

                    <?php foreach ($groupPemangkuSelected as $hubungan => $items): ?>

                        <div class="pk-pemangku-group"
                            data-role="<?= esc($hubungan) ?>">

                            <div class="pk-pemangku-title">
                                <?= esc($hubungan) ?>
                            </div>

                            <div class="pk-pemangku-items">

                                <?php foreach ($items as $p): ?>

                                    <div class="pk-pemangku-item <?= !$isViewMode ? 'pk-editable' : '' ?>"
                                        data-id="<?= $p['id_pemangku'] ?>">

                                        <span>
                                            <?= esc($p['nama_instansi']) ?>
                                        </span>

                                        <?php if (!$isViewMode): ?>
                                            <span class="pk-tag-remove">×</span>
                                        <?php endif; ?>

                                        <input
                                            type="hidden"
                                            name="pemangku[]"
                                            value="<?= $p['id_pemangku'] ?>">

                                    </div>

                                <?php endforeach; ?>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>
            </div>
        </div>
    </div>

    <div class="pk-section">
        <div class="pk-section-title">
            Peraturan Terkait
        </div>

        <div class="pk-context-panel">
            <div class="pk-field-vertical">
                <?php if (!$isViewMode): ?>
                    <div class="pk-combobox" id="pkPeraturanBox">
                        <input type="text"
                            class="pk-combobox-input"
                            id="pkPeraturanInput"
                            placeholder="Tambahkan Peraturan"
                            autocomplete="off">

                        <div class="pk-combobox-dropdown">
                            <div class="pk-combobox-options">
                                <?php foreach ($listPeraturan as $p): ?>
                                    <?php if ($p['is_default'] !== 't'): ?>
                                        <div class="pk-option"
                                            data-value="<?= $p['id_peraturan'] ?>">
                                            <?= esc($p['nama_peraturan']) ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div id="pkPeraturanTags" class="pk-law-list mt-3">

                    <?php $loopIndex = 0; ?>

                    <?php foreach (($selectedPeraturan ?? []) as $p): ?>

                        <?php $loopIndex++; ?>

                        <div class="pk-law-item"
                            data-id="<?= $p['id_peraturan'] ?>">

                            <div class="pk-law-number">
                                <?= $loopIndex ?>.
                            </div>

                            <div class="pk-law-title">
                                <?= esc($p['nama_peraturan']) ?>
                            </div>

                            <?php if (!$isViewMode): ?>
                                <span class="pk-tag-remove">×</span>
                            <?php endif; ?>

                            <input
                                type="hidden"
                                name="peraturan[]"
                                value="<?= $p['id_peraturan'] ?>">

                        </div>

                    <?php endforeach; ?>

                </div>
            </div>
        </div>
    </div>

    <?php
    $user = session('user');

    $canEdit = false;

    if (($user['role'] ?? null) === 'admin') {
        $canEdit = true;
    }

    if (
        ($user['role'] ?? null) === 'operator'
        &&
        !empty($activeKonteks)
    ) {
        // Semua tim yang dimiliki user
        $userTim = $user['tim'] ?? [];

        // Fallback untuk akun/session lama
        if (empty($userTim) && !empty($user['id_tim'])) {
            $userTim = [$user['id_tim']];
        }

        $userTim = array_map('intval', $userTim);

        $canEdit = in_array(
            (int)$activeKonteks['id_tim'],
            $userTim,
            true
        );
    }
    ?>

    <div class="d-flex justify-content-end gap-2 py-4">

        <?php if ($mode === 'view' && $canEdit): ?>
            <a
                href="<?= site_url('penetapan-konteks/konteks/' . $activeKonteks['id_konteks'] . '/edit') ?>"
                class="btn btn-warning">
                Edit
            </a>
        <?php endif; ?>

        <?php if (in_array($mode, ['create', 'edit'])): ?>
            <button type="submit" class="btn btn-primary">
                Simpan
            </button>
        <?php endif; ?>

    </div>
</form>

<?= view('penetapan_konteks/tabs/proses_bisnis/_offcanvas_form') ?>