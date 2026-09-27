<div class="grid-heatmap">
    <div class="card">
        <div class="card-head">
            <span>Peta Risiko</span>
            <small>Kemungkinan × Dampak</small>
        </div>

        <div class="card-body">
            <?= view('dashboard/_risk_matriks', [
                'matriks' => $matriks,
                'heatmap' => $heatmap
            ]) ?>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <span>Level Risiko</span>
            <small>Distribusi tingkat risiko</small>
        </div>

        <div class="card-body pie-wrap">
            <div class="chart-container" style="height:240px">
                <canvas
                    id="chartPie"
                    role="img"
                    aria-label="Distribusi level risiko">
                </canvas>
            </div>

            <div class="pie-legend" id="pieLegend"></div>
        </div>
    </div>
</div>


<!-- =========================
     POPUP DETAIL RISIKO
========================= -->
<div class="risk-popup-overlay" id="riskPopupOverlay">

    <div class="risk-popup">

        <!-- HEADER -->
        <div class="risk-popup-header">

            <div>
                <h3>Detail Risiko</h3>

                <p id="riskPopupInfo">
                    Kemungkinan - × Dampak -
                </p>
            </div>

            <button
                type="button"
                class="risk-popup-close"
                id="riskPopupClose"
                aria-label="Tutup">
                &times;
            </button>

        </div>


        <!-- INFO SINGKAT -->
        <div class="risk-popup-summary">

            <span>
                Nilai Risiko:
                <strong id="riskPopupScore">-</strong>
            </span>

            <span class="risk-popup-dot">•</span>

            <span>
                <strong id="riskPopupTotal">0</strong>
                Risiko
            </span>

        </div>


        <!-- LIST RISIKO -->
        <div class="risk-popup-table-wrap">

            <table class="risk-popup-table">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Pernyataan Risiko</th>
                        <th>Tim Kerja</th>
                        <th>Proses Bisnis</th>
                        <th>Jenis Proses</th>
                    </tr>
                </thead>

                <tbody id="riskPopupTableBody">

                    <tr>
                        <td colspan="5" class="risk-popup-empty">
                            Data risiko akan ditampilkan di sini.
                        </td>
                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {

        const overlay =
            document.getElementById('riskPopupOverlay');

        const closeButton =
            document.getElementById('riskPopupClose');

        const tableBody =
            document.getElementById('riskPopupTableBody');

        const cells =
            document.querySelectorAll('.risk-cell-clickable');


        // =====================================================
        // ESCAPE HTML
        // Mencegah isi database dianggap sebagai HTML
        // =====================================================
        function escapeHtml(value) {

            const div = document.createElement('div');

            div.textContent = String(value ?? '');

            return div.innerHTML;
        }

        function truncateText(value, maxLength = 25) {

    const text = String(value ?? '').trim();

    if (text.length <= maxLength) {
        return text;
    }

    let truncated = text.substring(0, maxLength);

    // Cari spasi terakhir supaya tidak memotong di tengah kata
    const lastSpace = truncated.lastIndexOf(' ');

    if (lastSpace > 0) {
        truncated = truncated.substring(0, lastSpace);
    }

    return truncated.trim() + '...';
}

        // =====================================================
        // AMBIL LIST RISIKO DARI DATABASE
        // =====================================================
        async function loadRiskDetail(kemungkinan, dampak) {

            // Loading
            tableBody.innerHTML = `
            <tr>
                <td colspan="5" class="risk-popup-empty">
                    Memuat data risiko...
                </td>
            </tr>
        `;


            try {

                const tahun =
                    document.getElementById('fTahun')?.value || '';

                const tim =
                    document.getElementById('fTim')?.value || '';

                const kategori =
                    document.getElementById('fKategori')?.value || '';


                const params = new URLSearchParams();

                params.set('kemungkinan', kemungkinan);
                params.set('dampak', dampak);

                if (tahun) {
                    params.set('tahun', tahun);
                }

                if (tim) {
                    params.set('tim', tim);
                }

                if (kategori) {
                    params.set('kategori', kategori);
                }


                const url =
                    `<?= base_url('dashboard/risk-detail') ?>?${params.toString()}`;

                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });


                if (!response.ok) {
                    throw new Error(
                        `HTTP Error ${response.status}`
                    );
                }


                const result = await response.json();


                // Response tidak sesuai
                if (
                    !result.status ||
                    !Array.isArray(result.data)
                ) {
                    throw new Error(
                        'Format response tidak sesuai.'
                    );
                }


                // Update jumlah berdasarkan response database
                document.getElementById(
                    'riskPopupTotal'
                ).textContent = result.total;


                // Tidak ada risiko
                if (result.data.length === 0) {

                    tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="risk-popup-empty">
                            Tidak ada risiko pada posisi ini.
                        </td>
                    </tr>
                `;

                    return;
                }


                // =================================================
                // TAMPILKAN DATA RISIKO
                // =================================================
                tableBody.innerHTML = result.data
    .map(function(risk, index) {

        return `
            <tr>

                <td>
                    ${index + 1}
                </td>

                <td class="risk-statement">
                    <span
                        class="risk-statement-text"
                        title="${escapeHtml(risk.pernyataan_risiko ?? '-')}"
                    >
                        ${escapeHtml(
                            truncateText(
                                risk.pernyataan_risiko ?? '-',
                                70
                            )
                        )}
                    </span>
                </td>

                <td>
                    ${escapeHtml(
                        risk.nama_tim ?? '-'
                    )}
                </td>

                <td>
                    ${escapeHtml(
                        risk.uraian_proses ?? '-'
                    )}
                </td>

                <td>
                    ${escapeHtml(
                        risk.jenis_proses ?? '-'
                    )}
                </td>

            </tr>
        `;

    })
    .join('');


            } catch (error) {

                console.error(
                    'Gagal mengambil detail risiko:',
                    error
                );


                tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="risk-popup-empty">
                        Gagal memuat data risiko.
                    </td>
                </tr>
            `;

            }

        }


        // =====================================================
        // KLIK CELL PETA RISIKO
        // =====================================================
        cells.forEach(function(cell) {

            cell.addEventListener('click', function() {

                const kemungkinan =
                    this.dataset.kemungkinan;

                const kemungkinanLabel =
                    this.dataset.kemungkinanLabel;

                const dampak =
                    this.dataset.dampak;

                const dampakLabel =
                    this.dataset.dampakLabel;

                const nilai =
                    this.dataset.nilai;


                // Ambil jumlah yang sedang tampil di matrix
                const countElement =
                    this.querySelector('.risk-count');

                const total = parseInt(
                    countElement?.textContent
                    .replace(/[()]/g, '')
                    .trim()
                ) || 0;


                // =================================================
                // ISI HEADER POPUP
                // =================================================

                document.getElementById(
                        'riskPopupInfo'
                    ).textContent =
                    `${kemungkinanLabel} (${kemungkinan}) × ` +
                    `${dampakLabel} (${dampak})`;


                document.getElementById(
                        'riskPopupScore'
                    ).textContent =
                    nilai;


                document.getElementById(
                        'riskPopupTotal'
                    ).textContent =
                    total;


                // =================================================
                // TAMPILKAN POPUP
                // =================================================

                overlay.classList.add('show');

                document.body.classList.add(
                    'risk-popup-open'
                );


                // =================================================
                // AMBIL DATA ASLI
                // =================================================

                loadRiskDetail(
                    kemungkinan,
                    dampak
                );

            });

        });


        // =====================================================
        // TUTUP POPUP
        // =====================================================
        function closeRiskPopup() {

            overlay.classList.remove('show');

            document.body.classList.remove(
                'risk-popup-open'
            );

        }


        // Tombol X
        closeButton.addEventListener(
            'click',
            closeRiskPopup
        );


        // Klik area gelap
        overlay.addEventListener(
            'click',
            function(event) {

                if (event.target === overlay) {
                    closeRiskPopup();
                }

            }
        );


        // Tombol ESC
        document.addEventListener(
            'keydown',
            function(event) {

                if (event.key === 'Escape') {
                    closeRiskPopup();
                }

            }
        );

    });
</script>