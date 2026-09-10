let tablaProductos;

function cargarTablaProductos() {
    tablaProductos = $("#tbProductos").DataTable({
        ajax: {
            url: "/administracion/compras/productos/listar",
            dataSrc: "",
        },
        columns: [
            { data: "id", width: "10%" },
            { data: "nombre", width: "70%" },
            { data: "codigo", width: "20%" },
        ],
        language: {
            url: "/js/es-ES.json",
        },
        scrollX: false,
        dom: "tir",
        scrollY: getScrollY(),
        autoWidth: false,
        paging: false,
        scrollCollapse: true,
    });

    $("#buscarProducto").on("input", function () {
        tablaProductos.search(this.value).draw();
    });

    $("#limpiarBusqueda").on("click", function () {
        $("#buscarProducto").val("");
        tablaProductos.search("").draw();
    });

    $(document).on("click", "#tbProductos tbody tr", function () {
        const modal = $("#modalDetalleProducto");
        const data = tablaProductos.row(this).data();
        if (!data) return;
        $("#inputIdProducto").val(data.id);
        $("#inputNombreProducto").val(data.nombre);
        $("#inputCodigoProducto").val(data.codigo);
        modal.modal("show");
    });
}

$(document).on("click", "#btnCrearProducto", function () {
    const nombreProducto = $('input[name="nombreProducto"]').val().trim();
    const codigoProducto = $('input[name="codigoProducto"]').val().trim();

    if (!nombreProducto) {
        Swal.fire({
            title: "Atención",
            text: "El nombre del producto es obligatorio.",
            icon: "warning",
            showConfirmButton: false,
            timer: 1500,
        });
        return;
    }

    $.ajax({
        url: "/administracion/compras/productos/crear",
        type: "POST",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        data: {
            nombreProducto: nombreProducto,
            codigoProducto: codigoProducto,
        },
        success: function () {
            Swal.fire({
                title: "Operación exitosa!",
                text: "Producto creado correctamente.",
                icon: "success",
                showConfirmButton: false,
                timer: 1500,
            });
            document.getElementById("formNuevoProducto").reset();
            tablaProductos.ajax.reload();
        },
        error: function (xhr) {
            const msg =
                xhr.responseJSON?.mensaje ||
                "Ocurrió un error al crear el producto.";
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

$(document).on("click", "#btnGuardarProducto", function () {
    const idProducto = $("#inputIdProducto").val();
    const nombreProducto = $("#inputNombreProducto").val().trim();
    const codigoProducto = $("#inputCodigoProducto").val().trim();

    if (!nombreProducto) {
        Swal.fire({
            title: "Atención",
            text: "El nombre del producto es obligatorio.",
            icon: "warning",
            showConfirmButton: false,
            timer: 1500,
        });
        return;
    }

    $.ajax({
        url: "/administracion/compras/productos/editar",
        type: "POST",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        data: {
            idProducto: idProducto,
            nombreProducto: nombreProducto,
            codigoProducto: codigoProducto,
        },
        success: function () {
            Swal.fire({
                title: "Operación exitosa!",
                text: "Producto actualizado correctamente.",
                icon: "success",
                showConfirmButton: false,
                timer: 1500,
            });
            $("#modalDetalleProducto").modal("hide");
            tablaProductos.ajax.reload(null, false); // false = no resetea scroll/posición
        },
        error: function (xhr) {
            const msg =
                xhr.responseJSON?.mensaje ||
                "Ocurrió un error al actualizar el producto.";
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
