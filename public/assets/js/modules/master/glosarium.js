document.addEventListener("DOMContentLoaded", () => {
  const config = window.GLOSARIUM_CONFIG;

  if (!config) {
    console.error("Konfigurasi Glosarium tidak ditemukan.");
    return;
  }

  const isAdmin = config.role === "admin";

  const elements = {
    tbody: document.getElementById("glosariumTableBody"),
    search: document.getElementById("glosariumSearch"),
    clear: document.getElementById("glosariumSearchClear"),
    perPage: document.getElementById("glosariumPerPage"),
    info: document.getElementById("glosariumInfo"),
    pagination: document.getElementById("glosariumPagination"),
    add: document.getElementById("btnTambahGlosarium"),
    form: document.getElementById("glosariumForm"),
    title: document.getElementById("glosariumTitle"),
    mode: document.getElementById("glosariumMode"),
    id: document.getElementById("glosariumId"),
    istilah: document.getElementById("glosariumIstilah"),
    definisi: document.getElementById("glosariumDefinisi"),
    edit: document.getElementById("glosariumBtnEdit"),
    cancel: document.getElementById("glosariumBtnCancel"),
    save: document.getElementById("glosariumBtnSave"),
    remove: document.getElementById("glosariumBtnDelete"),
    close: document.getElementById("glosariumBtnClose"),
  };

  if (!elements.tbody || !elements.form) {
    console.error("Elemen halaman Glosarium tidak lengkap.");
    return;
  }

  const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(elements.form);

  let allData = [];
  let currentPage = 1;
  let currentMode = "view";

  const escapeHtml = (value) =>
    String(value ?? "").replace(
      /[&<>"']/g,
      (char) =>
        ({
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          '"': "&quot;",
          "'": "&#039;",
        })[char],
    );

  const notify = (message, icon = "info") => {
    if (window.Swal) {
      return Swal.fire({
        text: message,
        icon,
        confirmButtonText: "OK",
      });
    }

    alert(message);
    return Promise.resolve();
  };

  const confirmDelete = async () => {
    if (window.Swal) {
      const result = await Swal.fire({
        title: "Hapus istilah?",
        text: "Data yang dihapus tidak dapat dikembalikan.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, hapus",
        cancelButtonText: "Batal",
        confirmButtonColor: "#dc3545",
      });

      return result.isConfirmed;
    }

    return confirm("Yakin ingin menghapus istilah ini?");
  };

  const setLoading = (message) => {
    elements.tbody.innerHTML = `
            <tr>
                <td colspan="3" class="text-center py-4 text-muted">
                    ${escapeHtml(message)}
                </td>
            </tr>`;
  };

  const getFilteredData = () => {
    const keyword = elements.search.value.trim().toLocaleLowerCase("id-ID");

    return allData.filter((item) => {
      const istilah = String(item.istilah ?? "").toLocaleLowerCase("id-ID");
      const definisi = String(item.definisi ?? "").toLocaleLowerCase("id-ID");

      return istilah.includes(keyword) || definisi.includes(keyword);
    });
  };

  const renderTable = () => {
    const filtered = getFilteredData();
    const perPage = Number(elements.perPage.value) || 10;
    const total = filtered.length;
    const totalPages = Math.ceil(total / perPage);

    currentPage = totalPages ? Math.min(currentPage, totalPages) : 1;

    const startIndex = (currentPage - 1) * perPage;
    const pageData = filtered.slice(startIndex, startIndex + perPage);

    if (!pageData.length) {
      elements.tbody.innerHTML = `
                <tr>
                    <td colspan="3" class="text-center py-4 text-muted">
                        ${total ? "Tidak ada data pada halaman ini." : "Data glosarium belum tersedia."}
                    </td>
                </tr>`;
    } else {
      elements.tbody.innerHTML = pageData
        .map(
          (item, index) => `
                <tr data-id="${Number(item.id_glosarium)}"
                    style="cursor:pointer">
                    <td>${startIndex + index + 1}</td>
                    <td class="fw-semibold">
                        ${escapeHtml(item.istilah)}
                    </td>
                    <td>
                        <div style="white-space:normal; overflow-wrap:anywhere">
                            ${escapeHtml(item.definisi)}
                        </div>
                    </td>
                </tr>
            `,
        )
        .join("");
    }

    elements.info.textContent = total
      ? `Menampilkan ${startIndex + 1}–${Math.min(startIndex + perPage, total)} dari ${total} data`
      : "Menampilkan 0 data";

    elements.clear.classList.toggle("d-none", !elements.search.value);

    renderPagination(totalPages);
  };

  const renderPagination = (totalPages) => {
    if (!totalPages) {
      elements.pagination.innerHTML = "";
      return;
    }

    const pages = new Set();

    // Selalu tampilkan halaman pertama dan terakhir.
    pages.add(1);
    pages.add(totalPages);

    // Tampilkan halaman di sekitar halaman aktif.
    for (
      let page = Math.max(1, currentPage - 1);
      page <= Math.min(totalPages, currentPage + 1);
      page++
    ) {
      pages.add(page);
    }

    const sortedPages = [...pages].sort((a, b) => a - b);
    let html = `
        <li class="page-item ${currentPage === 1 ? "disabled" : ""}">
            <button type="button" class="page-link"
                data-page="${currentPage - 1}"
                ${currentPage === 1 ? "disabled" : ""}>
                &laquo;
            </button>
        </li>`;

    let previousPage = 0;

    sortedPages.forEach((page) => {
      if (previousPage && page - previousPage > 1) {
        html += `
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>`;
      }

      html += `
            <li class="page-item ${page === currentPage ? "active" : ""}">
                <button type="button" class="page-link" data-page="${page}">
                    ${page}
                </button>
            </li>`;

      previousPage = page;
    });

    html += `
        <li class="page-item ${currentPage === totalPages ? "disabled" : ""}">
            <button type="button" class="page-link"
                data-page="${currentPage + 1}"
                ${currentPage === totalPages ? "disabled" : ""}>
                &raquo;
            </button>
        </li>`;

    elements.pagination.innerHTML = html;
  };

  const loadData = async () => {
    setLoading("Memuat data glosarium...");

    try {
      const response = await fetch(config.url.table, {
        headers: { Accept: "application/json" },
      });

      if (!response.ok) {
        throw new Error(`Gagal mengambil data (${response.status}).`);
      }

      const data = await response.json();

      if (!Array.isArray(data)) {
        throw new Error("Format data dari server tidak sesuai.");
      }

      allData = data;
      renderTable();
    } catch (error) {
      console.error(error);
      setLoading("Gagal memuat data. Silakan muat ulang halaman.");
      await notify(error.message, "error");
    }
  };

  const setReadOnly = (readOnly) => {
    elements.istilah.readOnly = readOnly;
    elements.definisi.readOnly = readOnly;
  };

  const openForm = (mode, item = null) => {
    currentMode = mode;
    elements.mode.value = mode;
    elements.id.value = item?.id_glosarium ?? "";
    elements.istilah.value = item?.istilah ?? "";
    elements.definisi.value = item?.definisi ?? "";

    setReadOnly(mode === "view");

    elements.title.textContent = {
      view: "Detail Istilah",
      create: "Tambah Istilah",
      edit: "Edit Istilah",
    }[mode];

    elements.add && (elements.add.disabled = false);

    elements.edit.classList.toggle("d-none", !isAdmin || mode !== "view");

    elements.remove.classList.toggle("d-none", !isAdmin || mode !== "view");

    elements.save.classList.toggle("d-none", !isAdmin || mode === "view");

    elements.cancel.classList.toggle("d-none", mode === "view");

    offcanvas.show();
  };

  const postData = async (url, payload = {}) => {
    const body = new URLSearchParams();

    Object.entries(payload).forEach(([key, value]) => {
      body.append(key, value);
    });

    const response = await fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        Accept: "application/json",
      },
      body,
    });

    const result = await response.json();

    if (!response.ok || result.status !== true) {
      throw new Error(result.message || "Permintaan gagal diproses.");
    }

    return result;
  };

  elements.add?.addEventListener("click", () => {
    if (isAdmin) openForm("create");
  });

  elements.tbody.addEventListener("click", (event) => {
    const row = event.target.closest("tr[data-id]");
    if (!row) return;

    const item = allData.find(
      (data) => Number(data.id_glosarium) === Number(row.dataset.id),
    );

    if (item) openForm("view", item);
  });

  elements.edit.addEventListener("click", () => {
    if (!isAdmin) return;

    const item = allData.find(
      (data) => Number(data.id_glosarium) === Number(elements.id.value),
    );

    if (item) openForm("edit", item);
  });

  elements.cancel.addEventListener("click", () => {
    const item = allData.find(
      (data) => Number(data.id_glosarium) === Number(elements.id.value),
    );

    if (currentMode === "edit" && item) {
      openForm("view", item);
    } else {
      offcanvas.hide();
    }
  });

  elements.close.addEventListener("click", () => offcanvas.hide());

  elements.save.addEventListener("click", async () => {
    if (!isAdmin) return;

    const istilah = elements.istilah.value.trim();
    const definisi = elements.definisi.value.trim();

    if (!istilah || !definisi) {
      await notify("Istilah dan definisi wajib diisi.", "warning");
      return;
    }

    const isEdit = currentMode === "edit";
    const url = isEdit
      ? config.url.update(elements.id.value)
      : config.url.store;

    elements.save.disabled = true;

    try {
      await postData(url, { istilah, definisi });

      offcanvas.hide();
      await loadData();
      await notify(
        isEdit
          ? "Istilah berhasil diperbarui."
          : "Istilah berhasil ditambahkan.",
        "success",
      );
    } catch (error) {
      console.error(error);
      await notify(error.message, "error");
    } finally {
      elements.save.disabled = false;
    }
  });

  elements.remove.addEventListener("click", async () => {
    if (!isAdmin) return;

    const id = elements.id.value;
    if (!id || !(await confirmDelete())) return;

    elements.remove.disabled = true;

    try {
      await postData(config.url.delete(id));
      offcanvas.hide();
      await loadData();
      await notify("Istilah berhasil dihapus.", "success");
    } catch (error) {
      console.error(error);
      await notify(error.message, "error");
    } finally {
      elements.remove.disabled = false;
    }
  });

  elements.search.addEventListener("input", () => {
    currentPage = 1;
    renderTable();
  });

  elements.clear.addEventListener("click", () => {
    elements.search.value = "";
    currentPage = 1;
    renderTable();
    elements.search.focus();
  });

  elements.perPage.addEventListener("change", () => {
    currentPage = 1;
    renderTable();
  });

  elements.pagination.addEventListener("click", (event) => {
    const button = event.target.closest("button[data-page]");
    if (!button || button.disabled) return;

    currentPage = Number(button.dataset.page);
    renderTable();
  });

  loadData();
});
