const CSRF_CRONO_PICKER = $('meta[name="csrf-token"]').attr("content");
let slotSeleccionado = null;
let debouncePicker = null;

$(document).on("click", ".slot-chip", function () {
    const ocupado = !$(this).hasClass("bg-light");
    slotSeleccionado = { id: $(this).data("slot-id") };

    $("#divOcupanteActual").html(
        ocupado
            ? `<div class="alert alert-secondary d-flex justify-content-between align-items-center py-2 mb-0">
             <span>Actualmente: <strong>${$(this).text().trim()}</strong></span>
             <button class="btn btn-sm btn-outline-danger" id="btnQuitarOcupante">Quitar</button>
           </div>`
            : `<div class="alert alert-light py-2 mb-0">Puesto vacante.</div>`,
    );

    $("#inputBuscarPersona").val("");
    $("#divResultadosBusqueda").empty();
    $("#modalPickerPersona").modal("show");
});

$(document).on("click", "#btnQuitarOcupante", function () {
    Swal.fire({
        title: "¿Quitar a esta persona del puesto?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Quitar",
    }).then((r) => {
        if (!r.isConfirmed) return;
        $.ajax({
            url: RUTAS_CRONO_GRILLA.quitarSlot,
            type: "POST",
            data: { _token: CSRF_CRONO_PICKER, slot_id: slotSeleccionado.id },
        }).done((resp) => {
            if (resp.success) {
                $("#modalPickerPersona").modal("hide");
                cargarGrilla();
            }
        });
    });
});

$(document).on("input", "#inputBuscarPersona", function () {
    const texto = $(this).val().trim();
    clearTimeout(debouncePicker);
    if (texto.length < 2) {
        $("#divResultadosBusqueda").empty();
        return;
    }

    debouncePicker = setTimeout(() => {
        $.get(
            RUTAS_CRONO_GRILLA.buscarEmpleados,
            { periodo: $("#selectorMesCrono").val(), texto },
            function (resp) {
                renderResultadosBusqueda(resp.data);
            },
        );
    }, 300);
});

function renderResultadosBusqueda(resultados) {
    const $cont = $("#divResultadosBusqueda");
    if (!resultados.length) {
        $cont.html('<p class="text-muted small mb-0">Sin resultados.</p>');
        return;
    }

    $cont.html(
        resultados
            .map(
                (r) => `
        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
          <div>
            <div>${r.COLABORADOR} <span class="text-muted small">(legajo ${r.LEGAJO})</span></div>
            <div class="small text-muted">${r.area_nombre || ""}${r.servicio_nombre ? " · " + r.servicio_nombre : ""}</div>
            ${r.asignado_slot_id ? `<div class="small text-danger">Ya asignado en ${r.asignado_area} · ${r.asignado_turno}</div>` : ""}
          </div>
          <button class="btn btn-sm btn-primary btn-elegir-persona" data-legajo="${r.LEGAJO}">Asignar</button>
        </div>
    `,
            )
            .join(""),
    );
}

$(document).on("click", ".btn-elegir-persona", function () {
    $.post(RUTAS_CRONO_GRILLA.asignarSlot, {
        _token: CSRF_CRONO_PICKER,
        slot_id: slotSeleccionado.id,
        legajo: $(this).data("legajo"),
        rol: $("#selRolAsignacion").val(),
    })
        .done((resp) => {
            if (resp.success) {
                $("#modalPickerPersona").modal("hide");
                cargarGrilla();
            }
        })
        .fail((xhr) =>
            Swal.fire(
                "Atención",
                xhr.responseJSON?.message || "No se pudo asignar.",
                "warning",
            ),
        );
});
