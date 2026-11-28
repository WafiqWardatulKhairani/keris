    <?php
    $konteksMap = [];
    $timKerjaOpt = [];
    $pengelolaOpt = [];

    // Tim yang boleh diakses user
    $userTim = array_map('intval', session('user_tim') ?? []);

    // Fallback legacy
    if (empty($userTim) && session('id_tim')) {
        $userTim = [(int) session('id_tim')];
    }

    foreach ($listKonteks as $k) {
        $id = $k['id_konteks'];

        $konteksMap[$id] = [
            'id_tim' => $k['id_tim'] ?? '',
            'pengelola_risiko_id' => $k['pengelola_risiko_id'] ?? '',
            'nama_pengelola' => $k['nama_pengelola'] ?? '',
            'id_kegiatan' => $k['id_kegiatan'] ?? '',
            'nama_kegiatan' => $k['nama_kegiatan'] ?? '',
            'tahun' => $k['tahun'],
        ];

        // Admin boleh melihat seluruh tim.
        // Operator/Ketua hanya tim yang dimiliki.
        if (!empty($k['id_tim'])) {
            $idTimKonteks = (int) $k['id_tim'];

            if (
                $userRole === 'admin'
                || in_array($idTimKonteks, $userTim, true)
            ) {
                $timKerjaOpt[$idTimKonteks] = $k['nama_tim'];
            }
        }

        if (!empty($k['pengelola_risiko_id'])) {
            $pengelolaOpt[$k['pengelola_risiko_id']]
                = $k['nama_pengelola'];
        }
    }

    asort($timKerjaOpt);
    asort($pengelolaOpt);

    $sel = $activeKonteks ?? [];

    $currentYear = (int) (
        service('request')->getGet('tahun')
        ?? $periode['tahun']
        ?? date('Y')
    );

    $selectedTriwulan = (string) (
        service('request')->getGet('triwulan') ?? ''
    );

    $tahunOptions = array_unique(array_filter(
        array_column($listKonteks, 'tahun')
    ));

    $tahunOptions[] = $currentYear;
    $tahunOptions = array_unique(array_map('intval', $tahunOptions));
    rsort($tahunOptions);
    ?>

    <div class="pl-filter-card mb-3">
        <form id="plContextSelectorForm" method="get" action="<?= site_url('pelaporan-risiko') ?>">
            <input type="hidden" name="id_konteks" id="plCsIdKonteks">
            <div class="pl-filter-inner">
                <div class="pl-filter-section pl-filter-context">
                    <div class="pl-section-label"><i class="ti ti-building"></i> Konteks</div>
                    <div class="pl-field-group">

                        <div class="pl-field">
                            <span class="pl-field-label">Tim Kerja</span>
                            <div class="pl-field-value">
                                <span class="pl-field-static">
                                    <?php
                                    $namaTimAktif = '-';

                                    foreach ($listKonteks as $k) {
                                        if (
                                            (string) ($k['id_tim'] ?? '') ===
                                            (string) ($sel['id_tim'] ?? '')
                                        ) {
                                            $namaTimAktif = $k['nama_tim'] ?? '-';
                                            break;
                                        }
                                    }
                                    ?>
                                    <?= esc($namaTimAktif) ?>
                                </span>
                            </div>
                        </div>

                        <div class="pl-field">
                            <span class="pl-field-label">Pengelola</span>
                            <div class="pl-field-value">
                                <?php
                                $namaPengelolaAktif = '-';

                                foreach ($listKonteks as $k) {
                                    if (
                                        (string) ($k['id_tim'] ?? '') ===
                                        (string) ($sel['id_tim'] ?? '')
                                        && !empty($k['nama_pengelola'])
                                    ) {
                                        $namaPengelolaAktif = $k['nama_pengelola'];
                                        break;
                                    }
                                }
                                ?>
                                <span class="pl-field-static">
                                    <?= esc($namaPengelolaAktif) ?>
                                </span>
                            </div>
                        </div>
                        <div class="pl-field">
                            <span class="pl-field-label">Kegiatan</span>
                            <div class="pl-field-value">
                                <select class="pl-select" id="plCsKegiatan" name="id_kegiatan">
                                    <option value="">Semua Kegiatan</option>
                                </select>
                            </div>
                        </div>

                        <?php if (in_array($userRole, ['operator', 'ketua'])): ?>
                            <div class="pl-field">
                                <span class="pl-field-label">Status Validasi</span>

                                <div class="pl-field-value">
                                    <select
                                        class="pl-select"
                                        id="plCsStatusValidasi"
                                        name="status_validasi">

                                        <option value="">Semua Status</option>

                                        <?php if (($userRole ?? '') !== 'ketua'): ?>
                                            <option value="Draft"
                                                <?= ($statusValidasi ?? '') === 'Draft' ? 'selected' : '' ?>>
                                                Draft
                                            </option>
                                        <?php endif; ?>

                                        <option value="Diajukan"
                                            <?= ($statusValidasi ?? '') === 'Diajukan' ? 'selected' : '' ?>>
                                            Diajukan
                                        </option>

                                        <option value="Disetujui"
                                            <?= ($statusValidasi ?? '') === 'Disetujui' ? 'selected' : '' ?>>
                                            Disetujui
                                        </option>

                                        <option value="Ditolak"
                                            <?= ($statusValidasi ?? '') === 'Ditolak' ? 'selected' : '' ?>>
                                            Ditolak
                                        </option>

                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="pl-filter-section pl-filter-periode">
                    <div class="pl-section-label">
                        <i class="ti ti-calendar"></i> PERIODE
                    </div>

                    <div class="pl-periode-modern">

                        <div class="pl-tahun-row">
                            <span class="pl-field-label">Tahun</span>

                            <select name="tahun" id="plCsTahun" class="pl-select">
                                <?php foreach ($tahunOptions as $tahun): ?>
                                    <option value="<?= $tahun ?>"
                                        <?= $currentYear === $tahun ? 'selected' : '' ?>>
                                        <?= $tahun ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="pl-triwulan-label">Pilih Triwulan</div>

                        <div class="pl-triwulan-grid">

                            <label class="pl-triwulan-card <?= $selectedTriwulan === '1' ? 'active' : '' ?>">
                                <input type="radio" name="triwulan" value="1"
                                    <?= $selectedTriwulan === '1' ? 'checked' : '' ?>>
                                <span>TW I</span>
                            </label>

                            <label class="pl-triwulan-card <?= $selectedTriwulan === '2' ? 'active' : '' ?>">
                                <input type="radio" name="triwulan" value="2"
                                    <?= $selectedTriwulan === '2' ? 'checked' : '' ?>>
                                <span>TW II</span>
                            </label>

                            <label class="pl-triwulan-card <?= $selectedTriwulan === '3' ? 'active' : '' ?>">
                                <input type="radio" name="triwulan" value="3"
                                    <?= $selectedTriwulan === '3' ? 'checked' : '' ?>>
                                <span>TW III</span>
                            </label>

                            <label class="pl-triwulan-card <?= $selectedTriwulan === '4' ? 'active' : '' ?>">
                                <input type="radio" name="triwulan" value="4"
                                    <?= $selectedTriwulan === '4' ? 'checked' : '' ?>>
                                <span>TW IV</span>
                            </label>

                        </div>

                        <label class="pl-semua-periode">
                            <input type="radio" name="triwulan" value=""
                                <?= $selectedTriwulan === '' ? 'checked' : '' ?>>
                            <span>Semua Periode</span>
                        </label>

                    </div> <!-- pl-periode-modern -->
                </div> <!-- pl-filter-periode -->

            </div> <!-- pl-filter-inner -->
                                    <button type="submit" class="pl-btn-submit">
                            <i class="ti ti-search"></i> Tampilkan
                        </button>
        </form>
    </div> <!-- pl-filter-card -->

    <script>
        window.PL_CS_DATA = {
            konteksMap: <?= json_encode($konteksMap) ?>,
            listKegiatan: <?= json_encode($listKegiatan) ?>,
            activeTimId: <?= json_encode(
                                $sel['id_tim']
                                    ?? session('global_id_tim')
                                    ?? session('id_tim')
                            ) ?>,
            selectedKegiatan: <?= json_encode($selectedKegiatan ?? '') ?>
        };
    </script>