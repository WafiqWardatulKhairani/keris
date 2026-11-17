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
                $line = preg_replace('/^[-•]\s*/', '', $line);
                $line = preg_replace('/^\d+[.)]\s*/', '', $line);

                $html .= ($i + 1) . '. ' . esc($line);

                if ($i < count($lines) - 1) {
                    $html .= '<br>';
                }
            }

            return $html;
        }

        // Kelompokkan berdasarkan proses bisnis
        $groupedProses = [];

        foreach ($form2Data as $row) {
            $key = ($row['kode_proses'] ?? '-') . '|' .
                ($row['uraian_proses'] ?? '-');

            $groupedProses[$key][] = $row;
        }

        // Urutkan risiko berdasarkan skor tertinggi
        usort($form2Data, function ($a, $b) {
            return (float) ($b['nilai_risiko'] ?? 0)
                <=> (float) ($a['nilai_risiko'] ?? 0);
        });

        // Nomor prioritas berdasarkan urutan skor
        $prioritasMap = [];
        $nomor = 1;

        foreach ($form2Data as $row) {
            $id = $row['id_identifikasi'];

            if (!isset($prioritasMap[$id])) {
                $prioritasMap[$id] = $nomor++;
            }
        }

        // Susun ulang grup mengikuti urutan prioritas
        $groupedProses = [];

        foreach ($form2Data as $row) {
            $key = ($row['kode_proses'] ?? '-') . '|' .
                ($row['uraian_proses'] ?? '-');

            $groupedProses[$key][] = $row;
        }
        if (!function_exists('form2RiskColor')) {
            function form2RiskColor($kemungkinan, $dampak)
            {
                $k = (int) $kemungkinan;
                $d = (int) $dampak;

                $matrix = [
                    5 => [
                        1 => '#bbf7d0',
                        2 => '#fde68a',
                        3 => '#fed7aa',
                        4 => '#fca5a5',
                        5 => '#fca5a5',
                    ],
                    4 => [
                        1 => '#bfdbfe',
                        2 => '#fde68a',
                        3 => '#fed7aa',
                        4 => '#fed7aa',
                        5 => '#fca5a5',
                    ],
                    3 => [
                        1 => '#bfdbfe',
                        2 => '#bbf7d0',
                        3 => '#fde68a',
                        4 => '#fed7aa',
                        5 => '#fca5a5',
                    ],
                    2 => [
                        1 => '#bfdbfe',
                        2 => '#bbf7d0',
                        3 => '#fde68a',
                        4 => '#fde68a',
                        5 => '#fca5a5',
                    ],
                    1 => [
                        1 => '#bfdbfe',
                        2 => '#bfdbfe',
                        3 => '#bfdbfe',
                        4 => '#bbf7d0',
                        5 => '#fca5a5',
                    ],
                ];

                return $matrix[$k][$d] ?? '#ffffff';
            }
        }
        ?>

        <table class="report-table form2-table">
            <colgroup>
                <col style="width: 9%"> <!-- Proses Bisnis -->
                <col style="width: 15%"> <!-- Pernyataan Risiko -->
                <col style="width: 20%"> <!-- Penyebab / Dampak -->
                <col style="width: 4%"> <!-- Kategori -->
                <col style="width: 5%"> <!-- Area Dampak -->
                <col style="width: 4%"> <!-- Sumber Risiko -->

                <col style="width: 3.5%"> <!-- Kemungkinan -->
                <col style="width: 3.5%"> <!-- Dampak -->
                <col style="width: 3.5%"> <!-- Skor Risiko -->
                <col style="width: 4.5%"> <!-- Selera Risiko -->
                <col style="width: 15%"> <!-- Uraian Pengendalian -->
                <col style="width: 4.5%"> <!-- Efek Pengendalian -->

                <col style="width: 4.5%"> <!-- Respon Risiko -->
                <col style="width: 3%"> <!-- Prioritas -->
            </colgroup>

            <thead>
                <!-- BARIS 1: KELOMPOK UTAMA -->
                <tr>
                    <th colspan="6" rowspan="2">Identifikasi Risiko</th>
                    <th colspan="6">Analisis Risiko</th>
                    <th colspan="2" rowspan="2">Evaluasi Risiko</th>
                </tr>

                <!-- BARIS 2: SUBKELOMPOK ANALISIS -->
                <tr>
                    <th colspan="3">Risiko Aktual</th>
                    <th colspan="3">Pengendalian yang Telah Dilaksanakan</th>
                </tr>

                <!-- BARIS 3: NAMA SELURUH KOLOM -->
                <tr>
                    <th>Proses Bisnis</th>
                    <th>Pernyataan Risiko</th>
                    <th>Penyebab Risiko / Dampak Risiko</th>
                    <th>Kategori Risiko</th>
                    <th>Area Dampak</th>
                    <th>Sumber Risiko</th>

                    <th>Kemungkinan</th>
                    <th>Dampak</th>
                    <th>Skor Risiko</th>
                    <th>Selera Risiko</th>
                    <th>Uraian</th>
                    <th>Efek Pengendalian</th>

                    <th>Respon Risiko</th>
                    <th>Prioritas</th>
                </tr>

                <!-- BARIS 4: NOMOR KOLOM -->
                <tr class="number-row">
                    <?php for ($i = 1; $i <= 14; $i++): ?>
                        <th>(<?= $i ?>)</th>
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
                        <?php
                        $idRisiko = $row['id_identifikasi'];
                        $skor = $row['nilai_risiko'] ?? null;
                        $selera = $row['nilai_selera'] ?? '-';
                        ?>

                        <tr class="form2-data-row <?= $index === $jumlahItem - 1 ? 'form2-group-last' : '' ?>">

                            <!-- 1. PROSES BISNIS -->
                            <td class="<?= $index > 0 ? 'form2-merge-cont' : '' ?>
    <?= $index < $jumlahItem - 1 ? 'form2-merge-start' : '' ?>">
                                <?php if ($index === 0): ?>
                                    <?= esc(
                                        ($first['kode_proses'] ?? '-') . ' — ' .
                                            ($first['uraian_proses'] ?? '-')
                                    ) ?>
                                <?php endif; ?>
                            </td>

                            <!-- 2. PERNYATAAN RISIKO -->
                            <td>
                                <?= nl2br(esc($row['pernyataan_risiko'] ?? '-')) ?>
                            </td>

                            <!-- 3  . PENYEBAB / DAMPAK -->
                            <td>
                                <strong>Penyebab:</strong><br>
                                <?= form2NumberedLines($row['penyebab_risiko'] ?? '-') ?>

                                <br><br>

                                <strong>Dampak:</strong><br>
                                <?= form2NumberedLines($row['dampak_risiko'] ?? '-') ?>
                            </td>

                            <!-- 4. KATEGORI RISIKO -->
                            <td>
                                <?= esc($row['nama_kategori'] ?? '-') ?>
                            </td>

                            <!-- 5. AREA DAMPAK -->
                            <td>
                                <?= esc($row['area_dampak_list'] ?? '-') ?>
                            </td>

                            <!-- 6. SUMBER RISIKO -->
                            <td class="text-center">
                                <?= esc($row['sumber_risiko'] ?? '-') ?>
                            </td>

                            <!-- 7. KEMUNGKINAN -->
                            <td class="text-center">
                                <?= esc($row['kemungkinan'] ?? '-') ?>
                            </td>

                            <!-- 8. DAMPAK -->
                            <td class="text-center">
                                <?= esc($row['dampak'] ?? '-') ?>
                            </td>

                            <!-- 9. SKOR RISIKO -->
                            <td class="text-center"
                                style="
        background-color: <?= form2RiskColor($row['kemungkinan'] ?? 0, $row['dampak'] ?? 0) ?>;
        font-weight: bold;
    ">
                                <?= esc($skor ?? '-') ?>
                            </td>

                            <!-- 10. SELERA RISIKO -->
                            <td class="form2-score-cell"
    style="
        background-color: <?= form2RiskColor($row['kemungkinan'] ?? 0, $row['dampak'] ?? 0) ?>;
        font-weight: bold;
        text-align: center !important;
        vertical-align: middle !important;
    ">
    <div style="
        text-align: center;
        vertical-align: middle;
        width: 100%;
    ">
        <?= esc($selera) ?>
    </div>
</td>

                            <!-- 11. URAIAN PENGENDALIAN -->
                            <td>
                                <?= form2NumberedLines($row['uraian_pengendalian'] ?? '-') ?>
                            </td>

                            <!-- 12. EFEK PENGENDALIAN -->
                            <td class="text-center">
                                <?= esc($row['efektivitas'] ?? '-') ?>
                            </td>

                            <!-- 13. RESPON RISIKO -->
                            <td class="text-center">
                                <?= esc($row['opsi_tindakan'] ?? '-') ?>
                            </td>

                            <!-- 14. PRIORITAS -->
                            <td class="text-center">
                                <?= esc($prioritasMap[$idRisiko] ?? '-') ?>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>
</div>