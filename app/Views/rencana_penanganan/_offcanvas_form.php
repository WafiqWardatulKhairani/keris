<div class="offcanvas offcanvas-end shadow-lg" tabindex="-1" id="rtpOffcanvas">
    <div class="offcanvas-header border-bottom">
        <div>
            <h5 class="offcanvas-title mb-0 fw-semibold" id="rtpOffcanvasTitle">Rencana Tindak Penanganan</h5>
            <small>Manajemen Risiko</small>
        </div>
    </div>
 
    <div class="offcanvas-body">

        <form id="rtpForm" novalidate>

            <input type="hidden" id="rtpMode" value="create">
            <input type="hidden" id="rtpId" value="">
            <input type="hidden" id="rtpIdEvaluasi" name="id_penilaian_awal" value="">

            <!-- ===== PANEL: INFORMASI KONTEKS ===== -->
            <div class="rtp-info-panel">
                <div class="rtp-section-title"><i class="ti ti-building me-1"></i>Informasi Konteks</div>

                <div class="rtp-grid-2">

                    <div>
                        <div class="rtp-info-row">
                            <span class="rtp-info-label">Tahun</span>
                            <span class="rtp-info-value" id="rtpInfoTahun">-</span>
                        </div>
                        <div class="rtp-info-row">
                            <span class="rtp-info-label">Tim Kerja</span>
                            <span class="rtp-info-value" id="rtpInfoTimKerja">-</span>
                        </div>
                        <div class="rtp-info-row">
                            <span class="rtp-info-label">Kegiatan</span>
                            <span class="rtp-info-value" id="rtpInfoKegiatan">-</span>
                        </div>
                    </div>

                    <div>
                        <div class="rtp-info-row">
                            <span class="rtp-info-label">Pengelola Risiko</span>
                            <span class="rtp-info-value" id="rtpInfoPengelola">-</span>
                        </div>
                        <div class="rtp-info-row">
                            <span class="rtp-info-label">Sasaran Strategis</span>
                            <span class="rtp-info-value" id="rtpInfoSasaran">-</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ===== PANEL: INFORMASI RISIKO ===== -->
            <div class="rtp-info-panel">

                <div class="rtp-section-title">
                    <i class="ti ti-shield me-1"></i>Informasi Risiko
                </div>

                <div class="rtp-info-row">
                    <span class="rtp-info-label">Proses Bisnis</span>
                    <span class="rtp-info-value" id="rtpInfoProses">-</span>
                </div>

                <div class="rtp-info-row">
                    <span class="rtp-info-label">Pernyataan Risiko</span>
                    <span class="rtp-info-value fw-semibold" id="rtpInfoPernyataan">-</span>
                </div>

                <div class="rtp-grid-2" style="margin-top:2px">
                    <div class="rtp-info-row">
                        <span class="rtp-info-label">Penyebab</span>
                        <span class="rtp-info-value" id="rtpInfoPenyebab">-</span>
                    </div>
                    <div class="rtp-info-row">
                        <span class="rtp-info-label">Dampak</span>
                        <span class="rtp-info-value" id="rtpInfoDampakRisiko">-</span>
                    </div>
                </div>

            </div>

            <hr class="rtp-divider">

            <!-- ===== SECTION: RISIKO AKTUAL ===== -->
            <div class="mb-3">

                <div class="rtp-section-title">
                    <i class="ti ti-chart-bar me-1"></i>Risiko Aktual
                </div>

                <div class="rtp-grid-2">
                    <div class="rtp-info-row">
                        <span class="rtp-info-label">Probability</span>
                        <span class="rtp-info-value" id="rtpInfoProb">-</span>
                    </div>
                    <div class="rtp-info-row">
                        <span class="rtp-info-label">Dampak</span>
                        <span class="rtp-info-value" id="rtpInfoImpact">-</span>
                    </div>
                </div>

                <div class="rtp-preview-card">
                    <div>
                        <div class="rtp-preview-label">Skor Risiko Aktual</div>
                        <div class="rtp-preview-nilai" id="rtpPreviewNilai">0</div>
                    </div>
                    <div class="text-end">
                        <div id="rtpPreviewBadge" class="rtp-preview-badge"></div>
                        <div id="rtpPreviewTindakan" class="rtp-preview-tindakan"></div>
                    </div>
                </div>

            </div>

            <hr class="rtp-divider">

            <!-- ===== PENANGGUNG JAWAB ===== -->
            <div class="mb-3">

                <div class="rtp-section-title">
                    <i class="ti ti-user me-1"></i>Penanggung Jawab
                </div>

                <div class="rtp-info-row">
                    <span class="rtp-info-label">Penanggung Jawab</span>
                    <span class="rtp-info-value fw-semibold" id="rtpInfoPenanggungjawab">-</span>
                </div>

            </div>

            <hr class="rtp-divider">

            <!-- ===== INPUT ZONE: RTP + RESIDU ===== -->
            <div class="rtp-input-zone">
                <!-- ===== RENCANA TINDAK PENANGANAN ===== -->
                <div class="mb-3">

                    <div class="rtp-section-title">
                        <i class="ti ti-list-check me-1"></i>Rencana Tindak Penanganan
                    </div>

                    <!-- CREATE MODE -->
                    <div id="rtpCreateContainer"></div>

                    <!-- TEMPLATE CARD -->
                    <template id="rtpCardTemplate">
                        <div class="rtp-card">
                            <div class="rtp-card-header">
                                <span class="rtp-card-title">RTP</span>
                                <button type="button" class="rtp-card-remove">
                                    <i class="ti ti-x"></i>
                                </button>
                            </div>
                            <div class="mb-2">
                                <label class="rtp-form-label">Uraian RTP</label>
                                <textarea name="uraian_rtp[]" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="rtp-target-grid">
                                <div>
                                    <label class="rtp-form-label rtp-sub-label">Output</label>
                                    <input type="text" name="target_output[]" class="form-control">
                                </div>
                                <div>
                                    <label class="rtp-form-label rtp-sub-label">Waktu</label>
                                    <input type="month" name="target_waktu[]" class="form-control">
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- TIMELINE MODE -->
                    <div id="rtpTimelineContainer" class="d-none"></div>

                    <!-- ADD BUTTON -->
                    <div id="rtpAddWrapper" class="mt-2">
                        <button type="button" id="rtpAddBtn" class="btn btn-light btn-sm">
                            <i class="ti ti-plus"></i>Tambah RTP
                        </button>
                    </div>

                </div>

                <hr class="rtp-divider">

                <!-- ===== RISIKO RESIDU ===== -->
                <div class="mb-3">

                    <div class="rtp-section-title">
                        <i class="ti ti-activity me-1"></i>Risiko Residu
                    </div>

                    <div class="rtp-grid-2">

                        <div class="mb-3">
                            <label class="rtp-form-label">Probability (P)</label>
                            <select name="id_kemungkinan_residu" id="rtpKemungkinanResidu" class="form-select">
                                <option value="">— Pilih —</option>
                                <?php if (!empty($kriteriaKemungkinan)): ?>
                                    <?php foreach ($kriteriaKemungkinan as $k): ?>
                                        <option
                                            value="<?= esc($k['id_kriteria']) ?>"
                                            data-level="<?= esc($k['level']) ?>"
                                            data-nama="<?= esc($k['nama_level']) ?>">
                                            <?= esc($k['level']) ?> - <?= esc($k['nama_level']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="rtp-form-label">Dampak (D)</label>
                            <select name="id_dampak_residu" id="rtpDampakResidu" class="form-select">
                                <option value="">— Pilih —</option>
                                <?php if (!empty($kriteriaDampak)): ?>
                                    <?php foreach ($kriteriaDampak as $k): ?>
                                        <option
                                            value="<?= esc($k['id_kriteria']) ?>"
                                            data-level="<?= esc($k['level']) ?>"
                                            data-nama="<?= esc($k['nama_level']) ?>">
                                            <?= esc($k['level']) ?> - <?= esc($k['nama_level']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                    </div>

                    <div class="rtp-residu-card">
                        <div>
                            <div class="rtp-preview-label">Skor Risiko Residu</div>
                            <div class="rtp-residu-nilai" id="rtpResiduNilai">0</div>
                        </div>
                        <div class="text-end">
                            <div id="rtpResiduBadge" class="rtp-residu-badge"></div>
                            <div id="rtpResiduTindakan" class="rtp-preview-tindakan"></div>
                        </div>
                    </div>

                </div>
            </div><!-- end rtp-input-zone -->

            <hr class="rtp-divider">

            <!-- ===== BUTTONS ===== -->
            <div class="d-flex align-items-center pt-3 border-top">
                <div>
                    <button type="button" id="rtpBtnHapus"
                        class="btn btn-sm btn-danger d-none"
                        onclick="rtpHapus()">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" id="rtpBtnEdit"
                        class="btn btn-sm btn-warning text-white d-none"
                        onclick="rtpSetMode('edit')">
                        <i class="ti ti-pencil me-1"></i>Edit</button>
                    <button type="button" id="rtpBtnBatal"
                        class="btn btn-sm btn-light d-none"
                        onclick="rtpBatal()">Batal</button>
                    <button type="submit"
                        id="rtpBtnSimpan"
                        class="btn btn-sm btn-primary px-4">
                        <i class="ti ti-device-floppy me-1"></i>Simpan
                    </button>
                    <button type="button" id="rtpBtnTutup"
                        class="btn btn-sm btn-light"
                        data-bs-dismiss="offcanvas">Tutup</button>
                </div>
            </div>
        </form>
    </div>
</div>