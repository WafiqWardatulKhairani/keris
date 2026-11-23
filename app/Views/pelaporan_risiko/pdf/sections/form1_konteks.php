<div class="report-section">

    <?php if (empty($form1)): ?>

        <div class="empty-message">
            Data Penetapan Konteks tidak ditemukan.
        </div>

    <?php else: ?>
        <!-- ============================= -->
        <!-- I. LINGKUP -->
        <!-- ============================= -->

        <table class="form1-context-table form1-context-scope">
            <colgroup>
                <col style="width: 25px">
                <col style="width: 150px">
                <col style="width: 18px">
                <col style="width: auto">
            </colgroup>

            <!-- I. LINGKUP -->
            <tr>
                <td class="form1-section-number">I</td>
                <td colspan="4" class="form1-section-title">
                    Lingkup
                </td>
            </tr>

            <tr>
                <td></td>
                <td class="form1-label">Tahun</td>
                <td class="form1-separator">:</td>
                <td><?= esc($tahun ?? '-') ?></td>
            </tr>

            <tr>
                <td></td>
                <td class="form1-label">Unit Organisasi</td>
                <td class="form1-separator">:</td>
                <td>BPS Provinsi Riau</td>
            </tr>

            <tr>
                <td></td>
                <td class="form1-label">Tim Kerja</td>
                <td class="form1-separator">:</td>
                <td><?= esc($timkerja ?? '-') ?></td>
            </tr>

            <tr>
                <td></td>
                <td class="form1-label">Kegiatan</td>
                <td class="form1-separator">:</td>
                <td><?= esc($kegiatan ?? '-') ?></td>
            </tr>
        </table>

        <!-- ============================= -->
        <!-- II. KONTEKS -->
        <!-- ============================= -->

        <table class="form1-context-table form1-context-main">

            <colgroup>
                <col style="width: 28px">
                <col style="width: 28px">
                <col style="width: 85px">
                <col style="width: 18px">
                <col style="width: auto">
            </colgroup>


            <!-- II. KONTEKS -->
            <tr>
                <td class="form1-section-number">II</td>
                <td colspan="4" class="form1-section-title">
                    Konteks
                </td>
            </tr>

            <!-- 1. STRUKTUR ORGANISASI -->
            <tr>
                <td></td>
                <td colspan="4" class="form1-subsection-title">
                    1. Struktur Organisasi Penerapan Manajemen Risiko
                </td>
            </tr>

            <!-- a. PEMILIK RISIKO -->
            <tr>
                <td></td>
                <td class="form1-letter">a</td>
                <td colspan="3" class="form1-role-title">
                    Pemilik Risiko
                </td>
            </tr>

            <tr>
                <td></td>
                <td></td>
                <td class="form1-label">Nama</td>
                <td class="form1-separator">:</td>
                <td><?= esc($nama_pemilik ?? '-') ?></td>
            </tr>

            <tr>
                <td></td>
                <td></td>
                <td class="form1-label">NIP</td>
                <td class="form1-separator">:</td>
                <td><?= esc($nip_pemilik ?? '-') ?></td>
            </tr>

            <tr>
                <td></td>
                <td></td>
                <td class="form1-label">Jabatan</td>
                <td class="form1-separator">:</td>
                <td><?= esc($jabatan_pemilik ?? '-') ?></td>
            </tr>

            <!-- b. PENGELOLA RISIKO -->
            <tr>
                <td></td>
                <td class="form1-letter">b</td>
                <td colspan="3" class="form1-role-title">
                    Pengelola Risiko
                </td>
            </tr>

            <tr>
                <td></td>
                <td></td>
                <td class="form1-label">Nama</td>
                <td class="form1-separator">:</td>
                <td><?= esc($nama_ketua ?? '-') ?></td>
            </tr>

            <tr>
                <td></td>
                <td></td>
                <td class="form1-label">NIP</td>
                <td class="form1-separator">:</td>
                <td><?= esc($nip_ketua ?? '-') ?></td>
            </tr>

            <tr>
                <td></td>
                <td></td>
                <td class="form1-label">Jabatan</td>
                <td class="form1-separator">:</td>
                <td>Ketua Tim <?= esc($timkerja ?? '-') ?></td>
            </tr>

            <!-- 2. PROSES BISNIS -->
            <tr class="form1-process-title-row">
                <td></td>
                <td colspan="4" class="form1-subsection-title">
                    2. Proses Bisnis dan Sasaran Kinerja
                </td>
            </tr>

        </table>

        <!-- PROSES BISNIS DAN SASARAN KINERJA-->
        <table class="report-table form1-table">
            <colgroup>
                <col style="width: 6%">
                <col style="width: 15%">
                <col style="width: 22%">
                <col style="width: 57%">
            </colgroup>

            <thead>

                <tr>
                    <th rowspan="2">No.</th>
                    <th colspan="2">Proses Bisnis</th>
                    <th rowspan="2">Sasaran Kinerja</th>
                </tr>

                <tr>
                    <th>Jenis</th>
                    <th>Tahapan</th>
                </tr>

            </thead>

            <tbody>

                <?php if (!empty($form1Proses)): ?>

                    <?php
                    $no = 1;
                    ?>

                    <?php foreach ($form1Proses as $row): ?>

                        <tr>

                            <td class="text-center">
                                <?= $no++ ?>
                            </td>

                            <td class="text-center">
                                <?= esc($row['jenis_proses'] ?? '-') ?>
                            </td>

                            <td>
                                <?= esc($row['uraian_proses'] ?? '-') ?>
                            </td>

                            <td>
                                <?= esc($row['sasaran_kinerja'] ?? '-') ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="4" class="text-center">
                            Belum ada data proses bisnis.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

        <!-- ============================= -->
        <!-- 3. DAFTAR PEMANGKU KEPENTINGAN -->
        <!-- ============================= -->
        <div class="form1-stakeholder-caption">
            3. Daftar pemangku kepentingan (internal maupun eksternal)
        </div>

        <table class="report-table form1-table form1-stakeholder-table">

            <colgroup>
                <col style="width: 6%">
                <col style="width: 60%">
                <col style="width: 34%">
            </colgroup>

            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama/Instansi</th>
                    <th>Hubungan</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!empty($form1Pemangku)): ?>
                    <?php $no = 1; ?>
                    <?php foreach ($form1Pemangku as $row): ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><?= esc($row['nama_instansi'] ?? $row['nama'] ?? '-') ?></td>
                            <td><?= esc($row['hubungan'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center">
                            Belum ada data pemangku kepentingan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>


        <!-- ============================= -->
        <!-- 4. DAFTAR PERATURAN -->
        <!-- ============================= -->
        <div class="form1-stakeholder-caption">
            4. Daftar Peraturan Perundang-undangan yang Terkait
        </div>

        <table class="report-table form1-table form1-regulation-table">

            <colgroup>
                <col style="width: 6%">
                <col style="width: 94%">
            </colgroup>

            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama Peraturan</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!empty($form1Peraturan)): ?>
                    <?php $no = 1; ?>
                    <?php foreach ($form1Peraturan as $row): ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td>
                                <?= esc($row['nama_peraturan'] ?? $row['nama'] ?? '-') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" class="text-center">
                            Belum ada data peraturan perundang-undangan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- ============================= -->
        <!-- 5. ANALISIS SWOT DAN PESTLE -->
        <!-- ============================= -->

        <div class="form1-analysis-section">

            <div class="form1-analysis-heading">
                5. Analisis SWOT dan PESTLE
            </div>

            <!-- ANALISIS PESTLE -->
            <div class="form1-analysis-subheading">
                <span>a</span>
                <span>Analisis PESTLE</span>
            </div>

            <div class="form1-analysis-description">
                Memetakan faktor lingkungan, terutama faktor eksternal
                ke dalam enam dimensi berikut:
            </div>

            <div class="form1-analysis-steps">
                <strong><i>Tahap pelaksanaan:</i></strong>
                <ol>
                    <li><i>Mengumpulkan bahan acuan berupa RENSTRA, kebijakan internal, dan laporan evaluasi.</i></li>
                    <li><i>Mengelompokkan setiap isu yang teridentifikasi ke dalam salah satu dari enam dimensi.</i></li>
                    <li><i>Menilai tingkat relevansi dan dampak setiap faktor terhadap sasaran Unit Organisasi.</i></li>
                    <li><i>Memetakan hasil analisis sebagai dasar penetapan konteks.</i></li>
                </ol>
            </div>

            <!-- TABEL PESTLE -->
            <table class="report-table form1-analysis-table">
                <colgroup>
                    <col style="width: 18%">
                    <col style="width: 82%">
                </colgroup>
                <tbody>
                    <?php
                    $pestleLabels = [
                        'political'     => 'Politic',
                        'economic'      => 'Economic',
                        'social'        => 'Social',
                        'technological' => 'Technology',
                        'legal'         => 'Legal',
                        'environmental' => 'Environment',
                    ];
                    ?>

                    <?php foreach ($pestleLabels as $key => $label): ?>
                        <tr>
                            <th class="form1-analysis-label">
                                <?= esc($label) ?>
                            </th>
                            <td>
                                <div class="form1-analysis-content">
                                    <?php
                                    $items = $form1Pestle[$key] ?? [];
                                    ?>

                                    <?php if (is_array($items) && !empty($items)): ?>
                                        <?php foreach ($items as $item): ?>
                                            <?= esc(is_array($item) ? ($item['uraian'] ?? $item['isi'] ?? '') : $item) ?><br>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- ANALISIS SWOT -->
            <div class="form1-analysis-subheading form1-swot-heading">
                <span>b</span>
                <span>Analisis SWOT</span>
            </div>

            <div class="form1-analysis-description">
                Memetakan kondisi internal organisasi.
            </div>

            <!-- TABEL SWOT -->
            <table class="report-table form1-analysis-table form1-swot-table">
                <colgroup>
                    <col style="width: 18%">
                    <col style="width: 82%">
                </colgroup>
                <tbody>
                    <?php
                    $swotLabels = [
                        'strength'    => 'Strength',
                        'weakness'    => 'Weakness',
                        'opportunity' => 'Opportunity',
                        'threat'      => 'Threat',
                    ];
                    ?>

                    <?php foreach ($swotLabels as $key => $label): ?>
                        <tr>
                            <th class="form1-analysis-label">
                                <?= esc($label) ?>
                            </th>
                            <td>
                                <div class="form1-analysis-content">
                                    <?php
                                    $items = $form1Swot[$key] ?? [];
                                    ?>

                                    <?php if (is_array($items) && !empty($items)): ?>
                                        <?php foreach ($items as $item): ?>
                                            <?= esc(is_array($item) ? ($item['uraian'] ?? $item['isi'] ?? '') : $item) ?><br>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>

        <!-- ============================= -->
        <!-- III. KRITERIA -->
        <!-- ============================= -->
        <div class="form1-criteria-new-page">
            <table class="form1-context-table">
                <tr>
                    <td class="form1-section-number">III</td>
                    <td colspan="3" class="form1-section-title">
                        Kriteria
                    </td>
                </tr>

                <!-- KRITERIA KEMUNGKINAN -->
                <tr>
                    <td></td>
                    <td class="form1-letter">a</td>
                    <td colspan="2" class="form1-role-title">
                        Level Kriteria Kemungkinan
                    </td>
                </tr>
            </table>

            <!-- TABEL KRITERIA KEMUNGKINAN -->
            <table class="report-table form1-criteria-table">
                <colgroup>
                    <col style="width: 28%">
                    <col style="width: 23%">
                    <col style="width: 49%">
                </colgroup>

                <thead>
                    <tr>
                        <th rowspan="2">Level Kemungkinan</th>
                        <th colspan="2">Kriteria Kemungkinan</th>
                    </tr>
                    <tr>
                        <th>Persentase</th>
                        <th>Jumlah Frekuensi Kemungkinan terjadi dalam 1 Periode</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>Hampir Tidak Terjadi (1)</td>
                        <td class="text-center">x ≤ 5%</td>
                        <td class="text-center">
                            Sangat jarang : &lt;2 kali dalam 1 tahun
                        </td>
                    </tr>

                    <tr>
                        <td>Jarang Terjadi (2)</td>
                        <td class="text-center">5% &lt; x ≤ 10%</td>
                        <td class="text-center">
                            Jarang : 2 kali s.d 5 kali dalam 1 tahun
                        </td>
                    </tr>

                    <tr>
                        <td>Kadang Terjadi (3)</td>
                        <td class="text-center">10% &lt; x ≤ 20%</td>
                        <td class="text-center">
                            Cukup sering : 6 s.d 9 kali dalam 1 tahun
                        </td>
                    </tr>

                    <tr>
                        <td>Sering Terjadi (4)</td>
                        <td class="text-center">20% &lt; x ≤ 50%</td>
                        <td class="text-center">
                            Sering : 10 kali s.d 12 kali dalam 1 tahun
                        </td>
                    </tr>

                    <tr>
                        <td>Hampir Pasti Terjadi (5)</td>
                        <td class="text-center">x &gt; 50%</td>
                        <td class="text-center">
                            Sangat sering : &gt;12 kali dalam 1 tahun
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- PENJELASAN KRITERIA KEMUNGKINAN -->
            <table class="form1-context-table form1-criteria-note">
                <tr>
                    <td></td>
                    <td colspan="3">
                        Penggunaan Kriteria Kemungkinan ditentukan oleh pemilik risiko
                        dengan pertimbangan sebagai berikut:
                        <br>
                        (1) Persentase digunakan apabila terdapat populasi yang jelas atas kegiatan tersebut.
                        <br>
                        (2) Jumlah digunakan apabila populasi tidak dapat ditemukan.
                    </td>
                </tr>
            </table>

            <!-- KRITERIA DAMPAK -->
            <table class="form1-context-table">
                <tr>
                    <td></td>
                    <td class="form1-letter">b</td>
                    <td colspan="2" class="form1-role-title">
                        Level Kriteria Dampak
                    </td>
                </tr>
            </table>

            <!-- TABEL KRITERIA DAMPAK -->
            <table class="report-table form1-criteria-table">
                <colgroup>
                    <col style="width: 28%">
                    <col style="width: 72%">
                </colgroup>

                <thead>
                    <tr>
                        <th>Level Dampak</th>
                        <th>Kriteria Dampak</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>Tidak Signifikan (1)</td>
                        <td class="text-center">
                            Pengaruh terhadap capaian tujuan sangat rendah
                        </td>
                    </tr>

                    <tr>
                        <td>Minor (2)</td>
                        <td class="text-center">
                            Pengaruh terhadap capaian tujuan rendah
                        </td>
                    </tr>

                    <tr>
                        <td>Moderat (3)</td>
                        <td class="text-center">
                            Pengaruh terhadap capaian tujuan sedang
                        </td>
                    </tr>

                    <tr>
                        <td>Signifikan (4)</td>
                        <td class="text-center">
                            Pengaruh terhadap capaian tujuan besar
                        </td>
                    </tr>

                    <tr>
                        <td>Sangat Signifikan (5)</td>
                        <td class="text-center">
                            Pengaruh terhadap capaian tujuan sangat besar
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ============================= -->
        <!-- c. MATRIKS ANALISIS RISIKO -->
        <!-- ============================= -->

        <div class="form1-risk-matrix-section">

            <div class="form1-subsection-title">
                c. Matriks Analisis Risiko
            </div>

            <table class="report-table form1-risk-matrix">
                <colgroup>
                    <col style="width: 8%">
                    <col style="width: 6%">
                    <col style="width: 20%">
                    <col style="width: 13.2%">
                    <col style="width: 13.2%">
                    <col style="width: 13.2%">
                    <col style="width: 13.2%">
                    <col style="width: 13.2%">
                </colgroup>

                <thead>
                    <tr>
                        <th rowspan="3" colspan="3" class="matrix-title">
                            MATRIKS<br>ANALISIS<br>RISIKO
                        </th>
                        <th colspan="5">DAMPAK</th>
                    </tr>
                    <tr>
                        <th>1</th>
                        <th>2</th>
                        <th>3</th>
                        <th>4</th>
                        <th>5</th>
                    </tr>
                    <tr>
                        <th>Tidak Signifikan</th>
                        <th>Minor</th>
                        <th>Moderat</th>
                        <th>Signifikan</th>
                        <th>Sangat<br>Signifikan</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <th rowspan="5" class="matrix-probability">
                            <svg width="100%" height="220" viewBox="0 0 80 220"
                                xmlns="http://www.w3.org/2000/svg">
                                <text x="40" y="110"
                                    transform="rotate(-90 40 110)"
                                    text-anchor="middle"
                                    dominant-baseline="middle"
                                    font-size="14"
                                    font-weight="bold"
                                    fill="#000">
                                    KEMUNGKINAN
                                </text>
                            </svg>
                        </th>
                        <th>5</th>
                        <th>Hampir Pasti Terjadi</th>
                        <td class="risk-green">9</td>
                        <td class="risk-yellow">15</td>
                        <td class="risk-orange">18</td>
                        <td class="risk-red">23</td>
                        <td class="risk-red">25</td>
                    </tr>

                    <tr>
                        <th>4</th>
                        <th>Sering Terjadi</th>
                        <td class="risk-green">6</td>
                        <td class="risk-yellow">12</td>
                        <td class="risk-orange">16</td>
                        <td class="risk-orange">19</td>
                        <td class="risk-red">24</td>
                    </tr>

                    <tr>
                        <th>3</th>
                        <th>Kadang Terjadi</th>
                        <td class="risk-blue">4</td>
                        <td class="risk-green">10</td>
                        <td class="risk-yellow">14</td>
                        <td class="risk-orange">17</td>
                        <td class="risk-red">22</td>
                    </tr>

                    <tr>
                        <th>2</th>
                        <th>Jarang Terjadi</th>
                        <td class="risk-blue">2</td>
                        <td class="risk-green">7</td>
                        <td class="risk-yellow">11</td>
                        <td class="risk-yellow">13</td>
                        <td class="risk-red">21</td>
                    </tr>

                    <tr>
                        <th>1</th>
                        <th>Hampir Tidak Terjadi</th>
                        <td class="risk-blue">1</td>
                        <td class="risk-blue">3</td>
                        <td class="risk-blue">5</td>
                        <td class="risk-green">8</td>
                        <td class="risk-red">20</td>
                    </tr>
                </tbody>
            </table>

        </div>


        <!-- ============================= -->
        <!-- d. SELERA RISIKO -->
        <!-- ============================= -->

        <div class="form1-risk-appetite">

            <div class="form1-subsection-title">
                d. Selera Risiko
            </div>

            <div class="risk-appetite-text">
                1) Risiko pada level rendah dan sangat rendah dapat diterima
                dan tidak perlu dilakukan proses mitigasi risiko.
            </div>

            <div class="risk-appetite-text">
                2) Risiko dengan level sedang hingga sangat tinggi harus
                ditangani untuk menurunkan level risikonya.
            </div>

        </div>


        <!-- ============================= -->
        <!-- TABEL LEVEL RISIKO -->
        <!-- ============================= -->

        <div class="form1-risk-level-section">

            <div class="form1-risk-level-title">
                Tabel Level Risiko
            </div>

            <table class="report-table form1-risk-level-table">
                <colgroup>
                    <col style="width: 13%">
                    <col style="width: 25%">
                    <col style="width: 18%">
                    <col style="width: 44%">
                </colgroup>

                <thead>
                    <tr>
                        <th>Warna</th>
                        <th>Level Risiko</th>
                        <th>Besaran Risiko</th>
                        <th>Tindakan Yang Diambil</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td class="risk-blue">Biru</td>
                        <td>Sangat Rendah (1)</td>
                        <td>1 - 5</td>
                        <td>Tidak Diperlukan Tindakan</td>
                    </tr>

                    <tr>
                        <td class="risk-green">Hijau</td>
                        <td>Rendah (2)</td>
                        <td>6 - 10</td>
                        <td>Diambil Tindakan Jika Diperlukan</td>
                    </tr>

                    <tr>
                        <td class="risk-yellow">Kuning</td>
                        <td>Sedang (3)</td>
                        <td>11 - 14</td>
                        <td>Diambil Tindakan Jika Sumber Daya Tersedia</td>
                    </tr>

                    <tr>
                        <td class="risk-orange">Oranye</td>
                        <td>Tinggi (4)</td>
                        <td>15 - 19</td>
                        <td>Diperlukan Tindakan Untuk Mengelola Risiko</td>
                    </tr>

                    <tr>
                        <td class="risk-red">Merah</td>
                        <td>Sangat Tinggi (5)</td>
                        <td>20 - 25</td>
                        <td>Diperlukan Tindakan Segera Untuk Mengelola Risiko</td>
                    </tr>
                </tbody>
            </table>

        </div>
    <?php endif; ?>

</div>