let selector = null;

$(document).on("change", "#selectorTipoVinculo", function () {
    const tipo = $(this).val();
    const esDep = tipo === "DEPENDENCIA";
    const esExt = tipo !== "" && !esDep;

    $("#divDependencia")
        .toggleClass("d-none", !esDep)
        .find("input, select")
        .prop("disabled", !esDep);
    $("#divExternos")
        .toggleClass("d-none", !esExt)
        .find("input, select")
        .prop("disabled", !esExt);
});

//API GEOREF
function obtenerLocalidadesGeoRest(idSelector, modal) {
    idSelector.select2({
        language: {
            inputTooShort: function () {
                return "Por favor, ingresá 3 o más caracteres";
            },
            searching: function () {
                return "Consultado API GeoRef...";
            },
            noResults: function () {
                return "No se encontraron localidades";
            },
            errorLoading: function () {
                return "La carga falló. Verificá tu conexión.";
            },
        },
        placeholder: "Buscar localidad...",
        minimumInputLength: 3,
        width: "100%",
        dropdownParent: modal,
        ajax: {
            url: "/rrhh/geo/localidades",
            dataType: "json",
            delay: 400, // Un poco más de delay ayuda a no disparar tantas peticiones
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return {
                    results: (data.localidades || []).map(function (l) {
                        let nombreCompleto =
                            l.nombre + " - " + l.provincia.nombre;
                        return {
                            id: nombreCompleto,
                            text: nombreCompleto,
                        };
                    }),
                };
            },
            cache: true,
        },
    });
}

function obtenerAreasSelector(idSelector) {
    $.ajax({
        url: "/rrhh/obtenerAreasSelector",
        type: "GET",
        dateType: "json",

        success: function (res) {
            selector = idSelector;
            selector.empty();
            selector.append('<option value="">Seleccione área</option>');
            $.each(res.data, function (index, item) {
                selector.append(
                    `
                        <option value="${item.id_area}">${item.nombre}</option>
                    `,
                );
            });
        },
    });
}

function obtenerTiposContratosSelector(idSelector) {
    $.ajax({
        url: "/rrhh/obtenerTiposContrato",
        type: "GET",
        dateType: "json",

        success: function (res) {
            selector = idSelector;
            selector.empty();
            selector.append(
                '<option value="">Seleccione tipo de contrato</option>',
            );
            $.each(res.data, function (index, item) {
                selector.append(
                    `
                        <option value="${item.nombre}">${item.nombre}</option>
                    `,
                );
            });
        },
    });
}

function obtenerServiciosSelector(idSelector) {
    $.ajax({
        url: "/rrhh/obtenerServiciosPersonal",
        type: "GET",
        dateType: "json",

        success: function (res) {
            selector = idSelector;
            selector.empty();
            selector.append('<option value="">Seleccione servicio</option>');
            $.each(res.data, function (index, item) {
                selector.append(
                    `
                        <option value="${item.id_servicios}">${item.nombre}</option>
                    `,
                );
            });
        },

        error: function (xhr, status, error) {
            console.error("Error al obtener servicios:", error);
            console.error("Respuesta:", xhr.responseText);
        },
    });
}

function obtenerObrasSociales(idSelector, modal, inputCodigo) {
    const $select = idSelector;

    $.ajax({
        url: "/rrhh/obra-social/lista",
        type: "GET",
        dataType: "json",

        success: function (data) {
            if ($select.hasClass("select2-hidden-accessible")) {
                $select.select2("destroy");
            }

            $select.empty().append('<option value=""></option>');

            $select.select2({
                data: data,
                placeholder: "Buscar obra social...",
                allowClear: true,
                width: "100%",
                dropdownParent: modal.find(".modal-content"),
            });
        },

        error: function (xhr, status, error) {
            console.error("Error cargando obras sociales:", error);
        },
    });

    $select.off("select2:select").on("select2:select", function (e) {
        const data = e.params.data;
        inputCodigo.val(data.codigo ?? "");
    });

    $select.off("select2:clear").on("select2:clear", function () {
        inputCodigo.val("");
    });
}

function obtenerPuestosTrabajoActivos(idSelector) {
    $.ajax({
        url: "/rrhh/personal/obtenerPuestoTrabajoActivos",
        type: "GET",
        dateType: "json",

        success: function (res) {
            selector = idSelector;
            selector.empty();
            selector.append(
                '<option value="">Seleccione puesto de trabajo</option>',
            );
            $.each(res.data, function (index, item) {
                selector.append(
                    `
                        <option value="${item.id}">${item.nombre}</option>
                    `,
                );
            });
        },

        error: function (xhr, status, error) {
            console.error("Error al obtener puestos de trabajo:", error);
            console.error("Respuesta:", xhr.responseText);
        },
    });
}

function obtenerCategoriasActivas(idSelector) {
    $.ajax({
        url: "/rrhh/personal/obtenerCategoriasActivas",
        type: "GET",
        dateType: "json",

        success: function (res) {
            selector = idSelector;
            selector.empty();
            selector.append('<option value="">Seleccione categoría</option>');
            $.each(res.data, function (index, item) {
                selector.append(
                    `
                        <option value="${item.id_categ}">${item.nombre}</option>
                    `,
                );
            });
        },

        error: function (xhr, status, error) {
            console.error("Error al obtener categorias:", error);
            console.error("Respuesta:", xhr.responseText);
        },
    });
}

function obtenerRegimenesActivos(idSelector, inputHoras) {
    $.ajax({
        url: "/rrhh/personal/obtenerRegimenesActivos",
        type: "GET",
        dataType: "json",

        success: function (res) {
            const selector = idSelector;

            selector.empty();
            selector.append('<option value="">Seleccione régimen</option>');

            $.each(res.data, function (index, item) {
                selector.append(
                    $("<option>", {
                        value: item.id,
                        text: item.regimen,
                    }).attr("data-horas", item.horasDiarias),
                );
            });
        },

        error: function (xhr, status, error) {
            console.error("Error al obtener regímenes:", error);
            console.error("Respuesta:", xhr.responseText);
        },
    });

    idSelector.off("change.regimen").on("change.regimen", function () {
        const horasDiarias = $(this).find("option:selected").attr("data-horas");

        inputHoras.val(horasDiarias ?? "");
    });
}

function obtenerPuestosTrabajoSelector($selector) {
    $.ajax({
        url: "/rrhh/personal/obtenerPuestoTrabajoActivos",
        type: "GET",
        dataType: "json",
        success: function (res) {
            $selector
                .empty()
                .append('<option value="">Seleccione puesto</option>');
            $.each(res.data, function (index, item) {
                $selector.append(
                    $("<option>", {
                        value: item.codigo_slug,
                        text: item.nombre,
                    }),
                );
            });
        },
        error: function (xhr, status, error) {
            console.error("Error al obtener puestos:", error);
            console.error("Respuesta:", xhr.responseText);
        },
    });
}
