let tablaDescriptivos = null;

function getAreasSeleccionadas() {
    return $(".check-area:checked")
        .map(function () {
            return $(this).val();
        })
        .get();
}

$("#toggleAreas").on("click", function () {
    $("#listaAreas").toggleClass("d-none");
});

function cargarAreas() {
    $.ajax({
        url: "/rrhh/areas/lista",
        method: "GET",
        success: function (data) {
            const container = $("#listaAreas");
            const areaFija = $("#areaFija").val();

            container.empty();

            data.forEach((area) => {
                const checked = areaFija == area.id_area ? "checked" : "";
                const disabled = areaFija ? "disabled" : "";

                container.append(`
                    <label class="filtro-item">
                        <input type="checkbox" 
                               class="check-area" 
                               value="${area.id_area}" 
                               ${checked} ${disabled}>
                        ${area.nombre}
                    </label>
                `);
            });
        },
    });
}

$(document).ready(function () {
    tablaDescriptivos = $("#tb_descriptivos").DataTable({
        ajax: {
            url: "/rrhh/descriptivos/obtenerDescriptivos",
            type: "GET",
            dataType: "json",
            data: function (d) {
                d.p_id_area = getAreasSeleccionadas().join(",") || null;
                d.p_estado = null;
                d.p_solo_ultima = null;
            },
        },
        language: {
            url: "/js/ES-es.json",
        },
        scrollX: false,
        autoWidth: true,
        scrollY: "65vh",
        scrollCollapse: true,
        dom: "tir",
        paging: false,
        columns: [
            { data: "nombre_puesto" },
            { data: "area" },
            {
                data: "version",
                render: function (v) {
                    return "v" + v;
                },
            },
            {
                data: "estado",
                render: function (estado) {
                    const colores = {
                        BORRADOR: "bg-secondary",
                        VALIDADO: "bg-info text-dark",
                        PUBLICADO: "bg-success",
                        REEMPLAZADO: "bg-dark",
                    };
                    return (
                        '<span class="badge ' +
                        (colores[estado] || "bg-secondary") +
                        '">' +
                        estado +
                        "</span>"
                    );
                },
            },
        ],
        createdRow: function (row) {
            $(row).addClass("fila-clickeable");
        },
    });

    $(document).on("change", ".check-area", function () {
        tablaDescriptivos.ajax.reload();
    });

    $("#tb_descriptivos tbody").on("click", "tr", function () {
        const fila = tablaDescriptivos.row(this).data();
        if (!fila) return;
        verDetalleDescriptivo(fila.id);
    });

    $("#btn-limpiar-filtros").on("click", function () {
        $(".check-area").prop("checked", false);
        $("#toggleAreas span").text("Áreas");
        $("#searchPersonal").val("");
        tablaDescriptivos.search("").draw();
        tablaDescriptivos.ajax.reload();
    });

    $("#searchPersonal").on("keyup", function () {
        tablaDescriptivos.search($(this).val()).draw();
    });

    $("#btnClearSearch").on("click", function () {
        $("#searchPersonal").val("");
        tablaDescriptivos.search("").draw();
    });
});

