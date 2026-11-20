$("#contenedorLinks").on("click", ".btn-eliminar-link", function () {
    const id = $(this).data("id");

    Swal.fire({
        title: "¿Eliminar link?",
        text: "El link dejará de estar disponible en el sistema.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "/administracion/links/eliminarLinkImportacion",
                type: "POST",
                dataType: "JSON",
                data: {
                    id: id,
                    _token: $('meta[name="csrf-token"]').attr("content"),
                },

                beforeSend: function () {
                    $(".btn-eliminar-link").prop("disabled", true);
                },

                success: function (res) {
                    if (res.success) {
                        Swal.fire({
                            icon: "success",
                            title: "¡Operación exitosa!",
                            text: res.mensaje,
                            timer: 1200,
                            showConfirmButton: false,
                        });

                        cargarLinksImportacion();
                    }
                },

                error: function (xhr) {
                    Swal.fire({
                        icon: "error",
                        title: "¡Error!",
                        text:
                            xhr.responseJSON?.mensaje ||
                            "Ocurrió un error al eliminar el link.",
                        confirmButtonText: "Aceptar",
                    });

                    console.error(
                        "Error al eliminar link:",
                        xhr.responseJSON || xhr.responseText,
                    );
                },

                complete: function () {
                    $(".btn-eliminar-link").prop("disabled", false);
                },
            });
        }
    });
});
