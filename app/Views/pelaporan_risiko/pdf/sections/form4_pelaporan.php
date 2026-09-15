<div class="report-section form4-section">

    <?php if (empty($form4Data)): ?>

        <div class="empty-message">
            Data Pelaporan Risiko yang telah disetujui tidak ditemukan.
        </div>

    <?php else: ?>

        <?php
        // =============================================
        // GROUP BERDASARKAN RISIKO / EVALUASI
        // =============================================
        $grouped = [];

        foreach ($form4Data as $row) {
            $grouped[$row['id_evaluasi']][] = $row;
        }
        ?>

        <table class="report-table form4-table">

            <thead>

                <tr>
                    <th rowspan="2">
                        Prioritas Risiko
                    </th>

                    <th rowspan="2">
                        Pernyataan Risiko
                    </th>

                    <th rowspan="2">
                        Rencana Tindak Penanganan (RTP)
                    </th>

                    <th colspan="2">
                        Target
                    </th>

                    <th colspan="2">
                        Realisasi
                    </th>

                    <th rowspan="2">
                        Penanggung Jawab
                    </th>
                </tr>

                <tr>
                    <th>Output</th>
                    <th>Waktu</th>

                    <th>Output</th>
                    <th>Waktu</th>
                </tr>

                <tr class="number-row">
                    <?php for ($i = 1; $i <= 8; $i++): ?>
                        <td>(<?= $i ?>)</td>
                    <?php endfor; ?>
                </tr>

            </thead>

            <tbody>

    <?php foreach ($grouped as $items): ?>

        <?php
        $first = $items[0];
        $jumlahItem = count($items);

        $prioritas =
            $first['prioritas_risiko']
            ?? '-';

        $risiko =
            $first['pernyataan_risiko']
            ?? '-';
        ?>

        <?php foreach ($items as $index => $item): ?>

            <?php
            $isFirst = ($index === 0);
            $isLast  = ($index === $jumlahItem - 1);

            // =============================================
            // CLASS FAKE MERGE
            // =============================================
            if ($jumlahItem === 1) {

                $mergeClass = 'form4-merge-single';

            } elseif ($isFirst) {

                $mergeClass = 'form4-merge-first';

            } elseif ($isLast) {

                $mergeClass = 'form4-merge-last';

            } else {

                $mergeClass = 'form4-merge-middle';
            }

            $rowClass = $isLast
                ? 'form4-group-last'
                : '';
            ?>

            <tr class="form4-data-row <?= $rowClass ?>">

                <!-- PRIORITAS -->
                <td class="
                    text-center
                    form4-priority
                    <?= $mergeClass ?>
                ">

                    <?php if ($isFirst): ?>

                        (<?= esc($prioritas) ?>)

                    <?php endif; ?>

                </td>


                <!-- PERNYATAAN RISIKO -->
                <td class="
                    form4-risk-statement
                    <?= $mergeClass ?>
                ">

                    <?php if ($isFirst): ?>

                        <?= nl2br(esc($risiko)) ?>

                    <?php endif; ?>

                </td>


                <!-- RTP -->
                <td>

                    <?= nl2br(esc(
                        $item['uraian_rtp'] ?? '-'
                    )) ?>

                </td>


                <!-- TARGET OUTPUT -->
                <td>

                    <?= nl2br(esc(
                        $item['target_output'] ?? '-'
                    )) ?>

                </td>


                <!-- TARGET WAKTU -->
                <td class="text-center form4-waktu">

                    <?php if (!empty($item['target_waktu'])): ?>

                        <?= date(
                            'd/m/Y',
                            strtotime($item['target_waktu'])
                        ) ?>

                    <?php else: ?>

                        -

                    <?php endif; ?>

                </td>


                <!-- REALISASI OUTPUT -->
                <td>

                    <?= nl2br(esc(
                        $item['realisasi_output'] ?? '-'
                    )) ?>

                </td>


                <!-- REALISASI WAKTU -->
                <td class="text-center form4-waktu">

                    <?php if (!empty($item['realisasi_waktu'])): ?>

                        <?= date(
                            'd/m/Y',
                            strtotime($item['realisasi_waktu'])
                        ) ?>

                    <?php else: ?>

                        -

                    <?php endif; ?>

                </td>


                <!-- PENANGGUNG JAWAB -->
                <td class="
                    text-center
                    form4-pj
                    <?= $mergeClass ?>
                ">

                    <?php if ($isFirst): ?>

                        Ketua Tim
                        <br>

                        <?= esc(
                            $timkerja ?? '-'
                        ) ?>

                    <?php endif; ?>

                </td>

            </tr>

        <?php endforeach; ?>

    <?php endforeach; ?>

</tbody>

        </table>

    <?php endif; ?>

</div>