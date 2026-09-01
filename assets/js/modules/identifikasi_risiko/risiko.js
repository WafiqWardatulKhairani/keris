const USER = window.APP_USER || {};
let IR_URL = {};
let irBankCache = [];
let irSuggestIndex = -1;

document.addEventListener("DOMContentLoaded", function () {
  IR_URL = {
    store: baseUrl + "identifikasi-risiko/store",
    update: (id) => baseUrl + `identifikasi-risiko/update/${id}`,
    delete: (id) => baseUrl + `identifikasi-risiko/delete/${id}`,
    detail: (id) => baseUrl + `identifikasi-risiko/detail/${id}`,
    detailArea: (id) => baseUrl + `identifikasi-risiko/detail-area/${id}`,
    bankRisiko: baseUrl + "identifikasi-risiko/bank-risiko",
    ajaxTable: baseUrl + "identifikasi-risiko/table",
  };

  irLoadBankRisiko();
  irBindRowClick();
  irInitAutocomplete();

  document.addEventListener("click", function (e) {
    if (!e.target.closest("#irBtnAdd")) return;

    const user = window.APP_USER || {};
    const konteks = window.APP_KONTEKS || null;

    // 🔍 DEBUG TARUH DI SINI
    console.log("USER:", user);
    console.log("KONTEKS:", konteks);

    const isOperator = user.role === "operator";

    if (!konteks || !konteks.id_konteks) {
      PkAlert.notAllowed({
        text: "Untuk menambah risiko, silakan pilih konteks terlebih dahulu melalui filter (Tim, Pengelola, Kegiatan, dan Tahun).",
      });
      return;
    }

    const bedaTim = String(user.id_tim) !== String(konteks.id_tim);

    if (isOperator && bedaTim) {
      PkAlert.notAllowed({
        text: "Tidak dapat menambahkan risiko milik tim lain.",
      });
      return;
    }

    irResetForm();
    bootstrap.Offcanvas.getOrCreateInstance(
      document.getElementById("offcanvasRisiko"),
    ).show();
  });

  document.getElementById("offcanvasRisiko")?.addEventListener("hidden.bs.offcanvas", irResetForm);
  enableAutoNumbering("irPenyebab");
  enableAutoNumbering("irDampak");
  
});

/* LOAD BANK RISIKO */
function irLoadBankRisiko() {
  PkAjax.get({
    url: IR_URL.bankRisiko,
    onSuccess(data) {
      irBankCache = data;
    },
  });
}

/* AUTOCOMPLETE */
function irInitAutocomplete() {
  const textarea = document.getElementById("irPernyataan");
  const dropdown = document.getElementById("irBankSuggest");
  if (!textarea || !dropdown) return;

  textarea.addEventListener("input", function () {
    const q = this.value.trim();
    irSuggestIndex = -1;
    q.length >= 2 ? irShowSuggest(q) : irHideSuggest();
  });

  textarea.addEventListener("keydown", function (e) {
    const items = dropdown.querySelectorAll(".ir-suggest-item");
    if (!items.length || dropdown.style.display === "none") return;

    if (e.key === "ArrowDown") {
      e.preventDefault();
      irSuggestIndex = Math.min(irSuggestIndex + 1, items.length - 1);
      irHighlight(items);
    } else if (e.key === "ArrowUp") {
      e.preventDefault();
      irSuggestIndex = Math.max(irSuggestIndex - 1, 0);
      irHighlight(items);
    } else if (e.key === "Enter" && irSuggestIndex >= 0) {
      e.preventDefault();
      irPickSuggest(items[irSuggestIndex].dataset.value, textarea);
    } else if (e.key === "Escape") {
      irHideSuggest();
    }
  });

  document.addEventListener("click", function (e) {
    if (!textarea.contains(e.target) && !dropdown.contains(e.target)) {
      irHideSuggest();
    }
  });
}

