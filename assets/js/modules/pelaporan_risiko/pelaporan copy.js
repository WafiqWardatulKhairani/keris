const USER = window.APP_USER || {};
const PL_URL = window.PL_CONFIG?.url || {};

let plCsrfToken = window.PL_CONFIG?.csrf?.token || "";
const plCsrfName = window.PL_CONFIG?.csrf?.name || "";
function setText(id, value) {
  const el = document.getElementById(id);
  if (el) el.textContent = value ?? "-";
}

function setFormattedText(id, value) {
  const el = document.getElementById(id);

  if (!el) return;

  if (!value) {
    el.innerHTML = "-";
    return;
  }

  let formatted = value;

  //formatted = formatted.replace(/(\d+\.)/g, "<br>$1");
  //formatted = formatted.replace(/(?:^|\s)(\d+\.)/g, "<br>$1");
  formatted = formatted.replace(/(\d+\.)/g, "<br>$1");
  formatted = formatted.replace(/^<br>/, "");

  el.innerHTML = formatted;
}

let currentId = null;

// INIT
document.addEventListener("DOMContentLoaded", function () {
  const role = USER.role || "operator";

  // hanya tampilkan footer untuk ketua
  const footer = document.getElementById("plFooterKetua");
  if (footer) {
    footer.style.display = role === "ketua" ? "block" : "none";
  }
});

//CLICK ROW
document.addEventListener("click", function (e) {
  const row = e.target.closest(".pl-row");
  if (!row) return;

  const id = row.dataset.id;
  if (!id) return;

  openPelaporanDetail(id);
});

function applyBadgeColor(el, warna) {
  if (!el) return;

  el.className = "ar-preview-badge";

  switch (warna) {
    case "biru":
      el.classList.add("bg-primary", "text-white");
      break;

    case "hijau":
      el.classList.add("bg-success", "text-white");
      break;

    case "kuning":
      el.classList.add("bg-warning", "text-dark");
      break;

    case "oranye":
      el.classList.add("bg-orange", "text-white");
      break;

    case "merah":
      el.classList.add("bg-danger", "text-white");
      break;

    default:
      el.classList.add("bg-secondary", "text-white");
      break;
  }
}

// OPEN DETAIL
function openPelaporanDetail(id) {
  currentId = id;

  fetch(PL_URL.detail(id))
    .then((res) => res.json())
    .then((data) => {
      setText("plInfoTahun", data.tahun);
      setText("plInfoTimKerja", data.nama_tim);
      setText("plInfoKegiatan", data.nama_kegiatan);
      setText("plInfoPengelola", data.nama_pengelola);
      setText("plInfoSasaran", data.sasaran_strategis);

      setText(
        "plInfoProses",
        (data.kode_proses ? data.kode_proses + " — " : "") +
          (data.uraian_proses ?? ""),
      );

      setText("plInfoSasaranKinerja", data.sasaran_kinerja);
      setText("plInfoRisiko", data.pernyataan_risiko);
      setFormattedText("plInfoPenyebab", data.penyebab_risiko);
      setFormattedText("plInfoDampak", data.dampak_risiko);

      setText("plInfoProb", data.level_kemungkinan);
      setText("plInfoImpact", data.level_dampak);

      document.getElementById("plPreviewNilai").textContent =
        data.nilai_risiko || 0;
      document.getElementById("plPreviewBadge").textContent =
        data.nama_selera || "";
      
      applyBadgeColor(
        document.getElementById("plPreviewBadge"),
        data.warna_risiko,
      );

      setFormattedText("plInfoPengendalian", data.uraian_pengendalian);
      setText("plInfoEfektivitas", data.efektivitas);

      setText("plInfoRtp", data.uraian_rtp);
      setText("plTargetOutput", data.target_output);
      setText("plTargetWaktu", data.target_waktu);

      setText("plRealisasiOutput", data.realisasi_output);
      setText("plRealisasiWaktu", data.realisasi_waktu);
      setText("plStatus", data.status);

      const buktiEl = document.getElementById("plLinkBukti");
      const buktiRow = document.getElementById("plRowBukti");

      if (buktiEl && buktiRow) {
        if (data.link_bukti) {
          buktiEl.href = data.link_bukti;
          buktiRow.style.display = "flex";
        } else {
          buktiRow.style.display = "none";
        }
      }

      setText("plInfoProbResidu", data.level_kemungkinan_residu);

      setText("plInfoImpactResidu", data.level_dampak_residu);

      document.getElementById("plPreviewNilaiResidu").textContent =
        data.nilai_residu || 0;

      document.getElementById("plPreviewBadgeResidu").textContent =
        data.nama_selera_residu || "";
      
      applyBadgeColor(
        document.getElementById("plPreviewBadgeResidu"),
        data.warna_residu,
      );

      document.getElementById("plCatatan").value = "";

      bootstrap.Offcanvas.getOrCreateInstance(
        document.getElementById("plOffcanvas"),
      ).show();
    })
    .catch(() => {
      Swal.fire("Error", "Gagal load detail", "error");
    });
}
//   APPROVE
function plApprove() {
  if (!currentId) return;

  Swal.fire({
    title: "Approve data ini?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Ya",
  }).then((res) => {
    if (!res.isConfirmed) return;

    fetch(PL_URL.approve(currentId), {
      method: "POST",
    })
      .then(() => {
        Swal.fire("Berhasil", "Data di-approve", "success").then(() =>
          location.reload(),
        );
      })
      .catch(() => Swal.fire("Error", "Gagal approve", "error"));
  });
}

//   REJECT
function plReject() {
  if (!currentId) return;

  const alasan = document.getElementById("plCatatan").value.trim();

  if (!alasan) {
    Swal.fire("Warning", "Catatan wajib diisi", "warning");
    return;
  }

  fetch(PL_URL.reject(currentId), {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ alasan }),
  })
    .then(() => {
      Swal.fire("Berhasil", "Data ditolak", "success").then(() =>
        location.reload(),
      );
    })
    .catch(() => Swal.fire("Error", "Gagal reject", "error"));
}
