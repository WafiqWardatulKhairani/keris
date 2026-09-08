<div class="grid-2">
    <div class="card card-kategori">
        <div class="card-head">
            <span>Risiko per Kategori</span>
            <small>Semua kategori</small>
        </div>
        <div class="card-body">
            <div class="chart-container" style="height:300px">
                <canvas id="chartKategori" role="img" aria-label="Risiko per kategori"></canvas>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head"> 
            <span>Status RTP</span>
            <small>Realisasi penanganan risiko</small>
        </div>
        <div class="card-body">
            <div class="status-rtp-grid" id="statusRtpGrid">
                <div class="status-card s-belum">
                    <div class="s-icon"><i class="ti ti-clock-pause"></i></div>
                    <div class="s-body">
                        <span>Belum Dilaksanakan</span>
                        <b id="sBelum">—</b>
                    </div>
                </div>
                <div class="status-card s-proses">
                    <div class="s-icon"><i class="ti ti-loader"></i></div>
                    <div class="s-body">
                        <span>Dalam Proses</span>
                        <b id="sProses">—</b>
                    </div>
                </div>
                <div class="status-card s-selesai">
                    <div class="s-icon"><i class="ti ti-circle-check"></i></div>
                    <div class="s-body">
                        <span>Selesai</span>
                        <b id="sSelesai">—</b>
                    </div>
                </div>
                <div class="status-card s-terlambat">
                    <div class="s-icon"><i class="ti ti-alert-circle"></i></div>
                    <div class="s-body">
                        <span>Terlambat</span>
                        <b id="sTerlambat">—</b>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>