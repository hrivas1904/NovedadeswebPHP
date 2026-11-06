$(document).ready(function () {
    const idVotacion = 1; // Reemplazar por el ID real

    // Ocultar formulario inicialmente
    $("#divResponderPreguntas").removeClass("d-flex").addClass("d-none");

    // Permitir solamente números
    $("#inputDni").on("input", function () {
        $(this).val($(this).val().replace(/\D/g, ""));
    });

    // Validar DNI
    $("#btnValidarDni").on("click", function () {
        const dni = $("#inputDni").val().trim();

        if (!/^[0-9]{7,8}$/.test(dni)) {
            mostrarMensajeDni(
                "Ingrese un DNI válido de 7 u 8 dígitos.",
                "danger",
            );
            return;
        }

        const $boton = $(this);

        $boton.prop("disabled", true).html(`
            <span class="spinner-border spinner-border-sm me-2"></span>
            Validando...
        `);

        $.ajax({
            url: `/rrhh/votaciones/validarDni/${DASHBOARD_TOKEN}`,
            method: "POST",
            dataType: "json",
            data: {
                dni: $("#inputDni").val().trim(),
                id_votacion: 1,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.success) {
                    mostrarMensajeDni(
                        `Colaborador validado correctamente. ¡Bienvenido/a, ${response.data.colaborador}!`,
                        "success",
                    );

                    $("#inputDni").prop("readonly", true);
                    $("#btnValidarDni").addClass("d-none");

                    $("#divResponderPreguntas")
                        .removeClass("d-none")
                        .addClass("d-flex");

                    obtenerColaboradoresxGrupo();
                    $("#btnEnviarVotacion").removeClass('d-none');
                }
            },
            error: function (xhr) {
                mostrarMensajeDni(
                    xhr.responseJSON?.mensaje ?? "Error al validar el DNI.",
                    "danger",
                );
            },
            complete: function () {
                $boton.prop("disabled", false).html(`
                    <i class="fa-solid fa-id-card me-2"></i>
                    Validar identidad
                `);
            },
        });
    });

    // Validar con ENTER
    $("#inputDni").on("keypress", function (e) {
        if (e.which === 13) {
            e.preventDefault();
            $("#btnValidarDni").trigger("click");
        }
    });
});

function mostrarMensajeDni(mensaje, tipo) {
    $("#mensajeValidacionDni")
        .removeClass("d-none alert-success alert-danger")
        .addClass(`alert alert-${tipo} mt-2 mb-0`)
        .text(mensaje);
}

function obtenerColaboradoresxGrupo() {
    const dni = $("#inputDni").val().trim();

    $.ajax({
        url: `/rrhh/obtenerColaboradoresxGrupo/${DASHBOARD_TOKEN}`,
        method: "GET",
        dataType: "json",
        data: {
            dni: dni,
        },
        success: function (response) {
            if (response.success) {
                cargarSelectorGrupo("#selectorGrupo1", response.data.grupo1);
                cargarSelectorGrupo("#selectorGrupo2", response.data.grupo2);
                cargarSelectorGrupo("#selectorGrupo3", response.data.grupo3);
            } else {
                console.error(response.mensaje);
            }
        },
        error: function (xhr) {
            console.error(
                xhr.responseJSON?.mensaje ?? "Error al cargar colaboradores.",
            );
        },
    });
}

function cargarSelectorGrupo(selector, colaboradores) {
    const $select = $(selector);

    $select.empty();

    $select.append(
        $("<option>", {
            value: "",
            text: "Selecciona un/a colaborador/a",
        }),
    );

    colaboradores.forEach(function (colaborador) {
        $select.append(
            $("<option>", {
                value: colaborador.legajo,
                text: colaborador.colaborador,
            }),
        );
    });

    $select.append(
        $("<option>", {
            value: "NO_CONOZCO",
            text: "No conozco lo suficiente a este grupo",
        }),
    );
}

$(document).on("click", "#btnEnviarVotacion", function () {
    const dni = $("#inputDni").val().trim();

    const grupos = [1, 2, 3].map((numero) => ({
        grupo: numero,
        candidato: $(`#selectorGrupo${numero}`).val(),
        motivo: $(`#inputRta${numero}2`).val().trim(),
        situacion: $(`#inputRta${numero}3`).val().trim(),
    }));

    // Validación de las respuestas
    for (const grupo of grupos) {
        if (!grupo.candidato) {
            alert(`Debe seleccionar una opción en el grupo ${grupo.grupo}.`);
            return;
        }

        if (grupo.candidato !== "NO_CONOZCO") {
            if (!grupo.motivo || !grupo.situacion) {
                alert(
                    `Debe completar las respuestas del grupo ${grupo.grupo}.`,
                );
                return;
            }
        }
    }

    if (
        !confirm(
            "¿Confirmás el envío de tus respuestas? Una vez registradas no podrás modificarlas.",
        )
    ) {
        return;
    }

    const $boton = $(this);

    $boton.prop("disabled", true).html(`
        <span class="spinner-border spinner-border-sm me-2"></span>
        Enviando respuestas...
    `);

    $.ajax({
        url: `/rrhh/votaciones/emitirVoto/${DASHBOARD_TOKEN}`,
        method: "POST",
        dataType: "json",
        data: {
            dni: dni,
            id_votacion: 1,
            grupos: grupos,
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            if (response.success) {
                $("#divResponderPreguntas").addClass("d-none");

                $("#btnEnviarVotacion").parent().addClass("d-none");

                mostrarMensajeDni(
                    "¡Tu voto fue registrado correctamente! Muchas gracias por participar.",
                    "success",
                );
            } else {
                alert(response.mensaje);
            }
        },
        error: function (xhr) {
            alert(
                xhr.responseJSON?.mensaje ??
                    "Ocurrió un error al registrar las respuestas.",
            );
        },
        complete: function () {
            $boton.prop("disabled", false).html(`
                <i class="fa-solid fa-check-to-slot me-2"></i>
                Enviar respuestas
            `);
        },
    });
});
