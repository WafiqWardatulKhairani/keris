<div class="signature-wrapper">

    <table class="signature-table">
        <tr>
            <td>
                <div class="signature-date invisible-date">
                    Pekanbaru, <?= date('d') ?>
                    <?= esc($bulan ?? '-') ?>
                    <?= esc($tahun ?? '-') ?>
                </div>
                <div class="signature-role">
                    Pengelola Risiko,
                </div>

                <div class="signature-role">
                    Ketua Tim <?= esc($timkerja ?? '-') ?>
                </div>

                <div class="signature-space"></div> 

                <div class="signature-name">
                    <?= esc($nama_ketua ?? '-') ?>
                </div>

                <div class="signature-nip">
                    NIP. <?= esc($nip_ketua ?? '-') ?>
                </div>

            </td>
            <td>
                <div class="signature-date">
                    Pekanbaru, <?= date('d') ?>
                    <?= esc($bulan ?? '-') ?>
                    <?= esc($tahun ?? '-') ?>
                </div>

                <div class="signature-role">
                    Pemilik Risiko,
                </div>

                <div class="signature-role">
                    Kepala BPS Provinsi Riau
                </div>

                <div class="signature-space"></div>

                <div class="signature-name">
                    <?= esc($nama_pemilik ?? '-') ?>
                </div>

                <div class="signature-nip">
                    NIP. <?= esc($nip_pemilik ?? '-') ?>
                </div>
            </td>
        </tr>
    </table>
</div>