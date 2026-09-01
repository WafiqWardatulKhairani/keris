const RL_URL = window.KONTEKS_CONFIG?.url || {};
const RL_CSRF = window.KONTEKS_CONFIG?.csrf || {};
function rlSetMode(mode) {
  document.getElementById("rlMode").value = mode;

  const isView = mode === "view";
  const isEdit = mode === "edit";
  const isCreate = mode === "create";

  const show = (id) => document.getElementById(id)?.classList.remove("d-none");

  const hide = (id) => document.getElementById(id)?.classList.add("d-none");

  [
    "rlBtnEdit",
    "rlBtnBatal",
    "rlBtnSimpan",
    "rlBtnDelete",
    "rlBtnTutup",
  ].forEach(hide);

  if (isCreate) {
    show("rlBtnSimpan");
    show("rlBtnTutup");
  }

  if (isView) {
    show("rlBtnEdit");
    show("rlBtnDelete");
    show("rlBtnTutup");
  }

  if (isEdit) {
    show("rlBtnBatal");
    show("rlBtnSimpan");
  }

  document.getElementById("rlInfoPanel")?.classList.toggle("d-none", !isView);

  document.getElementById("rlInputZone")?.classList.toggle("d-none", isView);

  ["rlTahun", "rl_tim", "rl_kegiatan"].forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.disabled = isView;
  });

  document.getElementById("rlOffcanvasTitle").textContent = isCreate
    ? "Tambah Ruang Lingkup"
    : isEdit
      ? "Edit Ruang Lingkup"
      : "Detail Ruang Lingkup";
}

function rlPopulateView(data) {
  const map = {
    rlViewTahun: data.tahun,
    rlViewTim: data.nama_tim,
    rlViewKegiatan: data.nama_kegiatan,
  };

  Object.entries(map).forEach(([id, value]) => {
    const el = document.getElementById(id);
    if (el) el.textContent = value || "-";
  });
}

async function loadKegiatan(idTim) {
  try {
    const kegiatanSelect = document.getElementById("rl_kegiatan");

    const response = await fetch(RL_URL.getKegiatan(idTim), {
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    });

    const result = await response.json();

    kegiatanSelect.innerHTML = '<option value="">Pilih Kegiatan</option>';

    result.forEach((item) => {
      kegiatanSelect.innerHTML += `
        <option value="${item.id_kegiatan}">
          ${item.nama_kegiatan}
        </option>
      `;
    });
  } catch (err) {
    console.error("Gagal load kegiatan", err);
  }
}

async function openRuangLingkupDetail(id) {
  try {
    const response = await fetch(RL_URL.detail(id), {
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    });

    const result = await response.json();
    const data = result.konteks;

    if (!data) {
      Swal.fire({
        icon: "error",
        title: "Gagal",
        text: "Data ruang lingkup tidak ditemukan",
      });

      return;
    }

    const user = window.APP_USER || {};

    const canManage =
      user.role === "admin" ||
      (user.role === "operator" && String(user.id_tim) === String(data.id_tim));

    document.getElementById("rlId").value = data.id_konteks;

    rlPopulateView(data);

    document.getElementById("rlTahun").value = data.tahun || "";

    const timSelect = document.getElementById("rl_tim");

    timSelect.innerHTML = `
      <option value="${data.id_tim}">
        ${data.nama_tim}
      </option>
    `;

    await loadKegiatan(data.id_tim);

    document.getElementById("rl_kegiatan").value = data.id_kegiatan;

    rlSetMode("view");

    document.getElementById("rl_tim").disabled = true;

    if (!canManage || user.role === "ketua") {
      document.getElementById("rlBtnEdit")?.classList.add("d-none");

      document.getElementById("rlBtnDelete")?.classList.add("d-none");
    }

    const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(
      document.getElementById("offcanvasRuangLingkup"),
    );

    offcanvas.show();
  } catch (err) {
    console.error(err);

    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Gagal memuat detail ruang lingkup",
    });
  }
}

