<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <title>
        Laporan Pemantauan Risiko
    </title>

    <style>
        <?= file_get_contents(FCPATH . 'assets/css/pelaporan-risiko-print.css') ?>
    </style>
</head>

<body>
    <?php
    $formTitle = match ($form) {
        'form1' => 'FORMULIR LINGKUP, KONTEKS, KRITERIA MANAJEMEN RISIKO										',
        'form2' => 'FORMULIR PENILAIAN RISIKO															',
        'form3' => 'FORMULIR RENCANA TINDAK PENANGANAN RISIKO								',
        'form4' => 'FORMULIR PEMANTAUAN RISIKO																	',
        default => 'LAPORAN RISIKO',
    };
    ?>
    <div class="report-wrapper <?= in_array($form, ['form1', 'form2', 'form3', 'form4']) ? $form . '-print' : '' ?>"> <?= view('pelaporan_risiko/pdf/header', [
                                                                                                                            'formTitle' => $formTitle
                                                                                                                        ]) ?>
        <?php if ($form !== 'form1'): ?>
            <?= view('pelaporan_risiko/pdf/report_info') ?>
        <?php endif; ?>

        <?php if ($form === 'form1'): ?>
            <?= view('pelaporan_risiko/pdf/sections/form1_konteks') ?>
        <?php elseif ($form === 'form2'): ?>
            <?= view('pelaporan_risiko/pdf/sections/form2_risiko') ?>
        <?php elseif ($form === 'form3'): ?>
            <?= view('pelaporan_risiko/pdf/sections/form3_rtp') ?>
        <?php elseif ($form === 'all'): ?>
            <?= view('pelaporan_risiko/pdf/sections/form1_konteks') ?>
            <?= view('pelaporan_risiko/pdf/sections/form2_risiko') ?>
            <?= view('pelaporan_risiko/pdf/sections/form3_rtp') ?>
            <?= view('pelaporan_risiko/pdf/sections/form4_pelaporan') ?>
        <?php else: ?>
            <?= view('pelaporan_risiko/pdf/sections/form4_pelaporan') ?>
        <?php endif; ?>
        <?= view('pelaporan_risiko/pdf/signature') ?>
    </div>
</body>

</html>