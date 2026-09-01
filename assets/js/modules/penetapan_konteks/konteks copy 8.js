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
  const form = document.getElementById("pkFormKonteks");
  if (!form) return;

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    PkAlert.confirm({
      text: "Simpan data konteks ini?",
      confirmText: "Simpan",
    }).then((result) => {

      if (!result.isConfirmed) return;

      const mode =
        document.getElementById("pkMode")?.value || "create";

      const url =
        mode === "edit"
          ? KONTEKS_URL.update
          : KONTEKS_URL.store;
      
      console.log("MODE =", mode);
      console.log("URL =", url);
      console.log($(form).serialize());

      PkAjax.post({
        url,
        data: $(form).serialize(),

        onSuccess(res) {
          if (res.status !== "success") return;

          PkAlert.success({
            text: res.message,
          }).then(() => {
            window.location.href = res.redirect;
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
};

// INIT
$(document).ready(function () {
  KonteksModule.init();
});