document.getElementById("rlBtnEdit")?.addEventListener("click", () => {
  rlSetMode("edit");
});

document
  .getElementById("rlBtnDelete")
  ?.addEventListener("click", async function () {
    const id = document.getElementById("rlId").value;

    if (!id) return;

    const confirm = await Swal.fire({
      title: "Hapus ruang lingkup ini?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Ya, Hapus",
      cancelButtonText: "Batal",
      confirmButtonColor: "#dc3545",
      reverseButtons: true,
    });

    if (!confirm.isConfirmed) return;

    const response = await fetch(RL_URL.delete, {
      method: "POST",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
      body: new URLSearchParams({
        id_konteks: id,
        [RL_CSRF.name]: RL_CSRF.token,
      }),
    });

    const result = await response.json();

    if (result.status === "success") {
      bootstrap.Offcanvas.getInstance(
        document.getElementById("offcanvasRuangLingkup"),
      )?.hide();

      Swal.fire({
        icon: "success",
        title: "Berhasil dihapus",
        timer: 1200,
        showConfirmButton: false,
      }).then(() => location.reload());
    }
  });

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("formRuangLingkup");

  const timSelect = document.getElementById("rl_tim");

  const kegiatanSelect = document.getElementById("rl_kegiatan");

  if (!form) return;

  $('input[name="tahun"]').datepicker({
    format: "yyyy",
    minViewMode: 2,
    autoHide: true,
    zIndex: 9999,
  });

  const user = window.APP_USER || {};

  if (user.role === "operator" && user.id_tim) {
    timSelect.value = user.id_tim;
    timSelect.disabled = true;

    loadKegiatan(user.id_tim);
  }

  timSelect.addEventListener("change", function () {
    const idTim = this.value;

    kegiatanSelect.innerHTML = '<option value="">Pilih Kegiatan</option>';

    if (!idTim) return;

    loadKegiatan(idTim);
  });

  form.addEventListener("submit", async function (e) {
    e.preventDefault();

    try {
      const formData = new FormData(form);

      if (timSelect.disabled) {
        formData.set("id_tim", timSelect.value);
      }

      formData.append(
        window.KONTEKS_CONFIG.csrf.name,
        window.KONTEKS_CONFIG.csrf.token,
      );

      const confirm = await Swal.fire({
        title: "Simpan ruang lingkup?",
        text: "Draft konteks akan dibuat dan dilanjutkan ke halaman konteks",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Ya, simpan",
        cancelButtonText: "Batal",
        reverseButtons: true,
        customClass: {
          popup: "swal-mantis",
        },
      });

      if (!confirm.isConfirmed) return;

      const response = await fetch(RL_URL.createDraft, {
        method: "POST",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
        body: formData,
      });

      const result = await response.json();

      if (result.status === "success") {
        Swal.fire({
          icon: "success",
          title: "Berhasil",
          text: "Ruang lingkup berhasil disimpan",
          confirmButtonText: "Lanjut",
          customClass: {
            popup: "swal-mantis",
          },
        }).then(() => {
          window.location.href = `/penetapan-konteks/konteks/${result.id}`;
        });

        return;
      }

      Swal.fire({
        icon: "error",
        title: "Gagal",
        text: result.message || "Gagal menyimpan ruang lingkup",
        customClass: {
          popup: "swal-mantis",
        },
      });
    } catch (err) {
      console.error(err);

      Swal.fire({
        icon: "error",
        title: "Terjadi Kesalahan",
        text: "Tidak dapat menyimpan ruang lingkup",
        customClass: {
          popup: "swal-mantis",
        },
      });
    }
  });

  rlSetMode("create");
});

document.addEventListener("click", function (e) {
  const row = e.target.closest(".rl-row");

  if (!row) return;

  openRuangLingkupDetail(row.dataset.id);
});
