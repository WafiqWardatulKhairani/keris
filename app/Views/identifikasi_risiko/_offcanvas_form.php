<div class="offcanvas offcanvas-end shadow-lg" tabindex="-1"
    id="offcanvasRisiko">

    <!-- HEADER -->
    <div class="offcanvas-header border-bottom" style="background:#f8f9fa;">
        <div>
            <h5 class="mb-0 fw-semibold" id="irOffcanvasTitle">Tambah Risiko</h5>
            <small class="text-muted">Identifikasi Risiko</small>
        </div>
    </div>

    <!-- BODY -->
    <div class="offcanvas-body">

        <!-- INFO KONTEKS -->
        <?php if ($activeKonteks): ?>
            <div class="card bg-light border-0 mb-2">
                <div class="card-body py-2">
                    <div style="font-size:12px; color:#6b7280; line-height:1.8;">
                        <div class="d-flex">
                            <span style="width:130px; flex-shrink:0;">Tahun</span>
                            <span style="margin-right:8px;">:</span>
                            <span class="text-dark fw-500"><?= esc($activeKonteks['tahun'] ?? 'Berdasarkan filter') ?></span>
                        </div>
                        <div class="d-flex">
                            <span style="width:130px; flex-shrink:0;">Tim Kerja</span>
                            <span style="margin-right:8px;">:</span>
                            <span class="text-dark fw-500"><?= esc($activeKonteks['nama_tim'] ?? '-') ?></span>
                        </div>
                        <div class="d-flex">
                            <span style="width:130px; flex-shrink:0;">Pengelola Risiko</span>
                            <span style="margin-right:8px;">:</span>
                            <span class="text-dark fw-500"><?= esc($activeKonteks['nama_pengelola'] ?? '-') ?></span>
                        </div>
                        <div class="d-flex">
                            <span style="width:130px; flex-shrink:0;">Sasaran Strategis</span>
                            <span style="margin-right:8px;">:</span>
                            <span class="text-dark fw-500"><?= esc($activeKonteks['uraian_sasaran'] ?? '-') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- FORM -->
        <form id="irForm" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="mode" id="irMode" value="create">
            <input type="hidden" name="id_identifikasi" id="irId">

            <!-- PROSES BISNIS -->
            <div class="mb-3">
                <label class="form-label ir-form-label">
                    Proses Bisnis <span class="text-danger">*</span>
                </label>
                <select class="form-select form-select-sm" name="id_konteks_proses" id="irKonteksProses" required>
                    <option value="">-- Pilih Proses Bisnis --</option>
                    <?php foreach ($listKonteksProses as $kp): ?>
                        <?php
                        $deskripsi = trim($kp['deskripsi_proses'] ?? '');
                        $subLabel = '';

                        if ($deskripsi !== '') {
                            $kataPertama = preg_split('/\s+/', $deskripsi)[0] ?? '';

                            $normalisasi = [
                                'Memeriksa' => 'Pemeriksaan',
                                'Membuat' => 'Pembuatan',
                                'Menerima' => 'Penerimaan',
                                'Mengirimkan' => 'Pengiriman',
                            ];

                            $subLabel = $normalisasi[$kataPertama] ?? $kataPertama;
                        }
                        ?>

                        <option value="<?= $kp['id_konteks_proses'] ?>">
                            <?= esc($kp['kode_proses']) ?>
                            — <?= esc($kp['uraian_proses']) ?>

                            <?php if ($subLabel !== ''): ?>
                                — <?= esc($subLabel) ?>
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">Proses bisnis wajib dipilih.</div>
            </div>

            <!-- PERNYATAAN RISIKO + AUTOCOMPLETE -->
            <div class="mb-3">
                <label class="form-label ir-form-label">
                    Pernyataan Risiko <span class="text-danger">*</span>
                </label>
                <div class="ir-autocomplete-wrapper">
                    <textarea class="form-control form-control-sm" name="pernyataan_risiko" id="irPernyataan"
                        rows="3" required
                        placeholder="Ketik untuk melihat rekomendasi dari bank risiko..."></textarea>
                    <div class="invalid-feedback">Pernyataan risiko wajib diisi.</div>
                    <div id="irBankSuggest" class="ir-suggest-dropdown" style="display:none;"></div>
                </div>
                <small class="text-muted mt-1 d-block">
                    <i class="ti ti-bulb text-warning me-1"></i>
                    Ketik minimal 2 karakter untuk melihat rekomendasi.
                    Gunakan <kbd>↑↓</kbd> dan <kbd>Enter</kbd> untuk memilih.
                </small>
            </div>

            <!-- PENYEBAB -->
            <div class="mb-3">
                <label class="form-label ir-form-label">Penyebab Risiko</label>
                <textarea class="form-control form-control-sm" name="penyebab_risiko" id="irPenyebab"
                    rows="2" required placeholder="Uraikan penyebab risiko..."></textarea>
            </div>

            <!-- DAMPAK -->
            <div class="mb-3">
                <label class="form-label ir-form-label">Dampak Risiko</label>
                <textarea class="form-control form-control-sm" name="dampak_risiko" id="irDampak"
                    rows="2" required placeholder="Uraikan dampak jika risiko terjadi..."></textarea>
            </div>

            <!-- KATEGORI RISIKO -->
            <div class="mb-3">
                <label class="form-label ir-form-label">Kategori Risiko</label>
                <select class="form-select form-select-sm" name="id_kategori_risiko" id="irKategori">
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($kategoriList as $k): ?>
                        <option value="<?= $k['id_kategori_risiko'] ?>">
                            <?= esc($k['nama_kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- AREA DAMPAK -->
            <div class="mb-3">
                <label class="form-label ir-form-label">Area Dampak</label>
                <select class="form-select form-select-sm" name="area_dampak" id="irAreaDampak" required>
                    <option value="">-- Pilih Area Dampak --</option>
                    <?php foreach ($areaDampakList as $ad): ?>
                        <option value="<?= $ad['id_area_dampak'] ?>">
                            <?= esc($ad['nama_area_dampak']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- SUMBER RISIKO -->
            <div class="mb-4">
                <label class="form-label ir-form-label">Sumber Risiko</label>
                <div class="d-flex gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio"
                            name="sumber_risiko" id="irSumberInternal" value="Internal" required>
                        <label class="form-check-label ir-check-label" for="irSumberInternal">Internal</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio"
                            name="sumber_risiko" id="irSumberEksternal" value="Eksternal" required>
                        <label class="form-check-label ir-check-label" for="irSumberEksternal">Eksternal</label>
                    </div>
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="ir-action-wrapper">

                <!-- CREATE -->
                <div id="irBtnCreate" class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>

                <!-- VIEW -->
                <div id="irBtnView" class="d-flex justify-content-between align-items-center d-none">
                    <button type="button" class="btn btn-danger" id="irBtnDelete">
                        <i class="ti ti-trash"></i>
                    </button>
                    <div class="d-flex gap-2 ms-auto">
                        <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Tutup</button>
                        <button type="button" class="btn btn-warning text-white" id="irBtnSwitchEdit">Edit</button>
                    </div>
                </div>

                <!-- EDIT -->
                <div id="irBtnEdit" class="d-flex justify-content-end gap-2 d-none">
                    <button type="button" class="btn btn-light" id="irBtnCancelEdit">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>

            </div>
        </form>
    </div>
</div>