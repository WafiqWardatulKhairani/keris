<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'KERIS' ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="<?= base_url('assets/fonts/tabler-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/fonts/feather.css') ?>">

    <!-- Core Styles -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style-preset.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/base/base.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/layout/navbar.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/layout/footer.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/layout/sidebar.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/responsive/mobile.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/context-selector.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/context-active.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/select2-custom.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/uikit.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@chenfengyuan/datepicker/dist/datepicker.min.css">

    <!-- OFFCANVAS KONTEKS CUSTOM CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/tabs.css') ?>">
    <link rel=" stylesheet" href="<?= base_url('assets/css/combobox.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/module/penetapan_konteks/ruang_lingkup/ruang-lingkup.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/penetapan-konteks.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/konteks.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/identifikasi-risiko.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/analisis-risiko.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/evaluasi-risiko.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/rencana-penanganan.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/pemantauan-risiko.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/pelaporan-risiko.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/satuan-kerja.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/kegiatan.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/module/master/manajemen-user.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/module/master/tim-kerja.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/module/master/penugasan-tim.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/module/master/pengelola-risiko.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/module/master/bank-risiko.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/sasaran-strategis.css') ?>">

    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

    <?= $this->include('partials/sidebar') ?>
    <?= $this->include('partials/navbar') ?>

    <div class="pc-container">
        <div class="pc-content">
            <?= $this->renderSection('content') ?>
            <?= $this->include('partials/footer') ?>
        </div>
    </div>
    <!-- ================= JS AREA ================= -->

    <!-- Global JS Variables -->
    <script>
        const baseUrl = "<?= base_url() ?>";
        const csrfName = "<?= csrf_token() ?>";
        const csrfToken = "<?= csrf_hash() ?>";
    </script>

    <!-- Bootstrap Bundle (WAJIB & SATU-SATUNYA) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


    <!-- jQuery (WAJIB untuk Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Tooltip Init -->
    <script>
        const tooltipTriggerList = [].slice.call(
            document.querySelectorAll('[data-bs-toggle="tooltip"]')
        );
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>

    <!-- Plugins -->
    <script src="<?= base_url('assets/js/plugins/simplebar.min.js') ?>"></script>

    <!-- Layout / Sidebar -->
    <script src="<?= base_url('assets/js/pcoded.js') ?>"></script>

    <!-- Feather Icons -->
    <script src="<?= base_url('assets/js/plugins/feather.min.js') ?>"></script>
    <script>
        feather.replace();
    </script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- Flatpickr Month Select -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/@chenfengyuan/datepicker/dist/datepicker.min.js"></script>

    <!-- GLOBAL COMPONENT -->
    <script src="<?= base_url('assets/js/components/combobox.js') ?>"></script>
    <script src="<?= base_url('assets/js/components/pk-alert.js') ?>"></script>
    <script src="<?= base_url('assets/js/components/pk-ajax.js') ?>"></script>

    <?= $this->renderSection('scripts') ?>

    <!-- Flash Message -->
    <?php if (session()->getFlashdata('success')): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '<?= session()->getFlashdata('success') ?>',
                timer: 2000,
                showConfirmButton: false,
                customClass: {
                    popup: 'swal-mantis'
                }
            });
        </script>
    <?php endif; ?>
    
    <script>
    document.addEventListener("DOMContentLoaded", function () {
    
        const currentUrl = window.location.pathname.replace(/\/$/, "");
    
        // Active link
        document.querySelectorAll(".pc-navbar .pc-link[href]").forEach(link => {
    
            const href = new URL(link.href).pathname.replace(/\/$/, "");
    
            if (href === currentUrl) {
    
                // aktifkan item
                const item = link.closest(".pc-item");
                if (item) item.classList.add("active");
    
                // expand semua parent menu
                let parent = item?.parentElement;
    
                while (parent) {
    
                    if (parent.classList.contains("pc-submenu")) {
    
                        parent.style.display = "block";
    
                        const hasMenu = parent.closest(".pc-hasmenu");
    
                        if (hasMenu) {
                            hasMenu.classList.add("pc-trigger");
                            hasMenu.classList.add("active");
                        }
                    }
    
                    parent = parent.parentElement;
                }
            }
    
        });
    
    });
    </script>
</body>

</html>