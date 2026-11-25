let legajoActual = null; // { id_personal, legajo }
var tablaPrestamos = null;

/* ---------------------------------------------------------------------
   Utilidades
   --------------------------------------------------------------------- */

$(document).ready(function () {
    if ($("#contenedorRequerimientos").length) {
        cargarRequerimientosAlimentarios();
    }
});

function escaparHtml(texto) {
    return $("<div>")
        .text(texto ?? "")
        .html();
}

function formatoFechaLegajo(valor) {
    if (!valor) return "—";
    const [a, m, d] = String(valor).substring(0, 10).split("-");
    return `${d}/${m}/${a}`;
}

function aniosDesde(fecha, hasta) {
    if (!fecha) return "";
    const ini = new Date(String(fecha).substring(0, 10) + "T00:00:00");
    const fin = hasta
        ? new Date(String(hasta).substring(0, 10) + "T00:00:00")
        : new Date();
    let anios = fin.getFullYear() - ini.getFullYear();
    const m = fin.getMonth() - ini.getMonth();
    if (m < 0 || (m === 0 && fin.getDate() < ini.getDate())) anios--;
    return anios >= 0 ? anios + " años" : "";
}

// Deja el valor esperado en el select y lo aplica si la opción ya existe.
// Llamá a aplicarValorPendiente($select) al final de tus funciones de carga.
function setValorSelect($select, valor) {
    const v = valor === null || valor === undefined ? "" : String(valor);
    $select.attr("data-valor", v).val(v);
    if ($select.hasClass("select2-hidden-accessible"))
        $select.trigger("change.select2");
}

function aplicarValorPendiente($select) {
    const v = $select.attr("data-valor");
    if (v !== undefined) {
        $select.val(v);
        if ($select.hasClass("select2-hidden-accessible"))
            $select.trigger("change.select2");
    }
}

/* ---------------------------------------------------------------------
   Filas dinámicas: contactos de emergencia
   --------------------------------------------------------------------- */
function agregarFilaContactoLegajo($div, c = {}) {
    const html = `
        <div class="col-12 col-xl-6 fila-contacto-legajo">
            <div class="row align-items-end g-2">
                <input type="hidden" name="idContacto[]" value="${escaparHtml(c.id || "")}">
                <div class="col-12">
                    <label class="label-control text-muted">Nombre</label>
                    <input class="form-control" name="nombreEmergencia[]" type="text" value="${escaparHtml(c.nombre)}">
                </div>
                <div class="col-12 col-xl-6">
                    <label class="label-control text-muted">Teléfono</label>
                    <input class="form-control" name="telefonoEmergencia[]" type="text" value="${escaparHtml(c.telefono)}">
                </div>
                <div class="col-11 col-xl-5">
                    <label class="label-control text-muted">Parentesco</label>
                    <input class="form-control" name="parentescoEmergencia[]" type="text" value="${escaparHtml(c.parentesco)}">
                </div>
                <div class="col-1">
                    <button type="button" class="btn btn-outline-danger btn-quitar-fila-legajo" data-tipo="contacto">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </div>
            </div>
        </div>`;
    $div.append(html);
}

/* ---------------------------------------------------------------------
   Filas dinámicas: grupo familiar
   --------------------------------------------------------------------- */
function agregarFilaFamiliarLegajo($div, f = {}) {
    const nacimiento = f.fecha_nacimiento
        ? String(f.fecha_nacimiento).substring(0, 10)
        : "";
    const html = `
        <div class="col-12 col-xl-6 fila-familiar-legajo">
            <div class="row align-items-end g-2">
                <input type="hidden" name="idFamiliar[]" value="${escaparHtml(f.id || "")}">
                <div class="col-12 col-xl-5">
                    <label class="label-control text-muted">Nombre y apellido</label>
                    <input type="text" name="familiarNombrePersonal[]" class="form-control" value="${escaparHtml(f.nombre)}">
                </div>
                <div class="col-12 col-xl-4">
                    <label class="label-control text-muted">DNI</label>
                    <input type="text" name="familiarDniPersonal[]" class="form-control" inputmode="numeric" maxlength="8" value="${escaparHtml(f.dni)}">
                </div>
                <div class="col-12 col-xl-3">
                    <label class="label-control text-muted">Fecha nacimiento</label>
                    <input type="date" name="familiarNacimientoPersonal[]" class="form-control" value="${nacimiento}">
                </div>
                <div class="col-11 col-xl-5">
                    <label class="label-control text-muted">Parentesco</label>
                    <input type="text" name="familiarParentescoPersonal[]" class="form-control" value="${escaparHtml(f.parentesco)}">
                </div>
                <div class="col-1">
                    <button type="button" class="btn btn-outline-danger btn-quitar-fila-legajo" data-tipo="familiar">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </div>
            </div>
        </div>`;
    $div.append(html);
}

