function agregarFormNuevoFamiliar(divFamiliares) {
    html = `
        <div class="col-12 col-xl-6 div-familiares">
            <div class="row align-items-end g-3">
                <div class="col-12 col-xl-5">
                    <label class="form-label">Nombre y apellido</label>
                    <input type="text" name="familiarNombrePersonal[]" class="form-control" required>
                </div>
                <div class="col-12 col-xl-4">
                    <label class="form-label">DNI</label>
                    <input type="text" name="familiarDniPersonal[]" class="form-control" inputmode="numeric" maxlength="8" pattern="\\d{7,8}">
                </div>
                <div class="col-12 col-xl-3">
                    <label class="form-label">Fecha nacimiento</label>
                    <input type="date" name="familiarNacimientoPersonal[]" class="form-control">
                </div>
                <div class=col-11 col-xl-5">
                    <label class="form-label">Parentesco</label>
                    <input type="date" name="familiarParentescoPersonal[]" class="form-control">
                </div>
                <div class="col-1">
                    <button type="button" class="btn btn-outline-danger btnQuitarHijoAlta">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </div>
            </div>
        </div>
    `;

    divFamiliares.append(html);
}

$("#divFamiliares").on("click", ".btnQuitarHijoAlta", function () {
    $(this).closest(".div-familiares").remove();
});
