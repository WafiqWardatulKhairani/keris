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

      document.getElementById("plPreviewNilai").textContent = data.nilai_risiko || 0;
      document.getElementById("plPreviewBadge").textContent = data.nama_selera || "";
      
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

      document.getElementById("plPreviewNilaiResidu").textContent = data.nilai_residu || 0;

      document.getElementById("plPreviewBadgeResidu").textContent = data.nama_selera_residu || "";
      
      applyBadgeColor(
        document.getElementById("plPreviewBadgeResidu"),
        data.warna_residu,
      );

      bootstrap.Offcanvas.getOrCreateInstance(
        document.getElementById("plOffcanvas"),
      ).show();
    })
    .catch(() => {
      Swal.fire("Error", "Gagal load detail", "error");
    });
}

function plAjukanKegiatan(idKegiatan) {
  Swal.fire({
    title: "Ajukan laporan ke ketua?",
    text: "Semua RTP pada kegiatan ini akan dikirim untuk validasi.",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Ya, Ajukan",
    cancelButtonText: "Batal",
  }).then((result) => {
    if (!result.isConfirmed) return;

    fetch(PL_URL.ajukan, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        id_kegiatan: idKegiatan,
      }),
    })
      .then((res) => res.json())
      .then((data) => {
        if (!data.success) {
          Swal.fire("Error", data.error || "Gagal mengajukan laporan", "error");
          return;
        }

        Swal.fire(
          "Berhasil",
          "Laporan berhasil diajukan ke ketua",
          "success",
        ).then(() => {
          location.reload();
        });
      })
      .catch(() => {
        Swal.fire("Error", "Terjadi kesalahan server", "error");
      });
  });
}

function plApproveKegiatan(idKegiatan) {
  Swal.fire({
    title: "Setujui laporan kegiatan?",
    text: "Semua RTP pada kegiatan ini akan disetujui.",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Ya, Setujui",
    cancelButtonText: "Batal",
  }).then((result) => {
    if (!result.isConfirmed) return;

    fetch(PL_URL.approveKegiatan(idKegiatan), {
      method: "POST",
    })
      .then((res) => res.json())
      .then((data) => {
        if (!data.success) {
          Swal.fire("Error", data.error || "Gagal approve", "error");
          return;
        }

        Swal.fire("Berhasil", "Laporan berhasil disetujui", "success").then(
          () => {
            location.reload();
          },
        );
      })
      .catch(() => {
        Swal.fire("Error", "Terjadi kesalahan server", "error");
      });
  });
}

function plRejectKegiatan(idKegiatan) {
  Swal.fire({
    title: "Reject laporan kegiatan?",
    input: "textarea",
    inputLabel: "Catatan Ketua",
    inputPlaceholder: "Wajib isi alasan reject...",
    inputAttributes: {
      "aria-label": "Catatan Ketua",
    },
    showCancelButton: true,
    confirmButtonText: "Reject",
    cancelButtonText: "Batal",
    confirmButtonColor: "#dc3545",

    inputValidator: (value) => {
      if (!value) {
        return "Catatan wajib diisi";
      }
    },
  }).then((result) => {
    if (!result.isConfirmed) return;

    fetch(PL_URL.rejectKegiatan(idKegiatan), {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        alasan: result.value,
      }),
    })
      .then((res) => res.json())
      .then((data) => {
        if (!data.success) {
          Swal.fire("Error", data.error || "Gagal reject", "error");
          return;
        }

        Swal.fire("Berhasil", "Laporan berhasil ditolak", "success").then(
          () => {
            location.reload();
          },
        );
      })
      .catch(() => {
        Swal.fire("Error", "Terjadi kesalahan server", "error");
      });
  });
}

function plShowCatatan(catatan) {
  Swal.fire({
    title: "Catatan Ketua",
    text: catatan,
    icon: "info",
    confirmButtonText: "Tutup",
  });
}

function plPrintReport(form = "form4") {
  const url = new URL(PL_URL.print, window.location.origin);

  const periode = document.getElementById("plCsPeriode");
  const type = document.getElementById("plCsType");
  const kegiatan = document.getElementById("plCsKegiatan");
  const status = document.getElementById("plStatusValidasi");

  url.searchParams.set("form", form);

  if (periode) {
    url.searchParams.set("periode", periode.value);
  }

  if (type) {
    url.searchParams.set("tipe_periode", type.value);
  }

  if (kegiatan) {
    url.searchParams.set("id_kegiatan", kegiatan.value);
  }

  if (status) {
    url.searchParams.set("status_validasi", status.value);
  }

  window.open(url.toString(), "_blank");
}
