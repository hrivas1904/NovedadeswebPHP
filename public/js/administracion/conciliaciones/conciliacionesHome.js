$(document).ready(function() {
    $('.btn-analisis').on('click', function() {
        const $btn = $(this);
        const url = $btn.data('url');

        $('.btn-analisis').removeClass('active');
        $btn.addClass('active');

        cargarSubVista(url);
    });

    cargarSubVista($('.btn-analisis.active').data('url'));
});

function cargarSubVista(url) {
    $('.renderBodyAnalisis').html('<div class="text-center p-4"><i class="fa-solid fa-spinner fa-spin"></i> Cargando...</div>');

    $.get(url)
        .done(function(html) {
            $('.renderBodyAnalisis').html(html);
            $('.renderBodyAnalisis').trigger('subvista:cargada', [url]);
        })
        .fail(function() {
            $('.renderBodyAnalisis').html('<div class="alert alert-danger">Error al cargar la información.</div>');
        });
}

function parsearMontoFlexible(raw) {
    let s = String(raw || '').trim().replace(/\$|\s/g, '');
    if (s === '') return 0;
 
    const tienePunto = s.includes('.');
    const tieneComa = s.includes(',');
 
    if (tienePunto && tieneComa) {
        s = s.replace(/\./g, '').replace(',', '.');
    } else if (tieneComa) {
        s = s.replace(',', '.');
    }
    // si solo tiene punto, ya esta en formato valido para Number()
 
    const n = parseFloat(s);
    return isNaN(n) ? 0 : n;
}