$(document).on("click", "#btnAgregarContactoLegajo", function () {
    agregarFilaContactoLegajo($("#divContactosLegajo"));
});

$(document).on("click", "#btnAgregarFamiliarLegajo", function () {
    agregarFilaFamiliarLegajo($("#divFamiliaresLegajo"));
});

// al quitar una fila que ya existía en la base, se guarda su id para borrarla al guardar
$(document).on("click", ".btn-quitar-fila-legajo", function () {
    const $fila = $(this).closest(
        ".fila-contacto-legajo, .fila-familiar-legajo",
    );
    const tipo = $(this).data("tipo");
    const id = $fila
        .find(
            tipo === "contacto"
                ? 'input[name="idContacto[]"]'
                : 'input[name="idFamiliar[]"]',
        )
        .val();
    if (id) {
        $("#divEliminadosLegajo").append(
            `<input type="hidden" name="${tipo === "contacto" ? "contactoEliminado[]" : "familiarEliminado[]"}" value="${escaparHtml(id)}">`,
        );
    }
    $fila.remove();
});

/* ---------------------------------------------------------------------
   CUIL (clases propias para no tocar el modal de alta)
   --------------------------------------------------------------------- */
$(document).on("input", ".inputDniLegajo", function () {
    const dni = $(this).val().replace(/\D/g, "");
    $(this).val(dni);
    $(".midCuilLegajo").val(dni ? dni.padStart(8, "0") : "");
});

function armarCuilLegajo() {
    $(".inputCuilLegajo").val(
        $(".preCuilLegajo").val() +
            $(".midCuilLegajo").val() +
            $(".posCuilLegajo").val(),
    );
}

function cargarCuilLegajo(cuil) {
    const c = String(cuil || "").replace(/\D/g, "");
    $(".preCuilLegajo").val(c.slice(0, 2));
    $(".midCuilLegajo").val(c.slice(2, 10));
    $(".posCuilLegajo").val(c.slice(10, 11));
    $(".inputCuilLegajo").val(c);
}

$(document).on("input", ".preCuilLegajo, .posCuilLegajo", armarCuilLegajo);

// edad y antigüedad se recalculan si cambian las fechas
$(document).on("change", '#formLegajo [name="fechaNacimiento"]', function () {
    $("#inputEdadLegajo").val(aniosDesde($(this).val()));
});
$(document).on("change", '#formLegajo [name="fechaIngreso"]', function () {
    $("#inputAntiguedadLegajo").val(
        aniosDesde($(this).val(), $('#formLegajo [name="fechaEgreso"]').val()),
    );
});

/* ---------------------------------------------------------------------
   Llenado del modal
   --------------------------------------------------------------------- */
