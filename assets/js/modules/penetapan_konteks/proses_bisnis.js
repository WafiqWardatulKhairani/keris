const PROSES_URL = window.PROSES_CONFIG?.url || {};

document.addEventListener("DOMContentLoaded", () => {
  const offcanvasEl = document.getElementById("offcanvasProsesBisnis");
  const form = document.getElementById("pbForm");

  if (!offcanvasEl || !form) return;

  const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);

  const currentUser = window.APP_USER || {};
  const activeKonteks = window.APP_KONTEKS || {};

  const isKetua = currentUser.role === "ketua";

  const bedaTim =
    currentUser.role === "operator" &&
    String(currentUser.id_tim) !== String(activeKonteks.id_tim);

  const canManage = !isKetua && !bedaTim;

  function setCreateMode() {
    document.getElementById("pbMode").value = "create";

    document.getElementById("pbTitle").textContent = "Tambah Proses Bisnis";

    document.getElementById("pbInputZone")?.classList.remove("d-none");

    document.getElementById("pbInfoPanel")?.classList.add("d-none");

    document.getElementById("pbProses").removeAttribute("disabled");
    document.getElementById("pbDeskripsi").readOnly = false;
    document.getElementById("pbSasaran").readOnly = false;

    document.getElementById("pbBtnDelete")?.classList.add("d-none");

    document.getElementById("pbBtnEdit")?.classList.add("d-none");

    document.getElementById("pbBtnTutup")?.classList.add("d-none");

    if (canManage) {
      document.getElementById("pbBtnSave")?.classList.remove("d-none");

      document.getElementById("pbBtnBatal")?.classList.remove("d-none");
    } else {
      document.getElementById("pbBtnSave")?.classList.add("d-none");

      document.getElementById("pbBtnBatal")?.classList.add("d-none");

      document.getElementById("pbBtnTutup")?.classList.remove("d-none");
    }
  }

  function setViewMode() {
    document.getElementById("pbMode").value = "view";

    document.getElementById("pbTitle").textContent = "Detail Proses Bisnis";

    document.getElementById("pbInputZone")?.classList.remove("d-none");

    document.getElementById("pbProses").setAttribute("disabled", true);
    document.getElementById("pbDeskripsi").readOnly = true;
    document.getElementById("pbSasaran").readOnly = true;

    if (canManage) {
      document.getElementById("pbBtnDelete")?.classList.remove("d-none");

      document.getElementById("pbBtnEdit")?.classList.remove("d-none");
    } else {
      document.getElementById("pbBtnDelete")?.classList.add("d-none");

      document.getElementById("pbBtnEdit")?.classList.add("d-none");
    }

    document.getElementById("pbBtnSave")?.classList.add("d-none");

    document.getElementById("pbBtnBatal")?.classList.add("d-none");

    document.getElementById("pbBtnTutup")?.classList.remove("d-none");
  }

  function setEditMode() {
    document.getElementById("pbMode").value = "edit";

    document.getElementById("pbTitle").textContent = "Edit Proses Bisnis";

    document.getElementById("pbProses").removeAttribute("disabled");
    document.getElementById("pbDeskripsi").readOnly = false;
    document.getElementById("pbSasaran").readOnly = false;

    if (canManage) {
      document.getElementById("pbBtnDelete")?.classList.remove("d-none");
    }

    document.getElementById("pbBtnEdit")?.classList.add("d-none");

    document.getElementById("pbBtnSave")?.classList.remove("d-none");

    document.getElementById("pbBtnBatal")?.classList.remove("d-none");

    document.getElementById("pbBtnTutup")?.classList.add("d-none");
  }

  function resetForm() {
    form.reset();

    document.getElementById("pbId").value = "";

    document.getElementById("pbSasaran").value = "";

    setCreateMode();
  }

  async function openEdit(id) {
    console.log("OPEN EDIT", id);
    try {
      const response = await fetch(PROSES_URL.detail(id), {
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      const result = await response.json();

      if (result.status !== "success") {
        PkAlert.error({
          text: result.message || "Gagal memuat data",
        });

        return;
      }

      const data = result.data;

      document.getElementById("pbId").value = data.id_konteks_proses;

      document.getElementById("pbProses").value = data.id_proses;

      document.getElementById("pbDeskripsi").value =
        data.deskripsi_proses || "";

      document.getElementById("pbSasaran").value = data.uraian_sasaran || "";

      setViewMode();

      bsOffcanvas.show();
    } catch (err) {
      console.error(err);

      PkAlert.error({
        text: "Gagal memuat detail proses bisnis",
      });
    }
  }

  document.getElementById("pbBtnCreate")?.addEventListener("click", () => {
    if (!canManage) return;

    resetForm();

    setCreateMode();

    bsOffcanvas.show();
  });
  document.getElementById("pbBtnEdit")?.addEventListener("click", () => {
    setEditMode();
  });

  document.addEventListener("click", (e) => {
    console.log("ROW CLICK");
    const row = e.target.closest("[data-pb-edit]");

    if (!row) return;

    const id = row.dataset.pbEdit;

    if (!id) return;

    openEdit(id);
  });

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    const id = document.getElementById("pbId").value;

    const url = id ? PROSES_URL.update(id) : PROSES_URL.store;

    try {
      const formData = new FormData(form);

      const response = await fetch(url, {
        method: "POST",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
        body: formData,
      });

      const result = await response.json();

      if (result.status !== "success") {
        PkAlert.error({
          text: result.message || "Gagal menyimpan data",
        });

        return;
      }

      bsOffcanvas.hide();

      PkAlert.toast({
        text: "Proses bisnis berhasil disimpan",
        icon: "success",
      });

      refreshTable();
    } catch (err) {
      console.error(err);

      PkAlert.error({
        text: "Terjadi kesalahan",
      });
    }
  });

  document
    .getElementById("pbBtnDelete")
    ?.addEventListener("click", async () => {
      const id = document.getElementById("pbId").value;

      if (!id) return;

      const confirm = await Swal.fire({
        title: "Hapus proses bisnis?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Hapus",
        cancelButtonText: "Batal",
        reverseButtons: true,
      });

      if (!confirm.isConfirmed) return;

      try {
        const response = await fetch(PROSES_URL.delete(id), {
          method: "POST",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
          },
        });

        const result = await response.json();

        if (result.status !== "success") {
          PkAlert.error({
            text: result.message || "Gagal menghapus data",
          });

          return;
        }

        bsOffcanvas.hide();

        PkAlert.toast({
          text: "Proses bisnis berhasil dihapus",
          icon: "success",
        });

        refreshTable();
      } catch (err) {
        console.error(err);

        PkAlert.error({
          text: "Terjadi kesalahan",
        });
      }
    });

  async function refreshTable() {
    try {
      const response = await fetch(PROSES_URL.table, {
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      const html = await response.text();

      document.getElementById("pkProsesBisnisTableWrapper").innerHTML = html;
    } catch (err) {
      console.error(err);
    }
  }
});
