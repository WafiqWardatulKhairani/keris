<div class="offcanvas offcanvas-end shadow-lg" tabindex="-1" id="plOffcanvas">

    <div class="offcanvas-header border-bottom">
        <div>
            <h5 class="mb-0 fw-semibold">Pelaporan Risiko</h5>
            <small>Detail & Validasi Ketua Tim</small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body">

        <!-- ===== INFORMASI KONTEKS ===== -->
        <div class="ar-info-panel">
            <div class="ar-section-title">
                <i class="ti ti-building me-1"></i>Informasi Konteks
            </div>
            <div class="ar-grid-2">
                <div> 
                    <div class="ar-info-row">
                        <span class="ar-info-label">Tahun</span>
                        <span class="ar-info-value" id="plInfoTahun">-</span>
                    </div>
                    <div class="ar-info-row">
                        <span class="ar-info-label">Tim Kerja</span>
                        <span class="ar-info-value" id="plInfoTimKerja">-</span>
                    </div>
                    <div class="ar-info-row">
                        <span class="ar-info-label">Kegiatan</span>
                        <span class="ar-info-value" id="plInfoKegiatan">-</span>
                    </div>
                </div>
                <div>
                    <div class="ar-info-row">
                        <span class="ar-info-label">Pengelola Risiko</span>
                        <span class="ar-info-value" id="plInfoPengelola">-</span>
                    </div>
                    <div class="ar-info-row">
                        <span class="ar-info-label">Sasaran Strategis</span>
                        <span class="ar-info-value" id="plInfoSasaran">-</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== INFORMASI RISIKO ===== -->
        <div class="ar-info-panel">
            <div class="ar-section-title">
                <i class="ti ti-shield me-1"></i>Informasi Risiko
            </div>
            <div class="ar-info-row">
                <span class="ar-info-label">Proses Bisnis</span>
                <span class="ar-info-value" id="plInfoProses">-</span>
            </div>
            <div class="ar-info-row">
                <span class="ar-info-label">Sasaran Kinerja</span>
                <span class="ar-info-value" id="plInfoSasaranKinerja">-</span>
            </div>
            <div class="ar-info-row">
                <span class="ar-info-label">Pernyataan Risiko</span>
                <span class="ar-info-value fw-semibold" id="plInfoRisiko">-</span>
            </div>
            <div class="ar-grid-2">
                <div class="ar-info-row">
                    <span class="ar-info-label">Penyebab</span>
                    <span class="ar-info-value" id="plInfoPenyebab">-</span>
                </div>
                <div class="ar-info-row">
                    <span class="ar-info-label">Dampak</span>
                    <span class="ar-info-value" id="plInfoDampak">-</span>
                </div>
            </div>
        </div>

        <hr class="ar-divider">

        <!-- ===== RISIKO AKTUAL ===== -->
        <div class="mb-3">
            <div class="ar-section-title">
                <i class="ti ti-chart-bar me-1"></i>Risiko Aktual
            </div>
            <div class="ar-grid-2">
                <div class="ar-info-row">
                    <span class="ar-info-label">Probability</span>
                    <span class="ar-info-value" id="plInfoProb">-</span>
                </div>
                <div class="ar-info-row">
                    <span class="ar-info-label">Dampak</span>
                    <span class="ar-info-value" id="plInfoImpact">-</span>
                </div>
            </div>
            <div class="ar-preview-card">
                <div>
                    <div class="ar-preview-label">Skor Risiko</div>
                    <div class="ar-preview-nilai" id="plPreviewNilai">0</div>
                </div>
                <div class="text-end">
                    <div id="plPreviewBadge" class="ar-preview-badge"></div>
                    <div id="plPreviewTindakan" class="ar-preview-tindakan"></div>
                </div>
            </div>
        </div>

        <hr class="ar-divider">

        <!-- ===== PENGENDALIAN ===== -->
        <div class="mb-3">
            <div class="ar-section-title">
                <i class="ti ti-shield-check me-1"></i>Pengendalian yang Telah Dilaksanakan
            </div>
            <div class="ar-info-row">
                <span class="ar-info-label">Uraian Pengendalian</span>
                <span class="ar-info-value" id="plInfoPengendalian">-</span>
            </div>
            <div class="ar-info-row">
                <span class="ar-info-label">Efektivitas</span>
                <span class="ar-info-value" id="plInfoEfektivitas">-</span>
            </div>
        </div>

        <hr class="ar-divider">

        <!-- ===== RTP ===== -->
        <div class="ar-info-panel">
            <div class="ar-section-title">
                <i class="ti ti-list-check me-1"></i>Rencana Tindak Pengendalian
            </div>
            <div class="ar-info-row">
                <span class="ar-info-label">Uraian RTP</span>
                <span class="ar-info-value" id="plInfoRtp">-</span>
            </div>
            <div class="ar-grid-2">
                <div class="ar-info-row">
                    <span class="ar-info-label">Target Output</span>
                    <span class="ar-info-value" id="plTargetOutput">-</span>
                </div>
                <div class="ar-info-row">
                    <span class="ar-info-label">Target Waktu</span>
                    <span class="ar-info-value" id="plTargetWaktu">-</span>
                </div>
            </div>
        </div>

        <hr class="ar-divider">

        <!-- ===== REALISASI ===== -->
        <div class="ar-info-panel">
            <div class="ar-section-title">
                <i class="ti ti-checklist me-1"></i>Realisasi
            </div>
            <div class="ar-grid-2">
                <div class="ar-info-row">
                    <span class="ar-info-label">Output</span>
                    <span class="ar-info-value" id="plRealisasiOutput">-</span>
                </div>
                <div class="ar-info-row">
                    <span class="ar-info-label">Waktu</span>
                    <span class="ar-info-value" id="plRealisasiWaktu">-</span>
                </div>
            </div>
            <div class="ar-info-row">
                <span class="ar-info-label">Status</span>
                <span class="ar-info-value" id="plStatus">-</span>
            </div>

            <div class="ar-info-row" id="plRowBukti">
                <span class="ar-info-label">Bukti</span>

                <span class="ar-info-value">
                    <a href="#" target="_blank" id="plLinkBukti">
                        Lihat Bukti
                    </a>
                </span>
            </div>
        </div>

        <hr class="ar-divider">

        <div class="mb-3">
            <div class="ar-section-title">
                <i class="ti ti-shield-half-filled me-1"></i>Risiko Residu
            </div>

            <div class="ar-grid-2">
                <div class="ar-info-row">
                    <span class="ar-info-label">Probability</span>
                    <span class="ar-info-value" id="plInfoProbResidu">-</span>
                </div>

                <div class="ar-info-row">
                    <span class="ar-info-label">Dampak</span>
                    <span class="ar-info-value" id="plInfoImpactResidu">-</span>
                </div>
            </div>

            <div class="ar-preview-card">
                <div>
                    <div class="ar-preview-label">Skor Residu</div>
                    <div class="ar-preview-nilai" id="plPreviewNilaiResidu">0</div>
                </div>

                <div class="text-end">
                    <div id="plPreviewBadgeResidu" class="ar-preview-badge"></div>
                </div>
            </div>
        </div>

    </div>
</div>