function llenarModalLegajo(res) {
    const d = res.data;
    const $form = $("#formLegajo");

    $form[0].reset();
    $(
        "#divEliminadosLegajo, #divContactosLegajo, #divFamiliaresLegajo, #legajoTbNovedades",
    ).empty();

    legajoActual = { id_personal: d.id_personal, legajo: d.legajo };

    // ocultos
    $("#hiddenIdPersonalLegajo").val(d.id_personal);
    $("#hiddenIdVinculoLegajo").val(d.id_vinculo || "");
    $("#hiddenIdTramoLegajo").val(d.id_tramo || "");
    $("#hiddenTipoVinculoLegajo").val(d.tipo_vinculo || "");

    // campos con data-campo
    $form.find("[data-campo]").each(function () {
        const $el = $(this);
        let valor = d[$el.data("campo")];
        if (valor === null || valor === undefined) valor = "";

        if ($el.data("formato") === "sino" && valor !== "")
            valor = Number(valor) === 1 ? "SI" : "NO";

        if ($el.is("select")) {
            setValorSelect($el, valor);
        } else if ($el.attr("type") === "date") {
            $el.val(valor ? String(valor).substring(0, 10) : "");
        } else {
            $el.val(valor);
        }
    });

    cargarCuilLegajo(d.cuil);
    $("#inputEdadLegajo").val(aniosDesde(d.fecha_nacimiento));
    $("#inputAntiguedadLegajo").val(
        aniosDesde(d.fecha_ingreso, d.fecha_egreso),
    );

    // cabecera
    $("#legajoTitulo").text(d.colaborador || "");
    $("#legajoNumero").text(
        d.legajo
            ? "Legajo " + String(d.legajo).padStart(5, "0")
            : d.tipo_vinculo || "Sin vínculo",
    );
    const colores = {
        ACTIVO: "bg-success",
        "PERIODO DE PRUEBA": "bg-warning text-dark",
        BAJA: "bg-danger",
    };
    $("#legajoEstado")
        .attr("class", "badge " + (colores[d.estado] || "bg-secondary"))
        .text(d.estado || "");
    $("#legajoResumen").text(
        [d.puesto, d.area, d.servicio].filter(Boolean).join(" · "),
    );

    const $foto = $("#legajoFoto");
    if (d.url_foto_legajo) {
        $foto
            .css("background-image", `url('/storage/${d.url_foto_legajo}')`)
            .text("");
    } else {
        $foto
            .css("background-image", "none")
            .text((d.apellido || "?").charAt(0) + (d.nombre || "").charAt(0));
    }

    // bloques según tipo de vínculo / estado
    const esDep = d.tipo_vinculo === "DEPENDENCIA";
    $form.find(".solo-dependencia").toggleClass("d-none", !esDep);
    $form.find(".solo-externo").toggleClass("d-none", esDep || !d.tipo_vinculo);
    $form.find(".solo-con-legajo").toggleClass("d-none", !d.legajo);
    $("#legajoBloqueBaja").toggleClass("d-none", d.estado !== "BAJA");

    // contactos y familiares (editables)
    (res.contactos || []).forEach((c) =>
        agregarFilaContactoLegajo($("#divContactosLegajo"), c),
    );
    (res.familiares || []).forEach((f) =>
        agregarFilaFamiliarLegajo($("#divFamiliaresLegajo"), f),
    );

    // historial (solo lectura)
    const $his = $("#legajoTbHistorial").empty();
    (res.tramos || []).forEach((t) => {
        $his.append(
            $("<tr>").append(
                [
                    formatoFechaLegajo(t.desde),
                    t.hasta ? formatoFechaLegajo(t.hasta) : "Vigente",
                    t.tipo_vinculo + (t.legajo ? " #" + t.legajo : ""),
                    t.puesto,
                    t.area,
                    t.servicio,
                    t.categoria,
                    t.regimen ? t.regimen + " hs" : null,
                ].map((v) => $("<td>").text(v ?? "—")),
            ),
        );
    });
    if (!(res.tramos || []).length) {
        $his.append(
            '<tr><td colspan="8" class="text-muted fst-italic">Sin historial</td></tr>',
        );
    }

    // cuentas bancarias (dentro de Datos personales)
    $("#inputLegajo").val(d.legajo || "");
    $("#contenedorCuentasBancarias").empty();
    $("#divCrearCuentaBancariaColab")
        .addClass("d-none")
        .find("input, select")
        .val("");
    if (d.legajo) {
        listarCuentasBancarias(d.legajo);
    }

    // requerimientos alimentarios (dentro de Datos personales)
    $("input[name='req_alimenticios[]']").prop("checked", false);
    $("#rowObservacionReq").hide();
    $("#inputReqOtro").val("");
    if (d.legajo) {
        cargarReqSeleccionados(d.legajo);
    }

    // filtros de novedades en limpio; se cargan al abrir la pestaña
    $(".filtro-novedades-legajo").val("");
    $("#legajoTbNovedades").data("cargado", false);

    // préstamos en limpio; se cargan al abrir la pestaña
    $("#filtroEstadoPrestamo").val("1");
    $("#saldoTotalPrestamos").text(fmtMoneda(0));
    if (tablaPrestamos) tablaPrestamos.clear().draw();

    bootstrap.Tab.getOrCreateInstance(
        $('[data-bs-target="#tabLegajoPersonales"]')[0],
    ).show();
    $("#modalVerLegajo").modal("show");
}

