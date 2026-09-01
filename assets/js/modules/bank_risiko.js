(() => {
  "use strict";

  const BASE_URL = window.location.origin;
  const modalEl = document.getElementById("modalBankRisiko");
  const modal = new bootstrap.Modal(modalEl);

  const showMode = (mode) => {
    document
      .querySelectorAll(".br-mode")
      .forEach((el) => (el.style.display = "none"));
    if (mode === "view")
      document.getElementById("bankRisikoBtnView").style.display = "flex";
    if (mode === "edit")
      document.getElementById("bankRisikoBtnEdit").style.display = "flex";
  };

  const resetForm = () => {
    document.getElementById("bankRisikoForm").reset();
    document.getElementById("bankRisikoId").value = "";
    document
      .getElementById("bankRisikoPernyataan")
      .classList.remove("is-invalid");
    document.getElementById("bankRisikoPernyataanError").textContent = "";
    document.getElementById("bankRisikoPernyataan").removeAttribute("readonly");
  };

  const getPerPage = () => document.getElementById("brPerPage")?.value ?? 10;

  const reloadTable = (page = 1) => {
    const perPage = getPerPage();
    PkAjax.get({
      url: `${BASE_URL}/bank-risiko/table?per_page=${perPage}&page=${page}`,
      onSuccess(html) {
        document.getElementById("bankRisikoTableWrapper").innerHTML = html;
        bindTableEvents();
      },
    });
  };

  const bindTableEvents = () => {
    document.querySelectorAll(".br-row").forEach((row) => {
      row.addEventListener("click", () => {
        resetForm();
        document.getElementById("bankRisikoModalTitle").textContent =
          "Detail Bank Risiko";
        document.getElementById("bankRisikoId").value = row.dataset.id;
        document.getElementById("bankRisikoPernyataan").value =
          row.dataset.pernyataan;
        document
          .getElementById("bankRisikoPernyataan")
          .setAttribute("readonly", true);
        showMode("view");
        modal.show();
      });
    });

    document
      .getElementById("brPerPage")
      ?.addEventListener("change", () => reloadTable());

    document
      .getElementById("bankRisikoTableWrapper")
      .addEventListener("click", (e) => {
        const link = e.target.closest("a[data-ci-pagination-page]");
        if (!link) return;
        e.preventDefault();
        reloadTable(link.getAttribute("data-ci-pagination-page"));
      });
  };

  bindTableEvents();

  document
    .getElementById("btnTambahBankRisiko")
    ?.addEventListener("click", () => {
      resetForm();
      document.getElementById("bankRisikoModalTitle").textContent =
        "Tambah Bank Risiko";
      showMode("edit");
      modal.show();
    });

  document
    .getElementById("bankRisikoBtnSwitchEdit")
    .addEventListener("click", () => {
      document.getElementById("bankRisikoModalTitle").textContent =
        "Edit Bank Risiko";
      document
        .getElementById("bankRisikoPernyataan")
        .removeAttribute("readonly");
      showMode("edit");
    });

  document
    .getElementById("bankRisikoBtnCancel")
    .addEventListener("click", () => {
      const id = document.getElementById("bankRisikoId").value;
      if (id) {
        document.getElementById("bankRisikoModalTitle").textContent =
          "Detail Bank Risiko";
        document
          .getElementById("bankRisikoPernyataan")
          .setAttribute("readonly", true);
        showMode("view");
      } else {
        modal.hide();
      }
    });

  document
    .getElementById("bankRisikoBtnSimpan")
    .addEventListener("click", () => {
      const id = document.getElementById("bankRisikoId").value;
      const pernyataan = document
        .getElementById("bankRisikoPernyataan")
        .value.trim();

      if (!pernyataan) {
        document
          .getElementById("bankRisikoPernyataan")
          .classList.add("is-invalid");
        document.getElementById("bankRisikoPernyataanError").textContent =
          "Pernyataan risiko wajib diisi.";
        return;
      }

      PkAlert.confirm({
        title: id ? "Simpan Perubahan?" : "Tambah Bank Risiko?",
        text: id
          ? "Data pernyataan risiko akan diperbarui."
          : "Data baru akan ditambahkan ke bank risiko.",
        confirmText: "Simpan",
        icon: "question",
      }).then((result) => {
        if (!result.isConfirmed) return;

        PkAjax.post({
          url: id
            ? `${BASE_URL}/bank-risiko/update/${id}`
            : `${BASE_URL}/bank-risiko/store`,
          data: { pernyataan_risiko: pernyataan },
          onSuccess(res) {
            if (res.status === "success") {
              modal.hide();
              reloadTable();
              PkAlert.toast({ text: res.message, icon: "success" });
            }
          },
        });
      });
    });

  document
    .getElementById("bankRisikoBtnDelete")
    .addEventListener("click", () => {
      const id = document.getElementById("bankRisikoId").value;
      if (!id) return;

      PkAlert.warning({
        title: "Hapus Bank Risiko?",
        text: "Data yang dihapus tidak dapat dikembalikan.",
        confirmText: "Hapus",
      }).then((result) => {
        if (!result.isConfirmed) return;

        PkAjax.post({
          url: `${BASE_URL}/bank-risiko/delete/${id}`,
          onSuccess(res) {
            if (res.status === "success") {
              modal.hide();
              reloadTable();
              PkAlert.toast({ text: res.message, icon: "success" });
            }
          },
        });
      });
    });

  modalEl.addEventListener("hidden.bs.modal", () => resetForm());
})();
