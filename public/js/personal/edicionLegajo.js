/* =====================================================================
   HP3C · RRHH · Edición del legajo campo por campo (on change)
   ---------------------------------------------------------------------
   Cada campo se guarda al cambiarlo (texto: al salir del campo; selects:
   al elegir). Si el servidor rechaza el cambio, el campo vuelve al valor
   guardado.

   Requiere, en llenarModalLegajo (administrarLegajo.js), al final:
       setSnapshotLegajo(d);

   No incluye: puesto, UTI y NOCHE (ya tienen su propio on change),
   contactos y familiares (se ven aparte).
   ===================================================================== */

let snapshotLegajo = null; // último estado guardado del legajo abierto

/* ---------------------------------------------------------------------
   Configuración de campos
   tabla: 'personal' o 'tramo' · campo: columna en la base
   ui:    selector del control en el modal
   --------------------------------------------------------------------- */
const CAMPOS_LEGAJO = [
    // personal
    { ui: '#formLegajo [name="nombre"]', tabla: "personal", campo: "nombre" },
    {
        ui: '#formLegajo [name="apellido"]',
        tabla: "personal",
        campo: "apellido",
    },
    { ui: '#formLegajo [name="dni"]', tabla: "personal", campo: "dni" },
    {
        ui: '#formLegajo [name="fechaNacimiento"]',
        tabla: "personal",
        campo: "fecha_nacimiento",
        tipo: "fecha",
    },
    {
        ui: '#formLegajo [name="telefono"]',
        tabla: "personal",
        campo: "telefono",
    },
    {
        ui: '#formLegajo [name="correoElectronico"]',
        tabla: "personal",
        campo: "correo",
    },
    {
        ui: '#formLegajo [name="domicilio"]',
        tabla: "personal",
        campo: "domicilio",
    },
    {
        ui: "#selectLocalidadLegajo",
        tabla: "personal",
        campo: "localidad",
        tipo: "select2",
    },
    {
        ui: '#formLegajo [name="estadoCivil"]',
        tabla: "personal",
        campo: "estado_civil",
    },
    { ui: '#formLegajo [name="genero"]', tabla: "personal", campo: "genero" },
    { ui: '#formLegajo [name="titulo"]', tabla: "personal", campo: "titulo" },
    {
        ui: '#formLegajo [name="descripcionTitulo"]',
        tabla: "personal",
        campo: "descripcion_titulo",
    },
    {
        ui: '#formLegajo [name="matricula"]',
        tabla: "personal",
        campo: "matricula",
    },
    // tramo
    { ui: "#selectorAreaLegajo", tabla: "tramo", campo: "id_area" },
    { ui: "#selectorServicioLegajo", tabla: "tramo", campo: "id_servicio" },
    { ui: "#selectorCategoriaLegajo", tabla: "tramo", campo: "id_categoria" },
    {
        ui: "#selectorRegimenLegajo",
        tabla: "tramo",
        campo: "regimen",
        tipo: "regimen",
    },
    {
        ui: "#selectorTipoContratoDepLegajo",
        tabla: "tramo",
        campo: "tipo_contrato",
        snap: "tipo_contrato_dep",
    },
    {
        ui: "#selectorObrasSocialesLegajo",
        tabla: "tramo",
        campo: "id_obra_social",
        tipo: "select2",
    },
    { ui: '#formLegajo [name="convenio"]', tabla: "tramo", campo: "convenio" },
    {
        ui: '#formLegajo [name="sindicato"]',
        tabla: "tramo",
        campo: "afiliado",
        tipo: "sino",
    },
];

const UI_CUIL = ".preCuilLegajo, .posCuilLegajo";

/* ---------------------------------------------------------------------
   Snapshot: lo llama llenarModalLegajo al terminar de cargar
   --------------------------------------------------------------------- */
function setSnapshotLegajo(d) {
    snapshotLegajo = Object.assign({}, d);
    CAMPOS_LEGAJO.forEach((c) => $(c.ui).prop("disabled", false));
    $(UI_CUIL).prop("disabled", false);
    $("#btnGuardarLegajo").addClass("d-none");
}

