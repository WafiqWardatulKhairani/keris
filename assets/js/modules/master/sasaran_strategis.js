const SS_URL = window.SS_CONFIG?.url || {};
let ssModal = null;
let currentPage = 1;
let perPage = 10;
let rawData = [];
let filteredData = [];

document.addEventListener("DOMContentLoaded", () => {
  const el = document.getElementById("ssForm");
  ssModal = bootstrap.Offcanvas.getOrCreateInstance(el);

  document.getElementById("ssPerPage").onchange = (e) => {
    perPage = parseInt(e.target.value);
    currentPage = 1;
    renderTable();
  };

  document.getElementById("ssSearch").addEventListener("input", (e) => {
    const keyword = e.target.value.toLowerCase().trim();

    filteredData = rawData.filter((row) => {
      return (
        row.kode_sasaran?.toLowerCase().includes(keyword) ||
        row.uraian_sasaran?.toLowerCase().includes(keyword)
      );
    });

    currentPage = 1;

    document
      .getElementById("ssSearchClear")
      .classList.toggle("d-none", keyword === "");

    renderTable();
  });

  document.getElementById("ssSearchClear").addEventListener("click", () => {
    document.getElementById("ssSearch").value = "";

    filteredData = [...rawData];

    currentPage = 1;

    document.getElementById("ssSearchClear").classList.add("d-none");

    renderTable();
  });

  loadTable();
});

function setMode(mode) {
  const hasId = !!document.getElementById("ssId").value;

  updateTitle(mode);

  const isView = mode === "view";
  const isEdit = mode === "edit";

  document.getElementById("ssMode").value = mode;

  document.getElementById("ssKode").disabled = isView;
  document.getElementById("ssUraian").disabled = isView;

  document.getElementById("ssBtnEdit").classList.toggle("d-none", !isView);

  document
    .getElementById("ssBtnDelete")
    .classList.toggle("d-none", !(isView && hasId));

  document.getElementById("ssBtnSimpan").classList.toggle("d-none", isView);

  document.getElementById("ssBtnBatal").classList.toggle("d-none", !isEdit);

  document.getElementById("ssBtnClose").classList.toggle("d-none", isEdit);
}

function loadTable() {
  fetch(SS_URL.table)
    .then((r) => r.json())
    .then((data) => {
      rawData = data;
      filteredData = [...data];
      renderTable();
    });
}

function updateTitle(mode) {
  const title = document.getElementById("ssOffcanvasTitle");

  if (!title) return;

  if (mode === "create") {
    title.textContent = "Tambah Sasaran Strategis";
  } else if (mode === "edit") {
    title.textContent = "Edit Sasaran Strategis";
  } else {
    title.textContent = "Detail Sasaran Strategis";
  }
}

function renderInfo() {
  const total = filteredData.length;

  if (total === 0) {
    document.getElementById("ssInfo").innerText = "Menampilkan 0 data";
    return;
  }

  const from = (currentPage - 1) * perPage + 1;
  const to = Math.min(currentPage * perPage, total);

  document.getElementById("ssInfo").innerText =
    `Menampilkan ${from}-${to} dari ${total} data`;
}

function renderPagination() {
  const totalPage = Math.ceil(filteredData.length / perPage);

  let html = "";

  for (let i = 1; i <= totalPage; i++) {
    html += `
      <li class="page-item ${i === currentPage ? "active" : ""}">
        <button
          type="button"
          class="page-link"
          onclick="goPage(${i})">
          ${i}
        </button>
      </li>
    `;
  }

  document.getElementById("ssPagination").innerHTML = html;
}

function goPage(page) {
  currentPage = page;
  renderTable();
}

function renderTable() {
  const start = (currentPage - 1) * perPage;
  const slice = filteredData.slice(start, start + perPage);

  let html = "";

  if (!slice.length) {
    html = `
      <tr>
        <td colspan="3"
            class="text-center py-4 text-muted">
          Tidak ada data
        </td>
      </tr>
    `;
  } else {
    slice.forEach((row, i) => {
      html += `
        <tr class="ss-row"
            data-id="${row.id}"
            data-kode="${row.kode_sasaran}"
            data-uraian="${row.uraian_sasaran}">

          <td>${start + i + 1}</td>

          <td>
            ${row.kode_sasaran}
          </td>

          <td>
            ${row.uraian_sasaran}
          </td>

        </tr>
      `;
    });
  }

  document.getElementById("ssTableBody").innerHTML = html;

  renderInfo();
  renderPagination();
}

document.addEventListener("click", (e) => {
  const row = e.target.closest(".ss-row");
  if (row) {
    document.getElementById("ssId").value = row.dataset.id;
    document.getElementById("ssKode").value = row.dataset.kode;
    document.getElementById("ssUraian").value = row.dataset.uraian;
    setMode("view");
    ssModal.show();
  }

  if (e.target.id === "btnTambah") {
    document.getElementById("ssId").value = "";
    document.getElementById("ssKode").value = "";
    document.getElementById("ssUraian").value = "";
    setMode("create");
    ssModal.show();
  }

  if (e.target.id === "ssBtnEdit") {
    setMode("edit");
  }

  if (e.target.id === "ssBtnBatal") {
    setMode("view");
  }

  if (e.target.closest("#ssBtnSimpan")) {
    e.preventDefault();
    const id = document.getElementById("ssId").value;
    const kode = document.getElementById("ssKode").value;
    const uraian = document.getElementById("ssUraian").value;

    if (!kode || !uraian) {
      Swal.fire("Validasi", "Lengkapi semua field", "warning");
      return;
    }

    const url = id ? SS_URL.update(id) : SS_URL.store;
    Swal.fire({
      title: "Simpan data?",
      text: "Pastikan data yang dimasukkan sudah benar",
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Ya, simpan",
      cancelButtonText: "Batal",
    }).then((result) => {
      if (!result.isConfirmed) return;

      fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `kode=${encodeURIComponent(kode)}&uraian=${encodeURIComponent(uraian)}`,
      })
        .then(() => {
          Swal.fire("Berhasil", "Data disimpan", "success");
          ssModal.hide();
          loadTable();
        })
        .catch(() => Swal.fire("Error", "Gagal", "error"));
    });
  }

  if (e.target.closest("#ssBtnDelete")) {
    const id = document.getElementById("ssId").value;

    Swal.fire({
      title: "Hapus data?",
      text: "Data tidak bisa dikembalikan",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Ya, hapus",
      cancelButtonText: "Batal",
    }).then((result) => {
      if (!result.isConfirmed) return;

      fetch(SS_URL.delete(id), {
        method: "POST",
      })
        .then(() => {
          Swal.fire("Berhasil", "Dihapus", "success");
          ssModal.hide();
          loadTable();
        })
        .catch(() => {
          Swal.fire("Error", "Gagal menghapus data", "error");
        });
    });
  }

  if (e.target.id === "ssBtnClose") {
    ssModal.hide();
  }
});
