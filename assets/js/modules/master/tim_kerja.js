const TK_URL = window.TK_CONFIG?.url || {};
let tkModal = null;
let currentPage = 1;
let perPage = 10;
let rawData = [];
let filteredData = [];

document.addEventListener("DOMContentLoaded", () => {
  const el = document.getElementById("tkForm");

  if (el && typeof bootstrap !== "undefined") {
    tkModal = bootstrap.Offcanvas.getOrCreateInstance(el);
  }

  document.getElementById("tkPerPage").onchange = (e) => {
    perPage = parseInt(e.target.value);
    currentPage = 1;
    renderTable();
  };

  // SEARCH REALTIME
  document.getElementById("tkSearch").addEventListener("input", (e) => {
    const keyword = e.target.value.toLowerCase().trim();

    document
      .getElementById("tkSearchClear")
      .classList.toggle("d-none", !keyword);

    currentPage = 1;

    filteredData = rawData.filter((row) => {
      return (
        row.nama_tim?.toLowerCase().includes(keyword) ||
        row.kegiatan?.toLowerCase().includes(keyword)
      );
    });

    renderTable();
  });

  // CLEAR SEARCH
  document.getElementById("tkSearchClear").addEventListener("click", () => {
    document.getElementById("tkSearch").value = "";

    filteredData = rawData;

    document.getElementById("tkSearchClear").classList.add("d-none");

    currentPage = 1;

    renderTable();
  });

  loadTable();
});

function setMode(mode) {
  const hasId = !!document.getElementById("tkId").value;
  updateTitle(mode);

  const isView = mode === "view";
  const isEdit = mode === "edit";
  const isCreate = mode === "create";

  document.getElementById("tkMode").value = mode;

  document.getElementById("tkNama").disabled = isView;

  // View
  document.getElementById("tkBtnEdit").classList.toggle("d-none", !isView);

  document
    .getElementById("tkBtnDelete")
    .classList.toggle("d-none", !(isView && hasId));

  // Edit
  document.getElementById("tkBtnBatal").classList.toggle("d-none", !isEdit);

  // Create & Edit
  document.getElementById("tkBtnSimpan").classList.toggle("d-none", isView);

  // Tutup hanya untuk View & Create
  document
    .querySelector('[data-bs-dismiss="offcanvas"]')
    .classList.toggle("d-none", isEdit);

  // ===== Kegiatan =====

  document.getElementById("tkAddKegiatan")?.classList.toggle("d-none", isView);

  document.querySelectorAll(".tk-kegiatan-remove").forEach((btn) => {
    btn.classList.toggle("d-none", isView);
  });

  document.querySelectorAll(".tk-kegiatan-input").forEach((input) => {
    input.disabled = isView;
  });

  document.getElementById("tkKegiatanView").classList.toggle("d-none", !isView);

  document
    .getElementById("tkKegiatanContainer")
    .classList.toggle("d-none", isView);

  document.getElementById("tkAddWrapper").classList.toggle("d-none", isView);
}

function addKegiatan(value = "", disabled = false, id = "") {
    const template = document.getElementById("tkKegiatanTemplate");

    if (!template) return;

    const clone = template.content.cloneNode(true);

    const input = clone.querySelector(".tk-kegiatan-input");

    input.value = value;
    input.disabled = disabled;
    input.dataset.id = id;

    document.getElementById("tkKegiatanContainer").appendChild(clone);
}

function clearKegiatan() {
  document.getElementById("tkKegiatanContainer").innerHTML = "";
}

function loadTable() {
  fetch(TK_URL.table)
    .then((r) => r.json())
    .then((data) => {
      rawData = data;
      filteredData = data;
      renderTable();
    });
}

function renderTable() {
  const start = (currentPage - 1) * perPage;
  const end = start + perPage;
  const slice = filteredData.slice(start, end);
  let html = "";
  if (!slice.length) {
    html = '<tr><td colspan="3" class="text-center">Tidak ada data</td></tr>';
  } else {
    slice.forEach((row, i) => {
      const kegiatanList = row.kegiatan ? row.kegiatan.split("||") : [];

      const firstKegiatan = kegiatanList[0] ?? "-";

      const moreCount = kegiatanList.length > 1 ? kegiatanList.length - 1 : 0;

      html += `
    <tr class="tk-row"
        data-id="${row.id}"
        data-nama="${row.nama_tim}">

        <td>${start + i + 1}</td>

        <td>
            <div class="tk-tim-name">
                ${row.nama_tim}
            </div>
        </td>

        <td>
            <div class="tk-kegiatan-preview">

                <span class="tk-kegiatan-text">
                    ${firstKegiatan}
                </span>

                ${
                  moreCount > 0
                    ? `
                        <span class="tk-kegiatan-more">
                            +${moreCount}
                        </span>
                      `
                    : ""
                }

            </div>
        </td>

    </tr>
  `;
    });
  }
  document.getElementById("tkTableBody").innerHTML = html;
  renderInfo();
  renderPagination();
}

