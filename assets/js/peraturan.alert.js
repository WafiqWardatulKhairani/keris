function confirmSavePeraturan(form) {
  Swal.fire({
    title: "Simpan Peraturan?",
    text: "Data peraturan akan disimpan",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Simpan",
    cancelButtonText: "Batal",
    customClass: { popup: "swal-mantis" },
  }).then((result) => {
    if (result.isConfirmed) {
      form.submit();
    }
  });
}

function confirmUpdatePeraturan(form) {
  Swal.fire({
    title: "Ubah Peraturan?",
    text: "Perubahan akan langsung disimpan",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Simpan",
    cancelButtonText: "Batal",
    customClass: { popup: "swal-mantis" },
  }).then((result) => {
    if (result.isConfirmed) {
      form.submit();
    }
  });
}
