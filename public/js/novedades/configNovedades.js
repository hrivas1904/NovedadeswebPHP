function verNovedad(codigo) {
    $.ajax({
        url: `/rrhh/novedades/${codigo}`,
        type: "GET",
        success: function (data) {
            $("#idNovedadEdit").val(data.id_novedad);
            $("#inputCodigoEdit").val(data.codigo_novedad);
            $("#inputNovedadEdit").val(data.nombre);
            $("#inputCantidadMaxima").val(data.limite);
            $("#selectEditTipoValor").val(data.tipo_valor);
            $("#paraFinnegansEdit").val(data.para_finnegans);
            $("#activaEdit").val(data.activa);

            $("#modalEdicionNovedad").modal("show");
        },
        error: function (err) {
            console.error("Error cargando novedad", err);
            alert("No se pudo cargar la novedad");
        },
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
                url: "/rrhh/novedades/data",
                type: "GET",
                error: function (e) {
                    console.error("Error al cargar datos:", e.responseText);
                },
            },
            language: {
                url: "/js/es-ES.json",
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
            createdRow: function (row, data) {
                $(row).attr("data-id", data.ID_NOVEDAD);
            },
            columns: [
                { data: "ID_NOVEDAD", visible: false },
                { data: "CODIGO_NOVEDAD", width: "10%" },
                { data: "NOVEDAD" },
                { data: "limite" },
            ],
            dom: "<'d-top d-flex flex-column flex-md-row align-items-md-center gap-2 mt-1 mx-1' \
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
            ],
            scrollX: true,
            paging: false,
            scrollCollapse: true,
            scrollY: "58vh",
        });
    }

    $(document).on("click", "#tb_configuracion tbody tr", function (event) {
        event.preventDefault();
        event.stopPropagation();
        const idRegistro = $(this).data("id");
        console.log("Id registro recibido:", idRegistro);
        if (!idRegistro) return;
        verNovedad(idRegistro);
    });
});

$(document).on("submit", "#formNuevaNovedad", function (e) {
    e.preventDefault();

    let form = $(this);

    $.ajax({
        url: "/rrhh/novedades/crear",
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
                customClass: {
                    confirmButton: "btn btn-primary",
                },
            });

            if (resp.ok) {
                form[0].reset();
                $("#tb_configuracion").DataTable().ajax.reload(null, false);
            }
        },
        error: function (xhr) {
            console.error("ERROR:", xhr.responseJSON);

            Swal.fire({
                icon: "error",
                title: "Error",
                text: xhr.responseJSON?.error || "Error inesperado",
                customClass: {
                    confirmButton: "btn btn-primary",
                },
            });
        },
    });
});

$(document).on("submit", "#formEditNovedades", function (e) {
    e.preventDefault();

    let form = $(this);

    $.ajax({
        url: "/rrhh/novedades/editar",
        method: "POST",
        data: form.serialize(),
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (resp) {
            Swal.fire({
                icon: resp.ok ? "success" : "warning",
                title: resp.ok ? "Actualizado" : "Atención",
                text: resp.mensaje,
                customClass: {
                    confirmButton: "btn btn-primary",
                },
            }).then(() => {
                if (resp.ok) {
                    cerrarModalNovedadEdit();
                    $("#tb_configuracion").ajax.reload(null, false);
                }
            });
        },
        error: function (xhr) {
            console.error("ERROR:", xhr.responseJSON);

            Swal.fire({
                icon: "error",
                title: "Error",
                text: xhr.responseJSON?.error || "Error inesperado",
                customClass: {
                    confirmButton: "btn btn-primary",
                },
            });
        },
    });
});
