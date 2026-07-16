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
            productos = response;
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
                <i class="fa fa-times"></i>
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
        const prodCodigo = document.getElementById(`prodCodigo_${id}`).value;
        const prodNombre = document
            .getElementById(`prodInput_${id}`)
            .value.trim();
        const desc = document.getElementById(`desc_${id}`).value.trim();
        const cant = parseFloat(document.getElementById(`cant_${id}`).value);
        const precio = parseFloat(
            document.getElementById(`precio_${id}`).value,
        );
        if (!prodNombre && !cant && !precio) return;
        out.push({ prodCodigo, prodNombre, desc, cant, precio });
    });
    return out;
}
