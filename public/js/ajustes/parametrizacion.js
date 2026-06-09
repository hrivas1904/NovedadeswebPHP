$(document).ready(function () {
    cargarAreasServicios();
});

//AREAS
$(document).ready(function () {
    const tablaArea=new DataTable("#tb_areas", {
        ajax: {
            url: "/areas/lista",
            type: "GET",
            dataSrc: "",
        },

        columns: [{ data: "id_area" }, { data: "nombre" }],

        paging: false,
        language: {
            url: "/js/es-ES.json",
        },
        autoWidth: false,
        scrollX: false,
        scrollY: "230px",
        info: false,
        searching: false,
    });

    $("#tb_areas tbody").on("click", "tr", function () {
        const rowData=tablaArea.row(this).data();
        verDetalleArea(rowData.id_area);
    });
});

function verDetalleArea(idArea){
    const modal=$("#modalEdicionArea");

    $.ajax({
        url:'/parametrizacion/verArea',
        type:'GET',
        data:{
            idArea:idArea
        },
        success: function(res){
            $("#idAreaEdit").val(res.data[0].id_area);
            $("#nombreAreaEdit").val(res.data[0].nombre);
        },
        error: function(){
            Swal.fire({
                title:'Error',
                text:'No se pudo cargar la información del área.',
                icon:'error',
                customClass:{   
                    confirmColorButton: 'btn-primary'
                },
                buttonsStyling:false,
            })
        }
    });

    modal.modal('show');
}

function cerrarModalAreaEdit(){
    const modal=$("#modalEdicionArea");
    const form=$("#formEditArea");
    form.reset[0];
    modal.modal('hide');
}

$("#formNuevaArea").on("submit", function (e) {
    e.preventDefault();

    const formData = $(this).serialize();

    $.ajax({
        url: "/areas/crear",
        method: "POST",
        data: formData,

        success: function (response) {
            Swal.fire({
                icon: "success",
                title: "Éxito",
                text: response.message,
                
            });

            $("#formNuevaArea")[0].reset();
            cargarAreasServicios();
        },

        error: function (xhr) {
            let mensaje = "Ocurrió un error.";

            if (xhr.responseJSON?.message) {
                mensaje = xhr.responseJSON.message;
            }

            Swal.fire({
                icon: "error",
                title: "Error",
                text: mensaje,
            });
        },
    });
});

$("#btnEditarArea").on("click", function () {
    const idArea = $("#idAreaEdit").val();
    const nombre = $("#nombreAreaEdit").val();

    $.ajax({
        url: '/parametrizacion/editarArea',
        type: 'POST',
        data: {
            idArea: idArea,
            nombre: nombre,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (res) {
            if (res.success) {
                Swal.fire({
                    title: 'Éxito',
                    text: 'Área actualizada correctamente.',
                    icon: 'success',
                    buttonsStyling: false,
                    customClass: { confirmButton: 'btn btn-primary' }
                }).then(() => {
                    $("#modalEdicionArea").modal('hide');
                    tablaAreas.ajax.reload();
                });
            }
        },
        error: function () {
            Swal.fire({
                title: 'Error',
                text: 'No se pudo actualizar el área.',
                icon: 'error',
                buttonsStyling: false,
                customClass: { confirmButton: 'btn btn-primary' }
            });
        }
    });
});

//CATEGORÍAS
$(document).ready(function () {
    new DataTable("#tb_categ", {
        ajax: {
            url: "/categorias-empleados/lista",
            type: "GET",
            dataSrc: "",
        },

        columns: [{ data: "id_categ" }, { data: "nombre" }],

        paging: false,
        language: {
            url: "/js/es-ES.json",
        },
        autoWidth: false,
        scrollX: false,
        scrollY: "230px",
        info: false,
        searching: false,
    });
});

function verDetalleCategorias(){
}

//SERVICIOS
$(document).ready(function () {
    new DataTable("#tb_servicios", {
        ajax: {
            url: "/servicios/listar",
            type: "GET",
            dataSrc: "",
        },

        columns: [
            { data: "id_servicios" },
            { data: "servicio" },
            { data: "id_area", visible: false },
            { data: "area" },
        ],

        paging: false,
        language: {
            url: "/js/es-ES.json",
        },
        autoWidth: false,
        scrollX: false,
        scrollY: "230px",
        info: false,
        searching: false,
    });
});

function verDetalleServicio(){

}

function cargarAreasServicios() {
    $.ajax({
        url: "/areas/lista",
        type: "GET",
        success: function (data) {
            const select = $("#selectAreaServicios");
            select.empty();
            select.append('<option value="">-- Seleccioná un área --</option>');
            data.forEach(function (area) {
                select.append(
                    `<option value="${area.id_area}">${area.nombre}</option>`,
                );
            });
            select.select2({
                placeholder: "-- Seleccioná un área --",
                allowClear: true,
            });
        },
    });
}
