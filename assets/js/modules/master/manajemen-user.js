const MU_URL = window.MU_CONFIG?.url || {};
let muModal = null;
let rawData = [];
let filteredData = [];
let currentPage = 1;
let perPage = 10;

document.addEventListener("DOMContentLoaded", () => {
  const el = document.getElementById("muForm");

  if (el && typeof bootstrap !== "undefined") {
    muModal = bootstrap.Offcanvas.getOrCreateInstance(el);
  }

  document.getElementById("muPerPage")?.addEventListener("change", (e) => {
    perPage = parseInt(e.target.value);
    currentPage = 1;
    renderTable();
  });

  document.getElementById("muSearch")?.addEventListener("input", (e) => {
    const keyword = e.target.value.toLowerCase().trim();

    document
      .getElementById("muSearchClear")
      ?.classList.toggle("d-none", !keyword);

    filteredData = rawData.filter((row) => {
      return (
        (row.name || "").toLowerCase().includes(keyword) ||
        (row.email || "").toLowerCase().includes(keyword) ||
        (row.role_name || "").toLowerCase().includes(keyword) ||
        (row.nama_tim || "").toLowerCase().includes(keyword)
      );
    });

    currentPage = 1;

    renderTable();
  });

  document.getElementById("muSearchClear")?.addEventListener("click", () => {
    document.getElementById("muSearch").value = "";

    filteredData = [...rawData];

    currentPage = 1;

    renderTable();

    document.getElementById("muSearchClear").classList.add("d-none");
  });

  loadDropdown();
  loadTable();
});

function loadDropdown() {
  fetch(MU_URL.roles)
    .then((r) => r.json())
    .then((roles) => {
      let html = '<option value="">Pilih Role</option>';

      roles.forEach((r) => {
        html += `
          <option value="${r.id}">
            ${r.name}
          </option>
        `;
      });

      document.getElementById("muRole").innerHTML = html;
    });

  fetch(MU_URL.timKerja)
    .then((r) => r.json())
    .then((tim) => {
      let html = '<option value="">Pilih Tim Kerja</option>';

      tim.forEach((t) => {
        html += `
          <option value="${t.id_tim}">
            ${t.nama_tim}
          </option>
        `;
      });

      document.getElementById("muTim").innerHTML = html;
    });
}

function setMode(mode) {
  const hasId = !!document.getElementById("muId").value;

  const isCreate = mode === "create";
  const isView = mode === "view";
  const isEdit = mode === "edit";

  updateTitle(mode);

  document.getElementById("muMode").value = mode;

  document.getElementById("muNama").disabled = isView;
  document.getElementById("muEmail").disabled = isView;
  document.getElementById("muPassword").disabled = isView;
  document.getElementById("muRole").disabled = isView;
  document.getElementById("muTim").disabled = isView;
    
  document.getElementById("muPasswordHint").classList.toggle("d-none", isCreate);

  document.getElementById("muBtnEdit").classList.toggle("d-none", !isView);

  document.getElementById("muBtnDelete").classList.toggle("d-none", !(isView && hasId));

  document.getElementById("muBtnSimpan").classList.toggle("d-none", isView);

  document.getElementById("muBtnBatal").classList.toggle("d-none", !isEdit);

  document.getElementById("muBtnTutup").classList.toggle("d-none", isEdit);
}

function loadTable() {
  fetch(MU_URL.table)
    .then((r) => r.json())
    .then((data) => {
      rawData = data;
      filteredData = [...data];

      renderTable();
    });
}

function updateTitle(mode) {
  const title = document.getElementById("muOffcanvasTitle");

  if (!title) return;

  if (mode === "create") {
    title.textContent = "Tambah User";
  } else if (mode === "edit") {
    title.textContent = "Edit User";
  } else {
    title.textContent = "Detail User";
  }
}

function renderTable() {
  const tbody = document.getElementById("muTableBody");

  const start = (currentPage - 1) * perPage;

  const pageData = filteredData.slice(start, start + perPage);

  let html = "";

  pageData.forEach((row, i) => {
    let badge = "secondary";

    if (row.role_name === "admin") {
      badge = "danger";
    } else if (row.role_name === "ketua") {
      badge = "warning";
    } else if (row.role_name === "operator") {
      badge = "primary";
    }

    html += `
      <tr class="mu-row"
          data-id="${row.id}">

          <td>${start + i + 1}</td>

          <td>${row.name}</td>

          <td>${row.email}</td>

          <td>
            <span class="badge bg-${badge}">
              ${row.role_name ?? "-"}
            </span>
          </td>

          <td>${row.nama_tim ?? "-"}</td>

      </tr>
    `;
  });

  tbody.innerHTML = html;

  renderInfo();
  renderPagination();
}

