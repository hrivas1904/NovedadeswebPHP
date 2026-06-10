function formatearFechaLarga(fecha) {
    const meses = [
        "enero",
        "febrero",
        "marzo",
        "abril",
        "mayo",
        "junio",
        "julio",
        "agosto",
        "septiembre",
        "octubre",
        "noviembre",
        "diciembre",
    ];

    const [anio, mes, dia] = fecha.split("-");

    return `${parseInt(dia)} de ${meses[parseInt(mes) - 1]} de ${anio}`;
}

function obtenerDia(fecha) {
    return parseInt(fecha.split("-")[2]);
}

function obtenerMes(fecha) {
    const meses = [
        "ENE",
        "FEB",
        "MAR",
        "ABR",
        "MAY",
        "JUN",
        "JUL",
        "AGO",
        "SEP",
        "OCT",
        "NOV",
        "DIC",
    ];

    return meses[parseInt(fecha.split("-")[1]) - 1];
}

function obtenerFeriados() {
    $.ajax({
        url: "/feriados/lista",
        type: "GET",
        success: function (res) {
            if (res.success) {
                let html = ``;
                res.data.forEach(function (res) {
                    html += `
                        <div class="d-flex align-items-start gap-2 mb-2 empleado-box p-1">
                            <div>
                                <div style="width:60px; background-color:#1DAC8A; color:white;" class="border border-radius rounded-2 d-flex flex-column text-center justify-content-center align-items-center py-2">
                                    <h2 class="fw-bolder mb-0">${obtenerDia(res.date)}</h2>
                                    <h6>${obtenerMes(res.date)}</h6>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 style="color: var(--color-default)" class="fw-bolder">${res.localName}</h6>
                                <p class="text-muted">${formatearFechaLarga(res.date)}</p>
                                <button type="button" class="btn text-muted">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </button>
                                <button type="button" class="btn text-muted">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });

                $("#divCardFeriado").html(html);
            } else {
                Swal.fire({
                    title: "Error",
                    text: "Error de conexión",
                    icon: "error",
                    confirmButtonColor: "#1DAC8A",
                });
            }
            console.log(res.data);
        },
        error: function () {
            Swal.fire({
                title: "Error",
                text: "Error de conexión",
                icon: "error",
                confirmButtonColor: "#1DAC8A",
            });
        },
    });
}

$("#btnRedactarComunicado").click(function () {
    let contenido = $("#txtNotificacion").val().trim();
    let titulo = $("#txtNotificacionTitulo").val().trim();

    if (contenido == "") {
        Swal.fire({
            title: "Debe escribir un mensaje",
            icon: "warning",
        });
        return;
    }

    $.ajax({
        url: "/notificaciones/publicar",
        type: "POST",
        headers: {
            "X-Requested-With": "XMLHttpRequest", // ← agregá esto
        },
        data: {
            contenido: contenido,
            titulo: titulo,
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (data) {
            if (data.success) {
                Swal.fire({
                    title: data.mensaje,
                    icon: "success",
                    customClass: {
                        confirmButtonColor: "btn-primary btn",
                    },
                });

                $("#txtNotificacion").val("");
                $("#txtNotificacionTitulo").val("");
                cargarNotificaciones();
            } else {
                Swal.fire({
                    title: data.mensaje,
                    icon: "error",
                    customClass: {
                        confirmButtonColor: "btn-primary btn",
                    },
                });
            }
        },
        error: function (xhr) {
            if (xhr.status === 401) {
                Swal.fire({
                    title: "Sesión expirada",
                    text: "Por favor recargá la página",
                    icon: "warning",
                    customClass: {
                        confirmButtonColor: "btn-primary btn",
                    },
                });
            } else {
                Swal.fire("Error", "Error del servidor", "error");
                Swal.fire({
                    title: "Error",
                    text: "Error del servidor",
                    icon: "error",
                    customClass: {
                        confirmButtonColor: "btn btn-primary",
                    },
                });
            }
        },
    });
});

function cargarNotificaciones() {
    $.ajax({
        url: "/notificaciones/lista",
        method: "GET",
        success: function (response) {
            const contenedor = $("#listaNotificaciones");
            contenedor.html("");
            let botones = "";

            if (!response || response.length === 0) {
                contenedor.html("<h6>Sin notificaciones...</h6>");
            } else {
                response.forEach((n) => {
                    if (n.estado) {
                    }

                    if (USER_ROLE === "Administrador/a") {
                        botones = `
                            <div class="d-flex justify-content-end">
                                <!--<button type="button" class="btn">
                                    <i class="fa-regular fa-pen-to-square"></i> Editar
                                </button>

                                <button type="button" class="btn">
                                    <i class="fa-regular fa-eye"></i> Mostrar
                                </button>-->

                                <button type="button" class="btn btn-Eliminar" data-id="${n.id_notificacion}">
                                    <i class="fa-regular fa-trash-can"></i> Eliminar
                                </button>
                            </div>
                        `;
                    }

                    contenedor.append(`
                        <div class="card mb-3 p-3 d-flex flex-row align-items-start gap-3" style="border-radius: 12px;">

                            <!-- Icono -->
                            <div class="icon-box d-flex align-items-center justify-content-center" style="
                                width: 50px;
                                height: 50px;
                                border-radius: 50%;
                                background: rgba(0, 145, 213, 0.1);
                                color: var(--color-second);
                                flex-shrink: 0;
                            ">
                                <i class="fa-solid fa-bullhorn"></i>
                            </div>

                            <!-- Contenido -->
                            <div class="flex-grow-1">

                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fa-regular fa-user"></i> Publicado por <strong>${n.name}</strong>
                                    </small>
                                    <small class="text-muted">
                                        <i class="fa-regular fa-calendar"></i> ${n.fecha_publicacion}
                                    </small>
                                </div>

                                <hr/>
                                
                                <h4 class="fw-bold mt-1" style="color: #0b3c6d;">
                                    ${n.titulo}
                                </h4>

                                <p style="white-space: pre-line; color: #1e293b;">
                                    ${n.contenido}
                                </p>

                                ${
                                    USER_ROLE === "Administrador/a"
                                        ? `
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-sm text-danger btn-Eliminar" data-id="${n.id_notificacion}">
                                            <i class="fa-regular fa-trash-can"></i> Eliminar
                                        </button>
                                    </div>
                                `
                                        : ""
                                }

                            </div>
                        </div>
                    `);
                });
            }
        },
        error: function () {
            console.log("Error cargando notificaciones");
        },
    });

    $(document).on("click", ".btn-Eliminar", function (event) {
        event.preventDefault();
        event.stopPropagation();
        const idNotificacion = $(this).data("id");
        console.log("Id recibido: " + idNotificacion);
        eliminarNotificacion(idNotificacion);
    });
}

function eliminarNotificacion(idNotificacion) {
    Swal.fire({
        title: "¿Borrar notificación?",
        text: "La siguiente notificación será eliminada",
        icon: "warning",
        showCancelButton: true,
        cancelButtonColor: "#004a7c",
        confirmButtonColor: "#00b18d",
        confirmButtonText: "Borrar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "/notificaciones/borrar",
                method: "POST",
                data: {
                    idNotificacion: idNotificacion,
                    _token: $('meta[name="csrf-token"]').attr("content"),
                },
                success: function (response) {
                    Swal.fire({
                        title: "Notificación eliminada",
                        text: response.mensaje,
                        icon: "success",
                    });
                    cargarNotificaciones();
                },
                error: function (e) {
                    console.error(e);
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "No se pudo eliminar la notificación",
                    });
                },
            });
        }
    });
}

function cargarDashboardDiario() {
    $.ajax({
        url: "/home/dashboardDiario",
        type: "GET",
        success: function (res) {
            dashboardData = res;

            if (res.totalColabActivos && res.totalColabActivos.length > 0) {
                total = res.totalColabActivos[0].total ?? 0;
                $("#lblColabsActivos").text(total);
            }

            if (res.totalNovMes && res.totalNovMes.length > 0) {
                total = res.totalNovMes[0].total ?? 0;
                $("#lblNovedadesMes").text(total);
            }

            if (res.totalAdPendiente && res.totalAdPendiente.length > 0) {
                total = res.totalAdPendiente[0].total ?? 0;
                $("#lblAdelantosPend").text(total);
            }

            if (
                res.totalTicketsAbiertos &&
                res.totalTicketsAbiertos.length > 0
            ) {
                total = res.totalTicketsAbiertos[0].total ?? 0;
                $("#lblTicketsAbiertos").text(total);
            }
        },
    });
}

function cargarMisOperaciones() {
    $.ajax({
        url: "/home/misOperaciones",
        type: "GET",
        success: function (res) {
            dashboardData = res;

            if (res.misNovedades && res.misNovedades.length > 0) {
                total = res.misNovedades[0].total ?? 0;
                $("#lblMisNovedades").text(total);
            }

            if (res.misAdelantos && res.misAdelantos.length > 0) {
                total = res.misAdelantos[0].total ?? 0;
                $("#lblMisAdelantos").text(total);
            }

            if (res.misTickets && res.misTickets.length > 0) {
                total = res.misTickets[0].total ?? 0;
                $("#lblMisTickets").text(total);
            }
        },
    });
}

$(document).ready(function () {
    cargarNotificaciones();
    obtenerFeriados();
    cargarDashboardDiario();
    cargarMisOperaciones();
});

$("#cardKpiColabActivos").on("click", function () {
    window.location.href = "/nomina";
});

$("#cardKpiNovMes").on("click", function () {
    window.location.href = "/controlNovedades";
});

$("#kpiMisNovedades").on("click", function () {
    window.location.href = "/novedades/misNovedades";
});

$("#cardKpiAdPendientes, #kpiMisSolicitudes").on("click", function () {
    window.location.href = "/solicitudes";
});

$("#cardKpiTicketAbiertos, #kpiMisTickets").on("click", function () {
    window.location.href = "/ayuda";
});
