let div;
let idHijo;
let nombre;
let dni;
let fechaNacimiento;
let legajo;

function cargarHijoColab(legajo) {
    $.ajax({
        url: "/rrhh/personal/listarFamiliares",
        type: "GET",
        data: { legajo: legajo },

        success: function (res) {
            if (res.success) {
                const familiares = res.data;              

                if (familiares && familiares.length > 0) {
                    $("#divHijosEdit").empty().removeAttr("hidden");
                    familiares.forEach((f) => {
                        const htmlFamiliar = `
                            <div class="col-lg-4 col-12">
                                <div class="card p-3 cardHijos">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-lg-8 col-12">
                                            <label class="form-label">Nombre</label>
                                            <input class="form-control inputNombre" value="${f.nombre}" readonly>
                                            <input type="hidden" class="form-control inputId" value="${f.id}" readonly>
                                            <input type="hidden" class="form-control inputLegajo" value="${f.legajo}" readonly>
                                        </div>
                                        <div class="col-lg-4 col-12">
                                            <label class="form-label">DNI</label>
                                            <input class="form-control inputDni" value="${f.dni}" readonly>
                                        </div>
                                        <div class="col-lg-4 col-12">
                                            <label class="form-label">Fecha nacimiento</label>
                                            <input type="date" class="form-control inputFechaNacimiento" value="${f.fechaNacimiento}" readonly>
                                        </div>
                                        <div class="col-lg-4 col-12">
                                            <label class="form-label">Parentesco</label>
                                            <input class="form-control" value="${f.parentesco}" readonly>
                                        </div>
                                        <div class="col-lg-4 col-12 justify-content-end d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-primary btn-edit-hijo">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-primary btn-update-hijo d-none">
                                                <i class="fa-regular fa-floppy-disk"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger btn-eliminar-hijo">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        $("#divHijosEdit").append(htmlFamiliar);
                    });
                }
            } else {
                Swal.fire({
                    title: "Error",
                    icon: "error",
                    text: res.message,
                    customClass: {
                        confirmButton: "btn btn-primary",
                    },
                });
            }
        },
        error: function () {
            Swal.fire({
                title: "Error",
                text: "No se pueden cargar los hijos del colaborador",
                icon: "error",
                customClass: {
                    confirmButton: "btn btn-primary",
                },
                buttonsStyling: false,
            });
        },
    });
}

$(document).on("click", ".btn-edit-hijo", function () {
    div = $(this).closest(".cardHijos");
    div.find(".inputNombre, .inputDni, .inputFechaNacimiento").prop("readonly",false);
    $(this).addClass("d-none");
    div.find(".btn-update-hijo").removeClass("d-none");
});

$(document).on("click", ".btn-update-hijo", function () {
    div=$(this).closest(".cardHijos");
    idHijo=div.find(".inputId").val();
    dni=div.find(".inputDni").val();
    fechaNacimiento=div.find(".inputFechaNacimiento").val();
    nombre=div.find(".inputNombre").val();
    legajo=div.find(".inputLegajo").val();

    if (!nombre || !dni || !fechaNacimiento) {
        Swal.fire({
            title: "Atención",
            icon: "warning",
            text: "Debe completar todos los campos.",
            customClass: {
                confirmButton: "btn btn-primary",
            },
        });
        return;
    };

    if (dni.length!=8){
        Swal.fire({
            title: "Atención",
            icon: "warning",
            text: "El DNI debe contener 8 dígitos.",
            customClass: {
                confirmButton: "btn btn-primary",
            },
        });
        return;
    };

    $.ajax({
        url: "/rrhh/personal/editarFamiliares",
        type: "GET",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            idHijo:idHijo,
            dni:dni,
            fechaNacimiento:fechaNacimiento,
            nombre:nombre,
            legajo:legajo
        },
        success: function (res) {
            if (res.success) {
                Swal.fire({
                    title: "Operación exitosa",
                    text: "Datos actualizados correctamente.",
                    icon: "success",
                    customClass: {
                        confirmButton: "btn btn-primary",
                    },
                    buttonsStyling: false,
                });

                div.find(".inputNombre, .inputDni, .inputFechaNacimiento").prop("readonly",true);
                div.find(".btn-update-hijo").addClass("d-none");
                div.find(".btn-edit-hijo").removeClass("d-none");
            }
            else {
                Swal.fire({
                    title: "Error",
                    text: "Error al actualizar datos.",
                    icon: "error",
                    customClass: {
                        confirmButton: "btn btn-primary",
                    },
                    buttonsStyling: false,
                });
            }
        },
        error: function () {
            Swal.fire({
                title: "Error",
                text: "Error del servidor.",
                icon: "error",
                customClass: {
                    confirmButton: "btn btn-primary",
                },
                buttonsStyling: false,
            });
        },
    });    
});

$(document).on("click",".btn-eliminar-hijo",function(){
    div=$(this).closest(".cardHijos");
    idHijo=div.find(".inputId").val();
    legajo=div.find(".inputLegajo").val();

    Swal.fire({
        title: "¿Quitar familiar?",
        text: "Esta acción no se puede deshacer",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        confirmButtonColor: "#00b18d",
        cancelButtonText: "Cancelar",
        cancelButtonColor: "#004a7c",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "/rrhh/personal/quitarFamiliares",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                data: { 
                    idHijo:idHijo,
                    legajo:legajo 
                },
                success: function (res) {
                    if (res.success) {
                        Swal.fire({
                            title: "Operación exitosa",
                            icon: "success",
                            text: "Información actualizada correctamente.",
                            customClass: {
                                confirmButton: "btn btn-primary",
                            },
                        });
                        div.closest(".col-lg-3").remove();
                        cargarHijoColab(legajo);
                    } else {
                        Swal.fire("Atención", res.mensaje, "warning");
                        Swal.fire({
                            title: "Error",
                            icon: "error",
                            text: "No se pudo actualizar la información.",
                            customClass: {
                                confirmButton: "btn btn-primary",
                            },
                        });
                    }
                },
            });
        }
    });
});

$("#btnAgregarHijoEdit").on("click", function (e) {
    e.preventDefault();
    e.stopPropagation();
    $("#divHijosEdit").removeAttr("hidden");

    const html = `
        <div class="row g-3 fila-hijo"> 
            <div class="col-lg-4 col-12">
                <label class="form-label">Nombre y Apellido</label>
                <input type="text" name="hijosEdit[0][nombre]" class="form-control inputNombreGuardar" required>
            </div>
            <div class="col-lg-2 col-12">
                <label class="form-label">DNI</label>
                <input type="number" name="hijosEdit[0][dni]" class="form-control inputDniGuardar" required>
            </div>
            <div class="col-lg-2 col-12">
                <label class="form-label">Fecha nacimiento</label>
                <input type="date" name="hijosEdit[0][fechaNacimiento]" class="form-control inputFechaNacGuardar" required>
            </div>
            <div class="col-lg-1 col-12 d-flex gap-2 align-items-end">
                <button type="button" class="btn btn-danger btnQuitarHijo">
                    <i class="fa-solid fa-trash"></i>
                </button>
                <button type="button" class="btn btn-primary btnGuardarHijo">
                    <i class="fa-regular fa-floppy-disk"></i>
                </button>
            </div>
        </div>
    `;
    $("#divHijosEdit").append(html);
});

$(document).on("click",".btnGuardarHijo",function(){
    div=$(this).closest(".fila-hijo");
    nombre=div.find(".inputNombreGuardar").val();
    dni=div.find(".inputDniGuardar").val();
    fechaNacimiento=div.find(".inputFechaNacGuardar").val();
    legajo=$(".cardHijos").find(".inputLegajo").val();

    if (!nombre || !dni || !fechaNacimiento) {
        Swal.fire({
            title: "Atención",
            icon: "warning",
            text: "Debe completar todos los campos.",
            customClass: {
                confirmButton: "btn btn-primary",
            },
        });
        return;
    };

    if (dni.length!=8){
        Swal.fire({
            title: "Atención",
            icon: "warning",
            text: "El DNI debe contener 8 dígitos.",
            customClass: {
                confirmButton: "btn btn-primary",
            },
        });
        return;
    };

    $.ajax({
        url:"/rrhh/personal/agregarFamiliares",
        type:"POST",
        data:{
            nombre:nombre,
            dni:dni,
            fechaNacimiento:fechaNacimiento,
            legajo:legajo,
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success:function(res){
            if(res.success){
                Swal.fire({
                    title: "Operación exitosa",
                    text: "El familiar ha sido agregado correctamente.",
                    icon: "success",
                    customClass: {
                        confirmButton: "btn btn-primary",
                    },
                    buttonsStyling: false,
                });

                div.fadeOut(400, function() { 
                    $(this).remove();
                });

                cargarHijoColab(legajo);

            }
            else{
                Swal.fire({
                    title: "Error",
                    text: "Error al guardar familiar.",
                    icon: "error",
                    customClass: {
                        confirmButton: "btn btn-primary",
                    },
                    buttonsStyling: false,
                });
            }
        },
        error:function(){
            Swal.fire({
                title: "Error",
                text: "Error del servidor.",
                icon: "error",
                customClass: {
                    confirmButton: "btn btn-primary",
                },
                buttonsStyling: false,
            });
        }
    });
});

$(document).on("click", ".btnQuitarHijo", function () {
    $(this).closest(".fila-hijo").remove();
});