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
        url: "/eventosProgramados/lista",
        type: "GET",
        success: function (res) {
            if (!res.success) {
                Swal.fire({
                    title: "Error",
                    text: "Error de conexión",
                    icon: "error",
                    confirmButtonColor: "#1DAC8A",
                });
                return;
            }

            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);

            // ordenar cronológicamente
            const eventos = [...res.data].sort((a, b) => {
                return new Date(a.fechaEvento) - new Date(b.fechaEvento);
            });

            // buscar el evento actual o próximo
            let proximoEvento = eventos.find((e) => {
                const fecha = new Date(e.fechaEvento);
                fecha.setHours(0, 0, 0, 0);
                return fecha >= hoy;
            });

            if (!proximoEvento) {
                proximoEvento = eventos[0];
            }

            //----------------------------------------------------
            // CARD DESTACADA
            //----------------------------------------------------

            let htmlProximo = "";

            if (proximoEvento) {
                const fecha = new Date(proximoEvento.fechaEvento);
                fecha.setHours(0, 0, 0, 0);

                const diferenciaDias = Math.floor(
                    (fecha - hoy) / (1000 * 60 * 60 * 24),
                );

                let badge = "";
                if (diferenciaDias === 0) {
                    badge = `<span class="badge bg-success">HOY</span>`;
                } else if (diferenciaDias === 1) {
                    badge = `<span class="badge bg-warning text-dark">MAÑANA</span>`;
                } else {
                    badge = `<span class="badge bg-primary">EN ${diferenciaDias} DÍAS</span>`;
                }

                htmlProximo = `
                    <div class="card border-0 shadow-sm mb-2"
                        style="background:#f7fffd;border-left:5px solid #1DAC8A;">

                        <div class="card-body p-3">

                            <div class="d-flex justify-content-between align-items-start">

                                <div>
                                    ${badge}
                                    <h6 class="fw-bold mt-2 mb-1"
                                        style="color:var(--color-default)">
                                        ${proximoEvento.tituloEvento}
                                    </h6>
                                </div>
                                <div
                                    style="
                                        width:65px;
                                        background:#1DAC8A;
                                        color:white;
                                        border-radius:10px;
                                    "
                                    class="text-center p-2">
                                    <h2 class="mb-0 fw-bold">
                                        ${obtenerDia(proximoEvento.fechaEvento)}
                                    </h2>
                                    <small>
                                        ${obtenerMes(proximoEvento.fechaEvento)}
                                    </small>

                                </div>

                            </div>

                        </div>

                    </div>
                `;
            }

            $("#divProximoEvento").html(htmlProximo);

            //----------------------------------------------------
            // LISTADO
            //----------------------------------------------------

            let html = "";

            eventos.forEach(function (evento) {
                const botonesAdmin =
                    USER_ROLE === "Administrador/a" ||
                    USER_ROLE === "Supervisor/a Calidad"
                        ? `
                            <button
                                class="btn text-muted btnEditarEvento"
                                data-id="${evento.idEvento}">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>

                            <button
                                class="btn text-muted btnEliminarEvento"
                                data-id="${evento.idEvento}">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        `
                        : "";

                const destacado = evento.idEvento === proximoEvento.idEvento;

                html += `
                    <div class="d-flex align-items-start gap-2 mb-2 empleado-box p-2
                        ${destacado ? "evento-destacado" : ""}">

                        <div>

                            <div
                                style="
                                    width:60px;
                                    background:#1DAC8A;
                                    color:white;
                                "
                                class="rounded-2 d-flex flex-column justify-content-center align-items-center py-2">

                                <h2 class="fw-bolder mb-0">
                                    ${obtenerDia(evento.fechaEvento)}
                                </h2>

                                <h6 class="mb-0">
                                    ${obtenerMes(evento.fechaEvento)}
                                </h6>

                            </div>

                        </div>

                        <div class="flex-grow-1">

                            <h6 class="fw-bold"
                                style="color:var(--color-default)">
                                ${evento.tituloEvento}
                            </h6>

                            ${
                                evento.descripcionEvento
                                    ? `<small class="text-muted">${evento.descripcionEvento}</small>`
                                    : ""
                            }

                            <div class="mt-1">
                                ${botonesAdmin}
                            </div>

                        </div>

                    </div>
                `;
            });

            $("#divCardFeriado").html(html);
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

