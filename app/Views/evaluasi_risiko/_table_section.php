<div class="card border-0 shadow-sm" id="erTableCard">
    <div class="card-body p-0">
        <div class="ar-table-scroll">
            <table class="table table-hover align-middle mb-0" id="erTable" style="font-size:12.5px;">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px" class="text-center py-2">#</th>
                        <th style="width:60px" class="text-center py-2">Kode<br>Proses</th>
                        <th class="py-2">Risiko</th>
                        <th class="text-center py-2" style="width:100px">Skor<br>Risiko</th>
                        <th class="py-2" style="width:120px">Efektivitas</th>
                        <th class="py-2" style="width:150px">Respon Risiko</th>
                        <th class="py-2" style="width:100px">Prioritas</th>
                        <th class="py-2" style="width:150px">Status</th>
                    </tr> 
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="ti ti-inbox fs-3 d-block mb-2 opacity-25"></i>
                                Belum ada data.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php
                        /* -------------------------------------------------------
                        * PRE-PASS: hitung prioritas otomatis
                        * Berdasarkan skor risiko tertinggi
                        * Jika skor sama → pakai urutan data
                        * ------------------------------------------------------- */
                        $prioritasMap = [];
                        $temp = [];

                        foreach ($data as $r) {
                            if (($r['opsi_tindakan'] ?? '') === 'Mengurangi') {
                                $temp[] = $r;
                            }
                        }

                        /* sort berdasarkan skor risiko DESC */
                        usort($temp, function ($a, $b) {
                            return ($b['nilai_risiko'] ?? 0) <=> ($a['nilai_risiko'] ?? 0);
                        });

                        /* assign prioritas */
                        $prioritasCounter = 1;
                        foreach ($temp as $r) {
                            $prioritasMap[$r['id_identifikasi']] = $prioritasCounter++;
                        }

                        $no = $from ?? 1;
                        foreach ($data as $row):
                            $sudah     = !empty($row['id_evaluasi']);
                            $warna     = $row['warna_risiko'] ?? null;
                            $prefix    = strtoupper(substr($row['kode_proses'] ?? '', 0, 1));
                            $kodeBadge = match ($prefix) {
                                'S'     => 'primary',
                                'K'     => 'warning',
                                default => 'secondary',
                            };
                            $noPrioritas = $prioritasMap[$row['id_identifikasi']] ?? null;
                        ?>
                            <tr class="er-row"
                                data-identifikasi="<?= esc($row['id_identifikasi']) ?>"
                                data-penilaian="<?= esc($row['id_penilaian'] ?? '') ?>"
                                data-evaluasi="<?= esc($row['id_evaluasi'] ?? '') ?>"
                                style="cursor:pointer;">
                                <td class="text-center py-1.5"><?= $no++ ?></td>

                                <!-- KODE PROSES -->
                                <td class="text-center py-1.5">
                                    <span class="badge bg-<?= $kodeBadge ?>-subtle text-<?= $kodeBadge ?> border border-<?= $kodeBadge ?>">
                                        <?= esc($row['kode_proses'] ?? '-') ?>
                                    </span>
                                </td>

                                <!-- RISIKO -->
                                <td class="py-1.5 ar-risiko-cell">
                                    <div class="fw-semibold text-truncate ar-risiko-text"
                                        style="font-size:0.875rem"
                                        title="<?= esc($row['pernyataan_risiko']) ?>">
                                        <?= esc($row['pernyataan_risiko']) ?>
                                    </div>
                                    <?php if (!empty($row['uraian_proses'])): ?>
                                        <div class="text-muted text-truncate ar-risiko-text"
                                            style="font-size:0.78rem"
                                            title="<?= esc($row['uraian_proses']) ?>">
                                            <i class="ti ti-arrow-right me-1"></i><?= esc($row['uraian_proses']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- SKOR RISIKO -->
                                <td class="text-center py-1.5">
                                    <?php if (!empty($row['id_penilaian']) && !empty($row['nilai_risiko'])): ?>
                                        <?php $warnaCell = hex_warna_selera_risiko($row['warna_risiko'] ?? null); ?>
                                        <span class="badge fw-bold px-2 py-1"
                                            style="background-color:<?= $warnaCell ?>;color:#fff;font-size:0.9rem">
                                            <?= esc($row['nilai_risiko']) ?>
                                        </span>
                                        <div class="fw-semibold mt-1"
                                            style="font-size:0.72rem;color:<?= esc($warnaCell) ?>">
                                            <?= esc($row['nama_selera'] ?? '') ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>

                                <!-- EFEKTIVITAS -->
                                <td class="py-1.5">
                                    <?php if (!empty($row['efektivitas'])): ?>
                                        <?php
                                        $efBadge = match ($row['efektivitas']) {
                                            'Efektif'        => 'success',
                                            'Kurang Efektif' => 'warning',
                                            'Tidak Efektif'  => 'danger',
                                            default          => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?= $efBadge ?>-subtle text-<?= $efBadge ?> border border-<?= $efBadge ?>">
                                            <?= esc($row['efektivitas']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>

                                <!-- RESPON RISIKO -->
                                <td class="py-1.5">
                                    <?php if (!empty($row['opsi_tindakan'])): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary">
                                            <?= esc($row['opsi_tindakan']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>

                                <!-- PRIORITAS -->
                                <td class="py-1.5">
                                    <?php if ($noPrioritas !== null): ?>
                                        <?php
                                        $prClass = match (true) {
                                            $noPrioritas === 1 => 'er-prioritas-1',
                                            $noPrioritas === 2 => 'er-prioritas-2',
                                            $noPrioritas === 3 => 'er-prioritas-3',
                                            $noPrioritas === 4 => 'er-prioritas-4',
                                            $noPrioritas === 5 => 'er-prioritas-5',
                                            default            => 'er-prioritas-n',
                                        };
                                        ?>
                                        <span class="badge <?= $prClass ?>">
                                            <?= $noPrioritas ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>

                                <!-- STATUS -->
                                <td class="py-1.5">
                                    <?php if ($sudah): ?>
                                        <span class="badge bg-success-subtle text-success border border-success">
                                            <i class="ti ti-check me-1"></i>Sudah Dievaluasi
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-info-subtle text-info border border-info">
                                            <i class="ti ti-clock me-1"></i>Belum Dievaluasi
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div><!-- /.card-body -->

    <!-- ======== BOTTOM BAR (selalu muncul jika data ada) ======== -->
    <?php if (!empty($data)): ?>
        <div class="ar-table-bottom pk-table-bottom">
            <div class="ar-table-info pk-table-info">
                <form method="get" class="ar-perpage-form d-flex align-items-center" id="erPerPageForm">
                    <!-- Bawa semua parameter GET kecuali perPage dan page -->
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
                    <select name="perPage" class="ar-perpage pk-perpage-select"
                            onchange="document.getElementById('erPerPageForm').submit();">
                        <?php foreach ([5, 10, 25, 50] as $size): ?>
                            <option value="<?= $size ?>" <?= ($perPage ?? 10) == $size ? 'selected' : '' ?>>
                                <?= $size ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <div class="ar-info-text pk-info-text">
                    Menampilkan <?= $from ?? 0 ?> – <?= $to ?? 0 ?> dari <?= $total ?? 0 ?> data
                </div>
            </div>

            <div class="ar-pagination pk-pagination-wrapper">
                <ul class="pagination mb-0">
                    <?php
                    $currentPage = $pager['currentPage'] ?? 1;
                    $totalPages  = $pager['totalPages'] ?? 1;
                    $queryString = $_GET;
                    unset($queryString['page']); // kita set sendiri
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
    <?php endif; ?>
    <!-- ======== END BOTTOM BAR ======== -->
</div>

<script>
    function erGoToPage(page) {
        const url = new URL(window.location.href);
        url.searchParams.set('page', page);
        url.searchParams.set('perPage', <?= $perPage ?? 10 ?>);

        const wrapper = document.getElementById('erTableCard');
        const scrollY = window.scrollY;

        fetch(url.toString())
            .then(r => r.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newCard = doc.getElementById('erTableCard');
                if (newCard && wrapper) {
                    wrapper.outerHTML = newCard.outerHTML;
                    window.history.pushState({}, '', url.toString());
                    window.scrollTo({ top: scrollY });
                }
            });
    }

    // Event delegation untuk klik baris (buka form evaluasi)
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.er-row').forEach(row => {
            row.addEventListener('click', function(e) {
                const id = this.dataset.identifikasi;
                if (id) {
                    if (typeof openEvaluasiRisiko === 'function') {
                        openEvaluasiRisiko(id);
                    } else {
                        console.warn('openEvaluasiRisiko not defined');
                    }
                }
            });
        });
    });
</script>