
/* URL & CSRF (di-inject dari view lewat window.AR_CONFIG) */
const AR_URL = window.AR_CONFIG?.url || {};
let arCsrfToken = window.AR_CONFIG?.csrf?.token || "";
const arCsrfName = window.AR_CONFIG?.csrf?.name || "";

/* MODE MANAGEMENT */
function arSetMode(mode) {
  const isView = mode === "view";
  const isEdit = mode === "edit";
  const isCreate = mode === "create";

  document.getElementById("arMode").value = mode;

  const fields = [
    "arKemungkinan",
    "arDampak",
    "arUraianPengendalian",
    "arEfektivitas",
  ];
  fields.forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.disabled = isView;
  });

  document.getElementById("arBtnEdit").classList.toggle("d-none", !isView);
  document.getElementById("arBtnBatal").classList.toggle("d-none", !isEdit);
  document.getElementById("arBtnSimpan").classList.toggle("d-none", isView);
  document.getElementById("arBtnTutup").classList.toggle("d-none", isEdit);
  document.getElementById("arBtnDelete").classList.toggle("d-none", !isView);

  document.getElementById("arOffcanvasTitle").textContent = isCreate
    ? "Tambah Analisis Risiko"
    : isEdit
      ? "Edit Analisis Risiko"
      : "Detail Analisis Risiko";
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
  set("arInfoPengelola", d.nama_pengelola);
  set("arInfoSasaran", d.sasaran_strategis);
  set(
    "arInfoProses",
    (d.kode_proses ? d.kode_proses + " — " : "") + (d.uraian_proses ?? ""),
  );
  set("arInfoSasaranKinerja", d.sasaran_kinerja);
  set("arInfoPernyataan", d.pernyataan_risiko);
  set("arInfoPenyebab", d.penyebab_risiko);
  set("arInfoDampak", d.dampak_risiko);
}

/* LOAD DETAIL PENILAIAN (view/edit mode) */
function arLoadDetail(idPenilaian) {
  return fetch(AR_URL.detail(idPenilaian))
    .then((r) => r.json())
    .then((d) => {
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
