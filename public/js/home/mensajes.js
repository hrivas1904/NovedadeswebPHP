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
                });

                $("#txtNotificacion").val("");
                $("#txtNotificacionTitulo").val("");
                cargarNotificaciones();
            } else {
                Swal.fire({
                    title: data.mensaje,
                    icon: "error",
                });
            }
        },
        error: function () {
            Swal.fire("Error", "Error del servidor", "error");
        },
    });
});

$(document).ready(function () {
    cargarNotificaciones();
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

                                ${USER_ROLE === "Administrador/a" ? `
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-sm text-danger btn-Eliminar" data-id="${n.id_notificacion}">
                                            <i class="fa-regular fa-trash-can"></i> Eliminar
                                        </button>
                                    </div>
                                ` : ''}

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
