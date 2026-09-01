
console.log("Rencana.js loaded");
/* URL & CSRF */
let RTP_CAN_EDIT = false;
const USER = window.APP_USER || {};
const RTP_URL = window.RTP_CONFIG?.url || {};
let rtpCsrfToken = window.RTP_CONFIG?.csrf?.token || "";
const rtpCsrfName = window.RTP_CONFIG?.csrf?.name || "csrf_token";

/* ================= RBAC ================= */
function rtpCanEdit(data) {
  if (!USER) return false;

  if (USER.role === "admin") return true;
  if (USER.role === "ketua") return false;

  if (USER.role === "operator") {
    return String(USER.id_tim) === String(data.id_tim);
  }

  return false;
}

/* ================= MODE ================= */
function rtpSetMode(mode) {
  const isView = mode === "view";
  const isEdit = mode === "edit";
  const isCreate = mode === "create";

  document.getElementById("rtpMode").value = mode;

  const createBox = document.getElementById("rtpCreateContainer");
  const timelineBox = document.getElementById("rtpTimelineContainer");

  createBox.classList.toggle("d-none", isView);
  timelineBox.classList.toggle("d-none", !isView);

  const disableAll = isView && !RTP_CAN_EDIT;

  document
    .querySelectorAll(
      "#rtpOffcanvas input, #rtpOffcanvas textarea, #rtpOffcanvas select",
    )
    .forEach((el) => {
      if (el.id === "rtpMode") return;
      el.disabled = disableAll;
    });

  document
    .querySelectorAll(
      "#rtpCreateContainer textarea, #rtpCreateContainer input, #rtpCreateContainer select",
    )
    .forEach((el) => {
      el.disabled = isView && !RTP_CAN_EDIT;
    });

  document
    .getElementById("rtpBtnEdit")
    .classList.toggle("d-none", !isView || !RTP_CAN_EDIT);

  document
    .getElementById("rtpBtnHapus")
    .classList.toggle("d-none", !isView || !RTP_CAN_EDIT);

  document
    .getElementById("rtpBtnBatal")
    .classList.toggle("d-none", !isEdit || !RTP_CAN_EDIT);

  document
    .getElementById("rtpBtnSimpan")
    .classList.toggle("d-none", isView || !RTP_CAN_EDIT);

  document.getElementById("rtpBtnTutup").classList.toggle("d-none", isEdit);

  document.getElementById("rtpAddWrapper")?.classList.toggle("d-none", isView);

  document.getElementById("rtpOffcanvasTitle").textContent = isCreate
    ? "Tambah RTP"
    : isEdit
      ? "Edit RTP"
      : "Detail RTP";

  if (isCreate) {
    document
      .querySelectorAll(
        "#rtpOffcanvas input, #rtpOffcanvas textarea, #rtpOffcanvas select",
      )
      .forEach((el) => (el.disabled = false));
  }

  // pastikan hidden field tidak pernah disabled
  document.getElementById("rtpIdEvaluasi").disabled = false;
}

/* ================= FORMAT TEXT ================= */
function rtpFormatText(text) {
  if (!text) return "-";
  return text
    .split(/\n|\r/)
    .filter((l) => l.trim())
    .map((l) => `<div>${l}</div>`)
    .join("");
}

/* ================= RESET ================= */
function rtpResetForm() {
  document.getElementById("rtpForm").reset();
  document.getElementById("rtpCreateContainer")?.replaceChildren();
  document.getElementById("rtpTimelineContainer")?.replaceChildren();

  [
    "rtpInfoTahun",
    "rtpInfoTimKerja",
    "rtpInfoKegiatan",
    "rtpInfoPengelola",
    "rtpInfoSasaran",
    "rtpInfoProses",
    "rtpInfoPernyataan",
    "rtpInfoPenyebab",
    "rtpInfoDampakRisiko",
    "rtpInfoProb",
    "rtpInfoImpact",
    "rtpInfoPenanggungjawab",
  ].forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.textContent = "-";
  });

  rtpResetResidu();
}

