const KonteksModule = {
  init() {
    console.log("Konteks module loaded");

    this.initSelect2();
    //this.initStrukturOrganisasi();
    this.initKabKotaCombobox();
    this.initTimCombobox();
    this.initTahunCombobox();
    this.initSasaranCombobox();
    this.initPeraturanCombobox();
    this.initPemangkuCombobox();
    this.initFormSubmit();
    this.initPreventEnterSubmit();
  },

  // SELECT2
  initSelect2() {
    const offcanvas = document.getElementById("offcanvasKonteks");
    if (!offcanvas) return;

    offcanvas.addEventListener("shown.bs.offcanvas", function () {
      KonteksModule.initStrukturOrganisasi();
    });

    offcanvas.addEventListener("shown.bs.offcanvas", function () {
      $(".pk-select-search").each(function () {
        if (!$(this).hasClass("select2-hidden-accessible")) {
          $(this).select2({
            width: "100%",
            dropdownParent: $("#offcanvasKonteks"),
            placeholder: "Pilih...",
            minimumResultsForSearch: 0,
          });
        }
      });
    });
  },

  // STRUKTUR ORGANISASI
  initStrukturOrganisasi() {
    const radios = document.querySelectorAll('input[name="level_struktur"]');
    const kabWrapper = document.getElementById("pkKabKotaWrapper");

    if (!radios.length) return;

    radios.forEach((radio) => {
      radio.addEventListener("change", function () {
        if (this.value === "provinsi") {
          kabWrapper.style.display = "none";

          KonteksModule.clearKabKota();
          KonteksModule.clearPengelola();
          KonteksModule.loadProvinsiPemilik();
        }

        if (this.value === "kabkota") {
          kabWrapper.style.display = "block";

          KonteksModule.clearPemilik();
          KonteksModule.clearPengelola();
        }
      });
    });

    this.loadProvinsiPemilik();
  },

  // KAB/KOTA COMBOBOX
  initKabKotaCombobox() {
    Combobox.init({
      boxId: "pkKabKotaBox",
      inputId: "pkKabKotaInput",
      hiddenId: "pkKabKotaValue",
      optionsSelector: ".pk-option",
    });
  },

  // PEMILIK RISIKO
  loadProvinsiPemilik() {
    $.get("/penetapan-konteks/konteks/get-pemilik-provinsi", (res) => {
      if (!res) return;

      this.setPemilikRisiko(res);
    });
  },

  setPemilikRisiko(data) {
    document.getElementById("pkPemilikId").value = data.id || "";

    document.getElementById("pkPemilikNama").innerText = data.nama || "-";
    document.getElementById("pkPemilikNip").innerText = data.nip || "-";
    document.getElementById("pkPemilikJabatan").innerText = data.jabatan || "-";
  },

  clearPemilik() {
    document.getElementById("pkPemilikId").value = "";

    document.getElementById("pkPemilikNama").innerText = "-";
    document.getElementById("pkPemilikNip").innerText = "-";
    document.getElementById("pkPemilikJabatan").innerText = "-";
  },

  clearKabKota() {
    const hidden = document.getElementById("pkKabKotaValue");
    const input = document.getElementById("pkKabKotaInput");
    if (hidden) hidden.value = "";
    if (input) input.value = "";
  },

  // PENGELOLA RISIKO
  loadPengelolaByTim(id) {
    if (!id) return;

    // ambil tahun dari form, fallback ke tahun sekarang
    const tahun =
      document.getElementById("pkTahun")?.value || new Date().getFullYear();

    $.get(
      "/penetapan-konteks/konteks/get-pengelola-list?tim=" +
        id +
        "&tahun=" +
        tahun,
      (res) => {
        // response sekarang object tunggal, bukan array
        if (!res || Object.keys(res).length === 0) {
          this.clearPengelola();
          return;
        }

        this.setPengelolaRisiko(res);

        // tampilkan warning kalau data dari fallback tahun lain
        if (res.is_fallback) {
          console.warn(
            "Data pengelola diambil dari tahun " + res.tahun + " (fallback)",
          );
          // opsional: tampilkan info kecil di UI
          const warningEl = document.getElementById("pkPengelolaWarning");
          if (warningEl) {
            warningEl.innerText =
              "⚠ Menampilkan data pengelola tahun " + res.tahun;
            warningEl.style.display = "block";
          }
        } else {
          const warningEl = document.getElementById("pkPengelolaWarning");
          if (warningEl) warningEl.style.display = "none";
        }
      },
    );
  },

  setPengelolaRisiko(data) {
    document.getElementById("pkPengelolaValue").value = data.id || "";
    document.getElementById("pkPengelolaNama").innerText = data.nama || "-";
    document.getElementById("pkPengelolaNip").innerText = data.nip || "-";
    document.getElementById("pkPengelolaJabatan").innerText =
      data.jabatan || "-";
  },

  clearPengelola() {
    document.getElementById("pkPengelolaValue").value = "";

    document.getElementById("pkPengelolaNama").innerText = "-";
    document.getElementById("pkPengelolaNip").innerText = "-";
    document.getElementById("pkPengelolaJabatan").innerText = "-";
  },

  // TIM KERJA COMBOBOX
  initTimCombobox() {
    Combobox.init({
      boxId: "pkTimBox",
      inputId: "pkTimInput",
      hiddenId: "pkTimValue",
      optionsSelector: ".pk-option",

      onSelect: (value) => {
        KonteksModule.resetKegiatan();

        KonteksModule.loadPengelolaByTim(value);
        KonteksModule.loadKegiatanByTim(value);
      },
    });
  },

  // KEGIATAN
  resetKegiatan() {
    const input = document.getElementById("pkKegiatanInput");
    const hidden = document.getElementById("pkKegiatanValue");
    const wrapper = document.getElementById("pkKegiatanOptions");

    if (input) input.value = "";
    if (hidden) hidden.value = "";

    if (wrapper) {
      wrapper.innerHTML =
        '<div class="pk-option text-muted">Pilih tim kerja terlebih dahulu</div>';
    }
  },

  loadKegiatanByTim(id, afterLoad) {
    if (!id) return;

    const wrapper = document.getElementById("pkKegiatanOptions");
    const input = document.getElementById("pkKegiatanInput");
    const hidden = document.getElementById("pkKegiatanValue");
    const dropdown = document.querySelector(
      "#pkKegiatanBox .pk-combobox-dropdown",
    );

    $.get("/penetapan-konteks/konteks/get-kegiatan/" + id, function (res) {
      wrapper.innerHTML = "";
      input.value = "";
      hidden.value = "";

      if (!res || res.length === 0) {
        wrapper.innerHTML =
          '<div class="pk-option text-muted">Tidak ada kegiatan</div>';
        if (afterLoad) afterLoad([]);
        return;
      }

      res.forEach(function (item) {
        const div = document.createElement("div");
        div.className = "pk-option";
        div.innerText = item.nama_kegiatan;
        div.dataset.value = item.id_kegiatan;

        div.addEventListener("mousedown", function (e) {
          e.preventDefault();
          e.stopPropagation();
          input.value = item.nama_kegiatan;
          hidden.value = item.id_kegiatan;
          dropdown.classList.remove("open");
        });

        wrapper.appendChild(div);
      });

      // buka/tutup dropdown saat klik input
      input.onclick = () => dropdown.classList.toggle("open");
      input.oninput = function () {
        const q = this.value.toLowerCase();
        wrapper.querySelectorAll(".pk-option").forEach((o) => {
          o.style.display = o.innerText.toLowerCase().includes(q) ? "" : "none";
        });
        dropdown.classList.add("open");
      };

      document.addEventListener(
        "click",
        function (e) {
          if (!document.getElementById("pkKegiatanBox").contains(e.target)) {
            dropdown.classList.remove("open");
          }
        },
        { once: false },
      );
      if (afterLoad) afterLoad(res);

      input.onkeydown = function (e) {
        if (e.key === "Enter") {
          e.preventDefault();
          e.stopPropagation();

          const active = wrapper.querySelector(".pk-option.active");
          const visible = [...wrapper.querySelectorAll(".pk-option")].filter(
            (o) => o.style.display !== "none",
          );

          const target = active || (visible.length === 1 ? visible[0] : null);

          if (target) {
            input.value = target.innerText;
            hidden.value = target.dataset.value;
            dropdown.classList.remove("open");
          }
        }

        if (e.key === "ArrowDown" || e.key === "ArrowUp") {
          e.preventDefault();
          const visible = [...wrapper.querySelectorAll(".pk-option")].filter(
            (o) => o.style.display !== "none",
          );

          let currentIdx = visible.findIndex((o) =>
            o.classList.contains("active"),
          );
          visible.forEach((o) => o.classList.remove("active"));

          if (e.key === "ArrowDown")
            currentIdx = (currentIdx + 1) % visible.length;
          if (e.key === "ArrowUp")
            currentIdx = (currentIdx - 1 + visible.length) % visible.length;

          if (visible[currentIdx]) {
            visible[currentIdx].classList.add("active");
            visible[currentIdx].scrollIntoView({ block: "nearest" });
          }
        }
      };
    });
  },

  initTahunCombobox() {
    Combobox.init({
      boxId: "pkTahunBox",
      inputId: "pkTahunInput",
      hiddenId: "pkTahun",
      optionsSelector: ".pk-option",

      onSelect: (tahun) => {
        // reload pengelola kalau tim kerja sudah dipilih
        const timId = document.getElementById("pkTimValue")?.value;
        if (timId) {
          KonteksModule.loadPengelolaByTim(timId);
        }
      },
    });
  },

  // SASARAN STRATEGIS
  initSasaranCombobox() {
    Combobox.init({
      boxId: "pkSasaranBox",
      inputId: "pkSasaranInput",
      hiddenId: "pkSasaranValue",
      optionsSelector: ".pk-option",
    });
  },

  // PERATURAN MULTI SELECT
  initPeraturanCombobox() {
    const tags = document.getElementById("pkPeraturanTags");

    Combobox.init({
      boxId: "pkPeraturanBox",
      inputId: "pkPeraturanInput",
      hiddenId: null,
      optionsSelector: ".pk-option",

      onSelect: (value, text) => {
        if (tags.querySelector(`[data-id="${value}"]`)) return;

        const index = tags.querySelectorAll(".pk-law-item").length + 1;

        const tag = document.createElement("div");
        tag.className = "pk-law-item";
        tag.dataset.id = value;
        tag.innerHTML = `
          <div class="pk-law-number">${index}.</div>
          <div class="pk-law-title">${text}</div>
          <span class="pk-tag-remove">×</span>
        `;

        const hidden = document.createElement("input");
        hidden.type = "hidden";
        hidden.name = "peraturan[]";
        hidden.value = value;
        tag.appendChild(hidden);

        tags.appendChild(tag);

        const option = document.querySelector(
          `#pkPeraturanBox .pk-option[data-value="${value}"]`,
        );
        if (option) option.style.display = "none";

        tag.querySelector(".pk-tag-remove").onclick = () => {
          tag.remove();
          if (option) option.style.display = "";
          KonteksModule.reindexPeraturan();
        };

        document.getElementById("pkPeraturanInput").value = "";
      },
    });
  },

  reindexPeraturan() {
    const items = document.querySelectorAll("#pkPeraturanTags .pk-law-item");

    items.forEach((el, i) => {
      const num = el.querySelector(".pk-law-number");
      if (num) num.innerText = i + 1 + ".";
    });
  },

  // HELPER: SET READONLY STATE
  setReadonly(isReadonly) {
    const fields = document.querySelectorAll(
      "#pkFormKonteks input:not([type=hidden]), #pkFormKonteks select, #pkFormKonteks textarea",
    );
    // 🔒 LOCK struktur organisasi (radio card)
document.querySelectorAll(".struktur-card").forEach((card) => {
  const radio = card.querySelector('input[type="radio"]');

  if (isReadonly) {
    card.style.pointerEvents = "none";
    card.classList.add("pk-disabled");

    if (radio) radio.disabled = true;
  } else {
    card.style.pointerEvents = "";
    card.classList.remove("pk-disabled");

    if (radio) radio.disabled = false;
  }
});
    fields.forEach((el) => {
      if (isReadonly) {
        el.setAttribute("readonly", true);
        el.setAttribute("tabindex", "-1");
        el.style.pointerEvents = "none";
        el.classList.add("pk-field-readonly");
      } else {
        el.removeAttribute("readonly");
        el.removeAttribute("tabindex");
        el.style.pointerEvents = "";
        el.classList.remove("pk-field-readonly");
      }
    });
    document
      .querySelectorAll("#pkFormKonteks .pk-combobox-input")
      .forEach((el) => {
        el.style.pointerEvents = isReadonly ? "none" : "";
      });
    const pemangkuBox = document.getElementById("pkPemangkuBox");
    if (pemangkuBox) pemangkuBox.style.display = isReadonly ? "none" : "";
    const peraturanBox = document.getElementById("pkPeraturanBox");
    if (peraturanBox) peraturanBox.style.display = isReadonly ? "none" : "";

    document
      .querySelectorAll("#pkPemangkuTags .pk-pemangku-item")
      .forEach((el) => {
        if (isReadonly) {
          el.classList.remove("pk-editable");
        } else {
          el.classList.add("pk-editable");
        }
      });
  },

  // PEMANGKU TAG INPUT
  initPemangkuCombobox() {
    const container = document.getElementById("pkPemangkuTags");

    Combobox.init({
      boxId: "pkPemangkuBox",
      inputId: "pkPemangkuInput",
      hiddenId: null,
      optionsSelector: ".pk-option",

      onSelect: (value, text) => {
        if (container.querySelector(`[data-id="${value}"]`)) return;

        const option = document.querySelector(
          `#pkPemangkuBox .pk-option[data-value="${value}"]`,
        );
        const role = option?.dataset.role || "";

        let group = container.querySelector(
          `.pk-pemangku-group[data-role="${role.replace(/"/g, '\\"')}"]`,
        );

        if (!group) {
          group = document.createElement("div");
          group.className = "pk-pemangku-group";
          group.dataset.role = role;
          group.innerHTML = `
            <div class="pk-pemangku-title">${role}</div>
            <div class="pk-pemangku-items"></div>
          `;
          container.appendChild(group);
        }

        const list = group.querySelector(".pk-pemangku-items");

        const item = document.createElement("div");
        item.className = "pk-pemangku-item pk-editable";
        item.dataset.id = value;
        item.innerHTML = `
          <span>${text}</span>
          <span class="pk-tag-remove">×</span>
        `;

        const hidden = document.createElement("input");
        hidden.type = "hidden";
        hidden.name = "pemangku[]";
        hidden.value = value;
        item.appendChild(hidden);

        list.appendChild(item);

        item.querySelector(".pk-tag-remove").onclick = function () {
          if (option) option.style.display = "";
          item.remove();
          if (list.children.length === 0) {
            group.remove();
          }
        };

        if (option) option.style.display = "none";

        document.getElementById("pkPemangkuInput").value = "";
      },
    });
  },

  // FORM SUBMIT (CREATE / UPDATE)
  initFormSubmit() {
    const form = document.getElementById("pkFormKonteks");
    if (!form) return;

    form.addEventListener("submit", function (e) {
      e.preventDefault();

      PkAlert.confirm({
        text: "Simpan data konteks ini?",
        confirmText: "Simpan",
      }).then((result) => {
        if (!result.isConfirmed) return;

        const mode = document.getElementById("pkMode").value;
        const url =
          mode === "edit"
            ? baseUrl +"/penetapan-konteks/konteks/update"
            : baseUrl + "/penetapan-konteks/konteks/store";

        PkAjax.post({
          url,
          data: $(form).serialize(),
          onSuccess(res) {
            if (res.status !== "success") return;
            bootstrap.Offcanvas.getInstance(
              document.getElementById("offcanvasKonteks"),
            ).hide();
            KonteksModule.refreshTable();
            PkAlert.success({ text: res.message });
          },
        });
      });
    });
  },

  // DELETE
  deleteKonteks(id) {
    PkAlert.warning({
      title: "Hapus data ini?",
      text: "Data yang dihapus tidak bisa dikembalikan.",
      confirmText: "Hapus",
    }).then((result) => {
      if (!result.isConfirmed) return;

      PkAjax.post({
        url: "/penetapan-konteks/konteks/delete",
        data: { id_konteks: id },
        onSuccess(res) {
          if (res.status !== "success") return;
          bootstrap.Offcanvas.getInstance(
            document.getElementById("offcanvasKonteks"),
          )?.hide();
          KonteksModule.refreshTable();
          PkAlert.success({ title: "Terhapus", text: res.message });
        },
      });
    });
  },

  // REFRESH TABLE (AJAX)
  refreshTable() {
    $.get("/penetapan-konteks/konteks/table", function (html) {
      $("#pkKonteksTableWrapper").html(html);
    });
  },

  initPreventEnterSubmit() {
    const form = document.getElementById("pkFormKonteks");
    if (!form) return;

    form.addEventListener("keydown", function (e) {
      if (e.key !== "Enter") return;

      const openDropdown = form.querySelector(".pk-combobox-dropdown.open");
      if (!openDropdown) {
        e.preventDefault();
        e.stopPropagation();
      }
    });
  },

  resetActionButtons() {
    ["pkBtnCreate", "pkBtnView", "pkBtnEdit"].forEach((id) => {
      const el = document.getElementById(id);
      if (el) el.style.display = "none";
    });
  },
};

