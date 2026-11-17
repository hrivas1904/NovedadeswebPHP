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

function obtenerObrasSociales(idSelector) {
    const $select = idSelector;

    $.ajax({
        url: "/rrhh/obra-social/lista",
        type: "GET",
        dataType: "json",
        success: function (data) {
            $select.select2({
                data: data,
                placeholder: "Buscar obra social...",
                allowClear: true,
                width: "100%",
                dropdownParent: $("#modalLegajoColaborador"),
            });
        },
        error: function (err) {
            console.error("Error cargando obras sociales:", err);
        },
    });

    idSelector.on("select2:select", function (e) {
        const data = e.params.data;
        $("#inputCodigoOS").val(data.codigo ?? "");
    });

    idSelector.on("select2:clear", function () {
        $("#inputCodigoOS").val(data.codigo ?? "");
    });
}
