document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("plContextSelectorForm");
  if (!form) return;

  const timSelect = document.getElementById("plCsTimKerja");
  const pengelolaSelect = document.getElementById("plCsPengelola");
  const kegiatanSelect = document.getElementById("plCsKegiatan");
  document
    .querySelectorAll('.pl-triwulan-card input[name="triwulan"]')
    .forEach((input) => {
      input.addEventListener("change", function () {
        document
          .querySelectorAll(".pl-triwulan-card")
          .forEach((card) => card.classList.remove("active"));

        this.closest(".pl-triwulan-card").classList.add("active");
      });
    });

  if (window.PL_CS_DATA) {
    const konteksMap = window.PL_CS_DATA.konteksMap || {};
    const listKegiatan = window.PL_CS_DATA.listKegiatan || [];

    // Ambil kegiatan yang sebelumnya dipilih
    let selectedKegiatan = window.PL_CS_DATA.selectedKegiatan || "";

    function filterPengelola() {
      const selectedTim = timSelect
        ? timSelect.value
        : window.PL_CS_DATA.activeTimId;

      const currentPengelola = pengelolaSelect ? pengelolaSelect.value : "";

      const currentKegiatan = kegiatanSelect?.value || selectedKegiatan;

      if (pengelolaSelect) {
        pengelolaSelect.innerHTML =
          '<option value="">– Pilih Pengelola –</option>';
      }

      if (kegiatanSelect) {
        kegiatanSelect.innerHTML = '<option value="">Semua Kegiatan</option>';
      }

      const addedPengelola = new Set();
      const addedKegiatan = new Set();

      Object.values(konteksMap).forEach((item) => {
        if (item.id_tim != selectedTim) return;

        if (
          item.pengelola_risiko_id &&
          !addedPengelola.has(item.pengelola_risiko_id)
        ) {
          addedPengelola.add(item.pengelola_risiko_id);

          if (pengelolaSelect) {
            const opt = document.createElement("option");
            opt.value = item.pengelola_risiko_id;
            opt.textContent = item.nama_pengelola;

            if (item.pengelola_risiko_id == currentPengelola) {
              opt.selected = true;
            }

            pengelolaSelect.appendChild(opt);
          }
        }
      });

      if (kegiatanSelect) {
        listKegiatan.forEach((item) => {
          if (item.id_tim != selectedTim) return;
          if (addedKegiatan.has(item.id_kegiatan)) return;

          addedKegiatan.add(item.id_kegiatan);

          const opt = document.createElement("option");
          opt.value = item.id_kegiatan;
          opt.textContent = item.nama_kegiatan;

          if (item.id_kegiatan == currentKegiatan) {
            opt.selected = true;
          }

          kegiatanSelect.appendChild(opt);
        });

        selectedKegiatan = kegiatanSelect.value;
      }
    }

    if (timSelect) {
      timSelect.addEventListener("change", () => {
        selectedKegiatan = "";
        filterPengelola();
      });
    }

    filterPengelola();
  }
});
