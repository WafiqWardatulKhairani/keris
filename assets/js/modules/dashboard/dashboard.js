

document.getElementById('dashDate').textContent = new Date().toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
});

let chartPie, chartKategori;

function initCharts() {
    chartPie = new Chart(document.getElementById('chartPie'), {
        type: 'pie',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [],
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const pct = total > 0 ? Math.round(ctx.parsed / total * 100) : 0;
                            return ` ${ctx.label}: ${ctx.parsed} (${pct}%)`;
                        }
                    }
                }
            }
        }
    });

    chartKategori = new Chart(document.getElementById('chartKategori'), {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Jumlah Risiko',
                data: [],
                backgroundColor: '#3b82f6',
                borderRadius: 5,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y} risiko`
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 10
                        },
                        maxRotation: 35,
                        minRotation: 35,
                        callback: function(val, idx) {
                            const label = this.getLabelForValue(val);
                            return label.length > 14 ? label.substring(0, 12) + '…' : label;
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.04)'
                    },
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}

function setBar(id, pct) {
    const el = document.getElementById(id);
    if (el) el.style.setProperty('--p', Math.min(pct, 100) + '%');
}

// Warna heatmap dari kolom `warna` di matriks_risiko
const WARNA_CSS = {
    biru: '#bfdbfe',
    hijau: '#bbf7d0',
    kuning: '#fde68a',
    oranye: '#fed7aa',
    merah: '#fca5a5',
};
const WARNA_TEXT = {
    biru: '#1e40af',
    hijau: '#166534',
    kuning: '#92400e',
    oranye: '#9a3412',
    merah: '#991b1b',
};

function renderHeatmap(grid) {
    const t = document.getElementById('heatmapTable');
    let html = '';
    // Baris: kemungkinan 5→1 (atas = tinggi)
    for (let k = 5; k >= 1; k--) {
        html += '<tr>';
        // Kolom: dampak 1→5
        for (let d = 1; d <= 5; d++) {
            const cell = (grid[k] && grid[k][d]) ? grid[k][d] : {};
            const count = parseInt(cell.total ?? 0);

            const warna = (cell.warna || '').toLowerCase();
            const bg = WARNA_CSS[warna] || '#f1f5f9';
            const color = WARNA_TEXT[warna] || '#334155';
            const nilai = cell.nilai_risiko || '';
            html += `<td style="background:${bg};color:${color}" title="Nilai: ${nilai}">`;
            html += `<span class="hm-dot">${count}</span>`;
            html += `</td>`;
        }
        html += '</tr>';
    }
    t.innerHTML = html;
}

function renderPieLegend(labels, values, colors) {
    const total = values.reduce((a, b) => a + b, 0);
    const el = document.getElementById('pieLegend');
    if (!labels.length) {
        el.innerHTML = '<span class="empty-state">Tidak ada data</span>';
        return;
    }
    el.innerHTML = labels.map((lbl, i) => {
        const pct = total > 0 ? Math.round(values[i] / total * 100) : 0;
        return `<div class="pie-legend-item">
            <i style="background:${colors[i]}"></i>
            <span>${lbl}</span>
            <b>${values[i]}</b>
            <em>${pct}%</em>
        </div>`;
    }).join('');
}

function renderProgress(rows) {
    const tbody = document.getElementById('progressBody');
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="empty-state">Tidak ada data</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(p => {
        const f1 = +p.f1 || 0;
        // Form 2 = konteks yang sudah punya setidaknya salah satu dari IR/PR/EV
        const f2 = +p.f2 || 0; // pakai IR sebagai indikator utama Form 2
        const f3 = +p.f3 || 0;
        const f4 = +p.f4 || 0;

        // Kelengkapan: proporsi konteks yang sudah mencapai Form 4 dari total konteks
        const pct = f1 > 0 ? Math.round((f4 / f1) * 100) : 0;
        const color = pct >= 100 ? '#22c55e' : pct >= 75 ? '#3b82f6' : pct >= 40 ? '#f59e0b' : '#ef4444';

        return `<tr>
            <td class="sk-name">${p.nama_tim}</td>
            <td><span class="count-badge blue">${f1}</span></td>
            <td><span class="count-badge purple">${f2}</span></td>
            <td><span class="count-badge teal">${f3}</span></td>
            <td><span class="count-badge orange">${f4}</span></td>
            <td>
                <div class="prog-bar-wrap">
                    <div class="prog-bar">
                        <div class="prog-fill" style="width:${pct}%;background:${color}"></div>
                    </div>
                    <span>${pct}%</span>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function updateFilterBadge() {
    const t = document.getElementById('fTahun').value;
    const tm = document.getElementById('fTim').selectedOptions[0];
    const k = document.getElementById('fKategori').selectedOptions[0];
    const parts = [];
    if (t) parts.push(t);
    if (tm && tm.value) parts.push(tm.text);
    if (k && k.value) parts.push(k.text);
    const ind = document.getElementById('filterIndicator');
    const badge = document.getElementById('filterBadge');
    if (parts.length) {
        badge.textContent = parts.join(' · ');
        ind.style.display = 'flex';
    } else {
        ind.style.display = 'none';
    }
}

function updateRiskMatrix(grid) {
    for (let k = 1; k <= 5; k++) {
        for (let d = 1; d <= 5; d++) {

            const el = document.getElementById(
                `risk-count-${k}-${d}`
            );

            if (!el) continue;

            const total =
                grid?.[k]?.[d]?.total ?? 0;

            el.textContent = total > 0
                ? `(${total})`
                : '';
        }
    }
}

let fetchTimer;

function fetchData() {
    clearTimeout(fetchTimer);
    fetchTimer = setTimeout(async () => {
        const tahun = document.getElementById('fTahun').value;
        const tim = document.getElementById('fTim').value;
        const kategori = document.getElementById('fKategori').value;
        console.log(
    'tahun=', tahun,
    'tim=', tim,
    'kategori=', kategori
);
        const overlay = document.getElementById('loadingOverlay');

        updateFilterBadge();
        overlay.classList.add('active');

        const params = new URLSearchParams();
        if (tahun) params.set('tahun', tahun);
        if (tim) params.set('tim', tim);
        if (kategori) params.set('kategori', kategori);

        try {
            const res = await fetch(DATA_URL + '?' + params.toString());
            const data = await res.json();
            updateRiskMatrix(data.heatmap);
            const kpi = data.kpi;

            // KPI cards
            document.getElementById('kTotalRisiko').textContent = kpi.totalRisiko;
            document.getElementById('kTotalRtp').textContent = kpi.totalRtp;
            document.getElementById('kRealisasi').textContent = kpi.realisasi + '%';
            setBar('kBarRisiko', 100);
            setBar('kBarRtp', kpi.totalRisiko > 0 ? Math.round(kpi.totalRtp / kpi.totalRisiko * 100) : 0);
            setBar('kBarRealisasi', kpi.realisasi);

            // Heatmap
            //renderHeatmap(data.heatmap);

            // Pie chart level risiko
            chartPie.data.labels = data.pieLabels;
            chartPie.data.datasets[0].data = data.pieValues;
            chartPie.data.datasets[0].backgroundColor = data.pieColors;
            chartPie.update();
            renderPieLegend(data.pieLabels, data.pieValues, data.pieColors);

            // Bar chart kategori
            chartKategori.data.labels = data.kategoriLabels;
            chartKategori.data.datasets[0].data = data.kategoriValues;
            chartKategori.update();

            // Status RTP
            const s = data.statusRtp || {};
            document.getElementById('sBelum').textContent = parseInt(s.belum) || 0;
            document.getElementById('sProses').textContent = parseInt(s.proses) || 0;
            document.getElementById('sSelesai').textContent = parseInt(s.selesai) || 0;
            document.getElementById('sTerlambat').textContent = parseInt(s.terlambat) || 0;

            // Progress
            renderProgress(data.progress);

        } catch (e) {
            console.error('Fetch error:', e);
        } finally {
            overlay.classList.remove('active');
        }
    }, 300);
}

document.getElementById('fTahun').addEventListener('change', fetchData);
document.getElementById('fTim').addEventListener('change', fetchData);
document.getElementById('fKategori').addEventListener('change', fetchData);
document.getElementById('btnReset').addEventListener('click', () => {
    document.getElementById('fTahun').value = '';
    document.getElementById('fTim').value = '';
    document.getElementById('fKategori').value = '';
    fetchData();
});

initCharts();
fetchData();