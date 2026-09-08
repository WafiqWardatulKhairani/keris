<?php

use App\Models\TimKerjaModel;

$timModel = new TimKerjaModel();

$listTim = $timModel
    ->orderBy('nama_tim', 'ASC')
    ->findAll();

$db = \Config\Database::connect();

$listTahun = $db->table('konteks')
    ->select('tahun')
    ->distinct()
    ->orderBy('tahun', 'DESC')
    ->get()
    ->getResultArray();

$selectedTahun = session('global_tahun') ?? date('Y');

$selectedTim = session('global_id_tim') ?? session('id_tim');

$selectedKegiatan = session('global_id_kegiatan') ?? '';
?>
<div class="global-context-bar">

    <div class="context-group">
        <select name="ctx_tahun" id="ctx_tahun" class="select-tahun">

            <?php foreach ($listTahun as $row): ?>

                <option
                    value="<?= $row['tahun'] ?>"
                    <?= $selectedTahun == $row['tahun'] ? 'selected' : '' ?>>
                    <?= $row['tahun'] ?>
                </option>

            <?php endforeach; ?>

        </select>
    </div>

    <div class="context-group">
        <select name="ctx_tim" id="ctx_tim">

            <?php foreach ($listTim as $tim): ?>

                <option
                    value="<?= $tim['id_tim'] ?>"
                    <?= $selectedTim == $tim['id_tim'] ? 'selected' : '' ?>>
                    <?= esc($tim['nama_tim']) ?>
                </option>

            <?php endforeach; ?>

        </select>
    </div>

    <div class="context-group">
        <select name="ctx_kegiatan" id="ctx_kegiatan">
            <option value="">Semua Kegiatan</option>
        </select>
    </div>

    <button class="btn-context-reset" title="Reset filter" onclick="resetGlobalContext()">
        <i class="ti ti-rotate"></i>
    </button>

</div>

<link rel="stylesheet" href="<?= base_url('assets/css/layout/global-context-selector.css') ?>">
<script>
    window.GC_CONFIG = {
        csrf: {
            name: '<?= csrf_token() ?>',
            token: '<?= csrf_hash() ?>',
        },

        url: {
            set: '<?= site_url('global-context/set') ?>',
            kegiatan: '<?= site_url('global-context/kegiatan') ?>',
            reset: '<?= site_url('global-context/reset') ?>',

            konteks: '<?= site_url('penetapan-konteks/konteks') ?>'
        },

        default: {
    tahun: '<?= session('global_tahun') ?? date('Y') ?>',
    id_tim: '<?= session('global_id_tim') ?? session('id_tim') ?>',
    id_kegiatan: '<?= session('global_id_kegiatan') ?? '' ?>',

    has_context: <?= (
        session('global_tahun') !== null &&
        session('global_id_tim') !== null
    ) ? 'true' : 'false' ?>
},

        user: {
            role: '<?= session('user_role') ?>',
            id_tim: '<?= session('id_tim') ?>'
        }
    };
</script>
<script src="<?= base_url('assets/js/global-context-selector.js') ?>"></script>