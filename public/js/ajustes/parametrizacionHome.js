$(document).ready(function () {
    cargarSubvistaParametros($(".btnParametro.active").first());
    $(document).on("click", ".btnParametro", function () {
        $(".btnParametro").removeClass("active");
        $(this).addClass("active");
        cargarSubvistaParametros($(this));
    });

});


function cargarSubvistaParametros(btn) {

    const url = btn.data("url");
    const vista = btn.data("vista");

    $(".renderDivParametros").html(`
        <div class="d-flex justify-content-center align-items-center py-5">
            <div class="spinner-border spinner-border-sm" 
                 style="color: var(--color-default);" 
                 role="status">
            </div>
        </div>
    `);

    $.ajax({
        url: url,
        type: "GET",

        success: function (html) {

            $(".renderDivParametros").html(html);

            inicializarSubvistaParametros(vista);
        },

        error: function () {

            $(".renderDivParametros").html(`
                <div class="alert alert-danger">
                    No se pudo cargar la configuración seleccionada.
                </div>
            `);

        }
    });
}

function inicializarSubvistaParametros(vista) {

    switch (vista) {

        case "areasServicios":
            cargarTablaAreas();
            cargarSelectorNovedadesFuncion();
            break;

        case "categorias":
            cargarTablaCategorias();
            break;

        case "regimenes":
            cargarTablaRegimenes();
            break;

    }
}