<?php

$labelDampak = [
    1 => 'Tidak Signifikan',
    2 => 'Minor',
    3 => 'Moderat',
    4 => 'Signifikan',
    5 => 'Sangat Signifikan',
];

$labelKemungkinan = [
    5 => 'Hampir Pasti Terjadi',
    4 => 'Sering Terjadi',
    3 => 'Kadang Terjadi',
    2 => 'Jarang Terjadi',
    1 => 'Hampir Tidak Terjadi',
];

$matrix = $matrix ?? [];

foreach ($matriks as $row) {
    $matrix[$row['level_kemungkinan']][$row['level_dampak']] = $row;
}
?>

<div class="dashboard-risk-matrix">
    <div class="table-responsive">

        <table class="risk-matrix">

            <thead>

                <tr>
                    <th rowspan="2">
                        Kemungkinan
                    </th>

                    <th colspan="5">
                        Dampak
                    </th>
                </tr>

                <tr>

                    <?php foreach ($labelDampak as $levelD => $label): ?>

                        <th class="risk-col-label">

                            <div class="risk-col-text">
                                <?= esc($label) ?>
                            </div>

                            <div class="risk-axis-number">
                                (<?= $levelD ?>)
                            </div>

                        </th>

                    <?php endforeach; ?>

                </tr>

            </thead>


            <tbody>

                <?php foreach ($labelKemungkinan as $levelK => $namaK): ?>

                    <tr>

                        <th class="risk-row-label">

                            <div class="risk-row-text">
                                <?= esc($namaK) ?>
                            </div>

                            <div class="risk-axis-number">
                                (<?= $levelK ?>)
                            </div>

                        </th>


                        <?php for ($d = 1; $d <= 5; $d++): ?>

                            <?php
                            $cell = $matrix[$levelK][$d] ?? null;
                            ?>

                            <td>

                                <div class="risk-cell <?= warna_risiko_class(
                                    $cell['warna'] ?? null
                                ) ?>">

                                    <div class="risk-score">
                                        <?= $cell['nilai_risiko'] ?? '-' ?>
                                    </div>

                                    <div
                                        class="risk-count"
                                        id="risk-count-<?= $levelK ?>-<?= $d ?>">
                                        0
                                    </div>

                                </div>

                            </td>

                        <?php endfor; ?>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>
</div>