let tablaMedicos = null;

function formatFechaHora(fecha) {
<<<<<<< HEAD
    if (!fecha) return '';
    const match = fecha.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2}):(\d{2})/);
=======
    if (!fecha) return "";
    const match = fecha.match(
        /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2}):(\d{2})/,
    );
>>>>>>> f884a2ed0480ebc85db2f0a15e7fa104e935f0a2
    if (!match) return fecha;
    const [, anio, mes, dia, hora, min, seg] = match;
    return `${dia}/${mes}/${anio} ${hora}:${min}:${seg}`;
}

<<<<<<< HEAD
=======
$(document).ready(function () {
    obtenerServiciosMedicos("selectServicioAltaMedico");
    obtenerServiciosMedicos("servicioMedico");
});

>>>>>>> f884a2ed0480ebc85db2f0a15e7fa104e935f0a2
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
<<<<<<< HEAD
            { data: "telefono", className: "text-start" },
=======
            { data: "correo", className: "text-start" },
>>>>>>> f884a2ed0480ebc85db2f0a15e7fa104e935f0a2
        ],
        language: {
            url: "/js/es-ES.json",
        },
        paging: false,
        scrollX: false,
        scrollY: "58vh",
        scrollCollapse: true,
        autoWidth: true,
        dom: "tir",
    });

    $("#tbMedicos").on("click", "tbody tr", function () {
        const id = tablaMedicos.row(this).data().id;
        verDetalleMedico(id);
    });
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

    $.ajax({
        url: "/rrhh/medicos/registrarNuevoMedico",
        type: "PUT",
        data: $(this).serialize(),
        success: function (response) {
            $("#formNuevoMedico")[0].reset();
            $("#modalNuevoMedico").modal("hide");
            tablaMedicos.ajax.reload(null, false);
        },
        error: function (xhr) {
            console.error(xhr);
        },
    });
});