$(document).on("click", ".btnEditarEvento", function () {
    const idEvento = $(this).data("id");

    $.ajax({
        url: `/eventosProgramados/verDetalle/${idEvento}`,
        type: "GET",
        dataType: "json",

        success: function (res) {
            if (res.success) {
                const e = res.data;

                $("#inputIdEventoEdit").val(e.idEvento);
                $("#inputFechaEventoEdit").val(e.fechaEvento.substring(0, 10));
                $("#selectTipoEventoEdit").val(e.tipoEvento).trigger("change");
                $("#inputTituloEventoEdit").val(e.tituloEvento);
                $("#inputDescripEventoEdit").val(e.descripcionEvento);
                $("#modalDetalleEvento").modal("show");
            }
        },

        error: function () {
            Swal.fire({
                title: "Error",
                text: "No se pudo obtener el detalle del evento.",
                icon: "error",
                confirmButtonColor: "#1DAC8A",
            });
        },
    });
});

$(document).on("click", ".btnEliminarEvento", function () {
    const idEvento = $(this).data("id");

    Swal.fire({
        title: "¿Eliminar evento?",
        text: "Esta acción no se puede deshacer.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Eliminar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "/eventosProgramados/eliminar",
                type: "POST",
                data: {
                    idEvento: idEvento,
                    _token: $('meta[name="csrf-token"]').attr("content"),
                },

                success: function (res) {
                    if (res.success) {
                        Swal.fire({
                            title: "Éxito",
                            text: res.message,
                            icon: "success",
                            confirmButtonColor: "#1DAC8A",
                        });

                        obtenerFeriados();
                    }
                },

                error: function () {
                    Swal.fire({
                        title: "Error",
                        text: "No se pudo eliminar el evento.",
                        icon: "error",
                        confirmButtonColor: "#1DAC8A",
                    });
                },
            });
        }
    });
});

