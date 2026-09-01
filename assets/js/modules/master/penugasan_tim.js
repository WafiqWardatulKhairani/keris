const PT_URL = window.PT_CONFIG?.url || {};
let ptModal = null;
let rawData = [];
let filteredData = [];
let currentPage = 1;
let perPage = 10;

document.addEventListener("DOMContentLoaded", () => {
  document.getElementById("ptSearch")?.addEventListener("keyup", (e) => {
    const keyword = e.target.value.toLowerCase();

    document
      .getElementById("ptSearchClear")
      ?.classList.toggle("d-none", !keyword);

    filteredData = rawData.filter((d) => {
      return (
        (d.nama_pengelola || "").toLowerCase().includes(keyword) ||
        (d.nama_tim || "").toLowerCase().includes(keyword) ||
        String(d.tahun).includes(keyword)
      );
    });

    currentPage = 1;

    render();
  });

  document.getElementById("ptSearchClear")?.addEventListener("click", () => {
    document.getElementById("ptSearch").value = "";

    filteredData = [...rawData];

    currentPage = 1;

    render();

    document.getElementById("ptSearchClear").classList.add("d-none");
  });

  document.getElementById("ptPerPage")?.addEventListener("change", (e) => {
    perPage = parseInt(e.target.value);

    currentPage = 1;

    render();
  });

  document.addEventListener("click", (e) => {
    const pageBtn = e.target.closest("[data-page]");

    if (pageBtn) {
      e.preventDefault();

      const page = parseInt(pageBtn.dataset.page);

      const totalPages = Math.ceil(filteredData.length / perPage);

      if (page < 1 || page > totalPages) return;

      currentPage = page;

      render();
    }
  });

  ptModal = bootstrap.Offcanvas.getOrCreateInstance(
    document.getElementById("ptForm"),
  );

  loadTable();
  loadDropdown();
});

function setMode(mode) {
  const hasId = !!document.getElementById("ptId").value;

  const isView = mode === "view";
  const isEdit = mode === "edit";

  document.getElementById("ptMode").value = mode;

  updateTitle(mode);

  document.getElementById("ptPengelola").disabled = isView;
  document.getElementById("ptTimKerja").disabled = isView;
  document.getElementById("ptTahun").disabled = isView;
  document.getElementById("ptRoleKetua").disabled = isView;
  document.getElementById("ptRoleOperator").disabled = isView;

  document.getElementById("ptBtnEdit").classList.toggle("d-none", !isView);

  document
    .getElementById("ptBtnDelete")
    .classList.toggle("d-none", !(isView && hasId));

  document.getElementById("ptBtnSimpan").classList.toggle("d-none", isView);

  document.getElementById("ptBtnBatal").classList.toggle("d-none", !isEdit);

  document.getElementById("ptBtnClose").classList.toggle("d-none", isEdit);
}

function updateTitle(mode) {
  const title = document.getElementById("ptOffcanvasTitle");

  if (!title) return;

  if (mode === "create") {
    title.textContent = "Tambah Penugasan Tim";
  } else if (mode === "edit") {
    title.textContent = "Edit Penugasan Tim";
  } else {
    title.textContent = "Detail Penugasan Tim";
  }
}

function loadDropdown() {
  fetch(PT_URL.timTable)
    .then((r) => r.json())
    .then((data) => {
      //console.log(data);
      let html = '<option value="">Pilih</option>';
      data.forEach((d) => {
        html += `<option value="${d.id}">${d.nama_tim}</option>`;
      });
      document.getElementById("ptTimKerja").innerHTML = html;
    });

  fetch(PT_URL.pengelolaTable)
    .then((r) => r.json())
    .then((data) => {
      let html = '<option value="">Pilih</option>';
      data.forEach((d) => {
        html += `<option value="${d.id}">${d.nama}</option>`;
      });
      document.getElementById("ptPengelola").innerHTML = html;
    });
}

function loadTable() {
  fetch(PT_URL.table)
    .then((r) => r.json())
    .then((data) => {
      //console.log(data);
      rawData = data;
      filteredData = [...rawData];
      render();
    });
}

