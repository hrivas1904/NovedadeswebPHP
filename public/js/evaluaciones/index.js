let tablaEvaluaciones = null;
let eddActual = null;

const ANIO_ACTUAL = new Date().getFullYear();
const ESTADOS_EDITA_EVALUADOR = [
    "PENDIENTE",
    "AUTO_EN_CURSO",
    "AUTO_ENVIADA",
    "SUPERIOR_EN_CURSO",
];

$(document).ready(function () {
    cargarFiltros();

    tablaEvaluaciones = $("#tbEvaluaciones").DataTable({
        ajax: {
            url: EDD_PANEL.routes.listar,
            type: "GET",
            dataType: "json",
            data: function (d) {
                d.periodo = $("#selectorFiltroPeriodoEdd").val();
            },
            dataSrc: "data",
            error: mostrarError,
        },
        columns: [
            { data: "id" },
            { data: "evaluado", render: renderColaborador },
            { data: "area" },
            { data: "servicio", defaultContent: "-" },
            { data: "evaluador", render: renderEvaluador },
            { data: "auto_enviada_at", render: renderFecha },
            {
                data: "prom_auto_general",
                render: renderPromedio,
                className: "text-center",
            },
            { data: "superior_enviada_at", render: renderFecha },
            {
                data: "evaluacion_final",
                render: renderPromedio,
                className: "text-center",
            },
            { data: "estado", render: renderEstado },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: renderAcciones,
            },
        ],
        order: [
            [2, "asc"],
            [1, "asc"],
        ],
        language: {
            url: "/js/es-ES.json",
        },
        autoWidth: false,
        scrollX: false,
        scrollY: "54vh",
        scrollCollapse: true,
        paging: false,
        dom: "tir",
    });

    tablaEvaluaciones.on("xhr.dt", function (e, settings, json) {
        poblarAreas(json && json.data ? json.data : []);
    });

    tablaEvaluaciones.on("draw.dt", actualizarMetricas);

    // filtros
    $("#selectorFiltroPeriodoEdd").on("change", function () {
        tablaEvaluaciones.ajax.reload();
    });
    $("#selectorFiltroAreaEdd").on("change", function () {
        poblarServicios();
        tablaEvaluaciones.draw();
    });
    $("#selectorFiltroServicioEdd, #selectorFiltroEstadoEdd").on(
        "change",
        function () {
            tablaEvaluaciones.draw();
        },
    );

    // generar período
    $("#btnGenerarPeriodo").on("click", abrirModalGenerar);
    $("#btnConfirmarGenerar").on("click", generarPeriodo);

    // cambiar evaluador
    $(document).on("click", ".btnCambiarEvaluador", abrirModalEvaluador);
    $("#btnConfirmarEvaluador").on("click", asignarEvaluador);
});

/* ============================ FILTROS ============================ */

function cargarFiltros() {
    for (let a = ANIO_ACTUAL; a >= 2024; a--) {
        $("#selectorFiltroPeriodoEdd, #selectPeriodoGenerar").append(
            '<option value="' + a + '">' + a + "</option>",
        );
    }
    $("#selectorFiltroPeriodoEdd").val(ANIO_ACTUAL);

    $.each(EDD_PANEL.estados, function (clave, estado) {
        $("#selectorFiltroEstadoEdd").append(
            '<option value="' + clave + '">' + esc(estado.label) + "</option>",
        );
    });

    // área / servicio / estado se aplican sobre lo cargado
    $.fn.dataTable.ext.search.push(function (settings, data, idx, row) {
        if (settings.nTable.id !== "tbEvaluaciones") return true;

        const area = $("#selectorFiltroAreaEdd").val();
        const servicio = $("#selectorFiltroServicioEdd").val();
        const estado = $("#selectorFiltroEstadoEdd").val();

        if (area && row.area !== area) return false;
        if (servicio && row.servicio !== servicio) return false;
        if (estado && row.estado !== estado) return false;
        return true;
    });
}

function opcionesUnicas($select, valores, textoTodos) {
    const actual = $select.val();
    const unicos = [...new Set(valores.filter(Boolean))].sort();

    $select.empty().append('<option value="">' + textoTodos + "</option>");
    unicos.forEach(function (v) {
        $select.append($("<option>").val(v).text(v));
    });
    $select.val(unicos.includes(actual) ? actual : "");
}

function poblarAreas(filas) {
    opcionesUnicas(
        $("#selectorFiltroAreaEdd"),
        filas.map(function (f) {
            return f.area;
        }),
        "Todas las áreas",
    );
    poblarServicios(filas);
}

