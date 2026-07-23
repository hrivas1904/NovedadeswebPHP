let tablaSolicitudes = null;
let seleccionados = new Set();

function formatearFechaHora(fechaInput) {
    const fecha = fechaInput ? new Date(fechaInput) : new Date();

    if (isNaN(fecha.getTime())) {
        console.error("HP3C SOFT Error: La fecha proporcionada no es válida.");
        return "Fecha inválida";
    }

    const pad = (num) => String(num).padStart(2, "0");
    const dia = pad(fecha.getDate());
    const mes = pad(fecha.getMonth() + 1);
    const año = fecha.getFullYear();
    const horas = pad(fecha.getHours());
    const minutos = pad(fecha.getMinutes());
    return `${año}-${mes}-${dia} ${horas}:${minutos}`;
}

flatpickr("#fechaDesde, #fechaHasta", {
    locale: "es",
    altInput: true,
    altFormat: "d/m/Y",
    dateFormat: "Y-m-d",
    onChange: function () {
        tablaSolicitudes.ajax.reload();
    },
});

$("#toggleEstado").on("click", function () {
    $("#listaEstados").toggleClass("d-none");
});

$("#toggleDepositado").on("click", function () {
    $("#listaSolicitudes").toggleClass("d-none");
});

function getEstadosSeleccionados() {
    let estados = [];
    $(".check-estado:checked").each(function () {
        estados.push($(this).val());
    });
    return estados;
}

function getDepositoSeleccionado() {
    return $("input[name='filtroDeposito']:checked").val() || null;
}

$(document).on("change", "input[name='filtroDeposito']", function () {
    tablaSolicitudes.ajax.reload();
});

$(document).on("change", ".check-estado", function () {
    tablaSolicitudes.ajax.reload();
});

//CHECK BOX PARA SELECCIONAR SOLICITUDES
$(document).on("change", "#checkAll", function () {
    const isChecked = $(this).is(":checked");
    $(".check-row").prop("checked", isChecked);
    actualizarBotonesMasivos();
});

$(document).on("change", ".check-row", function () {
    const total = $(".check-row").length;
    const checked = $(".check-row:checked").length;
    $("#checkAll").prop("checked", total === checked);
    actualizarBotonesMasivos();
});

$(document).on("click", ".check-row", function (event) {
    event.stopPropagation();
});

function obtenerEstadosSeleccionados() {
    let estados = [];
    $(".check-row:checked").each(function () {
        estados.push($(this).data("estado"));
    });
    return estados;
}

function actualizarBotonesMasivos() {
    let estados = obtenerEstadosSeleccionados();

    let todosPendientes =
        estados.length > 0 && estados.every((e) => e === "PENDIENTE");

    let todosAprobadas =
        estados.length > 0 && estados.every((e) => e === "APROBADA");

    let todosRechazadas =
        estados.length > 0 && estados.every((e) => e === "RECHAZADA");

    $("#btnAprobarSolicitudes").prop("disabled", !todosPendientes);
    $("#btnDepositarAdelantos").prop("disabled", !todosAprobadas);
    $("#btnRechazarSolicitudes").prop("disabled", !todosAprobadas);
}

$("#inputMonto").on("input", function () {
    let valor = parseFloat($(this).val());

    if (isNaN(valor)) return;

    if (valor > 300000) {
        Swal.fire({
            text: "El monto no puede ser superior a $300.000",
            icon: "warning",
            title: "Atención",
        });
        $(this).val(300000);
    } else if (valor < 0) {
        $(this).val(0);
        Swal.fire({
            text: "El monto no puede ser negativo",
            icon: "warning",
            title: "Atención",
        });
    } else if (valor === 0) {
        Swal.fire({
            text: "El monto no puede ser $0",
            icon: "warning",
            title: "Atención",
        });
    }
});