function updateTitle(mode) {
  const title = document.getElementById("tkOffcanvasTitle");

  if (!title) return;

  if (mode === "create") {
    title.textContent = "Tambah Tim Kerja & Kegiatan";
  } else if (mode === "edit") {
    title.textContent = "Edit Tim Kerja & Kegiatan";
  } else {
    title.textContent = "Detail Tim Kerja & Kegiatan";
  }
}

function renderInfo() {
  const total = filteredData.length;
  const from = (currentPage - 1) * perPage + 1;
  const to = Math.min(currentPage * perPage, total);
  document.getElementById("tkInfo").innerText =
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
  document.getElementById("tkPagination").innerHTML = html;
}

function goPage(p) {
  currentPage = p;
  renderTable();
}

document.addEventListener("click", (e) => {
  if (e.target.closest("#tkAddKegiatan")) {
    addKegiatan();
    return;
  }

  if (e.target.closest(".tk-kegiatan-remove")) {
    e.target.closest(".tk-kegiatan-item").remove();
    return;
  }

  const row = e.target.closest(".tk-row");
  if (row) {
    fetch(TK_URL.detail(row.dataset.id))
      .then((r) => r.json())
      .then((data) => {
        document.getElementById("tkId").value = data.tim.id_tim;
        document.getElementById("tkNama").value = data.tim.nama_tim;

        clearKegiatan();

        const view = document.getElementById("tkKegiatanView");

        view.innerHTML = `
<div class="tk-kegiatan-view-list">
    ${
      data.kegiatan.length
        ? data.kegiatan
            .map(
              (k) => `
                <div class="tk-kegiatan-view-item">
                    <span class="tk-kegiatan-view-dot"></span>
                    <span>${k.nama_kegiatan}</span>
                </div>
              `,
            )
            .join("")
        : `
            <div class="text-muted">
                Belum ada kegiatan
            </div>
          `
    }
</div>
`;

        // PENTING
        if (!data.kegiatan.length) {
          addKegiatan();
        } else {
          data.kegiatan.forEach((k) => {
            addKegiatan(
                k.nama_kegiatan,
                true,
                k.id_kegiatan
            );
        });
        }
        setMode("view");
        tkModal.show();
      })
      .catch(() => {
        Swal.fire("Error", "Gagal mengambil detail tim kerja", "error");
      });
  }

  if (e.target.id === "btnTambah") {
    document.getElementById("tkId").value = "";
    document.getElementById("tkNama").value = "";
    clearKegiatan();
    addKegiatan();
    setMode("create");
    tkModal.show();
  }

  if (e.target.closest("#tkBtnEdit")) {
    setMode("edit");
  }

  if (e.target.closest("#tkBtnBatal")) {
    setMode("view");
  }

  if (e.target.closest("#tkBtnSimpan")) {
    e.preventDefault();
    const id = document.getElementById("tkId").value;
    const nama = document.getElementById("tkNama").value.trim();

    const kegiatan = [...document.querySelectorAll(".tk-kegiatan-input")]
        .map(el => ({
            id: el.dataset.id || null,
            nama: el.value.trim()
        }))
        .filter(v => v.nama !== "");

    if (!nama) {
      Swal.fire("Validasi", "Nama wajib diisi", "warning");
      return;
    }

    console.log({
      nama,
      kegiatan,
    });

    const url = id ? TK_URL.update(id) : TK_URL.store;
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
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          nama,
          kegiatan,
        }),
      })
        .then((res) => {
          if (!res.ok) throw new Error();
          return res.json();
        })
        .then(() => {
          Swal.fire("Berhasil", "Data berhasil disimpan", "success");

          tkModal.hide();
          loadTable();
        })
        .catch(() => {
          Swal.fire("Error", "Gagal menyimpan data", "error");
        });
    });
  }

  if (e.target.closest("#tkBtnDelete")) {
    const id = document.getElementById("tkId").value;
    if (!id) return;

    Swal.fire({
      title: "Hapus data?",
      text: "Data tidak bisa dikembalikan",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Ya, hapus",
      cancelButtonText: "Batal",
    }).then((result) => {
      if (!result.isConfirmed) return;

      fetch(TK_URL.delete(id), { method: "POST" })
        .then((res) => {
          if (!res.ok) throw new Error();
          return res.json();
        })
        .then(() => {
          Swal.fire("Berhasil", "Data berhasil dihapus", "success");
          tkModal.hide();
          loadTable();
        })
        .catch(() => {
          Swal.fire("Error", "Gagal menghapus data (500)", "error");
        });
    });
  }
});
