const PR_URL = window.PR_CONFIG?.url || {};

let prModal = null;

let rawData = [];
let filteredData = [];

let currentPage = 1;
let perPage = 10;

document.addEventListener("DOMContentLoaded", () => {
  prModal = bootstrap.Offcanvas.getOrCreateInstance(
    document.getElementById("prForm"),
  );

  document.getElementById("prSearch")?.addEventListener("keyup", (e) => {
    const keyword = e.target.value.toLowerCase();

    document
      .getElementById("prSearchClear")
      ?.classList.toggle("d-none", !keyword);

    filteredData = rawData.filter((d) => {
      return (
        (d.nama || "").toLowerCase().includes(keyword) ||
        (d.nip || "").toLowerCase().includes(keyword) ||
        (d.jabatan || "").toLowerCase().includes(keyword)
      );
    });

    currentPage = 1;

    render();
  });

  document.getElementById("prSearchClear")?.addEventListener("click", () => {
    document.getElementById("prSearch").value = "";

    filteredData = [...rawData];

    currentPage = 1;

    render();

    document.getElementById("prSearchClear").classList.add("d-none");
  });

  document.getElementById("prPerPage")?.addEventListener("change", (e) => {
    perPage = parseInt(e.target.value);

    currentPage = 1;

    render();
  });

  document.addEventListener("click", (e) => {
    const pageBtn = e.target.closest("[data-page]");

    if (!pageBtn) return;

    e.preventDefault();

    const page = parseInt(pageBtn.dataset.page);

    const totalPages = Math.ceil(filteredData.length / perPage);

    if (page < 1 || page > totalPages) return;

    currentPage = page;

    render();
  });
    
  loadWilayah();
  loadTable();
});

function loadTable() {
  fetch(PR_URL.table)
    .then((r) => r.json())
    .then((data) => {
      rawData = data;
      filteredData = [...data];

      render();
    });
}

function loadWilayah() {
  fetch(PR_URL.wilayahTable)
    .then((r) => r.json())
    .then((data) => {
      let html = '<option value="">Pilih Wilayah</option>';

      data.forEach((d) => {
        html += `
          <option value="${d.id}">
            ${d.nama_wilayah}
          </option>
        `;
      });

      document.getElementById("prWilayah").innerHTML = html;
    });
}

function setMode(mode) {
  const hasId = !!document.getElementById("prId").value;

  const isView = mode === "view";
  const isEdit = mode === "edit";

  document.getElementById("prMode").value = mode;

  updateTitle(mode);

  document.getElementById("prNama").disabled = isView;
  document.getElementById("prNip").disabled = isView;
  document.getElementById("prJabatan").disabled = isView;
  document.getElementById("prWilayah").disabled = isView;
  document.getElementById("prPemilik").disabled = isView;
  document.getElementById("prAktif").disabled = isView;

  document.getElementById("prBtnEdit").classList.toggle("d-none", !isView);

  document
    .getElementById("prBtnDelete")
    .classList.toggle("d-none", !(isView && hasId));

  document.getElementById("prBtnSimpan").classList.toggle("d-none", isView);

  document.getElementById("prBtnBatal").classList.toggle("d-none", !isEdit);

  document.getElementById("prBtnTutup").classList.toggle("d-none", isEdit);
    
}

function updateTitle(mode) {
  const title = document.getElementById("prOffcanvasTitle");

  if (!title) return;

  if (mode === "create") {
    title.textContent = "Tambah Pengelola Risiko";
  } else if (mode === "edit") {
    title.textContent = "Edit Pengelola Risiko";
  } else {
    title.textContent = "Detail Pengelola Risiko";
  }
}

