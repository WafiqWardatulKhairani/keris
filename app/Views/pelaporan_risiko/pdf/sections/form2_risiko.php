<div class="report-section form2-section">

    <?php if (empty($form2Data)): ?>

        <div class="empty-message">
            Data Identifikasi Risiko tidak ditemukan.
        </div>

    <?php else: ?>

        <?php
        function form2NumberedLines($text)
        {
            $text = trim((string) $text);

            if ($text === '') {
                return '-';
            }

            $lines = preg_split('/\r\n|\r|\n/', $text);

            $lines = array_values(array_filter(
                array_map('trim', $lines),
                fn($line) => $line !== ''
            ));

            if (empty($lines)) {
                return '-';
            }

            $html = '';

            foreach ($lines as $i => $line) {

                // Hapus bullet / nomor lama
                $line = preg_replace('/^[-•]\s*/', '', $line);
                $line = preg_replace('/^\d+[\.\)]\s*/', '', $line);

                $html .= ($i + 1) . '. ' . esc($line);

                if ($i < count($lines) - 1) {
                    $html .= '<br>';
                }
            }

            return $html;
        }

        // =============================================
        // GROUP BERDASARKAN PROSES BISNIS
        // =============================================
        $groupedProses = [];

        foreach ($form2Data as $row) {

            $key =
                ($row['kode_proses'] ?? '-') . '|' .
                ($row['uraian_proses'] ?? '-');

            $groupedProses[$key][] = $row;
        }
        ?>

        <table class="report-table form2-table">
            <thead>
                <!-- HEADER UTAMA -->
                <tr>

                    <th colspan="7">
                        Identifikasi Risiko
                    </th>

                    <th colspan="3">
                        Analisis Risiko
                    </th>

                    <th colspan="2">
                        Evaluasi Risiko
                    </th>

                </tr>

                <!-- HEADER KOLOM -->
                <tr>

                    <th colspan="2">
                        Proses Bisnis
                    </th>

                    <th rowspan="2">
                        Pernyataan Risiko
                    </th>

                    <th rowspan="2">
                        Penyebab Risiko / Dampak Risiko
                    </th>

                    <th rowspan="2">
                        Kategori Risiko
                    </th>

                    <th rowspan="2">
                        Area Dampak
                    </th>

                    <th rowspan="2">
                        Sumber Risiko
                        <br>
                        (Internal / Eksternal)
                    </th>

                    <th rowspan="2">
                        Risiko Aktual
                        <br>
                        Probability (P)
                        <br>
                        Dampak (D)
                        <br>
                        Skor Risiko (SR)
                    </th>

                    <th colspan="2">
                        Pengendalian Yang Telah Dilaksanakan
                    </th>

                    <th rowspan="2">
                        Respon Risiko
                    </th>

                    <th rowspan="2">
                        Prioritas
                    </th>

                </tr>

                <tr>

                    <th>
                        Kode
                    </th>

                    <th>
                        Uraian Proses
                    </th>

                    <th>
                        Uraian
                    </th>

                    <th>
                        Efek Pengendalian
                    </th>

                </tr>

                <!-- NOMOR KOLOM -->
                <tr class="number-row">

                    <?php for ($i = 1; $i <= 12; $i++): ?>

                        <td>
                            (<?= $i ?>)
                        </td>

                    <?php endfor; ?>

                </tr>
            </thead>
            <tbody>

                <?php foreach ($groupedProses as $items): ?>

                    <?php
                    $first = $items[0];
                    $jumlahItem = count($items);
                    ?>

                    <?php foreach ($items as $index => $row): ?>

                        <tr class="form2-data-row <?= $index === $jumlahItem - 1 ? 'form2-group-last' : '' ?>">

                            <!-- KODE PROSES -->
                            <td class="text-center form2-process-cell
            <?= $index > 0 ? 'form2-merge-cont' : '' ?>
            <?= $index < $jumlahItem - 1 ? 'form2-merge-start' : '' ?>">

                                <?php if ($index === 0): ?>

                                    <?= esc(
                                        $first['kode_proses'] ?? '-'
                                    ) ?>

                                <?php endif; ?>

                            </td>

                            <!-- URAIAN PROSES -->
                            <td class="form2-process-cell
            <?= $index > 0 ? 'form2-merge-cont' : '' ?>
            <?= $index < $jumlahItem - 1 ? 'form2-merge-start' : '' ?>">

                                <?php if ($index === 0): ?>

                                    <?= esc(
                                        $first['uraian_proses'] ?? '-'
                                    ) ?>

                                <?php endif; ?>

                            </td>

                            <!-- PERNYATAAN RISIKO -->
                            <td>
                                <?= nl2br(esc(
                                    $row['pernyataan_risiko'] ?? '-'
                                )) ?>
                            </td>

                            <!-- PENYEBAB + DAMPAK -->
                            <td>

                                <strong>Penyebab:</strong>
                                <br>

                                <?= form2NumberedLines(
                                    $row['penyebab_risiko'] ?? '-'
                                ) ?>

                                <br><br>

                                <strong>Dampak:</strong>
                                <br>

                                <?= form2NumberedLines(
                                    $row['dampak_risiko'] ?? '-'
                                ) ?>

                            </td>

                            <!-- KATEGORI -->
                            <td>
                                <?= esc(
                                    $row['nama_kategori'] ?? '-'
                                ) ?>
                            </td>

                            <!-- AREA DAMPAK -->
                            <td>
                                <?= esc(
                                    $row['area_dampak_list'] ?? '-'
                                ) ?>
                            </td>

                            <!-- SUMBER RISIKO -->
                            <td class="text-center">
                                <?= esc(
                                    $row['sumber_risiko'] ?? '-'
                                ) ?>
                            </td>

                            <!-- RISIKO AKTUAL -->
                            <td class="text-center">

                                P:
                                <?= esc(
                                    $row['kemungkinan'] ?? '-'
                                ) ?>

                                <br>

                                D:
                                <?= esc(
                                    $row['dampak'] ?? '-'
                                ) ?>

                                <br>

                                SR:
                                <?= esc(
                                    $row['nilai_risiko'] ?? '-'
                                ) ?>

                            </td>

                            <!-- URAIAN PENGENDALIAN -->
                            <td>
                                <?= form2NumberedLines(
                                    $row['uraian_pengendalian'] ?? '-'
                                ) ?>
                            </td>

                            <!-- EFEK PENGENDALIAN -->
                            <td class="text-center">
                                <?= esc(
                                    $row['efektivitas'] ?? '-'
                                ) ?>
                            </td>

                            <!-- RESPON RISIKO -->
                            <td class="text-center">
                                <?= esc(
                                    $row['opsi_tindakan'] ?? '-'
                                ) ?>
                            </td>

                            <!-- PRIORITAS -->
                            <td class="text-center">

                                <?php if (!empty($row['prioritas'])): ?>

                                    (<?= esc($row['prioritas']) ?>)

                                <?php else: ?>

                                    -

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>