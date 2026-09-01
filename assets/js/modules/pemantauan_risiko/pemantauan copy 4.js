console.log("Pemantauan.js loaded");
let PR_CAN_EDIT = false;
const USER = window.APP_USER || {};
const PR_URL = window.PR_CONFIG?.url || {};
let prCsrfToken = window.PR_CONFIG?.csrf?.token || "";
const prCsrfName = window.PR_CONFIG?.csrf?.name || "csrf_token";

/* HELPER — format "YYYY-MM" / "YYYY-MM-DD" → "Bulan Tahun"
   [FIX #1] Gunakan locale id-ID agar nama bulan bahasa Indonesia */
function prFormatBulanTahun(val) {
  if (!val) return "-";
  // Ambil hanya YYYY-MM (abaikan hari jika ada)
  const str = String(val).substring(0, 7);
  const parts = str.split("-");
  if (parts.length < 2) return val;

  const year = parseInt(parts[0], 10);
  const month = parseInt(parts[1], 10);
  if (!year || !month || month < 1 || month > 12) return val;

  // Gunakan Intl agar otomatis bahasa Indonesia
  return new Date(year, month - 1, 1).toLocaleDateString("id-ID", {
    month: "long",
    year: "numeric",
  });
}

/* STATE — mode saat ini, untuk kontrol render bukti */
let prCurrentMode = "create";

/* MODE MANAGEMENT */
function prSetMode(mode) {
  prCurrentMode = mode;
  const isView = mode === "view";
  const isEdit = mode === "edit";
  const isCreate = mode === "create";

  document.getElementById("prMode").value = mode;

  // Disable/enable field form
  const disableAll = isView && !PR_CAN_EDIT;

  ["prRealisasiOutput", "prRealisasiWaktu", "prStatus", "prCatatan"].forEach(
    (id) => {
      const el = document.getElementById(id);
      if (el) el.disabled = isView;
    },
  );

  const linkInput = document.getElementById("prBuktiLinkInput");
  if (linkInput) {
    linkInput.disabled = disableAll;
    linkInput.classList.toggle("d-none", isView);
  }

  const catatanView = document.getElementById("prCatatanView");
  const catatanEdit = document.getElementById("prCatatan");
  if (catatanView && catatanEdit) {
    if (isView) {
      catatanView.textContent = catatanEdit.value || "-";
      catatanView.classList.remove("d-none");
      catatanEdit.classList.add("d-none");
    } else {
      catatanView.classList.add("d-none");
      catatanEdit.classList.remove("d-none");
    }
  }

  const toggle = (id, show) => {
    const el = document.getElementById(id);
    if (el) el.classList.toggle("d-none", !show);
  };

  toggle("prBtnEdit", isView && PR_CAN_EDIT);
  toggle("prBtnBatal", isEdit && PR_CAN_EDIT);
  toggle("prBtnSimpan", !isView && PR_CAN_EDIT);
  toggle("prBtnTutup", !isEdit);
  toggle("prBtnDelete", isView && PR_CAN_EDIT);

  const titleEl = document.getElementById("prOffcanvasTitle");
  if (titleEl) {
    titleEl.textContent = isCreate
      ? "Tambah Pemantauan"
      : isEdit
        ? "Edit Pemantauan"
        : "Detail Pemantauan";
  }

  // [FIX #2] Re-render bukti agar tombol ✕ muncul/hilang sesuai mode
  // Ambil bukti dari container yang sudah dirender sebelumnya
  prReRenderBuktiMode(isView);
}

function prRenderBuktiPreview(buktiList, showDelete = true) {
  const container = document.getElementById("prBuktiPreview");
  if (!container) return;

  if (!buktiList || buktiList.length === 0) {
    container.innerHTML =
      '<p class="text-muted small mb-0" style="font-size:11px">Belum ada bukti dukung</p>';
    return;
  }

  container.dataset.bukti = JSON.stringify(buktiList);

  container.innerHTML = buktiList
    .map((b) => {
      const link = b.url_link;
      let domain = link;
      try {
        domain = new URL(link).hostname;
      } catch {}
      const delBtn = showDelete
        ? `<button type="button" class="pr-bukti-del" onclick="prDeleteBukti(${b.id_bukti})" title="Hapus">✕</button>`
        : "";
      return `<div class="pr-bukti-card">
      <span class="pr-bukti-icon ti ti-link"></span>
      <a href="${link}" target="_blank" class="pr-bukti-url" title="${link}">${domain}</a>
      ${delBtn}
    </div>`;
    })
    .join("");
}

/* ======================================================
   RE-RENDER BUKTI SESUAI MODE (tanpa fetch ulang)
   Dipakai saat prSetMode dipanggil setelah preview sudah ada
====================================================== */
function prReRenderBuktiMode(isView) {
  const container = document.getElementById("prBuktiPreview");
  if (!container || !container.dataset.bukti) return;

  try {
    const buktiList = JSON.parse(container.dataset.bukti);
    prRenderBuktiPreview(buktiList, !isView); // showDelete = false saat view
  } catch (e) {
    // Abaikan jika data tidak valid
  }
}

