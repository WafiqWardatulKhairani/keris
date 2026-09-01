function confirmSaveSasaranKinerja(form) {
  Swal.fire({
    title: "Simpan Sasaran Kinerja?",
    text: "Data sasaran kinerja akan disimpan.",
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

function confirmUpdateSasaranKinerja(form) {
  Swal.fire({
    title: "Ubah Sasaran Kinerja?",
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
