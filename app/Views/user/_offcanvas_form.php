<div class="modal fade"
    id="muForm"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content mu-user-modal">

            <!-- HEADER -->
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1 fw-semibold"
                        id="muOffcanvasTitle">
                        Detail User
                    </h5>

                    <small class="text-muted">Manajemen User</small>
                </div>

                <button type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close">
                </button>
            </div>

            <!-- BODY -->
            <div class="modal-body">

                <input type="hidden" id="muMode" value="view">
                <input type="hidden" id="muId">

                <!-- INFORMASI USER -->
                <div class="mu-form-section-title">
                    Informasi User
                </div>

                <div class="row g-3 mb-4">

                    <div class="col-lg-4">
                        <label class="form-label">Nama</label>

                        <input type="text"
                            id="muNama"
                            class="form-control"
                            placeholder="Masukkan nama user">
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">Email</label>

                        <input type="email"
                            id="muEmail"
                            class="form-control"
                            placeholder="Masukkan email user">
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">Password</label>

                        <div class="position-relative">
                            <input type="password"
                                id="muPassword"
                                class="form-control pe-5"
                                placeholder="Masukkan password">

                            <button type="button"
                                id="muTogglePassword"
                                class="btn position-absolute top-50 end-0 translate-middle-y border-0 bg-transparent">

                                <i class="ti ti-eye"></i>
                            </button>
                        </div>

                        <small id="muPasswordHint"
                            class="text-muted">
                            Kosongkan jika tidak ingin mengganti password
                        </small>
                    </div>

                </div>

                <!-- AKSES USER -->
                <div class="mu-form-section-title">
                    Akses User
                </div>

                <div class="row g-3">

                    <!-- ROLE -->
                    <div class="col-lg-4">

                        <label class="form-label">
                            Role
                        </label>

                        <small class="text-muted d-block mb-2">
                            User dapat memiliki lebih dari satu role
                        </small>

                        <div id="muRole"
                            class="border rounded p-3 mu-access-box">

                            <div class="text-muted small">
                                Memuat role...
                            </div>

                        </div>

                    </div>

                    <!-- TIM KERJA -->
                    <div class="col-lg-8">

                        <label class="form-label">
                            Tim Kerja
                        </label>

                        <small class="text-muted d-block mb-2">
                            User dapat memiliki akses ke lebih dari satu tim kerja
                        </small>

                        <div id="muTim"
                            class="border rounded p-3 mu-team-box">

                            <div class="text-muted small">
                                Memuat tim kerja...
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <!-- FOOTER -->
            <div class="modal-footer">

                <button id="muBtnDelete"
                    type="button"
                    class="btn btn-sm btn-danger d-none me-auto">

                    <i class="ti ti-trash me-1"></i>
                    Hapus

                </button>

                <button id="muBtnEdit"
                    type="button"
                    class="btn btn-sm btn-warning text-white d-none">

                    <i class="ti ti-pencil me-1"></i>
                    Edit

                </button>

                <button id="muBtnBatal"
                    type="button"
                    class="btn btn-sm btn-light d-none">
                    Batal
                </button>

                <button id="muBtnSimpan"
                    type="button"
                    class="btn btn-sm btn-primary px-4 d-none">

                    <i class="ti ti-device-floppy me-1"></i>
                    Simpan

                </button>

                <button id="muBtnTutup"
                    type="button"
                    class="btn btn-sm btn-light">

                    Tutup

                </button>

            </div>

        </div>
    </div>
</div>