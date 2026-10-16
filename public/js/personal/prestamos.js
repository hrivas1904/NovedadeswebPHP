function convertirMontoPrestamo(valor) {
    let monto = String(valor).trim().replace(/\s/g, "").replace(/\$/g, "");

    if (monto.includes(",") && monto.includes(".")) {
        if (monto.lastIndexOf(",") > monto.lastIndexOf(".")) {
            monto = monto.replace(/\./g, "").replace(",", ".");
        } else {
            monto = monto.replace(/,/g, "");
        }
    } else if (monto.includes(",")) {
        monto = monto.replace(",", ".");
    } else if (/^\d{1,3}(\.\d{3})+$/.test(monto)) {
        monto = monto.replace(/\./g, "");
    }

    return monto === "" ? NaN : Number(monto);
}

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

$(document).on("submit", "#formPrestamo", function (event) {
    event.preventDefault();
    event.stopPropagation();

    const legajo = $("#inputLegajoPrest").val();
    const monto = convertirMontoPrestamo($("#inputMontoPrest").val());
    const cuotas = parseInt($("#inputCuotasPrest").val(), 10);
    const mesDescuento = $("#inputMesDescuentoPrest").val();

    // VALIDACIONES
    if (
        !legajo ||
        !Number.isFinite(monto) ||
        monto <= 0 ||
        !Number.isInteger(cuotas) ||
        cuotas <= 0 ||
        !mesDescuento
    ) {
        Swal.fire({
            icon: "warning",
            title: "Datos incompletos",
            text: "Complete correctamente todos los campos del préstamo.",
            timer: 2000,
            showConfirmButton: false,
        });
        return;
    }

    const totalCentavos = Math.round(monto * 100);

    if (totalCentavos < cuotas) {
        Swal.fire({
            icon: "warning",
            title: "Importe inválido",
            text: "El monto del préstamo no permite generar todas las cuotas con un importe positivo.",
            timer: 2000,
            showConfirmButton: false,
        });
        return;
    }

    const fechaInicio = `${mesDescuento}-01`;

    const btnRegistrar = $("#btnRegistrarPrestamo");

    // EVITAR DOBLE ENVÍO
    if (btnRegistrar.prop("disabled")) return;

    btnRegistrar.prop("disabled", true).html(`
        <span class="spinner-border spinner-border-sm me-2"></span>
        Registrando...
    `);

    $.ajax({
        url: "/rrhh/registrarPrestamo",
        type: "POST",
        dataType: "json",
        data: {
            _token: $('meta[name="csrf-token"]').attr("content"),
            legajo: legajo,
            monto: monto.toFixed(2),
            cuotas: cuotas,
            fecha_inicio: fechaInicio,
        },
        success: function (response) {
            if (response.success) {
                $("#modalPrestamo").modal("hide");

                $("#formPrestamo")[0].reset();

                if ($.fn.DataTable.isDataTable("#tbDetallePrestamo")) {
                    $("#tbDetallePrestamo").DataTable().clear().draw();
                }

                Swal.fire({
                    icon: "success",
                    title: "Préstamo registrado",
                    text:
                        response.mensaje ||
                        "El préstamo y sus novedades fueron registrados correctamente.",
                    showConfirmButton: false,
                    timer: 2000,
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "No se pudo registrar",
                    text:
                        response.mensaje ||
                        "Ocurrió un error al registrar el préstamo.",
                    timer: 2000,
                    showConfirmButton: false,
                });
            }
        },
        error: function (xhr) {
            console.error(
                "Error al registrar préstamo:",
                xhr.responseJSON || xhr.responseText,
            );

            let mensaje = "Ocurrió un error al registrar el préstamo.";

            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                mensaje = Object.values(xhr.responseJSON.errors)
                    .flat()
                    .join("\n");
            } else if (xhr.responseJSON?.mensaje) {
                mensaje = xhr.responseJSON.mensaje;
            } else if (xhr.responseJSON?.message) {
                mensaje = xhr.responseJSON.message;
            }

            Swal.fire({
                icon: "error",
                title: "Error",
                text: mensaje,
                timer: 2000,
                showConfirmButton: false,
            });
        },
        complete: function () {
            btnRegistrar.prop("disabled", false).html("Registrar préstamo");
        },
    });
});
