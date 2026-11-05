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

  // Efektivitas Pengendalian
  const efektivitasView = document.getElementById("prEfektivitasView");
  const efektivitasEdit = document.getElementById("prEfektivitasPengendalian");

  const canEditEfektivitas = USER.role === "operator";

  if (efektivitasView && efektivitasEdit) {
    if (isView || !canEditEfektivitas) {
      efektivitasView.textContent = efektivitasEdit.value || "-";
      efektivitasView.classList.remove("d-none");
      efektivitasEdit.classList.add("d-none");
      efektivitasEdit.disabled = true;
    } else {
      efektivitasView.classList.add("d-none");
      efektivitasEdit.classList.remove("d-none");
      efektivitasEdit.disabled = false;
    }
  }

  // Risiko Residu — hanya operator yang berhak mengubah
  const residuView = document.getElementById("prRisikoResiduView");
  const residuInput = document.getElementById("prRisikoResiduInput");
  const kemungkinanResidu = document.getElementById("prKemungkinanResidu");
  const dampakResidu = document.getElementById("prDampakResidu");

  const canEditResidu = USER.role === "operator" && PR_CAN_EDIT;

  if (residuView && residuInput) {
    if (isView || !canEditResidu) {
      residuView.classList.remove("d-none");
      residuInput.classList.add("d-none");

      if (kemungkinanResidu) kemungkinanResidu.disabled = true;
      if (dampakResidu) dampakResidu.disabled = true;
    } else {
      residuView.classList.add("d-none");
      residuInput.classList.remove("d-none");

      if (kemungkinanResidu) kemungkinanResidu.disabled = false;
      if (dampakResidu) dampakResidu.disabled = false;
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

  // ===== RISIKO RESIDU =====
  document
    .getElementById("prKemungkinanResidu")
    ?.addEventListener("change", prHitungResidu);

  document
    .getElementById("prDampakResidu")
    ?.addEventListener("change", prHitungResidu);

  // ===== BUKTI DUKUNG =====
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

  const efektivitasView = document.getElementById("prEfektivitasView");
  if (efektivitasView) {
    efektivitasView.textContent = "-";
    efektivitasView.classList.add("d-none");
  }

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
    "prResiduKemungkinanView",
    "prResiduDampakView",
    "prNilaiRisikoResiduView",
    "prSeleraRisikoResiduView",
    "prNilaiRisikoResidu",
    "prSeleraRisikoResidu",
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
  setVal("prEfektivitasPengendalian", d.efektivitas_pengendalian);
  setVal("prKemungkinanResidu", d.id_kemungkinan_residu);
  setVal("prDampakResidu", d.id_dampak_residu);
  setVal("prKemungkinanResidu", d.id_kemungkinan_residu);
  setVal("prDampakResidu", d.id_dampak_residu);

  prHitungResidu();

  const kemungkinanSelect = document.getElementById("prKemungkinanResidu");
  const dampakSelect = document.getElementById("prDampakResidu");

  const kemungkinanText = kemungkinanSelect?.selectedOptions[0]?.text;
  const dampakText = dampakSelect?.selectedOptions[0]?.text;

  const residuSet = (id, value) => {
    const el = document.getElementById(id);
    if (el) el.textContent = value || "-";
  };

  residuSet("prResiduKemungkinanView", kemungkinanText);
  residuSet("prResiduDampakView", dampakText);
  residuSet("prNilaiRisikoResiduView", d.nilai_risiko_residu);
  residuSet("prSeleraRisikoResiduView", d.nama_selera_residu);

  residuSet("prNilaiRisikoResidu", d.nilai_risiko_residu);
  residuSet("prSeleraRisikoResidu", d.nama_selera_residu);

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

function prHitungResidu() {
  const kemungkinan = document.getElementById("prKemungkinanResidu")?.value;
  const dampak = document.getElementById("prDampakResidu")?.value;

  if (!kemungkinan || !dampak) {
    return prResetResidu();
  }

  const body = new URLSearchParams({
    id_kemungkinan: kemungkinan,
    id_dampak: dampak,
    [prCsrfName]: prCsrfToken,
  });

  fetch(PR_URL.preview, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
      "X-Requested-With": "XMLHttpRequest",
    },
    body,
  })
    .then((response) => response.json())
    .then((res) => {
      if (res.status !== "success") {
        return prResetResidu();
      }

      const warnaMap = {
        biru: "#0d6efd",
        hijau: "#198754",
        kuning: "#ffc107",
        oranye: "#fd7e14",
        merah: "#dc3545",
      };

      const warna =
        warnaMap[(res.warna_selera || "").toLowerCase()] ||
        res.warna_selera ||
        "#6c757d";

      const setText = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.textContent = value ?? "-";
      };

      setText("prNilaiRisikoResidu", res.nilai_risiko);
      setText("prSeleraRisikoResidu", res.nama_selera);
      setText("prNilaiRisikoResiduView", res.nilai_risiko);
      setText("prSeleraRisikoResiduView", res.nama_selera);

      const nilai = document.getElementById("prNilaiRisikoResidu");
      const nilaiView = document.getElementById("prNilaiRisikoResiduView");

      if (nilai) nilai.style.color = res.warna || "";
      if (nilaiView) nilaiView.style.color = res.warna || "";

      const level = document.getElementById("prSeleraRisikoResidu");
      const levelView = document.getElementById("prSeleraRisikoResiduView");

      [level, levelView].forEach((el) => {
        if (el) {
          el.style.backgroundColor = warna;
          el.style.color = "#fff";
          el.style.padding = "3px 8px";
          el.style.borderRadius = "6px";
        }
      });
    })
    .catch((error) => {
      console.error("Gagal menghitung Risiko Residu:", error);
      prResetResidu();
    });
}

