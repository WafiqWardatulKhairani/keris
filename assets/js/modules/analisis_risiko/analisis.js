
const USER = window.APP_USER || {};
const AR_URL = window.AR_CONFIG?.url || {};
let arCsrfToken = window.AR_CONFIG?.csrf?.token || "";
const arCsrfName = window.AR_CONFIG?.csrf?.name || "";

/* MODE MANAGEMENT */
function arSetMode(mode) {
  const isView = mode === "view";
  const isEdit = mode === "edit";
  const isCreate = mode === "create";

  document.getElementById("arMode").value = mode;

  const role = USER.role || "guest";

  let isReadonly = false;

  if (role === "ketua") {
    isReadonly = true;
  } else if (role === "operator") {
    isReadonly = window.AR_IS_BEDA_TIM === true;
  }

  // ===== BUTTONS =====
  const btnEdit = document.getElementById("arBtnEdit");
  const btnBatal = document.getElementById("arBtnBatal");
  const btnSimpan = document.getElementById("arBtnSimpan");
  const btnDelete = document.getElementById("arBtnDelete");
  const btnTutup = document.getElementById("arBtnTutup");

  // reset semua
  [btnEdit, btnBatal, btnSimpan, btnDelete].forEach((b) =>
    b?.classList.add("d-none"),
  );

  // ===== DISABLE FIELD =====
  [
    "arKemungkinan",
    "arDampak",
    "arUraianPengendalian",
    "arEfektivitas",
  ].forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.disabled = isView || isReadonly;
  });

  // ===== MODE LOGIC =====
  if (isCreate) {
    if (!isReadonly) {
      btnSimpan?.classList.remove("d-none");
    }
  } else if (isView) {
    if (!isReadonly && (role === "admin" || role === "operator")) {
      btnEdit?.classList.remove("d-none");
    }

    if (!isReadonly && (role === "admin" || role === "operator")) {
      btnDelete?.classList.remove("d-none");
    }
  } else if (isEdit) {
    if (!isReadonly) {
      btnBatal?.classList.remove("d-none");
      btnSimpan?.classList.remove("d-none");
    }
  }

  // Tutup hanya di mode view/create, BUKAN saat edit
  if (!isEdit) {
    btnTutup?.classList.remove("d-none");
  }

  // ===== TITLE =====
  document.getElementById("arOffcanvasTitle").textContent = isCreate
    ? "Tambah Analisis Risiko"
    : isEdit
      ? "Edit Analisis Risiko"
      : "Detail Analisis Risiko";

  // ===== VIEW / EDIT SWITCH =====
  const viewEl = document.getElementById("arInfoPengendalian");
  const editEl = document.getElementById("arUraianPengendalian");

  if (isView || isReadonly) {
    viewEl?.classList.remove("d-none");
    editEl?.classList.add("d-none");
  } else {
    viewEl?.classList.add("d-none");
    editEl?.classList.remove("d-none");
  }
}

/* RESET FORM */
function arResetForm() {
  document.getElementById("arForm").reset();
  document.getElementById("arForm").classList.remove("was-validated");
  document.getElementById("arId").value = "";
  document.getElementById("arIdIdentifikasi").value = "";
  document.getElementById("arPreview").classList.add("d-none");

  [
    "arInfoTahun",
    "arInfoTimKerja",
    "arInfoKegiatan",
    "arInfoPengelola",
    "arInfoSasaran",
    "arInfoProses",
    "arInfoSasaranKinerja",
    "arInfoPernyataan",
    "arInfoPenyebab",
    "arInfoDampak",
    "arDescKemungkinan",
    "arDescDampak",
  ].forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.textContent = "-";
  });
}

