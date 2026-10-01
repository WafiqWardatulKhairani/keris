const MU_URL = window.MU_CONFIG?.url || {};
let muModal = null;
let rawData = [];
let filteredData = [];
let currentPage = 1;
let perPage = 10;

document.addEventListener("DOMContentLoaded", () => {
  const el = document.getElementById("muForm");
  if (el && typeof bootstrap !== "undefined") {
    muModal = bootstrap.Modal.getOrCreateInstance(el);
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
        (row.roles || []).join(" ").toLowerCase().includes(keyword) ||
        (row.tim_kerja || []).join(" ").toLowerCase().includes(keyword)
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
  // ROLE
  fetch(MU_URL.roles)
    .then((r) => r.json())
    .then((roles) => {
      let html = "";

      roles.forEach((r) => {
        html += `
          <div class="form-check mb-2">
            <input
              class="form-check-input mu-role-check"
              type="checkbox"
              value="${r.id}"
              id="muRole${r.id}"
            >
            <label
              class="form-check-label"
              for="muRole${r.id}"
            >
              ${r.name}
            </label>
          </div>
        `;
      });

      document.getElementById("muRole").innerHTML =
        html || '<div class="text-muted small">Role tidak tersedia</div>';
    });

  // TIM KERJA
  fetch(MU_URL.timKerja)
    .then((r) => r.json())
    .then((tim) => {
      let html = `
      <div class="form-check mb-2 pb-2 border-bottom">
        <input
          class="form-check-input"
          type="checkbox"
          id="muTimSelectAll"
        >
        <label
          class="form-check-label fw-semibold"
          for="muTimSelectAll"
        >
          Pilih Semua Tim
        </label>
      </div>
    `;

      tim.forEach((t) => {
        html += `
        <div class="form-check mb-2">
          <input
            class="form-check-input mu-tim-check"
            type="checkbox"
            value="${t.id_tim}"
            id="muTim${t.id_tim}"
          >
          <label
            class="form-check-label"
            for="muTim${t.id_tim}"
          >
            ${t.nama_tim}
          </label>
        </div>
      `;
      });

      document.getElementById("muTim").innerHTML =
        html || '<div class="text-muted small">Tim kerja tidak tersedia</div>';
    });
}

function syncTimSelectAll() {
  const selectAll = document.getElementById("muTimSelectAll");
  const timCheckboxes = Array.from(document.querySelectorAll(".mu-tim-check"));

  if (!selectAll || timCheckboxes.length === 0) return;

  selectAll.checked = timCheckboxes.every((checkbox) => checkbox.checked);
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

  // Role
  document.querySelectorAll(".mu-role-check").forEach((checkbox) => {
    checkbox.disabled = isView;
  });

  // Tim Kerja
  document.querySelectorAll(".mu-tim-check").forEach((checkbox) => {
    checkbox.disabled = isView;
  });

  const timSelectAll = document.getElementById("muTimSelectAll");

  if (timSelectAll) {
    timSelectAll.disabled = isView;
  }

  document
    .getElementById("muPasswordHint")
    .classList.toggle("d-none", isCreate);

  document.getElementById("muBtnEdit").classList.toggle("d-none", !isView);

  document
    .getElementById("muBtnDelete")
    .classList.toggle("d-none", !(isView && hasId));

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
    // ROLE
    const roleHtml = (row.roles || []).length
      ? row.roles
          .map((role) => {
            const roleClass = role.toLowerCase();

            return `
        <span class="mu-role-badge mu-role-${roleClass}">
          <span class="mu-role-dot"></span>
          ${role}
        </span>
      `;
          })
          .join("")
      : '<span class="mu-empty-value">—</span>';

    // TIM KERJA
    const timList = row.tim_kerja || [];

    let timHtml = "-";

    if (timList.length === 1) {
      timHtml = timList[0];
    } else if (timList.length > 1) {
      timHtml = `
    <div class="d-flex align-items-center flex-wrap gap-1">
      <span>${timList[0]}</span>

      <span class="badge bg-light text-secondary border">
        +${timList.length - 1} lainnya
      </span>
    </div>
  `;
    }
    html += `
      <tr class="mu-row"
          data-id="${row.id}">

        <td>${start + i + 1}</td>

        <td>${row.name}</td>

        <td>${row.email}</td>

<td>
  <div class="mu-role-list">
    ${roleHtml}
  </div>
</td>

        <td>
          ${timHtml}
        </td>

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

document.addEventListener("change", (e) => {
  // Klik "Pilih Semua Tim"
  if (e.target.id === "muTimSelectAll") {
    document.querySelectorAll(".mu-tim-check").forEach((checkbox) => {
      if (!checkbox.disabled) {
        checkbox.checked = e.target.checked;
      }
    });

    return;
  }

  // Centang tim satu per satu
  if (e.target.classList.contains("mu-tim-check")) {
    syncTimSelectAll();
  }
});

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
        const roleIds = (data.role_ids || []).map(String);
        const timIds = (data.tim_ids || []).map(String);

        document.querySelectorAll(".mu-role-check").forEach((checkbox) => {
          checkbox.checked = roleIds.includes(checkbox.value);
        });
        document.querySelectorAll(".mu-tim-check").forEach((checkbox) => {
          checkbox.checked = timIds.includes(checkbox.value);
        });

        syncTimSelectAll();

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

    document.querySelectorAll(".mu-role-check").forEach((checkbox) => {
      checkbox.checked = false;
    });

    document.querySelectorAll(".mu-tim-check").forEach((checkbox) => {
      checkbox.checked = false;
    });

    syncTimSelectAll();

    setMode("create");

    muModal.show();

    return;
  }

  if (e.target.closest("#muBtnSimpan")) {
    const id = document.getElementById("muId").value;

    const selectedRoles = Array.from(
      document.querySelectorAll(".mu-role-check:checked"),
    ).map((checkbox) => checkbox.value);

    const selectedTim = Array.from(
      document.querySelectorAll(".mu-tim-check:checked"),
    ).map((checkbox) => checkbox.value);

    if (selectedRoles.length === 0) {
      Swal.fire(
        "Role belum dipilih",
        "Pilih minimal satu role untuk user.",
        "warning",
      );
      return;
    }

    if (selectedTim.length === 0) {
      Swal.fire(
        "Tim kerja belum dipilih",
        "Pilih minimal satu tim kerja untuk user.",
        "warning",
      );
      return;
    }

    const formData = new URLSearchParams();

    formData.append("name", document.getElementById("muNama").value);

    formData.append("email", document.getElementById("muEmail").value);

    formData.append("password", document.getElementById("muPassword").value);

    selectedRoles.forEach((roleId) => {
      formData.append("role_ids[]", roleId);
    });

    selectedTim.forEach((timId) => {
      formData.append("tim_ids[]", timId);
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
      })
        .then(async (response) => {
          const res = await response.json();

          if (!response.ok || res.status === false) {
            throw new Error(res.message || "Gagal menghapus user");
          }

          return res;
        })
        .then((res) => {
          Swal.fire(
            "Berhasil",
            res.message || "User berhasil dihapus",
            "success",
          );

          muModal.hide();
          loadTable();
        })
        .catch((error) => {
          Swal.fire("Gagal", error.message, "error");

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
