<!-- ================= OFFCANVAS PEMANTAUAN RISIKO ================= -->
<div class="offcanvas offcanvas-end shadow-lg" tabindex="-1" id="prOffcanvas">
    <div class="offcanvas-header border-bottom">
        <div>
            <h5 class="offcanvas-title mb-0 fw-semibold" id="prOffcanvasTitle">Pemantauan Risiko</h5>
            <small>Manajemen Risiko</small>
        </div>
    </div>

    <div class="offcanvas-body">
        <form id="prForm" novalidate>
            <input type="hidden" id="prMode" value="create">
            <input type="hidden" id="prIdRtp" name="id_rtp">

            <!-- ===== INFORMASI KONTEKS ===== -->
            <div class="rtp-info-panel">
                <div class="rtp-section-title"><i class="ti ti-building me-1"></i>Informasi Konteks</div>

                <div class="rtp-grid-2">
                    <div>
                        <div class="rtp-info-row"><span class="rtp-info-label">Tahun</span><span class="rtp-info-value" id="prInfoTahun">-</span></div>
                        <div class="rtp-info-row"><span class="rtp-info-label">Tim Kerja</span><span class="rtp-info-value" id="prInfoTimKerja">-</span></div>
                    </div>

                    <div>
                        <div class="rtp-info-row"><span class="rtp-info-label">Pengelola Risiko</span><span class="rtp-info-value" id="prInfoPengelola">-</span></div>
                        <div class="rtp-info-row"><span class="rtp-info-label">Sasaran Strategis</span><span class="rtp-info-value" id="prInfoSasaran">-</span></div>
                    </div>
                </div>
            </div>

            <!-- ===== INFORMASI RISIKO ===== -->
            <div class="rtp-info-panel">
                <div class="rtp-section-title"><i class="ti ti-shield me-1"></i>Informasi Risiko</div>

                <div class="rtp-info-row"><span class="rtp-info-label">Proses Bisnis</span><span class="rtp-info-value" id="prInfoProses">-</span></div>

                <div class="rtp-info-row"><span class="rtp-info-label">Sasaran Kinerja</span><span class="rtp-info-value" id="prInfoSasaranKinerja">-</span></div>

                <div class="rtp-info-row"><span class="rtp-info-label">Pernyataan Risiko</span><span class="rtp-info-value fw-semibold" id="prInfoPernyataan">-</span></div>

                <div class="rtp-grid-2" style="margin-top:2px">
                    <div class="rtp-info-row"><span class="rtp-info-label">Penyebab</span><span class="rtp-info-value" id="prInfoPenyebab">-</span></div>
                    <div class="rtp-info-row"><span class="rtp-info-label">Dampak</span><span class="rtp-info-value" id="prInfoDampak">-</span></div>
                </div>
            </div>

            <hr class="rtp-divider">

            <!-- ===== PENANGGUNG JAWAB ===== -->
            <div class="mb-3">
                <div class="rtp-section-title"><i class="ti ti-user me-1"></i>Penanggung Jawab</div>
                <div class="rtp-info-row"><span class="rtp-info-label">Penanggung Jawab</span><span class="rtp-info-value fw-semibold" id="prInfoPenanggungJawab">-</span></div>
            </div>

            <hr class="rtp-divider">

            <!-- ===== RTP ===== -->
            <div class="mb-3">
                <div class="rtp-section-title"><i class="ti ti-list-check me-1"></i>RTP</div>
                <div class="rtp-info-row"><span class="rtp-info-value" id="prInfoRtp">-</span></div>
            </div>

            <!-- ===== TARGET ===== -->
            <div class="mb-3">
                <div class="rtp-section-title"><i class="ti ti-target me-1"></i>Target</div>

                <div class="rtp-grid-2">
                    <div class="rtp-info-row"><span class="rtp-info-label">Output</span><span class="rtp-info-value" id="prInfoTargetOutput">-</span></div>
                    <div class="rtp-info-row"><span class="rtp-info-label">Waktu</span><span class="rtp-info-value" id="prInfoTargetWaktu">-</span></div>
                </div>
            </div>

            <hr class="rtp-divider">

            <!-- ===== INPUT ZONE: REALISASI → BUKTI ===== -->
            <div class="rtp-input-zone">

                <!-- ===== REALISASI ===== -->
                <div class="mb-3">
                    <div class="rtp-section-title"><i class="ti ti-clipboard-check me-1"></i>Realisasi</div>

                    <div class="pr-grid-realisasi">
                        <div>
                            <label class="rtp-form-label">Realisasi Output</label>
                            <input type="text" name="realisasi_output" id="prRealisasiOutput" class="form-control" required>
                        </div>

                        <div>
                            <label class="rtp-form-label">Realisasi Waktu</label>
                            <input type="month" name="realisasi_waktu" id="prRealisasiWaktu" class="form-control">
                        </div>
                    </div>
                </div>

                <!-- ===== STATUS + CATATAN ===== -->
                <div class="rtp-grid-2 mb-3">

                    <div>
                        <div class="rtp-section-title">
                            <i class="ti ti-flag me-1"></i>Status
                        </div>

                        <span id="prStatusBadge"
                            class="badge bg-secondary-subtle text-secondary">
                            -
                        </span>

                        <div class="small text-muted mt-2">
                            Status dihitung otomatis berdasarkan target dan realisasi.
                        </div>
                    </div>

                    <div>
                        <div class="rtp-section-title">
                            <i class="ti ti-note me-1"></i>Catatan
                        </div>

                        <div id="prCatatanView"
                            class="rtp-info-value small d-none">
                            -
                        </div>

                        <textarea
                            name="catatan"
                            id="prCatatan"
                            class="form-control"
                            rows="2"></textarea>
                    </div>
                </div>

                <!-- ===== EFEKTIVITAS PENGENDALIAN ===== -->
                <div class="mb-3">
                    <div class="rtp-section-title">
                        <i class="ti ti-shield-check me-1"></i>
                        Efektivitas Pengendalian
                    </div>

                    <div id="prEfektivitasView"
                        class="rtp-info-value small d-none">
                        -
                    </div>

                    <textarea
                        name="efektivitas_pengendalian"
                        id="prEfektivitasPengendalian"
                        class="form-control"
                        rows="3"
                        placeholder="Tuliskan efektivitas pengendalian..."></textarea>
                </div>

                <!-- ===== BUKTI ===== -->
                <div class="mb-3">
                    <div class="rtp-section-title"><i class="ti ti-link me-1"></i>Bukti Dukung (Link)</div>
                    <input
                        type="url"
                        name="bukti_link"
                        id="prBuktiLinkInput"
                        class="form-control mb-2"
                        placeholder="https://contoh.com">
                    <div id="prBuktiPreview" class="mt-1"></div>
                </div>


                <!-- ===== RISIKO RESIDU ===== -->
                <div class="mb-3">
                    <div class="rtp-section-title">
                        <i class="ti ti-shield-check me-1"></i>
                        Risiko Residu
                    </div>

                    <!-- Tampilan khusus role selain operator -->
                    <div id="prRisikoResiduView" class="d-none">
                        <div class="rtp-grid-2 mb-2">
                            <div>
                                <div class="rtp-info-label">Kemungkinan (P)</div>
                                <div class="rtp-info-value" id="prResiduKemungkinanView">-</div>
                            </div>
                            <div>
                                <div class="rtp-info-label">Dampak (D)</div>
                                <div class="rtp-info-value" id="prResiduDampakView">-</div>
                            </div>
                        </div>

                        <div class="rtp-info-panel">
                            <div class="rtp-info-row">
                                <span class="rtp-info-label">Nilai Risiko</span>
                                <span class="rtp-info-value fw-semibold" id="prNilaiRisikoResiduView">-</span>
                            </div>
                            <div class="rtp-info-row">
                                <span class="rtp-info-label">Level Risiko</span>
                                <span class="rtp-info-value" id="prSeleraRisikoResiduView">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Input khusus operator -->
                    <div id="prRisikoResiduInput">
                        <div class="rtp-grid-2 mb-2">
                            <div>
                                <label class="rtp-form-label">Kemungkinan (P)</label>
                                <select
                                    name="id_kemungkinan_residu"
                                    id="prKemungkinanResidu"
                                    class="form-select">
                                    <option value="">Pilih Kemungkinan</option>
                                    <?php foreach ($kriteriaKemungkinan as $item): ?>
                                        <option value="<?= esc($item['id_kriteria']) ?>">
                                            <?= esc($item['level'] . ' - ' . $item['nama_level']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="rtp-form-label">Dampak (D)</label>
                                <select
                                    name="id_dampak_residu"
                                    id="prDampakResidu"
                                    class="form-select">
                                    <option value="">Pilih Dampak</option>
                                    <?php foreach ($kriteriaDampak as $item): ?>
                                        <option value="<?= esc($item['id_kriteria']) ?>">
                                            <?= esc($item['level'] . ' - ' . $item['nama_level']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="rtp-info-panel mt-2">
                            <div class="rtp-info-row">
                                <span class="rtp-info-label">Nilai Risiko</span>
                                <span class="rtp-info-value fw-semibold" id="prNilaiRisikoResidu">-</span>
                            </div>
                            <div class="rtp-info-row">
                                <span class="rtp-info-label">Level Risiko</span>
                                <span class="rtp-info-value" id="prSeleraRisikoResidu">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end rtp-input-zone -->

            <hr class="rtp-divider">

            <!-- ===== BUTTON ===== -->
            <div class="d-flex align-items-center pt-3 border-top">
                <div>
                    <button type="button" id="prBtnDelete" class="btn btn-sm btn-danger d-none" onclick="prHapus()"><i class="ti ti-trash"></i></button>
                </div>

                <div class="ms-auto d-flex gap-2">
                    <button type="button" id="prBtnEdit" class="btn btn-sm btn-warning text-white d-none" onclick="prSetMode('edit')">Edit</button>
                    <button type="button" id="prBtnBatal" class="btn btn-sm btn-light d-none" onclick="prBatal()">Batal</button>
                    <button type="submit" id="prBtnSimpan" class="btn btn-sm btn-primary px-4 d-none"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
                    <button type="button" id="prBtnTutup" class="btn btn-sm btn-light" data-bs-dismiss="offcanvas">Tutup</button>
                </div>
            </div>
        </form>
    </div>
</div>