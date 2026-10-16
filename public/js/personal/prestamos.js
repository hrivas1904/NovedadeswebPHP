//Prestamos
function modalRegistrarPrestamo(idColab, nombreColab) {
    const modalPrestamo = $("#modalPrestamo");

    $("#formPrestamo")[0].reset();

    const fechaActual = new Date();
    const fechaLocal = [
        fechaActual.getFullYear(),
        String(fechaActual.getMonth() + 1).padStart(2, "0"),
        String(fechaActual.getDate()).padStart(2, "0"),
    ].join("-");

    $("#inputFechaPrest").val(fechaLocal);
    $("#inputLegajoPrest").val(idColab);
    $("#inputColaboradorPrest").val(nombreColab);
    $("#inputMesDescuentoPrest").val(fechaLocal.substring(0, 7));

    $("#tbDetallePrestamo tbody").empty();

    modalPrestamo.modal("show");
}

$("#inputMontoPrest, #inputCuotasPrest").on("input change", function () {
    const montoTotalPrestamo = parseFloat($("#inputMontoPrest").val());
    const cantidadCuotasPrestamo = parseInt($("#inputCuotasPrest").val(), 10);

    if (
        isNaN(montoTotalPrestamo) ||
        isNaN(cantidadCuotasPrestamo) ||
        montoTotalPrestamo <= 0 ||
        cantidadCuotasPrestamo <= 0
    ) {
        $("#inputMontoCuotasPrest").val("");
        return;
    }

    const montoCuota = montoTotalPrestamo / cantidadCuotasPrestamo;

    $("#inputMontoCuotasPrest").val(montoCuota.toFixed(2));
});

$(document).on(
    "input change",
    "#inputMontoPrest, #inputCuotasPrest, #inputMesDescuentoPrest",
    function () {
        generarDetalleNovedadesAut();
    },
);

$(document).ready(function () {
    tablaDetallePrest = $("#tbDetallePrestamo").DataTable({
        data: [],
        paging: false,
        searching: false,
        ordering: false,
        info: false,
        autoWidth: false,
        language: {
            url: "/js/es-ES.json",
        },
        dom: "t",
        columns: [
            { data: "novedad" },
            { data: "codigo" },
            { data: "fecha" },
            { data: "detalle" },
            {
                data: "importe",
                className: "text-end",
                render: function (data, type) {
                    if (type !== "display") return data;

                    return Number(data).toLocaleString("es-AR", {
                        style: "currency",
                        currency: "ARS",
                    });
                },
            },
        ],
    });
});

function generarDetalleNovedadesAut() {
    const montoTotal = Number($("#inputMontoPrest").val());
    const cantidadCuotas = Number($("#inputCuotasPrest").val());
    const inicioDescuento = $("#inputMesDescuentoPrest").val();

    tablaDetallePrest.clear();

    if (
        !Number.isFinite(montoTotal) ||
        montoTotal <= 0 ||
        !Number.isInteger(cantidadCuotas) ||
        cantidadCuotas <= 0 ||
        !inicioDescuento
    ) {
        tablaDetallePrest.draw();
        return;
    }

    const totalCentavos = Math.round(montoTotal * 100);

    if (totalCentavos < cantidadCuotas) {
        tablaDetallePrest.draw();
        return;
    }

    const cuotaBase = Math.floor(totalCentavos / cantidadCuotas);
    const diferencia = totalCentavos - cuotaBase * cantidadCuotas;

    const [anio, mes] = inicioDescuento.substring(0, 7).split("-").map(Number);

    if (!anio || mes < 1 || mes > 12) {
        tablaDetallePrest.draw();
        return;
    }

    const novedades = [];

    for (let i = 0; i < cantidadCuotas; i++) {
        const fecha = new Date(anio, mes - 1 + i, 1);

        const fechaAplicacion = fecha.toLocaleDateString("es-AR");

        const importeCentavos =
            cuotaBase + (i === cantidadCuotas - 1 ? diferencia : 0);

        novedades.push({
            novedad: "Préstamo",
            codigo: "HP3C_NOV_0023",
            fecha: fechaAplicacion,
            detalle: `Cuota ${i + 1}/${cantidadCuotas}`,
            importe: importeCentavos / 100,
        });
    }

    tablaDetallePrest.rows.add(novedades).draw();
}