let tablaProveedores;

function cargarTablaProveedores() {
    tablaProveedores = $("#tbProveedores").DataTable({
        ajax: {
            url: "/administracion/compras/proveedores/listar",
            dataSrc: "",
        },
        columns: [
            { data: "id", width: "5%" },
            { data: "nombre", width: "30%" },
            { data: "razonSocial", width: "30%" },
            { data: "cuit", width: "15%" },
            { data: "codigo", width: "20%" },
        ],
        language: {
            url: "/js/es-ES.json",
        },
        dom: "tir",
        scrollY: getScrollY(),
        scrollX:false,
        scrollCollapse: true,
        autoWidth: false,
        paging: false,
    });

    $("#buscarProveedor").on("input", function () {
        tablaProveedores.search(this.value).draw();
    });

    $("#limpiarBusquedaProveedor").on("click", function () {
        $("#buscarProveedor").val("");
        tablaProveedores.search("").draw();
    });

    $(document).on("click", "#tbProveedores tbody tr", function () {
        const data = tablaProveedores.row(this).data();
        if (!data) return;

        $("#inputIdProveedor").val(data.id);
        $("#inputNombreProveedor").val(data.nombre);
        $("#inputRazonSocial").val(data.razonSocial);
        $("#inputCuitProveedor").val(data.cuit);
        $("#inputCodigoProveedor").val(data.codigo);

        $("#modalDetalleProveedor").modal("show");
    });
}

$(document).on("click", "#btnCrearProveedor", function () {
    const nombreProveedor = $('input[name="nombreProveedor"]').val().trim();
    const razonSocial = $('input[name="razonSocial"]').val().trim();
    const cuitProveedor = $('input[name="cuitProveedor"]').val().trim();
    const codigoProveedor = $('input[name="codigoProveedor"]').val().trim();

    if (!nombreProveedor) {
        Swal.fire({
            title: "Atención",
            text: "El nombre del proveedor es obligatorio.",
            icon: "warning",
            showConfirmButton: false,
            timer: 1500,
        });
        return;
    }

    $.ajax({
        url: "/administracion/compras/proveedores/crear",
        type: "POST",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        data: {
            nombreProveedor: nombreProveedor,
            razonSocial: razonSocial,
            cuitProveedor: cuitProveedor,
            codigoProveedor: codigoProveedor,
        },
        success: function () {
            Swal.fire({
                title: "Operación exitosa!",
                text: "Proveedor creado correctamente.",
                icon: "success",
                showConfirmButton: false,
                timer: 1500,
            });
            document.getElementById("formNuevoProveedor").reset();
            if (typeof tablaProveedores !== "undefined") {
                tablaProveedores.ajax.reload();
            }
        },
        error: function (xhr) {
            const msg =
                xhr.responseJSON?.mensaje ||
                "Ocurrió un error al crear el proveedor.";
            Swal.fire({
                title: "Error!",
                text: msg,
                icon: "error",
                showConfirmButton: false,
                timer: 1500,
            });
        },
    });
});

$(document).on("click", "#btnGuardarProveedor", function () {
    const idProveedor = $("#inputIdProveedor").val();
    const nombreProveedor = $("#inputNombreProveedor").val().trim();
    const razonSocial = $("#inputRazonSocial").val().trim();
    const cuitProveedor = $("#inputCuitProveedor").val().trim();
    const codigoProveedor = $("#inputCodigoProveedor").val().trim();

    if (!nombreProveedor) {
        Swal.fire({
            title: "Atención",
            text: "El nombre del proveedor es obligatorio.",
            icon: "warning",
            showConfirmButton: false,
            timer: 1500,
        });
        return;
    }

    $.ajax({
        url: "/administracion/compras/proveedores/editar",
        type: "POST",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        data: {
            idProveedor: idProveedor,
            nombreProveedor: nombreProveedor,
            razonSocial: razonSocial,
            cuitProveedor: cuitProveedor,
            codigoProveedor: codigoProveedor,
        },
        success: function () {
            Swal.fire({
                title: "Operación exitosa!",
                text: "Proveedor actualizado correctamente.",
                icon: "success",
                showConfirmButton: false,
                timer: 1500,
            });
            $("#modalDetalleProveedor").modal("hide");
            tablaProveedores.ajax.reload(null, false);
        },
        error: function (xhr) {
            const msg =
                xhr.responseJSON?.mensaje ||
                "Ocurrió un error al actualizar el proveedor.";
            Swal.fire({
                title: "Error!",
                text: msg,
                icon: "error",
                showConfirmButton: false,
                timer: 1500,
            });
        },
    });
});
