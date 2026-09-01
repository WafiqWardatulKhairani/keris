const ContextSelector = {
  elements: {},
  isFilterMode: false,

  init() {
    const form = document.getElementById("contextSelectorForm");
    if (!form) return;

    this.isFilterMode = form.method.toLowerCase() === "get";

    this.elements = {
      sk: document.getElementById("csTimKerja"),
      pg: document.getElementById("csPengelola"),
      kg: document.getElementById("csKegiatan"),
      ss: document.getElementById("csSasaran"),
      th: document.getElementById("csTahun"),
      id: document.getElementById("csIdKonteks"),
      form,
      btnReset: document.getElementById("csBtnReset"),
    };

    this.bindEvents();
    this.filterDropdownOptions();

    if (!this.isFilterMode) {
      this.resolveId();
    }
  },

  bindEvents() {
    const { sk, pg, kg, ss, th, form, btnReset } = this.elements;

    // Tim kerja → trigger filter dropdown
    if (sk) {
      sk.addEventListener("change", () => {
        if (pg) pg.value = "";
        if (kg) kg.value = "";
        if (ss) ss.value = "";
        if (th) th.value = "";

        this.filterDropdownOptions();

        if (!this.isFilterMode) {
          this.resolveId();
        }
      });
    }

    // dropdown lain
    [pg, kg, ss, th].forEach((el) => {
      if (el) {
        el.addEventListener("change", () => {
          if (!this.isFilterMode) {
            this.resolveId();
          }
        });
      }
    });

    if (form) {
      form.addEventListener("submit", (e) => this.onSubmit(e));
    }

    if (btnReset) {
      btnReset.addEventListener("click", () => this.onReset());
    }
  },

  resolveId() {
    const { sk, pg, kg, ss, th, id } = this.elements;
    const map = window.CS_DATA?.konteksMap ?? {};

    if (!id) return;

    const vSk = sk?.value;
    const vPg = pg?.value;
    const vKg = kg?.value;
    const vSs = ss?.value;
    const vTh = th?.value;

    id.value = "";

    if (!vSk && !vPg && !vKg && !vSs && !vTh) return;

    const matches = [];

    for (const [konteksId, k] of Object.entries(map)) {
      if (vSk && String(k.id_tim) !== String(vSk)) continue;
      if (vPg && String(k.pengelola_risiko_id) !== String(vPg)) continue;
      if (vKg && String(k.id_kegiatan) !== String(vKg)) continue;
      if (vSs && String(k.id_sasaran_strategis) !== String(vSs)) continue;
      if (vTh && String(k.tahun) !== String(vTh)) continue;

      matches.push(konteksId);
    }

    if (matches.length === 1) {
      id.value = matches[0];
    } else {
      id.value = "";
    }
  },

  filterDropdownOptions() {
    const { sk, pg, kg, ss, th } = this.elements;
    const map = window.CS_DATA?.konteksMap ?? {};

    const selectedTim = sk?.value;

    // DEBUG
    console.log("Selected Tim:", selectedTim);
    console.log("Map:", map);

    // helper reset dropdown
    const reset = (el) => {
      if (!el) return;

      const placeholder = el.getAttribute("data-placeholder") || "– Pilih –";
      el.innerHTML = `<option value="">${placeholder}</option>`;
    };

    reset(pg);
    reset(kg);
    reset(ss);
    reset(th);

    if (!selectedTim) return;

    const pgSet = new Map();
    const kgSet = new Map();
    const ssSet = new Map();
    const thSet = new Set();

    Object.values(map).forEach((k) => {
      if (String(k.id_tim) !== String(selectedTim)) return;

      if (k.pengelola_risiko_id) {
        pgSet.set(k.pengelola_risiko_id, k.nama_pengelola);
      }

      if (k.id_kegiatan) {
        kgSet.set(k.id_kegiatan, k.nama_kegiatan);
      }

      if (k.id_sasaran_strategis) {
        ssSet.set(k.id_sasaran_strategis, k.uraian_sasaran);
      }

      if (k.tahun) {
        thSet.add(k.tahun);
      }
    });

    const append = (el, mapOrSet) => {
      if (!el) return;

      mapOrSet.forEach((val, key) => {
        const opt = document.createElement("option");
        opt.value = key ?? val;
        opt.textContent = val ?? key;
        el.appendChild(opt);
      });
    };

    append(pg, pgSet);
    append(kg, kgSet);
    append(ss, ssSet);

    thSet.forEach((tahun) => {
      const opt = document.createElement("option");
      opt.value = tahun;
      opt.textContent = tahun;
      th.appendChild(opt);
    });
  },

  onSubmit(e) {
    if (this.isFilterMode) return;

    const { id, sk, pg, kg, ss, th } = this.elements;

    const anyFilter = [sk, pg, kg, ss, th].some((el) => el && el.value);

    if (!anyFilter) {
      e.preventDefault();
      PkAlert.toast({
        text: "Pilih minimal satu filter terlebih dahulu.",
        icon: "warning",
      });
      return;
    }

    if (!id?.value) {
      e.preventDefault();
      PkAlert.toast({
        text: "Tidak ada konteks yang cocok.",
        icon: "error",
      });
    }
  },

  onReset() {
    const { sk, pg, kg, ss, th } = this.elements;

    if (this.isFilterMode) {
      window.location.href = window.location.pathname;
      return;
    }

    [sk, pg, kg, ss, th].forEach((el) => {
      if (el) el.value = "";
    });

    if (this.elements.id) this.elements.id.value = "";

    fetch(window.CS_DATA.resetUrl, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        "X-CSRF-TOKEN": window.CS_DATA.csrfToken,
      },
      body: `id_konteks=&redirect=${encodeURIComponent(
        window.CS_DATA.currentUrl,
      )}`,
    }).then(() => window.location.reload());
  },
};

document.addEventListener("DOMContentLoaded", () => ContextSelector.init());
