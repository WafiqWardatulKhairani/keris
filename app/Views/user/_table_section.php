<div class="card border-0 shadow-sm mu-table-card">

    <div class="card-body">

        <!-- TOOLBAR -->
        <div class="mu-toolbar mb-3">
            <div class="mu-search">
                <i class="ti ti-search"></i>

                <input
                    type="text"
                    id="muSearch"
                    placeholder="Cari nama, email, role, atau tim...">

                <button
                    type="button"
                    id="muSearchClear"
                    class="d-none"
                    aria-label="Hapus pencarian">
                    <i class="ti ti-x"></i>
                </button>
            </div>
        </div>

        <!-- TABLE -->
        <div class="table-responsive mu-table-wrapper">

            <table class="table align-middle mb-0 mu-table">

                <thead>
                    <tr>
                        <th class="mu-col-number">#</th>
                        <th class="mu-col-name">Nama</th>
                        <th class="mu-col-email">Email</th>
                        <th class="mu-col-role">Role</th>
                        <th class="mu-col-team">Tim Kerja</th>
                    </tr>
                </thead>

                <tbody id="muTableBody">

                    <tr>
                        <td colspan="5">
                            <div class="mu-empty-state">
                                <span class="text-muted">
                                    Memuat data...
                                </span>
                            </div>
                        </td>
                    </tr>

                </tbody>

            </table>

        </div>

    </div>

    <!-- TABLE FOOTER -->
    <div class="ar-table-bottom mu-table-footer">

        <div class="ar-table-info">

            <select
                id="muPerPage"
                class="ar-perpage"
                aria-label="Jumlah data per halaman">

                <option value="5">5</option>
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>

            </select>

            <div
                class="ar-info-text"
                id="muInfo">
                Menampilkan 0 data
            </div>

        </div>

        <div class="ar-pagination">
            <ul
                class="pagination mb-0"
                id="muPagination">
            </ul>
        </div>

    </div>

</div>