function numeroALetras(num) {
    num = parseInt(num);

    if (isNaN(num) || num === 0) return "cero";

    const unidades = [
        "",
        "un",
        "dos",
        "tres",
        "cuatro",
        "cinco",
        "seis",
        "siete",
        "ocho",
        "nueve",
    ];
    const decenas = [
        "diez",
        "once",
        "doce",
        "trece",
        "catorce",
        "quince",
        "dieciséis",
        "diecisiete",
        "dieciocho",
        "diecinueve",
    ];
    const decenasMas = [
        "veinte",
        "treinta",
        "cuarenta",
        "cincuenta",
        "sesenta",
        "setenta",
        "ochenta",
        "noventa",
    ];
    const centenas = [
        "",
        "ciento",
        "doscientos",
        "trescientos",
        "cuatrocientos",
        "quinientos",
        "seiscientos",
        "setecientos",
        "ochocientos",
        "novecientos",
    ];

    if (num < 10) return unidades[num];

    if (num < 20) return decenas[num - 10];

    if (num < 100) {
        return (
            decenasMas[Math.floor(num / 10) - 2] +
            (num % 10 !== 0 ? " y " + unidades[num % 10] : "")
        );
    }

    if (num === 100) return "cien";

    if (num < 1000) {
        return (
            centenas[Math.floor(num / 100)] +
            (num % 100 !== 0 ? " " + numeroALetras(num % 100) : "")
        );
    }

    if (num < 1000000) {
        const miles = Math.floor(num / 1000);
        const resto = num % 1000;

        let textoMiles = "";

        if (miles === 1) {
            textoMiles = "mil";
        } else {
            textoMiles = numeroALetras(miles) + " mil";
        }

        return textoMiles + (resto !== 0 ? " " + numeroALetras(resto) : "");
    }

    return "Número muy grande";
}

function getScrollY() {
    return window.innerWidth < 768 ? "28vh" : "56vh";
}

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
    $("#inputDescripMonto").val(
        numeroALetras($("#inputMontoEnviar").val()).toUpperCase() + " PESOS",
    );
    console.log($("#inputDescripMonto").val());
});

$("#inputMonto").on("keydown", function (e) {
    const teclasBloqueadas = [190, 188, 186, 110];
    if (teclasBloqueadas.includes(e.which)) {
        e.preventDefault();
    }
});

function abrirSolicitud() {
    const legajo = USER_LEGAJO;

    Swal.showLoading();

    $.ajax({
        url: `/rrhh/personal/info/${legajo}`,
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
        url: "/rrhh/solicitudes/registrar",
        method: "POST",
        data: $(this).serialize(),
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (resp) {
            if (resp.ok) {
                Swal.fire({
                    icon: "success",
                    title: "Operación exitosa",
                    text: resp.mensaje,
                    confirmButtonColor: "#00b18d",
                });

                cerrarModalSolicitud();
                $("#formCargaSolicitud")[0].reset();
                tablaSolicitudes.ajax.reload(null, false);
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: resp.mensaje,
                    confirmButtonColor: "#00b18d",
                });
            }
        },
        error: function () {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Error del servidor",
                confirmButtonColor: "#00b18d",
            });
        },
    });
});

$(document).on("click", "#btnAprobarSolicitudes", function (event) {
    event.preventDefault();

    let ids = [];

    $(".check-row:checked").each(function () {
        ids.push($(this).data("id"));
    });

    if (ids.length === 0) {
        Swal.fire({
            title: "Atención",
            text: "Seleccione al menos una solicitud",
            icon: "warning",
            confirmButtonColor: "#1DAC8A",
        });
        return;
    }

    Swal.fire({
        title: "¿Confirmar aprobación?",
        text: `Se aprobarán ${ids.length} solicitud(es)`,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, aprobar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#00b18d",
        cancelButtonColor: "#004a7c",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "/rrhh/personal/aprobarSolicitud",

                type: "POST",

                data: {
                    ids: ids,
                },

                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content",
                    ),
                },

                success: function (res) {
                    if (res.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Operación exitosa",
                            text: res.mensaje,
                            confirmButtonColor: "#00b18d",
                        });

                        $("#checkAll").prop("checked", false);

                        tablaSolicitudes.ajax.reload(null, false);
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: res.mensaje,
                            confirmButtonColor: "#00b18d",
                        });
                    }
                },

                error: function () {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Error del servidor",
                        confirmButtonColor: "#00b18d",
                    });
                },
            });
        }
    });
});

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
            url: "/rrhh/personal/anularSolicitud",
            method: "POST",
            data: { idSolicitud: idSolicitud },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (resp) {
                if (resp.ok) {
                    Swal.fire({
                        title: "Solicitud anulada",
                        text: resp.mensaje,
                        icon: "success",
                        confirmButtonColor: "#00b18d",
                    });
                    tablaSolicitudes.ajax.reload(null, false);
                } else {
                    Swal.fire({
                        title: "Error",
                        text: resp.mensaje,
                        icon: "error",
                        confirmButtonColor: "#00b18d",
                    });
                }
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                Swal.fire({
                    title: "Error",
                    text: "Error del servidor",
                    icon: "error",
                    confirmButtonColor: "#00b18d",
                });
            },
        });
    });
}