function render() {
  const tbody = document.getElementById("ptTableBody");

  const start = (currentPage - 1) * perPage;
  const end = start + perPage;

  const pageData = filteredData.slice(start, end);

  if (!pageData.length) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="text-center text-muted py-4">
          Tidak ada data ditemukan
        </td>
      </tr>
    `;

    updateInfo();
    renderPagination();

    return;
  }

  let html = "";

  pageData.forEach((d, i) => {
    html += `
      <tr class="pt-row"
          data-id="${d.id}"
          data-p="${d.pengelola_id}"
          data-tim="${d.tim_kerja_id}"
          data-t="${d.tahun}"
          data-k="${d.is_ketua_tim}">
          
          <td>${start + i + 1}</td>
          <td>${d.nama_pengelola}</td>
          <td>${d.nama_tim}</td>
          <td>${d.tahun}</td>
          <td>${
            ["t", "true", "1", true, 1].includes(d.is_ketua_tim) ? "✔" : ""
          }</td>
      </tr>
    `;
  });

  tbody.innerHTML = html;

  updateInfo();
  renderPagination();
}

function renderPagination() {
  const totalPages = Math.ceil(filteredData.length / perPage);

  const container = document.getElementById("ptPagination");

  if (totalPages <= 1) {
    container.innerHTML = "";
    return;
  }

  let html = "";

  html += `
    <li class="page-item ${currentPage === 1 ? "disabled" : ""}">
      <a class="page-link" href="#" data-page="${currentPage - 1}">
        &laquo;
      </a>
    </li>
  `;

  for (let i = 1; i <= totalPages; i++) {
    html += `
      <li class="page-item ${i === currentPage ? "active" : ""}">
        <a class="page-link" href="#" data-page="${i}">
          ${i}
        </a>
      </li>
    `;
  }

  html += `
    <li class="page-item ${currentPage === totalPages ? "disabled" : ""}">
      <a class="page-link" href="#" data-page="${currentPage + 1}">
        &raquo;
      </a>
    </li>
  `;

  container.innerHTML = html;
}

function updateInfo() {
  const info = document.getElementById("ptInfo");

  if (!filteredData.length) {
    info.innerHTML = "Menampilkan 0 data";
    return;
  }

  const start = (currentPage - 1) * perPage + 1;

  const end = Math.min(currentPage * perPage, filteredData.length);

  info.innerHTML = `Menampilkan ${start}-${end} dari ${filteredData.length} data`;
}

document.addEventListener("click", (e) => {
  if (e.target.closest(".pt-row")) {
    const r = e.target.closest(".pt-row");
    // console.log("Ketua =", r.dataset.k);
    // console.log(r.dataset.k);
    document.getElementById("ptId").value = r.dataset.id;
    document.getElementById("ptPengelola").value = r.dataset.p;
    // console.log({
    //   tim: r.dataset.tim,
    // });
    document.getElementById("ptTimKerja").value = r.dataset.tim;
    document.getElementById("ptTahun").value = r.dataset.t;
    const isKetua = ["t", "true", "1"].includes(r.dataset.k);

    document.getElementById("ptRoleKetua").checked = isKetua;
    document.getElementById("ptRoleOperator").checked = !isKetua;
    setMode("view");
    ptModal.show();
  }

  if (e.target.id === "btnTambah") {
    document.getElementById("ptId").value = "";
    document.getElementById("ptPengelola").value = "";
    document.getElementById("ptTimKerja").value = "";
    document.getElementById("ptTahun").value = new Date().getFullYear();
    document.getElementById("ptRoleOperator").checked = true;
    setMode("create");
    ptModal.show();
  }

  if (e.target.id === "ptBtnSimpan") {
    const id = document.getElementById("ptId").value;
    const body = new URLSearchParams({
      pengelola_id: document.getElementById("ptPengelola").value,
      tim_kerja_id: document.getElementById("ptTimKerja").value,
      tahun: document.getElementById("ptTahun").value,
      is_ketua_tim: document.getElementById("ptRoleKetua").checked ? 1 : 0,
    });

    const url = id ? PT_URL.update(id) : PT_URL.store;

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
        body,
      })
        .then(async (response) => {
          const result = await response.json();

          if (!response.ok) {
            throw new Error(result.message);
          }

          Swal.fire("Berhasil", "Data berhasil disimpan", "success");

          ptModal.hide();
          loadTable();
        })
        .catch((err) => {
          Swal.fire(
            "Validasi",
            err.message || "Gagal menyimpan data",
            "warning",
          );
        });
    });
  }

  if (e.target.id === "ptBtnEdit") {
    setMode("edit");
  }

  if (e.target.id === "ptBtnBatal") {
    setMode("view");
  }

  if (e.target.closest("#ptBtnDelete")) {
    const id = document.getElementById("ptId").value;

    Swal.fire({
      title: "Hapus data?",
      text: "Data tidak bisa dikembalikan",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Ya, hapus",
      cancelButtonText: "Batal",
    }).then((result) => {
      if (!result.isConfirmed) return;

      fetch(PT_URL.delete(id), {
        method: "POST",
      })
        .then(() => {
          Swal.fire("Berhasil", "Data berhasil dihapus", "success");

          ptModal.hide();
          loadTable();
        })
        .catch(() => {
          Swal.fire("Error", "Gagal menghapus data", "error");
        });
    });
  }

  if (e.target.id === "ptBtnClose") ptModal.hide();
});