/* ---------------------------------------------------------------------
   Apertura
   --------------------------------------------------------------------- */

function cargarSelectoresLegajo(d) {
    const $modal = $("#modalVerLegajo");

    // localidad (Select2 con georef): se inicializa una sola vez
    const $loc = $("#selectLocalidadLegajo");
    if (!$loc.hasClass("select2-hidden-accessible")) {
        obtenerLocalidadesGeoRest($loc, $modal);
    }
    setLocalidadLegajo(d.localidad);

    // cuando terminen TODAS las cargas, se aplican los valores del legajo
    $(document).one("ajaxStop", function () {
        $("#formLegajo select[data-campo]").each(function () {
            if (this.id !== "selectLocalidadLegajo")
                aplicarValorPendiente($(this));
        });

        // régimen: el select usa el id como value, pero lo guardado son las horas;
        // se elige la opción cuyo texto coincide con las horas guardadas
        if (d.regimen) {
            const $reg = $("#selectorRegimenLegajo");
            const $opt = $reg.find("option").filter(function () {
                return $(this).text().trim() === String(d.regimen);
            });
            if ($opt.length) $reg.val($opt.val());
        }
    });

    // dependencia
    obtenerPuestosTrabajoActivos($("#selectorPuestoTrabajoLegajo"));
    obtenerAreasSelector($("#selectorAreaLegajo"));
    obtenerServiciosSelector($("#selectorServicioLegajo"));
    obtenerCategoriasActivas($("#selectorCategoriaLegajo"));
    obtenerRegimenesActivos(
        $("#selectorRegimenLegajo"),
        $("#inputHorasDiariasLegajo"),
    );
    obtenerTiposContratosSelector($("#selectorTipoContratoDepLegajo"));
    obtenerObrasSociales(
        $("#selectorObrasSocialesLegajo"),
        $modal,
        $("#inputCodigoObraSocialLegajo"),
    );

    // externos / monotributistas
    obtenerTiposContratosSelector($("#selectorTipoContratoExtLegajo"));
    obtenerServiciosSelector($("#selectorServicioExternoLegajo"));
}

function setLocalidadLegajo(localidad) {
    const $sel = $("#selectLocalidadLegajo");
    $sel.find('option[value!=""]').remove();
    if (localidad) {
        $sel.append(new Option(localidad, localidad, true, true));
    }
    $sel.trigger("change");
}

function verLegajo(idPersonal) {
    $.ajax({
        url: "/rrhh/personal/verLegajo",
        type: "GET",
        dataType: "json",
        data: { p_id_personal: idPersonal },
        success: function (res) {
            llenarModalLegajo(res);
            cargarSelectoresLegajo(res.data);
        },
        error: function (xhr) {
            const r = xhr.responseJSON || {};
            Swal.fire(
                "Error",
                r.message || r.mensaje || "No se pudo cargar el legajo.",
                "error",
            );
        },
    });
}

/* ---------------------------------------------------------------------
   Novedades
   --------------------------------------------------------------------- */