function poblarServicios(filas) {
    const area = $("#selectorFiltroAreaEdd").val();
    const base = filas || tablaEvaluaciones.rows().data().toArray();
    const delArea = base.filter(function (f) {
        return !area || f.area === area;
    });

    opcionesUnicas(
        $("#selectorFiltroServicioEdd"),
        delArea.map(function (f) {
            return f.servicio;
        }),
        "Todos los servicios",
    );
}

function actualizarMetricas() {
    const filas = tablaEvaluaciones
        .rows({ search: "applied" })
        .data()
        .toArray();
    const total = filas.length;
    const completas = filas.filter(function (f) {
        return f.estado === "CERRADA";
    }).length;
    const pendientes = filas.filter(function (f) {
        return f.estado === "PENDIENTE";
    }).length;
    const enCurso = total - completas - pendientes;

    $("#lblPorcAvance").text(
        total ? Math.round((completas * 100) / total) + "%" : "-",
    );
    $("#lblCompletas").text(completas);
    $("#lblEnCurso").text(enCurso);
    $("#lblPendientes").text(pendientes);
}

/* ============================ RENDERS ============================ */

function renderColaborador(data, type, row) {
    if (type !== "display") return data;
    return (
        '<div class="fw-bold">' +
        esc(data) +
        "</div>" +
        '<small class="text-muted">Leg. ' +
        esc(row.legajo ?? "-") +
        " · " +
        esc(row.puesto) +
        "</small>"
    );
}

function renderEvaluador(data, type) {
    if (data) return type === "display" ? esc(data) : data;
    return type === "display"
        ? '<span class="badge bg-danger">A definir</span>'
        : "";
}

function renderFecha(data, type) {
    if (!data)
        return type === "display" ? '<span class="text-muted">-</span>' : "";
    if (type !== "display") return data;

    const partes = data.split(" ")[0].split("-");
    return partes[2] + "/" + partes[1] + "/" + partes[0];
}

function renderPromedio(data, type) {
    if (data === null || data === undefined)
        return type === "display" ? '<span class="text-muted">-</span>' : -1;
    return type === "display"
        ? Number(data).toFixed(2).replace(".", ",")
        : Number(data);
}

function renderEstado(data, type) {
    const estado = EDD_PANEL.estados[data] || {
        label: data,
        class: "bg-secondary",
    };
    return type === "display"
        ? '<span class="badge ' +
              estado.class +
              '">' +
              esc(estado.label) +
              "</span>"
        : estado.label;
}

function renderAcciones(data, type, row) {
    const url = EDD_PANEL.routes.formulario.replace(":id", row.id);

    let html =
        Number(row.puede_evaluar) === 1
            ? '<a href="' +
              url +
              '" class="btn btn-sm btn-primary" title="Evaluar"><i class="fa-solid fa-pen-to-square"></i></a>'
            : '<a href="' +
              url +
              '" class="btn btn-sm btn-outline-secondary" title="Ver"><i class="fa-solid fa-eye"></i></a>';

    if (EDD_PANEL.esAdmin && ESTADOS_EDITA_EVALUADOR.includes(row.estado)) {
        html +=
            '<button type="button" class="btn btn-sm btn-outline-warning btnCambiarEvaluador" title="Asignar evaluador"' +
            ' data-id="' +
            row.id +
            '" data-estado="' +
            row.estado +
            '" data-evaluado="' +
            esc(row.evaluado) +
            '">' +
            '<i class="fa-solid fa-user-pen"></i></button>';
    }

    return '<div class="d-flex gap-1">' + html + "</div>";
}

/* ============================ GENERAR PERÍODO ============================ */

function abrirModalGenerar() {
    $("#resultadoGeneracion").addClass("d-none");
    $("#selectPeriodoGenerar").val(ANIO_ACTUAL);
    modal("modalGenerarPeriodo").show();
}

function generarPeriodo() {
    const $btn = $(this);
    const periodo = $("#selectPeriodoGenerar").val();

    Swal.fire({
        icon: "question",
        title: "¿Generar las EDD de " + periodo + "?",
        text: "Solo se crean las que todavía no existen.",
        showCancelButton: true,
        confirmButtonText: "Generar",
        cancelButtonText: "Cancelar",
    }).then(function (r) {
        if (!r.isConfirmed) return;

        $btn.prop("disabled", true);

        postJson(EDD_PANEL.routes.generar, { periodo: periodo })
            .done(function (res) {
                mostrarResultadoGeneracion(res);
                $("#selectorFiltroPeriodoEdd").val(periodo);
                tablaEvaluaciones.ajax.reload();
            })
            .fail(mostrarError)
            .always(function () {
                $btn.prop("disabled", false);
            });
    });
}