$(document).ready(function () {
    if ($("#tb_solicitudes").length > 0) {
        tablaSolicitudes = $("#tb_solicitudes").DataTable({
            ajax: {
                url: "/rrhh/personal/listarSolicitudes",
                type: "GET",
                dataSrc: "data",
                data: function (d) {
                    d.estado = getEstadosSeleccionados().join(",") || null;
                    d.depositado = getDepositoSeleccionado();
                    d.fechaDesde = $("#fechaDesde").val() || null;
                    d.fechaHasta = $("#fechaHasta").val() || null;
                },
            },
            autoWidth: true,
            scrollX: false,
            paging: false,
            scrollCollapse: true,
            scrollY: getScrollY(),
            responsive: true,
            columnDefs: [
                {
                    targets: [1, 8],
                    className: "all",
                },
                { responsivePriority: 1, targets: 1 }, // fecha
                { responsivePriority: 2, targets: 8 }, // monto
                { responsivePriority: 3, targets: 13 }, // estado
                { responsivePriority: 100, targets: 0 }, // id
                { responsivePriority: 100, targets: 2 }, // legajo
                { responsivePriority: 100, targets: 3 }, // cuil
                { responsivePriority: 100, targets: 4 }, // colab
                { responsivePriority: 100, targets: 5 }, // area
                { responsivePriority: 100, targets: 6 }, // cuenta
                { responsivePriority: 100, targets: 7 }, // cbu
                { responsivePriority: 100, targets: 10 }, // comprobante
                { responsivePriority: 100, targets: 12 }, // observaciones
                { responsivePriority: 100, targets: 14 }, // acciones
                { responsivePriority: 100, targets: 9 }, // monto export
                {
                    targets: [3, 5, 6, 7, 9, 10],
                    visible: false,
                },
                {
                    targets: [14],
                    visible: USER_ROLE === "Administrador/a",
                },
            ],
            order: [[0, "desc"]],
            language: {
                url: "/js/es-ES.json",
            },
            footerCallback: function (row, data, start, end, display) {
                let api = this.api();
                let totalFiltrado = api
                    .column(9, { filter: "applied" })
                    .data()
                    .reduce(function (a, b) {
                        return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                    }, 0);
                $(api.column(8).footer()).html(formatearPesos(totalFiltrado));
            },
            columns: [
                {
                    data: "id",
                    width: "3%",
                    className: "text-start",
                    orderable: true,
                },
                {
                    data: "fecha",
                    width: "6%",
                    className: "text-start",
                    render: function (data) {
                        return formatearFechaHora(data);
                    },
                },
                {
                    data: "legajo",
                    width: "4%",
                    className: "text-start",
                },
                {
                    data: "cuil",
                    width: "4%",
                },
                {
                    data: "colaborador",
                    width: "25%",
                    className: "text-start",
                },
                { data: "area", width: "10%", className: "text-start" },
                {
                    data: "numero_cuenta",
                    render: function (data, type, row) {
                        return data;
                    },
                },
                {
                    data: "cbu",
                    render: function (data, type, row) {
                        if (type === "display" || type === "filter") {
                            return data;
                        }
                        return data;
                    },
                },
                {
                    data: "monto",
                    width: "5%",
                    className: "text-start",
                    render: function (data) {
                        return formatearPesos(data);
                    },
                },
                {
                    data: "monto",
                    width: "5%",
                    className: "text-end",
                },
                {
                    data: "comprobante",
                    width: "5%",
                    className: "text-start",
                },
                {
                    data: "banco",
                    width: "5%",
                    className: "text-start",
                },
                {
                    data: "observaciones",
                    width: "25%",
                    className: "text-start",
                    render: function (data) {
                        const texto = data ?? "";
                        if (!texto) return ""; // 👈 si está vacío, no generar HTML
                        return `<span title="${texto}" style="display:block;white-space:normal;word-break:break-word;">${texto}</span>`;
                    },
                },
                {
                    data: "estado",
                    width: "12%",
                    orderable: false,
                    className: "text-start",
                    render: function (data) {
                        let clase = "bg-secondary";
                        if (data === "PENDIENTE") clase = "bg-warning";
                        if (data === "APROBADA") clase = "bg-success";
                        if (data === "RECHAZADA") clase = "bg-danger";
                        return `<span style="font-size:.80rem" class="badge ${clase}">${data}</span>`;
                    },
                },
                {
                    data: null,
                    width: "3%",
                    orderable: false,
                    className: "text-center",
                    render: function (data, type, row) {
                        return `
                            <input 
                                type="checkbox"
                                class="form-check-input check-row"
                                data-id="${row.id}"
                                data-estado="${row.estado}">
                        `;
                    },
                },
            ],
            dom: "<'d-top d-flex flex-column flex-md-row align-items-md-center gap-2 mt-1 mx-1' \
                    <'d-flex flex-column flex-sm-row gap-2'B> \
                    <'ms-md-auto mt-2 mt-md-0'f> \
                > \
                <'my-2'rt> \
                <'d-bottom d-flex justify-content-center'i>",
            buttons: [
                {
                    extend: "excelHtml5",
                    text: '<i class="fa-solid fa-file-excel"></i> Excel',
                    className: "btn-export-excel dt-buttons",
                    exportOptions: {
                        columns: [2, 3, 4, 6, 7, 9, 10, 11, 12],
                        format: {
                            body: function (data, row, column, node) {
                                // Limpiar HTML y obtener solo el texto
                                const tmp = document.createElement("div");
                                tmp.innerHTML = data;
                                const texto =
                                    tmp.textContent || tmp.innerText || data;

                                if (/^\d{11,}$/.test(texto)) {
                                    return "'" + texto;
                                }
                                return texto;
                            },
                        },
                    },
                },
            ],
        });
    }

    $("#selectEstado").on("change", function () {
        tablaSolicitudes.ajax.reload();
    });

    $("#selectDepositado").on("change", function () {
        tablaSolicitudes.ajax.reload();
    });
});

