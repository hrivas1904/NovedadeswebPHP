console.log('nominaPersonal.js cargado');


$(document).ready(function () {
    if ($("#tb_personal").length > 0) {
        new DataTable("#tb_personal", {
            scrollX: true, // Mejor para 15 columnas
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            },
            paging: false,
            scrollX: true,
            autoWidth: true,
            fixedColumns: true,
            paging: false,
            /*scrollY: 'var(--tabla-altura)',*/
            dom: "<'dt-top d-flex align-items-center justify-content-between'Bf>rt<'dt-bottom'p>",
            buttons: [
                {
                    extend: "excelHtml5",
                    text: "Exportar Excel",
                    filename: "Productos en sucursal",
                    title: "",
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5] },
                    className: "btn-export-excel",
                },
                {
                    extend: "pdfHtml5",
                    text: "Exportar PDF",
                    filename: "Productos en sucursal",
                    title: "Productos en sucursal",
                    pageSize: "A4",
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5] },
                    className: "btn-export-pdf",
                },
                {
                    extend: "print",
                    text: "Imprimir",
                    title: "Productos en sucursal",
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5] },
                    className: "btn-printer",
                },
            ],
        });
    }
});

function previewImagen(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
            document.getElementById("previewFoto").src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

//sp para cargar selector areas
$(document).ready(function () {
    cargarAreas();
});

function cargarAreas() {
    $.ajax({
        url: "/areas/lista",
        method: "GET",
        success: function (data) {
            $(".js-select-area").each(function () {
                const select = $(this);
                const valorActual = select.val();

                select.empty();
                select.append('<option value="">Seleccione área</option>');

                data.forEach((area) => {
                    select.append(
                        `<option value="${area.id_area}">
                            ${area.nombre}
                        </option>`
                    );
                });

                if (valorActual) {
                    select.val(valorActual);
                }
            });
        },
    });
}

//sp para cargar selector categorias
$(document).ready(function () {
    cargarCategorias();
});

function cargarCategorias() {
    $.ajax({
        url: '/categorias-empleados/lista',
        type: 'GET',
        success: function (data) {

            $('.js-select-categoria').each(function () {
                const select = $(this);
                const valorActual = select.val();

                select.empty();
                select.append('<option value="">Seleccione categoría</option>');

                data.forEach(cat => {
                    select.append(
                        `<option value="${cat.id_categ}">
                            ${cat.nombre}
                        </option>`
                    );
                });

                // mantiene selección si existía (edición)
                if (valorActual) {
                    select.val(valorActual);
                }
            });
        },
        error: function (err) {
            console.error('Error cargando categorías', err);
        }
    });
}

//sp para cargar selector de roles
$(document).on('change', '.js-select-categoria', function () {

    const idCategoria = $(this).val();
    const selectRol = $('.js-select-rol');

    // reset del rol
    selectRol.empty()
             .append('<option value="">Seleccione rol</option>')
             .prop('disabled', true);

    if (!idCategoria) return;

    $.ajax({
        url: `/roles-empleados/por-categoria/${idCategoria}`,
        method: 'GET',
        success: function (data) {

            data.forEach(rol => {
                selectRol.append(
                    `<option value="${rol.id_rol}">
                        ${rol.nombre}
                    </option>`
                );
            });

            selectRol.prop('disabled', false);
        },
        error: function (err) {
            console.error('Error cargando roles', err);
        }
    });
});

//selectores de os y codigo
$(document).ready(function () {

    inicializarSelectObraSocial();

    // cuando se selecciona una obra social
    $(document).on('select2:select', '.js-select-obra-social', function (e) {
        const data = e.params.data;
        $('.js-input-codigo-os').val(data.codigo);
    });

    // si se limpia la selección
    $(document).on('select2:clear', '.js-select-obra-social', function () {
        $('.js-input-codigo-os').val('');
    });
});

function inicializarSelectObraSocial() {

    $('.js-select-obra-social').select2({
        theme: 'bootstrap-5',
        placeholder: 'Buscar obra social...',
        allowClear: true,
        width: '100%',

        ajax: {
            url: '/obra-social/lista',
            dataType: 'json',
            delay: 250,
            processResults: function (data) {
                return {
                    results: data.map(item => ({
                        id: item.id,
                        text: item.nombre,
                        codigo: item.codigo // 👈 lo pasamos como extra
                    }))
                };
            }
        }
    });
}



