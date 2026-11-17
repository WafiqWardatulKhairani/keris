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
            <colgroup>
                <col style="width: 7%">
                <col style="width: 16%">
                <col style="width: 22%">
                <col style="width: 18%">
                <col style="width: 10%">
                <col style="width: 10%">
                <col style="width: 6%">
                <col style="width: 6%">
                <col style="width: 5%">
            </colgroup>

            <thead>
                <!-- BARIS 1: KELOMPOK UTAMA -->
                <tr>
                    <th rowspan="3">Prioritas Risiko</th>
                    <th rowspan="3">Pernyataan Risiko</th>
                    <th colspan="4">Rencana Penanganan Risiko</th>
                    <th colspan="3">Risiko Residu</th>
                </tr>

                <!-- BARIS 2: SUBKELOMPOK -->
                <tr>
                    <th rowspan="2">Rencana Tindak Penanganan (RTP)</th>
                    <th colspan="2">Target</th>
                    <th rowspan="2">Penanggung Jawab</th>

                    <th rowspan="2">Kemungkinan</th>
                    <th rowspan="2">Dampak</th>
                    <th rowspan="2">Skor Risiko</th>
                </tr>

                <!-- BARIS 3: TARGET -->
                <tr>
                    <th>Output</th>
                    <th>Waktu</th>
                </tr>

                <!-- BARIS 4: NOMOR KOLOM -->
                <tr class="number-row">
                    <?php for ($i = 1; $i <= 9; $i++): ?>
                        <th>(<?= $i ?>)</th>
                    <?php endfor; ?>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($groupedRisiko as $items): ?>
                    <?php
                    $first = $items[0];
                    $jumlahItem = count($items);
                    ?>

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

                        $rowClass = $isLast ? 'form3-group-last' : '';
                        ?>

                        <tr class="form3-data-row <?= $rowClass ?>">

                            <!-- 1. PRIORITAS RISIKO -->
                            <td class="text-center form3-priority <?= $mergeClass ?>">
                                <?php if ($isFirst): ?>
                                    (<?= esc($first['prioritas_risiko'] ?? '-') ?>)
                                <?php endif; ?>
                            </td>

                            <!-- 2. PERNYATAAN RISIKO -->
                            <td class="form3-risk-statement <?= $mergeClass ?>">
                                <?php if ($isFirst): ?>
                                    <?= nl2br(esc($first['pernyataan_risiko'] ?? '-')) ?>
                                <?php endif; ?>
                            </td>

                            <!-- 3. RENCANA TINDAK PENANGANAN -->
                            <td>
                                <?= nl2br(esc($item['uraian_rtp'] ?? '-')) ?>
                            </td>

                            <!-- 4. TARGET OUTPUT -->
                            <td>
                                <?= nl2br(esc($item['target_output'] ?? '-')) ?>
                            </td>

                            <!-- 5. TARGET WAKTU -->
                            <td class="text-center form3-waktu">
                                <?php if (!empty($item['target_waktu'])): ?>
                                    <?php
                                    $timestamp = strtotime($item['target_waktu']);
                                    $tanggal = date('d', $timestamp);
                                    $bulan = $bulanIndonesia[(int) date('n', $timestamp)];
                                    $tahunTarget = date('Y', $timestamp);
                                    ?>
                                    <?= $tanggal ?> <?= $bulan ?> <?= $tahunTarget ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>

                            <!-- 6. PENANGGUNG JAWAB -->
                            <td class="text-center form3-pj <?= $mergeClass ?>">
                                <?php if ($isFirst): ?>
                                    Ketua Tim<br>
                                    <?= esc($timkerja ?? '-') ?>
                                <?php endif; ?>
                            </td>

                            <!-- 7. KEMUNGKINAN RESIDU -->
                            <td class="text-center form3-residu form3-residu-cell <?= $mergeClass ?>">
                                <?php if ($isFirst): ?>
                                    <?= esc($first['kemungkinan_residu'] ?? '-') ?>
                                <?php endif; ?>
                            </td>

                            <!-- 8. DAMPAK RESIDU -->
                            <td class="text-center form3-residu form3-residu-cell <?= $mergeClass ?>">
                                <?php if ($isFirst): ?>
                                    <?= esc($first['dampak_residu'] ?? '-') ?>
                                <?php endif; ?>
                            </td>

                            <!-- 9. SKOR RISIKO RESIDU -->
                            <td class="text-center form3-residu form3-residu-cell <?= $mergeClass ?>">
                                <?php if ($isFirst): ?>
                                    <?= esc($first['skor_residu'] ?? '-') ?>
                                <?php endif; ?>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>