$("#btnLimpiarFiltros").on("click", function (e) {
    e.preventDefault();
    $("#selectEstado").val("");
    $("#selectDepositado").val(0);
    document.querySelector("#fechaDesde")._flatpickr.clear();
    document.querySelector("#fechaHasta")._flatpickr.clear();
    tablaSolicitudes.search("").draw(); // opcional: limpia búsqueda global
    tablaSolicitudes.ajax.reload(null, false);
});

$(document).on("click", "#btnDepositarAdelantos", function (event) {
    event.preventDefault();
    let ids = [];
    $(".check-row:checked").each(function () {
        ids.push($(this).data("id"));
    });

    if (ids.length === 0) {
        Swal.fire({
            title: "Atención",
            text: "Seleccione al menos una solicitud",
            icon: "warning",
            confirmButtonColor: "#1DAC8A",
        });
        return;
    }

    Swal.fire({
        title: "¿Confirmar depósito?",
        text: `Se depositarán ${ids.length} solicitud(es)`,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, depositar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#00b18d",
        cancelButtonColor: "#004a7c",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "/rrhh/personal/depositarAdelantos",
                type: "POST",
                data: {
                    ids: ids,
                },
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content",
                    ),
                },
                success: function (res) {
                    if (res.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Operación exitosa",
                            text: res.mensaje,
                            confirmButtonColor: "#00b18d",
                        });
                        $("#checkAll").prop("checked", false);
                        tablaSolicitudes.ajax.reload(null, false);
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: res.mensaje,
                            confirmButtonColor: "#00b18d",
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Error del servidor",
                        confirmButtonColor: "#00b18d",
                    });
                },
            });
        }
    });
});

$(document).on("click", "#btnRechazarAdelantos", function (event) {
    event.preventDefault();

    let ids = [];

    $(".check-row:checked").each(function () {
        ids.push($(this).data("id"));
    });

    if (ids.length === 0) {
        Swal.fire({
            title: "Atención",
            text: "Seleccione al menos una solicitud",
            icon: "warning",
            confirmButtonColor: "#1DAC8A",
        });
        return;
    }

    Swal.fire({
        title: "¿Confirmar rechazo?",
        text: `Se rechazarán ${ids.length} solicitud(es)`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, rechazar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#1DAC8A",
        cancelButtonColor: "#004a7c",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "/rrhh/personal/rechazarSolicitud",

                type: "POST",

                data: {
                    ids: ids,
                },

                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content",
                    ),
                },

                success: function (res) {
                    if (res.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Operación exitosa",
                            text: res.mensaje,
                            confirmButtonColor: "#00b18d",
                        });

                        $("#checkAll").prop("checked", false);

                        tablaSolicitudes.ajax.reload(null, false);
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: res.mensaje,
                            confirmButtonColor: "#00b18d",
                        });
                    }
                },

                error: function (xhr) {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: xhr.responseJSON?.error || "Error del servidor",
                        confirmButtonColor: "#00b18d",
                    });
                },
            });
        }
    });
});