/* ================= POPULATE ================= */
function rtpPopulateInfo(d) {
  const set = (id, val) => {
    const el = document.getElementById(id);
    if (el) el.textContent = val || "-";
  };

  set("rtpInfoTahun", d.tahun);
  set("rtpInfoTimKerja", d.nama_tim);
  set("rtpInfoKegiatan", d.nama_kegiatan);
  set("rtpInfoPengelola", d.nama_pengelola);
  set("rtpInfoSasaran", d.sasaran_strategis);

  set(
    "rtpInfoProses",
    (d.kode_proses ? d.kode_proses + " — " : "") + (d.uraian_proses ?? ""),
  );

  set("rtpInfoPernyataan", d.pernyataan_risiko);

  document.getElementById("rtpInfoPenyebab").innerHTML = rtpFormatText(
    d.penyebab_risiko,
  );

  document.getElementById("rtpInfoDampakRisiko").innerHTML = rtpFormatText(
    d.dampak_risiko,
  );

  set("rtpInfoProb", d.level_kemungkinan);
  set("rtpInfoImpact", d.level_dampak);

  set("rtpInfoPenanggungjawab", d.nama_tim ? "Ketua " + d.nama_tim : "-");

  // preview skor (SAMAKAN evaluasi)
  const nilaiEl = document.getElementById("rtpPreviewNilai");
  const badgeEl = document.getElementById("rtpPreviewBadge");

  if (nilaiEl) {
    nilaiEl.textContent = d.nilai_risiko || "0";
    nilaiEl.style.color = d.warna_risiko || "";
  }

  if (badgeEl) {
    const warnaMap = {
      biru: "#0d6efd",
      hijau: "#198754",
      kuning: "#ffc107",
      oranye: "#fd7e14",
      merah: "#dc3545",
    };

    badgeEl.textContent = d.nama_selera || "";

    const warna =
      warnaMap[(d.warna_selera || "").toLowerCase()] ||
      d.warna_selera ||
      "#6c757d";

    badgeEl.style.backgroundColor = warna;
    badgeEl.style.color = "#fff";

    console.log("WARNA BADGE =", warna);
  }

  const tindakanEl = document.getElementById("rtpPreviewTindakan");

  if (tindakanEl) {
    tindakanEl.textContent = d.tindakan_selera || "";
  }

  RTP_CAN_EDIT = rtpCanEdit(d);
}

function rtpAddCard(data = {}) {
  const tpl = document.getElementById("rtpCardTemplate");
  const container = document.getElementById("rtpCreateContainer");

  if (!tpl || !container) return;

  const node = tpl.content.cloneNode(true);

  const textarea = node.querySelector("textarea[name='uraian_rtp[]']");
  const output = node.querySelector('input[name="target_output[]"]');
  const waktu = node.querySelector('input[name="target_waktu[]"]');

  if (textarea) textarea.value = data.uraian_rtp || "";
  if (output) output.value = data.target_output || "";
  if (waktu) {
    waktu.value = data.target_waktu ? data.target_waktu.substring(0, 7) : "";
  }

  node
    .querySelector(".rtp-card-remove")
    ?.addEventListener("click", function () {
      this.closest(".rtp-card").remove();
    });

  container.appendChild(node);
}

function rtpLoadDetail(idRtp) {
  return fetch(RTP_URL.detail(idRtp))
    .then((r) => r.json())
    .then((d) => {
      console.log("USER", USER);
      console.log("DATA", d);
      console.log("CAN EDIT?", rtpCanEdit(d));
      document.getElementById("rtpId").value = d.id_rtp;
      document.getElementById("rtpIdEvaluasi").value = d.id_evaluasi;

      // ✅ SET RESIDU DARI RTP LIST
      const selK = document.getElementById("rtpKemungkinanResidu");
      const selD = document.getElementById("rtpDampakResidu");

      rtpPopulateInfo(d);

      // ✅ SET RESIDU HARUS SETELAH populate
      if (d.rtp_list && d.rtp_list.length > 0) {
        const first = d.rtp_list[0];

        if (selK) selK.value = first.id_kemungkinan_residu ?? "";
        if (selD) selD.value = first.id_dampak_residu ?? "";
      }

      rtpHitungResidu();

      document.getElementById("rtpTimelineContainer")?.replaceChildren();

      // timeline (view)
      if (d.rtp_list && d.rtp_list.length > 0) {
        rtpRenderTimeline(d.rtp_list);
      }

      // cards (edit)
      const container = document.getElementById("rtpCreateContainer");
      container.innerHTML = "";

      if (d.rtp_list && d.rtp_list.length > 0) {
        d.rtp_list.forEach((rtp) => rtpAddCard(rtp));
      } else {
        rtpAddCard();
      }

      return d;
    });
}

/* ================= RESIDU ================= */
function rtpHitungResidu() {
  const k = document.getElementById("rtpKemungkinanResidu")?.value;
  const d = document.getElementById("rtpDampakResidu")?.value;

  if (!k || !d) return rtpResetResidu();

  fetch(RTP_URL.preview, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
      "X-Requested-With": "XMLHttpRequest",
    },
    body: `id_kemungkinan=${k}&id_dampak=${d}&${rtpCsrfName}=${rtpCsrfToken}`,
  })
    .then((r) => r.json())
    .then((res) => {
      if (res.csrf_token) rtpCsrfToken = res.csrf_token;

      if (res.status !== "success") return rtpResetResidu();

      document.getElementById("rtpResiduNilai").textContent = res.nilai_risiko;
      document.getElementById("rtpResiduNilai").style.color = res.warna;

      const badge = document.getElementById("rtpResiduBadge");

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

      badge.textContent = res.nama_selera || "";
      badge.style.backgroundColor = warna;
      badge.style.color = "#fff";

      const tindakan = document.getElementById("rtpResiduTindakan");

      if (tindakan) {
        tindakan.textContent = res.tindakan || "";
      }
    });
}

