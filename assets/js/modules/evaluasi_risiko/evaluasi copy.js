/* URL & CSRF */
let ER_CAN_EDIT = false;
const USER = window.APP_USER || {};
const ER_URL = window.ER_CONFIG?.url || {};
let erCsrfToken = window.ER_CONFIG?.csrf?.token || "";
const erCsrfName = window.ER_CONFIG?.csrf?.name || "";

/* MODE MANAGEMENT */
function erSetMode(mode) {
  const isView = mode === "view";
  const isEdit = mode === "edit";
  const isCreate = mode === "create";

  document.getElementById("erMode").value = mode;

  const fields = ["erOpsiTindakan", "erKeterangan"];
  fields.forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.disabled = isView;
  });

  // EDIT
  document
    .getElementById("erBtnEdit")
    .classList.toggle("d-none", !isView || !ER_CAN_EDIT);

  // DELETE
  document
    .getElementById("erBtnDelete")
    .classList.toggle("d-none", !isView || !ER_CAN_EDIT);

  // BATAL
  document
    .getElementById("erBtnBatal")
    .classList.toggle("d-none", !isEdit || !ER_CAN_EDIT);

  // SIMPAN
  document
    .getElementById("erBtnSimpan")
    .classList.toggle("d-none", isView || !ER_CAN_EDIT);

  // TUTUP
  document.getElementById("erBtnTutup").classList.toggle("d-none", isEdit);

  document
    .getElementById("erBtnDelete")
    .classList.toggle("d-none", !isView || !ER_CAN_EDIT);

  document.getElementById("erOffcanvasTitle").textContent = isCreate
    ? "Tambah Evaluasi Risiko"
    : isEdit
      ? "Edit Evaluasi Risiko"
      : "Detail Evaluasi Risiko";
}

function erResetForm() {
  document.getElementById("erForm").reset();
  document.getElementById("erForm").classList.remove("was-validated");

  document.getElementById("erId").value = "";
  document.getElementById("erIdIdentifikasi").value = "";
  document.getElementById("erIdPenilaian").value = "";

  [
    "erInfoTahun",
    "erInfoTimKerja",
    "erInfoKegiatan",
    "erInfoPengelola",
    "erInfoSasaran",
    "erInfoProses",
    "erInfoSasaranKinerja",
    "erInfoPernyataan",
    "erInfoPenyebab",
    "erInfoDampakRisiko",
    "erInfoProb",
    "erInfoImpact",
    "erInfoPengendalian",
    "erInfoEfektivitas",
  ].forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.textContent = "-";
  });

  // Reset preview skor
  const nilaiEl = document.getElementById("erPreviewNilai");
  const badgeEl = document.getElementById("erPreviewBadge");
  if (nilaiEl) {
    nilaiEl.textContent = "0";
    nilaiEl.style.color = "";
  }
  if (badgeEl) {
    badgeEl.textContent = "";
    badgeEl.style.backgroundColor = "";
  }
}

function erFormatNumberedText(text) {
  if (!text) return "-";

  const lines = text.split(/\n|\r/).filter((l) => l.trim() !== "");

  return lines.map((line) => `<div>${line}</div>`).join("");
}

function erCanEdit(data) {
  const user = window.APP_USER;
  if (!user) return false;

  // Admin bebas
  if (user.role === "admin") return true;

  // Ketua TIDAK BOLEH edit
  if (user.role === "ketua") return false;

  // Operator → hanya tim sendiri
  if (user.role === "operator") {
    return String(user.id_tim) === String(data.id_tim);
  }

  return false;
}

/* POPULATE INFO */
function erPopulateInfo(d) {
  const set = (id, val) => {
    const el = document.getElementById(id);
    if (el) el.textContent = val || "-";
  };

  set("erInfoTahun", d.tahun);
  set("erInfoTimKerja", d.nama_tim);
  set("erInfoKegiatan", d.nama_kegiatan);
  set("erInfoPengelola", d.nama_pengelola);
  set("erInfoSasaran", d.sasaran_strategis);
  set(
    "erInfoProses",
    (d.kode_proses ? d.kode_proses + " — " : "") + (d.uraian_proses ?? ""),
  );
  set("erInfoSasaranKinerja", d.sasaran_kinerja);
  set("erInfoPernyataan", d.pernyataan_risiko);
  document.getElementById("erInfoPenyebab").innerHTML = erFormatNumberedText(
    d.penyebab_risiko,
  );
  document.getElementById("erInfoDampakRisiko").innerHTML =
    erFormatNumberedText(d.dampak_risiko);
  set("erInfoProb", d.level_kemungkinan);
  set("erInfoImpact", d.level_dampak);
  document.getElementById("erInfoPengendalian").innerHTML =
    erFormatNumberedText(d.uraian_pengendalian);
  set("erInfoEfektivitas", d.efektivitas);

  // Preview skor risiko
  const nilaiEl = document.getElementById("erPreviewNilai");
  const badgeEl = document.getElementById("erPreviewBadge");
  if (nilaiEl) {
    nilaiEl.textContent = d.nilai_risiko || "0";
    nilaiEl.style.color = d.warna_risiko || "";
  }
  if (badgeEl) {
    badgeEl.textContent = d.nama_selera || "";
    badgeEl.style.backgroundColor = d.warna_risiko || "";
  }

  // === RBAC CONTROL ===
  ER_CAN_EDIT = erCanEdit(d);
}

