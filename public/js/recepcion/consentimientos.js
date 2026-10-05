let tablaConsentimientos = null;
let filtroEstado = "todos";

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
            { data: "fecha_ingreso", className: "text-start" },
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
        order: [], // respeta el orden del SP: sin firma del paciente primero
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

    $("#fechaDesde, #fechaHasta").on("change", function () {
        tablaConsentimientos.ajax.reload();
    });

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

if (filtroEstado !== 'todos') tablaConsentimientos.draw(false);

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
            let html =
                `<b>${r.insertadas}</b> atenciones nuevas` +
                (r.omitidas
                    ? `<br>${r.omitidas} ya estaban cargadas y se omitieron`
                    : "");
            let icono = "success";

            if (r.en_pdf !== r.leidas) {
                icono = "warning";
                html += `<br><br>El PDF tiene <b>${r.en_pdf}</b> atenciones pero se pudieron leer <b>${r.leidas}</b>.
                         Revisá las faltantes a mano.`;
            }

            Swal.fire({ title: archivo.name, html: html, icon: icono });
            tablaConsentimientos.ajax.reload();
        })
        .fail(function (xhr) {
            const r = xhr.responseJSON || {};
            const msg =
                r.mensaje ||
                (r.errors && r.errors.archivo && r.errors.archivo[0]) ||
                "No se pudo procesar el archivo.";
            Swal.fire("Error al importar", msg, "error");
        });
}

/* ---------------- Utilidades ---------------- */

function fechaISO(d) {
    const p = (n) => String(n).padStart(2, "0");
    return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}`;
}

$('#btnLimpiarFiltros').on('click', function () {
    $('#fechaDesde, #fechaHasta').val('');

    filtroEstado = 'todos';
    $('#filtroEstado button').removeClass('active');
    $('#filtroEstado button[data-estado="todos"]').addClass('active');

    tablaConsentimientos.search('');      // también limpia el buscador de DataTables
    tablaConsentimientos.ajax.reload();   // recarga sin fechas: trae todo
});