document.addEventListener("DOMContentLoaded", function () {
  const outputEl = document.getElementById("prRealisasiOutput");

  if (outputEl) {
    //outputEl.addEventListener("input", prAutoSetStatus);
  }

  const input = document.getElementById("prBuktiLinkInput");
  const preview = document.getElementById("prBuktiPreview");

  if (!input || !preview) return;

  input.addEventListener("input", function () {
    if (prCurrentMode === "view") return;
    const val = this.value.trim();
    if (!val) {
      preview.innerHTML = "";
      return;
    }
    try {
      const u = new URL(val);
      preview.innerHTML = `<div class="pr-bukti-card">
        <span class="pr-bukti-icon ti ti-link"></span>
        <a href="${val}" target="_blank" class="pr-bukti-url" title="${val}">${u.hostname}</a>
      </div>`;
    } catch {
      preview.innerHTML =
        '<span class="text-danger" style="font-size:11px">Link tidak valid</span>';
    }
  });
});

/* ======================================================
   RESET FORM
====================================================== */
function prResetForm() {
  document.getElementById("prForm").reset();
  document.getElementById("prForm").classList.remove("was-validated");
  document.getElementById("prIdRtp").value = "";

  [
    "prInfoTahun",
    "prInfoTimKerja",
    "prInfoPengelola",
    "prInfoSasaran",
    "prInfoSasaranKinerja",
    "prInfoProses",
    "prInfoPernyataan",
    "prInfoPenyebab",
    "prInfoDampak",
    "prInfoPenanggungJawab",
    "prInfoRtp",
    "prInfoTargetOutput",
    "prInfoTargetWaktu",
  ].forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.textContent = "-";
  });

  const container = document.getElementById("prBuktiPreview");
  if (container) {
    delete container.dataset.bukti;
    container.innerHTML = '<p class="text-muted small mb-0">Belum ada link</p>';
  }

  const linkInput = document.getElementById("prBuktiLinkInput");
  if (linkInput) {
    linkInput.disabled = false;
    linkInput.value = "";
  }
}

/* POPULATE INFO */
function prPopulateInfo(d) {
  const set = (id, val) => {
    const el = document.getElementById(id);
    if (el) el.textContent = val || "-";
  };

  set("prInfoTahun", d.tahun);
  set("prInfoTimKerja", d.nama_tim);
  set("prInfoPengelola", d.nama_pengelola);
  set("prInfoSasaran", d.uraian_sasaran);

  set(
    "prInfoProses",
    (d.kode_proses ? d.kode_proses + " — " : "") + (d.uraian_proses ?? ""),
  );
  set("prInfoSasaranKinerja", d.uraian_sasaran_kinerja);
  set("prInfoPernyataan", d.pernyataan_risiko);
  set("prInfoPenyebab", d.penyebab_risiko);
  set("prInfoDampak", d.dampak_risiko);
  set("prInfoPenanggungJawab", "Ketua " + (d.nama_tim ?? "-"));
  set("prInfoRtp", d.uraian_rtp);
  set("prInfoTargetOutput", d.target_output);

  // [FIX #1] Format waktu dengan locale id-ID
  set("prInfoTargetWaktu", prFormatBulanTahun(d.target_waktu));

  const setVal = (id, val) => {
    const el = document.getElementById(id);
    if (el) el.value = val ?? "";
  };

  setVal("prRealisasiOutput", d.realisasi_output);
  prRenderStatusBadge(d.status);
  setVal("prCatatan", d.catatan);

  const rwEl = document.getElementById("prRealisasiWaktu");
  if (rwEl) {
    if (d.realisasi_waktu) {
      rwEl.value = String(d.realisasi_waktu).substring(0, 7);
    } else {
      const now = new Date();
      const year = now.getFullYear();
      const month = String(now.getMonth() + 1).padStart(2, "0");
      rwEl.value = `${year}-${month}`;
    }
  }

  // Render preview — showDelete akan di-override setelah prSetMode dipanggil
  // Sementara render dulu dengan showDelete = true, nanti prSetMode akan re-render
  prRenderBuktiPreview(d.bukti_list || [], true);
  prAutoSetStatus();
}

/* ======================================================
   LOAD DETAIL
====================================================== */
function prLoadDetail(idRtp) {
  return fetch(PR_URL.detail(idRtp))
    .then((r) => r.json())
    .then((d) => {
      console.log("USER", USER);
      console.log("DATA", d);
      PR_CAN_EDIT = prCanEdit(d);
      console.log("CAN EDIT?", prCanEdit(d));
      document.getElementById("prIdRtp").value = d.id_rtp;
      prPopulateInfo(d);
      return d;
    });
}