/* valor actual del control, normalizado como se guarda en la base */
function valorUI(cfg) {
    const $el = $(cfg.ui);
    if (cfg.tipo === "regimen") {
        const texto = $el.find("option:selected").text().trim();
        return /^\d+$/.test(texto) ? texto : ""; // horas semanales
    }
    if (cfg.tipo === "sino")
        return $el.val() === "SI" ? "1" : $el.val() === "NO" ? "0" : "";
    return String($el.val() ?? "").trim();
}

/* valor guardado en el snapshot, normalizado igual */
function valorSnapshot(cfg) {
    let v = snapshotLegajo ? snapshotLegajo[cfg.snap || cfg.campo] : null;
    if (v === null || v === undefined) return "";
    if (cfg.tipo === "fecha") return String(v).substring(0, 10);
    if (cfg.tipo === "sino") return String(Number(v));
    return String(v).trim();
}

/* vuelve el control al valor guardado */
function revertirUI(cfg) {
    const $el = $(cfg.ui);
    const v = valorSnapshot(cfg);
    if (cfg.tipo === "regimen") {
        const $opt = $el.find("option").filter(function () {
            return $(this).text().trim() === v;
        });
        $el.val($opt.length ? $opt.val() : "");
        $("#inputHorasDiariasLegajo").val(
            snapshotLegajo ? (snapshotLegajo.horas_diarias ?? "") : "",
        );
    } else if (cfg.tipo === "sino") {
        $el.val(v === "1" ? "SI" : v === "0" ? "NO" : "");
    } else {
        $el.val(v);
    }
    if ($el.hasClass("select2-hidden-accessible"))
        $el.trigger("change.select2");
}

/* ---------------------------------------------------------------------
   Guardado
   --------------------------------------------------------------------- */
