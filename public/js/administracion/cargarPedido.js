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

let archivosSeleccionados = []; // File[] reales, para mandar en el FormData

const TIPOS_PERMITIDOS = [
    ".pdf",
    ".jpg",
    ".jpeg",
    ".png",
    ".webp",
    ".xlsx",
    ".xls",
    ".doc",
    ".docx",
];
const TAMANIO_MAX = 3 * 1024 * 1024; // 3 MB

function onAdjuntosSeleccionados(event) {
    const nuevos = Array.from(event.target.files);

    nuevos.forEach((file) => {
        const ext = "." + file.name.split(".").pop().toLowerCase();

        if (!TIPOS_PERMITIDOS.includes(ext)) {
            Swal.fire(
                "Atención",
                `"${file.name}" no es un tipo de archivo permitido.`,
                "warning",
            );
            return;
        }
        if (file.size > TAMANIO_MAX) {
            Swal.fire(
                "Atención",
                `"${file.name}" supera los 3 MB permitidos.`,
                "warning",
            );
            return;
        }
        archivosSeleccionados.push(file);
    });

    event.target.value = ""; // permite volver a elegir el mismo archivo si lo saca y lo re-agrega
    renderAdjuntosList();
}

function quitarAdjunto(index) {
    archivosSeleccionados.splice(index, 1);
    renderAdjuntosList();
}

function renderAdjuntosList() {
    const cont = document.getElementById("adjuntosList");
    cont.innerHTML = "";

    archivosSeleccionados.forEach((file, i) => {
        const kb = (file.size / 1024).toFixed(0);
        const div = document.createElement("div");
        div.className =
            "d-flex align-items-center justify-content-between border rounded px-2 py-1 mb-1";
        div.innerHTML = `
      <span class="text-truncate" style="max-width: 80%;">
        <i class="fa-regular fa-file me-1"></i>${file.name} <small class="text-muted">(${kb} KB)</small>
      </span>
      <button type="button" class="btn btn-sm btn-outline-danger" onclick="quitarAdjunto(${i})">
        <i class="fa-solid fa-xmark"></i>
      </button>
    `;
        cont.appendChild(div);
    });
}

function enviarPedido() {
    const lineas = leerLineas();

    if (lineas.length === 0) {
        Swal.fire("Atención", "Debe agregar al menos un producto.", "warning");
        return;
    }

    const formData = new FormData();

    formData.append("fecha", $("#fFecha").val());
    formData.append("prioridad", $("#fPrioridad").val());
    formData.append("solicitante_id", $("#fUserId").val());
    formData.append("centro_costo_id", $("#cmbCentroCosto").val());
    formData.append("proveedor_id", $("#cmbProveedor").val());
    formData.append("descripcion", $("#fDescripcion").val());

    lineas.forEach((item, i) => {
        formData.append(`detalle[${i}][producto_id]`, item.producto_id);
        formData.append(`detalle[${i}][cantidad]`, item.cantidad);
        formData.append(`detalle[${i}][precio]`, item.precio);
        formData.append(
            `detalle[${i}][descripcion_item]`,
            item.descripcion_item || "",
        );
    });

    archivosSeleccionados.forEach((file, i) => {
        formData.append(`adjuntos[${i}]`, file);
    });

    $.ajax({
        url: "/compras/guardar",
        type: "POST",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        data: formData,
        processData: false,
        contentType: false,

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
