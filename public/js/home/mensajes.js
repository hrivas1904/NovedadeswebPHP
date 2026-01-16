$("#btnRedactarComunicado").click(function () {
    let contenido = $("#txtNotificacion").val().trim();

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
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (data) {
            if (data.success) {
                Swal.fire({
                    title: data.mensaje,
                    icon: "success",
                });

                $("#txtNotificacion").val("");
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
        url: '/notificaciones/lista',
        method: "GET",
        success: function (response) {

            const contenedor = $("#listaNotificaciones");
            contenedor.html("");
            let botones="";

            if (!response || response.length === 0) {

                contenedor.html("<h6>Sin notificaciones...</h6>");

            } else {

                response.forEach((n) => {

                    if (n.estado) {
                    }

                    if (
                            USER_ROLE === "Administrador/a"
                        ) {
                            botones = `
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn">
                                <i class="fa-regular fa-pen-to-square"></i> Editar
                                </button>

                                <button type="button" class="btn">
                                <i class="fa-regular fa-eye"></i> Mostrar
                                </button>
                            </div>
                        `;
                        }

                    contenedor.append(`
                        <div class="border-bottom pb-2 mb-3">
                             <small class="text-muted">Publicado por <strong>${n.name}</strong></small>
                            <small class="text-muted float-end">${n.fecha_publicacion}</small>
                            <h5 style="color: #1e293b;">${n.contenido}</h5>                            
                            ${botones}                                                        
                        </div>
                    `);
                });
            }
        },
        error: function () {
            console.log("Error cargando notificaciones");
        }
    });
}