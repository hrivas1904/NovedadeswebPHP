let tablaSolicitudes = null;

function formatearPesos(valor) {
    if (valor === null || valor === undefined || valor === "") return "";

    return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
        minimumFractionDigits: 2,
    }).format(valor);
}

function parsearMonto(valor) {
    if (!valor) return 0;
    let limpio = valor.toString();
    limpio = limpio.replace(/\$/g, "").trim();
    limpio = limpio.replace(/\./g, "");
    limpio = limpio.replace(/,/g, ".");

    const resultado = parseFloat(limpio);
    return isNaN(resultado) ? 0 : resultado;
}

$("#inputMonto").on("blur", function () {
    const valorInput = $(this).val();
    const montoFormateado = formatearPesos(valorInput);
    $(this).val(montoFormateado);
});

$("#inputMonto").on("focus", function () {
    $(this).val("");
});

$("#inputMonto").on("change", function () {
    const montoFormateado = $("#inputMonto").val();
    const montoParseado = parsearMonto(montoFormateado);
    $("#inputMontoEnviar").val(montoParseado);
    console.log($("#inputMontoEnviar").val());
});

function abrirSolicitud() {
    const legajo = USER_LEGAJO;

    Swal.showLoading();

    $.ajax({
        url: `/personal/info/${legajo}`,
        type: "GET",
        success: function (data) {
            Swal.close();
            const $modal = $("#modalRegSolicitud");

            $modal.find("input[name='colaborador']").val(data.COLABORADOR);
            $modal.find("input[name='servicio']").val(data.SERVICIO);
            $modal.modal("show");
        },
        error: function () {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "No se pudo obtener la información del colaborador",
            });
        },
    });
}

$("#btnAbrirModalSolicitud").on("click", function () {
    abrirSolicitud();
});

function cerrarModalSolicitud() {
    const modalSolicitud = $("#modalRegSolicitud");
    const formulario = $("#formCargaSolicitud");
    formulario[0].reset();
    modalSolicitud.modal("hide");
}

$("#formCargaSolicitud").on("submit", function (e) {
    e.preventDefault();

    $.ajax({
        url: "/solicitudes/registrar",
        method: "POST",
        data: $(this).serialize(),
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (resp) {
            if (resp.ok) {
                Swal.fire({
                    icon: "success",
                    title: "Solicitud registrada",
                    text: resp.mensaje,
                });

                cerrarModalSolicitud();
                $("#formCargaSolicitud")[0].reset();
                tablaSolicitudes.ajax.reload(null,false);
            } else {
                Swal.fire("Error", resp.mensaje, "error");
            }
        },
        error: function () {
            Swal.fire("Error", "Error servidor", "error");
        },
    });
});

$(document).ready(function () {
    if ($("#tb_solicitudes").length > 0) {
        tablaSolicitudes = $("#tb_solicitudes").DataTable({
            ajax: {
                url: "/personal/listarSolicitudes",
                type: "GET",
                dataSrc: "data",
                data: function (d) {
                    d.area_id = $("#filtroArea").val() || null;
                    d.categ_id = $("#filtroCategoria").val() || null;
                    d.p_regimen = $("#filtroRegimen").val() || null;
                    d.p_convenio = $("#filtroConvenio").val() || null;
                },
            },
            autoWidth: true,
            scrollX: false,
            paging: false,
            scrollCollapse: true,
            scrollY: "60vh",
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            },
            columns: [
                { data: "id", width: "3%", className: "text-start", orderable: "desc" },
                { data: "fecha", width: "6%", className: "text-start" },
                {
                    data: "legajo",
                    width: "4%",
                    className: "text-start",
                    render: function (data, type, row) {
                        return data.toString().padStart(5, "0");
                    },
                },
                {
                    data: "colaborador",
                    width: "auto",
                    className: "text-start",
                    width: "25%",
                },
                { data: "area", width: "10%", className: "text-start" },
                {
                    data: "monto",
                    width: "5%",
                    className: "text-end",
                    render: function (data) {
                        return formatearPesos(data);
                    },
                },
                {
                    data: "observaciones",
                    width: "25%",
                    className: "text-start",
                },
                {
                    data: "estado",
                    width: "10%",
                    orderable: false,
                    className: "text-center",
                    render: function (data) {
                        let clase = "bg-secondary";

                        if (data === "PENDIENTE DE AUTORIZACIÓN")
                            clase = "bg-warning";
                        if (data === "APROBADO") clase = "bg-success";
                        if (data === "RECHAZADO") clase = "bg-danger";
                        return `<span style="font-size: 0.80rem;" class="badge ${clase}">${data}</span>`;
                    },
                },
                {
                    data: null,
                    className: "text-center",
                    orderable: false,
                    render: function (data) {
                        return `
                            <button 
                                class="btn-primario btn-AprobarSolicitud"
                                data-id="${data.id}"
                                title="Aprobar solicitud">
                                <i class="fa-solid fa-square-check"></i>  
                            </button>
                            <button 
                                class="btn-secundario btn-RechazarSolicitud"
                                data-id="${data.id}"
                                title="Rechazar solicitud">
                                <i class="fa-solid fa-square-xmark fs-5"></i>
                            </button>
                        `;
                    },
                },
            ],
            dom: "<'d-top d-flex align-items-center gap-2 mt-1'B<'d-flex ms-auto'f>><'my-2'rt><'d-bottom d-flex align-items-center justify-content-center'i>",
            buttons: [
                {
                    extend: "excelHtml5",
                    text: '<i class="fa-solid fa-file-excel"></i> Excel',
                    className: "btn-export-excel",
                    exportOptions: { columns: ":visible" },
                },
                {
                    extend: "pdfHtml5",
                    text: '<i class="fa-solid fa-file-pdf"></i> PDF',
                    className: "btn-export-pdf",
                    exportOptions: { columns: ":visible" },
                },
                {
                    extend: "print",
                    text: '<i class="fa-solid fa-print"></i> Imprimir',
                    title: "Nómina de personal",
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5] },
                    className: "btn-printer",
                },
            ],
        });
    }
});
