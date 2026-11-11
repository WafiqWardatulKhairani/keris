<div class="offcanvas offcanvas-end shadow-lg"
    tabindex="-1"
    id="erOffcanvas">

    <div class="offcanvas-header border-bottom">
        <div>
            <h5 class="offcanvas-title mb-0 fw-semibold" id="erOffcanvasTitle">
                Evaluasi Risiko
            </h5>
            <small>Manajemen Risiko</small>
        </div>
    </div>

    <div class="offcanvas-body">

        <form id="erForm" novalidate>

            <input type="hidden" id="erMode" value="create">
            <input type="hidden" id="erId" value="">
            <input type="hidden" id="erIdIdentifikasi" name="id_identifikasi" value="">
            <input type="hidden" id="erIdPenilaian" name="id_penilaian" value="">

            <!-- ===== PANEL: INFORMASI KONTEKS ===== -->
            <div class="ar-info-panel">
                <div class="ar-section-title">
                    <i class="ti ti-building me-1"></i>Informasi Konteks
                </div>

                <div class="ar-grid-2">

                    <div>
                        <div class="ar-info-row">
                            <span class="ar-info-label">Tahun</span>
                            <span class="ar-info-value" id="erInfoTahun">-</span>
                        </div>

                        <div class="ar-info-row">
                            <span class="ar-info-label">Tim Kerja</span>
                            <span class="ar-info-value" id="erInfoTimKerja">-</span>
                        </div>

                        <div class="ar-info-row">
                            <span class="ar-info-label">Kegiatan</span>
                            <span class="ar-info-value" id="erInfoKegiatan">-</span>
                        </div>
                    </div>

                    <div>
                        <div class="ar-info-row">
                            <span class="ar-info-label">Pengelola Risiko</span>
                            <span class="ar-info-value" id="erInfoPengelola">-</span>
                        </div>

                        <div class="ar-info-row">
                            <span class="ar-info-label">Sasaran Strategis</span>
                            <span class="ar-info-value" id="erInfoSasaran">-</span>
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
                    <span class="ar-info-value" id="erInfoProses">-</span>
                </div>

                <div class="ar-info-row">
                    <span class="ar-info-label">Sasaran Kinerja</span>
                    <span class="ar-info-value" id="erInfoSasaranKinerja">-</span>
                </div>

                <div class="ar-info-row">
                    <span class="ar-info-label">Pernyataan Risiko</span>
                    <span class="ar-info-value fw-semibold" id="erInfoPernyataan">-</span>
                </div>

                <div class="ar-grid-2" style="margin-top:2px">

                    <div class="ar-info-row">
                        <span class="ar-info-label">Penyebab</span>
                        <span class="ar-info-value" id="erInfoPenyebab">-</span>
                    </div>

                    <div class="ar-info-row">
                        <span class="ar-info-label">Dampak</span>
                        <span class="ar-info-value" id="erInfoDampakRisiko">-</span>
                    </div>

                </div>

            </div>


            <hr class="ar-divider">


            <!-- ===== SECTION: RISIKO AKTUAL (READ ONLY) ===== -->
            <div class="mb-3">

                <div class="ar-section-title">
                    <i class="ti ti-chart-bar me-1"></i>Risiko Aktual
                </div>

                <div class="ar-grid-2">

                    <div class="ar-info-row">
                        <span class="ar-info-label">Probability</span>
                        <span class="ar-info-value" id="erInfoProb">-</span>
                    </div>

                    <div class="ar-info-row">
                        <span class="ar-info-label">Dampak</span>
                        <span class="ar-info-value" id="erInfoImpact">-</span>
                    </div>
                </div>

                <!-- Skor Risiko -->
                <div class="ar-preview-card">

                    <div>
                        <div class="ar-preview-label">Skor Risiko</div>
                        <div class="ar-preview-nilai" id="erPreviewNilai">0</div>
                    </div>

                    <div class="text-end">
                        <div id="erPreviewBadge" class="ar-preview-badge"></div>
                        <div id="erPreviewTindakan" class="ar-preview-tindakan"></div>
                    </div>
                </div>
            </div>

            <hr class="ar-divider">

            <!-- ===== SECTION: PENGENDALIAN (READ ONLY) ===== -->
            <div class="mb-3">

                <div class="ar-section-title">
                    <i class="ti ti-shield-check me-1"></i>
                    Pengendalian yang Telah Dilaksanakan
                </div>

                <div class="ar-info-row">
                    <span class="ar-info-label">Uraian Pengendalian</span>
                    <span class="ar-info-value" id="erInfoPengendalian">-</span>
                </div>

                <div class="ar-info-row">
                    <span class="ar-info-label">Efektivitas</span>
                    <span class="ar-info-value" id="erInfoEfektivitas">-</span>
                </div>
            </div>

            <hr class="ar-divider">

            <!-- ===== SECTION: RESPON RISIKO ===== -->
            <div class="er-input-zone">
                <div class="mb-3">

                    <div class="ar-section-title">
                        <i class="ti ti-shield-exclamation me-1"></i>
                        Respon Risiko
                    </div>

                    <div class="mb-3">

                        <label class="ar-form-label">
                            Opsi Tindakan <span class="text-danger">*</span>
                        </label>

                        <div id="erWarningBatas"
                            class="alert alert-warning py-2 px-3 mb-2 d-none"
                            role="alert">
                            <i class="ti ti-alert-triangle me-1"></i>
                            <strong>Peringatan Risiko!</strong>
                            <div class="small mt-1">
                                Skor risiko melebihi batas penerimaan (11).
                                Respons risiko tidak boleh menggunakan pilihan Menerima.
                            </div>
                        </div>

                        <select name="opsi_tindakan"
                            id="erOpsiTindakan"
                            class="form-select"
                            required>

                            <option value="">— Pilih —</option>
                            <option value="Menghindari">Menghindari</option>
                            <option value="Membagi">Membagi</option>
                            <option value="Mengurangi">Mengurangi</option>
                            <option value="Menerima">Menerima</option>
                        </select>
                    </div>

                    <div class="mb-3">

                        <label class="ar-form-label">
                            Keterangan
                        </label>

                        <textarea name="keterangan"
                            id="erKeterangan"
                            class="form-control"
                            rows="3"
                            placeholder="Tambahkan keterangan jika diperlukan..."></textarea>
                    </div>
                </div>
            </div><!-- end er-input-zone -->

            <hr class="ar-divider">

            <!-- ===== BUTTONS ===== -->
            <div class="d-flex align-items-center pt-3 border-top">
                <div>
                    <button type="button" id="erBtnDelete"
                        class="btn btn-sm btn-danger d-none"
                        onclick="erHapus()">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" id="erBtnEdit"
                        class="btn btn-sm btn-warning text-white d-none"
                        onclick="erSetMode('edit')">Edit</button>
                    <button type="button" id="erBtnBatal"
                        class="btn btn-sm btn-light d-none"
                        onclick="erBatal()">Batal</button>
                    <button type="submit" id="erBtnSimpan"
                        class="btn btn-sm btn-primary px-4 d-none">
                        <i class="ti ti-device-floppy me-1"></i>Simpan</button>
                    <button type="button" id="erBtnTutup"
                        class="btn btn-sm btn-light"
                        data-bs-dismiss="offcanvas">Tutup</button>
                </div>
            </div>
        </form>
    </div>
</div>