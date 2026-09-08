<div class="card border-0 shadow-sm" id="brTableCard">
    <div class="card-body p-0">

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;" class="text-center py-2">#</th>
                        <th class="py-2">Pernyataan Risiko</th>
                    </tr>
                </thead>

                <tbody id="brTableBody">

                    <?php if (empty($data)): ?>

                        <tr>
                            <td colspan="2" class="text-center py-5 text-muted">
                                <i class="ti ti-database fs-1 mb-2 d-block opacity-25"></i>
                                Belum ada data
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php $no = $from ?? 1; ?>

                        <?php foreach ($data as $row): ?>

                            <tr
                                class="br-row"
                                style="cursor: pointer;"
                                data-id="<?= $row['id_bank_risiko'] ?>"
                                data-pernyataan="<?= esc($row['pernyataan_risiko']) ?>">

                                <td class="text-center text-muted">
                                    <?= $no++ ?>
                                </td>

                                <td>
                                    <?= esc($row['pernyataan_risiko']) ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>
        </div>


        <?php if (!empty($data)): ?>

            <div class="pk-table-bottom">

                <div class="pk-table-info">

                    <form
                        method="get"
                        action="<?= base_url('bank-risiko') ?>"
                        class="d-flex align-items-center"
                        id="brPerPageForm">

                        <select
                            name="perPage"
                            id="brPerPage"
                            class="pk-perpage-select"
                            onchange="this.form.submit();">

                            <?php foreach ([5, 10, 25, 50] as $size): ?>

                                <option
                                    value="<?= $size ?>"
                                    <?= ($perPage ?? 10) == $size ? 'selected' : '' ?>>

                                    <?= $size ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </form>


                    <div class="pk-info-text">
                        Menampilkan
                        <?= $from ?? 0 ?> – <?= $to ?? 0 ?>
                        dari <?= $total ?? 0 ?> data
                    </div>

                </div>


                <div class="pk-pagination-wrapper">

                    <?php
                    $currentPage = $pager['currentPage'] ?? 1;
                    $totalPages  = $pager['totalPages'] ?? 1;

                    $queryString = $_GET;

                    unset($queryString['page']);

                    $queryString['perPage'] = $perPage;
                    ?>

                    <ul class="pagination mb-0">

                        <!-- PREVIOUS -->
                        <?php if ($currentPage <= 1): ?>

                            <li class="page-item disabled">
                                <span class="page-link">&laquo;</span>
                            </li>

                        <?php else: ?>

                            <?php
                            $queryString['page'] = $currentPage - 1;
                            ?>

                            <li class="page-item">
                                <a
                                    class="page-link"
                                    href="<?= base_url('bank-risiko') ?>?<?= http_build_query($queryString) ?>">
                                    &laquo;
                                </a>
                            </li>

                        <?php endif; ?>


                        <!-- NOMOR HALAMAN -->
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                            <?php if (
                                $i == 1 ||
                                $i == $totalPages ||
                                abs($i - $currentPage) <= 1
                            ): ?>

                                <?php if ($i == $currentPage): ?>

                                    <li class="page-item active">
                                        <span class="page-link">
                                            <?= $i ?>
                                        </span>
                                    </li>

                                <?php else: ?>

                                    <?php
                                    $queryString['page'] = $i;
                                    ?>

                                    <li class="page-item">
                                        <a
                                            class="page-link"
                                            href="<?= base_url('bank-risiko') ?>?<?= http_build_query($queryString) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>

                                <?php endif; ?>

                            <?php elseif (
                                $i == 2 ||
                                $i == $totalPages - 1
                            ): ?>

                                <li class="page-item disabled">
                                    <span class="page-link">…</span>
                                </li>

                            <?php endif; ?>

                        <?php endfor; ?>


                        <!-- NEXT -->
                        <?php if ($currentPage >= $totalPages): ?>

                            <li class="page-item disabled">
                                <span class="page-link">&raquo;</span>
                            </li>

                        <?php else: ?>

                            <?php
                            $queryString['page'] = $currentPage + 1;
                            ?>

                            <li class="page-item">
                                <a
                                    class="page-link"
                                    href="<?= base_url('bank-risiko') ?>?<?= http_build_query($queryString) ?>">
                                    &raquo;
                                </a>
                            </li>

                        <?php endif; ?>

                    </ul>

                </div>

            </div>

        <?php endif; ?>

    </div>
</div>