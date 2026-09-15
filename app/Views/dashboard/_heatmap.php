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
                <canvas id="chartPie" role="img" aria-label="Distribusi level risiko"></canvas>
            </div>
            <div class="pie-legend" id="pieLegend"></div>
        </div>
    </div>
</div>