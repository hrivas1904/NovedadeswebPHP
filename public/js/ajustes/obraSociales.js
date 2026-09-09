function cargarTablaOs() {
    if ($("#tb_obraSocial").length > 0) {
        $("#tb_obraSocial").DataTable({
            ajax: {
                url: "/rrhh/obra-social/lista",
                type: "GET",
                dataSrc: "",
            },
            createdRow: function(row, data) {
                $(row).attr("data-id", data.id);
            },
            columns: [
                { data: "id", className:"text-start" },
                { data: "text",
                    render:function(data){
                        return(
                            `
                                <input class="form-control inputObra" data-campo="nombreObra" value="${data}">
                            `
                        );
                    }
                 },
                { data: "codigo",
                    render:function(data){
                        return(
                            `
                                <input class="form-control inputObra" data-campo="codigoObra" value="${data}">
                            `
                        );
                    }
                 },
                { data: "estado", className:"text-center",
                    render: function (data, type, row) {
                        if (type !== "display") {
                            return data;
                        }
                        const checked = Number(data) === 1 ? "checked" : "";
                        return `
                            <div class="form-check form-switch d-flex justify-content-center m-0">
                                <input
                                    class="form-check-input switchActivoServicio inputObra"
                                    type="checkbox"
                                    role="switch"
                                    data-campo="estadoObra"
                                    data-id="${row.id}"
                                    ${checked}>
                            </div>
                        `;
                    },
                 },
            ],
            language: {
                url: "/js/es-ES.json",
            },
            info: false,
            order: [[1, "asc"]],
            autoWidth: false,
            scrollX: true,
            paging: false,
            scrollCollapse: true,
            scrollY: "58vh",
            dom:'tir'
        });
    }
};

$(document).on("submit", "#formNuevaObraSocial", function (e) {
    e.preventDefault();

    let form = $(this);

    // 👉 deshabilitar botón (evita doble click)
    $("#btnCrearOs").prop("disabled", true);

    $.ajax({
        url: "/rrhh/obra-social/crear",
        method: "POST",
        data: form.serialize(),
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (resp) {
            Swal.fire({
                icon: resp.success ? "success" : "warning",
                title: resp.success ? "Correcto" : "Atención",
                text: resp.mensaje,
            }).then(() => {
                if (resp.success) {
                    form[0].reset();

                    // 👉 si tenés select de obras sociales
                    if (typeof cargarObrasSociales === "function") {
                        cargarObrasSociales();
                    }
                    $("#modalNuevaOs").modal("hide");
                    $("#tb_obraSocial").DataTable().ajax.reload(null, false);
                }

                $("#btnCrearOs").prop("disabled", false);
            });
        },
        error: function (xhr) {
            console.error("ERROR:", xhr.responseJSON);

            Swal.fire({
                icon: "error",
                title: "Error",
                text: xhr.responseJSON?.error || "Error inesperado",
            });

            $("#btnCrearOs").prop("disabled", false);
        },
    });
});

$(document)
    .off("change", "#tb_obraSocial .inputObra")
    .on("change", "#tb_obraSocial .inputObra", function () {

        const input = $(this);
        const fila = input.closest("tr");

        const idObra = fila.data("id");
        const campo = input.data("campo");

        let valor;

        if (input.attr("type") === "checkbox") {

            valor = input.is(":checked") ? 1 : 0;

        } else {

            valor = input.val().trim();

        }


        if (campo === "nombreObra" && valor === "") {

            Swal.fire({
                title: "Atención!",
                text: "El nombre de la obra social es obligatorio.",
                icon: "warning",
                timer: 1500,
                showConfirmButton: false
            });

            return;
        }


        $.ajax({

            url: `/rrhh/obras-sociales/${idObra}/campo`,

            type: "PUT",

            data: {
                campo: campo,
                valor: valor,
                _token: $('meta[name="csrf-token"]').attr("content")
            },

            success: function(response) {

                Swal.fire({
                    title: "Operación exitosa!",
                    text: "Obra social actualizada correctamente.",
                    icon: "success",
                    timer: 1200,
                    showConfirmButton: false
                });

            },

            error: function(xhr) {

                console.error(xhr.responseText);

                let mensaje = "No se pudo actualizar la obra social.";

                if (xhr.responseJSON?.message) {
                    mensaje = xhr.responseJSON.message;
                }

                Swal.fire({
                    title: "Error!",
                    text: mensaje,
                    icon: "error",
                    timer: 1200,
                    showConfirmButton: false
                });

            }

        });

    });