/* ======================================================
   OPEN ROW
====================================================== */
document.addEventListener("click", function (e) {
  const row = e.target.closest(".rtp-row");
  if (!row) return;

  const idRtp = row.dataset.rtp;
  if (!idRtp) return;

  prResetForm();

  bootstrap.Offcanvas.getOrCreateInstance(
    document.getElementById("prOffcanvas"),
  ).show();

  prLoadDetail(idRtp).then((d) => {
    const canEdit = prCanEdit(d);

    if (d.id_pemantauan) {
      prSetMode("view");
    } else {
      prSetMode("create");
    }

    // 🔥 kalau tidak boleh edit → paksa view
    if (!canEdit) {
      prSetMode("view");
    }
  });
});

/* ======================================================
   BATAL
====================================================== */
function prBatal() {
  const id = document.getElementById("prIdRtp").value;
  if (id) {
    prLoadDetail(id).then(() => prSetMode("view"));
  } else {
    bootstrap.Offcanvas.getInstance(
      document.getElementById("prOffcanvas"),
    ).hide();
  }
}

/* ======================================================
   SUBMIT
====================================================== */
document.getElementById("prForm")?.addEventListener("submit", function (e) {
  e.preventDefault();

  const form = e.target;
  const mode = document.getElementById("prMode").value;

  if (!form.checkValidity()) {
    form.classList.add("was-validated");
    return;
  }

  Swal.fire({
    title: mode === "edit" ? "Simpan Perubahan?" : "Simpan Pemantauan?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Simpan",
    cancelButtonText: "Batal",
    reverseButtons: true,
  }).then((result) => {
    if (!result.isConfirmed) return;

    const data = new FormData(form);
    data.append(prCsrfName, prCsrfToken);

    $.ajax({
      url: PR_URL.store,
      method: "POST",
      data,
      processData: false,
      contentType: false,

      success(res) {
        if (res.csrf_token) prCsrfToken = res.csrf_token;

        bootstrap.Offcanvas.getInstance(
          document.getElementById("prOffcanvas"),
        ).hide();

        Swal.fire({
          icon: "success",
          title: "Berhasil disimpan",
          timer: 1200,
          showConfirmButton: false,
        }).then(() => location.reload());
      },

      error(xhr) {
        Swal.fire({
          icon: "error",
          title: "Gagal",
          text: xhr.responseJSON?.message ?? "Terjadi kesalahan",
        });
      },
    });
  });
});

/* ======================================================
   DELETE PEMANTAUAN
====================================================== */
function prHapus() {
  const id = document.getElementById("prIdRtp").value;
  if (!id) return;

  Swal.fire({
    title: "Hapus pemantauan ini?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, Hapus",
    cancelButtonText: "Batal",
  }).then((result) => {
    if (!result.isConfirmed) return;

    $.ajax({
      url: PR_URL.delete(id),
      method: "POST",
      data: { [prCsrfName]: prCsrfToken },
      success(res) {
        if (res.csrf_token) prCsrfToken = res.csrf_token;
        bootstrap.Offcanvas.getInstance(
          document.getElementById("prOffcanvas"),
        ).hide();
        location.reload();
      },
    });
  });
}

function prDeleteBukti(id) {
  Swal.fire({
    title: "Hapus link ini?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, Hapus",
    cancelButtonText: "Batal",
  }).then((result) => {
    if (!result.isConfirmed) return;

    $.ajax({
      url: PR_URL.deleteBukti(id),
      method: "DELETE",
      data: { [prCsrfName]: prCsrfToken },
      success(res) {
        if (res.csrf_token) prCsrfToken = res.csrf_token;

        // Hapus data bukti dari container dan reset tampilan
        const container = document.getElementById("prBuktiPreview");
        if (container) {
          delete container.dataset.bukti;
          container.innerHTML =
            '<p class="text-muted small mb-0">Belum ada link</p>';
        }
      },
      error(xhr) {
        Swal.fire({
          icon: "error",
          title: "Gagal menghapus link",
          text: xhr.responseJSON?.message ?? "Terjadi kesalahan",
        });
      },
    });
  });
}

function prRenderStatusBadge(status) {
  const el = document.getElementById("prStatusBadge");
  if (!el) return;

  let cls = "secondary";

  if (status === "Selesai") cls = "success";
  else if (status === "Dalam Proses") cls = "primary";
  else if (status === "Terlambat") cls = "danger";

  el.className = `badge bg-${cls}-subtle text-${cls} border border-${cls}`;
  el.textContent = status || "-";
}

function prAutoSetStatus(data) {
  const statusEl = document.getElementById("prStatus");
  if (!statusEl) return;

  statusEl.value = data.status || "Terlambat";
}

function prCanEdit(data) {
  if (!USER) return false;

  if (USER.role === "admin") return true;
  if (USER.role === "ketua") return false; 

  if (USER.role === "operator") {
    return String(USER.id_tim) === String(data.id_tim);
  }

  return false;
}