function prResetResidu() {
  const ids = [
    "prNilaiRisikoResidu",
    "prSeleraRisikoResidu",
    "prNilaiRisikoResiduView",
    "prSeleraRisikoResiduView",
  ];

  ids.forEach((id) => {
    const el = document.getElementById(id);
    if (el) {
      el.textContent = "-";
      el.style.color = "";
      el.style.backgroundColor = "";
    }
  });
}

/* ======================================================
   LOAD DETAIL
====================================================== */
function prLoadDetail(idRtp) {
    console.log("ID RTP YANG DIKLIK:", idRtp);

    return fetch(PR_URL.detail(idRtp))
        .then((r) => r.json())
        .then((d) => {
            console.log("HASIL DETAIL:", d);
            console.log("ID PEMANTAUAN:", d.id_pemantauan);

            PR_CAN_EDIT = prCanEdit(d);
            console.log("CAN EDIT:", PR_CAN_EDIT);

            document.getElementById("prIdRtp").value = d.id_rtp;
            prPopulateInfo(d);

            return d;
        });
}
/* ======================================================
   OPEN ROW
====================================================== */
document.addEventListener("click", function (e) {
  // Abaikan klik pada dropdown dan input jumlah keterjadian
  if (e.target.closest(".pr-keterjadian-select, .pr-jumlah-keterjadian")) {
    return;
  }

  const row = e.target.closest(".rtp-row");
  if (!row) return;

  const idRtp = row.dataset.rtp;
  if (!idRtp) return;

  prResetForm();

  bootstrap.Offcanvas.getOrCreateInstance(
    document.getElementById("prOffcanvas"),
  ).show();

  prLoadDetail(idRtp).then((d) => {
    console.log("DATA SETELAH LOAD:", d);
    console.log("PR_CAN_EDIT:", PR_CAN_EDIT);
    console.log("ID PEMANTAUAN:", d.id_pemantauan);

    if (d.id_pemantauan) {
      prSetMode("view");
    } else {
      prSetMode("create");
    }

    console.log(
      "CLASS TOMBOL SETELAH MODE:",
      document.getElementById("prBtnEdit").className
    );
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

  switch (status) {
    case "Selesai":
      cls = "success";
      break;

    case "Dalam Proses":
      cls = "primary";
      break;

    case "Terlambat":
      cls = "danger";
      break;

    case "Belum Dilaksanakan":
      cls = "secondary";
      break;
  }

  el.className = `badge bg-${cls}-subtle text-${cls} border border-${cls}`;
  el.textContent = status || "-";
}

function prAutoSetStatus(data) {
  const statusEl = document.getElementById("prStatus");
  if (!statusEl) return;

  statusEl.value = data.status || "Belum Dilaksanakan";
}

function prCanEdit(data) {
  if (!USER) return false;

  if (USER.role === "admin") return true;
  if (USER.role === "ketua") return false;

  if (USER.role === "operator") {
    const userTeams = (USER.tim || []).map(String);

    return userTeams.includes(String(data.id_tim));
  }

  return false;
}

/* ======================================================
   PAGINATION AJAX PEMANTAUAN RISIKO
====================================================== */

function prGoToPage(page) {
  const tableContainer = document.getElementById("prTableCard");

  if (!tableContainer) {
    console.error("Container tabel tidak ditemukan");
    return;
  }

  const perPage = tableContainer.querySelector('[name="perPage"]')?.value || 10;

  const filter =
    tableContainer.querySelector('[name="filter"]')?.value || "semua";

  const url = new URL(PR_URL.table, window.location.origin);

  url.searchParams.set("page", page);
  url.searchParams.set("perPage", perPage);
  url.searchParams.set("filter", filter);

  tableContainer.style.opacity = "0.5";

  fetch(url.toString(), {
    method: "GET",
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("Gagal mengambil data tabel");
      }

      return response.text();
    })
    .then((html) => {
      const temp = document.createElement("div");
      temp.innerHTML = html;

      const newContainer = temp.querySelector("#prTableCard");

      if (!newContainer) {
        throw new Error("Container tabel tidak ditemukan pada respons");
      }

      tableContainer.replaceWith(newContainer);
    })
    .catch((error) => {
      console.error("Pagination error:", error);

      tableContainer.style.opacity = "1";

      Swal.fire({
        icon: "error",
        title: "Gagal memuat data",
        text: "Silakan coba kembali.",
      });
    });
}

/* ======================================================
   UPDATE KETERJADIAN RISIKO
====================================================== */

// Simpan perubahan keterjadian ke database
function prSimpanKeterjadian(select, jumlahInput) {
  const idIdentifikasi = select.dataset.idIdentifikasi;

  if (!idIdentifikasi || !PR_URL.updateKeterjadian) {
    console.error("ID risiko atau URL update tidak tersedia.");
    return;
  }

  const data = new FormData();
  data.append("id_identifikasi", idIdentifikasi);
  data.append("keterjadian_risiko", select.value);
  data.append("jumlah_keterjadian", jumlahInput.value || 0);
  data.append(prCsrfName, prCsrfToken);

  select.disabled = true;
  jumlahInput.disabled = true;

  fetch(PR_URL.updateKeterjadian, {
    method: "POST",
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
    body: data,
  })
    .then(async (response) => {
      const result = await response.json();

      if (!response.ok || !result.success) {
        throw new Error(
          result.message || "Gagal menyimpan keterjadian risiko.",
        );
      }

      if (result.csrfHash) {
        prCsrfToken = result.csrfHash;
      }

      return result;
    })
    .then((result) => {
      jumlahInput.value = result.data.jumlah_keterjadian;
      console.log("Keterjadian berhasil disimpan:", result.data);
    })
    .catch((error) => {
      console.error("Update keterjadian gagal:", error);

      Swal.fire({
        icon: "error",
        title: "Gagal menyimpan",
        text: error.message,
      });
    })
    .finally(() => {
      select.disabled = false;
      jumlahInput.disabled = select.value !== "T";
    });
}

// Ketika dropdown status berubah
document.addEventListener("change", function (e) {
  if (!e.target.matches(".pr-keterjadian-select")) return;

  const select = e.target;
  const idIdentifikasi = select.dataset.idIdentifikasi;
  const table = select.closest("table");

  const jumlahInput = table.querySelector(
    `.pr-jumlah-keterjadian[data-id-identifikasi="${idIdentifikasi}"]`,
  );

  if (!jumlahInput) return;

  if (select.value === "T") {
    jumlahInput.disabled = false;
    jumlahInput.focus();
  } else {
    jumlahInput.value = 0;
    jumlahInput.disabled = true;
  }

  prSimpanKeterjadian(select, jumlahInput);
});

// Ketika jumlah keterjadian diubah
document.addEventListener("change", function (e) {
  if (!e.target.matches(".pr-jumlah-keterjadian")) return;

  const jumlahInput = e.target;
  const idIdentifikasi = jumlahInput.dataset.idIdentifikasi;
  const table = jumlahInput.closest("table");

  const select = table.querySelector(
    `.pr-keterjadian-select[data-id-identifikasi="${idIdentifikasi}"]`,
  );

  if (!select || select.value !== "T") return;

  if (jumlahInput.value === "" || Number(jumlahInput.value) < 0) {
    Swal.fire({
      icon: "warning",
      title: "Jumlah tidak valid",
      text: "Masukkan jumlah minimal 0.",
    });
    return;
  }

  prSimpanKeterjadian(select, jumlahInput);
});