/* POPULATE INFO KONTEKS & RISIKO */
function arPopulateInfo(d) {
  const set = (id, val) => {
    const el = document.getElementById(id);
    if (el) el.textContent = val || "-";
  };
  set("arInfoTahun", d.tahun);
  set("arInfoTimKerja", d.nama_tim);
  set("arInfoKegiatan", d.nama_kegiatan);
  set("arInfoPengelola", d.nama_pengelola);
  set("arInfoSasaran", d.sasaran_strategis);
  set(
    "arInfoProses",
    (d.kode_proses ? d.kode_proses + " — " : "") + (d.uraian_proses ?? ""),
  );
  set("arInfoSasaranKinerja", d.sasaran_kinerja);
  set("arInfoPernyataan", d.pernyataan_risiko);

  const warnaMap = {
    biru: "#0d6efd",
    hijau: "#198754",
    kuning: "#ffc107",
    oranye: "#fd7e14",
    merah: "#dc3545",
  };

  const warna = warnaMap[d.warna_selera] || "#6c757d";

  document.getElementById("arPreview").classList.remove("d-none");

  const nilaiEl = document.getElementById("arPreviewNilai");
  const badgeEl = document.getElementById("arPreviewBadge");

  if (nilaiEl) {
    nilaiEl.textContent = d.nilai_risiko || "0";
    nilaiEl.style.color = warna;
  }

  if (badgeEl) {
    badgeEl.textContent = d.nama_selera || "-";
    badgeEl.style.backgroundColor = warna;
    badgeEl.style.color = "#fff";
  }

  document.getElementById("arPreviewTindakan").textContent = d.tindakan || "";

  function formatNumberedText(text) {
    if (!text) return "-";

    const lines = text.split(/\n|\r/).filter((l) => l.trim() !== "");

    return lines.map((line) => `<div>${line}</div>`).join("");
  }

  document.getElementById("arInfoPenyebab").innerHTML = formatNumberedText(
    d.penyebab_risiko,
  );

  document.getElementById("arInfoDampak").innerHTML = formatNumberedText(
    d.dampak_risiko,
  );
  document.getElementById("arInfoPengendalian").innerHTML = formatNumberedText(
    d.uraian_pengendalian,
  );
}

/* LOAD DETAIL PENILAIAN (view/edit mode) */
function arLoadDetail(idPenilaian) {
  return fetch(AR_URL.detail(idPenilaian))
    .then((r) => r.json())
    .then((d) => {
      console.log("USER:", window.APP_USER);
      console.log("DATA:", d);
      const currentUser = window.APP_USER || {};

      const isOperator = currentUser.role === "operator";
      const bedaTim = String(currentUser.id_tim) !== String(d.id_tim);

      window.AR_IS_BEDA_TIM = isOperator && bedaTim;
      document.getElementById("arId").value = d.id_penilaian;
      document.getElementById("arIdIdentifikasi").value = d.id_identifikasi;
      document.getElementById("arKemungkinan").value = d.id_kemungkinan ?? "";
      document.getElementById("arDampak").value = d.id_dampak ?? "";
      document.getElementById("arUraianPengendalian").value = d.uraian_pengendalian ?? "";
      document.getElementById("arEfektivitas").value = d.efektivitas ?? "";
      arPopulateInfo(d);
      arLoadPreview();
      return d;
    });
}

/* BATAL — kembali ke view */
function arBatal() {
  const id = document.getElementById("arId").value;
  if (id) {
    arLoadDetail(id).then(() => arSetMode("view"));
  } else {
    bootstrap.Offcanvas.getInstance(
      document.getElementById("arOffcanvas"),
    ).hide();
  }
}

/* OPEN ROW — klik baris tabel */
document.addEventListener("click", function (e) {
  const row = e.target.closest(".ar-row");
  if (!row) return;
  window.AR_IS_BEDA_TIM = false;

  const idIdentifikasi = row.dataset.identifikasi;
  const idPenilaian = row.dataset.penilaian;

  arResetForm();
  bootstrap.Offcanvas.getOrCreateInstance(document.getElementById("arOffcanvas"),).show();

  if (idPenilaian) {
    arLoadDetail(idPenilaian).then(() => arSetMode("view"));
  } else {
    document.getElementById("arIdIdentifikasi").value = idIdentifikasi;
    fetch(AR_URL.detailIdentifikasi(idIdentifikasi))
      .then((r) => r.json())
      .then((d) => {
        const currentUser = window.APP_USER || {};
        const isOperator = currentUser.role === "operator";
        const bedaTim = String(currentUser.id_tim) !== String(d.id_tim);
        window.AR_IS_BEDA_TIM = isOperator && bedaTim;
        arPopulateInfo(d);        

        arSetMode("create");
      });
  }
});

