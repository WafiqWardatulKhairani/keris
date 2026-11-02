<div class="card border-0 shadow-sm" id="prTableCard">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table rtp-table align-middle mb-0">

                <thead>
                    <tr class="rtp-thead-main">
                        <th rowspan="2" class="rtp-th text-center">#</th>
                        <th rowspan="2" class="rtp-th text-center">Kode<br>Proses</th>
                        <th rowspan="2" class="rtp-th">Risiko</th>
                        <th rowspan="2" class="rtp-th">RTP</th>
                        <th colspan="2" class="rtp-th text-center">Target</th>
                        <th colspan="3" class="rtp-th text-center">Realisasi</th>
                        <th rowspan="2" class="rtp-th text-center">Keterjadian Risiko</th>
                        <th rowspan="2" class="rtp-th text-center">Jumlah Keterjadian</th>
                    </tr>

                    <tr class="rtp-thead-sub">
                        <th class="rtp-th text-center">Output</th>
                        <th class="rtp-th text-center">Waktu</th>
                        <th class="rtp-th text-center">Output</th>
                        <th class="rtp-th text-center">Waktu</th>
                        <th class="rtp-th text-center">Status</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($grouped)): ?>
                        <tr>
                            <td colspan="11" class="text-center py-5 text-muted">
                                <i class="ti ti-inbox fs-3 d-block mb-2 opacity-25"></i>
                                Belum ada data.
                            </td>
                        </tr>
                    <?php else: ?>

                        <?php $no = ($from ?? 1); ?>

                        <?php foreach ($grouped as $item): ?>

                            <?php
                            $rtpList = $item['pemantauan_list'] ?? [];
                            $rowspan = max(count($rtpList), 1);
                            ?>

                            <?php foreach ($rtpList as $i => $rtp): ?>

                                <?php
                                $isFirst = ($i === 0);

                                // TARGET
                                $bulan = $tahun = null;
                                if (!empty($rtp['target_waktu'])) {
                                    $ts = strtotime($rtp['target_waktu']);
                                    $bulan = date('M', $ts);
                                    $tahun = date('Y', $ts);
                                }

                                // REALISASI
                                $rBulan = $rTahun = null;
                                if (!empty($rtp['realisasi_waktu'])) {
                                    $ts2 = strtotime($rtp['realisasi_waktu']);
                                    $rBulan = date('M', $ts2);
                                    $rTahun = date('Y', $ts2);
                                }

                                // STATUS
                                $status = $rtp['status'] ?? 'Belum Dilaksanakan';

                                $statusClass = match ($status) {
                                    'Selesai'            => 'success',
                                    'Dalam Proses'       => 'primary',
                                    'Terlambat'          => 'danger',
                                    'Belum Dilaksanakan' => 'secondary',
                                    default              => 'secondary'
                                };
                                ?>

                                <tr class="rtp-row" data-rtp="<?= esc($rtp['id_rtp']) ?>">

                                    <?php if ($isFirst): ?>
                                        <td rowspan="<?= $rowspan ?>" class="text-center">
                                            <?= $no++ ?>
                                        </td>

                                        <td rowspan="<?= $rowspan ?>" class="text-center">
                                            <span class="badge bg-primary-subtle text-primary">
                                                <?= esc($item['kode_proses']) ?>
                                            </span>
                                        </td>

                                        <td rowspan="<?= $rowspan ?>">
                                            <div class="fw-semibold">
                                                <?= esc($item['pernyataan_risiko']) ?>
                                            </div>
                                            <div class="text-muted small">
                                                → <?= esc($item['uraian_proses']) ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>

                                    <!-- RTP -->
                                    <td><?= esc($rtp['uraian_rtp']) ?></td>

                                    <!-- TARGET OUTPUT -->
                                    <td><?= esc($rtp['target_output'] ?? '—') ?></td>

                                    <!-- TARGET WAKTU -->
                                    <td class="text-center">
                                        <?php if ($bulan && $tahun): ?>
                                            <div><?= $bulan ?></div>
                                            <div class="small text-muted"><?= $tahun ?></div>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- REALISASI OUTPUT -->
                                    <td class="<?= !empty($rtp['realisasi_output']) ? 'fw-semibold' : '' ?>">
                                        <?= esc($rtp['realisasi_output'] ?? '—') ?>
                                    </td>

                                    <!-- REALISASI WAKTU -->
                                    <td class="text-center <?= !empty($rtp['realisasi_waktu']) ? 'fw-semibold' : '' ?>">
                                        <?php if ($rBulan && $rTahun): ?>
                                            <div><?= $rBulan ?></div>
                                            <div class="small text-muted"><?= $rTahun ?></div>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- STATUS -->
                                    <td class="text-center">
                                        <span class="badge bg-<?= $statusClass ?>-subtle text-<?= $statusClass ?> border border-<?= $statusClass ?>">
                                            <?= esc($status) ?>
                                        </span>
                                    </td>

                                    <?php if ($isFirst): ?>
                                        <!-- KETERJADIAN RISIKO -->
                                        <td rowspan="<?= $rowspan ?>" class="text-center">
                                            <select
                                                class="form-select form-select-sm pr-keterjadian-select"
                                                data-id-identifikasi="<?= esc($item['id_identifikasi']) ?>"
                                                <?= !in_array(session('user_role'), ['admin', 'operator'], true) ? 'disabled' : '' ?>>
                                                <option value="T" <?= ($item['keterjadian_risiko'] ?? 'TT') === 'T' ? 'selected' : '' ?>>
                                                    Terjadi
                                                </option>
                                                <option value="TT" <?= ($item['keterjadian_risiko'] ?? 'TT') === 'TT' ? 'selected' : '' ?>>
                                                    Tidak Terjadi
                                                </option>
                                                <option value="BT" <?= ($item['keterjadian_risiko'] ?? 'TT') === 'BT' ? 'selected' : '' ?>>
                                                    Belum Terjadi
                                                </option>
                                            </select>
                                        </td>

                                        <!-- JUMLAH KETERJADIAN -->
                                        <td rowspan="<?= $rowspan ?>" class="text-center">
                                            <input
                                                type="number"
                                                min="0"
                                                class="form-control form-control-sm pr-jumlah-keterjadian"
                                                data-id-identifikasi="<?= esc($item['id_identifikasi']) ?>"
                                                value="<?= esc($item['jumlah_keterjadian'] ?? 0) ?>"
                                                placeholder="0"
                                                <?= ($item['keterjadian_risiko'] ?? 'TT') !== 'T' ? 'disabled' : '' ?>
                                                <?= !in_array(session('user_role'), ['admin', 'operator'], true) ? 'disabled' : '' ?>>
                                        </td>
                                    <?php endif; ?>

                                </tr>

                            <?php endforeach; ?>

                        <?php endforeach; ?>

                    <?php endif; ?>
                </tbody>

            </table>
        </div>
    </div>

    <?php if (!empty($grouped)): ?>
        <div class="pr-table-bottom">

            <div class="pr-table-info">

                <form method="get" class="pr-perpage-form" id="prPerPageForm">
                    <input type="hidden" name="filter" value="<?= esc($filter ?? '') ?>">

                    <select name="perPage" class="pr-perpage"
                        onchange="document.getElementById('prPerPageForm').submit()">

                        <?php foreach ([5, 10, 25, 50] as $size): ?>
                            <option value="<?= $size ?>" <?= ($perPage ?? 10) == $size ? 'selected' : '' ?>>
                                <?= $size ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </form>

                <div class="pr-info-text">
                    Menampilkan <?= $from ?? 0 ?>–<?= $to ?? 0 ?> dari <?= $total ?? 0 ?> data
                </div>
            </div>

            <?php if (!empty($pager) && $pager['totalPages'] > 1): ?>
                <div class="pr-pagination">
                    <ul class="pagination mb-0">

                        <li class="page-item <?= $pager['currentPage'] <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="#"
                                onclick="prGoToPage(<?= $pager['currentPage'] - 1 ?>); return false;">
                                &laquo;
                            </a>
                        </li>

                        <?php for ($i = 1; $i <= $pager['totalPages']; $i++): ?>
                            <li class="page-item <?= $i === $pager['currentPage'] ? 'active' : '' ?>">
                                <a class="page-link" href="#"
                                    onclick="prGoToPage(<?= $i ?>); return false;">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= $pager['currentPage'] >= $pager['totalPages'] ? 'disabled' : '' ?>">
                            <a class="page-link" href="#"
                                onclick="prGoToPage(<?= $pager['currentPage'] + 1 ?>); return false;">
                                &raquo;
                            </a>
                        </li>

                    </ul>
                </div>
            <?php endif; ?>

        </div>
    <?php endif; ?>
</div>