/* LOAD DETAIL EVALUASI (view/edit mode) */
function erLoadDetail(idEvaluasi) {
  return fetch(ER_URL.detail(idEvaluasi), {
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
  })
    .then((r) => r.json())
    .then((d) => {
      document.getElementById("erId").value = d.id_evaluasi;
      document.getElementById("erIdIdentifikasi").value = d.id_identifikasi;
      document.getElementById("erIdPenilaian").value = d.id_penilaian;

      document.getElementById("erOpsiTindakan").value = d.opsi_tindakan ?? "";
      const erPrioritas = document.getElementById("erPrioritas");
      if (erPrioritas) erPrioritas.value = d.prioritas ?? "";
      document.getElementById("erKeterangan").value = d.keterangan ?? "";

      erPopulateInfo(d);
      return d;
    });
}

/* BATAL */
function erBatal() {
  const id = document.getElementById("erId").value;
  if (id) {
    erLoadDetail(id).then(() => erSetMode("view"));
  } else {
    bootstrap.Offcanvas.getInstance(
      document.getElementById("erOffcanvas"),
    ).hide();
  }
}

/* OPEN ROW */
document.addEventListener("click", function (e) {
  const row = e.target.closest(".er-row");
  if (!row) return;

  const idIdentifikasi = row.dataset.identifikasi;
  const idPenilaian = row.dataset.penilaian;
  const idEvaluasi = row.dataset.evaluasi;

  erResetForm();
  bootstrap.Offcanvas.getOrCreateInstance(
    document.getElementById("erOffcanvas"),
  ).show();

  if (idEvaluasi) {
    // Sudah dievaluasi → load detail evaluasi (view mode)
    erLoadDetail(idEvaluasi).then(() => erSetMode("view"));
  } else {
    // Belum dievaluasi → load detail identifikasi untuk create mode
    // [FIX] Gunakan idIdentifikasi, bukan idPenilaian
    document.getElementById("erIdIdentifikasi").value = idIdentifikasi;
    document.getElementById("erIdPenilaian").value = idPenilaian;

    fetch(ER_URL.detailAnalisis(idIdentifikasi), {
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    })
      .then((r) => r.json())
      .then((d) => {
        erPopulateInfo(d);

        // Jika ada id_penilaian dari response, update hidden field
        if (d.id_penilaian) {
          document.getElementById("erIdPenilaian").value = d.id_penilaian;
        }

        erSetMode("create");
      });
  }
});

/* SUBMIT (STORE / UPDATE) */
document.getElementById("erForm")?.addEventListener("submit", function (e) {
  e.preventDefault();

  const form = e.target;
  const mode = document.getElementById("erMode").value;
  const id = document.getElementById("erId").value;

  if (!form.checkValidity()) {
    form.classList.add("was-validated");
    return;
  }

  Swal.fire({
    title: mode === "edit" ? "Simpan Perubahan?" : "Simpan Evaluasi?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Simpan",
    cancelButtonText: "Batal",
    reverseButtons: true,
  }).then((result) => {
    if (!result.isConfirmed) return;

    const freshData = new FormData(form);
    freshData.append(erCsrfName, erCsrfToken);

    $.ajax({
      url: mode === "edit" ? ER_URL.update(id) : ER_URL.store,
      method: "POST",
      data: freshData,
      processData: false,
      contentType: false,
      headers: { "X-Requested-With": "XMLHttpRequest" },

      success(res) {
        if (res.csrf_token) erCsrfToken = res.csrf_token;
        bootstrap.Offcanvas.getInstance(
          document.getElementById("erOffcanvas"),
        ).hide();
        Swal.fire({
          icon: "success",
          title: "Berhasil disimpan",
          timer: 1200,
          showConfirmButton: false,
        }).then(() => location.reload());
      },

      error(xhr) {
        const msg = xhr.responseJSON?.message ?? "Terjadi kesalahan.";
        Swal.fire({ icon: "error", title: "Gagal", text: msg });
      },
    });
  });
});

function erHapus() {
  const id = document.getElementById("erId").value;
  if (!id) return;

  Swal.fire({
    title: "Hapus evaluasi ini?",
    text: "Data RTP terkait juga akan ikut terhapus.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, Hapus",
    cancelButtonText: "Batal",
    confirmButtonColor: "#dc3545",
    reverseButtons: true,
  }).then((result) => {
    if (!result.isConfirmed) return;

    $.ajax({
      url: ER_URL.delete(id),
      method: "POST",
      data: { [erCsrfName]: erCsrfToken },
      processData: true,
      contentType: "application/x-www-form-urlencoded",
      headers: { "X-Requested-With": "XMLHttpRequest" },
      success(res) {
        if (res.csrf_token) erCsrfToken = res.csrf_token;
        bootstrap.Offcanvas.getInstance(
          document.getElementById("erOffcanvas"),
        ).hide();
        Swal.fire({
          icon: "success",
          title: "Berhasil dihapus",
          timer: 1200,
          showConfirmButton: false,
        }).then(() => location.reload());
      },
      error() {
        Swal.fire({ icon: "error", title: "Gagal menghapus" });
      },
    });
  });
}

function enableAutoNumbering(textareaId) {
  const el = document.getElementById(textareaId);
  if (!el) return;

  el.addEventListener("keydown", function (e) {
    if (e.key !== "Enter") return;

    e.preventDefault();

    let value = el.value.trim();
    let lines = value ? value.split("\n") : [];

    if (lines.length === 1 && !lines[0].match(/^\d+\./)) {
      el.value = `1. ${lines[0]}\n2. `;
    } else {
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

document.addEventListener("DOMContentLoaded", function () {
  enableAutoNumbering("erKeterangan"); // nanti kalau ada textarea lain tinggal tambah
});
