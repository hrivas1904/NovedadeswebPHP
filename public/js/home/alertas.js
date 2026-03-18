$(document).on("keydown", function (e) {

    if (e.key === "Escape") {
        $("#dropdownAlertas").hide();
    }

});

$(document).on("click", function (e) {

    if (!$(e.target).closest("#btnAlertas, #dropdownAlertas").length) {
        $("#dropdownAlertas").hide();
    }

});

function cargarAlertas() {

    $.ajax({
        url: "/alertas/listar",
        type: "GET",
        dataType: "json",

        success: function (resp) {

            console.log("RESP ALERTAS:", resp);
            let lista = Array.isArray(resp) ? resp : (resp.data ?? []);
            console.log("CANTIDAD:", lista.length);

            const $badge = $("#contadorAlertas");
            $badge.text(lista.length);

            if (lista.length === 0) $badge.hide(); else $badge.show();

            let html = "";
            if (lista.length === 0) {
                html = `<li class="text-muted p-2">Sin notificaciones</li>`;
            } else {
                lista.forEach(a => {
                    html += `
                        <li class="alerta-item p-2 border-bottom"
                            data-id="${a.id}"
                            data-modulo="${a.modulo}"
                            data-ref="${a.idReferencia}"
                            data-url="${a.url}">
                            ${a.mensaje}
                        </li>
                    `;
                });
            }

            $("#listaAlertas").html(html);
        },

        error: function (xhr) {
            console.error("ERROR ALERTAS:", xhr.status, xhr.responseText);
        }
    });

}

$("#btnAlertas").click(function () {
    $("#dropdownAlertas").toggle();
});

$(document).on("click", "#listaAlertas li", function () {
    let id = $(this).data("id");

    $.post("/alertas/leida", {
        id: id,
        _token: $('meta[name="csrf-token"]').attr("content"),
    });

    $(this).remove();
});

setInterval(cargarAlertas, 5000);

$(document).on("click", ".alerta-item", function () {

    let idAlerta = $(this).data("id");
    let url = $(this).data("url");

    // Marcar como leída
    $.ajax({
        url: "/alertas/marcar-leida",
        type: "POST",
        data: { id: idAlerta },
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
        },
        complete: function () {

            // UI
            $(`[data-id="${idAlerta}"]`).remove();

            let contador = parseInt($("#contadorAlertas").text());
            if (contador > 0) {
                $("#contadorAlertas").text(contador - 1);
            }

            // 🔥 REDIRECCIÓN
            if (url) {
                window.location.href = url;
            }
        }
    });

});
