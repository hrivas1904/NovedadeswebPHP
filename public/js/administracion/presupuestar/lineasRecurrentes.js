function poblarSelectConceptos() {
    let opts = "";
    CONCEPTOS_CATALOGO.forEach(
        (c) => (opts += '<option value="' + c + '">' + c + "</option>"),
    );
    $("#selConceptoRecurrente").html(opts).val("SUELDOS");
}

function renderRecurrentes(rows) {
    if (!rows.length) {
        $("#wrapperRecurrentes").hide();
        $("#msgSinRecurrentes").show();
        return;
    }
    let html = "";
    rows.forEach(function (r) {
        const color = Number(r.importe) >= 0 ? "text-success" : "text-danger";
        html +=
            '<tr data-id="' +
            r.id +
            '">' +
            "<td>" +
            r.banco +
            "</td><td>" +
            r.concepto +
            "</td><td>" +
            r.detalle +
            "</td>" +
            '<td class="text-end fw-bold ' +
            color +
            '">' +
            fmtPesos(r.importe) +
            "</td>" +
            '<td class="text-center"><button class="btn btn-sm btn-danger btn-eliminar-recurrente" data-id="' +
            r.id +
            '"><i class="fa-solid fa-trash"></i></button></td>' +
            "</tr>";
    });
    $("#bodyRecurrentes").html(html);
    $("#cantidadRecurrentes").text(rows.length);
    $("#wrapperRecurrentes").show();
    $("#msgSinRecurrentes").hide();
}

function cargarRecurrentes() {
    $.get(PRESUPUESTAR_ROUTES.recurrentesListar, function (rows) {
        renderRecurrentes(rows);
    });
}

$("#btnAgregarRowRecurrente").on("click", function () {
    const detalle = $("#inputDetalleRecurrente").val().trim();
    const importeRaw = $("#inputImporteRecurrente").val().trim();

    if (!detalle || !importeRaw) return;

    $.post(
        PRESUPUESTAR_ROUTES.recurrentesGuardar,
        {
            banco: $("#selBancoRecurrente").val(),
            seccion: $("#selSeccionRecurrente").val(),
            concepto: $("#selConceptoRecurrente").val(),
            subconcepto: $("#inputSubconceptoRecurrente").val(),
            detalle: detalle,
            importe: importeRaw.replace(/\./g, "").replace(",", "."),
        }
    )
    .done(function () {
        $(
            "#inputSubconceptoRecurrente, #inputDetalleRecurrente, #inputImporteRecurrente"
        ).val("");

        cargarRecurrentes();

        Swal.fire({
            icon: "success",
            title: "¡Operación exitosa!",
            text: "Línea recurrente agregada correctamente!",
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false,
        });
    })
    .fail(function () {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "No se pudo agregar la línea recurrente.",
        });
    });
});

$(document).on("click", ".btn-eliminar-recurrente", function () {
    const id = $(this).data("id");

    $.ajax({
        url: PRESUPUESTAR_ROUTES.recurrentesEliminar.replace(":id", id),
        type: "DELETE",
        success: function () {
            cargarRecurrentes();

            Swal.fire({
                icon: "success",
                title: "¡Operación exitosa!",
                text: "Línea recurrente eliminada correctamente.",
                timer: 1500,
                timerProgressBar: true,
                showConfirmButton: false,
            });
        },
        error: function () {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "No se pudo eliminar la línea recurrente.",
            });
        },
    });
});

$("#btnAplicarRecurrentes").on("click", function () {
    const mes = $("#inputMesAplicarRecurrentes").val();
    if (!mes) return;
    $.post(
        PRESUPUESTAR_ROUTES.recurrentesAplicar,
        { mes: mes },
        function (data) {
            let msg = data.insertados + " líneas aplicadas al mes " + mes + ".";
            if (data.omitidas > 0) {
                msg +=
                    " (" +
                    data.omitidas +
                    " ya estaban aplicadas y se omitieron.)";
            }
            alert(msg);
        },
    );
});

$(document).ready(function () {
    poblarSelectConceptos();
    $("#inputMesAplicarRecurrentes").val(new Date().toISOString().slice(0, 7));
    cargarRecurrentes();
});