function irShowSuggest(q) {
  const dropdown = document.getElementById("irBankSuggest");
  if (!dropdown) return;

  const safeQ = q.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  const matched = irBankCache
    .filter((item) =>
      item.pernyataan_risiko.toLowerCase().includes(q.toLowerCase()),
    )
    .slice(0, 8);

  if (!matched.length) {
    dropdown.innerHTML = `<div class="ir-suggest-empty">Tidak ada rekomendasi yang cocok</div>`;
    dropdown.style.display = "block";
    return;
  }

  dropdown.innerHTML = matched
    .map((item) => {
      const highlighted = item.pernyataan_risiko.replace(
        new RegExp(`(${safeQ})`, "gi"),
        "<mark>$1</mark>",
      );
      const safeVal = item.pernyataan_risiko.replace(/"/g, "&quot;");
      return `<div class="ir-suggest-item" data-value="${safeVal}">${highlighted}</div>`;
    })
    .join("");

  dropdown.querySelectorAll(".ir-suggest-item").forEach((el) => {
    el.addEventListener("mousedown", function (e) {
      e.preventDefault();
      irPickSuggest(
        this.dataset.value,
        document.getElementById("irPernyataan"),
      );
    });
  });

  dropdown.style.display = "block";
}

function irPickSuggest(value, textarea) {
  textarea.value = value;
  textarea.setSelectionRange(value.length, value.length);
  textarea.focus();
  irHideSuggest();
  irSuggestIndex = -1;
}

function irHighlight(items) {
  items.forEach((el, i) => {
    el.classList.toggle("ir-suggest-active", i === irSuggestIndex);
    if (i === irSuggestIndex) el.scrollIntoView({ block: "nearest" });
  });
}

function irHideSuggest() {
  const el = document.getElementById("irBankSuggest");
  if (el) el.style.display = "none";
}

/* SET MODE: create | view | edit */
function irSetMode(mode) {
  const modeCreate = document.getElementById("irBtnCreate");
  const modeView = document.getElementById("irBtnView");
  const modeEdit = document.getElementById("irBtnEdit");

  const role = USER.role || "guest";

  // 1. RESET SEMUA
  [modeCreate, modeView, modeEdit].forEach((el) => el?.classList.add("d-none"));

  // 2. SHOW SESUAI MODE
  if (mode === "create") {
    modeCreate?.classList.remove("d-none");
  } else if (mode === "view") {
    modeView?.classList.remove("d-none");
  } else if (mode === "edit") {
    modeEdit?.classList.remove("d-none");
  }

  // 3. DISABLE FORM SAAT VIEW
  const isView = mode === "view";

  [
    "irKonteksProses",
    "irPernyataan",
    "irDampak",
    "irPenyebab",
    "irKategori",
  ].forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.disabled = isView;
  });

  document
    .querySelectorAll('input[name="sumber_risiko"]')
    .forEach((r) => (r.disabled = isView));

  const areaSelect = document.getElementById("irAreaDampak");
  if (areaSelect) areaSelect.disabled = isView;
}

/* RESET FORM */
function irResetForm() {
  const form = document.getElementById("irForm");
  if (!form) return;
  form.reset();
  form.classList.remove("was-validated");
  document.getElementById("irMode").value = "create";
  document.getElementById("irId").value = "";
  document.getElementById("irOffcanvasTitle").textContent = "Tambah Risiko";
  document.getElementById("irPenyebab").value = "";
  document.getElementById("irDampak").value = "";
  const area = document.getElementById("irAreaDampak");
  if (area) area.value = "";
  irHideSuggest();
  irSetMode("create");
}

/* BIND ROW CLICK */
function irBindRowClick() {
  document.querySelectorAll(".ir-row").forEach((row) => {
    row.addEventListener("click", function () {
      const data = JSON.parse(this.dataset.row);
      irLoadDetail(this.dataset.id, data);
    });
  });
}

