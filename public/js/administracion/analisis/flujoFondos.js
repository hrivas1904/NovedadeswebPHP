$(document).ready(function () {
    $(".renderBodyAnalisis").on("change", "#mesFlujoFondos", function () {
        const mes = $(this).val();

        $(".renderBodyAnalisis").html(
            '<div class="text-center p-4"><i class="fa-solid fa-spinner fa-spin"></i> Cargando...</div>',
        );

        $.get(FLUJO_FONDOS_ROUTES.view, { mes: mes }, function (html) {
            $(".renderBodyAnalisis").html(html);
        });
    });
});

$(document).on(
    "click",
    ".renderBodyAnalisis .fila-detalle-concepto",
    function () {
        const concepto = $(this).data("concepto");
        const mes = $("#cardFlujoFondos").data("mes");

        $("#nombreConcepto").text(concepto);
        $("#mesVista").text(mes);

        $("#tbDetalleMovimientosConcepto tbody").remove();
        $("#tbDetalleMovimientosConcepto").append(
            '<tbody><tr><td colspan="4" class="text-center"><i class="fa-solid fa-spinner fa-spin"></i></td></tr></tbody>',
        );

        const modal = new bootstrap.Modal(
            document.getElementById("modalDetalleMovimientos"),
        );
        modal.show();

        $.get(
            FLUJO_FONDOS_ROUTES.detalle,
            { concepto: concepto, periodo: mes },
            function (rows) {
                let html = "";
                let total = 0;

                if (!rows.length) {
                    html =
                        '<tr><td colspan="4" class="text-center text-muted">Sin movimientos.</td></tr>';
                } else {
                    rows.forEach(function (r) {
                        total += Number(r.importe);
                        html +=
                            "<tr>" +
                            "<td>" +
                            r.fecha +
                            "</td>" +
                            "<td>" +
                            (r.subconcepto || "") +
                            "</td>" +
                            "<td>" +
                            (r.detalle || "") +
                            "</td>" +
                            '<td class="text-end' +
                            (r.importe < 0 ? " text-danger" : "") +
                            '">$ ' +
                            Number(r.importe).toLocaleString("es-AR", {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2,
                            }) +
                            "</td>" +
                            "</tr>";
                    });
                }

                $("#tbDetalleMovimientosConcepto tbody").replaceWith(
                    "<tbody>" + html + "</tbody>",
                );
                $("#totalDetalleMovimientos")
                    .text(
                        "$ " +
                            total.toLocaleString("es-AR", {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2,
                            }),
                    )
                    .css("color", total < 0 ? "#dc3545" : "inherit");
            },
        );
    },
);
