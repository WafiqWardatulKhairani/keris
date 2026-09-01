// ======================================================
// PK ALERT COMPONENT
// Wrapper SweetAlert2 untuk digunakan di semua modul
// ======================================================

const PkAlert = {
  confirm({
    title = "Konfirmasi",
    text,
    confirmText = "Ya",
    cancelText = "Batal",
    icon = "question",
    confirmColor = "#2f80ed",
  } = {}) {
    return Swal.fire({
      title,
      text,
      icon,
      showCancelButton: true,
      confirmButtonText: confirmText,
      cancelButtonText: cancelText,
      confirmButtonColor: confirmColor,
    });
  },

  success({ title = "Berhasil", text, confirmText = "Oke" } = {}) {
    return Swal.fire({
      title,
      text,
      icon: "success",
      confirmButtonText: confirmText,
      confirmButtonColor: "#2f80ed",
    });
  },

  error({
    title = "Gagal",
    text = "Terjadi kesalahan.",
    confirmText = "Oke",
  } = {}) {
    return Swal.fire({
      title,
      text,
      icon: "error",
      confirmButtonText: confirmText,
      confirmButtonColor: "#2f80ed",
    });
  },

  warning({
    title,
    text,
    confirmText = "Hapus",
    cancelText = "Batal",
    confirmColor = "#e53e3e",
  } = {}) {
    return Swal.fire({
      title,
      text,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: confirmText,
      cancelButtonText: cancelText,
      confirmButtonColor: confirmColor,
    });
  },

  notAllowed({
    title = "Akses Ditolak",
    text = "Kamu tidak memiliki akses untuk aksi ini.",
    confirmText = "Mengerti",
  } = {}) {
    return Swal.fire({
      icon: "error",
      title,
      text,
      confirmButtonText: confirmText,
      confirmButtonColor: "#e53e3e",
      allowOutsideClick: false,
      allowEscapeKey: false,
    });
  },

  toast({ text, icon = "success", duration = 3000 } = {}) {
    const existing = document.getElementById("pkToastEl");
    if (existing) existing.remove();

    const colors = {
      success: { border: "#22c55e", iconColor: "#22c55e", icon: "✓" },
      warning: { border: "#f59e0b", iconColor: "#f59e0b", icon: "⚠" },
      error: { border: "#ef4444", iconColor: "#ef4444", icon: "✕" },
      info: { border: "#3b82f6", iconColor: "#3b82f6", icon: "ℹ" },
    };

    const c = colors[icon] ?? colors.info;

    const toast = document.createElement("div");
    toast.id = "pkToastEl";
    toast.style.cssText = `
        position: fixed;
        top: 16px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        border-left: 4px solid ${c.border};
        padding: 8px 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        white-space: nowrap;
        animation: pkToastIn 0.25s ease;
    `;

    toast.innerHTML = `
        <style>
            @keyframes pkToastIn {
                from { opacity: 0; transform: translateX(-50%) translateY(-10px); }
                to   { opacity: 1; transform: translateX(-50%) translateY(0); }
            }
            @keyframes pkToastOut {
                from { opacity: 1; transform: translateX(-50%) translateY(0); }
                to   { opacity: 0; transform: translateX(-50%) translateY(-10px); }
            }
        </style>
        <span style="font-size:14px; color:${c.iconColor};">${c.icon}</span>
        <span style="font-size:13px; color:#374151;">${text}</span>
    `;

    document.body.appendChild(toast);

    const remove = () => {
      toast.style.animation = "pkToastOut 0.25s ease forwards";
      setTimeout(() => toast.remove(), 250);
    };

    setTimeout(remove, duration);
  },
};

