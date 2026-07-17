$(function () {
    cargarCentrosCosto();
    cargarProveedores();
    cargarProductos();
});

function cargarCentrosCosto() {
    $.ajax({
        url: "/compras/centros-costo/listar",
        type: "GET",
        dataType: "json",

        success: function (response) {
            let combo = $("#cmbCentroCosto");

            combo.empty();

            combo.append(
                '<option value="">Seleccione un centro de costo...</option>',
            );

            $.each(response, function (i, item) {
                combo.append(`
            <option value="${item.id}">
                ${item.codigo} - ${item.nombre}
            </option>
        `);
            });

            combo.select2({
                placeholder: "Seleccione un centro de costo...",
                width: "100%",
            });
        },

        error: function () {
            Swal.fire(
                "Error",
                "No fue posible cargar los centros de costo.",
                "error",
            );
        },
    });
}

function cargarProveedores() {
    $.ajax({
        url: "/compras/proveedores/listar",
        type: "GET",
        dataType: "json",

        success: function (response) {
            let combo = $("#cmbProveedor");

            combo.empty();

            combo.append(
                '<option value="">Seleccione un proveedor...</option>',
            );

            $.each(response, function (i, item) {
                combo.append(`
                    <option value="${item.id}">
                        ${item.nombre}
                    </option>
                `);
            });

            combo.select2({
                placeholder: "Seleccione un proveedor...",
                allowClear: true,
                width: "100%",
            });
        },

        error: function () {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "No fue posible cargar los proveedores.",
            });
        },
    });
}

let PRODUCTOS = [];

function cargarProductos() {
    $.ajax({
        url: "/compras/productos/listar",
        type: "GET",
        dataType: "json",

        success: function (response) {
            PRODUCTOS = response;

            console.log("Productos cargados:", PRODUCTOS);

            // Agrega la primera línea recién ahora
            agregarLinea();
        },

        error: function (xhr) {
            console.error(xhr.responseText);

            Swal.fire({
                icon: "error",
                title: "Error",
                text: "No se pudieron cargar los productos.",
            });
        },
    });
}

function setPrioridad(valor) {
    document.getElementById("fPrioridad").value = valor;

    document
        .querySelectorAll("#prioridadGroup .prioridad-btn")
        .forEach((btn) => {
            btn.classList.toggle("active", btn.dataset.val === valor);
        });
}

let lineaSeq = 0;

function agregarLinea(prefill = null) {
    lineaSeq++;
    const id = "ln" + lineaSeq;

    const tr = document.createElement("tr");
    tr.id = id;

    tr.innerHTML = `
        <td class="ps-4">

            <select
                id="producto_${id}"
                class="form-select form-select-sm">
            </select>

        </td>

        <td>
            <input type="text"
                class="form-control form-control-sm"
                id="desc_${id}">
        </td>

        <td>
            <input type="number"
                class="form-control form-control-sm"
                id="cant_${id}"
                value="1">
        </td>

        <td>
            <input type="number"
                class="form-control form-control-sm"
                id="precio_${id}">
        </td>

        <td class="text-center">
            <button
                class="btn btn-sm btn-outline-danger"
                onclick="quitarLinea('${id}')">
                <i class="fa-solid fa-trash"></i>
            </button>
        </td>
    `;

    document.getElementById("lineasBody").appendChild(tr);

    // Cargar productos en el Select2
    let select = $("#producto_" + id);

    select.append('<option value="">Seleccione un producto...</option>');

    PRODUCTOS.forEach(function (item) {
        select.append(`
            <option value="${item.id}">
                ${item.nombre}
            </option>
        `);
    });

    select.select2({
        placeholder: "Seleccione un producto...",
        width: "100%",
    });

    // Completar automáticamente la descripción
    select.on("change", function () {
        let descripcion = $("#desc_" + id);

        if (descripcion.val() === "") {
            descripcion.val($(this).find("option:selected").text());
        }
    });

    // Si viene una línea para editar
    if (prefill) {
        select.val(prefill.producto_id).trigger("change");

        $("#desc_" + id).val(prefill.desc || "");

        $("#cant_" + id).val(prefill.cant ?? 1);

        $("#precio_" + id).val(prefill.precio ?? "");
    }
}

function quitarLinea(id) {
    const tr = document.getElementById(id);
    if (tr) tr.remove();
}

function leerLineas() {
    const out = [];

    document.querySelectorAll("#lineasBody tr").forEach((tr) => {
        const id = tr.id;

        const producto_id = $("#producto_" + id).val();

        const descripcion = $("#desc_" + id)
            .val()
            .trim();

        const cantidad = parseFloat($("#cant_" + id).val());

        const precio = parseFloat($("#precio_" + id).val());

        if (!producto_id) return;

        out.push({
            producto_id: producto_id,
            descripcion_item: descripcion,
            cantidad: cantidad,
            precio: precio,
        });
    });

    return out;
}

function enviarPedido() {
    const lineas = leerLineas();

    if (lineas.length === 0) {
        Swal.fire("Atención", "Debe agregar al menos un producto.", "warning");

        return;
    }

    $.ajax({
        url: "/compras/guardar",

        type: "POST",

        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },

        data: {
            fecha: $("#fFecha").val(),

            prioridad: $("#fPrioridad").val(),

            solicitante_id: $("#fUserId").val(),

            centro_costo_id: $("#cmbCentroCosto").val(),

            proveedor_id: $("#cmbProveedor").val(),

            descripcion: $("#fDescripcion").val(),

            detalle: lineas,
        },

        success: function () {
            Swal.fire({
                icon: "success",

                title: "Pedido registrado correctamente",
            }).then(() => {
                location.reload();
            });
        },

        error: function (xhr) {
            console.log(xhr.responseText);

            Swal.fire({
                icon: "error",

                title: "Ocurrió un error",
            });
        },
    });
}
