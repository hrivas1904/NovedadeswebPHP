let tablaMedicos = null;

function formatFechaHora(fecha) {
    if (!fecha) return '';
    const match = fecha.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2}):(\d{2})/);
    if (!match) return fecha;
    const [, anio, mes, dia, hora, min, seg] = match;
    return `${dia}/${mes}/${anio} ${hora}:${min}:${seg}`;
}

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
            { data: "telefono", className: "text-start" },
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
        url: `/rrhh/medicos/verLegajo/${idMedico}`,
        type: "GET",
        dataType: "json",
        success: function (medico) {
            $("#inputIdMedico").val(medico.id ?? '');
            $("#inputNombreMedico").val(medico.nombre ?? '');
            $("#inputApellidoMedico").val(medico.apellido ?? '');
            $("#inputCuitMedico").val(medico.dni ?? '');
            $("#inputMpMedico").val(medico.matricula ?? '');
            $("#inputDomicilioMedico").val(medico.domicilio ?? '');
            $("#inputCorreoMedico").val(medico.correo ?? '');
            $("#inputTelefonoMedico").val(medico.telefono ?? '');
            $("#inputServicioMedico").val(medico.servicio ?? '');
            $("#inputRazonMedico").val(medico.razonSocial ?? '');
            $("#inputFechaAlta").val(formatFechaHora(medico.created_at ?? ''));
            $("#lblNombreMedico").text((medico.apellido+' '+medico.nombre).toUpperCase());
            $("#modalDetalleMedico").modal("show");
        },
        error: function (xhr, status, error) {
            console.error("Error al traer datos del legajo");
        },
    });
}

