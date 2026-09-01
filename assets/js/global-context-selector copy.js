/* GLOBAL CONTEXT CONFIG */
const GC_URL = window.GC_CONFIG?.url || {};

let gcCsrfToken = window.GC_CONFIG?.csrf?.token || "";
const gcCsrfName = window.GC_CONFIG?.csrf?.name || "";

const GC_DEFAULT = window.GC_CONFIG?.default || {};

const GC_USER = window.GC_CONFIG?.user || {};
let gcInitialized = false;

document.addEventListener("DOMContentLoaded", () => {
  initGlobalContext();
});

async function initGlobalContext() {
  bindEvents();

  const selectedTim = document.getElementById("ctx_tim").value;

  if (selectedTim) {
    await loadKegiatan(selectedTim, GC_DEFAULT.id_kegiatan);
  }
  gcInitialized = true;
}

function bindEvents() {
  const tahun = document.getElementById("ctx_tahun");
  const tim = document.getElementById("ctx_tim");
  const kegiatan = document.getElementById("ctx_kegiatan");

  tahun.addEventListener("change", saveGlobalContext);

  tim.addEventListener("change", async function () {
    await loadKegiatan(this.value);

    document.getElementById("ctx_kegiatan").value = "";

    saveGlobalContext();
  });

  kegiatan.addEventListener("change", () => {
    applySelectedAlias();
    saveGlobalContext();
  });
}

async function saveGlobalContext() {
  if (!gcInitialized) return;
  const tahun = document.getElementById("ctx_tahun").value;
  const idTim = document.getElementById("ctx_tim").value;
  const idKegiatan = document.getElementById("ctx_kegiatan").value;

  const formData = new FormData();

  formData.append("tahun", tahun);
  formData.append("id_tim", idTim);
  formData.append("id_kegiatan", idKegiatan);

  formData.append(gcCsrfName, gcCsrfToken);

  try {
    await fetch(GC_URL.set, {
      method: "POST",
      body: formData,
    });
    window.location.reload();
  } catch (err) {
    console.error("Gagal menyimpan global context:", err);
  }
}

async function loadKegiatan(idTim, selectedKegiatan = "") {
  const kegiatanSelect = document.getElementById("ctx_kegiatan");

  kegiatanSelect.innerHTML = '<option value="">Semua Kegiatan</option>';

  if (!idTim) return;

  try {
    const response = await fetch(`${GC_URL.kegiatan}?id_tim=${idTim}`);

    const data = await response.json();

    data.forEach((item) => {
      const option = document.createElement("option");

      option.value = item.id_kegiatan;
      const nama = item.nama_kegiatan || "";
      const match = nama.match(/\(([^)]+)\)/);

      option.textContent = nama;
      option.dataset.alias = match ? match[1] : nama;

      if (String(selectedKegiatan) === String(item.id_kegiatan)) {
        option.selected = true;
      }

      kegiatanSelect.appendChild(option);
    });
    applySelectedAlias();
  } catch (err) {
    console.error("Gagal load kegiatan:", err);
  }
}

function applySelectedAlias() {
  const select = document.getElementById("ctx_kegiatan");

  if (!select) return;

  const selectedOption = select.options[select.selectedIndex];

  if (!selectedOption) return;

  // restore semua option ke nama lengkap
  Array.from(select.options).forEach((opt) => {
    if (opt.dataset.fulltext) {
      opt.textContent = opt.dataset.fulltext;
    }
  });

  // simpan nama asli
  if (!selectedOption.dataset.fulltext) {
    selectedOption.dataset.fulltext = selectedOption.textContent;
  }

  // ubah selected jadi alias
  if (selectedOption.dataset.alias) {
    selectedOption.textContent = selectedOption.dataset.alias;
  }
}

async function resetGlobalContext() {
  const formData = new FormData();

  formData.append(gcCsrfName, gcCsrfToken);

  try {
    await fetch(GC_URL.reset, {
      method: "POST",
      body: formData,
    });

    window.location.reload();
  } catch (err) {
    console.error("Gagal reset global context:", err);
  }
}
