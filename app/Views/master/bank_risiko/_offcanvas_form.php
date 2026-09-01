<div
    class="offcanvas offcanvas-end br-offcanvas"
    tabindex="-1"
    id="brForm">
    <div class="br-container">

        <div class="offcanvas-header border-bottom">
            <div>
                <h5
                    id="brTitle"
                    class="offcanvas-title mb-0 fw-semibold">
                    Detail Bank Risiko
                </h5>
                <small>Master Data</small>
            </div>
        </div>

        <div class="offcanvas-body">

            <input type="hidden" id="brMode" value="view">
            <input type="hidden" id="brId">

            <div class="mb-3">
                <label class="form-label">Pernyataan Risiko</label>
                <textarea id="brText" class="form-control" rows="3"></textarea>
            </div>

            <!-- APPROVAL SECTION -->
            <!-- <div id="brApprovalBox" class="br-approval-box d-none">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-semibold text-muted">Approval</span>
                </div>

                <div class="d-flex gap-2">
                    <button id="brBtnApprove" class="btn btn-success btn-sm flex-fill">
                        <i class="ti ti-check me-1"></i>Approve
                    </button>

                    <button id="brBtnReject" class="btn btn-danger btn-sm flex-fill">
                        <i class="ti ti-x me-1"></i>Reject
                    </button>
                </div>
            </div> -->

            <div class="d-flex align-items-center pt-3 border-top mt-3">

                <div>
                    <button id="brBtnDelete" class="btn btn-sm btn-danger d-none">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>

                <div class="ms-auto d-flex gap-2">
                    <button id="brBtnEdit" class="btn btn-sm btn-warning d-none">Edit</button>
                    <button id="brBtnBatal" class="btn btn-sm btn-light d-none">Batal</button>
                    <button id="brBtnSimpan" class="btn btn-sm btn-primary d-none">Simpan</button>
                    <button id="brBtnClose" class="btn btn-sm btn-light">Tutup</button>
                </div>

            </div>

        </div>
    </div>
</div>