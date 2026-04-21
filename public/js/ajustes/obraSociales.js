function verObraSocial(id) {
    $.ajax({
        url: `/obra-social/${id}`,
        type: "GET",
        success: function (data) {
            $("#idOs").val(data.id);
            $("#codigoOsEdit").val(data.codigo);
            $("#nombreOsEdit").val(data.nombre);
            $("#modalEdicionOs").modal("show");
        },
        error: function (err) {
            console.error("Error cargando obra social", err);
            alert("No se pudo cargar la obra socual");
        },
    });
}

$(document).ready(function () {
    if ($("#tb_obraSocial").length > 0) {
        $("#tb_obraSocial").DataTable({
            ajax: {
                url: "/obra-social/lista",
                type: "GET",
                dataSrc: "", // 👈 clave
            },
            columns: [
                { data: "id", visible: false },
                { data: "text" },
                { data: "codigo" },
            ],
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
            info: false,
            order: [[1, "asc"]],
            responsive: true,
            autoWidth: false,
            scrollX: true,
            paging: false,
            scrollCollapse: true,
            scrollY: "65vh",
        });
    }

    $(document).on("click", "#tb_obraSocial tbody tr", function () {
        const table = $("#tb_obraSocial").DataTable();
        const data = table.row(this).data();
        if (!data) return;
        console.log("OS seleccionada:", data);
        verObraSocial(data.id);
    });
});

$(document).on("submit", "#formNuevaObraSocial", function (e) {
    e.preventDefault();

    let form = $(this);

    // 👉 deshabilitar botón (evita doble click)
    $("#btnCrearOs").prop("disabled", true);

    $.ajax({
        url: "/obra-social/crear",
        method: "POST",
        data: form.serialize(),
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (resp) {
            Swal.fire({
                icon: resp.success ? "success" : "warning",
                title: resp.success ? "Correcto" : "Atención",
                text: resp.mensaje,
            }).then(() => {
                if (resp.success) {
                    form[0].reset();

                    // 👉 si tenés select de obras sociales
                    if (typeof cargarObrasSociales === "function") {
                        cargarObrasSociales();
                    }
                    $("#modalNuevaOs").modal("hide");
                    $("#tb_obraSocial").DataTable().ajax.reload(null, false);
                }

                $("#btnCrearOs").prop("disabled", false);
            });
        },
        error: function (xhr) {
            console.error("ERROR:", xhr.responseJSON);

            Swal.fire({
                icon: "error",
                title: "Error",
                text: xhr.responseJSON?.error || "Error inesperado",
            });

            $("#btnCrearOs").prop("disabled", false);
        },
    });
});

$(document).on("submit", "#formEditObraSocial", function (e) {
    e.preventDefault();

    let form = $(this);

    $("#btnGuardarOsEdit").prop("disabled", true);

    $.ajax({
        url: "/obra-social/editar",
        method: "POST",
        data: form.serialize(),
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (resp) {
            Swal.fire({
                icon: resp.success ? "success" : "warning",
                title: resp.success ? "Actualizado" : "Atención",
                text: resp.mensaje,
            }).then(() => {
                if (resp.success) {
                    cerrarModalOsEdit();

                    $("#tb_obraSocial").DataTable().ajax.reload(null, false);
                }

                $("#btnGuardarOsEdit").prop("disabled", false);
            });
        },
        error: function (xhr) {
            console.error(xhr.responseJSON);

            Swal.fire({
                icon: "error",
                title: "Error",
                text: xhr.responseJSON?.error || "Error inesperado",
            });

            $("#btnGuardarOsEdit").prop("disabled", false);
        },
    });
});

function cerrarModalOsEdit() {
    $("#formEditObraSocial")[0].reset();
    const modalElement = document.getElementById("modalEdicionOs");
    const modalInstance = bootstrap.Modal.getInstance(modalElement);
    if (modalInstance) {
        modalInstance.hide();
    }
}
