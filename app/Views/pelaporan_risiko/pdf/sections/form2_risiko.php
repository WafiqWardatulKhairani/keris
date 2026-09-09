<div class="report-section form2-section">

    <?php if (empty($form2Data)): ?>

        <div class="empty-message">
            Data Identifikasi Risiko tidak ditemukan.
        </div>

    <?php else: ?>

        <table class="report-table form2-table">

            <thead>

                <tr>
                    <th colspan="8">
                        Identifikasi Risiko
                    </th>

                    <th colspan="4">
                        Analisis dan Evaluasi Risiko
                    </th>
                </tr>

                <tr>
                    <th rowspan="2">No</th>

                    <th colspan="2">
                        Proses Bisnis
                    </th>

                    <th rowspan="2">
                        Pernyataan Risiko
                    </th>

                    <th rowspan="2">
                        Penyebab Risiko
                    </th>

                    <th rowspan="2">
                        Dampak Risiko
                    </th>

                    <th rowspan="2">
                        Kategori / Area Dampak
                    </th>

                    <th rowspan="2">
                        Sumber Risiko
                    </th>

                    <th rowspan="2">
                        Risiko Aktual
                    </th>

                    <th rowspan="2">
                        Pengendalian yang Telah Dilaksanakan
                    </th>

                    <th rowspan="2">
                        Efektivitas
                    </th>

                    <th rowspan="2">
                        Respon / Prioritas
                    </th>
                </tr>

                <tr>
                    <th>Kode</th>
                    <th>Uraian Proses</th>
                </tr>

                <tr class="number-row">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <td>(<?= $i ?>)</td>
                    <?php endfor; ?>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($form2Data as $i => $row): ?>

                    <tr>

                        <td class="text-center">
                            <?= $i + 1 ?>
                        </td>

                        <td class="text-center">
                            <?= esc($row['kode_proses'] ?? '-') ?>
                        </td>

                        <td>
                            <?= esc($row['uraian_proses'] ?? '-') ?>
                        </td>

                        <td>
                            <?= nl2br(esc(
                                $row['pernyataan_risiko'] ?? '-'
                            )) ?>
                        </td>

                        <td>
                            <?= nl2br(esc(
                                $row['penyebab_risiko'] ?? '-'
                            )) ?>
                        </td>

                        <td>
                            <?= nl2br(esc(
                                $row['dampak_risiko'] ?? '-'
                            )) ?>
                        </td>

                        <td>
                            <strong>Kategori:</strong><br>
                            <?= esc($row['nama_kategori'] ?? '-') ?>

                            <br><br>

                            <strong>Area Dampak:</strong><br>
                            <?= esc($row['area_dampak_list'] ?? '-') ?>
                        </td>

                        <td class="text-center">
                            <?= esc($row['sumber_risiko'] ?? '-') ?>
                        </td>

                        <td class="text-center">
                            K: <?= esc($row['kemungkinan'] ?? '-') ?><br>
                            D: <?= esc($row['dampak'] ?? '-') ?><br>
                            NR: <?= esc($row['nilai_risiko'] ?? '-') ?>
                        </td>

                        <td>
                            <?= nl2br(esc(
                                $row['uraian_pengendalian'] ?? '-'
                            )) ?>
                        </td>

                        <td class="text-center">
                            <?= esc($row['efektivitas'] ?? '-') ?>
                        </td>

                        <td>
                            <strong>Respon:</strong><br>
                            <?= esc($row['opsi_tindakan'] ?? '-') ?>

                            <br><br>

                            <strong>Prioritas:</strong><br>
                            <?= esc($row['prioritas'] ?? '-') ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>

</div>