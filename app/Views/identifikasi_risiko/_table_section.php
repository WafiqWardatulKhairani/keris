<?php if (empty($data)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5 text-muted">
            <i class="ti ti-database fs-1 mb-2 d-block opacity-25"></i>
            <p class="mb-0">
                <?= $activeKonteks
                    ? 'Belum ada identifikasi risiko untuk konteks ini.'
                    : 'Belum ada data identifikasi risiko.' ?>
            </p>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 pk-konteks-table" style="font-size: 12.5px;">
                    <thead class="table-light">
                        <tr>
                            <th width="50" class="text-center py-2" style="font-size: 12px;">#</th>
                            <th width="90" class="py-2" style="font-size: 12px;">Kode</th>
                            <th width="200" class="py-2" style="font-size: 12px;">Proses Bisnis</th>
                            <th class="py-2" style="font-size: 12px;">Pernyataan Risiko</th>
                            <th width="150" class="py-2" style="font-size: 12px;">Kategori</th>
                            <th width="220" class="py-2" style="font-size: 12px;">Area Dampak</th>
                            <th width="110" class="py-2" style="font-size: 12px;">Sumber</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = $from ?? 1; ?>
                        <?php foreach ($data as $row): ?>
                            <tr class="ir-row table-row-click"
                                data-id="<?= $row['id_identifikasi'] ?>"
                                data-row='<?= json_encode($row) ?>'
                                style="cursor:pointer;">
                                <td class="text-center text-muted py-1.5"><?= $no++ ?></td>
                                <td class="py-1.5">
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary">
                                        <?= esc($row['kode_proses']) ?>
                                    </span>
                                </td>
                                <td class="py-1.5">
                                    <div class="text-truncate" style="max-width: 190px;" title="<?= esc($row['uraian_proses']) ?>">
                                        <?= esc($row['uraian_proses']) ?>
                                    </div>
                                </td>
                                <td class="py-1.5">
                                    <div class="text-truncate" style="max-width: 280px;" title="<?= esc($row['pernyataan_risiko']) ?>">
                                        <?= esc($row['pernyataan_risiko']) ?>
                                    </div>
                                    <?php if (!empty($row['penyebab_risiko'])): ?>
                                        <div class="text-muted small text-truncate" style="max-width: 280px;" title="Penyebab: <?= esc($row['penyebab_risiko']) ?>">
                                            <i class="ti ti-arrow-right me-1"></i><?= esc($row['penyebab_risiko']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-1.5">
                                    <?php if (!empty($row['nama_kategori'])): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary">
                                            <?= esc($row['nama_kategori']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-1.5">
                                    <?php if (!empty($row['area_dampak_list'])): ?>
                                        <?php foreach (explode(', ', $row['area_dampak_list']) as $area): ?>
                                            <span class="badge bg-success-subtle text-success border border-success me-1 mb-1">
                                                <?= esc(trim($area)) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-1.5">
                                    <?php if ($row['sumber_risiko'] === 'Internal'): ?>
                                        <span class="badge bg-info-subtle text-info border border-info">Internal</span>
                                    <?php elseif ($row['sumber_risiko'] === 'Eksternal'): ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning">Eksternal</span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- ======== BOTTOM BAR (selalu muncul jika data ada) ======== -->
            <div class="pk-table-bottom">
                <div class="pk-table-info">
                    <form method="get" class="d-flex align-items-center" id="irPerPageForm">
                        <!-- Bawa semua filter yang ada di URL -->
                        <?php
                        $getParams = $_GET;
                        unset($getParams['perPage'], $getParams['page']);
                        foreach ($getParams as $key => $value):
                            if (is_array($value)) {
                                foreach ($value as $v) {
                                    echo '<input type="hidden" name="' . esc($key) . '[]" value="' . esc($v) . '">';
                                }
                            } else {
                                echo '<input type="hidden" name="' . esc($key) . '" value="' . esc($value) . '">';
                            }
                        endforeach;
                        ?>
                        <select name="perPage"
                                class="pk-perpage-select"
                                onchange="document.getElementById('irPerPageForm').submit();">
                            <?php foreach ([5, 10, 25, 50] as $size): ?>
                                <option value="<?= $size ?>" <?= ($perPage ?? 10) == $size ? 'selected' : '' ?>>
                                    <?= $size ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                    <div class="pk-info-text">
                        Menampilkan <?= $from ?? 0 ?> – <?= $to ?? 0 ?> dari <?= $total ?? 0 ?> data
                    </div>
                </div>

                <div class="pk-pagination-wrapper">
                    <ul class="pagination mb-0">
                        <?php
                        $currentPage = $pager['currentPage'] ?? 1;
                        $totalPages  = $pager['totalPages'] ?? 1;
                        $queryString = $_GET;
                        unset($queryString['page']);
                        ?>
                        <!-- Prev -->
                        <?php if ($currentPage <= 1): ?>
                            <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                        <?php else:
                            $queryString['page'] = $currentPage - 1;
                            ?>
                            <li class="page-item"><a class="page-link" href="?<?= http_build_query($queryString) ?>">&laquo;</a></li>
                        <?php endif; ?>

                        <!-- Nomor halaman dengan elipsis -->
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == 1 || $i == $totalPages || abs($i - $currentPage) <= 1): ?>
                                <?php if ($i == $currentPage): ?>
                                    <li class="page-item active"><span class="page-link"><?= $i ?></span></li>
                                <?php else:
                                    $queryString['page'] = $i;
                                    ?>
                                    <li class="page-item"><a class="page-link" href="?<?= http_build_query($queryString) ?>"><?= $i ?></a></li>
                                <?php endif; ?>
                            <?php elseif ($i == 2 || $i == $totalPages - 1): ?>
                                <li class="page-item disabled"><span class="page-link">…</span></li>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <!-- Next -->
                        <?php if ($currentPage >= $totalPages): ?>
                            <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                        <?php else:
                            $queryString['page'] = $currentPage + 1;
                            ?>
                            <li class="page-item"><a class="page-link" href="?<?= http_build_query($queryString) ?>">&raquo;</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    // Fungsi AJAX untuk mengganti halaman tanpa reload (opsional, tetap pakai)
    function irGoToPage(page) {
        const url = new URL(window.location.href);
        url.searchParams.set('page', page);
        url.searchParams.set('perPage', <?= $perPage ?? 10 ?>);

        const wrapper = document.getElementById('irTableWrapper');
        const scrollY = window.scrollY;

        fetch(url.toString())
            .then(r => r.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newCard = doc.querySelector('.card');
                if (newCard && wrapper) {
                    wrapper.innerHTML = newCard.outerHTML;
                    window.history.pushState({}, '', url.toString());
                    window.scrollTo({ top: scrollY });
                }
            });
    }

    // Event delegation untuk klik baris (buka form edit)
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.ir-row').forEach(row => {
            row.addEventListener('click', function(e) {
                const id = this.dataset.id;
                if (id) {
                    // panggil fungsi global dari risiko.js
                    if (typeof openEditRisiko === 'function') {
                        openEditRisiko(id);
                    } else {
                        console.warn('openEditRisiko not defined');
                    }
                }
            });
        });
    });
</script>