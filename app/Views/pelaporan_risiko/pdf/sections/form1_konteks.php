<div class="report-section">
    <?php if (empty($form1)): ?>

        <div class="empty-message">
            Data Penetapan Konteks tidak ditemukan.
        </div>

    <?php else: ?>

        <table class="report-table context-summary-table">
            <tr>
                <td class="label-cell">Tahun</td>
                <td><?= esc($form1['tahun'] ?? '-') ?></td>

                <td class="label-cell">Tim Kerja</td>
                <td><?= esc($form1['nama_tim'] ?? '-') ?></td>
            </tr>

            <tr>
                <td class="label-cell">Kegiatan</td>
                <td colspan="3">
                    <?= esc($form1['nama_kegiatan'] ?? '-') ?>
                </td>
            </tr>

            <tr>
                <td class="label-cell">Sasaran Strategis</td>
                <td colspan="3">
                    <?= esc($form1['sasaran_strategis'] ?? '-') ?>
                </td>
            </tr>
        </table>


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

                    <?php foreach ($form1Proses as $i => $row): ?>
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
                                <?= esc($row['deskripsi_proses'] ?? '-') ?>
                            </td>

                            <td>
                                <?= esc($row['sasaran_kinerja'] ?? '-') ?>
                            </td>
                        </tr>
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


        <div class="two-column-section">

            <div class="column-block">
                <div class="subsection-title">
                    Pemangku Kepentingan
                </div>

                <table class="report-table">
                    <thead>
                        <tr>
                            <th width="10%">No</th>
                            <th width="55%">Instansi</th>
                            <th width="35%">Hubungan</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($form1Pemangku)): ?>

                            <?php foreach ($form1Pemangku as $i => $row): ?>
                                <tr>
                                    <td class="text-center">
                                        <?= $i + 1 ?>
                                    </td>

                                    <td>
                                        <?= esc($row['nama_instansi'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= esc($row['hubungan'] ?? '-') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="3" class="text-center">
                                    Belum ada data pemangku kepentingan.
                                </td>
                            </tr>

                        <?php endif; ?>
                    </tbody>
                </table>
            </div>


            <div class="column-block">
                <div class="subsection-title">
                    Peraturan Terkait
                </div>

                <table class="report-table">
                    <thead>
                        <tr>
                            <th width="10%">No</th>
                            <th>Peraturan</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($form1Peraturan)): ?>

                            <?php foreach ($form1Peraturan as $i => $row): ?>
                                <tr>
                                    <td class="text-center">
                                        <?= $i + 1 ?>
                                    </td>

                                    <td>
                                        <?= esc($row['nama_peraturan'] ?? '-') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="2" class="text-center">
                                    Belum ada data peraturan terkait.
                                </td>
                            </tr>

                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>

    <?php endif; ?>

</div>