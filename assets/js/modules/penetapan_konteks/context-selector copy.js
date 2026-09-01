const ContextSelector = {
  elements: {},
  isFilterMode: false,

  init() {
    const form = document.getElementById("contextSelectorForm");
    if (!form) return;

    this.isFilterMode = form.method.toLowerCase() === "get";

    this.elements = {
      sk: document.getElementById("csSatuanKerja"),
      pg: document.getElementById("csPengelola"),
      kg: document.getElementById("csKegiatan"),
      ss: document.getElementById("csSasaran"),
      th: document.getElementById("csTahun"),
      id: document.getElementById("csIdKonteks"),
      form,
      btnReset: document.getElementById("csBtnReset"),
    };

    this.bindEvents();

    if (!this.isFilterMode) {
      this.resolveId();
    }
  },

  bindEvents() {
    const { sk, pg, kg, ss, th, form, btnReset } = this.elements;

    if (!this.isFilterMode) {
      [sk, pg, kg, ss, th].forEach((el) => {
        el.addEventListener("change", () => this.resolveId());
      });
    }

    form.addEventListener("submit", (e) => this.onSubmit(e));
    btnReset.addEventListener("click", () => this.onReset());
  },

  resolveId() {
    const { sk, pg, kg, ss, th, id } = this.elements;
    const map = window.CS_DATA?.konteksMap ?? {};

    const vSk = sk.value;
    const vPg = pg.value;
    const vKg = kg.value;
    const vSs = ss.value;
    const vTh = th.value;

    id.value = "";

    if (!vSk && !vPg && !vKg && !vSs && !vTh) return;

    for (const [konteksId, k] of Object.entries(map)) {
      if (vSk && String(k.id_satuan_kerja) !== String(vSk)) continue;
      if (vPg && String(k.pengelola_risiko_id) !== String(vPg)) continue;
      if (vKg && String(k.id_kegiatan) !== String(vKg)) continue;
      if (vSs && String(k.id_sasaran_strategis) !== String(vSs)) continue;
      if (vTh && String(k.tahun) !== String(vTh)) continue;
      id.value = konteksId;
      break;
    }
  },

  onSubmit(e) {
    if (this.isFilterMode) return; // GET biasa, biarkan submit

    const { id, sk, pg, kg, ss, th } = this.elements;
    const anyFilter = [sk, pg, kg, ss, th].some((el) => el.value);

    if (!anyFilter) {
      e.preventDefault();
      PkAlert.toast({
        text: "Pilih minimal satu filter terlebih dahulu.",
        icon: "warning",
      });
      return;
    }

    if (!id.value) {
      e.preventDefault();
      PkAlert.toast({ text: "Tidak ada konteks yang cocok.", icon: "error" });
    }
  },

  onReset() {
    const { sk, pg, kg, ss, th } = this.elements;

    if (this.isFilterMode) {
      // redirect ke URL bersih tanpa query params
      window.location.href = window.location.pathname;
      return;
    }

    [sk, pg, kg, ss, th].forEach((el) => (el.value = ""));
    if (this.elements.id) this.elements.id.value = "";

    fetch(window.CS_DATA.resetUrl, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        "X-CSRF-TOKEN": window.CS_DATA.csrfToken,
      },
      body: `id_konteks=&redirect=${encodeURIComponent(window.CS_DATA.currentUrl)}`,
    }).then(() => window.location.reload());
  },
};

document.addEventListener("DOMContentLoaded", () => ContextSelector.init());
