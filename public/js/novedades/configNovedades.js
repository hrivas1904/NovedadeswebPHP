function verNovedad(codigo) {
    $.ajax({
        url: `/novedades/${codigo}`,
        type: "GET",
        success: function (data) {
            $("#inputCodigo").val(data.codigo_novedad);
            $("#inputNovedad").val(data.nombre);
            $("#selectEditCategoriaNovedades").val(data.id_categ);
            $("#selectEditTipoValor").val(data.tipo_valor);

            $("#modalEdicionNovedad").modal("show");
        },
        error: function (err) {
            console.error("Error cargando novedad", err);
            alert("No se pudo cargar la novedad");
        }
    });
}

function abrirModalNovedadEdit() {
    $("#modalEdicionNovedad").modal("show");
}

function cerrarModalNovedadEdit() {
    $("#modalEdicionNovedad").modal("hide");
}

function abrirModalCateg() {
    $("#modalAltaCategoria").modal("show");
}

function cerrarModalCateg() {
    $("#modalAltaCategoria").modal("hide");
}

$(document).ready(function () {
    if ($("#tb_configuracion").length > 0) {
        $("#tb_configuracion").DataTable({
            ajax: {
                url: "/novedades/data",
                type: "GET",
                error: function (e) {
                    console.error("Error al cargar datos:", e.responseText);
                },
            },
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
                lengthMenu: "_MENU_",
                paginate: {
                    first: "<<",
                    previous: "<",
                    next: ">",
                    last: ">>",
                },
            },
            order: [[2, "asc"]],
            info: true,
            columns: [
                { data: "CODIGO_NOVEDAD" },
                { data: "NOVEDAD" },
                { data: "CATEG" },
                { data: "TIPO_VALOR" },
                {
                    data: "CODIGO_NOVEDAD",
                    render: function (data) {
                        return `
                            <button class="btn-alerta btn-edit" data-id="${data}">
                                <i class="fa-solid fa-pen-to-square"></i> Editar
                            </button>
                        `;
                    },
                    orderable: false,
                    searchable: false,
                },
            ],
            dom: "<'d-top d-flex align-items-center gap-2'lB<'d-flex ms-auto'f>><'my-2'rt><'d-bottom d-flex align-items-center justify-content-between'ip>",
            buttons: [
                {
                    extend: "excelHtml5",
                    className: "btn-export-excel",
                    text: '<i class="fa-solid fa-file-excel"></i> Excel',
                },
                {
                    extend: "pdfHtml5",
                    text: '<i class="fa-solid fa-file-pdf"></i> PDF',
                    className: "btn-export-pdf",
                },
                {
                    extend: "print",
                    className: "btn-printer",
                    text: '<i class="fa-solid fa-print"></i> Imprimir',
                },
            ],
            scrollX: true,
        });
    }

    $(document).on("click", ".btn-edit", function (event) {
        event.preventDefault();
        event.stopPropagation();
        const codigo = $(this).data("id");
        console.log("Código recibido: " + codigo);
        verNovedad(codigo);
    });
});

//sp para cargar selector categorias
$(document).ready(function () {
    cargarCategoriasNovedades();
    cargarCategoriasNovedadesEdit();
});

function cargarCategoriasNovedades() {
    $.ajax({
        url: "/categorias-novedad/lista",
        type: "GET",
        dataType: "json",
        success: function (data) {
            const select = $("#selectCategoriaNovedades");

            select.empty();
            select.append('<option value="">Seleccione categoría</option>');

            data.forEach((cat) => {
                select.append(`
                    <option value="${cat.ID_CATEG}">
                        ${cat.NOMBRE}
                    </option>
                `);
            });
        },
        error: function (xhr, status, error) {
            console.error("Error cargando categorías:", error);
        },
    });
}

function cargarCategoriasNovedadesEdit() {
    $.ajax({
        url: "/categorias-novedad/lista",
        type: "GET",
        dataType: "json",
        success: function (data) {
            const select = $("#selectEditCategoriaNovedades");

            select.empty();

            data.forEach((cat) => {
                select.append(`
                    <option value="${cat.ID_CATEG}">
                        ${cat.NOMBRE}
                    </option>
                `);
            });
        },
        error: function (xhr, status, error) {
            console.error("Error cargando categorías:", error);
        },
    });
}

$("#formNuevaNovedad").on("submit", function (e) {
    e.preventDefault();
    $.ajax({
        url: "/novedades",
        type: "POST",
        data: $(this).serialize(),
        success: function (res) {
            alert(res.mensaje);

            if (res.mensaje.includes("correctamente")) {
                $("#formNuevaNovedad")[0].reset();
                $("#tb_configuracion").ajax.reload();
            }
        },
        error: function (err) {
            console.error("Error creando novedad", err);
            alert("Error al crear la novedad");
        }
    });
});