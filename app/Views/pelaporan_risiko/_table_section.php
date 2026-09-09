<div class="card border-0 shadow-sm" id="plTableCard">
    <div class="card-body">

        <div class="ar-table-scroll">
            <table class="table table-hover align-middle mb-0 pl-report-table" id="plTable">
                <thead class="table-light">
                    <tr>
                        <th rowspan="2" style="width:50px">#</th>
                        <th rowspan="2" style="width:24%">Risiko</th>
                        <th rowspan="2" style="width:24%">RTP</th>
                        <th colspan="2" class="text-center" style="width:22%">Target</th>
                        <th colspan="3" class="text-center" style="width:30%">Realisasi</th>
                    </tr>
                    <tr>
                        <th style="width:14%">Output</th>
                        <th style="width:8%" class="text-center">Waktu</th>
                        <th style="width:14%">Output</th>
                        <th style="width:8%" class="text-center">Waktu</th>
                        <th style="width:8%" class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="ti ti-inbox fs-3 d-block mb-2 opacity-25"></i>
                                Tidak ada data pelaporan risiko.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php
                        $no = $from ?? 1;
                        $lastKegiatan = null;

                        foreach ($data as $row):
                            $currentKegiatan = $row['nama_kegiatan'] ?? 'Tanpa Kegiatan';
                            if ($currentKegiatan !== $lastKegiatan):
                        ?>
                                <tr class="pl-kegiatan-separator">
                                    <td colspan="8">

                                        <div class="d-flex justify-content-between align-items-center gap-3">

                                            <!-- LEFT -->
                                            <div class="d-flex align-items-center gap-2 flex-wrap">

                                                <div class="pl-kegiatan-title">
                                                    <i class="ti ti-folders me-2"></i>
                                                    <?= esc($currentKegiatan) ?>
                                                </div>

                                                <?php
                                                $statusValidasi = $row['status_validasi'] ?? 'Draft';

                                                $badgeClass = match ($statusValidasi) {
                                                    'Diajukan'  => 'bg-warning-subtle text-warning-emphasis border border-warning',
                                                    'Disetujui' => 'bg-success-subtle text-success border border-success',
                                                    'Ditolak'   => 'bg-danger-subtle text-danger border border-danger',
                                                    default     => 'bg-secondary-subtle text-secondary border border-secondary',
                                                };
                                                ?>

                                                <span class="badge <?= $badgeClass ?>">
                                                    <?= esc($statusValidasi) ?>
                                                </span>

                                            </div>

                                            <!-- RIGHT -->
                                            <div class="d-flex align-items-center gap-2 flex-shrink-0">

                                                <?php if (
                                                    ($statusValidasi ?? '') === 'Ditolak'
                                                    && !empty($row['catatan_validasi'])
                                                ): ?>

                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="event.stopPropagation(); plShowCatatan(`<?= esc($row['catatan_validasi']) ?>`)">

                                                        <i class="ti ti-message-circle-exclamation me-1"></i>
                                                        Catatan
                                                    </button>

                                                <?php endif; ?>


                                                <?php if (($userRole ?? '') === 'operator'): ?>

                                                    <?php if ($statusValidasi === 'Diajukan'): ?>

                                                        <button
                                                            class="btn btn-sm btn-outline-danger"
                                                            onclick="event.stopPropagation(); plBatalAjukanKegiatan(<?= (int)$row['id_kegiatan'] ?>)">
                                                            <i class="ti ti-arrow-back-up me-1"></i>
                                                            Batalkan Pengajuan
                                                        </button>

                                                    <?php elseif ($statusValidasi === 'Disetujui'): ?>

                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-success"
                                                            disabled>
                                                            <i class="ti ti-circle-check me-1"></i>
                                                            Sudah Disetujui
                                                        </button>

                                                    <?php else: ?>

                                                        <button
                                                            class="btn btn-sm btn-primary"
                                                            onclick="event.stopPropagation(); plAjukanKegiatan(<?= (int)$row['id_kegiatan'] ?>)">
                                                            <i class="ti ti-send me-1"></i>
                                                            Ajukan ke Ketua
                                                        </button>

                                                    <?php endif; ?>

                                                <?php endif; ?>


                                                <?php if (
                                                    ($userRole ?? '') === 'ketua'
                                                    && $statusValidasi === 'Diajukan'
                                                ): ?>

                                                    <button
                                                        class="btn btn-sm btn-success"
                                                        onclick="event.stopPropagation(); plApproveKegiatan(<?= (int)$row['id_kegiatan'] ?>)">
                                                        <i class="ti ti-check me-1"></i>
                                                        Setujui
                                                    </button>

                                                    <button
                                                        class="btn btn-sm btn-danger"
                                                        onclick="event.stopPropagation(); plRejectKegiatan(<?= (int)$row['id_kegiatan'] ?>)">
                                                        <i class="ti ti-x me-1"></i>
                                                        Tolak
                                                    </button>

                                                <?php endif; ?>

                                            </div>

                                        </div>

                                    </td>
                                </tr>
                            <?php
                                $lastKegiatan = $currentKegiatan;

                            endif;
                            $status = $row['status'] ?? 'Belum Dilaksanakan';
                            $badge  = match ($status) {
                                'Selesai'      => 'success',
                                'Dalam Proses' => 'warning',
                                'Terlambat'    => 'danger',
                                default        => 'secondary',
                            };
                            ?>

                            <tr class="pl-row"
                                data-id="<?= esc($row['id_rtp']) ?>"
                                style="cursor:pointer">

                                <!-- NO -->
                                <td><?= $no++ ?></td>

                                <!-- RISIKO -->
                                <td class="pl-col-risiko">
                                    <div class="fw-semibold text-truncate pl-truncate"
                                        title="<?= esc($row['pernyataan_risiko']) ?>">
                                        <?= esc($row['pernyataan_risiko']) ?>
                                    </div>

                                    <?php if (!empty($row['nama_tim'])): ?>
                                        <div class="text-muted text-truncate small pl-truncate"
                                            title="<?= esc($row['nama_tim']) ?>">
                                            <i class="ti ti-building me-1"></i><?= esc($row['nama_tim']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- RTP -->
                                <td class="pl-col-rtp">
                                    <div class="text-truncate pl-truncate"
                                        title="<?= esc($row['uraian_rtp']) ?>">
                                        <?= esc($row['uraian_rtp']) ?>
                                    </div>
                                </td>

                                <!-- TARGET OUTPUT -->
                                <td>
                                    <div class="text-truncate small pl-truncate" style="font-size:0.85rem"
                                        title="<?= esc($row['target_output']) ?>">
                                        <?= esc($row['target_output'] ?? '-') ?>
                                    </div>
                                </td>

                                <!-- TARGET WAKTU -->
                                <td class="text-center">
                                    <?php if (!empty($row['target_waktu'])): ?>
                                        <?php
                                        $ts = strtotime($row['target_waktu']);
                                        ?>
                                        <div class="fw-semibold"><?= date('M', $ts) ?></div>
                                        <div class="text-muted small"><?= date('Y', $ts) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>

                                <!-- REALISASI OUTPUT -->
                                <td>
                                    <div class="text-truncate small pl-truncate" style="font-size:0.85rem"
                                        title="<?= esc($row['realisasi_output'] ?? '') ?>">
                                        <?= esc($row['realisasi_output'] ?? '-') ?>
                                    </div>
                                </td>

                                <!-- REALISASI WAKTU -->
                                <td class="text-center">
                                    <?php if (!empty($row['realisasi_waktu'])): ?>
                                        <?php
                                        $ts = strtotime($row['realisasi_waktu']);
                                        ?>
                                        <div class="fw-semibold"><?= date('M', $ts) ?></div>
                                        <div class="text-muted small"><?= date('Y', $ts) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>

                                <!-- STATUS -->
                                <td class="text-center">
                                    <span class="badge bg-<?= $badge ?>-subtle text-<?= $badge ?> border border-<?= $badge ?>">
                                        <?= esc($status) ?>
                                    </span>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>
        </div>

    </div><!-- /.card-body -->

    <!-- BOTTOM: per-page + info + pagination -->
    <?php if (!empty($data)): ?>
        <div class="ar-table-bottom">

            <!-- PER PAGE + INFO -->
            <div class="ar-table-info">
                <form method="get" class="ar-perpage-form" id="plPerPageForm">
                    <select name="perPage" class="ar-perpage"
                        onchange="document.getElementById('plPerPageForm').submit()">
                        <?php foreach ([5, 10, 25, 50] as $size): ?>
                            <option value="<?= $size ?>"
                                <?= ($perPage ?? 10) == $size ? 'selected' : '' ?>>
                                <?= $size ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <div class="ar-info-text">
                    Menampilkan <?= $from ?? 1 ?>–<?= $to ?? count($data) ?>
                    dari <?= $total ?? count($data) ?> data
                </div>
            </div>

            <!-- PAGINATION -->
            <?php if (isset($pager)): ?>
                <div class="ar-pagination">
                    <ul class="pagination mb-0">

                        <li class="page-item <?= $pager['currentPage'] <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="#"
                                onclick="plGoToPage(<?= $pager['currentPage'] - 1 ?>); return false;">
                                &laquo;
                            </a>
                        </li>

                        <?php
                        $cur         = $pager['currentPage'];
                        $total_pages = $pager['totalPages'];
                        $shown       = [];
                        for ($i = 1; $i <= $total_pages; $i++) {
                            if ($i === 1 || $i === $total_pages || abs($i - $cur) <= 2) {
                                $shown[] = $i;
                            }
                        }
                        $prev = null;
                        foreach ($shown as $i):
                            if ($prev !== null && $i - $prev > 1): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">…</span>
                                </li>
                            <?php endif; ?>
                            <li class="page-item <?= $i === $cur ? 'active' : '' ?>">
                                <a class="page-link"
                                    href="#"
                                    onclick="plGoToPage(<?= $i ?>); return false;">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php $prev = $i;
                        endforeach; ?>

                        <li class="page-item <?= $cur >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="#"
                                onclick="plGoToPage(<?= $cur + 1 ?>); return false;">
                                &raquo;
                            </a>
                        </li>

                    </ul>
                </div>
            <?php endif; ?>

        </div>
    <?php endif; ?>

</div>

<script>
    function plGoToPage(page) {
        const url = new URL(window.location.href);

        url.searchParams.set('page', page);
        url.searchParams.set('perPage', <?= $perPage ?? 10 ?>);

        // preserve filter
        const periode = document.getElementById('plCsPeriode');
        const type = document.getElementById('plCsType');
        const tim = document.getElementById('plCsTimKerja');
        const pengelola = document.getElementById('plCsPengelola');
        const kegiatan = document.getElementById('plCsKegiatan');
        const start = document.getElementById('plStart');
        const end = document.getElementById('plEnd');

        if (periode) url.searchParams.set('periode', periode.value);
        if (type) url.searchParams.set('tipe_periode', type.value);
        if (tim) url.searchParams.set('id_tim', tim.value);
        if (pengelola) url.searchParams.set('pengelola_risiko_id', pengelola.value);
        if (kegiatan) url.searchParams.set('id_kegiatan', kegiatan.value);
        if (start) url.searchParams.set('start_periode', start.value);
        if (end) url.searchParams.set('end_periode', end.value);

        const wrapper = document.getElementById('plTableCard');
        const scrollY = window.scrollY;

        fetch(url.toString())
            .then(r => r.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newCard = doc.getElementById('plTableCard');

                if (newCard && wrapper) {
                    wrapper.outerHTML = newCard.outerHTML;

                    window.history.pushState({}, '', url.toString());

                    window.scrollTo({
                        top: scrollY
                    });
                }
            });
    }
</script>