function llenarModalDescriptivo(d, competencias) {
    const $modal = $("#modalVerDescriptivo");

    $modal.find("[data-campo]").each(function () {
        const $el = $(this);
        const valor = d[$el.data("campo")];
        let texto = valor === null || valor === undefined ? "" : String(valor);

        // tareas: una viñeta por renglón (solo para mostrar)
        if ($el.data("campo") === "tareas" && texto) {
            texto = texto
                .split("\n")
                .map((t) => t.replace(/^[•\-\*]\s*/, "").trim())
                .filter(Boolean)
                .map((t) => "• " + t)
                .join("\n");
        }

        if ($el.is("input, textarea")) {
            $el.val(texto).attr("placeholder", "Sin completar");
            // alto según la cantidad de líneas (tareas, normas, etc.)
            if ($el.is("textarea")) {
                $el.attr("rows", Math.max(2, texto.split("\n").length));
            }
        } else {
            $el.text(texto);
        }
    });

    const colores = {
        BORRADOR: "bg-secondary",
        VALIDADO: "bg-info text-dark",
        PUBLICADO: "bg-success",
        REEMPLAZADO: "bg-dark",
    };
    $("#badgeEstadoDescriptivo")
        .attr("class", "badge " + (colores[d.estado] || "bg-secondary"))
        .text(d.estado);

    const pintarCompetencias = function ($lista, items) {
        $lista.empty();
        if (!items.length) {
            $lista.append(
                $('<div class="list-group-item text-muted fst-italic">').text(
                    "Sin competencias",
                ),
            );
            return;
        }
        items.forEach(function (c) {
            const $item = $('<div class="list-group-item">')
                .append($('<div class="fw-semibold">').text(c.nombre))
                .append(
                    $('<small class="text-muted">').text(c.descripcion || ""),
                );
            $lista.append($item);
        });
    };
    competencias = competencias || [];
    pintarCompetencias(
        $("#listaCompGenericas"),
        competencias.filter((c) => c.tipo === "ORGANIZACIONAL"),
    );
    pintarCompetencias(
        $("#listaCompEspecificas"),
        competencias.filter((c) => c.tipo === "FUNCIONAL"),
    );

    // auditoría
    const partes = [
        "Creado por " + (d.creado_por || "-") + " el " + (d.created_at || "-"),
    ];
    if (d.validated_at)
        partes.push("validado por " + d.validado_por + " el " + d.validated_at);
    if (d.published_at)
        partes.push(
            "publicado por " + d.publicado_por + " el " + d.published_at,
        );
    $("#lblAuditoriaDescriptivo").text(partes.join(" · "));

    // botones del circuito según estado y rol (esAdmin / esCoordinador vienen del layout)
    $modal.data("idDescriptivo", d.id);

    $modal
        .find('[data-accion="validar"]')
        .toggleClass(
            "d-none",
            !(d.estado === "BORRADOR" && (esAdmin || esCoordinador)),
        );

    $modal
        .find('[data-accion="devolver"], [data-accion="publicar"]')
        .toggleClass("d-none", !(d.estado === "VALIDADO" && esAdmin));

    // guardo lo cargado para poder pasarlo a edición
    $modal.data("descriptivo", d).data("competencias", competencias);
    $("#btnContinuarBorrador").toggleClass(
        "d-none",
        !(d.estado === "BORRADOR" && esAdmin),
    );

    // compromiso: un ítem por campo, cada renglón como viñeta (solo lectura)
    const compromisos = [
        { campo: "capacitacion_continua", titulo: "Capacitación continua" },
        { campo: "normas_calidad", titulo: "Normas de calidad (ISO 9001)" },
        { campo: "compromiso_ambiental", titulo: "Compromiso ambiental" },
        {
            campo: "seguridad_salud_ocupacional",
            titulo: "Seguridad y salud ocupacional",
        },
        { campo: "etica_confidencialidad", titulo: "Ética y confidencialidad" },
    ];
    const $comp = $("#listaCompromisos").empty();

    compromisos.forEach(function (c) {
        const lineas = String(d[c.campo] || "")
            .split("\n")
            .map((t) => t.trim())
            .filter(Boolean);
        const $item = $('<div class="list-group-item">').append(
            $('<div class="fw-semibold">').text(c.titulo),
        );

        if (lineas.length) {
            const $ul = $('<ul class="mb-0 ps-3 small text-muted">');
            lineas.forEach((t) => $ul.append($("<li>").text(t)));
            $item.append($ul);
        } else {
            $item.append(
                $('<small class="text-muted fst-italic">').text(
                    "Sin completar",
                ),
            );
        }
        $comp.append($item);
    });

    $modal.modal("show");
}

function verDetalleDescriptivo(idDescriptivo) {
    $.ajax({
        url: "/rrhh/descriptivos/verDetalleDescriptivo",
        type: "GET",
        dataType: "json",
        data: {
            p_id_descriptivo: idDescriptivo,
        },
        success: function (res) {
            llenarModalDescriptivo(res.data, res.competencias);
        },
        error: function (xhr) {
            const msg =
                (xhr.responseJSON && xhr.responseJSON.message) ||
                "No se pudo cargar el descriptivo.";
            Swal.fire("Error", msg, "error");
        },
    });
}

