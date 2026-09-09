<?php
$logoPath = ROOTPATH . 'tampilanSimiko/assets/img/BPS_Provinsi_Riau.png';

$logoBase64 = null;

if (is_file($logoPath)) {

    $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
    $logoData = file_get_contents($logoPath);

    $logoBase64 =
        'data:image/' .
        $logoType .
        ';base64,' .
        base64_encode($logoData);
}
?>

<div class="report-header">

    <table class="header-table">
        <tr>

            <td class="brand-cell">

                <?php if ($logoBase64): ?>
                    <img
                        src="<?= $logoBase64 ?>"
                        alt="BPS Provinsi Riau"
                        class="bps-brand-image">
                <?php endif; ?>

            </td>

            <td class="title-cell">
                <?= esc($formTitle ?? 'LAPORAN RISIKO') ?>
            </td>

        </tr>
    </table>

</div>

<div class="header-line"></div>