function renderInfo() {
  const total = filteredData.length;

  const from = total === 0 ? 0 : (currentPage - 1) * perPage + 1;

  const to = Math.min(currentPage * perPage, total);

  document.getElementById("muInfo").innerText =
    `Menampilkan ${from}-${to} dari ${total} data`;
}

function renderPagination() {
  const totalPage = Math.ceil(filteredData.length / perPage);
  let html = "";
  for (let i = 1; i <= totalPage; i++) {
    html += `<li class="page-item ${i === currentPage ? "active" : ""}">
<a class="page-link" href="#" onclick="goPage(${i})">${i}</a>
</li>`;
  }
  document.getElementById("muPagination").innerHTML = html;
}

function goPage(p) {
  currentPage = p;
  renderTable();
}

document.addEventListener("click", (e) => {
  const row = e.target.closest(".mu-row");

  if (row) {
    fetch(`${MU_URL.detail}/${row.dataset.id}`)
      .then((r) => r.json())
      .then((res) => {
        const data = res.data;

        document.getElementById("muId").value = data.id;
        document.getElementById("muNama").value = data.name ?? "";
        document.getElementById("muEmail").value = data.email ?? "";
        document.getElementById("muPassword").value = "";
        document.getElementById("muRole").value = data.role_id ?? "";
        document.getElementById("muTim").value = data.id_tim ?? "";

        setMode("view");

        muModal.show();
      });

    return;
  }

  if (e.target.closest("#btnTambahUser")) {
    document.getElementById("muId").value = "";
    document.getElementById("muNama").value = "";
    document.getElementById("muEmail").value = "";
    document.getElementById("muPassword").value = "";
    document.getElementById("muRole").value = "";
    document.getElementById("muTim").value = "";

    setMode("create");

    muModal.show();

    return;
    }
    
if (e.target.closest("#muBtnSimpan")) {
  const id = document.getElementById("muId").value;

  const formData = new URLSearchParams({
    name: document.getElementById("muNama").value,
    email: document.getElementById("muEmail").value,
    password: document.getElementById("muPassword").value,
    role_id: document.getElementById("muRole").value,
    id_tim: document.getElementById("muTim").value,
  });

  const url = id ? `${MU_URL.update}/${id}` : MU_URL.store;

  Swal.fire({
    title: "Simpan data?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Ya, simpan",
  }).then((result) => {
    if (!result.isConfirmed) return;

    fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: formData,
    }).then(() => {
      Swal.fire("Berhasil", "Data berhasil disimpan", "success");

      muModal.hide();

      loadTable();
    });
  });

  return;
    }
    
if (e.target.closest("#muBtnDelete")) {
  const id = document.getElementById("muId").value;

  Swal.fire({
    title: "Hapus user?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, hapus",
  }).then((result) => {
    if (!result.isConfirmed) return;

    fetch(`${MU_URL.delete}/${id}`, {
      method: "POST",
    }).then(() => {
      Swal.fire("Berhasil", "User berhasil dihapus", "success");

      muModal.hide();

      loadTable();
    });
  });

  return;
}

  if (e.target.closest("#muBtnEdit")) {
    setMode("edit");
    return;
  }

  if (e.target.closest("#muBtnBatal")) {
    setMode("view");
    return;
  }

  if (e.target.closest("#muBtnTutup")) {
    muModal.hide();
    return;
    }
    
    if (e.target.closest("#muTogglePassword")) {
      const input = document.getElementById("muPassword");
      const icon = e.target.closest("#muTogglePassword").querySelector("i");

      if (input.type === "password") {
        input.type = "text";

        icon.classList.remove("ti-eye");
        icon.classList.add("ti-eye-off");
      } else {
        input.type = "password";

        icon.classList.remove("ti-eye-off");
        icon.classList.add("ti-eye");
      }
    }
});
