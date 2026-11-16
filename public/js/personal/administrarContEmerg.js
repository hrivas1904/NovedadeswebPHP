let html = "";

function agregarFormNuevoContacto(divContacto) {
    html = `
        <div class="col-12 col-xl-6 contacto-emergencia">
            <div class="row align-items-end g-3">
                <div class="col-12">
                    <label class="label-control text-muted">Nombre</label>
                    <input class="form-control" name="nombreEmergencia[]" type="text">
                </div>
                <div class="col-12 col-xl-6">
                    <label class="label-control text-muted">Teléfono</label>
                    <input class="form-control" name="telefonoEmergencia[]" type="text">
                </div>
                <div class="col-11 col-xl-5">
                    <label class="label-control text-muted">Parentesco</label>
                    <input class="form-control" name="parentescoEmergencia[]" type="text">
                </div>
                <div class="col-1">
                    <button type="button" class="btn btn-outline-danger btn-eliminar-div">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </div>
            </div>
        </div>
    `;

    divContacto.append(html);
}

$("#divContactosEmergencia").on("click", ".btn-eliminar-div", function () {
    $(this).closest(".contacto-emergencia").remove();
});