/* LOAD DETAIL */
function irLoadDetail(id, row) {
  Promise.all([
    fetch(IR_URL.detail(id), {
      headers: { "X-Requested-With": "XMLHttpRequest" },
    }).then((r) => r.json()),
    fetch(IR_URL.detailArea(id), {
      headers: { "X-Requested-With": "XMLHttpRequest" },
    }).then((r) => r.json()),
  ])
    .then(([data, areas]) => {
      document.getElementById("irId").value = data.id_identifikasi;
      document.getElementById("irMode").value = "view";
      document.getElementById("irOffcanvasTitle").textContent = "Detail Risiko";

      const select = document.getElementById("irKonteksProses");

      if (!select.querySelector(`option[value="${data.id_konteks_proses}"]`)) {
        const opt = document.createElement("option");
        opt.value = data.id_konteks_proses;
        opt.text = `${data.kode_proses} — ${data.uraian_proses}`;
        select.appendChild(opt);
      }

      select.value = data.id_konteks_proses ?? "";
      document.getElementById("irPernyataan").value =data.pernyataan_risiko ?? "";
      document.getElementById("irDampak").value = data.dampak_risiko ?? "";
      document.getElementById("irPenyebab").value = data.penyebab_risiko ?? "";
      document.getElementById("irKategori").value = data.id_kategori_risiko ?? "";

      document.querySelectorAll('input[name="sumber_risiko"]').forEach((r) => {
        r.checked = r.value === data.sumber_risiko;
      });
      const areaSelect = document.getElementById("irAreaDampak");
      if (areaSelect) {
        areaSelect.value = areas[0] || "";
      }

      irHideSuggest();
      const currentUser = window.APP_USER || {};

      const isOperator = currentUser.role === "operator";
      const bedaTim = String(currentUser.id_tim) !== String(row.id_tim);
      const isKetua = currentUser.role === "ketua";

      const isReadonlyMode = isKetua || (isOperator && bedaTim);

      irSetMode("view");

      // APPLY READONLY MODE (COPY DARI KONTEKS)
      const btnDelete = document.getElementById("irBtnDelete");
      const btnEdit = document.getElementById("irBtnSwitchEdit");
      const btnClose = document.querySelector("#irBtnView button[data-bs-dismiss='offcanvas']");

      // DEFAULT
      if (btnDelete) btnDelete.style.display = "";
      if (btnEdit) btnEdit.style.display = "";
      if (btnClose) {
        btnClose.classList.remove("btn-secondary");
        btnClose.classList.add("btn-light");
      }

      // READONLY MODE
      if (isReadonlyMode) {
        if (btnDelete) btnDelete.style.display = "none";
        if (btnEdit) btnEdit.style.display = "none";

        if (btnClose) {
          btnClose.classList.remove("btn-light");
          btnClose.classList.add("btn-secondary");
        }
      }
      bootstrap.Offcanvas.getOrCreateInstance(
        document.getElementById("offcanvasRisiko"),
      ).show();
    })
    .catch(() => PkAlert.error({ text: "Gagal memuat detail risiko." }));
}

/* SWITCH TO EDIT */
document.addEventListener("click", function (e) {
  if (e.target?.id === "irBtnSwitchEdit") {
    document.getElementById("irMode").value = "edit";
    document.getElementById("irOffcanvasTitle").textContent = "Ubah Risiko";
    irSetMode("edit");
  }
});

/* CANCEL EDIT */
document.addEventListener("click", function (e) {
  if (e.target?.id === "irBtnCancelEdit") {
    const id = document.getElementById("irId").value;
    if (id) {
      const rowEl = document.querySelector(`.ir-row[data-id="${id}"]`);
      const row = rowEl ? JSON.parse(rowEl.dataset.row) : null;
      if (row) irLoadDetail(id, row);
    }
  }
});

/* REFRESH TABLE */
function irRefreshTable() {
  PkAjax.get({
    url: IR_URL.ajaxTable + window.location.search,
    onSuccess(html) {
      const wrapper = document.getElementById("irTableWrapper");
      if (wrapper) {
        wrapper.innerHTML = html;
        irBindRowClick();
      }
    },
  });
}

/* REFRESH CSRF TOKEN */
function irRefreshCsrf(res) {
  if (res && res.csrf_token) {
    window.csrfToken = res.csrf_token;
  }
}