function rtpResetResidu() {
  const nilai = document.getElementById("rtpResiduNilai");
  const badge = document.getElementById("rtpResiduBadge");

  if (nilai) {
    nilai.textContent = "0";
    nilai.style.color = "";
  }

  if (badge) {
    badge.textContent = "";
    badge.style.backgroundColor = "";
    badge.style.color = "";
  }
}

function rtpRenderTimeline(list) {
  const container = document.getElementById("rtpTimelineContainer");
  if (!container) return;

  const wrapper = document.createElement("div");
  wrapper.className = "rtp-timeline";

  list.forEach((r, i) => {
    const item = document.createElement("div");
    item.className = "rtp-timeline-item";

    item.innerHTML = `
      <div class="rtp-timeline-row">
        <div class="rtp-timeline-content">
          <div><span class="rtp-timeline-label">Output :</span> ${r.target_output ?? "-"}</div>
          <div><span class="rtp-timeline-label">Target :</span> ${r.target_waktu ?? "-"}</div>
        </div>
      </div>
    `;

    wrapper.appendChild(item);
  });

  container.appendChild(wrapper);
}

function rtpBatal() {
  const mode = document.getElementById("rtpMode").value;

  if (mode === "edit") {
    rtpLoadDetail(document.getElementById("rtpId").value).then(() => {
      rtpSetMode("view");
    });
  } else {
    rtpResetForm();
    rtpSetMode("view");
  }
}


/* ================= CLICK ROW ================= */
document.addEventListener("click", function (e) {
  if (e.target.closest("a, button")) return;

  const row = e.target.closest("tr[data-rtp]");

  if (!row) return;

  console.log("ROW CLICKED", row);

  const idRtp = row.dataset.rtp || "";
  const idEvaluasi = row.dataset.idEvaluasi || "";

  const offcanvasEl = document.getElementById("rtpOffcanvas");
  if (!offcanvasEl) {
    console.error("Offcanvas tidak ditemukan");
    return;
  }

  const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);

  rtpResetForm();
  offcanvas.show();

  if (!idRtp) {
    if (!idEvaluasi) return;

    fetch(RTP_URL.detailEvaluasi(idEvaluasi))
      .then((r) => r.json())
      .then((d) => {
        document.getElementById("rtpIdEvaluasi").value = d.id_evaluasi;
        console.log("USER", USER);
        console.log("DATA (CREATE)", d);
        console.log("CAN EDIT?", rtpCanEdit(d));
        console.log("ID EVALUASI DIKIRIM:", d.id_evaluasi);
          
        rtpPopulateInfo(d);
        rtpSetMode("create");
        rtpAddCard();
        rtpResetResidu();
      });

    return;
  }

  rtpLoadDetail(idRtp).then(() => {
    rtpSetMode("view");
  });
});

document.addEventListener("DOMContentLoaded", function () {
  document
    .getElementById("rtpKemungkinanResidu")
    ?.addEventListener("change", rtpHitungResidu);

  document
    .getElementById("rtpDampakResidu")
    ?.addEventListener("change", rtpHitungResidu);

  document.getElementById("rtpAddBtn")?.addEventListener("click", function () {
    rtpAddCard();
  });

  document.getElementById("rtpForm")?.addEventListener("submit", function (e) {
    e.preventDefault();

    console.log("SUBMIT KE-TRIGGER");

    const mode = document.getElementById("rtpMode").value;
    const id = document.getElementById("rtpId").value;

    let url = "";
    if (mode === "create") {
      url = RTP_URL.store;
    } else if (mode === "edit") {
      url = RTP_URL.update(id);
    }

    const formData = new FormData(this);
    formData.append(rtpCsrfName, rtpCsrfToken);

    fetch(url, {
      method: "POST",
      body: formData,
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    })
      .then((res) => res.json())
      .then((res) => {
        console.log("RESPONSE:", res);

        if (res.csrf_token) rtpCsrfToken = res.csrf_token;

        if (res.status !== "success") {
          alert("Gagal menyimpan data");
          return;
        }

        location.reload();
      })
      .catch((err) => {
        console.error("ERROR SIMPAN:", err);
      });
  });  
});

