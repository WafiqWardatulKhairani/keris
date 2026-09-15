<?php
$konteksMap = [];
$timKerjaOpt = [];
$pengelolaOpt = [];
foreach ($listKonteks as $k) {
    $id = $k['id_konteks'];
    $konteksMap[$id] = [
        'id_tim' => $k['id_tim'] ?? '',
        'pengelola_risiko_id' => $k['pengelola_risiko_id'] ?? '',
        'nama_pengelola' => $k['nama_pengelola'] ?? '',
        'id_kegiatan' => $k['id_kegiatan'] ?? '',
        'nama_kegiatan' => $k['nama_kegiatan'] ?? '',
        'tahun' => $k['tahun'],
    ];
    if (!empty($k['id_tim'])) $timKerjaOpt[$k['id_tim']] = $k['nama_tim'];
    if (!empty($k['pengelola_risiko_id'])) $pengelolaOpt[$k['pengelola_risiko_id']] = $k['nama_pengelola'];
}
asort($timKerjaOpt);
asort($pengelolaOpt);

$sel = $activeKonteks ?? [];
$currentMonth = $periode['bulan'] ?? date('m');
$currentYear = $periode['tahun'] ?? date('Y');
$currentPeriode = $currentYear . '-' . $currentMonth;
$type = $tipe_periode ?? 'bulanan';
?>

<div class="pl-filter-card mb-3">
    <form id="plContextSelectorForm" method="get" action="<?= site_url('pelaporan-risiko') ?>">
        <input type="hidden" name="id_konteks" id="plCsIdKonteks">
        <div class="pl-filter-inner">

            <div class="pl-filter-section pl-filter-context">
                <div class="pl-section-label"><i class="ti ti-building"></i> Konteks</div>
                <div class="pl-field-group">
                    <div class="pl-field">
                        <span class="pl-field-label">Tim Kerja</span>
                        <div class="pl-field-value">
                            <?php if ($userRole === 'admin'): ?>
                                <select class="pl-select" id="plCsTimKerja" name="id_tim">
                                    <option value="">– Pilih Tim –</option>
                                    <?php foreach ($timKerjaOpt as $id => $nama): ?>
                                        <option value="<?= $id ?>" <?= ($sel['id_tim'] ?? '') == $id ? 'selected' : '' ?>>
                                            <?= esc($nama) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <span class="pl-field-static">
                                    <?= esc($ketuaInfo['nama_tim'] ?? '-') ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="pl-field">
                        <span class="pl-field-label">Pengelola</span>
                        <div class="pl-field-value">
                            <?php if ($userRole === 'admin'): ?>
                                <select class="pl-select" id="plCsPengelola" name="pengelola_risiko_id">
                                    <option value="">– Pilih Pengelola –</option>
                                    <?php foreach ($pengelolaOpt as $id => $nama): ?>
                                        <option value="<?= $id ?>" <?= ($sel['pengelola_risiko_id'] ?? '') == $id ? 'selected' : '' ?>>
                                            <?= esc($nama) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <span class="pl-field-static">
                                    <?= esc($ketuaInfo['nama'] ?? '-') ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="pl-field">
                        <span class="pl-field-label">Kegiatan</span>
                        <div class="pl-field-value">
                            <select class="pl-select" id="plCsKegiatan" name="id_kegiatan">
                                <option value="">Semua Kegiatan</option>
                            </select>
                        </div>
                    </div>

                    <?php if (in_array($userRole, ['operator', 'ketua'])): ?>
                        <div class="pl-field">
                            <span class="pl-field-label">Status Validasi</span>

                            <div class="pl-field-value">
                                <select
                                    class="pl-select"
                                    id="plCsStatusValidasi"
                                    name="status_validasi">

                                    <option value="">Semua Status</option>

                                    <?php if (($userRole ?? '') !== 'ketua'): ?>
                                        <option value="Draft"
                                            <?= ($statusValidasi ?? '') === 'Draft' ? 'selected' : '' ?>>
                                            Draft
                                        </option>
                                    <?php endif; ?>

                                    <option value="Diajukan"
                                        <?= ($statusValidasi ?? '') === 'Diajukan' ? 'selected' : '' ?>>
                                        Diajukan
                                    </option>

                                    <option value="Disetujui"
                                        <?= ($statusValidasi ?? '') === 'Disetujui' ? 'selected' : '' ?>>
                                        Disetujui
                                    </option>

                                    <option value="Ditolak"
                                        <?= ($statusValidasi ?? '') === 'Ditolak' ? 'selected' : '' ?>>
                                        Ditolak
                                    </option>

                                </select>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="pl-filter-divider"></div>

            <div class="pl-filter-section pl-filter-periode">
                <div class="pl-section-label"><i class="ti ti-calendar"></i> Periode</div>

                <div class="pl-periode-tipe">
                    <span class="pl-field-label">Tipe</span>
                    <div class="pl-field-value">
                        <select name="tipe_periode" id="plCsType" class="pl-select">
                            <option value="bulanan" <?= $type === 'bulanan' ? 'selected' : '' ?>>Bulanan</option>
                            <option value="range" <?= $type === 'range' ? 'selected' : '' ?>>Range Bulan</option>
                        </select>
                    </div>
                </div>

                <div class="pl-periode-slot">
                    <div id="plSingle" class="pl-periode-slot-inner">
                        <div class="pl-slot-label">Bulan</div>
                        <input type="month" name="periode" id="plCsPeriode" class="pl-slot-input" value="<?= $currentPeriode ?>">
                    </div>
                    <div id="plRange" class="pl-periode-slot-inner" style="display:none;">
                        <div class="pl-range-row">
                            <div class="pl-range-col">
                                <div class="pl-slot-label">Dari</div>
                                <input type="month" name="start_periode" id="plStart" class="pl-slot-input">
                            </div>
                            <div class="pl-range-sep">→</div>
                            <div class="pl-range-col">
                                <div class="pl-slot-label">Sampai</div>
                                <input type="month" name="end_periode" id="plEnd" class="pl-slot-input">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="pl-btn-submit">
                    <i class="ti ti-search"></i> Tampilkan
                </button>
            </div>

        </div>
    </form>
</div>

<script>
    window.PL_CS_DATA = {
        konteksMap: <?= json_encode($konteksMap) ?>,
        listKegiatan: <?= json_encode($listKegiatan) ?>,
        activeTimId: <?= json_encode($sel['id_tim'] ?? session('id_tim')) ?>,
        selectedKegiatan: <?= json_encode($selectedKegiatan ?? '') ?>
    };
</script>