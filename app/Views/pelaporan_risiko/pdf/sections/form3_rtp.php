<div class="report-section form3-section">

    <?php if (empty($form3Data)): ?>

        <div class="empty-message">
            Data Rencana Penanganan Risiko tidak ditemukan.
        </div>

    <?php else: ?>

        <?php
        // =============================================
        // GROUP BERDASARKAN RISIKO / EVALUASI
        // =============================================
        $groupedRisiko = [];

        foreach ($form3Data as $row) {
            $groupedRisiko[$row['id_evaluasi']][] = $row;
        }

        $bulanIndonesia = [
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        ?>

        <table class="report-table form3-table">
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

                    <th rowspan="2">
                        Penanggung Jawab
                    </th>

                    <th rowspan="2">
                        Risiko Residu
                    </th>

                </tr>

                <tr>

                    <th>
                        Output
                    </th>

                    <th>
                        Waktu
                    </th>

                </tr>

                <tr class="number-row">

                    <?php for ($i = 1; $i <= 7; $i++): ?>

                        <td>
                            (<?= $i ?>)
                        </td>

                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody class="form3-risk-group">
                <?php foreach ($groupedRisiko as $items): ?>

                    <?php
                    $first = $items[0];
                    $jumlahItem = count($items);
                    ?>

            <tbody class="form3-risk-group">

                <?php foreach ($items as $index => $item): ?>

                    <?php
                        $isFirst = ($index === 0);
                        $isLast  = ($index === $jumlahItem - 1);

                        if ($jumlahItem === 1) {

                            $mergeClass = 'form3-merge-single';
                        } elseif ($isFirst) {

                            $mergeClass = 'form3-merge-first';
                        } elseif ($isLast) {

                            $mergeClass = 'form3-merge-last';
                        } else {

                            $mergeClass = 'form3-merge-middle';
                        }

                        $rowClass = $isLast
                            ? 'form3-group-last'
                            : '';
                    ?>

                    <tr class="form3-data-row <?= $rowClass ?>">

                        <!-- PRIORITAS RISIKO -->
                        <td class="text-center form3-priority <?= $mergeClass ?>">

                            <?php if ($isFirst): ?>

                                (<?= esc(
                                        $first['prioritas_risiko'] ?? '-'
                                    ) ?>)

                            <?php endif; ?>

                        </td>


                        <!-- PERNYATAAN RISIKO -->
                        <td class="form3-risk-statement <?= $mergeClass ?>">

                            <?php if ($isFirst): ?>

                                <?= nl2br(esc(
                                    $first['pernyataan_risiko'] ?? '-'
                                )) ?>

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
                        <td class="text-center form3-waktu">

                            <?php if (!empty($item['target_waktu'])): ?>

                                <?php
                                $timestamp = strtotime(
                                    $item['target_waktu']
                                );

                                $tanggal = date(
                                    'd',
                                    $timestamp
                                );

                                $bulan = $bulanIndonesia[(int) date('n', $timestamp)];

                                $tahunTarget = date(
                                    'Y',
                                    $timestamp
                                );
                                ?>

                                <?= $tanggal ?>
                                <?= $bulan ?>
                                <?= $tahunTarget ?>

                            <?php else: ?>

                                -

                            <?php endif; ?>

                        </td>


                        <!-- PENANGGUNG JAWAB -->
                        <td class="text-center form3-pj <?= $mergeClass ?>">

                            <?php if ($isFirst): ?>

                                Ketua Tim
                                <br>

                                <?= esc(
                                    $timkerja ?? '-'
                                ) ?>

                            <?php endif; ?>

                        </td>


                        <!-- RISIKO RESIDU -->
                        <td class="text-center form3-residu <?= $mergeClass ?>">

                            <?php if ($isFirst): ?>

                                P:
                                <?= esc(
                                    $first['kemungkinan_residu'] ?? '-'
                                ) ?>

                                <br>

                                D:
                                <?= esc(
                                    $first['dampak_residu'] ?? '-'
                                ) ?>

                                <br>

                                SR:
                                <?= esc(
                                    $first['skor_residu'] ?? '-'
                                ) ?>

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        <?php endforeach; ?>

        </tbody>

        </table>
    <?php endif; ?>

</div>