let tablaConsentimientos = null;
let filtroEstado = "todos";

$(document).ready(function () {
    cargarObrasSociales();
});

const CAMPOS_CONSENTIMIENTO = {
    firmado_pac: "Firmado por paciente",
    firmado_med: "Firmado por médico",
    subido: "Consentimiento subido",
};

$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    DataTable.ext.search.push(function (settings, data, dataIndex, row) {
        if (
            settings.nTable.id !== "tbConsentimientos" ||
            filtroEstado === "todos"
        )
            return true;

        const pac = esVerdadero(row.firmado_pac);
        const med = esVerdadero(row.firmado_med);
        const sub = esVerdadero(row.subido);

        if (filtroEstado === "pendientes") return !(pac && med && sub);
        if (filtroEstado === "sinfirma") return !(pac && med);
        if (filtroEstado === "completos") return pac && med && sub;
        return true;
    });

    tablaConsentimientos = new DataTable("#tbConsentimientos", {
        ajax: {
            url: RECEPCION_ROUTES.listarAtenciones,
            data: function (d) {
                d.desde = $("#fechaDesde").val() || null;
                d.hasta = $("#fechaHasta").val() || null;
                d.obra_social = $("#selectorObraSocial").val() || null;
            },
            dataSrc: "data",
        },
        columns: [
            { data: "id", visible: false },
            { data: "atencion", className: "text-start" },
            {
                data: "paciente",
                render: DataTable.render.text(),
                className: "text-start",
            },
            {
                data: "obra_social",
                render: DataTable.render.text(),
                className: "text-start",
            },
            {
                data: "fecha_ingreso",
                render: (v, t, row) =>
                    t === "display" ? renderIngreso(row) : v || "",
            },
            { data: "hora_ingreso", className: "text-start" },
            {
                data: "medico",
                render: DataTable.render.text(),
                className: "text-start",
            },
            {
                data: "firmado_pac",
                orderable: false,
                className: "text-center",
                render: (v, t, row) => renderCheck(row, "firmado_pac"),
            },
            {
                data: "firmado_med",
                orderable: false,
                className: "text-center",
                render: (v, t, row) => renderCheck(row, "firmado_med"),
            },
            {
                data: "subido",
                orderable: false,
                className: "text-center",
                render: (v, t, row) => renderCheck(row, "subido"),
            },
        ],
        order: [],
        scrollX: false,
        scrollY: "60vh",
        scrollCollapse: true,
        pageLength: 25,
        language: { url: "/js/es-ES.json" },
        createdRow: function (tr, row) {
            pintarFila(tr, row);
        },
    });

    tablaConsentimientos.on("xhr", function () {
        const json = tablaConsentimientos.ajax.json();
        actualizarTotales(json ? json.data : []);
    });

    $("#fechaDesde, #fechaHasta, #selectorObraSocial").on(
        "change",
        function () {
            tablaConsentimientos.ajax.reload();
        },
    );

    $("#inputFileConsentimientos").on("change", function () {
        const archivo = this.files[0];
        if (archivo) importarArchivo(archivo);
        this.value = "";
    });

    // Delegado: las filas se re-renderizan
    $(document).on(
        "change",
        "#tbConsentimientos .chk-consentimiento",
        function () {
            marcar($(this));
        },
    );

    $("#filtroEstado").on("click", "button", function () {
        $("#filtroEstado button").removeClass("active");
        $(this).addClass("active");
        filtroEstado = $(this).data("estado");
        tablaConsentimientos.draw();
    });
});

if (filtroEstado !== "todos") tablaConsentimientos.draw(false);

/* ---------------- Render ---------------- */

function esVerdadero(v) {
    return Number(v) === 1;
}

function renderCheck(row, campo) {
    const marcado = esVerdadero(row[campo]);
    const quien = row[campo + "_por"];
    const cuando = row[campo + "_at"];
    const titulo =
        marcado && cuando
            ? `${quien || "Usuario"} · ${cuando}`
            : CAMPOS_CONSENTIMIENTO[campo];
    return `<input type="checkbox" class="form-check-input chk-consentimiento"
                data-atencion="${row.atencion}" data-campo="${campo}"
                title="${titulo}" aria-label="${CAMPOS_CONSENTIMIENTO[campo]}"
                ${marcado ? "checked" : ""}>`;
}

function pintarFila(tr, row) {
    const completo =
        esVerdadero(row.firmado_pac) &&
        esVerdadero(row.firmado_med) &&
        esVerdadero(row.subido);
    tr.classList.toggle("consent-sin-firma-pac", !esVerdadero(row.firmado_pac));
    tr.classList.toggle("consent-pendiente", !completo);
}

