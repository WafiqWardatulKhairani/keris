
<div
    class="offcanvas offcanvas-end br-offcanvas"
    tabindex="-1"
    id="glosariumForm">

    <div class="br-container">

        <div class="offcanvas-header border-bottom">
            <div>
                <h5
                    id="glosariumTitle"
                    class="offcanvas-title mb-0 fw-semibold">
                    Detail Istilah
                </h5>

                <small>Glosarium Manajemen Risiko</small>
            </div>
        </div>

        <div class="offcanvas-body">

            <input type="hidden" id="glosariumMode" value="view">
            <input type="hidden" id="glosariumId">

            <div class="mb-3">
                <label for="glosariumIstilah" class="form-label">
                    Istilah
                </label>

                <input
                    type="text"
                    id="glosariumIstilah"
                    class="form-control"
                    maxlength="255"
                    placeholder="Contoh: Risiko">
            </div>

            <div class="mb-3">
                <label for="glosariumDefinisi" class="form-label">
                    Definisi
                </label>

                <textarea
                    id="glosariumDefinisi"
                    class="form-control"
                    rows="5"
                    placeholder="Tuliskan pengertian istilah..."></textarea>
            </div>

            <div class="d-flex align-items-center pt-3 border-top mt-3">

                <div>
                    <button
                        type="button"
                        id="glosariumBtnDelete"
                        class="btn btn-sm btn-danger d-none">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>

                <div class="ms-auto d-flex gap-2">
                    <button
                        type="button"
                        id="glosariumBtnEdit"
                        class="btn btn-sm btn-warning d-none">
                        Edit
                    </button>

                    <button
                        type="button"
                        id="glosariumBtnCancel"
                        class="btn btn-sm btn-light d-none">
                        Batal
                    </button>

                    <button
                        type="button"
                        id="glosariumBtnSave"
                        class="btn btn-sm btn-primary d-none">
                        Simpan
                    </button>

                    <button
                        type="button"
                        id="glosariumBtnClose"
                        class="btn btn-sm btn-light">
                        Tutup
                    </button>
                </div>

            </div>

        </div>
    </div>
</div>
