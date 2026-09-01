function confirmSavePemangkuKepentingan(form) {
  Swal.fire({
    title: "Simpan Pemangku Kepentingan?",
    text: "Data pemangku kepentingan akan disimpan.",
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

function confirmUpdatePemangkuKepentingan(form) {
  Swal.fire({
    title: "Ubah Pemangku Kepentingan?",
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
