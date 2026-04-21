function verNovedad(codigo) {
    $.ajax({
        url: `/novedades/${codigo}`,
        type: "GET",
        success: function (data) {
            $("#inputCodigo").val(data.codigo_novedad);
            $("#inputNovedad").val(data.nombre);
            $("#inputCantidadMaxima").val(data.limite);
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
            order: [[1, "asc"]],
            info: false,
            columns: [
                { data: "ID_NOVEDAD", 'visible':false },
                { data: "CODIGO_NOVEDAD", width:"10%" },
                { data: "NOVEDAD" },
                { data: "limite" },
                {
                    data: "ID_NOVEDAD", className: "text-center",
                    render: function (data) {
                        return `
                            <button class="btn btn-secondary btn-edit" data-id="${data}">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        `;
                    },
                    orderable: false,
                    searchable: false,
                },
            ],
            dom:
                "<'d-top d-flex flex-column flex-md-row align-items-md-center gap-2 mt-1 mx-1' \
                    <'d-flex flex-column flex-sm-row gap-2'B> \
                    <'ms-md-auto mt-2 mt-md-0'f> \
                > \
                <'my-2'rt> \
                <'d-bottom d-flex justify-content-center'i>",
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
            paging: false,
            scrollCollapse: true,
            scrollY: "65vh",
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

$(document).on("submit", "#formNuevaNovedad", function (e) {
    e.preventDefault();

    let form = $(this);

    $.ajax({
        url: "/novedades/crear",
        method: "POST",
        data: form.serialize(),
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (resp) {

            console.log("RESP:", resp);

            const icono = resp.ok ? "success" : "warning";
            const titulo = resp.ok ? "Operación Exitosa" : "Atención";

            Swal.fire({
                icon: icono,
                title: titulo,
                text: resp.mensaje,
            });

            if (resp.ok) {
                form[0].reset();

                // si tenés tabla de novedades
                if (typeof tablaNovedades !== "undefined") {
                    tablaNovedades.ajax.reload(null, false);
                }
            }
        },
        error: function (xhr) {

            console.error("ERROR:", xhr.responseJSON);

            Swal.fire({
                icon: "error",
                title: "Error",
                text: xhr.responseJSON?.error || "Error inesperado",
            });
        }
    });
});