function actualizarTotales(filas) {
    const total = filas.length;
    const contar = (campo) => filas.filter((r) => esVerdadero(r[campo])).length;
    const pendientes = filas.filter(
        (r) =>
            !(
                esVerdadero(r.firmado_pac) &&
                esVerdadero(r.firmado_med) &&
                esVerdadero(r.subido)
            ),
    ).length;

    $("#lblTotalPacientes").text(total);
    $("#lblTotalFirmadoPacientes").text(`${contar("firmado_pac")} / ${total}`);
    $("#lblTotalFirmadoMedicos").text(`${contar("firmado_med")} / ${total}`);
    $("#lblTotalConsentSubidos").text(`${contar("subido")} / ${total}`);
    $("#lblTotalPendientes").text(pendientes);
}

/* ---------------- Acciones ---------------- */

function marcar($chk) {
    const tr = $chk.closest("tr")[0];
    const fila = tablaConsentimientos.row(tr);
    const campo = $chk.data("campo");
    const valor = $chk.is(":checked");

    $chk.prop("disabled", true);

    $.ajax({
        url: RECEPCION_ROUTES.marcarAtencion,
        method: "POST",
        data: {
            atencion: String($chk.data("atencion")),
            campo: campo,
            valor: valor ? 1 : 0,
        },
    })
        .done(function (resp) {
            // Actualiza los datos de la fila sin redibujar, para que no salte de lugar mientras se trabaja
            const datos = Object.assign({}, fila.data(), resp.data);
            datos[campo + "_at"] = resp.data.marcado_at;
            datos[campo + "_por"] = valor
                ? window.USUARIO_NOMBRE || null
                : null;
            fila.data(datos);
            pintarFila(tr, datos);
            actualizarTotales(tablaConsentimientos.rows().data().toArray());
        })
        .fail(function (xhr) {
            $chk.prop("checked", !valor);
            Swal.fire(
                "No se pudo guardar",
                xhr.responseJSON?.message || "Intentá de nuevo.",
                "error",
            );
        })
        .always(function () {
            // fila.data() re-renderiza la celda; si no, se reactiva el mismo checkbox
            $(tr)
                .find(`.chk-consentimiento[data-campo="${campo}"]`)
                .prop("disabled", false);
        });
}

