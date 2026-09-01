document.addEventListener("DOMContentLoaded", function () {
  const typeEl = document.getElementById("plCsType");
  const single = document.getElementById("plSingle");
  const range = document.getElementById("plRange");
  const startEl = document.getElementById("plStart");
  const endEl = document.getElementById("plEnd");

  if (!typeEl) return;

  function setMonthPlaceholder(input, label) {
    input.addEventListener("focus", function () {
      input.type = "month";
    });
    input.addEventListener("blur", function () {
      if (!input.value) input.type = "text";
    });
    if (!input.value) {
      input.type = "text";
      input.placeholder = label;
    }
  }

  if (startEl) setMonthPlaceholder(startEl, "Pilih bulan awal");
  if (endEl) setMonthPlaceholder(endEl, "Pilih bulan akhir");

  function toggleMode() {
    const isRange = typeEl.value === "range";
    single.style.display = isRange ? "none" : "block";
    range.style.display = isRange ? "block" : "none";
  }

  typeEl.addEventListener("change", toggleMode);

  if (startEl && endEl) {
    startEl.addEventListener("change", function () {
      if (!startEl.value) return;
      const s = new Date(startEl.value);
      const exactEnd = new Date(s);
      exactEnd.setMonth(s.getMonth() + 2);
      const exactEndStr = exactEnd.toISOString().slice(0, 7);
      endEl.min = exactEndStr;
      endEl.max = exactEndStr;
      endEl.value = exactEndStr;
      endEl.type = "month";
    });

    endEl.addEventListener("change", function () {
      if (!startEl.value || !endEl.value) return;
      const s = new Date(startEl.value);
      const e = new Date(endEl.value);
      const diff =
        (e.getFullYear() - s.getFullYear()) * 12 +
        (e.getMonth() - s.getMonth());
      if (diff !== 2) {
        alert("Harus memilih rentang tepat 3 bulan");
        endEl.value = "";
        endEl.type = "text";
      }
    });
  }

  const timSelect = document.getElementById("plCsTimKerja");
  const pengelolaSelect = document.getElementById("plCsPengelola");
  const kegiatanSelect = document.getElementById("plCsKegiatan");

  if (window.PL_CS_DATA) {
    const konteksMap = window.PL_CS_DATA.konteksMap;
    const listKegiatan = window.PL_CS_DATA.listKegiatan || [];

    function filterPengelola() {
      const selectedTim = timSelect
        ? timSelect.value
        : window.PL_CS_DATA.activeTimId;

      // simpan selected lama
      const currentPengelola = pengelolaSelect ? pengelolaSelect.value : "";
      const currentKegiatan = kegiatanSelect ? kegiatanSelect.value : "";

      // reset pengelola
      if (pengelolaSelect) {
        pengelolaSelect.innerHTML =
          '<option value="">– Pilih Pengelola –</option>';
      }

      // reset kegiatan
      if (kegiatanSelect) {
        kegiatanSelect.innerHTML = '<option value="">Semua Kegiatan</option>';
      }

      const addedPengelola = new Set();
      const addedKegiatan = new Set();

      Object.values(konteksMap).forEach((item) => {
        // FILTER BERDASARKAN TIM
        if (item.id_tim != selectedTim) return;

        // PENGELOLA
        if (
          item.pengelola_risiko_id &&
          !addedPengelola.has(item.pengelola_risiko_id)
        ) {
          addedPengelola.add(item.pengelola_risiko_id);

          const opt = document.createElement("option");

          opt.value = item.pengelola_risiko_id;
          opt.textContent = item.nama_pengelola;

          if (item.pengelola_risiko_id == currentPengelola) {
            opt.selected = true;
          }

          if (pengelolaSelect) {
            pengelolaSelect.appendChild(opt);
          }
        }
      });

      if (kegiatanSelect) {
        listKegiatan.forEach((item) => {
          if (item.id_tim != selectedTim) return;

          if (addedKegiatan.has(item.id_kegiatan)) return;

          addedKegiatan.add(item.id_kegiatan);

          const kegiatanOpt = document.createElement("option");

          kegiatanOpt.value = item.id_kegiatan;
          kegiatanOpt.textContent = item.nama_kegiatan;

          if (item.id_kegiatan == currentKegiatan) {
            kegiatanOpt.selected = true;
          }

          kegiatanSelect.appendChild(kegiatanOpt);
        });
      }
    }

    if (timSelect) {
      timSelect.addEventListener("change", filterPengelola);
    }

    filterPengelola();
  }

  toggleMode();

  // SAVE SCROLL POSITION
  const form = document.getElementById("plContextSelectorForm");

  if (form) {
    form.addEventListener("submit", function () {
      sessionStorage.setItem("plScrollY", window.scrollY);
    });
  }

  // RESTORE AFTER PAGE FULLY LOADED
  window.addEventListener("load", function () {
    const savedScroll = sessionStorage.getItem("plScrollY");

    if (savedScroll !== null) {
      setTimeout(() => {
        window.scrollTo(0, parseInt(savedScroll, 10));
        sessionStorage.removeItem("plScrollY");
      }, 50);
    }
  });  
});

