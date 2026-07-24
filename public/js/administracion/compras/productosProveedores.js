let tablaProductos;
let tablaProveedores;

function getScrollY() {
    return window.innerWidth < 768 ? "30vh" : "45vh";
}

$(document).ready(function () {
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
});

$(document).on("click", "#btnCrearProducto", function () {
    const nombreProducto = $('input[name="nombreProducto"]').val().trim();
    const codigoProducto = $('input[name="codigoProducto"]').val().trim();

    if (!nombreProducto) {
        Swal.fire(
            "Atención",
            "El nombre del producto es obligatorio.",
            "warning",
        );
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
            Swal.fire(
                "Operación exitosa!",
                "Producto creado correctamente.",
                "success",
            );
            document.getElementById("formNuevoProducto").reset();
            tablaProductos.ajax.reload();
        },
        error: function (xhr) {
            const msg =
                xhr.responseJSON?.mensaje ||
                "Ocurrió un error al crear el producto.";
            Swal.fire("Error", msg, "error");
        },
    });
});

$(document).on("click", "#btnGuardarProducto", function () {
    const idProducto = $("#inputIdProducto").val();
    const nombreProducto = $("#inputNombreProducto").val().trim();
    const codigoProducto = $("#inputCodigoProducto").val().trim();

    if (!nombreProducto) {
        Swal.fire(
            "Atención",
            "El nombre del producto es obligatorio.",
            "warning",
        );
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
            Swal.fire(
                "Listo",
                "Producto actualizado correctamente.",
                "success",
            );
            $("#modalDetalleProducto").modal("hide");
            tablaProductos.ajax.reload(null, false); // false = no resetea scroll/posición
        },
        error: function (xhr) {
            const msg =
                xhr.responseJSON?.mensaje ||
                "Ocurrió un error al actualizar el producto.";
            Swal.fire("Error", msg, "error");
        },
    });
});

$(document).ready(function () {
    tablaProveedores = $("#tbProveedores").DataTable({
        ajax: {
            url: "/administracion/compras/proveedores/listar",
            dataSrc: "",
        },
        columns: [
            { data: "id", width: "8%" },
            { data: "nombre" },
            { data: "razonSocial" },
            { data: "cuit", width: "15%" },
            { data: "codigo", width: "15%" },
        ],
        language: {
            url: "/js/es-ES.json",
        },
        dom: "tir",
        scrollY: getScrollY(),
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


});

$(document).on("click", "#btnCrearProveedor", function () {
    const nombreProveedor = $('input[name="nombreProveedor"]').val().trim();
    const razonSocial = $('input[name="razonSocial"]').val().trim();
    const cuitProveedor = $('input[name="cuitProveedor"]').val().trim();
    const codigoProveedor = $('input[name="codigoProveedor"]').val().trim();

    if (!nombreProveedor) {
        Swal.fire(
            "Atención",
            "El nombre del proveedor es obligatorio.",
            "warning",
        );
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
            Swal.fire(
                "Operación exitosa!",
                "Proveedor creado correctamente.",
                "success",
            );
            document.getElementById("formNuevoProveedor").reset();
            if (typeof tablaProveedores !== "undefined") {
                tablaProveedores.ajax.reload();
            }
        },
        error: function (xhr) {
            const msg =
                xhr.responseJSON?.mensaje ||
                "Ocurrió un error al crear el proveedor.";
            Swal.fire("Error", msg, "error");
        },
    });
});

$(document).on('click', '#btnGuardarProveedor', function () {
    const idProveedor = $('#inputIdProveedor').val();
    const nombreProveedor = $('#inputNombreProveedor').val().trim();
    const razonSocial = $('#inputRazonSocial').val().trim();
    const cuitProveedor = $('#inputCuitProveedor').val().trim();
    const codigoProveedor = $('#inputCodigoProveedor').val().trim();

    if (!nombreProveedor) {
        Swal.fire("Atención", "El nombre del proveedor es obligatorio.", "warning");
        return;
    }

    $.ajax({
        url: "/administracion/compras/proveedores/editar",
        type: "POST",
        headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        data: {
            idProveedor: idProveedor,
            nombreProveedor: nombreProveedor,
            razonSocial: razonSocial,
            cuitProveedor: cuitProveedor,
            codigoProveedor: codigoProveedor,
        },
        success: function () {
            Swal.fire("Listo", "Proveedor actualizado correctamente.", "success");
            $('#modalDetalleProveedor').modal('hide');
            tablaProveedores.ajax.reload(null, false);
        },
        error: function (xhr) {
            const msg = xhr.responseJSON?.mensaje || "Ocurrió un error al actualizar el proveedor.";
            Swal.fire("Error", msg, "error");
        },
    });
});