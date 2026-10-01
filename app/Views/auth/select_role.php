<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pilih Role — KERIS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/login.css') ?>">
</head>

<body>

<div class="role-page">

    <!-- Dekorasi background -->
    <div class="role-bg role-bg-left"></div>
    <div class="role-bg role-bg-right"></div>

    <div class="role-wrapper">

        <!-- Logo -->
        <div class="role-logo">
            <img
                src="<?= base_url('assets/images/logo-keris-v2.png') ?>"
                class="keris-icon"
                alt="KERIS">

            <img
                src="<?= base_url('assets/images/logo-keris-text-v2.png') ?>"
                class="keris-text-logo"
                alt="KERIS">
        </div>

        <!-- Card utama -->
        <div class="role-box">

            <div class="role-header">
                <p class="role-eyebrow">AKSES PENGGUNA</p>

                <h1>Pilih Role</h1>

                <p>
                    Pilih role yang ingin digunakan untuk mengakses KERIS.
                </p>
            </div>

            <!-- User yang sudah login -->
            <div class="role-user">
                <div class="role-user-avatar">
                    <?= strtoupper(substr($userName, 0, 1)) ?>
                </div>

                <div class="role-user-info">
                    <span>Masuk sebagai</span>
                    <strong><?= esc($userName) ?></strong>
                </div>

                <div class="role-user-status">
                    <span></span>
                    Terverifikasi
                </div>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="role-alert">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <!-- Pilihan role -->
            <form
                method="post"
                action="<?= site_url('select-role') ?>"
                class="role-list">

                <?= csrf_field() ?>

                <?php foreach ($roles as $role): ?>

                    <?php
                    $roleLabel = match ($role) {
                        'admin'    => 'Admin',
                        'operator' => 'Operator',
                        'ketua'    => 'Ketua',
                        default    => ucfirst($role),
                    };

                    $roleDescription = match ($role) {
                        'admin'    => 'Kelola sistem dan data pengguna',
                        'operator' => 'Kelola dan input data risiko',
                        'ketua'    => 'Review dan persetujuan risiko',
                        default    => 'Akses sistem KERIS',
                    };
                    ?>

                    <button
                        type="submit"
                        name="role"
                        value="<?= esc($role) ?>"
                        class="role-option">

                        <div class="role-option-icon">

                            <?php if ($role === 'admin'): ?>

                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round">

                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />

                                </svg>

                            <?php elseif ($role === 'operator'): ?>

                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round">

                                    <path d="M12 20h9" />
                                    <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" />

                                </svg>

                            <?php else: ?>

                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round">

                                    <circle cx="12" cy="12" r="9" />
                                    <path d="m8 12 2.5 2.5L16 9" />

                                </svg>

                            <?php endif; ?>

                        </div>

                        <div class="role-option-text">
                            <strong><?= esc($roleLabel) ?></strong>
                            <span><?= esc($roleDescription) ?></span>
                        </div>

                        <div class="role-option-arrow">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round">

                                <path d="m9 18 6-6-6-6" />

                            </svg>
                        </div>

                    </button>

                <?php endforeach; ?>

            </form>

            <p class="role-note">
                Role yang dipilih akan digunakan selama sesi berlangsung.
            </p>

        </div>

        <!-- Bawah card -->
        <a href="<?= site_url('logout') ?>" class="role-back">
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round">

                <path d="m15 18-6-6 6-6" />

            </svg>

            Kembali ke Login
        </a>

        <p class="role-copyright">
            &copy; <?= date('Y') ?> BPS Provinsi Riau &mdash; KERIS v1.0
        </p>

    </div>

</div>

</body>
</html>