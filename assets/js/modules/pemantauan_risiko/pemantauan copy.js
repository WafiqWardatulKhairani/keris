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
  ["prRealisasiOutput", "prRealisasiWaktu", "prStatus", "prCatatan"].forEach(
    (id) => {
      const el = document.getElementById(id);
      if (el) el.disabled = isView;
    },
  );

  // Input file: sembunyikan di view mode
  const fileInput = document.getElementById("prBuktiFileInput");
  if (fileInput) {
    fileInput.disabled = isView;
    fileInput.style.display = isView ? "none" : "";
  }

  const toggle = (id, show) => {
    const el = document.getElementById(id);
    if (el) el.classList.toggle("d-none", !show);
  };

  toggle("prBtnEdit", isView);
  toggle("prBtnBatal", isEdit);
  toggle("prBtnSimpan", !isView);
  toggle("prBtnTutup", !isEdit);
  toggle("prBtnDelete", isView);

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

/* RENDER BUKTI PREVIEW
   [FIX #2] showDelete dikontrol dari mode (false = view)
   [FIX #4] Tombol Lihat & Unduh dibuat lebih kecil/simple */
function prRenderBuktiPreview(buktiList, showDelete = true) {
  const container = document.getElementById("prBuktiPreview");
  if (!container) return;

  if (!buktiList || buktiList.length === 0) {
    container.innerHTML = '<p class="text-muted small mb-0">Belum ada file</p>';
    return;
  }

  // Simpan data bukti di dataset container untuk keperluan re-render
  container.dataset.bukti = JSON.stringify(buktiList);

  container.innerHTML = buktiList
    .map((b) => {
      const viewUrl = PR_URL.viewBukti(b.id_bukti);
      const downloadUrl = PR_URL.downloadBukti(b.id_bukti);
      const ext = b.nama_file.split(".").pop().toLowerCase();
      const isImage = ["jpg", "jpeg", "png"].includes(ext);
      const isPdf = ext === "pdf";

      const thumbHtml = isImage
        ? `<img src="${downloadUrl}" alt="${b.nama_file}"
            style="width:44px;height:44px;object-fit:cover;border-radius:4px;border:1px solid #dee2e6;">`
        : `<div style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;
            background:#f8f9fa;border:1px solid #dee2e6;border-radius:4px;font-size:20px;">
            ${isPdf ? "📄" : "📎"}
          </div>`;

      // [FIX #4] Tombol lebih kecil, pakai ikon saja
      const actionHtml = `
      <div class="d-flex gap-1 mt-1">
        <a href="${viewUrl}" target="_blank" title="Lihat"
           style="font-size:11px;padding:2px 8px;" class="btn btn-xs btn-outline-primary">
          👁
        </a>
        <a href="${downloadUrl}" download title="Unduh"
           style="font-size:11px;padding:2px 8px;" class="btn btn-xs btn-outline-secondary">
          ⬇
        </a>
      </div>`;

      // [FIX #2] Tombol ✕ hanya muncul jika showDelete = true (edit/create mode)
      const deleteBtn = showDelete
        ? `<button type="button" class="btn btn-sm btn-outline-danger ms-auto"
           style="flex-shrink:0;padding:2px 8px;font-size:12px;"
           onclick="prDeleteBukti(${b.id_bukti})">✕</button>`
        : "";

      return `
      <div class="d-flex align-items-center gap-2 border rounded p-2 mb-2 bg-light">
        <a href="${viewUrl}" target="_blank" style="cursor:pointer;flex-shrink:0;">
          ${thumbHtml}
        </a>
        <div class="flex-grow-1 overflow-hidden">
          <div class="small fw-semibold text-truncate" style="max-width:160px;">${b.nama_file}</div>
          ${actionHtml}
        </div>
        ${deleteBtn}
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

/* ======================================================
   PREVIEW FILE LOKAL (sebelum disimpan)
====================================================== */
function prPreviewLocalFile(file) {
  const container = document.getElementById("prBuktiPreview");
  if (!container || !file) return;

  // Hapus data bukti lama (ini file baru belum tersimpan)
  delete container.dataset.bukti;

  const ext = file.name.split(".").pop().toLowerCase();
  const isImage = ["jpg", "jpeg", "png"].includes(ext);
  const isPdf = ext === "pdf";
  const url = URL.createObjectURL(file);

  const thumbHtml = isImage
    ? `<img src="${url}" alt="${file.name}"
          style="width:44px;height:44px;object-fit:cover;border-radius:4px;border:1px solid #dee2e6;">`
    : `<div style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;
          background:#f8f9fa;border:1px solid #dee2e6;border-radius:4px;font-size:20px;">
          ${isPdf ? "📄" : "📎"}
        </div>`;

  container.innerHTML = `
    <div class="d-flex align-items-center gap-2 border rounded p-2 mb-2 bg-light">
      <a href="${url}" target="_blank" style="cursor:pointer;flex-shrink:0;">
        ${thumbHtml}
      </a>
      <div class="flex-grow-1 overflow-hidden">
        <div class="small fw-semibold text-truncate" style="max-width:160px;">${file.name}</div>
        <div class="small text-muted">${(file.size / 1024).toFixed(1)} KB</div>
        <div class="mt-1">
          <a href="${url}" target="_blank" title="Lihat"
             style="font-size:11px;padding:2px 8px;" class="btn btn-xs btn-outline-primary">👁</a>
        </div>
      </div>
      <span class="badge bg-warning text-dark ms-auto" style="font-size:10px;">Belum disimpan</span>
    </div>`;
}

/* ======================================================
   EVENT: File dipilih → validasi tipe + preview lokal
   [FIX #5] Hanya 1 file, file baru menggantikan lama (replace)
====================================================== */
document.addEventListener("DOMContentLoaded", function () {
  const fileInput = document.getElementById("prBuktiFileInput");
  if (!fileInput) return;

  fileInput.addEventListener("change", function () {
    const file = this.files[0];
    if (!file) return;

    const allowedExts = ["jpg", "jpeg", "png", "pdf"];
    const ext = file.name.split(".").pop().toLowerCase();

    if (!allowedExts.includes(ext)) {
      Swal.fire({
        icon: "warning",
        title: "Format tidak didukung",
        text: "File hanya boleh berupa JPG, PNG, atau PDF.",
      });
      this.value = "";
      const container = document.getElementById("prBuktiPreview");
      if (container) {
        delete container.dataset.bukti;
        container.innerHTML =
          '<p class="text-muted small mb-0">Belum ada file</p>';
      }
      return;
    }

    // Preview file baru (menggantikan preview lama)
    prPreviewLocalFile(file);
  });
});

function prAutoSetStatus() {
  const outputEl = document.getElementById("prRealisasiOutput");
  const statusEl = document.getElementById("prStatus");

  if (!outputEl || !statusEl) return;

  const val = outputEl.value.trim();

  // Kalau user sudah pilih selesai → jangan ganggu
  if (statusEl.value === "Selesai") return;

  if (!val) {
    statusEl.value = "Belum Dilaksanakan";
  } else {
    statusEl.value = "Dalam Proses";
  }
}

document.addEventListener("DOMContentLoaded", function () {
  const outputEl = document.getElementById("prRealisasiOutput");

  if (outputEl) {
    outputEl.addEventListener("input", prAutoSetStatus);
  }
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
    container.innerHTML = '<p class="text-muted small mb-0">Belum ada file</p>';
  }

  const fileInput = document.getElementById("prBuktiFileInput");
  if (fileInput) {
    fileInput.disabled = false;
    fileInput.style.display = "";
    fileInput.value = "";
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
  setVal("prStatus", d.status ?? "Belum Dilaksanakan");
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
    if (d.id_pemantauan) {
      prSetMode("view"); // prSetMode akan re-render bukti tanpa tombol ✕
    } else {
      prSetMode("create");
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

        const fileInput = document.getElementById("prBuktiFileInput");
        if (fileInput) fileInput.value = "";

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

/* ======================================================
   DELETE BUKTI
   [FIX #3] Gunakan $.ajax dengan method DELETE sesuai route,
            atau POST dengan _method override jika perlu.
            Route: DELETE pemantauan-risiko/bukti/{id}
====================================================== */
function prDeleteBukti(id) {
  Swal.fire({
    title: "Hapus file ini?",
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
            '<p class="text-muted small mb-0">Belum ada file</p>';
        }
      },
      error(xhr) {
        Swal.fire({
          icon: "error",
          title: "Gagal menghapus file",
          text: xhr.responseJSON?.message ?? "Terjadi kesalahan",
        });
      },
    });
  });
}
