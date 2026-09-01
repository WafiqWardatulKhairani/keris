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
    this.filterDropdownOptions(); // 🔥 penting
    this.resolveId();
  },

  bindEvents() {
    const { sk, pg, kg, th, form, btnReset } = this.elements;

    // 🔥 sama persis seperti RTP
    if (sk) {
      sk.addEventListener("change", () => {
        if (pg) pg.value = "";
        if (kg) kg.value = "";
        if (th) th.value = "";

        this.filterDropdownOptions();
        this.resolveId();
      });
    }

    [pg, kg, th].forEach((el) => {
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

    if (btnReset) {
      btnReset.style.display = hasActive || hasAnyFilter ? "" : "none";
    }

    if (!hasAnyFilter) return;

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

  // 🔥 INI YANG HILANG SEBELUMNYA
  filterDropdownOptions() {
    const { sk, pg, kg, th } = this.elements;
    const map = this.map;

    const selectedTim = sk?.value;

    const reset = (el) => {
      if (!el) return;
      el.innerHTML = `<option value="">– Pilih –</option>`;
    };

    reset(pg);
    reset(kg);
    reset(th);

    if (!selectedTim) return;

    const pgSet = new Map();
    const kgSet = new Map();
    const thSet = new Set();

    Object.values(map).forEach((k) => {
      if (String(k.id_tim) !== String(selectedTim)) return;

      if (k.pengelola_risiko_id) {
        pgSet.set(k.pengelola_risiko_id, k.nama_pengelola);
      }

      if (k.id_kegiatan) {
        kgSet.set(k.id_kegiatan, k.nama_kegiatan);
      }

      if (k.tahun) {
        thSet.add(k.tahun);
      }
    });

    pgSet.forEach((val, key) => {
      const opt = document.createElement("option");
      opt.value = key;
      opt.textContent = val;
      pg.appendChild(opt);
    });

    kgSet.forEach((val, key) => {
      const opt = document.createElement("option");
      opt.value = key;
      opt.textContent = val;
      kg.appendChild(opt);
    });

    thSet.forEach((tahun) => {
      const opt = document.createElement("option");
      opt.value = tahun;
      opt.textContent = tahun;
      th.appendChild(opt);
    });

    // restore dari URL (penting untuk reload/pagination)
    const params = new URLSearchParams(window.location.search);
    if (pg && params.get("pg")) pg.value = params.get("pg");
    if (kg && params.get("kg")) kg.value = params.get("kg");
    if (th && params.get("th")) th.value = params.get("th");
  },
};

document.addEventListener("DOMContentLoaded", () => PmContextSelector.init());