function cargarNovedadesLegajo() {
    if (!legajoActual || !legajoActual.legajo) return;

    const $tb = $("#legajoTbNovedades");
    $tb.html('<tr><td colspan="10" class="text-muted">Cargando...</td></tr>');

    $.ajax({
        url: "/rrhh/personal/verNovedadesLegajo",
        type: "GET",
        dataType: "json",
        data: {
            p_legajo: legajoActual.legajo,
            p_fecha_desde: $("#filtroNovDesdeLegajo").val() || null,
            p_fecha_hasta: $("#filtroNovHastaLegajo").val() || null,
            p_liquidada: $("#filtroNovLiquidadaLegajo").val(),
        },
        success: function (res) {
            $tb.empty().data("cargado", true);
            const filas = res.data || [];
            if (!filas.length) {
                $tb.append(
                    '<tr><td colspan="10" class="text-muted fst-italic">Sin novedades</td></tr>',
                );
                return;
            }
            filas.forEach((n) => {
                const liquidada =
                    Number(n.liquidada) === 1
                        ? '<span class="badge bg-success">SI</span>'
                        : '<span class="badge bg-secondary">NO</span>';
                const $tr = $("<tr>").append(
                    [
                        formatoFechaLegajo(n.fecha_registro),
                        (n.codigo ? n.codigo + " - " : "") + (n.novedad || ""),
                        formatoFechaLegajo(n.fecha_desde),
                        formatoFechaLegajo(n.fecha_hasta),
                        formatoFechaLegajo(n.fecha_aplicacion),
                        n.duracion,
                        n.descripcion,
                        n.registrante,
                    ].map((v) => $("<td>").text(v ?? "—")),
                );
                $tr.append($('<td class="text-center">').html(liquidada));
                $tr.append(
                    $('<td class="text-center text-nowrap">').html(
                        botonesComprobantes(n.registro),
                    ),
                );
                $tb.append($tr);
            });
        },
        error: function () {
            $tb.html(
                '<tr><td colspan="9" class="text-danger">No se pudieron cargar las novedades.</td></tr>',
            );
        },
    });
}

// primera vez que se abre la pestaña
$(document).on("shown.bs.tab", "#tabBtnNovedadesLegajo", function () {
    if (!$("#legajoTbNovedades").data("cargado")) cargarNovedadesLegajo();
});

$(document).on("change", ".filtro-novedades-legajo", cargarNovedadesLegajo);

/* ---------------------------------------------------------------------
   Guardado: se implementa con la edición
   --------------------------------------------------------------------- */

$(document).on("submit", "#formLegajo", function (e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $("#btnGuardarLegajo");

    armarCuilLegajo();

    // régimen: se guardan las horas (texto de la opción), igual que los datos migrados
    const datos = $form.serializeArray().map(function (campo) {
        if (campo.name === "regimen") {
            const texto = $("#selectorRegimenLegajo option:selected")
                .text()
                .trim();
            return { name: "regimen", value: /^\d+$/.test(texto) ? texto : "" };
        }
        return campo;
    });

    $btn.prop("disabled", true);

    $.ajax({
        url: "/rrhh/personal/guardarEdicionLegajo",
        type: "POST",
        dataType: "json",
        data: $.param(datos),
        success: function (res) {
            Swal.fire({
                icon: "success",
                title: "Legajo actualizado",
                timer: 1400,
                showConfirmButton: false,
            });
            verLegajo(legajoActual.id_personal); // recarga con los ids nuevos de contactos/familiares
            if (typeof tablaPersonal !== "undefined")
                tablaPersonal.ajax.reload(null, false);
        },
        error: function (xhr) {
            const r = xhr.responseJSON || {};
            let msg = "No se pudo guardar el legajo.";
            if (r.errors) msg = Object.values(r.errors)[0][0];
            else if (r.message) msg = r.message;
            Swal.fire("Error", msg, "error");
        },
        complete: function () {
            $btn.prop("disabled", false);
        },
    });
});