$(document).on("click", ".btn-estado-descriptivo", function () {
    const accion = $(this).data("accion");
    const idDescriptivo = $("#modalVerDescriptivo").data("idDescriptivo");

    const textos = {
        validar: {
            titulo: "¿Validar el descriptivo?",
            texto: "Queda listo para que RRHH lo publique.",
        },
        devolver: {
            titulo: "¿Devolver a borrador?",
            texto: "Indicá qué hay que corregir.",
        },
        publicar: {
            titulo: "¿Publicar el descriptivo?",
            texto: "Pasa a ser la versión vigente del puesto y reemplaza a la anterior.",
        },
    };

    Swal.fire({
        title: textos[accion].titulo,
        text: textos[accion].texto,
        icon: "question",
        input: accion === "devolver" ? "textarea" : undefined,
        inputPlaceholder: "Motivo de la devolución",
        inputValidator:
            accion === "devolver"
                ? (v) =>
                      !v || !v.trim() ? "El motivo es obligatorio" : undefined
                : undefined,
        showCancelButton: true,
        confirmButtonText: "Confirmar",
        cancelButtonText: "Cancelar",
    }).then(function (r) {
        if (!r.isConfirmed) return;

        $.ajax({
            url: "/rrhh/descriptivos/cambiarEstadoDescriptivo",
            type: "POST",
            dataType: "json",
            data: {
                idDescriptivo: idDescriptivo,
                accion: accion,
                motivo: accion === "devolver" ? r.value : null,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (res) {
                Swal.fire({
                    icon: "success",
                    title: res.message,
                    timer: 1500,
                    showConfirmButton: false,
                });
                verDetalleDescriptivo(idDescriptivo); // recarga el modal con el estado nuevo
                tablaDescriptivos.ajax.reload(null, false);
            },
            error: function (xhr) {
                const res = xhr.responseJSON || {};
                Swal.fire(
                    "Error",
                    res.message || "No se pudo cambiar el estado.",
                    "error",
                );
            },
        });
    });
});

// aplica el valor a un select cuando ya tiene opciones; si no, cuando terminen las cargas
function setSelectEdicion($sel, valor) {
    const v = valor === null || valor === undefined ? "" : String(valor);
    if ($sel.find(`option[value="${v}"]`).length) {
        $sel.val(v).trigger("change");
    } else {
        $(document).one("ajaxStop", () => $sel.val(v).trigger("change"));
    }
}

// abre el modal de alta precargado con el borrador
function abrirEdicionBorrador(d, competencias) {
    const $form = $("#formAltaDescriptivo");
    $form[0].reset();
    $("#divCompetenciasGenericas, #divCompetenciasEspecificas").empty();

    $("#hiddenIdDescriptivoEdicion").val(d.id);
    $("#tituloModalDescriptivo").text("Continuar borrador");
    $("#subtituloModalDescriptivo").text(
        `${d.nombre_puesto} · versión ${d.version}`,
    );

    // el puesto no se cambia en la edición
    const $puesto = $("#selectPuestoDescriptivo");
    setSelectEdicion($puesto, d.codigo_puesto);
    $puesto.prop("disabled", true).prop("required", false);

    setSelectEdicion($("#selectAreaDescriptivo"), d.id_area);
    setSelectEdicion($("#selectServicioDescriptivo"), d.id_servicio);

    const campos = {
        nombrePuesto: d.nombre_puesto,
        reportaA: d.reporta_a,
        supervisorDirecto: d.supervisor_directo,
        supervisaA: d.supervisa_a,
        propositoPuesto: d.proposito_puesto,
        tareas: d.tareas,
        relacionesInternas: d.relaciones_internas,
        relacionesExternas: d.relaciones_externas,
        formacion: d.formacion,
        experiencia: d.experiencia,
        conocimientos: d.conocimientos,
        capacitacionContinua: d.capacitacion_continua,
        normasCalidad: d.normas_calidad,
        compromisoAmbiental: d.compromiso_ambiental,
        seguridadSaludOcupacional: d.seguridad_salud_ocupacional,
        eticaConfidencialidad: d.etica_confidencialidad,
        contribucion: d.contribucion,
    };
    $.each(campos, (name, valor) =>
        $form.find(`[name="${name}"]`).val(valor || ""),
    );

    // competencias como filas editables
    competencias.forEach(function (c) {
        const $div =
            c.tipo === "ORGANIZACIONAL"
                ? $("#divCompetenciasGenericas")
                : $("#divCompetenciasEspecificas");
        agregarFormNuevaCompetencia($div, c.tipo);
        const $fila = $div.find(".div-competencia").last();
        $fila.find('[name="competenciaNombre[]"]').val(c.nombre);
        $fila
            .find('[name="competenciaDescripcion[]"]')
            .val(c.descripcion === "A definir." ? "" : c.descripcion);
    });

    $("#modalAltaDescriptivo").modal("show");
}

$(document).on("click", "#btnContinuarBorrador", function () {
    const $ver = $("#modalVerDescriptivo");
    const d = $ver.data("descriptivo");
    const competencias = $ver.data("competencias");
    $ver.modal("hide");
    abrirEdicionBorrador(d, competencias);
});

// al cerrar, el modal vuelve a modo alta
$(document).on("hidden.bs.modal", "#modalAltaDescriptivo", function () {
    $("#hiddenIdDescriptivoEdicion").val("");
    $("#tituloModalDescriptivo").text("Nuevo descriptivo de puesto");
    $("#subtituloModalDescriptivo").text("Se guarda como borrador (versión 1)");
    $("#selectPuestoDescriptivo")
        .prop("disabled", false)
        .prop("required", true);
    $("#formAltaDescriptivo")[0].reset();
    $("#divCompetenciasGenericas, #divCompetenciasEspecificas").empty();
});

$(document).ready(function () {
    cargarAreas();
});