function mostrarResultadoGeneracion(res) {
    const s = res.resumen || {};

    $("#resumenGeneracion").html(
        '<span class="badge bg-success fs-6">Generadas: ' +
            (s.GENERADA || 0) +
            "</span>" +
            '<span class="badge bg-secondary fs-6">Ya existían: ' +
            (s.YA_EXISTIA || 0) +
            "</span>" +
            '<span class="badge bg-danger fs-6">No evaluables: ' +
            (s.NO_EVALUABLE || 0) +
            "</span>",
    );

    // no evaluables + generadas con observación (evaluador a definir)
    const filas = res.data.filter(function (f) {
        return (
            f.resultado === "NO_EVALUABLE" ||
            (f.resultado === "GENERADA" && f.motivo)
        );
    });

    let html =
        '<tr><td colspan="5" class="text-center text-muted">Sin observaciones</td></tr>';

    if (filas.length) {
        html = filas
            .map(function (f) {
                const badge =
                    f.resultado === "NO_EVALUABLE"
                        ? '<span class="badge bg-danger">No evaluable</span>'
                        : '<span class="badge bg-warning text-dark">Generada</span>';

                return (
                    "<tr><td>" +
                    esc(f.nombre) +
                    "</td><td>" +
                    esc(f.area) +
                    "</td><td>" +
                    esc(f.puesto ?? "-") +
                    "</td><td>" +
                    badge +
                    "</td><td>" +
                    esc(f.motivo) +
                    "</td></tr>"
                );
            })
            .join("");
    }

    $("#tbodyResultadoGeneracion").html(html);
    $("#resultadoGeneracion").removeClass("d-none");
}

/* ============================ CAMBIAR EVALUADOR ============================ */

function abrirModalEvaluador() {
    eddActual = $(this).data("id");

    $("#lblEvaluadoModal").text($(this).data("evaluado"));
    $("#avisoDescartaBorrador").toggleClass(
        "d-none",
        $(this).data("estado") !== "SUPERIOR_EN_CURSO",
    );

    const $select = $("#selectEvaluador");
    if ($select.hasClass("select2-hidden-accessible"))
        $select.select2("destroy");
    $select.empty().append("<option></option>");

    $.get(EDD_PANEL.routes.candidatos.replace(":id", eddActual))
        .done(function (res) {
            const sugeridos = res.data.filter(function (c) {
                return Number(c.sugerido) === 1;
            });
            const resto = res.data.filter(function (c) {
                return Number(c.sugerido) !== 1;
            });

            if (sugeridos.length)
                $select.append(
                    grupoCandidatos("Sugeridos (puesto superior)", sugeridos),
                );
            $select.append(grupoCandidatos("Resto del personal", resto));

            $select.select2({
                dropdownParent: $("#modalCambiarEvaluador"),
                width: "100%",
                placeholder: "Buscar colaborador...",
            });

            modal("modalCambiarEvaluador").show();
        })
        .fail(mostrarError);
}

function grupoCandidatos(label, lista) {
    const $grupo = $("<optgroup>").attr("label", label);

    lista.forEach(function (c) {
        $grupo.append(
            $("<option>")
                .val(c.id_tramo)
                .text(
                    c.nombre +
                        " — " +
                        (c.puesto || "Sin puesto") +
                        " (" +
                        (c.area || "-") +
                        ")",
                ),
        );
    });

    return $grupo;
}

function asignarEvaluador() {
    const $btn = $(this);
    const tramo = $("#selectEvaluador").val();

    if (!tramo) {
        Swal.fire({ icon: "warning", title: "Seleccioná un evaluador" });
        return;
    }

    $btn.prop("disabled", true);

    postJson(EDD_PANEL.routes.asignar.replace(":id", eddActual), {
        id_tramo_evaluador: tramo,
    })
        .done(function (res) {
            modal("modalCambiarEvaluador").hide();
            Swal.fire({
                icon: "success",
                title: res.message,
                timer: 1500,
                showConfirmButton: false,
            });
            tablaEvaluaciones.ajax.reload(null, false);
        })
        .fail(mostrarError)
        .always(function () {
            $btn.prop("disabled", false);
        });
}

/* ============================ HELPERS ============================ */

function esc(texto) {
    return $("<div>")
        .text(texto ?? "")
        .html();
}

function modal(id) {
    return bootstrap.Modal.getOrCreateInstance(document.getElementById(id));
}

function postJson(url, payload) {
    return $.ajax({
        url: url,
        type: "POST",
        dataType: "json",
        data: JSON.stringify(payload),
        contentType: "application/json",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });
}

function mostrarError(xhr) {
    const msg =
        (xhr.responseJSON && xhr.responseJSON.message) ||
        "Ocurrió un error inesperado.";
    Swal.fire({ icon: "error", title: "No se pudo completar", text: msg });
}
