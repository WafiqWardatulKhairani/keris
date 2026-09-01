const PROSES_URL = window.PROSES_CONFIG?.url || {};
document.addEventListener("DOMContentLoaded", function () {
  const offcanvasEl = document.getElementById("offcanvasProsesBisnis");
  if (!offcanvasEl) return;

  // Reset tombol saat load
  const btnView = document.getElementById("pbBtnView");
  const btnEdit = document.getElementById("pbBtnEdit");

  if (btnView) btnView.style.display = "none";
  if (btnEdit) btnEdit.style.display = "none";

  // Hide tombol + Proses kalau sudah ada data
  const hasExistingData =
    document.querySelectorAll('#pbFormSync input[type="checkbox"]:checked')
      .length > 0;

  if (hasExistingData) {
    const btnAdd = document.querySelector(
      '[data-bs-target="#offcanvasProsesBisnis"]',
    );
    if (btnAdd) btnAdd.style.display = "none";
  }

  function hasData() {
    return (
      document.querySelectorAll('#pbFormSync input[type="checkbox"]:checked')
        .length > 0
    );
  }

  function setCreateMode() {
    document
      .querySelectorAll('#pbFormSync input[type="checkbox"]')
      .forEach((el) => (el.disabled = false));

    const btnView = document.getElementById("pbBtnView");
    const btnEdit = document.getElementById("pbBtnEdit");

    if (btnView) btnView.style.display = "none";
    if (btnEdit) btnEdit.style.display = "flex";
  }

  function setViewMode() {
    document
      .querySelectorAll('#pbFormSync input[type="checkbox"]')
      .forEach((el) => (el.disabled = true));

    const btnView = document.getElementById("pbBtnView");
    const btnEdit = document.getElementById("pbBtnEdit");

    if (btnView) btnView.style.display = "flex";
    if (btnEdit) btnEdit.style.display = "none";
  }

  function setEditMode() {
    document
      .querySelectorAll('#pbFormSync input[type="checkbox"]')
      .forEach((el) => (el.disabled = false));

    const btnView = document.getElementById("pbBtnView");
    const btnEdit = document.getElementById("pbBtnEdit");

    if (btnView) btnView.style.display = "none";
    if (btnEdit) btnEdit.style.display = "flex";
  }

  let originalChecked = [];

  // Saat offcanvas dibuka
  offcanvasEl.addEventListener("show.bs.offcanvas", () => {
    originalChecked = Array.from(
      document.querySelectorAll('#pbFormSync input[type="checkbox"]:checked'),
    ).map((el) => el.value);

    if (hasData()) {
      setViewMode();
    } else {
      setCreateMode();
    }
  });

  // ambil data user & konteks dari global (inject dari blade nanti)
  const currentUser = window.APP_USER || {};
  const activeKonteks = window.APP_KONTEKS || {};

  const isKetua = currentUser.role === "ketua";
  const isOperator = currentUser.role === "operator";
  const bedaTim = String(currentUser.id_tim) !== String(activeKonteks.id_tim);

  // hide tombol tambah untuk ketua
  if (isKetua) {
    const btnAdd = document.querySelector(
      '[data-bs-target="#offcanvasProsesBisnis"]',
    );
    if (btnAdd) btnAdd.style.display = "none";
  }

  // Row click → RBAC check
  document
    .querySelectorAll("#pkProsesBisnisTableWrapper tbody tr")
    .forEach((row) => {
      if (isKetua) {
        row.style.cursor = "default";
        return;
      }

      if (isOperator && bedaTim) {
        row.style.cursor = "not-allowed";

        row.addEventListener("click", () => {
          PkAlert.notAllowed({
            text: "Kamu hanya bisa melihat proses bisnis tim lain.",
          });
        });

        return;
      }

      row.style.cursor = "pointer";
      row.addEventListener("click", () => {
        new bootstrap.Offcanvas(offcanvasEl).show();
      });
    });

  document
    .getElementById("pbBtnSwitchEdit")
    ?.addEventListener("click", () => setEditMode());

  document.getElementById("pbBtnCancelEdit")?.addEventListener("click", () => {
    document
      .querySelectorAll('#pbFormSync input[type="checkbox"]')
      .forEach((el) => {
        el.checked = originalChecked.includes(el.value);
      });
    if (originalChecked.length > 0) {
      setViewMode();
    } else {
      setCreateMode();
    }
  });

  document
    .getElementById("pbBtnSimpan")
    ?.addEventListener("click", function () {
      const form = document.getElementById("pbFormSync");
      const params = new URLSearchParams(new FormData(form));

      PkAlert.confirm({ text: "Simpan perubahan proses bisnis ini?" }).then(
        (result) => {
          if (!result.isConfirmed) return;

          PkAjax.post({
            url: PROSES_URL.sync,
            data: params.toString(),
            onSuccess(res) {
              if (res.status !== "success") return;
              bootstrap.Offcanvas.getInstance(offcanvasEl)?.hide();
              PkAlert.toast({ text: res.message, icon: "success" });
              refreshTable();
            },
          });
        },
      );
    });

  document
    .getElementById("pbBtnDelete")
    ?.addEventListener("click", function () {
      PkAlert.warning({
        title: "Hapus semua proses bisnis?",
        text: "Semua proses bisnis pada konteks ini akan dihapus.",
        confirmText: "Hapus",
      }).then((result) => {
        if (!result.isConfirmed) return;

        const idKonteks = document.querySelector(
          '#pbFormSync input[name="id_konteks"]',
        ).value;

        PkAjax.post({
          url: PROSES_URL.sync,
          data: `id_konteks=${idKonteks}`,
          onSuccess(res) {
            if (res.status !== "success") return;
            bootstrap.Offcanvas.getInstance(offcanvasEl)?.hide();
            PkAlert.toast({
              text: "Semua proses bisnis berhasil dihapus.",
              icon: "success",
            });
            refreshTable();

            // Tampilkan kembali tombol + Proses
            const btnAdd = document.querySelector(
              '[data-bs-target="#offcanvasProsesBisnis"]',
            );
            if (btnAdd) btnAdd.style.display = "";
          },
        });
      });
    });

  function refreshTable() {
    PkAjax.get({
      url: PROSES_URL.table,
      onSuccess(html) {
        $("#pkProsesBisnisTableWrapper").html(html);

        // Re-bind row click setelah refresh
        document
          .querySelectorAll("#pkProsesBisnisTableWrapper tbody tr")
          .forEach((row) => {
            const isKetua = currentUser.role === "ketua";
            const isOperator = currentUser.role === "operator";
            const bedaTim =
              String(currentUser.id_tim) !== String(activeKonteks.id_tim);

            // 🔒 KETUA → full read-only (no click sama sekali)
            if (isKetua) {
              row.style.cursor = "default";
              return;
            }

            // ❌ OPERATOR beda tim → pakai alert
            if (isOperator && bedaTim) {
              row.style.cursor = "not-allowed";

              row.addEventListener("click", () => {
                PkAlert.notAllowed({
                  text: "Kamu hanya bisa melihat proses bisnis tim lain.",
                });
              });

              return;
            }

            // ✅ normal
            row.style.cursor = "pointer";
            row.addEventListener("click", () => {
              new bootstrap.Offcanvas(offcanvasEl).show();
            });
          });
      },
    });
  }
});
