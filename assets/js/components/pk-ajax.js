// ======================================================
// PK AJAX COMPONENT
// Wrapper jQuery AJAX untuk operasi CRUD
// ======================================================

const PkAjax = {
  post({ url, data, onSuccess, onError } = {}) {
    return $.ajax({
      url,
      method: "POST",
      data,
      headers: { "X-Requested-With": "XMLHttpRequest" },
      success(res) {
        if (typeof onSuccess === "function") onSuccess(res);
      },
      error(err) {
        if (typeof onError === "function") onError(err);
        else PkAlert.error();
      },
    });
  },

  get({ url, onSuccess, onError } = {}) {
    return $.ajax({
      url,
      method: "GET",
      headers: { "X-Requested-With": "XMLHttpRequest" },
      success(res) {
        if (typeof onSuccess === "function") onSuccess(res);
      },
      error(err) {
        if (typeof onError === "function") onError(err);
        else PkAlert.error();
      },
    });
  },
};
