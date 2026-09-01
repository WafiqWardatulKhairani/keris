document.addEventListener("DOMContentLoaded", function () {
  function loadData() {
    const tahun = document.getElementById("filterTahun").value;
    const triwulan = document.getElementById("filterTriwulan").value;

    fetch(`/mr-instansi/data?tahun=${tahun}&triwulan=${triwulan}`)
      .then((res) => res.json())
      .then((data) => {
        const tbody = document.getElementById("mrTableBody");
        tbody.innerHTML = "";

        if (!data.length) {
          tbody.innerHTML = `<tr><td colspan="5" class="text-center">Tidak ada data</td></tr>`;
          return;
        }

        data.forEach((item) => {
          tbody.insertAdjacentHTML(
            "beforeend",
            `
                <tr>
                    <td><span class="mr-badge">${item.sumber}</span></td>
                    <td><div class="mr-text" title="${item.pernyataan_risiko}">${item.pernyataan_risiko}</div></td>
                    <td><div class="mr-text" title="${item.kendala}">${item.kendala}</div></td>
                    <td><div class="mr-text" title="${item.solusi}">${item.solusi}</div></td>
                    <td><div class="mr-text" title="${item.rtp}">${item.rtp}</div></td>
                </tr>
            `,
          );
        });
      });
  }

  document.getElementById("btnFilter").addEventListener("click", loadData);

  document.getElementById("btnSync").addEventListener("click", function () {
    fetch(`/mr-instansi/sync`, { method: "POST" })
      .then((res) => res.json())
      .then((res) => {
        alert(res.message);
        loadData();
      });
  });

  loadData();
});

function loadData() {
  const tbody = document.getElementById("mrTableBody");

  tbody.innerHTML = `
        <tr>
            <td colspan="5" class="text-center py-4 text-muted">
                <i class="ti ti-loader me-1"></i> Memuat...
            </td>
        </tr>
    `;

  fetch(`/mr-instansi/data`)
    .then((res) => res.json())
    .then((data) => {
      tbody.innerHTML = "";

      if (!data.length) {
        tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="ti ti-inbox fs-3 d-block mb-2 opacity-25"></i>
                        Tidak ada data
                    </td>
                </tr>
            `;
        return;
      }

      data.forEach((item) => {
        tbody.insertAdjacentHTML(
          "beforeend",
          `
                <tr>
                    <td>
                        <span class="badge bg-primary-subtle text-primary border border-primary">
                            ${item.sumber}
                        </span>
                    </td>
                    <td class="text-truncate" style="max-width:250px" title="${item.pernyataan_risiko}">
                        ${item.pernyataan_risiko}
                    </td>
                    <td class="text-truncate" style="max-width:250px" title="${item.kendala}">
                        ${item.kendala}
                    </td>
                    <td class="text-truncate" style="max-width:250px" title="${item.solusi}">
                        ${item.solusi}
                    </td>
                    <td class="text-truncate" style="max-width:250px" title="${item.rtp}">
                        ${item.rtp}
                    </td>
                </tr>
            `,
        );
      });
    });
}