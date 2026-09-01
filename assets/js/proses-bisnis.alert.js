function confirmSaveProsesBisnis(form) {
  Swal.fire({
    title: "Simpan Proses Bisnis?",
    text: "Data proses bisnis akan disimpan.",
    icon: "question",
    customClass: {
      popup: "swal-mantis",
    },
    showCancelButton: true,
    confirmButtonText: "Simpan",
    cancelButtonText: "Batal",
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) {
      form.submit();
    }
  });
}

function confirmUpdateProsesBisnis(form) {
  Swal.fire({
    title: "Ubah Proses Bisnis?",
    text: "Perubahan akan langsung disimpan.",
    icon: "warning",
    customClass: {
      popup: "swal-mantis",
    },
    showCancelButton: true,
    confirmButtonText: "Ubah",
    cancelButtonText: "Batal",
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) {
      form.submit();
    }
  });
}
