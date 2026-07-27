$(document).ready(function () {
    $('.renderBodyAnalisis').on('change', '#mesPresupEjecutado', function () {
        const mes = $(this).val();

        $('.renderBodyAnalisis').html(
            '<div class="text-center p-4"><i class="fa-solid fa-spinner fa-spin"></i> Cargando...</div>'
        );

        $.get(PRESUP_EJECUTADO_ROUTES.view, { mes: mes }, function (html) {
            $('.renderBodyAnalisis').html(html);
        });
    });
});