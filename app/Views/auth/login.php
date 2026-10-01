<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — KERIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/login.css') ?>">
</head>

<body>

    <div class="login-page">
        <div class="login-card">

            <!-- ════════════════════════════
             PANEL KIRI — Form Login
        ════════════════════════════ -->
            <div class="login-form-panel">

                <!-- Logo -->
                <div class="login-logo">
                    <img src="<?= base_url('assets/images/logo-keris-v2.png') ?>" class="keris-icon" alt="KERIS">
                    <img src="<?= base_url('assets/images/logo-keris-text-v2.png') ?>" class="keris-text-logo" alt="KERIS TEXT">
                </div>

                <!-- Heading -->
                <div class="login-heading">
                    <p class="eyebrow">AKUN PENGGUNA</p>
                    <h1>Selamat Datang<br>di KERIS</h1>
                    <p>KERIS untuk Risiko yang Lebih Terkelola</p>
                </div>

                <!-- Alert error -->
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert-custom">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <form method="post" action="<?= site_url('login') ?>" class="login-form">
                    <?= csrf_field() ?>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Alamat Email</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="4" width="20" height="16" rx="2" />
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                            </svg>
                            <input type="email" id="email" name="email"
                                class="form-control-custom"
                                placeholder="nama@bps.go.id"
                                required autocomplete="email">
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password">Kata Sandi</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                            <input type="password" id="password" name="password"
                                class="form-control-custom"
                                placeholder="Masukkan kata sandi"
                                required autocomplete="current-password">
                            <button type="button" class="toggle-password" id="togglePassword" aria-label="Tampilkan/sembunyikan kata sandi">
                                <!-- Eye icon (default: show) -->
                                <svg id="iconEye" xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <!-- Eye-off icon (hidden by default) -->
                                <svg id="iconEyeOff" xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    style="display:none">
                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
                                    <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61" />
                                    <line x1="2" y1="2" x2="22" y2="22" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <!-- Tombol Login -->
                    <button type="submit" class="btn-login">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                            <polyline points="10 17 15 12 10 7" />
                            <line x1="15" y1="12" x2="3" y2="12" />
                        </svg>
                        Masuk
                    </button>

                    <div class="divider">atau</div>
                     <a href="<?= site_url('auth/sso') ?>" class="btn-sso">
                        <svg class="sso-icon"
                            xmlns="http://www.w3.org/2000/svg"
                            width="18"
                            height="18"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M20 21a8 8 0 1 0-16 0" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                        Masuk dengan SSO
                    </a> 

                </form>

                <p class="login-footer-note">
                    &copy; <?= date('Y') ?> BPS Provinsi Riau &mdash; KERIS v1.0
                </p>
            </div>

            <!-- ════════════════════════════
             PANEL KANAN — Visual
        ════════════════════════════ -->
            <div class="login-visual-panel">
                <div class="visual-blob visual-blob-1"></div>
                <div class="visual-blob visual-blob-2"></div>

                <!-- Stat card -->
                <div class="visual-stat-card">
                    <div class="stat-label">Monitoring Risiko</div>
                    <!-- Mini bar chart -->
                    <div class="mini-chart">
                        <div class="bar" style="height:50%"></div>
                        <div class="bar" style="height:65%"></div>
                        <div class="bar" style="height:45%"></div>
                        <div class="bar" style="height:80%"></div>
                        <div class="bar" style="height:60%"></div>
                        <div class="bar active" style="height:95%"></div>
                        <div class="bar" style="height:70%"></div>
                    </div>
                </div>

                <!-- Activity cards -->
                <div class="visual-activity">
                    <div class="activity-item">
                        <div class="activity-avatar">OP</div>
                        <div class="activity-info">
                            <div class="activity-name">Operator</div>
                            <div class="activity-desc">Menambahkan laporan risiko baru</div>
                        </div>
                        <span class="activity-badge badge-blue">Baru</span>
                    </div>
                    <div class="activity-item">
                        <div class="activity-avatar">KT</div>
                        <div class="activity-info">
                            <div class="activity-name">Ketua</div>
                            <div class="activity-desc">Menyetujui mitigasi Triwulan II</div>
                        </div>
                        <span class="activity-badge badge-green">Disetujui</span>
                    </div>
                </div>

                <div class="visual-headline" style="margin-top: 28px;">
                    Sistem Monitoring &amp; Evaluasi Risiko
                </div>
            </div>

        </div>
    </div>

    <script>
        // Toggle show/hide password
        const toggleBtn = document.getElementById('togglePassword');
        const passInput = document.getElementById('password');
        const iconEye = document.getElementById('iconEye');
        const iconEyeOff = document.getElementById('iconEyeOff');

        toggleBtn.addEventListener('click', function() {
            const isPassword = passInput.type === 'password';
            passInput.type = isPassword ? 'text' : 'password';
            iconEye.style.display = isPassword ? 'none' : 'block';
            iconEyeOff.style.display = isPassword ? 'block' : 'none';
        });
    </script>

</body>

</html>