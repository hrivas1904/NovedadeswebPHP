$(document).ready(function () {
    $('.renderBodyAnalisis').on('change', '#selectAnioResumen', function () {
        const anio = $(this).val();

        $('.renderBodyAnalisis').html(
            '<div class="text-center p-4"><i class="fa-solid fa-spinner fa-spin"></i> Cargando...</div>'
        );

        $.get(RESUMEN_ANUAL_ROUTES.view, { anio: anio }, function (html) {
            $('.renderBodyAnalisis').html(html);
        });
    });
});