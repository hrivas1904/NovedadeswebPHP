$(document).ready(function () {
    $(".btn-analisis").on("click", function () {
        const $btn = $(this);
        const url = $btn.data("url");

        $(".btn-analisis").removeClass("active");
        $btn.addClass("active");

        cargarSubVista(url);
    });

    cargarSubVista($(".btn-analisis.active").data("url"));
});

function cargarSubVista(url) {
    $(".renderBodyImportacion").html(
        '<div class="text-center p-4"><i class="fa-solid fa-spinner fa-spin"></i> Cargando...</div>',
    );

    $.get(url)
        .done(function (html) {
            $(".renderBodyImportacion").html(html);
            inicializarComponentes();
        })
        .fail(function () {
            $(".renderBodyImportacion").html(
                '<div class="alert alert-danger">Error al cargar la información.</div>',
            );
        });
}
