<div class="report-section">
    <?php if (empty($form1)): ?>

        <div class="empty-message">
            Data Penetapan Konteks tidak ditemukan.
        </div>

    <?php else: ?>

        <div class="subsection-title">
            Proses Bisnis dan Sasaran Kinerja
        </div>

        <table class="report-table">
            <thead>
                <tr>
                    <th width="6%">No</th>
                    <th width="10%">Kode</th>
                    <th width="20%">Proses Bisnis</th>
                    <th width="28%">Deskripsi Proses</th>
                    <th width="36%">Sasaran Kinerja</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($form1Proses)): ?>

                    <?php
                    $groupedProses = [];

                    foreach ($form1Proses as $row) {
                        $key =
                            ($row['kode_proses'] ?? '-') . '|' .
                            ($row['uraian_proses'] ?? '-');

                        $groupedProses[$key][] = $row;
                    }

                    $no = 1;
                    ?>

                    <?php foreach ($groupedProses as $items): ?>

                        <?php
                        $rowspan = count($items);
                        $first = $items[0];
                        ?>

                        <?php foreach ($items as $index => $row): ?>

                            <tr>

                                <?php if ($index === 0): ?>

                                    <td
                                        rowspan="<?= $rowspan ?>"
                                        class="text-center">
                                        <?= $no++ ?>
                                    </td>

                                    <td
                                        rowspan="<?= $rowspan ?>"
                                        class="text-center">
                                        <?= esc($first['kode_proses'] ?? '-') ?>
                                    </td>

                                    <td rowspan="<?= $rowspan ?>">
                                        <?= esc($first['uraian_proses'] ?? '-') ?>
                                    </td>

                                <?php endif; ?>

                                <td>
                                    <?= esc($row['deskripsi_proses'] ?? '-') ?>
                                </td>

                                <td>
                                    <?= esc($row['sasaran_kinerja'] ?? '-') ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="5" class="text-center">
                            Belum ada data proses bisnis.
                        </td>
                    </tr>

                <?php endif; ?>
            </tbody>
        </table>
</div>

</div>

<?php endif; ?>

</div>