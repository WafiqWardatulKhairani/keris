const PmContextSelector = {
  elements: {},
  map: {},

  init() {
    const form = document.getElementById("pmContextSelectorForm");
    if (!form) return;

    this.map = window.PM_CS_DATA?.konteksMap ?? {};

    this.elements = {
      form,
      sk: document.getElementById("pmCsTimKerja"),
      pg: document.getElementById("pmCsPengelola"),
      kg: document.getElementById("pmCsKegiatan"),
      th: document.getElementById("pmCsTahun"),
      idEl: document.getElementById("pmCsIdKonteks"),
      btnApply: document.getElementById("pmCsBtnApply"),
      btnReset: document.getElementById("pmCsBtnReset"),
    };

    this.bindEvents();
    this.resolveId();
  },

  bindEvents() {
    const { sk, pg, kg, th, form, btnReset } = this.elements;

    [sk, pg, kg, th].forEach((el) => {
      if (el) el.addEventListener("change", () => this.resolveId());
    });

    form.addEventListener("submit", (e) => this.onSubmit(e));

    btnReset?.addEventListener("click", () => {
      document.getElementById("pmResetForm")?.submit();
    });
  },

  resolveId() {
    const { sk, pg, kg, th, idEl, btnApply, btnReset } = this.elements;

    const vSk = sk?.value ?? "";
    const vPg = pg?.value ?? "";
    const vKg = kg?.value ?? "";
    const vTh = th?.value ?? "";

    idEl.value = "";
    btnApply.disabled = true;
    btnApply.title = "Pilih filter terlebih dahulu";

    const hasAnyFilter = vSk || vPg || vKg || vTh;
    const hasActive = window.PM_CS_DATA?.hasActive ?? false;

    // tampilkan reset kalau ada filter / aktif
    if (btnReset) {
      btnReset.style.display = hasActive || hasAnyFilter ? "" : "none";
    }

    if (!hasAnyFilter) return;

    // cari konteks yang cocok
    for (const [id, k] of Object.entries(this.map)) {
      const matchSk = !vSk || String(k.id_tim) === vSk;
      const matchPg = !vPg || String(k.pengelola_risiko_id) === vPg;
      const matchKg = !vKg || String(k.id_kegiatan) === vKg;
      const matchTh = !vTh || String(k.tahun) === vTh;

      if (matchSk && matchPg && matchKg && matchTh) {
        idEl.value = id;
        btnApply.disabled = false;
        btnApply.title = "Terapkan konteks ini";
        return;
      }
    }

    // kalau tidak ketemu
    btnApply.title = "Tidak ada konteks yang cocok";
  },

  onSubmit(e) {
    const { idEl } = this.elements;

    if (!idEl.value) {
      e.preventDefault();
      PkAlert.toast({
        text: "Tidak ada konteks yang cocok.",
        icon: "error",
      });
    }
  },
};

document.addEventListener("DOMContentLoaded", () => PmContextSelector.init());