function importarArchivo(archivo) {
    if (!/\.pdf$/i.test(archivo.name)) {
        Swal.fire(
            "Archivo no válido",
            'Subí el PDF "Listado de Encabezado de Atenciones en Internado" exportado de Geclisa.',
            "warning",
        );
        return;
    }

    const fd = new FormData();
    fd.append("archivo", archivo);

    Swal.fire({
        title: "Leyendo archivo…",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    $.ajax({
        url: RECEPCION_ROUTES.importarAtenciones,
        method: "POST",
        data: fd,
        processData: false,
        contentType: false,
    })
        .done(function (r) {
            // MySQL puede devolver los conteos como string: normalizar antes de comparar
            const enPdf = Number(r.en_pdf) || 0;
            const leidas = Number(r.leidas) || 0;
            const insertadas = Number(r.insertadas) || 0;
            const omitidas = Number(r.omitidas) || 0;
            const completadas = Number(r.completadas) || 0;
            const nuevasSinFecha = Number(r.nuevas_sin_fecha) || 0;

            const lineas = [`<b>${insertadas}</b> atenciones nuevas`];
            let icono = "success";

            if (omitidas) {
                lineas.push(
                    `${omitidas} ya estaban cargadas` +
                        (completadas
                            ? ` (a ${completadas} se les completaron datos que faltaban)`
                            : " y se omitieron"),
                );
            }

            const avisos = [];

            if (enPdf !== leidas) {
                avisos.push(
                    `El PDF tiene <b>${enPdf}</b> atenciones pero se pudieron leer <b>${leidas}</b>. Revisá las faltantes a mano.`,
                );
            }

            if (nuevasSinFecha) {
                avisos.push(
                    `<b>${nuevasSinFecha}</b> atenciones nuevas quedaron sin fecha u hora de ingreso. Cargalas con el lápiz de la columna Fecha.`,
                );
            }

            if (avisos.length) icono = "warning";

            Swal.fire({
                title: $("<div>").text(archivo.name).html(),
                html:
                    lineas.join("<br>") +
                    (avisos.length ? "<hr>" + avisos.join("<br><br>") : ""),
                icon: icono,
            });

            tablaConsentimientos.ajax.reload(null, false);
        })
        .fail(function (xhr) {
            const r = xhr.responseJSON || {};
            let msg =
                r.mensaje ||
                (r.errors && r.errors.archivo && r.errors.archivo[0]) ||
                "No se pudo procesar el archivo.";

            if (xhr.status === 413)
                msg = "El archivo es demasiado grande para subirlo.";
            if (xhr.status === 419)
                msg = "La sesión expiró. Recargá la página e intentá de nuevo.";

            Swal.fire("Error al importar", msg, "error");
        });
}

/* ---------------- Utilidades ---------------- */

function fechaISO(d) {
    const p = (n) => String(n).padStart(2, "0");
    return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}`;
}

$("#btnLimpiarFiltros").on("click", function () {
    $("#fechaDesde, #fechaHasta").val("");

    filtroEstado = "todos";
    $("#filtroEstado button").removeClass("active");
    $('#filtroEstado button[data-estado="todos"]').addClass("active");

    tablaConsentimientos.search(""); // también limpia el buscador de DataTables
    tablaConsentimientos.ajax.reload(); // recarga sin fechas: trae todo
});

function cargarObrasSociales() {
    const selector = $("#selectorObraSocial");

    $.ajax({
        url: "/recepcion/consentimientos/obtenerObrasSociales",
        type: "GET",
        dataType: "json",
        beforeSend: function () {
            selector.html(
                '<option value="">Cargando obras sociales...</option>',
            );
            selector.prop("disabled", true);
        },
        success: function (response) {
            selector.empty();
            selector.append('<option value="">Seleccione obra social</option>');

            response.forEach(function (item) {
                if (item.obra_social) {
                    selector.append(
                        $("<option>", {
                            value: item.obra_social,
                            text: item.obra_social,
                        }),
                    );
                }
            });
        },
        error: function (xhr) {
            console.error("Error al cargar obras sociales:", xhr.responseText);

            selector.html(
                '<option value="">Error al cargar obras sociales</option>',
            );
        },
        complete: function () {
            selector.prop("disabled", false);
        },
    });
}

function renderIngreso(row) {
    const btn = `<button type="button" class="btn btn-sm p-0 ms-2 btn-editar-ingreso"
                    data-atencion="${row.atencion}" title="Editar fecha y hora de ingreso">
                    <i class="fa-regular fa-pen-to-square"></i>`;
    return row.fecha_ingreso
        ? `${row.fecha_ingreso}${btn}`
        : `<span class="badge text-bg-warning">Sin fecha</span>${btn}`;
}

function ddmmaaaaAISO(f) {
    if (!f) return "";
    const [d, m, a] = f.split("/");
    return `${a}-${m}-${d}`;
}

function editarIngreso(btn) {
    const tr = $(btn).closest("tr")[0];
    const fila = tablaConsentimientos.row(tr);
    const row = fila.data();

    Swal.fire({
        title: `Atención ${row.atencion}`,
        html: `
            <div class="text-start small text-muted mb-3">${$("<div>").text(row.paciente).html()}</div>
            <div class="d-flex gap-2">
                <div class="flex-fill text-start">
                    <label for="swFecha" class="form-label small fw-bold">Fecha de ingreso</label>
                    <input type="date" id="swFecha" class="form-control" value="${ddmmaaaaAISO(row.fecha_ingreso)}" max="${fechaISO(new Date())}">
                </div>
                <div class="flex-fill text-start">
                    <label for="swHora" class="form-label small fw-bold">Hora</label>
                    <input type="time" id="swHora" class="form-control" value="${row.hora_ingreso || ""}">
                </div>
            </div>`,
        showCancelButton: true,
        confirmButtonText: "Guardar",
        cancelButtonText: "Cancelar",
        focusConfirm: false,
        preConfirm: () => {
            const fecha = $("#swFecha").val();
            if (!fecha) {
                Swal.showValidationMessage("Ingresá la fecha de ingreso");
                return false;
            }
            return $.post(RECEPCION_ROUTES.editarIngreso, {
                atencion: String(row.atencion),
                fecha: fecha,
                hora: $("#swHora").val() || null,
            }).catch((xhr) => {
                Swal.showValidationMessage(
                    xhr.responseJSON?.message || "No se pudo guardar",
                );
            });
        },
        allowOutsideClick: () => !Swal.isLoading(),
    }).then((res) => {
        if (!res.isConfirmed || !res.value) return;
        fila.data(Object.assign({}, row, res.value.data));
        Swal.fire({
            icon: "success",
            title: "Guardado",
            timer: 1200,
            showConfirmButton: false,
        });
    });
}

$(document).on("click", "#tbConsentimientos .btn-editar-ingreso", function () {
    editarIngreso(this);
});