function guardarCampoLegajo(tabla, campo, valor, extra) {
    return $.ajax({
        url: "/rrhh/personal/actualizarCampoLegajo",
        type: "POST",
        dataType: "json",
        data: Object.assign(
            {
                tabla: tabla,
                campo: campo,
                valor: valor,
                idPersonal: $("#hiddenIdPersonalLegajo").val(),
                idTramo: $("#hiddenIdTramoLegajo").val(),
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            extra || {},
        ),
    });
}

function avisoGuardado(texto) {
    Swal.fire({
        toast: true,
        position: "top-end",
        icon: "success",
        title: texto || "Guardado",
        timer: 1200,
        showConfirmButton: false,
    });
}

function avisoError(xhr) {
    const r = xhr.responseJSON || {};
    let msg = "No se pudo guardar el cambio.";
    if (r.errors) msg = Object.values(r.errors)[0][0];
    else if (r.message) msg = r.message;
    Swal.fire("Error", msg, "error");
}

function refrescarCabeceraLegajo() {
    if (!snapshotLegajo) return;
    $("#legajoTitulo").text(
        [snapshotLegajo.apellido, snapshotLegajo.nombre]
            .filter(Boolean)
            .join(" "),
    );
    if (typeof tablaPersonal !== "undefined" && tablaPersonal)
        tablaPersonal.ajax.reload(null, false);
}

/* ---------------------------------------------------------------------
   Handler genérico
   --------------------------------------------------------------------- */
function onCambioCampoLegajo(cfg) {
    if (!snapshotLegajo) return;

    const $el = $(cfg.ui);
    const nuevo = valorUI(cfg);
    if (nuevo === valorSnapshot(cfg)) return; // sin cambios (o carga del modal)

    if (cfg.tabla === "tramo" && !$("#hiddenIdTramoLegajo").val()) {
        Swal.fire(
            "Atención",
            "Este legajo no tiene un tramo de dependencia para editar.",
            "warning",
        );
        revertirUI(cfg);
        return;
    }

    const extra = {};
    if (cfg.tipo === "regimen") {
        extra.horas = $el.find("option:selected").attr("data-horas") || "";
    }

    $el.prop("disabled", true);

    guardarCampoLegajo(cfg.tabla, cfg.campo, nuevo, extra)
        .done(function (res) {
            // actualizar snapshot
            const clave = cfg.snap || cfg.campo;
            snapshotLegajo[clave] = cfg.tipo === "sino" ? Number(nuevo) : nuevo;
            if (cfg.tipo === "regimen")
                snapshotLegajo.horas_diarias = extra.horas;
            if (res && res.valor !== undefined && res.valor !== null) {
                snapshotLegajo[clave] = res.valor; // p. ej. nombre en mayúsculas
                $el.val(res.valor);
            }

            // al cambiar el área, el servicio puede haberse limpiado
            if (cfg.campo === "id_area" && res && res.servicio_limpiado) {
                snapshotLegajo.id_servicio = null;
                $("#selectorServicioLegajo").val("");
                Swal.fire(
                    "Atención",
                    "El servicio anterior no pertenece a la nueva área y se quitó. Elegí el servicio correspondiente.",
                    "info",
                );
            } else {
                avisoGuardado();
            }

            if (
                cfg.campo === "nombre" ||
                cfg.campo === "apellido" ||
                cfg.tabla === "tramo"
            )
                refrescarCabeceraLegajo();
            if (
                cfg.campo === "fecha_nacimiento" &&
                typeof aniosDesde === "function"
            ) {
                $("#inputEdadLegajo").val(aniosDesde(nuevo));
            }

            // el DNI arrastra el CUIL (la parte central es el DNI)
            if (cfg.campo === "dni") onCambioCuilLegajo();
        })
        .fail(function (xhr) {
            revertirUI(cfg);
            if (cfg.campo === "dni" && typeof cargarCuilLegajo === "function")
                cargarCuilLegajo(snapshotLegajo.cuil);
            avisoError(xhr);
        })
        .always(function () {
            $el.prop("disabled", false);
        });
}

/* CUIL: se guarda solo cuando está completo (11 dígitos) */
function onCambioCuilLegajo() {
    if (!snapshotLegajo) return;
    if (typeof armarCuilLegajo === "function") armarCuilLegajo();

    const cuil = String($(".inputCuilLegajo").val() || "");
    if (cuil === String(snapshotLegajo.cuil || "")) return;
    if (!/^\d{11}$/.test(cuil)) return; // incompleto: se guarda cuando lo completen

    $(UI_CUIL).prop("disabled", true);

    guardarCampoLegajo("personal", "cuil", cuil)
        .done(function () {
            snapshotLegajo.cuil = cuil;
            avisoGuardado("CUIL guardado");
        })
        .fail(function (xhr) {
            if (typeof cargarCuilLegajo === "function")
                cargarCuilLegajo(snapshotLegajo.cuil);
            avisoError(xhr);
        })
        .always(function () {
            $(UI_CUIL).prop("disabled", false);
        });
}

/* ---------------------------------------------------------------------
   Enganche de eventos (delegados: el modal se reutiliza)
   --------------------------------------------------------------------- */
CAMPOS_LEGAJO.forEach(function (cfg) {
    $(document).on("change", cfg.ui, function () {
        onCambioCampoLegajo(cfg);
    });
});

$(document).on("change", UI_CUIL, onCambioCuilLegajo);

// Enter en un campo de texto: guarda (dispara el change) en lugar de enviar el formulario
$(document).on(
    "keydown",
    "#formLegajo input[type='text'], #formLegajo input[type='email']",
    function (e) {
        if (e.key === "Enter") {
            e.preventDefault();
            $(this).trigger("blur");
        }
    },
);

// el formulario ya no se envía entero
$(document).on("submit", "#formLegajo", function (e) {
    e.preventDefault();
});

// al cerrar el modal, se descarta el snapshot
$(document).on("hidden.bs.modal", "#modalVerLegajo", function () {
    snapshotLegajo = null;
});