// asignar puesto: se guarda apenas se elige
$(document).on("change", "#selectorPuestoTrabajoLegajo", function () {
    const $sel = $(this);
    const anterior = $sel.attr("data-valor") || "";
    const nuevo = $sel.val() || "";
    const idTramo = $("#hiddenIdTramoLegajo").val();

    if (!idTramo || nuevo === anterior) return;

    $sel.prop("disabled", true);

    $.ajax({
        url: "/rrhh/personal/asignarPuestoTramo",
        type: "POST",
        dataType: "json",
        data: {
            idTramo: idTramo,
            idPuesto: nuevo || null,
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
        success: function () {
            $sel.attr("data-valor", nuevo);
            const texto = nuevo
                ? $sel.find("option:selected").text().trim()
                : "";
            $("#legajoResumen").text(
                [
                    texto,
                    $("#selectorAreaLegajo option:selected").text().trim(),
                    $("#selectorServicioLegajo option:selected").text().trim(),
                ]
                    .filter((t) => t && !t.startsWith("Seleccione"))
                    .join(" · "),
            );
            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title: "Puesto asignado",
                timer: 1500,
                showConfirmButton: false,
            });
            if (typeof tablaPersonal !== "undefined")
                tablaPersonal.ajax.reload(null, false);
        },
        error: function (xhr) {
            $sel.val(anterior); // vuelve al puesto que tenía
            const r = xhr.responseJSON || {};
            Swal.fire(
                "Error",
                r.message || "No se pudo asignar el puesto.",
                "error",
            );
        },
        complete: function () {
            $sel.prop("disabled", false);
        },
    });
});

function fmtMoneda(v) {
    return Number(v || 0).toLocaleString("es-AR", {
        style: "currency",
        currency: "ARS",
    });
}
function fmtFecha(f) {
    // 'YYYY-MM-DD' -> 'DD/MM/YYYY'
    if (!f) return "-";
    var p = f.substring(0, 10).split("-");
    return p[2] + "/" + p[1] + "/" + p[0];
}
function fmtPeriodo(f) {
    // 'YYYY-MM-DD' -> 'MM/YYYY'
    if (!f) return "-";
    var p = f.substring(0, 10).split("-");
    return p[1] + "/" + p[0];
}
function badgeEstadoPrestamo(txt) {
    var clase =
        {
            VIGENTE: "bg-primary",
            FINALIZADO: "bg-success",
            ANULADO: "bg-secondary",
            PENDIENTE: "bg-warning text-dark",
            DESCONTADA: "bg-success",
            ANULADA: "bg-secondary",
        }[txt] || "bg-light text-dark";
    return '<span class="badge ' + clase + '">' + txt + "</span>";
}

function initTablaPrestamos() {
    tablaPrestamos = $("#tablaLegajoPrestamos").DataTable({
        ajax: {
            url: "/rrhh/legajo/prestamos",
            data: function (d) {
                d.legajo = legajoActual ? legajoActual.legajo : null;
                d.estado = $("#filtroEstadoPrestamo").val();
            },
            dataSrc: "data",
        },
        columns: [
            {
                data: null,
                className: "dt-control",
                orderable: false,
                defaultContent: "",
            },
            { data: "id" },
            {
                data: "fecha_inicio",
                render: function (d, t) {
                    return t === "display" ? fmtFecha(d) : d;
                },
            },
            {
                data: "monto_total",
                className: "text-end",
                render: function (d, t) {
                    return t === "display" ? fmtMoneda(d) : d;
                },
            },
            {
                data: null,
                orderable: false,
                render: function (r) {
                    return r.cuotas_descontadas + " / " + r.cantidad_cuotas;
                },
            },
            {
                data: "saldo",
                className: "text-end",
                render: function (d, t) {
                    return t === "display" ? fmtMoneda(d) : d;
                },
            },
            {
                data: "proximo_periodo",
                render: function (d, t) {
                    return t === "display" ? fmtPeriodo(d) : d;
                },
            },
            {
                data: "estado_texto",
                render: function (d) {
                    return badgeEstadoPrestamo(d);
                },
            },
        ],
        order: [[2, "desc"]],
        paging: false,
        info: false,
        searching: false,
        language: { emptyTable: "Sin préstamos registrados" },
    });

    tablaPrestamos.on("xhr.dt", function (e, settings, json) {
        var total = 0;
        $.each((json && json.data) || [], function (i, r) {
            if (r.estado_texto === "VIGENTE") total += Number(r.saldo);
        });
        $("#saldoTotalPrestamos").text(fmtMoneda(total));
    });
}

