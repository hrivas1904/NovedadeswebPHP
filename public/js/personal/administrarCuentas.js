function listarCuentasBancarias(legajo) {
    $.ajax({
        url: "/personal/cuentasBancarias",
        type: "GET",
        data: { legajo: legajo },
        success: function (respuesta) {
            if (respuesta.success) {
                let html = "";

                respuesta.data.forEach((r) => {
                    html += `
                        <div class="col-lg-4 col-md-6 col-sm-12 col-12">
                            <label class="req-card w-100">
                                <div class="req-check">
                                    <input type="radio"
                                        name="cuenta_${r.legajo}"
                                        value="${r.id}"
                                        ${r.prioridad == 1 ? "checked" : "-"}>
                                    <span class="req-title">
                                        <strong>Banco: </strong>${r.banco}
                                        ${r.prioridad == 1 ? '<span class="badge bg-success ms-2">Predeterminada</span>' : ""}
                                    </span>
                                </div>
                                <small class="req-desc">
                                    <strong>CBU: </strong>${r.cbu ?? ""}
                                </small>
                                <small class="req-desc">
                                    <strong>Cuenta N°: </strong>${r.numero_cuenta ?? "-"}
                                </small>
                            </label>
                        </div>
                    `;
                });

                $("#contenedorCuentasBancarias").html(html);
            } else {
                Swal.fire("Error", respuesta.message, "error");
            }
        },
        error: function (xhr, status, error) {
            Swal.fire("Error", error, "error");
        },
    });
}