/* SUBMIT FORM */
document.addEventListener("submit", function (e) {
  if (e.target?.id !== "irForm") return;
  e.preventDefault();

  const form = e.target;
  // Tambahkan CSRF token ke FormData
  const formData = new FormData(form);
  formData.append(csrfName, csrfToken);
  const mode = document.getElementById("irMode").value;
  const id = document.getElementById("irId").value;
  const isEdit = mode === "edit";

  // Validasi area dampak wajib dipilih 1
  const area = document.getElementById("irAreaDampak")?.value;

  if (!area) {
    PkAlert.toast({ text: "Area dampak wajib dipilih.", icon: "warning" });
    return;
  }

  if (!form.checkValidity()) {
    form.classList.add("was-validated");
    return;
  }

  PkAlert.confirm({
    title: isEdit ? "Ubah Risiko?" : "Simpan Risiko?",
    text: isEdit
      ? "Data identifikasi risiko akan diperbarui."
      : "Data identifikasi risiko akan disimpan.",
    icon: isEdit ? "warning" : "question",
    confirmText: isEdit ? "Ubah" : "Simpan",
  }).then((result) => {
    if (!result.isConfirmed) return;

    $.ajax({
      url: isEdit ? IR_URL.update(id) : IR_URL.store,
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      headers: { "X-Requested-With": "XMLHttpRequest" },
      success(res) {
        irRefreshCsrf(res);
        if (res.status === "success") {
          bootstrap.Offcanvas.getInstance(
            document.getElementById("offcanvasRisiko"),
          )?.hide();
          PkAlert.toast({
            text: isEdit
              ? "Risiko berhasil diperbarui."
              : "Risiko berhasil disimpan.",
            icon: "success",
          });
          irRefreshTable();
        } else {
          PkAlert.error({ text: res.message ?? "Terjadi kesalahan." });
        }
      },
      error() {
        PkAlert.error();
      },
    });
  });
});

/* DELETE */
document.addEventListener("click", function (e) {
  if (!e.target?.closest("#irBtnDelete")) return;

  const id = document.getElementById("irId").value;
  if (!id) return;

  PkAlert.warning({
    title: "Hapus Risiko?",
    text: "Data identifikasi risiko ini akan dihapus permanen.",
    confirmText: "Hapus",
  }).then((result) => {
    if (!result.isConfirmed) return;

    $.ajax({
      url: IR_URL.delete(id),
      method: "POST",
      data: { [csrfName]: csrfToken },
      processData: true,
      contentType: "application/x-www-form-urlencoded",
      headers: { "X-Requested-With": "XMLHttpRequest" },
      success(res) {
        irRefreshCsrf(res);
        if (res.status === "success") {
          bootstrap.Offcanvas.getInstance(
            document.getElementById("offcanvasRisiko"),
          )?.hide();
          PkAlert.toast({ text: "Risiko berhasil dihapus.", icon: "success" });
          irRefreshTable();
        } else {
          PkAlert.error({ text: res.message ?? "Gagal menghapus." });
        }
      },
      error() {
        PkAlert.error();
      },
    });
  });
});

document.addEventListener("click", function (e) {
  if (e.target?.id !== "irBtnRequest") return;

  const pernyataan = document.getElementById("irPernyataan").value.trim();

  if (!pernyataan) {
    PkAlert.toast({ text: "Pernyataan risiko wajib diisi.", icon: "warning" });
    return;
  }

  PkAlert.confirm({
    title: "Kirim ke Bank Risiko?",
    text: "Data akan dikirim sebagai request (pending).",
    icon: "question",
    confirmText: "Kirim",
  }).then((result) => {
    if (!result.isConfirmed) return;

    $.ajax({
      url: baseUrl + "identifikasi-risiko/request-bank-risiko",
      method: "POST",
      data: {
        pernyataan_risiko: pernyataan,
        [csrfName]: csrfToken,
      },
      success(res) {
        if (res.status === "success") {
          PkAlert.toast({
            text: "Berhasil dikirim ke Bank Risiko (pending approval).",
            icon: "success",
          });
        }
      },
      error() {
        PkAlert.error();
      },
    });
  });
});

function enableAutoNumbering(textareaId) {
  const el = document.getElementById(textareaId);
  if (!el) return;

  el.addEventListener("keydown", function (e) {
    if (e.key !== "Enter") return;

    e.preventDefault();

    let value = el.value.trim();
    let lines = value ? value.split("\n") : [];

    // 🔥 CASE 1: belum ada numbering sama sekali
    if (lines.length === 1 && !lines[0].match(/^\d+\./)) {
      el.value = `1. ${lines[0]}\n2. `;
    }
    // 🔥 CASE 2: sudah ada numbering
    else {
      const lastLine = lines[lines.length - 1];
      const match = lastLine.match(/^(\d+)\./);

      const nextNumber = match ? parseInt(match[1]) + 1 : lines.length + 1;

      el.value += `\n${nextNumber}. `;
    }

    setTimeout(() => {
      el.selectionStart = el.selectionEnd = el.value.length;
    }, 0);
  });
}