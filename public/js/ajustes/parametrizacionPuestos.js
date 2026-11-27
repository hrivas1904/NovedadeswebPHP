let tablaPuestos = null;

function cargarTablaPuestos() {
    if ($.fn.DataTable.isDataTable("#tbPuestos")) {
        tablaPuestos.ajax.reload(null, false);
        return;
    }

    tablaPuestos = $("#tbPuestos").DataTable({
        ajax: {
            url: "/rrhh/puestos/obtenerTodosPuestos",
            type: "GET",
            dataType: "json",
        },

        autoWidth: false,
        paging: false,
        scrollX: false,
        scrollY: "55vh",
        scrollCollapse: true,
        dom: "tir",
        language: {
            url: "/js/es-ES.json",
        },
        columns: [
            {
                data: "nombre",
                width: "25%",
                render: function (data, type, row) {
                    if (type !== "display") return data ?? "";

                    return `
                        <input type="text"
                            class="form-control"
                            data-campo="nombre"
                            data-id="${row.id}"
                            value="${escaparHtml(data)}">
                    `;
                },
            },
            {
                data: "descripcion",
                width: "10%",
                render: function (data, type, row) {
                    if (type !== "display") return data ?? "";

                    return `
                        <input type="text"
                            class="form-control"
                            data-campo="descripcion"
                            data-id="${row.id}"
                            value="${escaparHtml(data)}">
                    `;
                },
            },
            {
                data: "codigo_slug",
                width: "10%",
                render: function (data, type, row) {
                    if (type !== "display") return data ?? "";

                    return `
                        <input type="text"
                            class="form-control"
                            data-campo="codigo"
                            data-id="${row.id}"
                            value="${escaparHtml(data)}">
                    `;
                },
            },
            {
                data: "id_area",
                width: "15%",
                render: function (data, type, row) {
                    if (type !== "display") return data;

                    return `
                        <select
                            class="form-select selector-area"
                            data-campo="area"
                            data-id="${row.id}"
                            data-valor="${escaparHtml(data)}">
                        </select>
                    `;
                },
            },
            {
                data: "id_servicio",
                width: "15%",
                render: function (data, type, row) {
                    if (type !== "display") return data;

                    return `
                        <select
                            class="form-select selector-servicio"
                            data-campo="servicio"
                            data-id="${row.id}"
                            data-valor="${escaparHtml(data)}">
                        </select>
                    `;
                },
            },
            {
                data: "es_coordinador",
                width: "10%",
                render: function (data, type, row) {
                    if (type !== "display") return data;

                    return `
                        <select
                            class="form-select selector-coordinador"
                            data-campo="coordinador"
                            data-id="${row.id}">

                            <option value=""
                                ${data == null ? "selected" : ""}>
                                Seleccione opción
                            </option>

                            <option value="1"
                                ${String(data) === "1" ? "selected" : ""}>
                                SÍ
                            </option>

                            <option value="0"
                                ${String(data) === "0" ? "selected" : ""}>
                                NO
                            </option>

                        </select>
                    `;
                },
            },
            {
                data: "parent_id",
                width: "10%",
                render: function (data, type, row) {
                    if (type !== "display") return data;

                    return `
                        <select
                            class="form-select selector-superior"
                            data-campo="superior"
                            data-id="${row.id}"
                            data-valor="${escaparHtml(data)}">
                        </select>
                    `;
                },
            },
            {
                data: "activo",
                width: "5%",
                className: "text-center align-middle",
                render: function (data, type, row) {
                    if (type !== "display") return data;

                    return `
                        <div class="form-check form-switch d-flex justify-content-center">
                            <input
                                type="checkbox"
                                class="form-check-input switch-activo"
                                role="switch"
                                data-campo="activo"
                                data-id="${row.id}"
                                aria-label="Estado del puesto"
                                ${Number(data) === 1 ? "checked" : ""}>
                        </div>
                    `;
                },
            },
        ],

        drawCallback: function () {
            obtenerAreasSelector($("#tbPuestos .selector-area"));
            obtenerServiciosSelector($("#tbPuestos .selector-servicio"));
            obtenerPuestosTrabajoSelector($("#tbPuestos .selector-superior"));
        },
    });
}

function escaparHtml(valor) {
    return String(valor ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#39;");
}

function generarSlug(texto) {
    return String(texto ?? "")
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "") // saca tildes
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/^-+|-+$/g, "");
}

$(document).on("input", "#formNuevoPuesto #nombre", function () {
    const $codigo = $("#formNuevoPuesto #codigo");
    if (!$codigo.data("editado")) {
        $codigo.val(generarSlug($(this).val()));
    }
});

$(document).on("input", "#formNuevoPuesto #codigo", function () {
    $(this).data("editado", $(this).val() !== "");
});

$(document).on("submit", "#formNuevoPuesto", function (e) {
    e.preventDefault();

    const $form = $(this);
    const $btn = $form.find("button[type=submit]").prop("disabled", true);

    $.ajax({
        url: "/rrhh/puestos/guardarPuesto",
        type: "POST",
        dataType: "json",
        data: $form.serialize(),
    })
        .done(function (resp) {
            Swal.fire({
                icon: "success",
                title: resp.mensaje,
                timer: 1500,
                showConfirmButton: false,
            });

            $form[0].reset();
            $form.find("#codigo").data("editado", false);
            $form.find("select").val("").trigger("change");

            // el nuevo puesto tiene que aparecer como opción de superior
            obtenerPuestosTrabajoSelector($("#selectorSuperior"));
            cargarTablaPuestos(); // como ya existe, hace ajax.reload
        })
        .fail(function (xhr) {
            Swal.fire(
                "No se creó el puesto",
                xhr.responseJSON?.mensaje ?? "Error al guardar.",
                "error",
            );
        })
        .always(function () {
            $btn.prop("disabled", false);
        });
});

$(document).on("change", "#tbPuestos [data-campo]", function () {
    const $el = $(this);
    const campo = $el.data("campo");
    const valor = $el.is(":checkbox")
        ? $el.prop("checked")
            ? 1
            : 0
        : $el.val();

    $.ajax({
        url: "/rrhh/puestos/actualizarCampoPuesto",
        type: "POST",
        dataType: "json",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        data: { id: $el.data("id"), campo: campo, valor: valor },
    })
        .done(function (resp) {
            if (resp.servicio_limpiado) {
                // change.select2 refresca el select sin volver a disparar este handler
                $el.closest("tr")
                    .find(".selector-servicio")
                    .val("")
                    .trigger("change.select2");
            }

            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title: resp.servicio_limpiado
                    ? "Guardado. Se quitó el servicio por no pertenecer al área."
                    : "Cambio guardado",
                showConfirmButton: false,
                timer: 1800,
            });

            // nombre/código/estado cambian las opciones del selector de superior
            if (["nombre", "codigo", "activo"].includes(campo)) {
                tablaPuestos.ajax.reload(null, false);
            }
        })
        .fail(function (xhr) {
            Swal.fire(
                "No se guardó",
                xhr.responseJSON?.mensaje ?? "Error al guardar el cambio.",
                "error",
            );
            tablaPuestos.ajax.reload(null, false);
        });
});
