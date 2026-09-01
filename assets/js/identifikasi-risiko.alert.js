function confirmSaveIdentifikasi(form, mode) {
  let url;

  if (mode === "edit") {
    const id = document.getElementById("id_identifikasi").value;
    url =
      window.location.origin + "/index.php/identifikasi-risiko/update/" + id;
  } else {
    url = window.location.origin + "/index.php/identifikasi-risiko/store";
  }

  Swal.fire({
    title: mode === "edit" ? "Ubah Risiko?" : "Simpan Risiko?",
    icon: mode === "edit" ? "warning" : "question",
    showCancelButton: true,
    confirmButtonText: mode === "edit" ? "Ubah" : "Simpan",
    cancelButtonText: "Batal",
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) {
      fetch(url, {
        method: "POST",
        body: new FormData(form),
      })
        .then((res) => res.json())
        .then(() => {
          Swal.fire({
            icon: "success",
            title: "Berhasil",
            timer: 1000,
            showConfirmButton: false,
          }).then(() => location.reload());
        });
    }
  });
}

function confirmDeleteIdentifikasi(id) {
  Swal.fire({
    title: "Hapus Risiko?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Hapus",
    cancelButtonText: "Batal",
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) {
      fetch(
        window.location.origin + "/index.php/identifikasi-risiko/delete/" + id,
        {
          method: "POST",
        },
      )
        .then((res) => res.json())
        .then(() => {
          Swal.fire({
            icon: "success",
            title: "Berhasil dihapus",
            timer: 1000,
            showConfirmButton: false,
          }).then(() => location.reload());
        });
    }
  });
}
