/**
 * KERIS - Analisis SWOT dan PESTLE
 *
 * Fitur:
 * - Memuat jumlah poin per kategori.
 * - Menampilkan detail kategori dalam modal.
 * - CRUD poin analisis.
 * - Membuka dan mengunci kembali periode oleh admin.
 *
 * Endpoint mengikuti grup penetapan-konteks.
 */
(() => {
  "use strict";

  const root = document.getElementById("swotPestleSection");

  if (!root) return;

  const idKonteks = Number(root.dataset.idKonteks);
  const tahun = Number(root.dataset.tahun);
  const canManage = root.dataset.canManage === "1";
  const role = window.APP_USER?.role ?? "";

  const triwulanInput = document.getElementById("swotPestleTriwulan");
  const periodInfo = document.getElementById("swotPestlePeriodInfo");

  const BASE = `${window.BASE_URL ?? window.location.origin}/penetapan-konteks/analisis-swot-pestle`;

  const categories = {
    SWOT: {
      S: "Strengths — Kekuatan",
      W: "Weaknesses — Kelemahan",
      O: "Opportunities — Peluang",
      T: "Threats — Ancaman",
    },
    PESTLE: {
      P: "Political — Politik",
      E: "Economic — Ekonomi",
      S: "Social — Sosial",
      T: "Technological — Teknologi",
      L: "Legal — Hukum",
      ENV: "Environmental — Lingkungan",
    },
  };

  let activeCategory = null;
  let modal = null;
  let currentEditable = false;

  let currentPeriodStatus = {
    sudah_berakhir: false,
    dibuka_kembali: false,
    terkunci: false,
  };

  function currentQuarter() {
    return Number(triwulanInput?.value || 1);
  }

  function quarterLabel(quarter) {
    return (
      {
        1: "Triwulan I (Jan–Mar)",
        2: "Triwulan II (Apr–Jun)",
        3: "Triwulan III (Jul–Sep)",
        4: "Triwulan IV (Okt–Des)",
      }[quarter] || `Triwulan ${quarter}`
    );
  }

  function requestHeaders() {
    const headers = {
      "X-Requested-With": "XMLHttpRequest",
    };

    // Mendukung CSRF CodeIgniter apabila diaktifkan.
    const csrfName = document.querySelector('meta[name="csrf-name"]')?.content;
    const csrfToken = document.querySelector(
      'meta[name="csrf-token"]',
    )?.content;

    if (csrfName && csrfToken) {
      headers[csrfName] = csrfToken;
    }

    return headers;
  }

  async function request(url, options = {}) {
    let response;

    try {
      response = await fetch(url, {
        ...options,
        headers: {
          ...requestHeaders(),
          ...(options.headers || {}),
        },
      });
    } catch (error) {
      throw new Error(
        "Tidak dapat terhubung ke server. Periksa koneksi atau coba kembali.",
      );
    }

    let result;

    try {
      result = await response.json();
    } catch {
      result = {};
    }

    if (!response.ok || result.status === "error") {
      // Pesan ramah untuk pengguna.
      const messages = {
        400: "Permintaan tidak valid. Periksa kembali data yang dipilih.",
        401: "Sesi login kamu sudah berakhir. Silakan login kembali.",
        403: "Kamu tidak memiliki akses untuk melihat data ini.",
        404: "Data atau alamat fitur tidak ditemukan.",
        422: "Data belum valid. Periksa kembali isian kamu.",
        423: "Periode ini sudah terkunci dan hanya dapat dilihat.",
        429: "Terlalu banyak permintaan. Tunggu sebentar lalu coba lagi.",
        500: "Terjadi kendala pada server saat memuat analisis. Silakan coba lagi. Jika masih terjadi, hubungi administrator.",
        503: "Layanan sedang tidak tersedia. Silakan coba beberapa saat lagi.",
      };

      throw new Error(
        result.message ||
          messages[response.status] ||
          "Terjadi kendala saat memproses permintaan. Silakan coba kembali.",
      );
    }

    return result;
  }

  function post(url, values) {
    const body = new URLSearchParams();

    Object.entries(values).forEach(([key, value]) => {
      body.append(key, value ?? "");
    });

    return request(url, {
      method: "POST",
      body,
      headers: {
        "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
      },
    });
  }

  function showMessage(message) {
    if (window.Swal) {
      return Swal.fire({
        text: message,
        icon: "info",
        confirmButtonText: "OK",
      });
    }

    alert(message);
    return Promise.resolve();
  }

  function showError(error) {
    console.error("[SWOT/PESTLE]", error);

    if (window.Swal) {
      Swal.fire({
        icon: "error",
        title: "Oops, ada kendala!",
        text:
          error.message || "Data belum berhasil dimuat. Silakan coba kembali.",
        confirmButtonText: "Mengerti",
      });
      return;
    }

    showMessage(
      error.message || "Data belum berhasil dimuat. Silakan coba kembali.",
    );
  }

  function ensureModal() {
    if (modal) return modal;

    const element = document.createElement("div");
    element.className = "modal fade";
    element.id = "swotPestleModal";
    element.tabIndex = -1;
    element.setAttribute("aria-hidden", "true");

    element.innerHTML = `
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="swotPestleModalTitle">
                                Analisis
                            </h5>
                            <small class="text-muted" id="swotPestleModalPeriod"></small>
                        </div>
                        <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Tutup"></button>
                    </div>

                    <div class="modal-body">
                        <div id="swotPestleModalMessage"
                            class="alert alert-info">
                            Memuat data...
                        </div>

                        <div id="swotPestleItems"></div>

                        <div id="swotPestleEditor"
                            class="border rounded p-3 mt-3"
                            hidden>
                            <label for="swotPestleUraian"
                                class="form-label fw-semibold">
                                Uraian analisis
                            </label>

                            <textarea
                                id="swotPestleUraian"
                                class="form-control"
                                rows="3"
                                maxlength="10000"
                                placeholder="Tuliskan poin analisis..."></textarea>

                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <button type="button"
                                    class="btn btn-light"
                                    id="swotPestleCancelEdit">
                                    Batal
                                </button>

                                <button type="button"
                                    class="btn btn-primary"
                                    id="swotPestleSave">
                                    Simpan
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                            Tutup
                        </button>

                        <button type="button"
                            class="btn btn-primary"
                            id="swotPestleAdd">
                            <i class="ti ti-plus"></i> Tambah Poin
                        </button>
                    </div>
                </div>
            </div>`;

    document.body.appendChild(element);

    if (!window.bootstrap?.Modal) {
      throw new Error("Bootstrap Modal belum tersedia pada halaman ini.");
    }

    modal = {
      element,
      instance: bootstrap.Modal.getOrCreateInstance(element),
      title: element.querySelector("#swotPestleModalTitle"),
      period: element.querySelector("#swotPestleModalPeriod"),
      message: element.querySelector("#swotPestleModalMessage"),
      items: element.querySelector("#swotPestleItems"),
      editor: element.querySelector("#swotPestleEditor"),
      textarea: element.querySelector("#swotPestleUraian"),
      add: element.querySelector("#swotPestleAdd"),
      save: element.querySelector("#swotPestleSave"),
      cancel: element.querySelector("#swotPestleCancelEdit"),
    };

    modal.add.addEventListener("click", () => openEditor(null));

    modal.cancel.addEventListener("click", () => closeEditor());

    modal.save.addEventListener("click", saveItem);

    return modal;
  }

  function closeEditor() {
    if (!modal) return;

    modal.editor.hidden = true;
    modal.textarea.value = "";
    modal.save.dataset.id = "";
  }

  function openEditor(item) {
    if (!canManage || !currentEditable) return;

    modal.editor.hidden = false;
    modal.textarea.value = item?.uraian ?? "";
    modal.save.dataset.id = item?.id_analisis ?? "";
    modal.textarea.focus();
  }

  function setMessage(message, type = "info") {
    modal.message.className = `alert alert-${type}`;
    modal.message.textContent = message;
    modal.message.hidden = !message;
  }

  async function loadCategoryCounts() {
    const quarter = currentQuarter();

    const tasks = [];

    Object.entries(categories).forEach(([jenis, items]) => {
      Object.keys(items).forEach((kategori) => {
        const countElement = document.getElementById(
          `count-${jenis}-${kategori}`,
        );

        if (!countElement) return;

        countElement.textContent = "Memuat...";

        const query = new URLSearchParams({
          id_konteks: idKonteks,
          jenis,
          kategori,
          tahun,
          triwulan: quarter,
        });

        tasks.push(
          request(`${BASE}/list?${query}`)
            .then((result) => {
              countElement.textContent = `${(result.data || []).length} poin analisis`;

              // Perbarui status periode dari respons server.
              if (result.periode?.status_periode) {
                currentPeriodStatus = result.periode.status_periode;
                updatePeriodButton();
              }
            })
            .catch(() => {
              countElement.textContent = "Gagal memuat";
            }),
        );
      });
    });

    await Promise.all(tasks);
  }

  async function openCategory(jenis, kategori) {
    activeCategory = { jenis, kategori };

    const ui = ensureModal();

    ui.title.textContent = `${kategori} — ${categories[jenis][kategori]}`;
    ui.period.textContent = `${tahun} · ${quarterLabel(currentQuarter())}`;

    ui.items.innerHTML = "";
    ui.editor.hidden = true;
    ui.add.hidden = !canManage;
    ui.add.disabled = true;
    ui.save.disabled = true;

    setMessage("Memuat data...");

    ui.instance.show();

    try {
      const query = new URLSearchParams({
        id_konteks: idKonteks,
        jenis,
        kategori,
        tahun,
        triwulan: currentQuarter(),
      });

      const result = await request(`${BASE}/list?${query}`);
      currentPeriodStatus = result.periode?.status_periode ?? {
        sudah_berakhir: false,
        dibuka_kembali: false,
        terkunci: false,
      };

      updatePeriodButton();

      currentEditable = Boolean(result.periode?.editable);

      ui.add.hidden = !canManage || !currentEditable;
      ui.add.disabled = !canManage || !currentEditable;
      ui.save.disabled = !canManage || !currentEditable;

      if (!currentEditable) {
        setMessage("Periode ini terkunci atau hanya dapat dilihat.", "warning");
      } else if (!result.data?.length) {
        setMessage("Belum ada poin analisis pada kategori ini.");
      } else {
        setMessage("");
      }

      renderItems(result.data || []);
    } catch (error) {
      currentEditable = false;
      setMessage(error.message, "danger");
    }
  }

  function updatePeriodButton() {
    const button = document.getElementById("btnTogglePeriode");
    const statusText = document.getElementById("swotPestleStatusPeriode");

    if (!button) return;

    const { sudah_berakhir, dibuka_kembali, terkunci } = currentPeriodStatus;

    // Periode yang masih berjalan tidak membutuhkan tombol.
    button.hidden = !sudah_berakhir;

    if (terkunci) {
      button.textContent = "Buka Kembali";
      button.className = "btn btn-sm btn-outline-warning";
      statusText.textContent = "Periode telah berakhir dan terkunci.";
    } else if (dibuka_kembali) {
      button.textContent = "Kunci Periode";
      button.className = "btn btn-sm btn-outline-secondary";
      statusText.textContent = "Periode dibuka kembali oleh admin.";
    } else {
      statusText.textContent = sudah_berakhir
        ? "Periode telah berakhir."
        : "Periode masih berjalan.";
    }
  }

  function renderItems(items) {
    modal.items.innerHTML = "";

    if (!items.length) return;

    items.forEach((item, index) => {
      const card = document.createElement("div");
      card.className = "border rounded p-3 mb-2";

      const row = document.createElement("div");
      row.className = "d-flex justify-content-between align-items-start gap-3";

      const content = document.createElement("div");
      content.className = "flex-grow-1";

      const number = document.createElement("div");
      number.className = "text-muted small mb-1";
      number.textContent = `Poin ${index + 1}`;

      const description = document.createElement("div");
      description.style.whiteSpace = "pre-wrap";
      description.textContent = item.uraian;

      content.append(number, description);
      row.appendChild(content);

      if (canManage && currentEditable) {
        const actions = document.createElement("div");
        actions.className = "d-flex gap-1 flex-shrink-0";

        const edit = document.createElement("button");
        edit.type = "button";
        edit.className = "btn btn-sm btn-outline-primary";
        edit.textContent = "Edit";
        edit.addEventListener("click", () => openEditor(item));

        const remove = document.createElement("button");
        remove.type = "button";
        remove.className = "btn btn-sm btn-outline-danger";
        remove.textContent = "Hapus";
        remove.addEventListener("click", () => deleteItem(item));

        actions.append(edit, remove);
        row.appendChild(actions);
      }

      card.appendChild(row);
      modal.items.appendChild(card);
    });
  }

  async function saveItem() {
    if (!activeCategory || !canManage || !currentEditable) return;

    const uraian = modal.textarea.value.trim();

    if (!uraian) {
      await showMessage("Uraian analisis wajib diisi.");
      return;
    }

    const id = modal.save.dataset.id;
    const values = {
      id_konteks: idKonteks,
      jenis_analisis: activeCategory.jenis,
      kategori: activeCategory.kategori,
      tahun,
      triwulan: currentQuarter(),
      uraian,
    };

    modal.save.disabled = true;

    try {
      if (id) {
        await post(`${BASE}/update/${encodeURIComponent(id)}`, values);
      } else {
        await post(`${BASE}/store`, values);
      }

      closeEditor();

      await openCategory(activeCategory.jenis, activeCategory.kategori);

      await loadCategoryCounts();
    } catch (error) {
      showError(error);
    } finally {
      modal.save.disabled = false;
    }
  }

  async function deleteItem(item) {
    if (!canManage || !currentEditable) return;

    let confirmed = false;

    if (window.Swal) {
      const result = await Swal.fire({
        title: "Hapus poin analisis?",
        text: "Data yang dihapus tidak dapat dikembalikan.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, hapus",
        cancelButtonText: "Batal",
      });

      confirmed = result.isConfirmed;
    } else {
      confirmed = confirm("Yakin ingin menghapus poin analisis ini?");
    }

    if (!confirmed) return;

    try {
      await post(`${BASE}/delete/${encodeURIComponent(item.id_analisis)}`, {
        tahun,
        triwulan: currentQuarter(),
      });

      await openCategory(activeCategory.jenis, activeCategory.kategori);

      await loadCategoryCounts();
    } catch (error) {
      showError(error);
    }
  }

  async function managePeriod(action) {
    if (role !== "admin") return;

    const quarter = currentQuarter();
    let alasan = "";

    if (action === "buka-periode") {
      if (window.Swal) {
        const result = await Swal.fire({
          title: "Buka kembali periode?",
          input: "textarea",
          inputLabel: "Alasan pembukaan",
          inputPlaceholder: "Tuliskan alasan pembukaan periode...",
          inputValidator: (value) => {
            if (!value?.trim()) {
              return "Alasan wajib diisi.";
            }
          },
          showCancelButton: true,
          confirmButtonText: "Buka periode",
          cancelButtonText: "Batal",
        });

        if (!result.isConfirmed) return;
        alasan = result.value.trim();
      } else {
        alasan = prompt("Alasan membuka kembali periode:")?.trim() || "";
        if (!alasan) return;
      }
    } else {
      let confirmed = false;

      if (window.Swal) {
        const result = await Swal.fire({
          title: "Kunci kembali periode?",
          text: "Jika periode telah berakhir, data akan menjadi read-only.",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Kunci periode",
          cancelButtonText: "Batal",
        });

        confirmed = result.isConfirmed;
      } else {
        confirmed = confirm("Kunci kembali periode ini?");
      }

      if (!confirmed) return;
    }

    try {
      const result = await post(`${BASE}/${action}`, {
        id_konteks: idKonteks,
        tahun,
        triwulan: quarter,
        alasan_buka: alasan,
      });

      // Perbarui status tombol setelah server menyatakan berhasil.
      if (action === "tutup-periode") {
        currentPeriodStatus = {
          ...currentPeriodStatus,
          sudah_berakhir: true,
          dibuka_kembali: false,
          terkunci: true,
        };
      } else {
        currentPeriodStatus = {
          ...currentPeriodStatus,
          sudah_berakhir: true,
          dibuka_kembali: true,
          terkunci: false,
        };
      }

      updatePeriodButton();

      await showMessage(
        result.message || "Status periode berhasil diperbarui.",
      );

      await loadCategoryCounts();

      if (activeCategory) {
        await openCategory(activeCategory.jenis, activeCategory.kategori);
      }
    } catch (error) {
      showError(error);
    }
  }

  document.querySelectorAll(".swot-pestle-category").forEach((button) => {
    button.addEventListener("click", () => {
      openCategory(button.dataset.jenis, button.dataset.kategori).catch(
        showError,
      );
    });
  });

  document.getElementById("btnMuatAnalisis")?.addEventListener("click", () => {
    loadCategoryCounts().catch(showError);
  });

  triwulanInput?.addEventListener("change", () => {
    loadCategoryCounts().catch(showError);

    if (activeCategory && modal?.element.classList.contains("show")) {
      openCategory(activeCategory.jenis, activeCategory.kategori).catch(
        showError,
      );
    }
  });

  document.getElementById("btnTogglePeriode")?.addEventListener("click", () => {
    const action = currentPeriodStatus.terkunci
      ? "buka-periode"
      : "tutup-periode";

    managePeriod(action).catch(showError);
  });

  loadCategoryCounts().catch(showError);
})();