function render() {
  const tbody = document.getElementById("prTableBody");

  const start = (currentPage - 1) * perPage;

  const pageData = filteredData.slice(start, start + perPage);

  if (!pageData.length) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7"
            class="text-center text-muted py-4">
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
      <tr class="pr-row"
    data-id="${d.id}"
    data-nama="${d.nama}"
    data-nip="${d.nip}"
    data-jabatan="${d.jabatan}"
    data-wilayah="${d.wilayah_id}"
    data-pemilik="${d.is_pemilik}"
    data-aktif="${d.aktif}">

        <td>${start + i + 1}</td>

        <td>${d.nama ?? "-"}</td>

        <td>${d.nip ?? "-"}</td>

        <td>${d.jabatan ?? "-"}</td>

        <td>${d.nama_wilayah ?? "-"}</td>

        <td>
          ${["1", 1, true, "true", "t"].includes(d.is_pemilik) ? "✔" : ""}
        </td>

        <td>
          ${["1", 1, true, "true", "t"].includes(d.aktif) ? "Aktif" : "Nonaktif"}
        </td>

      </tr>
    `;
  });

  tbody.innerHTML = html;

  updateInfo();
  renderPagination();
}

function updateInfo() {
  const info = document.getElementById("prInfo");

  if (!filteredData.length) {
    info.innerHTML = "Menampilkan 0 data";
    return;
  }

  const start = (currentPage - 1) * perPage + 1;

  const end = Math.min(currentPage * perPage, filteredData.length);

  info.innerHTML = `Menampilkan ${start}-${end} dari ${filteredData.length} data`;
}

function renderPagination() {
  const totalPages = Math.ceil(filteredData.length / perPage);

  const container = document.getElementById("prPagination");

  if (totalPages <= 1) {
    container.innerHTML = "";
    return;
  }

  let html = "";

  html += `
    <li class="page-item ${currentPage === 1 ? "disabled" : ""}">
      <a class="page-link"
         href="#"
         data-page="${currentPage - 1}">
        &laquo;
      </a>
    </li>
  `;

  for (let i = 1; i <= totalPages; i++) {
    html += `
      <li class="page-item ${i === currentPage ? "active" : ""}">
        <a class="page-link"
           href="#"
           data-page="${i}">
          ${i}
        </a>
      </li>
    `;
  }

  html += `
    <li class="page-item ${currentPage === totalPages ? "disabled" : ""}">
      <a class="page-link"
         href="#"
         data-page="${currentPage + 1}">
        &raquo;
      </a>
    </li>
  `;

  container.innerHTML = html;
}
document.addEventListener("click", (e) => {
  if (e.target.closest("#btnTambah")) {
    document.getElementById("prId").value = "";

    document.getElementById("prNama").value = "";
    document.getElementById("prNip").value = "";
    document.getElementById("prJabatan").value = "";
    document.getElementById("prWilayah").value = "";

    document.getElementById("prPemilik").checked = false;
    document.getElementById("prAktif").checked = true;

    setMode("create");

    prModal.show();
  }

    if (e.target.closest("#prBtnSimpan")) {
      const nama = document.getElementById("prNama").value.trim();
      const nip = document.getElementById("prNip").value.trim();
      const jabatan = document.getElementById("prJabatan").value.trim();
      const wilayah = document.getElementById("prWilayah").value;

      if (!nama) {
        Swal.fire("Validasi", "Nama wajib diisi", "warning");
        return;
      }

      if (!nip) {
        Swal.fire("Validasi", "NIP wajib diisi", "warning");
        return;
      }

      if (!jabatan) {
        Swal.fire("Validasi", "Jabatan wajib diisi", "warning");
        return;
      }

      if (!wilayah) {
        Swal.fire("Validasi", "Wilayah wajib dipilih", "warning");
        return;
      }
    const id = document.getElementById("prId").value;

    const body = new URLSearchParams({
      nama: document.getElementById("prNama").value,
      nip: document.getElementById("prNip").value,
      jabatan: document.getElementById("prJabatan").value,
      wilayah_id: document.getElementById("prWilayah").value,
      is_pemilik: document.getElementById("prPemilik").checked ? 1 : 0,
      aktif: document.getElementById("prAktif").checked ? 1 : 0,
    });

    const url = id ? PR_URL.update(id) : PR_URL.store;

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

          prModal.hide();

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
    
    if (e.target.closest(".pr-row")) {
      const row = e.target.closest(".pr-row");

      document.getElementById("prId").value = row.dataset.id;

      document.getElementById("prNama").value = row.dataset.nama;

      document.getElementById("prNip").value = row.dataset.nip;

      document.getElementById("prJabatan").value = row.dataset.jabatan;

      document.getElementById("prWilayah").value = row.dataset.wilayah;

      document.getElementById("prPemilik").checked = [
        "1",
        "true",
        "t",
      ].includes(row.dataset.pemilik);

      document.getElementById("prAktif").checked = ["1", "true", "t"].includes(
        row.dataset.aktif,
      );
        
      setMode("view");

      prModal.show();
    }

    if (e.target.closest("#prBtnEdit")) {
      setMode("edit");
    }

    if (e.target.closest("#prBtnBatal")) {
      setMode("view");
    }

    if (e.target.closest("#prBtnDelete")) {
      const id = document.getElementById("prId").value;

      Swal.fire({
        title: "Nonaktifkan pengelola?",
        text: "Data akan diubah menjadi nonaktif",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya",
        cancelButtonText: "Batal",
      }).then((result) => {
        if (!result.isConfirmed) return;

        fetch(PR_URL.delete(id), {
          method: "POST",
        })
          .then((r) => r.json())
          .then(() => {
            Swal.fire(
              "Berhasil",
              "Pengelola berhasil dinonaktifkan",
              "success",
            );

            prModal.hide();

            loadTable();
          })
          .catch(() => {
            Swal.fire("Error", "Gagal menonaktifkan data", "error");
          });
      });
    }
});