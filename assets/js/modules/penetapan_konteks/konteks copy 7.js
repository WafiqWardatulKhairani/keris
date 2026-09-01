const KONTEKS_URL = window.KONTEKS_CONFIG?.url || {};
const KONTEKS_CSRF = window.KONTEKS_CONFIG?.csrf || {};
const roleOrder = {
  Pembina: 1,
  "Pimpinan Lembaga": 2,
  "Mitra Kerja Internal": 3,
  "Mitra Kerja Eksternal": 4,
};

function insertPemangkuGroup(container, group, role) {
  const groups = container.querySelectorAll(".pk-pemangku-group");

  let inserted = false;

  groups.forEach((existingGroup) => {
    if (inserted) return;

    const existingRole = existingGroup.dataset.role;

    const currentOrder = roleOrder[role] ?? 999;
    const existingOrder = roleOrder[existingRole] ?? 999;

    if (currentOrder < existingOrder) {
      container.insertBefore(group, existingGroup);
      inserted = true;
    }
  });

  if (!inserted) {
    container.appendChild(group);
  }
}
const KonteksModule = {
  init() {
    console.log("Konteks module loaded");

    this.initSelect2();
    this.initSasaranCombobox();
    this.initPeraturanCombobox();
    this.initPemangkuCombobox();
    this.initQuickCreatePemangku();
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

  // PEMILIK RISIKO
  loadProvinsiPemilik() {
    $.get(KONTEKS_URL.getPemilik, (res) => {
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
      allowCreate: true,

      onCreate: async (keyword) => {
        try {
          const formData = new FormData();

          formData.append("nama_peraturan", keyword);

          const response = await fetch(window.PERATURAN_CONFIG.storeQuick, {
            method: "POST",
            headers: {
              "X-Requested-With": "XMLHttpRequest",
            },
            body: formData,
          });

          const result = await response.json();

          if (result.status !== "success") return;

          const data = result.data;

          const option = document.createElement("div");

          option.className = "pk-option";

          option.dataset.value = data.id_peraturan;

          option.innerText = data.nama_peraturan;

          document
            .querySelector("#pkPeraturanBox .pk-combobox-options")
            .prepend(option);

          KonteksModule.addPeraturanTag(data.id_peraturan, data.nama_peraturan);
          document.getElementById("pkPeraturanInput").value = "";

          const createOption = document.querySelector(
            "#pkPeraturanBox .pk-option-create",
          );

          if (createOption) {
            createOption.style.display = "none";
          }
        } catch (err) {
          console.error(err);
        }
      },

      onSelect: (value, text) => {
        KonteksModule.addPeraturanTag(value, text);
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

  addPemangkuTag(value, text, role) {
    const container = document.getElementById("pkPemangkuTags");

    if (container.querySelector(`[data-id="${value}"]`)) return;

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

      insertPemangkuGroup(container, group, role);
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

    const option = document.querySelector(
      `#pkPemangkuBox .pk-option[data-value="${value}"]`,
    );

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

  addPeraturanTag(id, nama) {
    const tags = document.getElementById("pkPeraturanTags");

    if (tags.querySelector(`[data-id="${id}"]`)) return;

    const index = tags.querySelectorAll(".pk-law-item").length + 1;

    const tag = document.createElement("div");

    tag.className = "pk-law-item";
    tag.dataset.id = id;

    tag.innerHTML = `
      <div class="pk-law-number">${index}.</div>
      <div class="pk-law-title">${nama}</div>
      <span class="pk-tag-remove">×</span>
  `;

    const hidden = document.createElement("input");
    hidden.type = "hidden";
    hidden.name = "peraturan[]";
    hidden.value = id;

    tag.appendChild(hidden);

    tags.appendChild(tag);

    const option = document.querySelector(
      `#pkPeraturanBox .pk-option[data-value="${id}"]`,
    );

    if (option) {
      option.style.display = "none";
    }

    tag.querySelector(".pk-tag-remove").onclick = () => {
      tag.remove();

      if (option) {
        option.style.display = "";
      }

      KonteksModule.reindexPeraturan();
    };

    KonteksModule.reindexPeraturan();
  },

  // PEMANGKU TAG INPUT
  initPemangkuCombobox() {
    const container = document.getElementById("pkPemangkuTags");
    const roleOrder = {
      Pembina: 1,
      "Pimpinan Lembaga": 2,
      "Mitra Kerja Internal": 3,
      "Mitra Kerja Eksternal": 4,
    };

    Combobox.init({
      boxId: "pkPemangkuBox",
      inputId: "pkPemangkuInput",
      hiddenId: null,
      optionsSelector: ".pk-option",

      allowCreate: true,

      onCreate: (keyword) => {
        console.log("KEYWORD =", keyword);
        const namaInput = document.getElementById("pmQuickNama");
        console.log("INPUT =", namaInput);
        if (namaInput) {
          namaInput.value = keyword;
        }

        //document.getElementById("pmQuickNama").value = keyword;

        //document.getElementById("pmQuickHubungan").value = "";

        bootstrap.Offcanvas.getOrCreateInstance(
          document.getElementById("offcanvasCreatePemangku"),
        ).show();

        setTimeout(() => {
          console.log(
            "AFTER SHOW =",
            document.getElementById("pmQuickNama")?.value,
          );
        }, 100);
      },

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
          //container.appendChild(group);
          insertPemangkuGroup(container, group, role);
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

  initQuickCreatePemangku() {
    const form = document.getElementById("pmQuickCreateForm");

    if (!form) return;

    form.addEventListener("submit", async (e) => {
      e.preventDefault();

      const formData = new FormData(form);

      try {
        const response = await fetch(window.PEMANGKU_QUICK_CONFIG.store, {
          method: "POST",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
          },
          body: formData,
        });

        const result = await response.json();

        console.log(result);

        if (result.status === "success") {
          const data = result.data;

          const optionsContainer = document.querySelector(
            "#pkPemangkuBox .pk-combobox-options",
          );

          const option = document.createElement("div");

          option.className = "pk-option";
          option.dataset.value = data.id_pemangku;
          option.dataset.role = data.hubungan;
          option.innerText = data.nama_instansi;

          optionsContainer.prepend(option);

          KonteksModule.addPemangkuTag(
            data.id_pemangku,
            data.nama_instansi,
            data.hubungan,
          );

          const offcanvas = bootstrap.Offcanvas.getInstance(
            document.getElementById("offcanvasCreatePemangku"),
          );

          offcanvas?.hide();

          document.getElementById("pkPemangkuInput").value = "";

          form.reset();
        }
      } catch (err) {
        console.error(err);
      }
    });
  },

  // FORM SUBMIT (CREATE / UPDATE)
  initFormSubmit() {
    console.log("INIT FORM SUBMIT");
    const form = document.getElementById("pkFormKonteks");
    console.log("FORM =", form);
    if (!form) return;

    form.addEventListener("submit", function (e) {
      console.log("FORM SUBMIT TRIGGERED");
      e.preventDefault();

      PkAlert.confirm({
        text: "Simpan data konteks ini?",
        confirmText: "Simpan",
      }).then((result) => {
        console.log("CONFIRM RESULT =", result);

        if (!result.isConfirmed) return;

        const mode = document.getElementById("pkMode").value;
        const url = mode === "edit" ? KONTEKS_URL.update : KONTEKS_URL.store;

        console.log("MODE =", mode);
        console.log("URL =", url);
        console.log("SERIALIZE =", $(form).serialize());

        PkAjax.post({
          url,
          data: $(form).serialize(),

          onSuccess(res) {
            console.log("SUCCESS =", res);

            if (res.status !== "success") return;

            PkAlert.success({
              text: res.message,
            });
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
        url: KONTEKS_URL.delete,
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
    $.get(KONTEKS_URL.table, function (html) {
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

  $.get(KONTEKS_URL.detail(id), (res) => {
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

    // isi pengelola — response sekarang object tunggal, bukan array
    if (k.pengelola_risiko_id) {
      $.get(
        KONTEKS_URL.getPengelola + "?tim=" + k.id_tim + "&tahun=" + k.tahun,
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
          //container.appendChild(group);
          insertPemangkuGroup(container, group, role);
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
  KonteksModule.loadProvinsiPemilik();

  const pemangkuTags = document.getElementById("pkPemangkuTags");
  if (pemangkuTags) pemangkuTags.innerHTML = "";

  document.querySelectorAll("#pkPemangkuBox .pk-option").forEach((el) => {
    el.style.display = "";
  });
};
