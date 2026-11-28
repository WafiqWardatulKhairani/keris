<?php
$totalTim = count(array_unique(array_column($data, 'id_tim')));
$totalKegiatan = count(array_unique(array_column($data, 'id_kegiatan')));
$totalDraft = count(array_filter($data, function ($row) {
    return ($row['status'] ?? '') !== 'lengkap';
}));
$totalLengkap = count(array_filter($data, function ($row) {
    return ($row['status'] ?? '') === 'lengkap';
}));
?>

<div class="pk-summary-grid">
    <div class="pk-summary-card">
        <div class="pk-summary-icon bg-primary-subtle text-primary">
            <i class="ti ti-users"></i>
        </div>
        <div class="pk-summary-content">
            <div class="pk-summary-label">Tim Kerja</div>
            <div class="pk-summary-value"><?= $totalTim ?></div>
            <div class="pk-summary-subtext">Tim Kerja</div>
        </div>
    </div>

    <div class="pk-summary-card">
        <div class="pk-summary-icon bg-success-subtle text-success">
            <i class="ti ti-calendar-event"></i>
        </div>
        <div class="pk-summary-content">
            <div class="pk-summary-label">Kegiatan</div>
            <div class="pk-summary-value"><?= $totalKegiatan ?></div>
            <div class="pk-summary-subtext">Kegiatan</div>
        </div>
    </div>

    <div class="pk-summary-card">
        <div class="pk-summary-icon bg-warning-subtle text-warning">
            <i class="ti ti-file-text"></i>
        </div>
        <div class="pk-summary-content">
            <div class="pk-summary-label">Draft</div>
            <div class="pk-summary-value"><?= $totalDraft ?></div>
            <div class="pk-summary-subtext">Ruang Lingkup</div>
        </div>
    </div>

    <div class="pk-summary-card">
        <div class="pk-summary-icon bg-success-subtle text-success">
            <i class="ti ti-circle-check"></i>
        </div>
        <div class="pk-summary-content">
            <div class="pk-summary-label">Ada Konteks</div>
            <div class="pk-summary-value"><?= $totalLengkap ?></div>
            <div class="pk-summary-subtext">Ruang Lingkup</div>
        </div>
    </div>
</div>

<?= view('penetapan_konteks/tabs/ruang_lingkup/_table_section', [
    'data'    => $data, 
    'pager'   => $pager ?? null,
    'from'    => $from ?? 0,
    'to'      => $to ?? 0,
    'total'   => $total ?? 0,
    'perPage' => $perPage ?? 5,
    'filters' => $filters ?? [],
]) ?>

<div class="d-flex align-items-center gap-4 mt-3 px-3" style="font-size: 12px;">
    <span class="fw-bold text-secondary">Keterangan Status:</span>

    <div class="d-flex align-items-center gap-2">
        <span class="pk-status-badge pk-badge-lengkap">
            <span class="pk-badge-dot"></span>
            Ada Konteks
        </span>
        <span class="text-muted">= Ruang lingkup sudah memiliki konteks</span>
    </div>

    <div class="d-flex align-items-center gap-2">
        <span class="pk-status-badge pk-badge-draft">
            <span class="pk-badge-dot"></span>
            Draft
        </span>
        <span class="text-muted">= Ruang lingkup dibuat, konteks belum ditetapkan</span>
    </div>
</div>