function editarNotificacion(id) {
    const titulo = $("#txtNotificacionTitulo").val().trim();
    const contenido = quill.root.innerHTML;

    if (!titulo) {
        Swal.fire({
            icon: "warning",
            title: "Asunto requerido",
            text: "Debe ingresar un asunto.",
        });
        return;
    }

    if (!quill.getText().trim()) {
        Swal.fire({
            icon: "warning",
            title: "Contenido requerido",
            text: "Debe ingresar el contenido del aviso.",
        });
        return;
    }

    $.ajax({
        url: NOTIFICACIONES_ROUTES.editar,
        type: "POST",
        dataType: "json",

        data: {
            id: id,
            titulo: titulo,
            contenido: contenido,
            _token: $("meta[name='csrf-token']").attr("content"),
        },

        beforeSend: function () {
            $("#btnRedactarComunicado").prop("disabled", true).html(`
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    Guardando...
                `);
        },

        success: function (res) {
            if (!res.success) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: res.message,
                });

                return;
            }

            Swal.fire({
                icon: "success",
                title: "¡Operación exitosa!",
                text: res.message,
                timer: 1200,
                showConfirmButton: false,
            });

            limpiarFormularioNotificacion();
            cargarNotificaciones();
        },

        error: function (xhr) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text:
                    xhr.responseJSON?.message ??
                    "No fue posible actualizar la notificación.",
            });
        },

        complete: function () {
            $("#btnRedactarComunicado").prop("disabled", false).html(`
                    <i id="iconBtnRedactar" class="fa-solid fa-floppy-disk"></i>
                    Guardar cambios
                `);
        },
    });
}

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
                                    USER_ROLE === "Administrador/a" ||
                                    USER_ROLE === "Supervisor/a Calidad"
                                        ? `
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-sm text-danger btn-Eliminar" data-id="${n.id_notificacion}">
                                            <i class="fa-regular fa-trash-can"></i> Eliminar
                                        </button>
                                        <button type="button" class="btn btn-sm text-primary btn-HabilitarEdicion" data-id="${n.id_notificacion}">
                                            <i class="fa-regular fa-pen-to-square"></i> Editar
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

    $(document).on("click", ".btn-HabilitarEdicion", function (event) {
        event.preventDefault();
        event.stopPropagation();
        const idNotificacion = $(this).data("id");
        console.log("Id recibido: " + idNotificacion);
        habilitarEdicionNotificacion(idNotificacion);
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

function habilitarEdicionNotificacion(idNotificacion) {
    $.ajax({
        url: "/notificaciones/ver/" + idNotificacion,
        type: "GET",
        dataType: "json",

        beforeSend: function () {
            $("#btnRedactarComunicado")
                .prop("disabled", true)
                .html(
                    '<span class="spinner-border spinner-border-sm me-2"></span>Cargando...',
                );
        },

        success: function (res) {
            console.log("Respuesta completa:", res);

            if (!res.success || !res.notificacion) {
                Swal.fire({
                    icon: "warning",
                    title: "No se pudo cargar",
                    text:
                        res.message ||
                        "No se encontró la notificación seleccionada.",
                });

                return;
            }

            const notificacion = res.notificacion;

            console.log("Notificación:", notificacion);

            modoEdicion = true;
            idNotificacionEditar = notificacion.id_notificacion;

            $("#txtIdNotificacionEditar").val(notificacion.id_notificacion);

            $("#txtNotificacionTitulo").val(notificacion.titulo ?? "");

            quill.root.innerHTML = notificacion.contenido ?? "";

            $("#lblTituloFormularioAviso").text("Editar aviso");

            $("#lblSubtituloFormularioAviso").text(
                "Modificá el asunto o el contenido del aviso.",
            );

            $("#iconBtnRedactar")
                .removeClass("fa-bullhorn")
                .addClass("fa-floppy-disk");

            $("#btnRedactarComunicado")
                .prop("disabled", false)
                .html(
                    '<i id="iconBtnRedactar" class="fa-solid fa-floppy-disk"></i> Guardar cambios',
                );

            $("#divCardBodyPublicarAviso").removeClass("d-none");

            $("#iconPublicarAviso")
                .removeClass("fa-circle-arrow-down")
                .addClass("fa-circle-arrow-up");

            document.getElementById("publicarNotificacion").scrollIntoView({
                behavior: "smooth",
                block: "start",
            });
        },

        error: function (xhr) {
            console.error("Error AJAX:", xhr);
            console.error("Respuesta:", xhr.responseText);

            Swal.fire({
                icon: "error",
                title: "Error",
                text:
                    xhr.responseJSON?.error ||
                    xhr.responseJSON?.message ||
                    "No se pudo cargar la notificación.",
            });
        },

        complete: function () {
            if (!modoEdicion) {
                $("#btnRedactarComunicado")
                    .prop("disabled", false)
                    .html(
                        '<i id="iconBtnRedactar" class="fa-solid fa-bullhorn"></i> Publicar',
                    );
            }
        },
    });
}

$("#btnCancelarRedactarComunicado").on("click", function () {
    limpiarFormularioNotificacion();
});

function limpiarFormularioNotificacion() {
    modoEdicion = false;
    idNotificacionEditar = null;

    $("#txtIdNotificacionEditar").val("");
    $("#txtNotificacionTitulo").val("");
    $("#txtNotificacion").val("");

    quill.setContents([]);

    $("#lblTituloFormularioAviso").text("Publicar nuevo aviso");

    $("#lblSubtituloFormularioAviso").text(
        "Escribí el asunto y el contenido del aviso.",
    );

    $("#btnRedactarComunicado").html(`
        <i id="iconBtnRedactar" class="fa-solid fa-bullhorn"></i>
        Publicar
    `);

    $("#divCardBodyPublicarAviso").addClass("d-none");

    $("#iconPublicarAviso")
        .removeClass("fa-circle-arrow-up")
        .addClass("fa-circle-arrow-down");
}

function publicarNotificacion() {
    const titulo = $("#txtNotificacionTitulo").val().trim();
    const contenido = quill.root.innerHTML;

    if (!titulo) {
        Swal.fire({
            icon: "warning",
            title: "Falta el asunto",
            text: "Ingresá un asunto para el aviso.",
        });
        return;
    }

    if (!quill.getText().trim()) {
        Swal.fire({
            icon: "warning",
            title: "Falta el contenido",
            text: "Ingresá el contenido del aviso.",
        });
        return;
    }

    $.ajax({
        url: "/notificaciones/publicar",
        type: "POST",
        dataType: "json",

        data: {
            titulo: titulo,
            contenido: contenido,
            _token: $("meta[name='csrf-token']").attr("content"),
        },

        beforeSend: function () {
            $("#btnRedactarComunicado").prop("disabled", true).html(`
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    Publicando...
                `);
        },

        success: function (res) {
            if (!res.success) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: res.mensaje,
                });
                return;
            }

            Swal.fire({
                icon: "success",
                title: "¡Operación exitosa!",
                text: res.mensaje,
                timer: 1200,
                showConfirmButton: false,
            });

            limpiarFormularioNotificacion();
            cargarNotificaciones();
        },

        error: function (xhr) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text:
                    xhr.responseJSON?.mensaje ??
                    "No fue posible publicar la notificación.",
            });
        },

        complete: function () {
            $("#btnRedactarComunicado").prop("disabled", false).html(`
                    <i id="iconBtnRedactar" class="fa-solid fa-bullhorn"></i>
                    Publicar
                `);
        },
    });
}

function cargarDashboardDiario() {
    $.ajax({
        url: "/rrhh/home/dashboardDiario",
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
        url: "/rrhh/home/misOperaciones",
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

$("#btnGuardarEvento").on("click", function () {
    const fecha = $("#inputFechaEvento").val();
    const tipo = $("#selectTipoEvento").val();
    const titulo = $("#inputTituloEvento").val();

    if (!fecha || !tipo || !titulo) {
        Swal.fire({
            title: "Atención",
            text: "Complete todos los campos.",
            icon: "warning",
            buttonsStyling: false,
            customClass: {
                confirmButton: "btn btn-primary",
            },
        });
        return;
    }

    $.ajax({
        url: "/calendario/agendarEvento",
        type: "POST",
        data: {
            fechaEvento: $("#inputFechaEvento").val(),
            tipoEvento: $("#selectTipoEvento").val(),
            tituloEvento: $("#inputTituloEvento").val(),
            descripEvento: $("#inputDescripEvento").val(),
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (res) {
            if (res.success) {
                Swal.fire({
                    title: "Éxito",
                    text: res.message,
                    icon: "success",
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: "btn btn-primary",
                    },
                }).then(() => {
                    $("#formNuevoEventoProgramado")[0].reset();
                    $("#modalNuevoEventoProgramado").modal("hide");
                });
            }
        },
        error: function () {
            Swal.fire({
                title: "Error",
                text: "No se pudo registrar el evento.",
                icon: "error",
                buttonsStyling: false,
                customClass: {
                    confirmButton: "btn btn-primary",
                },
            });
        },
    });
});

$("#btnEditarEvento").on("click", function () {
    $.ajax({
        url: "/eventosProgramados/editar",
        type: "POST",
        data: {
            idEvento: $("#inputIdEventoEdit").val(),
            fechaEvento: $("#inputFechaEventoEdit").val(),
            tipoEvento: $("#selectTipoEventoEdit").val(),
            tituloEvento: $("#inputTituloEventoEdit").val(),
            descripcionEvento: $("#inputDescripEventoEdit").val(),
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (res) {
            Swal.fire({
                title: "Éxito",
                text: res.message,
                icon: "success",
                confirmButtonColor: "#1DAC8A",
            }).then(() => {
                $("#modalDetalleEvento").modal("hide");

                obtenerFeriados();
            });
        },
        error: function () {
            Swal.fire({
                title: "Error",
                text: "No se pudo actualizar el evento.",
                icon: "error",
                confirmButtonColor: "#1DAC8A",
            });
        },
    });
});

$("#divHeaderPublicarAviso").on("click", function () {
    $("#divCardBodyPublicarAviso").toggleClass("d-none");
    $("#iconPublicarAviso").toggleClass(
        "fa-circle-arrow-down fa-circle-arrow-up",
    );
});

$(document).ready(function () {
    cargarNotificaciones();
    obtenerFeriados();
    cargarDashboardDiario();
    cargarMisOperaciones();
});

$("#cardKpiColabActivos").on("click", function () {
    window.location.href = "/rrhh/nomina";
});

$("#cardKpiNovMes").on("click", function () {
    window.location.href = "/rrhh/controlNovedades";
});

$("#kpiMisNovedades").on("click", function () {
    window.location.href = "/rrhh/novedades/misNovedades";
});

$("#cardKpiAdPendientes, #kpiMisSolicitudes").on("click", function () {
    window.location.href = "/rrhh/solicitudes";
});

$("#cardKpiTicketAbiertos, #kpiMisTickets").on("click", function () {
    window.location.href = "/rrhh/ayuda";
});
