
<div class="card border-0 shadow-sm" id="glosariumTableCard">
    <div class="card-body">

        <div class="br-toolbar mb-3">
            <div class="br-search">
                <i class="ti ti-search"></i>

                <input
                    type="text"
                    id="glosariumSearch"
                    placeholder="Cari istilah atau definisi...">

                <button
                    type="button"
                    id="glosariumSearchClear"
                    class="d-none">
                    <i class="ti ti-x"></i>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">No.</th>
                        <th style="width: 30%;">Istilah</th>
                        <th>Definisi</th>
                    </tr>
                </thead>

                <tbody id="glosariumTableBody">
                    <tr>
                        <td colspan="3"
                            class="text-center py-4 text-muted">
                            Memuat data glosarium...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

    <div class="ar-table-bottom">
        <div class="ar-table-info">
            <select id="glosariumPerPage" class="ar-perpage">
                <option value="5">5</option>
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>

            <div class="ar-info-text" id="glosariumInfo">
                Menampilkan 0 data
            </div>
        </div>

        <div class="ar-pagination">
            <ul class="pagination mb-0" id="glosariumPagination"></ul>
        </div>
    </div>
</div>
