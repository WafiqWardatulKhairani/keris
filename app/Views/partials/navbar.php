<style>
    /* Batasi lebar area logo agar tidak makan terlalu banyak ruang */
    .navbar-bps-logo {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
        max-width: 220px;
        /* atau sesuaikan */
    }

    .navbar-bps-text {
        white-space: nowrap;
        /* cegah text wrap */
    }

    .navbar-bps-main {
        font-size: 13px;
        font-weight: 700;
        white-space: nowrap;
    }

    .navbar-bps-sub {
        font-size: 11px;
        white-space: nowrap;
    }
</style>

<!-- [ Header Topbar ] start -->
<header class="pc-header">
    <div class="header-wrapper"> <!-- [Mobile Media Block] start -->
        <div class="me-auto pc-mob-drp">
            <ul class="list-unstyled">

                <!-- ======= Menu collapse Icon ===== -->
                <li class="pc-h-item pc-sidebar-collapse">
                    <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
                <li class="pc-h-item pc-sidebar-popup">
                    <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
                <li class="dropdown pc-h-item d-inline-flex d-md-none">
                    <a
                        class="pc-head-link dropdown-toggle arrow-none m-0"
                        data-bs-toggle="dropdown"
                        href="#"
                        role="button"
                        aria-haspopup="false"
                        aria-expanded="false">
                        <i class="ti ti-search"></i>
                    </a>
                    <div class="dropdown-menu pc-h-dropdown drp-search">
                        <form class="px-3">
                            <div class="form-group mb-0 d-flex align-items-center">
                                <i data-feather="search"></i>
                                <input type="search" class="form-control border-0 shadow-none" placeholder="Search here. . .">
                            </div>
                        </form>
                    </div>
                </li>
            </ul>
            <div class="navbar-bps-logo">
                <img src="<?= base_url('assets/images/logo-bps.png') ?>" alt="BPS">
                <div class="navbar-bps-text">
                    <div class="navbar-bps-main">Badan Pusat Statistik</div>
                    <div class="navbar-bps-sub">Provinsi Riau</div>
                </div>
            </div>
        </div>

        <?php if (empty($hideGlobalContext)): ?>
            <?= $this->include('partials/global_context_selector') ?>
        <?php endif; ?>

        <!-- [Mobile Media Block end] -->
        <div class="ms-auto">
            <ul class="list-unstyled">
                <li class="dropdown pc-h-item header-user-profile">
                    <a
                        class="pc-head-link dropdown-toggle arrow-none me-0"
                        data-bs-toggle="dropdown"
                        href="#"
                        role="button"
                        aria-haspopup="false"
                        data-bs-auto-close="outside"
                        aria-expanded="false">
                        <span><?= esc(explode(' ', currentUserName())[0]) ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
                        <div class="dropdown-header">
                            <div class="d-flex mb-1">
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1"><?= esc(currentUserName()) ?></h6>
                                    <span class="text-muted">
                                        <?= ucfirst(session('user_role')) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>

                        <div class="px-3 py-2">

                            <small class="d-block mb-1">
                                <strong>NIP:</strong>
                                <?= esc(session('nip') ?? '-') ?>
                            </small>

                            <small class="d-block mb-1">
                                <strong>Jabatan:</strong>
                                <?= esc(session('jabatan') ?? '-') ?>
                            </small>

                            <small class="d-block mb-1">
                                <strong>Tim Kerja:</strong>
                                <?= esc(session('nama_tim') ?? '-') ?>
                            </small>

                            <small class="d-block">
                                <strong>Email:</strong>
                                <?= esc(session('email') ?? '-') ?>
                            </small>

                        </div>

                        <?php
                        $userRoles  = session('user_roles') ?? [];
                        $activeRole = session('user_role');
                        ?>

                        <?php if (count($userRoles) > 1): ?>

                            <div class="dropdown-divider"></div>

                            <div class="px-3 py-2">
                                <small class="text-muted d-block mb-2">
                                    <strong>Ganti Role</strong>
                                </small>

                                <form method="post" action="<?= site_url('select-role') ?>">
                                    <?= csrf_field() ?>

                                    <?php foreach ($userRoles as $role): ?>

                                        <?php
                                        $roleLabel = match ($role) {
                                            'admin'    => 'Admin',
                                            'operator' => 'Operator',
                                            'ketua'    => 'Ketua',
                                            default    => ucfirst($role),
                                        };

                                        $roleIcon = match ($role) {
                                            'admin'    => 'ti-shield',
                                            'operator' => 'ti-edit',
                                            'ketua'    => 'ti-circle-check',
                                            default    => 'ti-user',
                                        };

                                        $isActive = $role === $activeRole;
                                        ?>

                                        <button
                                            type="submit"
                                            name="role"
                                            value="<?= esc($role) ?>"
                                            class="dropdown-item d-flex align-items-center rounded <?= $isActive ? 'active' : '' ?>"
                                            <?= $isActive ? 'disabled' : '' ?>>
                                            <i class="ti <?= $roleIcon ?> me-2"></i>

                                            <span class="flex-grow-1 text-start">
                                                <?= esc($roleLabel) ?>
                                            </span>

                                            <?php if ($isActive): ?>
                                                <i class="ti ti-check"></i>
                                            <?php endif; ?>
                                        </button>

                                    <?php endforeach; ?>
                                </form>
                            </div>

                        <?php endif; ?>

                        <div class="dropdown-divider"></div>

                        <a href="<?= base_url('logout') ?>" class="dropdown-item">
                            <i class="ti ti-power"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>
<!-- [ Header ] end -->