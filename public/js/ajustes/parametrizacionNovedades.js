function cargarNovedades() {
    if ($("#tb_configuracion").length > 0) {
        $("#tb_configuracion").DataTable({
            ajax: {
                url: "/rrhh/novedades/listarConceptos",
                type: "GET",
                error: function (e) {
                    console.error("Error al cargar datos:", e.responseText);
                },
            },
            language: {
                url: "/js/es-ES.json",
                lengthMenu: "_MENU_",
                paginate: {
                    first: "<<",
                    previous: "<",
                    next: ">",
                    last: ">>",
                },
            },
            order: [[1, "asc"]],
            info: false,
            createdRow: function (row, data) {
                $(row).attr("data-id", data.ID_NOVEDAD);
            },
            columns: [
                { data: "ID_NOVEDAD", visible: false },
                {
                    data: "CODIGO_NOVEDAD",
                    width: "10%",
                    render: function (data) {
                        return `
                            <input type="text" class="form-control inputNovedad" value="${data ?? ""}" data-campo="codigoNovedad">       
                        `;
                    },
                },
                {
                    data: "NOVEDAD",
                    width: "25%",
                    render: function (data) {
                        return `
                            <input type="text" class="form-control inputNovedad" value="${data ?? ""}" data-campo="nombreNovedad">       
                        `;
                    },
                },
                {
                    data: "TIPO_VALOR",
                    width: "15%",
                    render: function (data) {
                        return `
                            <select class="form-select inputNovedad" data-campo="tipoValorNovedad">
                                <option value="Días" ${data === "Días" ? "selected" : ""}>DÍAS</option>
                                <option value="Horas" ${data === "Horas" ? "selected" : ""}>HORAS</option>
                                <option value="Pesos" ${data === "Pesos" ? "selected" : ""}>PESOS</option>
                                <option value="Unidades" ${data === "Unidades" ? "selected" : ""}>UNIDADES</option>
                            </select>
                        `;
                    },
                },
                {
                    data: "LIMITE",
                    width: "5%",
                    render: function (data) {
                        return `
                            <input type="text" class="form-control inputNovedad" value="${data ?? ""}" data-campo="limiteNovedad">       
                        `;
                    },
                },
                {
                    data: "PARA_FINNEGANS",
                    width: "5%",
                    className: "text-center",
                    render: function (data, type, row) {
                        const isChecked = parseInt(data) === 1 ? "checked" : "";
                        return `
                            <div class="form-check form-switch">
                                <input class="form-check-input switch-finnegans inputNovedad" type="checkbox" ${isChecked} data-campo="finnegansNovedad">
                            </div>
                        `;
                    },
                },
                {
                    data: "abreviatura",
                    width: "10%",
                    render: function (data) {
                        return `
                            <input type="text" class="form-control inputNovedad" value="${data ?? ""}" data-campo="abreviaturaNovedad">       
                        `;
                    },
                },
                {
                    data: "ACTIVA",
                    width: "5%",
                    className: "text-center",
                    render: function (data, type, row) {
                        const isChecked = parseInt(data) === 1 ? "checked" : "";
                        return `
                            <div class="form-check form-switch">
                                <input class="form-check-input switch-finnegans inputNovedad" type="checkbox" ${isChecked} data-campo="estadoNovedad">
                            </div>
                        `;
                    },
                },
            ],
            dom: "<'d-top d-flex flex-column flex-md-row align-items-md-center gap-2 mt-1 mx-1' \
                    <'d-flex flex-column flex-sm-row gap-2'> \
                    <'ms-md-auto mt-2 mt-md-0'> \
                > \
                <'my-2'rt> \
                <'d-bottom d-flex justify-content-center'i>",
            scrollX: true,
            paging: false,
            scrollCollapse: true,
            scrollY: "56vh",
        });
    }
}

$(document).on("submit", "#formNuevaNovedad", function (e) {
    e.preventDefault();

    let form = $(this);

    $.ajax({
        url: "/rrhh/novedades/crear",
        method: "POST",
        data: form.serialize(),
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (resp) {
            console.log("RESP:", resp);

            const icono = resp.ok ? "success" : "warning";
            const titulo = resp.ok ? "Operación Exitosa" : "Atención";

            Swal.fire({
                icon: icono,
                title: titulo,
                text: resp.mensaje,
                customClass: {
                    confirmButton: "btn btn-primary",
                },
            });

            if (resp.ok) {
                form[0].reset();
                $("#tb_configuracion").DataTable().ajax.reload(null, false);
            }
        },
        error: function (xhr) {
            console.error("ERROR:", xhr.responseJSON);

            Swal.fire({
                icon: "error",
                title: "Error",
                text: xhr.responseJSON?.error || "Error inesperado",
                showConfirmButton: false,
                timer: 1200,
            });
        },
    });
});

$(document)
    .off("change", "#tb_configuracion .inputNovedad")
    .on("change", "#tb_configuracion .inputNovedad", function () {
        const input = $(this);
        const fila = input.closest("tr");

        const idConcepto = fila.data("id");
        const campo = input.data("campo");

        let valor;

        if (input.attr("type") === "checkbox") {
            valor = input.is(":checked") ? 1 : 0;
        } else {
            valor = input.val();
        }

        $.ajax({
            url: `/rrhh/novedades/${idConcepto}/campo`,
            type: "PUT",

            data: {
                campo: campo,
                valor: valor,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },

            success: function (response) {
                Swal.fire({
                    icon: "success",
                    title: "Operación exitosa!",
                    text: "Concepto actualizado correctamente.",
                    timer: 1200,
                    showConfirmButton: false,
                });
            },

            error: function (xhr) {
                console.error(xhr.responseText);

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo actualizar el concepto.",
                    timer: 1800,
                    showConfirmButton: false,
                });
            },
        });
    });
