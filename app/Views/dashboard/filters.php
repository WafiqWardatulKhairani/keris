<div class="filter-bar">
    <div class="filter-group">
        <label>Tahun</label>
        <select id="fTahun">
            <option value="">Semua Tahun</option>
            <?php foreach ($tahunList as $t): ?>
                <option value="<?= esc($t['tahun']) ?>"><?= esc($t['tahun']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <!-- Filter Triwulan -->
    <div class="filter-group">
        <label>Triwulan</label>
        <select id="fTriwulan">
            <option value="">Semua Triwulan</option>
            <option value="1">Triwulan I (Jan–Mar)</option>
            <option value="2">Triwulan II (Apr–Jun)</option>
            <option value="3">Triwulan III (Jul–Sep)</option>
            <option value="4">Triwulan IV (Okt–Des)</option>
        </select>
    </div>
    <div class="filter-group">
        <label>Tim Kerja</label>
        <select id="fTim">
            <option value="">Semua Tim Kerja</option>
            <?php foreach ($timKerjaList as $sk): ?>
                <option value="<?= esc($sk['id_tim']) ?>"><?= esc($sk['nama_tim']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-group">
        <label>Kategori Risiko</label>
        <select id="fKategori">
            <option value="">Semua Kategori</option>
            <?php foreach ($kategoriList as $k): ?>
                <option value="<?= esc($k['id_kategori_risiko']) ?>"><?= esc($k['nama_kategori']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button class="btn-reset" id="btnReset">Reset</button>
    <div class="filter-indicator" id="filterIndicator" style="display:none">
        <span id="filterBadge"></span>
    </div>
</div>