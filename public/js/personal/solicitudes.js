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
                tablaSolicitudes.ajax.reload(null, false);
            } else {
                Swal.fire("Error", resp.mensaje, "error");
            }
        },
        error: function () {
            Swal.fire("Error", "Error servidor", "error");
        },
    });
});

function aprobarSolicitud(idSolicitud, nombre, e) {
    e.preventDefault();

    Swal.fire({
        title: `¿Aprobar la solicitud N° ${idSolicitud}?`,
        text: `Colaborador: ${nombre}`,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Aprobar",
        cancelButtonText: "Rechazar",
        confirmButtonColor: "#00b18d",
        cancelButtonColor: "#004a7c",
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "/personal/aprobarSolicitud",
                method: "POST",
                data: { idSolicitud: idSolicitud },
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content",
                    ),
                },
                success: function (resp) {
                    if (resp.ok) {
                        Swal.fire("Aprobado", resp.mensaje, "success");
                        tablaSolicitudes.ajax.reload(null, false);
                    } else {
                        Swal.fire("Error", resp.mensaje, "error");
                    }
                },
                error: function (xhr) {
                    console.log(xhr.responseText);
                    Swal.fire("Error", "Error servidor", "error");
                },
            });
        }

        if (result.dismiss === Swal.DismissReason.cancel) {
            $.ajax({
                url: "/personal/rechazarSolicitud",
                method: "POST",
                data: { idSolicitud: idSolicitud },
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content",
                    ),
                },
                success: function (resp) {
                    if (resp.ok) {
                        Swal.fire("Rechazada", resp.mensaje, "success");
                        tablaSolicitudes.ajax.reload(null, false);
                    } else {
                        Swal.fire("Error", resp.mensaje, "error");
                    }
                },
                error: function (xhr) {
                    console.log(xhr.responseText);
                    Swal.fire("Error", "Error servidor", "error");
                },
            });
        }
    });
}

function anularSolicitud(idSolicitud, nombre, e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }

    Swal.fire({
        title: `¿Anular la solicitud N° ${idSolicitud}?`,
        text: `Colaborador: ${nombre}`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Anular",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#00b18d",
        cancelButtonColor: "#004a7c",
        reverseButtons: true,
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            url: "/personal/anularSolicitud",
            method: "POST",
            data: { idSolicitud: idSolicitud },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (resp) {
                if (resp.ok) {
                    Swal.fire("Listo", resp.mensaje, "success");
                    tablaSolicitudes.ajax.reload(null, false);
                } else {
                    Swal.fire("Error", resp.mensaje, "error");
                }
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                Swal.fire("Error", "Error servidor", "error");
            },
        });
    });
}

$(document).ready(function () {
    if ($("#tb_solicitudes").length > 0) {
        tablaSolicitudes = $("#tb_solicitudes").DataTable({
            ajax: {
                url: "/personal/listarSolicitudes",
                type: "GET",
                dataSrc: "data",
                data: function (d) {
                    d.estado = $("#selectEstado").val() || null;
                    d.fechaDesde = $("#fechaDesde").val() || null;
                    d.fechaHasta = $("#fechaHasta").val() || null;
                },
            },
            autoWidth: true,
            scrollX: false,
            paging: false,
            scrollCollapse: true,
            scrollY: "60vh",
            order: [[0, "desc"]],
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            },
            columns: [
                {
                    data: "id",
                    width: "3%",
                    className: "text-start",
                    orderable: true,
                },
                { data: "fecha", width: "6%", className: "text-start" },
                {
                    data: "legajo",
                    width: "4%",
                    className: "text-start",
                    render: function (data) {
                        return data.toString().padStart(5, "0");
                    },
                },
                {
                    data: "colaborador",
                    width: "25%",
                    className: "text-start",
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
                        if (data === "APROBADA") clase = "bg-success";
                        if (data === "RECHAZADA") clase = "bg-danger";
                        return `<span style="font-size:.80rem" class="badge ${clase}">${data}</span>`;
                    },
                },
                {
                    data: null,
                    className: "text-center",
                    orderable: false,
                    render: function (row) {
                        if (USER_ROLE !== "Administrador/a") return "";

                        let botones = "";

                        if (row.estado === "PENDIENTE DE AUTORIZACIÓN") {
                            botones += `
                                <button
                                    type="button"
                                    class="btn-primario btn-AprobarSolicitud"
                                    data-id="${row.id}"
                                    data-nombre="${row.colaborador}"
                                    title="Aprobar solicitud">
                                    <i class="fa-solid fa-square-check"></i>
                                </button>
                            `;
                        }

                        if (row.estado === "APROBADA") {
                            botones += `
                                <button
                                    type="button"
                                    class="btn-secundario btn-RechazarSolicitud"
                                    data-id="${row.id}"
                                    data-nombre="${row.colaborador}"
                                    title="Anular solicitud">
                                    <i class="fa-solid fa-square-xmark fs-5"></i>
                                </button>
                            `;
                        }
                        return botones;
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

    $(
            "#selectEstado",
        ).on("change", function () {
            tablaSolicitudes.ajax.reload();
        });

    $(document).on("click", ".btn-AprobarSolicitud", function (event) {
        event.preventDefault();
        event.stopPropagation();
        const idSolicitud = $(this).data("id");
        const nombre = $(this).data("nombre");
        aprobarSolicitud(idSolicitud, nombre, event);
    });

    $(document).on("click", ".btn-RechazarSolicitud", function (event) {
        const idSolicitud = $(this).data("id");
        const nombre = $(this).data("nombre");
        anularSolicitud(idSolicitud, nombre, event);
    });
});

$("#btnAplicarFiltros").on("click", function (e) {
    e.preventDefault();
    tablaSolicitudes.ajax.reload(null, false);
});

$("#btnLimpiarFiltros").on("click", function (e) {
    e.preventDefault();

    $("#selectEstado").val("");
    $("#fechaDesde").val("");
    $("#fechaHasta").val("");

    tablaSolicitudes.search("").draw(); // opcional: limpia búsqueda global
    tablaSolicitudes.ajax.reload(null, false);
});