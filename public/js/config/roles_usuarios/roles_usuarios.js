$(document).ready(function () {
    cargarRoles();
});

function cargarRoles() {
    $.ajax({
        url: "/configuracion/roles/lista",
        type: "GET",
        dataType: "json",
        success: function (response) {
            let html = "";

            response.roles.forEach(function (rol) {
                html += `
                    <div class="card mb-2 shadow-sm border-0 btnRol role-card"
                        data-id="${rol.id}"
                        data-nombre="${rol.rol.toLowerCase()}"
                        style="cursor:pointer;">

                        <div class="card-body py-2">
                            <h6 class="mb-1 fw-bold">${rol.rol}</h6>
                        </div>

                    </div>
                `;
            });

            $(".renderListaRoles").html(html);
        },
        error: function () {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "No fue posible cargar los roles.",
            });
        },
    });
}

$(document).on("click", ".btnRol", function () {
    const rolId = $(this).data("id");
    const nombreRol = $(this).data("nombre");

    $(".btnRol").removeClass("active");
    $(this).addClass("active");

    $("#nombreRolSeleccionado").text(nombreRol.toUpperCase());

    cargarPermisos(rolId, "rrhh", "#renderPermisosRecursosHumanos");
    cargarPermisos(rolId, "calidad", "#renderPermisosCalidad");
    cargarPermisos(rolId, "administracion", "#renderPermisosAdministracion");
});

$("#txtBuscarRol").on("keyup", function () {
    const texto = $(this).val().toLowerCase().trim();
    $(".role-card").each(function () {
        const nombre = $(this).data("nombre");
        if (nombre.includes(texto)) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
});

$("#btnLimpiarBusqueda").click(function () {
    $("#txtBuscarRol").val("").trigger("keyup");
});

function cargarPermisos(rolId, modulo, render) {
    $.ajax({
        url: `/configuracion/roles/${rolId}/permisos/${modulo}`,
        type: "GET",
        dataType: "json",

        success: function (response) {
            let html = "";

            if (response.permisos.length === 0) {
                html = `
                    <span class="text-muted">
                        No posee permisos.
                    </span>
                `;
            } else {
                response.permisos.forEach(function (permiso) {
                    html += `
                        <div class="form-check mb-2">

                            <input
                                class="form-check-input checkPermiso"
                                type="checkbox"
                                value="${permiso.id}"
                                data-id="${permiso.id}"
                                ${permiso.asignado ? "checked" : ""}>

                            <label class="form-check-label">
                                ${permiso.permiso}
                            </label>

                        </div>
                    `;
                });
            }

            $(render).html(html);
        },

        error: function () {
            $(render).html(`
                <div class="alert alert-danger mb-0">
                    Error al cargar permisos.
                </div>
            `);
        },
    });
}