// INIT
$(document).ready(function () {
  KonteksModule.init();
});

// GLOBAL FUNCTION
window.pkOpenViewMode = function (el) {
  const row = JSON.parse(el.dataset.row);
  const id = row.id_konteks;

  const currentUser = window.APP_USER || {};

  const isOperator = currentUser.role === "operator";
  const bedaTim = String(currentUser.id_tim) !== String(row.id_tim);

  const isKetua = currentUser.role === "ketua";

  const isReadonlyMode = isKetua || (isOperator && bedaTim);

  document.getElementById("pkMode").value = "view";
  document.getElementById("pkId").value = id;

  document.getElementById("pkOffcanvasTitle").innerText = "Detail Konteks";

  KonteksModule.resetActionButtons();
  document.getElementById("pkBtnView").style.display = "flex";

  const btnDelete = document.getElementById("pkBtnDelete");
  const btnEdit = document.getElementById("pkBtnSwitchEdit");
  const btnClose = document.getElementById("pkBtnCloseView");

  // DEFAULT (normal mode)
  if (btnDelete) btnDelete.style.display = "";
  if (btnEdit) btnEdit.style.display = "";
  if (btnClose) btnClose.textContent = "Tutup";

  // READONLY MODE (operator beda tim)
  if (isReadonlyMode) {
    if (btnClose) {
      btnClose.classList.remove("btn-light");
      btnClose.classList.add("btn-secondary");
    }

    if (btnDelete) btnDelete.style.display = "none";
    if (btnEdit) btnEdit.style.display = "none";
  }

  // selalu readonly di view mode
  KonteksModule.setReadonly(true);

  // DELETE
  if (btnDelete) {
    btnDelete.onclick = () => {
      if (isReadonlyMode) return;
      KonteksModule.deleteKonteks(id);
    };
  }

  // EDIT
  if (btnEdit) {
    btnEdit.onclick = () => {
      if (isReadonlyMode) return;

      document.getElementById("pkMode").value = "edit";
      document.getElementById("pkOffcanvasTitle").innerText = "Edit Konteks";
      KonteksModule.resetActionButtons();
      document.getElementById("pkBtnEdit").style.display = "flex";

      KonteksModule.setReadonly(false);
    };
  }

  const btnCancelEdit = document.getElementById("pkBtnCancelEdit");

  if (btnCancelEdit) {
    btnCancelEdit.onclick = () => {
      document.getElementById("pkMode").value = "view";
      document.getElementById("pkOffcanvasTitle").innerText = "Detail Konteks";
      KonteksModule.resetActionButtons();
      document.getElementById("pkBtnView").style.display = "flex";
      KonteksModule.setReadonly(true);
    };
  }

  new bootstrap.Offcanvas(document.getElementById("offcanvasKonteks")).show();

  $.get(`/penetapan-konteks/konteks/detail/${id}`, (res) => {
    const k = res.konteks;

    // ===== FIX WILAYAH =====
    if (k.id_wilayah) {
      // switch ke kab/kota
      const kabRadio = document.querySelector(
        'input[name="level_struktur"][value="kabkota"]',
      );

      if (kabRadio) {
        kabRadio.checked = true;
        kabRadio.dispatchEvent(new Event("change"));
      }

      const opt = document.querySelector(
        `#pkKabKotaBox .pk-option[data-value="${k.id_wilayah}"]`,
      );

      if (opt) {
        document.getElementById("pkKabKotaValue").value = k.id_wilayah;
        document.getElementById("pkKabKotaInput").value = opt.innerText.trim();
      }
    } else {
      // fallback provinsi
      const provRadio = document.querySelector(
        'input[name="level_struktur"][value="provinsi"]',
      );

      if (provRadio) {
        provRadio.checked = true;
        provRadio.dispatchEvent(new Event("change"));
      }
    }
    if (!k.id_wilayah) {
      KonteksModule.loadProvinsiPemilik();
    }

    document.getElementById("pkTimValue").value = k.id_tim;
    document.getElementById("pkTimInput").value = k.nama_tim ?? "";
    document.getElementById("pkTahun").value = k.tahun;
    document.getElementById("pkTahunInput").value = k.tahun;
    document.getElementById("pkSasaranValue").value = k.id_sasaran_strategis;
    document.getElementById("pkSasaranInput").value = k.uraian_sasaran ?? "";

    if (k.id_tim) {
      KonteksModule.loadKegiatanByTim(k.id_tim, function () {
        document.getElementById("pkKegiatanValue").value = k.id_kegiatan ?? "";
        document.getElementById("pkKegiatanInput").value =
          k.nama_kegiatan ?? "";
      });
    }

    // isi pengelola — response sekarang object tunggal, bukan array
    if (k.pengelola_risiko_id) {
      $.get(
        "/penetapan-konteks/konteks/get-pengelola-list?tim=" +
          k.id_tim +
          "&tahun=" +
          k.tahun,
        (res) => {
          if (res && Object.keys(res).length > 0) {
            KonteksModule.setPengelolaRisiko(res);
          }
        },
      );
    }

    // isi pemangku (view only)
    const container = document.getElementById("pkPemangkuTags");
    if (container) container.innerHTML = "";
    if (res.pemangku && res.pemangku.length > 0) {
      res.pemangku.forEach((idP) => {
        const opt = document.querySelector(
          `#pkPemangkuBox .pk-option[data-value="${idP}"]`,
        );
        if (!opt) return;
        const role = opt.dataset.role || "";
        let group = container.querySelector(
          `.pk-pemangku-group[data-role="${role.replace(/"/g, '\\"')}"]`,
        );
        if (!group) {
          group = document.createElement("div");
          group.className = "pk-pemangku-group";
          group.dataset.role = role;
          group.innerHTML = `<div class="pk-pemangku-title">${role}</div><div class="pk-pemangku-items"></div>`;
          container.appendChild(group);
        }
        const list = group.querySelector(".pk-pemangku-items");
        const item = document.createElement("div");
        item.className = "pk-pemangku-item";
        item.dataset.id = idP;
        item.innerHTML = `<span>${opt.innerText.trim()}</span><span class="pk-tag-remove">×</span>`;
        opt.style.display = "none";

        const hidden = document.createElement("input");
        hidden.type = "hidden";
        hidden.name = "pemangku[]";
        hidden.value = idP;
        item.appendChild(hidden);

        item.querySelector(".pk-tag-remove").onclick = function () {
          opt.style.display = "";
          item.remove();
          if (list.children.length === 0) group.remove();
        };

        list.appendChild(item);
      });
    }

    // isi sasaran kinerja
    const skBody = document.getElementById("pkSasaranOrganisasiBody");
    if (skBody) {
      skBody.innerHTML = "";
      if (res.sasaranKinerja && res.sasaranKinerja.length > 0) {
        res.sasaranKinerja.forEach((sk, i) => {
          const badgeClass =
            sk.jenis_proses === "Teknis"
              ? "bg-primary-subtle text-primary"
              : "bg-warning-subtle text-warning";
          skBody.innerHTML += `
                <tr>
                    <td>${i + 1}</td>
                    <td><span class="badge ${badgeClass}">${sk.kode_proses}</span></td>
                    <td>${sk.uraian_proses}</td>
                    <td>${sk.uraian_sasaran}</td>
                </tr>
            `;
        });
      } else {
        skBody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-muted py-3">
                    Belum ada sasaran organisasi
                </td>
            </tr>
        `;
      }
    }
  });
};

window.pkOpenCreateMode = function () {
  const mode = document.getElementById("pkMode");
  if (!mode) return;

  mode.value = "create";

  document.getElementById("pkOffcanvasTitle").innerText = "Tambah Konteks";

  KonteksModule.resetActionButtons();
  document.getElementById("pkBtnCreate").style.display = "flex";

  const form = document.getElementById("pkFormKonteks");
  if (form) form.reset();

  KonteksModule.setReadonly(false);
  KonteksModule.resetKegiatan();
  KonteksModule.loadProvinsiPemilik();

  const pemangkuTags = document.getElementById("pkPemangkuTags");
  if (pemangkuTags) pemangkuTags.innerHTML = "";

  document.querySelectorAll("#pkPemangkuBox .pk-option").forEach((el) => {
    el.style.display = "";
  });
};
