let tablaFuncionesAdicAreas = null;
let $select = null;
let novedadesFunciones = [];
let d = null;

function cargarFuncionesAreaSeleccionada(idArea, nombreArea) {
    $("#inputIdAreaFuncAdic").val(idArea);

    if (novedadesFunciones.length === 0) {
        $.ajax({
            url: "/rrhh/novedades/selector",
            type: "GET",
            dataType: "json",

            success: function (data) {
                novedadesFunciones = data;

                cargarFuncionesAreaSeleccionada(idArea, nombreArea);
            },

            error: function (err) {
                console.error("Error al cargar novedades:", err);
            },
        });

        return;
    }

    if ($.fn.DataTable.isDataTable("#tb_funciones_adic")) {
        $("#tb_funciones_adic")
            .DataTable()
            .ajax.url(`/rrhh/funcionesAdicionales/por-area/${idArea}`)
            .load();
        return;
    }

    tablaFuncionesAdicAreas = $("#tb_funciones_adic").DataTable({
        ajax: {
            url: `/rrhh/funcionesAdicionales/por-area/${idArea}`,
            type: "GET",
            dataSrc: "",
        },

        columns: [
            { data: "id", className: "text-start" },
            {
                data: "funcion",
                className: "text-start",
                render: function (data) {
                    return `
                        <input type="text" class="form-control inputFuncion" data-campo="nombre" value="${data}">
                    `;
                },
            },
            {
                data: "marca",
                className: "text-start",
                render: function (data) {
                    const d = data ?? "";
                    return `
                        <input type="text" class="form-control inputFuncion" data-campo="marca" value="${d}">
                    `;
                },
            },
            {
                data: "id_novedad",
                className: "text-start",

                render: function (data, type, row) {
                    let opciones = `
                        <option value="">
                            Sin novedad
                        </option>
                    `;

                    novedadesFunciones.forEach(function (novedad) {
                        const selected =
                            Number(novedad.id) === Number(row.id_novedad)
                                ? "selected"
                                : "";

                        opciones += `
                            <option
                                value="${novedad.id}"
                                ${selected}>

                                ${novedad.text}

                            </option>
                        `;
                    });

                    return `
                        <select
                            class="form-select inputFuncionAdicional inputFuncion"
                            data-campo="id_novedad">
                            ${opciones}

                        </select>
                    `;
                },
            },
            {
                data: "cod_liq",
                className: "text-start",
                render: function (data) {
                    const d = data ?? "";
                    return `
                        <input type="text" class="form-control" value="${d}" readonly>
                    `;
                },
            },
            {
                data: "unidad",
                className: "text-start",
                render: function (data) {
                    const d = data ?? "";
                    return `
                        <input type="text" class="form-control" value="${d}" readonly>
                    `;
                },
            },
            {
                data: "activo",
                className: "text-center",
                orderable: false,
                render: function (data, type, row) {
                    if (type !== "display") {
                        return data;
                    }
                    const checked = Number(data) === 1 ? "checked" : "";
                    return `
                        <div class="form-check form-switch d-flex justify-content-center m-0">
                            <input
                                class="form-check-input switchActivoServicio inputFuncion"
                                type="checkbox"
                                role="switch"
                                data-campo="estado"
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
        paging: false,
        searching: false,
        info: false,
        autoWidth: false,
        scrollX: false,
        scrollY: "55vh",
        scrollCollapse: true,
    });
}

function cargarNovedadesFunciones() {
    return $.ajax({
        url: "/rrhh/novedades/selector",
        type: "GET",
        dataType: "json",
        success: function (data) {
            novedadesFunciones = data;
        },
        error: function (err) {
            console.error("Error al cargar novedades:", err);
        },
    });
}

function cargarSelectorNovedadesFuncion() {
    const $select = $("#selectNovedadFuncion");

    if (!$select.length) {
        return;
    }

    // Evita inicializar Select2 nuevamente
    if ($select.hasClass("select2-hidden-accessible")) {
        return;
    }

    $.ajax({
        url: "/rrhh/novedades/selector",

        type: "GET",

        dataType: "json",

        success: function (data) {
            $select.select2({
                data: data,
                placeholder: "Seleccione una novedad",
                allowClear: true,
                width: "100%",
            });
        },

        error: function (err) {
            console.error("Error al cargar novedades:", err);
        },
    });

    $select.on("select2:select", function (e) {
        const novedad = e.params.data;

        $("#inputIdNovedadFuncAdic").val(novedad.id);
    });

    $select.on("select2:clear", function () {
        $("#inputIdNovedadFuncAdic").val("");
    });
}

$(document)
    .off("submit", "#formNuevaFuncionAdicional")
    .on("submit", "#formNuevaFuncionAdicional", function (e) {
        e.preventDefault();

        console.log("SUBMIT FUNCIÓN ADICIONAL CAPTURADO");

        const $form = $(this);

        const idArea = $("#inputIdAreaFuncAdic").val();
        const idNovedad = $("#inputIdNovedadFuncAdic").val();

        console.log({
            idArea,
            idNovedad,
            datos: $form.serialize(),
        });

        if (!idArea) {
            Swal.fire({
                icon: "warning",
                title: "Atención",
                text: "Primero debe seleccionar un área.",
                confirmButtonColor: "#1DAC8A",
            });

            return false;
        }

        $.ajax({
            url: "/rrhh/funciones-adicionales/crear",
            type: "POST",
            data: $form.serialize(),

            success: function (response) {
                console.log("RESPUESTA ALTA FUNCIÓN:", response);

                $("#formNuevaFuncionAdicional")[0].reset();

                $("#inputIdAreaFuncAdic").val(idArea);

                $("#selectNovedadFuncion").val(null).trigger("change");

                $("#inputIdNovedadFuncAdic").val("");

                Swal.fire({
                    title: "Operación exitosa!",
                    text: "Función adicional creada correctamente.",
                    icon: "success",
                    timer: 1200,
                    showConfirmButton: false,
                });

                $("#tb_funciones_adic").DataTable().ajax.reload(null, false);
            },

            error: function (xhr) {
                console.error("ERROR AL CREAR FUNCIÓN:", xhr);

                console.error("RESPUESTA:", xhr.responseText);

                let mensaje = "No se pudo crear la función adicional.";

                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    const errores = Object.values(xhr.responseJSON.errors);

                    mensaje = errores[0][0];
                } else if (xhr.responseJSON?.message) {
                    mensaje = xhr.responseJSON.message;
                }

                Swal.fire({
                    title: "Error",
                    text: mensaje,
                    icon: "error",
                });
            },
        });

        return false;
    });

$(document).on("change", "#tb_funciones_adic .inputFuncion", function () {
    const input = $(this);

    const tabla = $("#tb_funciones_adic").DataTable();

    const fila = input.closest("tr");

    const datos = tabla.row(fila).data();

    if (!datos) return;

    const idFuncion = datos.id;

    const campo = input.attr("data-campo");

    const idArea = $("#inputIdAreaFuncAdic").val();

    let valor;

    if (input.is(":checkbox")) {
        valor = input.is(":checked") ? 1 : 0;
    } else {
        valor = input.val();
    }

    console.log({
        idFuncion,
        campo,
        valor,
        idArea,
    });

    actualizarCampoFuncionAdicional(idFuncion, campo, valor, idArea);
});

function actualizarCampoFuncionAdicional(idFuncion, campo, valor, idArea) {
    $.ajax({
        url: `/rrhh/funcionesAdicionales/${idFuncion}/campo`,

        type: "PUT",

        data: {
            _token: $('meta[name="csrf-token"]').attr("content"),
            campo: campo,
            valor: valor,
            idArea: idArea,
        },

        success: function (response) {
            Swal.fire({
                title: "Operación exitosa!",
                icon: 'success',
                text: "Función adicional actualizada correctamente.",
                timer: 1200,
                showConfirmButton: false,
            });

            /*
             * Recargamos para que, si cambió id_novedad,
             * también se actualicen cod_liq y unidad.
             */
            $("#tb_funciones_adic").DataTable().ajax.reload(null, false);
        },

        error: function (xhr) {
            console.error("ERROR ACTUALIZANDO FUNCIÓN:", xhr.responseText);

            let mensaje = "No se pudo actualizar la función adicional.";

            if (xhr.responseJSON?.message) {
                mensaje = xhr.responseJSON.message;
            }

            Swal.fire({
                title: "Error",
                text: mensaje,
                icon: "error",
            });

            // Restauramos valores reales desde BD
            $("#tb_funciones_adic").DataTable().ajax.reload(null, false);
        },
    });
}

