function agregarFormNuevaCompetencia(divCompetencias, tipo) {
    const html = `
        <div class="col-12 div-competencia">
            <div class="row align-items-end g-2">
                <input type="hidden" name="competenciaTipo[]" value="${tipo}">
                <div class="col-11">
                    <label class="label-control text-muted">Competencia</label>
                    <input type="text" name="competenciaNombre[]" class="form-control" maxlength="512">
                </div>
                <div class="col-1">
                    <button type="button" class="btn btn-outline-danger btn-quitar-competencia">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </div>
                <div class="col-12">
                    <textarea name="competenciaDescripcion[]" class="form-control" rows="2"
                              placeholder="Descripción de la competencia"></textarea>
                </div>
            </div>
        </div>
    `;
    divCompetencias.append(html);
}

$(document).on('show.bs.modal', '#modalAltaDescriptivo', function () {
    if ($('#divCompetenciasGenericas').children().length === 0) {
        agregarFormNuevaCompetencia($('#divCompetenciasGenericas'), 'ORGANIZACIONAL');
    }
    if ($('#divCompetenciasEspecificas').children().length === 0) {
        agregarFormNuevaCompetencia($('#divCompetenciasEspecificas'), 'FUNCIONAL');
    }
});

$(document).on('click', '.btn-agregar-competencia', function () {
    agregarFormNuevaCompetencia($($(this).data('destino')), $(this).data('tipo'));
});

$(document).on('click', '.btn-quitar-competencia', function () {
    $(this).closest('.div-competencia').remove();
});