/* PREVIEW SKOR RISIKO */
function arLoadPreview() {
  const idK = document.getElementById("arKemungkinan").value;
  const idD = document.getElementById("arDampak").value;
  const preview = document.getElementById("arPreview");

  if (!idK || !idD) {
    preview.classList.add("d-none");
    return;
  }

  const selK = document.getElementById("arKemungkinan");
  const selD = document.getElementById("arDampak");
  document.getElementById("arDescKemungkinan").textContent = selK.options[selK.selectedIndex]?.dataset.desc ?? "";
  document.getElementById("arDescDampak").textContent = selD.options[selD.selectedIndex]?.dataset.desc ?? "";

  fetch(AR_URL.preview, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `id_kemungkinan=${idK}&id_dampak=${idD}`,
  })
    .then((r) => r.json())
    .then((res) => {
      if (res.status !== "success") return;
      preview.classList.remove("d-none");

      const nilai = document.getElementById("arPreviewNilai");
      const badge = document.getElementById("arPreviewBadge");
      nilai.textContent = res.nilai_risiko;
      nilai.style.color = res.warna;
      badge.textContent = res.nama_selera;
      badge.style.backgroundColor = res.warna;
      badge.style.color = "#fff";
      document.getElementById("arPreviewTindakan").textContent = res.tindakan;
    });
}

document.getElementById("arKemungkinan")?.addEventListener("change", arLoadPreview);
document.getElementById("arDampak")?.addEventListener("change", arLoadPreview);

/* HAPUS */
function arHapus() {
  const id = document.getElementById("arId").value;
  if (!id) return;

  Swal.fire({
    title: "Hapus analisis ini?",
    text: "Data penilaian akan dihapus permanen.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, Hapus",
    cancelButtonText: "Batal",
    confirmButtonColor: "#dc3545",
    reverseButtons: true,
  }).then((result) => {
    if (!result.isConfirmed) return;

    $.ajax({
      url: AR_URL.delete(id),
      method: "POST",
      data: { [arCsrfName]: arCsrfToken },
      processData: true,
      contentType: "application/x-www-form-urlencoded",
      headers: { "X-Requested-With": "XMLHttpRequest" },
      success(res) {
        if (res.csrf_token) arCsrfToken = res.csrf_token;
        bootstrap.Offcanvas.getInstance(
          document.getElementById("arOffcanvas"),
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

/* SUBMIT — STORE / UPDATE */
document.getElementById("arForm")?.addEventListener("submit", function (e) {
  e.preventDefault();
  const form = e.target;
  const mode = document.getElementById("arMode").value;
  const id = document.getElementById("arId").value;

  if (!form.checkValidity()) {
    form.classList.add("was-validated");
    return;
  }

  Swal.fire({
    title: mode === "edit" ? "Simpan Perubahan?" : "Simpan Analisis?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Simpan",
    cancelButtonText: "Batal",
    reverseButtons: true,
  }).then((result) => {
    if (!result.isConfirmed) return;

    const freshData = new FormData(form);
    freshData.append(arCsrfName, arCsrfToken);

    $.ajax({
      url: mode === "edit" ? AR_URL.update(id) : AR_URL.store,
      method: "POST",
      data: freshData,
      processData: false,
      contentType: false,
      headers: { "X-Requested-With": "XMLHttpRequest" },
      success(res) {
        if (res.csrf_token) arCsrfToken = res.csrf_token;
        bootstrap.Offcanvas.getInstance(
          document.getElementById("arOffcanvas"),
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
  enableAutoNumbering("arUraianPengendalian");
});

document.addEventListener("click", function (e) {
  if (e.target.closest("#arBtnEdit")) {
    arSetMode("edit");
  }

  if (e.target.closest("#arBtnBatal")) {
    arBatal();
  }

  if (e.target.closest("#arBtnDelete")) {
    arHapus();
  }
});
