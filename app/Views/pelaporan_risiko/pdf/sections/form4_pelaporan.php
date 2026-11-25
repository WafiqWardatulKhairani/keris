<div class="report-section form4-section">

    <?php if (empty($form4Data)): ?>

        <div class="empty-message">
            Data Pemantauan Risiko yang telah disetujui tidak ditemukan.
        </div>

    <?php else: ?>

        <table class="report-table form4-table" style="width: 100%; table-layout: fixed;">

            <colgroup>
                <col style="width: 2mm;">
                <col style="width: 14%">
                <col style="width: 8%">
                <col style="width: 7%">
                <col style="width: 6%">
                <col style="width: 13%">
                <col style="width: 9%">
                <col style="width: 7%">
                <col style="width: 9%">
                <col style="width: 7%">
                <col style="width: 5%">
                <col style="width: 5%">
                <col style="width: 5%">
                <col style="width: 5%">
                <col style="width: 5%">
                <col style="width: 5%">
                <col style="width: 4%">
                <col style="width: 5%">
            </colgroup>

            <thead>
                <!-- BARIS 1: HEADER UTAMA -->
                <tr>
                    <th colspan="2" rowspan="3">
                        Pernyataan Risiko<br>
                    </th>

                    <th rowspan="3">
                        Respon Risiko<br>
                    </th>

                    <th colspan="2">Keterjadian Risiko</th>

                    <th colspan="13">Rencana Penanganan Risiko</th>
                </tr>

                <!-- BARIS 2: HEADER KELOMPOK -->
                <tr>
                    <th rowspan="2">Status</th>
                    <th rowspan="2">
                        Jumlah<br>
                    </th>

                    <th rowspan="2">Rencana Tindak Penanganan (RTP)</th>

                    <th colspan="2">Target</th>
                    <th colspan="3">Realisasi</th>

                    <th rowspan="2">Status</th>
                    <th rowspan="2">Link Bukti Dukung</th>
                    <th rowspan="2">Efektivitas RTP</th>
                    <th rowspan="2">Penjelasan Efektivitas</th>

                    <th colspan="3">Risiko Residual</th>
                </tr>

                <!-- BARIS 3: SUB HEADER -->
                <tr>
                    <th>Output</th>
                    <th>Waktu</th>

                    <th>Tindak Penanganan</th>
                    <th>Output</th>
                    <th>Waktu</th>

                    <th>Kemungkinan</th>
                    <th>Dampak</th>
                    <th>
                        Skor Risiko<br>
                    </th>
                </tr>

                <!-- BARIS 4: NOMOR KOLOM -->
                <tr class="number-row">
                    <?php for ($i = 1; $i <= 18; $i++): ?>
                        <th>(<?= $i ?>)</th>
                    <?php endfor; ?>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($form4Data as $index => $item): ?>

                    <tr class="form4-data-row">
                        <!-- (1) NOMOR URUT -->
                        <td class="form4-no" style="
    width: 5mm !important;
    min-width: 5mm !important;
    max-width: 5mm !important;
    text-align: center !important;
    vertical-align: top !important;
    padding: 4px 0 !important;
    white-space: nowrap;
">
                            <?= $index + 1 ?>
                        </td>

                        <!-- (1) PERNYATAAN RISIKO -->
                        <td>
                            <?= nl2br(esc($item['pernyataan_risiko'] ?? '-')) ?>
                        </td>

                        <!-- (2) RESPON RISIKO -->
                        <td>
                            <?php
                            $responRisiko = trim($item['respon_risiko'] ?? '');

                            echo esc(
                                $responRisiko !== ''
                                    ? $responRisiko . ' Risiko'
                                    : 'Belum Diisi'
                            );
                            ?>
                        </td>
                        <!-- (3) STATUS KETERJADIAN -->
                        <td class="text-center">
                            <?php
                            if (!empty($item['id_pemantauan_keterjadian'])) {

                                $status = $item['keterjadian_risiko'] ?? '';

                                $statusKejadian = [
                                    'T'  => 'Terjadi',
                                    'TT' => 'Tidak Terjadi',
                                    'BT' => 'Belum Terjadi',
                                ];

                                echo esc($statusKejadian[$status] ?? '');
                            }
                            ?>
                        </td>

                        <!-- (4) JUMLAH KETERJADIAN -->
                        <td class="text-center">
                            <?php
                            if (
                                !empty($item['id_pemantauan_keterjadian']) &&
                                ($item['keterjadian_risiko'] ?? '') === 'T'
                            ) {
                                echo esc($item['jumlah_keterjadian'] ?? '');
                            }
                            ?>
                        </td>

                        <!-- (4) JUMLAH KETERJADIAN -->
                        <td class="text-center">
                            <?php if (($item['keterjadian_risiko'] ?? '') === 'T'): ?>
                                <?= esc($item['jumlah_keterjadian'] ?? 0) ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>

                        <!-- (5) RTP -->
                        <td>
                            <?= nl2br(esc($item['uraian_rtp'] ?? '-')) ?>
                        </td>

                        <!-- (6) TARGET OUTPUT -->
                        <td>
                            <?= nl2br(esc($item['target_output'] ?? '-')) ?>
                        </td>

                        <!-- (7) TARGET WAKTU -->
                        <td class="text-center">
                            <?php if (!empty($item['target_waktu'])): ?>
                                <?= date('d/m/Y', strtotime($item['target_waktu'])) ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>

                        <!-- (8) TINDAK PENANGANAN -->
                        <td>
                            <?= nl2br(esc($item['uraian_rtp'] ?? '-')) ?>
                        </td>

                        <!-- (9) REALISASI OUTPUT -->
                        <td>
                            <?= nl2br(esc($item['realisasi_output'] ?? '-')) ?>
                        </td>

                        <!-- (10) REALISASI WAKTU -->
                        <td class="text-center">
                            <?php if (!empty($item['realisasi_waktu'])): ?>
                                <?= date('d/m/Y', strtotime($item['realisasi_waktu'])) ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>

                        <!-- (11) STATUS -->
                        <td class="text-center">
                            <?= esc($item['status'] ?? 'Belum Dilaksanakan') ?>
                        </td>

                        <!-- (12) LINK BUKTI DUKUNG -->
                        <td>
                            <?php if (!empty($item['link_bukti'])): ?>
                                <?= esc($item['link_bukti']) ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>

                        <!-- (13) EFEKTIVITAS RTP -->
                        <td class="text-center">
                            <?= esc($item['efektivitas_pengendalian'] ?? '-') ?>
                        </td>

                        <!-- PENJELASAN EFEKTIVITAS -->
                        <td>
                            <?= nl2br(esc($item['catatan'] ?? '-')) ?>
                        </td>

                        <!-- (15) KEMUNGKINAN RESIDUAL -->
                        <td class="text-center">
                            <?= esc($item['kemungkinan_residu'] ?? '-') ?>
                        </td>

                        <!-- (16) DAMPAK RESIDUAL -->
                        <td class="text-center">
                            <?= esc($item['dampak_residu'] ?? '-') ?>
                        </td>

                        <!-- (17) SKOR RISIKO RESIDUAL -->
                        <td class="text-center">
                            <?= esc($item['skor_risiko_residu'] ?? '-') ?>
                        </td>

                    </tr>

                <?php endforeach; ?>
            </tbody>

        </table>

    <?php endif; ?>

</div>