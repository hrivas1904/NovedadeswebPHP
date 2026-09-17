let tablaMedicos = null;

function formatFechaHora(fecha) {
    if (!fecha) return "";
    const match = fecha.match(
        /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2}):(\d{2})/,
    );

    if (!match) return fecha;
    const [, anio, mes, dia, hora, min, seg] = match;
    return `${dia}/${mes}/${anio} ${hora}:${min}:${seg}`;
}

$(document).ready(function () {
    obtenerServiciosMedicos("selectServicioAltaMedico");
    obtenerServiciosMedicos("servicioMedico");
});

$(document).ready(function () {
    tablaMedicos = new DataTable("#tbMedicos", {
        ajax: {
            url: "/rrhh/medicos/obtenerMedicos",
            type: "GET",
            dataSrc: "",
        },
        columns: [
            { data: "id", className: "text-start" },
            { data: "medico", className: "text-start" },
            { data: "matricula", className: "text-start" },
            { data: "dni", className: "text-start" },
            { data: "servicio", className: "text-start" },
            { data: "correo", className: "text-start" },
        ],
        language: {
            url: "/js/es-ES.json",
        },
        paging: false,
        scrollX: false,
        scrollY: "68vh",
        scrollCollapse: true,
        autoWidth: true,
        dom: "tir",
    });

    $("#tbMedicos").on("click", "tbody tr", function () {
        const id = tablaMedicos.row(this).data().id;
        verDetalleMedico(id);
    });

    $("#buscadorMedicos").on("keyup", function () {
        tablaMedicos.search(this.value).draw();
    });
});

$("#btnLimpiarBuscadorMedico").on("click", function () {
    $("#buscadorMedicos").val("");
    tablaMedicos.search(this.value).draw();
});

function verDetalleMedico(idMedico) {
    $.ajax({
        url: `/rrhh/medicos/obtenerLegajo/${idMedico}`,
        type: "GET",
        dataType: "json",
        success: function (medico) {
            $("#idMedico").val(medico.id);
            $("#nombreMedico").val(medico.nombre);
            $("#cuitMedico").val(medico.dni);
            $("#matriculaMedico").val(medico.matricula);
            $("#domicilioMedico").val(medico.domicilio);
            $("#correoMedico").val(medico.correo);
            $("#telefonoMedico").val(medico.telefono);
            $("#servicioMedico").val(medico.servicio);
            $("#razonSocialMedico").val(medico.razonSocial ?? "");
            $("#fechaAltaMedico").val(formatFechaHora(medico.created_at ?? ""));
            $("#lblNombreMedico").text(medico.nombre.toUpperCase());
            $("#modalDetalleMedico").modal("show");
        },
    });
}

function obtenerServiciosMedicos(idSelectServicios) {
    $.ajax({
        url: "/rrhh/medicos/obtenerServicios",
        type: "GET",
        dataType: "json",
        success: function (servicios) {
            const selector = $(`#${idSelectServicios}`);
            servicios.forEach(function (servicio) {
                selector.append(
                    $("<option>", {
                        value: servicio.servicio,
                        text: servicio.servicio,
                    }),
                );
            });
        },
    });
}

$("#formNuevoMedico").on("submit", function (e) {
    e.preventDefault();

    const telefono=$("#inpTelefono").val();

    if (!$("#inpMedico").val()) {
        Swal.fire({
            title: "Atención!",
            text: "El campo nombre es obligatorio.",
            icon: "warning",
            timer: 2000,
            showConfirmButton: false,
        });
    }

    if (!$("#inpCuit").val()) {
        Swal.fire({
            title: "Atención!",
            text: "El campo cuit es obligatorio.",
            icon: "warning",
            timer: 2000,
            showConfirmButton: false,   
        });
    }

    if (!$("#selectServicioAltaMedico").val()) {
        Swal.fire({
            title: "Atención!",
            text: "El campo servicio es obligatorio.",
            icon: "warning",
            timer: 2000,
            showConfirmButton: false,
        });
    }

    if (telefono && !/^\d+$/.test(telefono)) {
        Swal.fire({
            icon: "warning",
            title: "Atención!",
            text: "El teléfono solo puede contener números.",
            timer: 2000,
            showConfirmButton: false,
        });
        return;
    }

    $.ajax({
        url: "/rrhh/medicos/registrarNuevoMedico",
        type: "PUT",
        data: $(this).serialize(),
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            $("#formNuevoMedico")[0].reset();
            $("#modalNuevoMedico").modal("hide");
            tablaMedicos.ajax.reload(null, false);

            Swal.fire({
                title: "Operación exitosa!",
                text: "Médico registrado correctamente",
                icon: "success",
                timer: 1200,
                showConfirmButton: false,
            });
        },
        error: function (xhr) {
            console.error(xhr);
        },
    });
});
