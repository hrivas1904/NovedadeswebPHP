$(document).on('change', '#selectorTipoVinculo', function () {
    const tipo = $(this).val();
    const esDep = tipo === 'DEPENDENCIA';
    const esExt = tipo !== '' && !esDep;

    $('#divDependencia').toggleClass('d-none', !esDep)
        .find('input, select').prop('disabled', !esDep);
    $('#divExternos').toggleClass('d-none', !esExt)
        .find('input, select').prop('disabled', !esExt);
});