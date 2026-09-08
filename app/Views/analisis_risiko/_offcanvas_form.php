<div class="offcanvas offcanvas-end shadow-lg"
    tabindex="-1"
    id="arOffcanvas">

    <div class="offcanvas-header border-bottom">
        <div>
            <h5 class="offcanvas-title mb-0 fw-semibold" id="arOffcanvasTitle">Analisis Risiko</h5>
            <small>Manajemen Risiko</small>
        </div>
    </div>

    <div class="offcanvas-body">
        <form id="arForm" novalidate>
            <input type="hidden" id="arMode" value="create">
            <input type="hidden" id="arId" value="">
            <input type="hidden" id="arIdIdentifikasi" name="id_identifikasi" value="">

            <!-- ===== PANEL: INFORMASI KONTEKS ===== -->
            <div class="ar-info-panel">
                <div class="ar-section-title">
                    <i class="ti ti-building me-1"></i>Informasi Konteks
                </div>
                <div class="ar-grid-2">
                    <div>
                        <div class="ar-info-row">
                            <span class="ar-info-label">Tahun</span>
                            <span class="ar-info-value" id="arInfoTahun">-</span>
                        </div>
                        <div class="ar-info-row">
                            <span class="ar-info-label">Tim Kerja</span>
                            <span class="ar-info-value" id="arInfoTimKerja">-</span>
                        </div>
                        <div class="ar-info-row">
                            <span class="ar-info-label">Kegiatan</span>
                            <span class="ar-info-value" id="arInfoKegiatan">-</span>
                        </div>
                    </div>
                    <div>
                        <div class="ar-info-row">
                            <span class="ar-info-label">Pengelola Risiko</span>
                            <span class="ar-info-value" id="arInfoPengelola">-</span>
                        </div>
                        <div class="ar-info-row">
                            <span class="ar-info-label">Sasaran Strategis</span>
                            <span class="ar-info-value" id="arInfoSasaran">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== PANEL: INFORMASI RISIKO ===== -->
            <div class="ar-info-panel">
                <div class="ar-section-title">
                    <i class="ti ti-shield me-1"></i>Informasi Risiko
                </div>
                <div class="ar-info-row">
                    <span class="ar-info-label">Proses Bisnis</span>
                    <span class="ar-info-value" id="arInfoProses">-</span>
                </div>
                <div class="ar-info-row">
                    <span class="ar-info-label">Sasaran Kinerja</span>
                    <span class="ar-info-value" id="arInfoSasaranKinerja">-</span>
                </div>
                <div class="ar-info-row">
                    <span class="ar-info-label">Pernyataan Risiko</span>
                    <span class="ar-info-value fw-semibold" id="arInfoPernyataan">-</span>
                </div>
                <div class="ar-grid-2" style="margin-top:2px">
                    <div class="ar-info-row">
                        <span class="ar-info-label">Penyebab</span>
                        <div class="ar-info-value" id="arInfoPenyebab"></div>
                    </div>
                    <div class="ar-info-row">
                        <span class="ar-info-label">Dampak</span>
                        <div class="ar-info-value" id="arInfoDampak"></div>
                    </div>
                </div>
            </div>

            <hr class="ar-divider">

            <!-- ===== SECTION: INPUT USER (Risiko Aktual + Pengendalian) ===== -->
            <div class="ar-input-zone">
                <!-- ===== SECTION: RISIKO AKTUAL ===== -->
                <div class="mb-3">
                    <div class="ar-section-title">
                        <i class="ti ti-chart-bar me-1"></i>Risiko Aktual
                    </div>

                    <div class="ar-grid-2">
                        <!-- Probability -->
                        <div>
                            <label class="ar-form-label">
                                Probability (Kemungkinan) <span class="text-danger">*</span>
                            </label>
                            <select name="id_kemungkinan" id="arKemungkinan"
                                class="form-select" required>
                                <option value="">— Pilih Level —</option>
                                <?php foreach ($kemungkinanList as $k): ?>
                                    <option value="<?= esc($k['id_kriteria']) ?>"
                                        data-level="<?= esc($k['level']) ?>"
                                        data-desc="<?= esc($k['deskripsi_frekuensi'] ?? '') ?>">
                                        Level <?= esc($k['level']) ?> — <?= esc($k['nama_level']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="ar-desc-hint" id="arDescKemungkinan"></div>
                        </div>

                        <!-- Dampak -->
                        <div>
                            <label class="ar-form-label">
                                Dampak <span class="text-danger">*</span>
                            </label>
                            <select name="id_dampak" id="arDampak"
                                class="form-select" required>
                                <option value="">— Pilih Level —</option>
                                <?php foreach ($dampakList as $d): ?>
                                    <option value="<?= esc($d['id_kriteria']) ?>"
                                        data-level="<?= esc($d['level']) ?>"
                                        data-desc="<?= esc($d['deskripsi'] ?? '') ?>">
                                        Level <?= esc($d['level']) ?> — <?= esc($d['nama_level']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="ar-desc-hint" id="arDescDampak"></div>
                        </div>
                    </div>

                    <!-- Preview Skor -->
                    <div id="arPreview" class="ar-preview-card d-none">
                        <div>
                            <div class="ar-preview-label">Skor Risiko</div>
                            <div class="ar-preview-nilai" id="arPreviewNilai">0</div>
                        </div>
                        <div class="text-end">
                            <div id="arPreviewBadge" class="ar-preview-badge"></div>
                            <div class="ar-preview-tindakan" id="arPreviewTindakan"></div>
                        </div>
                    </div>
                </div>

                <hr class="ar-divider">

                <!-- ===== SECTION: PENGENDALIAN ===== -->
                <div class="mb-3">
                    <div class="ar-section-title">
                        <i class="ti ti-shield-check me-1"></i>Pengendalian yang Telah Dilaksanakan
                    </div>

                    <div class="mb-3">
                        <label class="ar-form-label">Uraian Pengendalian</label>
                        <!-- VIEW MODE -->
                        <div id="arInfoPengendalian" class="ar-info-value mb-2"></div>

                        <!-- EDIT MODE -->
                        <textarea name="uraian_pengendalian" id="arUraianPengendalian"
                            class="form-control d-none" rows="3"
                            placeholder="Masukkan pengendalian yang telah dilaksanakan"></textarea>
                    </div>

                    <div style="max-width: 320px;">
                        <label class="ar-form-label">
                            Efektivitas <span class="text-danger">*</span>
                        </label>
                        <select name="efektivitas" id="arEfektivitas"
                            class="form-select" required>
                            <option value="">— Pilih —</option>
                            <option value="Efektif">Efektif</option>
                            <option value="Kurang Efektif">Kurang Efektif</option>
                            <option value="Tidak Efektif">Tidak Efektif</option>
                        </select>
                    </div>
                </div>
            </div><!-- end ar-input-zone -->

            <hr class="ar-divider">

            <!-- ===== BUTTONS ===== -->
            <div class="d-flex align-items-center pt-3 border-top">

                <!-- DELETE (admin only nanti) -->
                <div>
                    <button type="button" id="arBtnDelete"
                        class="btn btn-sm btn-danger d-none">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>

                <!-- RIGHT SIDE -->
                <div class="ms-auto d-flex gap-2">

                    <!-- EDIT -->
                    <button type="button" id="arBtnEdit"
                        class="btn btn-sm btn-warning text-white d-none">
                        Edit
                    </button>

                    <!-- CANCEL -->
                    <button type="button" id="arBtnBatal"
                        class="btn btn-sm btn-light d-none">
                        Batal
                    </button>

                    <!-- SAVE -->
                    <button type="submit" id="arBtnSimpan"
                        class="btn btn-sm btn-primary px-4 d-none">
                        <i class="ti ti-device-floppy me-1"></i>Simpan
                    </button>

                    <button type="button" id="arBtnTutup"
                        class="btn btn-sm btn-light d-none"
                        data-bs-dismiss="offcanvas">
                        Tutup
                    </button>

                </div>
            </div>
        </form>
    </div>
</div>