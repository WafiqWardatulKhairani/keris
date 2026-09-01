document.addEventListener("DOMContentLoaded", function () {
  const offcanvasEl = document.getElementById("offcanvasPemangku");
  if (!offcanvasEl) return;

  let currentMode = null;
  let currentId = null;
  let originalData = null;

  // ── MODE HELPERS ──────────────────────────────────────────
  function setCreateMode() {
    currentMode = "create";
    currentId = null;
    document.getElementById("pmOffcanvasTitle").textContent =
      "Tambah Pemangku Kepentingan";
    resetForm();
    setFieldsDisabled(false);
    document.getElementById("pmBtnView").style.display = "none";
    document.getElementById("pmBtnEdit").style.display = "flex";
  }

  function setViewMode(data) {
    currentMode = "view";
    currentId = data.id_pemangku;
    document.getElementById("pmOffcanvasTitle").textContent =
      "Detail Pemangku Kepentingan";
    fillForm(data);
    setFieldsDisabled(true);
    document.getElementById("pmBtnView").style.display = "flex";
    document.getElementById("pmBtnEdit").style.display = "none";
  }

  function setEditMode() {
    currentMode = "edit";
    document.getElementById("pmOffcanvasTitle").textContent =
      "Edit Pemangku Kepentingan";
    setFieldsDisabled(false);
    document.getElementById("pmBtnView").style.display = "none";
    document.getElementById("pmBtnEdit").style.display = "flex";
  }

  function setFieldsDisabled(disabled) {
    document.getElementById("pmNamaInstansi").disabled = disabled;
    document.getElementById("pmHubungan").disabled = disabled;
  }

  function resetForm() {
    document.getElementById("pmIdPemangku").value = "";
    document.getElementById("pmNamaInstansi").value = "";
    document.getElementById("pmHubungan").value = "";
  }

  function fillForm(data) {
    document.getElementById("pmIdPemangku").value = data.id_pemangku;
    document.getElementById("pmNamaInstansi").value = data.nama_instansi;
    document.getElementById("pmHubungan").value = data.hubungan;
  }

  // ── OFFCANVAS EVENTS ──────────────────────────────────────
  offcanvasEl.addEventListener("show.bs.offcanvas", () => {
    if (currentMode === null) setCreateMode();
    originalData = {
      nama_instansi: document.getElementById("pmNamaInstansi").value,
      hubungan: document.getElementById("pmHubungan").value,
    };
  });

  offcanvasEl.addEventListener("hidden.bs.offcanvas", () => {
    currentMode = null;
  });

  // ── ROW CLICK → VIEW MODE ─────────────────────────────────
  function bindRowClick() {
    document
      .querySelectorAll("#pkPemangkuTableWrapper .pm-row")
      .forEach((row) => {
        row.addEventListener("click", function () {
          const id = this.dataset.id;
          PkAjax.get({
            url: `/penetapan-konteks/pemangku/detail/${id}`,
            onSuccess(data) {
              setViewMode(data);
              new bootstrap.Offcanvas(offcanvasEl).show();
            },
          });
        });
      });
  }

  bindRowClick();

  // ── TOMBOL EDIT ───────────────────────────────────────────
  document
    .getElementById("pmBtnSwitchEdit")
    ?.addEventListener("click", () => setEditMode());

  // ── TOMBOL BATAL ──────────────────────────────────────────
  document.getElementById("pmBtnCancel")?.addEventListener("click", () => {
    if (currentMode === "edit" && originalData) {
      document.getElementById("pmNamaInstansi").value =
        originalData.nama_instansi;
      document.getElementById("pmHubungan").value = originalData.hubungan;
      setFieldsDisabled(true);
      document.getElementById("pmOffcanvasTitle").textContent =
        "Detail Pemangku Kepentingan";
      document.getElementById("pmBtnView").style.display = "flex";
      document.getElementById("pmBtnEdit").style.display = "none";
      currentMode = "view";
    } else {
      bootstrap.Offcanvas.getInstance(offcanvasEl)?.hide();
    }
  });

  // ── TOMBOL SIMPAN ─────────────────────────────────────────
  document
    .getElementById("pmBtnSimpan")
    ?.addEventListener("click", function () {
      const namaInstansi = document
        .getElementById("pmNamaInstansi")
        .value.trim();
      const hubungan = document.getElementById("pmHubungan").value.trim();

      if (!namaInstansi || !hubungan) {
        PkAlert.error({ text: "Nama Instansi dan Hubungan wajib diisi." });
        return;
      }

      const isEdit = currentMode === "edit";
      const confirmText = isEdit
        ? "Simpan perubahan pemangku kepentingan ini?"
        : "Tambah pemangku kepentingan baru?";

      PkAlert.confirm({ text: confirmText }).then((result) => {
        if (!result.isConfirmed) return;

        const url = isEdit
          ? `/penetapan-konteks/pemangku/update/${currentId}`
          : `/penetapan-konteks/pemangku/store`;
        const params = new URLSearchParams(
          new FormData(document.getElementById("pmForm")),
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

  // ── TOMBOL DELETE ─────────────────────────────────────────
  document
    .getElementById("pmBtnDelete")
    ?.addEventListener("click", function () {
      PkAlert.warning({
        title: "Hapus pemangku kepentingan ini?",
        text: "Data yang dihapus tidak dapat dikembalikan.",
        confirmText: "Hapus",
      }).then((result) => {
        if (!result.isConfirmed) return;

        const idKonteks = document.querySelector(
          "#pmForm input[name='id_konteks']",
        ).value;
        const params = new URLSearchParams({ id_konteks: idKonteks });

        PkAjax.post({
          url: `/penetapan-konteks/pemangku/delete/${currentId}`,
          data: params.toString(),
          onSuccess(res) {
            if (res.status !== "success") return;
            bootstrap.Offcanvas.getInstance(offcanvasEl)?.hide();
            PkAlert.success({ text: res.message }).then(() => refreshTable());
          },
        });
      });
    });

  // ── REFRESH TABLE ─────────────────────────────────────────
  function refreshTable() {
    PkAjax.get({
      url: PEMANGKU_CONFIG.url.table,
      onSuccess(html) {
        $("#pkPemangkuTableWrapper").html(html);
        bindRowClick();
      },
    });
  }
});
