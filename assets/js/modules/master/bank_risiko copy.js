const BR_URL = window.BANK_RISIKO_CONFIG?.url || {};
let brModal = null;
let currentPage = 1;
let perPage = 10;
let rawData = [];

document.addEventListener("DOMContentLoaded", () => {
  const el = document.getElementById("brForm");
  if (el && typeof bootstrap !== "undefined") {
    brModal = bootstrap.Offcanvas.getOrCreateInstance(el);
    el.addEventListener("show.bs.offcanvas", () => {
      el.style.transform = "none";
      el.style.visibility = "visible";
    });
    el.addEventListener("shown.bs.offcanvas", () => {
      el.style.transform = "none";
    });
  }

  document.getElementById("brForm").addEventListener("click", (e) => {
    if (e.target.id === "brForm") brModal.hide();
  });

  document.getElementById("brPerPage").onchange = (e) => {
    perPage = parseInt(e.target.value);
    currentPage = 1;
    renderTable();
  };

  loadTable();
});

function setMode(mode) {
  const isView = mode === "view";
  const isEdit = mode === "edit";

  document.getElementById("brMode").value = mode;
  document.getElementById("brText").disabled = isView;

  document.getElementById("brBtnEdit").classList.toggle("d-none", !isView);
  document.getElementById("brBtnBatal").classList.toggle("d-none", !isEdit);
  document.getElementById("brBtnSimpan").classList.toggle("d-none", isView);
  document
    .getElementById("brApprovalBox")
    .classList.toggle("d-none", !(isView && hasId));

  const hasId = !!document.getElementById("brId").value;

  document
    .getElementById("brBtnDelete")
    .classList.toggle("d-none", !(isView && hasId));
  document
    .getElementById("brBtnApprove")
    .classList.toggle("d-none", !(isView && hasId));
  document
    .getElementById("brBtnReject")
    .classList.toggle("d-none", !(isView && hasId));
}

function loadTable() {
  fetch(BR_URL.table)
    .then((r) => r.json())
    .then((data) => {
      rawData = data;
      renderTable();
    });
}

function renderTable() {
  const start = (currentPage - 1) * perPage;
  const end = start + perPage;
  const slice = rawData.slice(start, end);
  let html = "";

  if (!slice.length) {
    html = '<tr><td colspan="3" class="text-center">Tidak ada data</td></tr>';
  } else {
    slice.forEach((row, i) => {
      const statusBadge =
        row.status === "approved"
          ? '<span class="badge bg-success">Approved</span>'
          : row.status === "pending"
            ? '<span class="badge bg-warning">Pending</span>'
            : '<span class="badge bg-danger">Rejected</span>';

      html += `<tr class="br-row" 
data-id="${row.id}" 
data-text="${escapeHtml(row.pernyataan_risiko)}"
data-status="${row.status}"
data-notes="${escapeHtml(row.notes || "")}">
<td>${start + i + 1}</td>
<td>${row.pernyataan_risiko}</td>
<td>${statusBadge}</td>
</tr>`;
    });
  }

  document.getElementById("brTableBody").innerHTML = html;
  renderInfo();
  renderPagination();
}

function renderInfo() {
  const total = rawData.length;
  const from = total === 0 ? 0 : (currentPage - 1) * perPage + 1;
  const to = Math.min(currentPage * perPage, total);
  document.getElementById("brInfo").innerText =
    `Menampilkan ${from}-${to} dari ${total} data`;
}

function renderPagination() {
  const totalPage = Math.ceil(rawData.length / perPage);
  let html = "";

  for (let i = 1; i <= totalPage; i++) {
    html += `<li class="page-item ${i === currentPage ? "active" : ""}">
<a class="page-link" href="#" onclick="goPage(${i})">${i}</a>
</li>`;
  }

  document.getElementById("brPagination").innerHTML = html;
}

function goPage(p) {
  currentPage = p;
  renderTable();
}

function escapeHtml(text) {
  if (!text) return "";
  return text
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

document.addEventListener("click", (e) => {
  const row = e.target.closest(".br-row");

  if (row) {
    document.getElementById("brId").value = row.dataset.id;
    document.getElementById("brText").value = row.dataset.text;

    setMode("view");
    brModal.show();
  }

  if (e.target.id === "btnTambah") {
    document.getElementById("brId").value = "";
    document.getElementById("brText").value = "";
    setMode("edit");
    brModal.show();
  }

  if (e.target.id === "brBtnEdit") setMode("edit");

  if (e.target.id === "brBtnBatal") setMode("view");

  if (e.target.id === "brBtnClose") brModal.hide();

  if (e.target.id === "brBtnSimpan") {
    const id = document.getElementById("brId").value;
    const text = document.getElementById("brText").value.trim();

    if (!text) {
      Swal.fire("Validasi", "Pernyataan risiko wajib diisi", "warning");
      return;
    }

    const url = id ? BR_URL.update(id) : BR_URL.store;

    fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `pernyataan=${encodeURIComponent(text)}`,
    })
      .then((res) => res.json())
      .then(() => {
        Swal.fire("Berhasil", "Data disimpan", "success");
        brModal.hide();
        loadTable();
      })
      .catch(() => {
        Swal.fire("Error", "Gagal menyimpan data", "error");
      });
  }

  if (e.target.id === "brBtnDelete") {
    const id = document.getElementById("brId").value;
    if (!id) return;

    Swal.fire({
      title: "Hapus data?",
      icon: "warning",
      showCancelButton: true,
    }).then((result) => {
      if (!result.isConfirmed) return;

      fetch(BR_URL.delete(id), { method: "POST" })
        .then(() => {
          Swal.fire("Berhasil", "Data dihapus", "success");
          brModal.hide();
          loadTable();
        })
        .catch(() => {
          Swal.fire("Error", "Gagal menghapus", "error");
        });
    });
  }

  if (e.target.id === "brBtnApprove") {
    const id = document.getElementById("brId").value;

    fetch(BR_URL.approve(id), { method: "POST" })
      .then(() => {
        Swal.fire("Berhasil", "Disetujui", "success");
        brModal.hide();
        loadTable();
      })
      .catch(() => {
        Swal.fire("Error", "Gagal approve", "error");
      });
  }

  if (e.target.id === "brBtnReject") {
    const id = document.getElementById("brId").value;

    Swal.fire({
      title: "Alasan Penolakan",
      input: "textarea",
      showCancelButton: true,
    }).then((result) => {
      if (!result.isConfirmed) return;

      fetch(BR_URL.reject(id), {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `notes=${encodeURIComponent(result.value || "")}`,
      })
        .then(() => {
          Swal.fire("Berhasil", "Ditolak", "success");
          brModal.hide();
          loadTable();
        })
        .catch(() => {
          Swal.fire("Error", "Gagal reject", "error");
        });
    });
  }
});