function htmlCuotasPrestamo(prestamo, cuotas) {
    var filas = "";
    $.each(cuotas, function (i, c) {
        filas +=
            "<tr><td>" +
            c.numero_cuota +
            "</td><td>" +
            fmtPeriodo(c.periodo) +
            "</td>" +
            '<td class="text-end">' +
            fmtMoneda(c.importe) +
            "</td><td>" +
            badgeEstadoPrestamo(c.estado_texto) +
            "</td></tr>";
    });
    if (!filas)
        filas =
            '<tr><td colspan="4" class="text-center text-muted">Sin cuotas generadas</td></tr>';

    return (
        '<div class="p-2">' +
        '<div class="small mb-2"><strong>Registró:</strong> ' +
        (prestamo.auditor || "-") +
        " &nbsp;·&nbsp; <strong>Observaciones:</strong> " +
        $("<div>")
            .text(prestamo.observaciones || "-")
            .html() +
        "</div>" +
        '<table class="table table-sm table-bordered mb-0 w-auto">' +
        '<thead><tr><th>Cuota</th><th>Período</th><th class="text-end">Importe</th><th>Estado</th></tr></thead>' +
        "<tbody>" +
        filas +
        "</tbody></table></div>"
    );
}

// Fila hija con las cuotas
$(document).on("click", "#tablaLegajoPrestamos td.dt-control", function () {
    var tr = $(this).closest("tr");
    var row = tablaPrestamos.row(tr);

    if (row.child.isShown()) {
        row.child.hide();
        return;
    }

    var prestamo = row.data();
    $.get("/rrhh/legajo/prestamos/cuotas", {
        legajo: legajoActual.legajo,
        id_prestamo: prestamo.id,
    })
        .done(function (resp) {
            row.child(htmlCuotasPrestamo(prestamo, resp.data)).show();
        })
        .fail(function () {
            Swal.fire("Error", "No se pudieron cargar las cuotas.", "error");
        });
});

// Carga recién cuando se abre la pestaña (columns.adjust por estar oculta)
$(document).on("shown.bs.tab", "#tab-prestamos-btn", function () {
    if (!tablaPrestamos) initTablaPrestamos();
    else tablaPrestamos.ajax.reload();
    tablaPrestamos.columns.adjust();
});

$(document).on("change", "#filtroEstadoPrestamo", function () {
    if (tablaPrestamos) tablaPrestamos.ajax.reload();
});

// UTI / NOCHE: se guardan apenas se cambian
$(document).on(
    "change",
    '#formLegajo [name="selector-uti"], #formLegajo [name="selector-noche"]',
    function () {
        const $sel = $(this);
        const condicion =
            $sel.attr("name") === "selector-uti" ? "UTI" : "NOCHE";
        const anterior = $sel.attr("data-valor") ?? "";
        const nuevo = $sel.val();
        const idVinculo = $("#hiddenIdVinculoLegajo").val();

        if (!idVinculo || nuevo === anterior) return;

        $sel.prop("disabled", true);

        $.ajax({
            url: "/rrhh/cambiarCondicionVinculo",
            type: "POST",
            dataType: "json",
            data: {
                idVinculo: idVinculo,
                condicion: condicion,
                activo: nuevo,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function () {
                $sel.attr("data-valor", nuevo);
                Swal.fire({
                    toast: true,
                    position: "top-end",
                    icon: "success",
                    title:
                        condicion + (nuevo === "1" ? " activada" : " quitada"),
                    timer: 1500,
                    showConfirmButton: false,
                });
            },
            error: function (xhr) {
                $sel.val(anterior); // vuelve al valor que tenía
                Swal.fire(
                    "Error",
                    (xhr.responseJSON || {}).message ||
                        "No se pudo guardar la condición.",
                    "error",
                );
            },
            complete: function () {
                $sel.prop("disabled", false);
            },
        });
    },
);
