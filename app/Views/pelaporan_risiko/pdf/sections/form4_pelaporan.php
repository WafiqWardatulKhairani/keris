<div class="report-section form4-section">

    <?php if (empty($form4Data)): ?>

        <div class="empty-message">
            Data Pemantauan Risiko yang telah disetujui tidak ditemukan.
        </div>

    <?php else: ?>

        <table class="report-table form4-table">
            <colgroup>
                <col style="width: 8%">
                <col style="width: 6%">
                <col style="width: 6%">
                <col style="width: 6%">
                <col style="width: 8%">
                <col style="width: 6%">
                <col style="width: 5%">
                <col style="width: 6%">
                <col style="width: 6%">
                <col style="width: 5%">
                <col style="width: 5%">
                <col style="width: 6%">
                <col style="width: 6%">
                <col style="width: 6%">
                <col style="width: 6%">
                <col style="width: 5%">
                <col style="width: 4%">
            </colgroup>


            <thead>
                <!-- HEADER KELOMPOK -->
                <tr>
                    <th rowspan="2">Pernyataan Risiko</th>
                    <th rowspan="2">Respon Risiko</th>

                    <th colspan="2">Keterjadian Risiko</th>

                    <th rowspan="2">Rencana Tindak Penanganan (RTP)</th>

                    <th colspan="2">Target</th>
                    <th colspan="3">Realisasi</th>

                    <th rowspan="2">Status</th>
                    <th rowspan="2">Link Bukti Dukung</th>
                    <th rowspan="2">Efektivitas RTP</th>
                    <th rowspan="2">Penjelasan Efektivitas</th>

                    <th colspan="3">Risiko Residual</th>
                </tr>

                <!-- SUB HEADER -->
                <tr>
                    <th>Status</th>
                    <th>
                        Kumulatif
                    </th>

                    <th>Output</th>
                    <th>Waktu</th>

                    <th>Tindak Penanganan</th>
                    <th>Output</th>
                    <th>Waktu</th>

                    <th>Kemungkinan</th>
                    <th>Dampak</th>
                    <th>
                        Skor Risiko<br>
                        <small>[Terisi otomatis]</small>
                    </th>
                </tr>

                <!-- NOMOR KOLOM -->
                <tr class="number-row">
                    <?php for ($i = 1; $i <= 17; $i++): ?>
                        <th>(<?= $i ?>)</th>
                    <?php endfor; ?>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($form4Data as $index => $item): ?>

                    <tr class="form4-data-row">

                        <!-- (1) PERNYATAAN RISIKO -->
                        <td>
                            <?= nl2br(esc($item['pernyataan_risiko'] ?? '-')) ?>
                        </td>

                        <!-- (2) RESPON RISIKO -->
                        <td>
                            <?= esc($item['respon_risiko'] ?? '-') ?>
                        </td>

                        <!-- (3) STATUS KETERJADIAN -->
                        <td class="text-center">
                            <?= esc($item['keterjadian_risiko'] ?? '-') ?>
                        </td>

                        <!-- (4) JUMLAH KETERJADIAN -->
                        <td class="text-center">
                            <?php if (($item['keterjadian_risiko'] ?? '') === 'T'): ?>
                                <?= esc($item['jumlah_keterjadian'] ?? '-') ?>
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

                        <!-- (14) PENJELASAN EFEKTIVITAS -->
                        <td>
                            -
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