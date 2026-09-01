const SK_URL = window.SK_CONFIG?.url || {};
document.addEventListener("DOMContentLoaded", function () {
  const offcanvasEl = document.getElementById("offcanvasSasaranKinerja");
  if (!offcanvasEl) return;

  // RBAC CONTEXT
  const currentUser = window.APP_USER || {};
  const activeKonteks = window.APP_KONTEKS || {};

  const isKetua = currentUser.role === "ketua";
  const isOperator = currentUser.role === "operator";
  const bedaTim = String(currentUser.id_tim) !== String(activeKonteks.id_tim);

  // hide tombol tambah untuk ketua
  if (isKetua) {
    const btnAdd = document.querySelector(
      '[data-bs-target="#offcanvasSasaranKinerja"]',
    );
    if (btnAdd) btnAdd.style.display = "none";
  }

  let currentMode = null;
  let currentId = null;
  let originalData = null;

  // MODE HELPERS
  function setCreateMode() {
    currentMode = "create";
    currentId = null;
    document.getElementById("skOffcanvasTitle").textContent =
      "Tambah Sasaran Kinerja";
    resetForm();
    setFieldsDisabled(false);
    document.getElementById("skBtnView").style.display = "none";
    document.getElementById("skBtnEdit").style.display = "flex";
  }

  function setViewMode(data) {
    currentMode = "view";
    currentId = data.id_sasaran;
    document.getElementById("skOffcanvasTitle").textContent =
      "Detail Sasaran Kinerja";
    fillForm(data);
    setFieldsDisabled(true);
    document.getElementById("skBtnView").style.display = "flex";
    document.getElementById("skBtnEdit").style.display = "none";
  }

  function setEditMode() {
    currentMode = "edit";
    document.getElementById("skOffcanvasTitle").textContent =
      "Edit Sasaran Kinerja";
    setFieldsDisabled(false);
    document.getElementById("skBtnView").style.display = "none";
    document.getElementById("skBtnEdit").style.display = "flex";
  }

  function setFieldsDisabled(disabled) {
    document.getElementById("skIdKonteksProses").disabled = disabled;
    document.getElementById("skUraianSasaran").disabled = disabled;
  }

  function resetForm() {
    document.getElementById("skIdSasaran").value = "";
    document.getElementById("skIdKonteksProses").value = "";
    document.getElementById("skUraianSasaran").value = "";
  }

  function fillForm(data) {
    document.getElementById("skIdSasaran").value = data.id_sasaran;
    document.getElementById("skIdKonteksProses").value = data.id_konteks_proses;
    document.getElementById("skUraianSasaran").value = data.uraian_sasaran;
  }

  // OFFCANVAS EVENTS
  offcanvasEl.addEventListener("show.bs.offcanvas", () => {
    if (currentMode === null) setCreateMode();
    originalData = {
      id_konteks_proses: document.getElementById("skIdKonteksProses").value,
      uraian_sasaran: document.getElementById("skUraianSasaran").value,
    };
  });

  offcanvasEl.addEventListener("hidden.bs.offcanvas", () => {
    currentMode = null;
  });

  // ROW CLICK → VIEW MODE
  function bindRowClick() {
    document
      .querySelectorAll("#pkSasaranKinerjaTableWrapper .sk-row")
      .forEach((row) => {
        // KETUA → boleh lihat (view only)
        if (isKetua) {
          row.style.cursor = "pointer";

          row.addEventListener("click", function () {
            const id = this.dataset.id;

            PkAjax.get({
              url: SK_URL.detail(id),
              onSuccess(data) {
                setViewMode(data);

                // paksa tetap view mode (no edit)
                setFieldsDisabled(true);
                document.getElementById("skBtnView").style.display = "none";
                document.getElementById("skBtnEdit").style.display = "none";

                new bootstrap.Offcanvas(offcanvasEl).show();
              },
            });
          });

          return;
        }

        // OPERATOR beda tim → not allowed
        if (isOperator && bedaTim) {
          row.style.cursor = "not-allowed";

          row.addEventListener("click", () => {
            PkAlert.notAllowed({
              text: "Kamu hanya bisa melihat sasaran kinerja tim lain.",
            });
          });

          return;
        }

        // NORMAL (admin / operator tim sendiri)
        row.style.cursor = "pointer";

        row.addEventListener("click", function () {
          const id = this.dataset.id;

          PkAjax.get({
            url: SK_URL.detail(id),
            onSuccess(data) {
              setViewMode(data);
              new bootstrap.Offcanvas(offcanvasEl).show();
            },
          });
        });
      });
  }

  bindRowClick();

  // TOMBOL EDIT
  document.getElementById("skBtnSwitchEdit")?.addEventListener("click", () => {
    if (isKetua || (isOperator && bedaTim)) {
      PkAlert.notAllowed({
        text: "Kamu tidak memiliki izin untuk mengubah data ini.",
      });
      return;
    }

    setEditMode();
  });

  // TOMBOL BATAL
  document.getElementById("skBtnCancel")?.addEventListener("click", () => {
    if (currentMode === "edit" && originalData) {
      document.getElementById("skIdKonteksProses").value =
        originalData.id_konteks_proses;
      document.getElementById("skUraianSasaran").value =
        originalData.uraian_sasaran;
      setFieldsDisabled(true);
      document.getElementById("skOffcanvasTitle").textContent =
        "Detail Sasaran Kinerja";
      document.getElementById("skBtnView").style.display = "flex";
      document.getElementById("skBtnEdit").style.display = "none";
      currentMode = "view";
    } else {
      bootstrap.Offcanvas.getInstance(offcanvasEl)?.hide();
    }
  });

  // TOMBOL SIMPAN
  document
    .getElementById("skBtnSimpan")
    ?.addEventListener("click", function () {
      if (isKetua || (isOperator && bedaTim)) {
        PkAlert.notAllowed({
          text: "Kamu tidak memiliki izin untuk menyimpan perubahan.",
        });
        return;
      }
      const idKonteksProses =
        document.getElementById("skIdKonteksProses").value;
      const uraian = document.getElementById("skUraianSasaran").value.trim();

      if (!idKonteksProses || !uraian) {
        PkAlert.error({
          text: "Proses Bisnis dan Uraian Sasaran wajib diisi.",
        });
        return;
      }

      const isEdit = currentMode === "edit";
      const confirmText = isEdit
        ? "Simpan perubahan sasaran kinerja ini?"
        : "Tambah sasaran kinerja baru?";

      PkAlert.confirm({ text: confirmText }).then((result) => {
        if (!result.isConfirmed) return;

        const url = isEdit
          ? `/penetapan-konteks/sasaran-kinerja/update/${currentId}`
          : `/penetapan-konteks/sasaran-kinerja/store`;
        const params = new URLSearchParams(
          new FormData(document.getElementById("skForm")),
        );

        PkAjax.post({
          url,
          data: params.toString(),
          onSuccess(res) {
            if (res.status !== "success") return;
            bootstrap.Offcanvas.getInstance(offcanvasEl)?.hide();
            PkAlert.success({ text: res.message }).then(() => refreshTable());
          },
        });
      });
    });

  // TOMBOL DELETE
  document
    .getElementById("skBtnDelete")
    ?.addEventListener("click", function () {
      if (isKetua || (isOperator && bedaTim)) {
        PkAlert.notAllowed({
          text: "Kamu tidak memiliki izin untuk menghapus data.",
        });
        return;
      }
      PkAlert.warning({
        title: "Hapus sasaran kinerja ini?",
        text: "Data yang dihapus tidak dapat dikembalikan.",
        confirmText: "Hapus",
      }).then((result) => {
        if (!result.isConfirmed) return;

        PkAjax.post({
          url: `/penetapan-konteks/sasaran-kinerja/delete/${currentId}`,
          onSuccess(res) {
            if (res.status !== "success") return;
            bootstrap.Offcanvas.getInstance(offcanvasEl)?.hide();
            PkAlert.success({ text: res.message }).then(() => refreshTable());
          },
        });
      });
    });

  // REFRESH TABLE
  function refreshTable() {
    PkAjax.get({
      url: "/penetapan-konteks/sasaran-kinerja/table",
      onSuccess(html) {
        $("#pkSasaranKinerjaTableWrapper").html(html);
        bindRowClick();
      },
    });
  }
});
