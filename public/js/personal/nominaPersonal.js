let tablaPersonal = null;

//TB PERSONAL
$(document).ready(function () {
    if ($("#tb_personal").length > 0) {
        tablaPersonal = $("#tb_personal").DataTable({
            ajax: {
                url: "/rrhh/personal/obtenerPersonalDependencia",
                type: "GET",
                dataSrc: "data",
                data: function (d) {
                    d.area_id = null;
                    d.categ_id = null;
                    d.p_convenio = null;
                    d.p_estado = null;
                },
            },
            autoWidth: false,
            scrollX: false,
            paging: false,
            scrollCollapse: true,
            scrollY: getScrollY(),
            responsive: true,
            columnDefs: [
                { responsivePriority: 1, targets: 1 },
                { responsivePriority: 2, targets: 0 },
                { responsivePriority: 3, targets: 2 },
                { responsivePriority: 4, targets: 7 },
                { responsivePriority: 100, targets: 5 },
                { responsivePriority: 100, targets: 6 },
                { responsivePriority: 100, targets: 4 },
            ],
            language: {
                url: "/js/es-ES.json",
            },
            columns: [
                {
                    data: "legajo",
                    width: "5%",
                    className: "text-start",
                    render: function (data) {
                        return data ? data.toString().padStart(5, "0") : "—";
                    },
                },
                { data: "colaborador", width: "auto", className: "text-start" },
                { data: "dni", width: "5%", className: "text-start" },
                {
                    data: "area",
                    width: "auto",
                    className: "text-start",
                    render: function (data) {
                        return data
                            ? data
                            : '<span class="text-muted">Sin área</span>';
                    },
                },
                {
                    data: "categoria",
                    width: "auto",
                    className: "text-start",
                    render: function (data) {
                        return data
                            ? data
                            : '<span class="text-muted">—</span>';
                    },
                },
                {
                    data: "convenio",
                    width: "auto",
                    className: "text-start",
                    visible: false,
                },
                {
                    data: "estado",
                    width: "3%",
                    orderable: true,
                    className: "text-center",
                    render: function (data) {
                        const clases = {
                            ACTIVO: "bg-success",
                            "PERIODO DE PRUEBA": "bg-warning text-dark",
                            BAJA: "bg-danger",
                        };
                        return `<span class="badge ${clases[data] || "bg-secondary"}">${data}</span>`;
                    },
                },
                {
                    data: null,
                    className: "text-center acciones-nowrap",
                    width: "5%",
                    orderable: false,
                    render: function (data) {
                        let opciones = "";

                        const esAdmin = USER_ROLE === "Administrador/a";

                        const esCoordinador = [
                            "Administrador/a",
                            "Coordinador/a",
                            "Coordinador/a L2",
                        ].includes(USER_ROLE);

                        // sin acciones para bajas
                        if (data.estado === "BAJA") {
                            return `<span class="text-muted">—</span>`;
                        }

                        // legajo (lo que usan préstamos/novedades) + ids del modelo nuevo
                        const attrs = `
                            data-id="${data.legajo}"
                            data-id-personal="${data.id_personal}"
                            data-id-vinculo="${data.id_vinculo}"
                            data-nombre="${$("<div>").text(data.colaborador).html()}"`;

                        // SOLO ADMIN
                        if (esAdmin) {
                            opciones += `
                                <li>
                                    <button type="button" class="dropdown-item btn-Prestamo" ${attrs}>
                                        <i class="fa-solid fa-file-invoice-dollar me-2"
                                           style="color: var(--color-accent-green)"></i>
                                        Registrar préstamo
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item btn-DarBaja" ${attrs}>
                                        <i class="fa-regular fa-trash-can me-2"
                                           style="color: var(--color-accent-red)"></i>
                                        Dar de baja
                                    </button>
                                </li>
                            `;
                        }

                        // ADMIN + COORDINADORES
                        if (esCoordinador) {
                            opciones += `
                                <li>
                                    <button type="button" class="dropdown-item btn-RegNovedad" ${attrs}>
                                        <i class="fa-solid fa-floppy-disk me-2"
                                           style="color: var(--color-default)"></i>
                                        Cargar novedad
                                    </button>
                                </li>
                            `;
                        }

                        if (!opciones.trim()) {
                            return `<span class="text-muted">—</span>`;
                        }

                        return `
                            <div class="dropdown d-inline-block">
                                <button type="button" class="btn btn-acciones"
                                        data-bs-toggle="dropdown" aria-expanded="false"
                                        title="Acciones" aria-label="Mostrar acciones">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    ${opciones}
                                </ul>
                            </div>
                        `;
                    },
                },
            ],
            dom: "<'my-2'rt> \
                <'d-bottom d-flex justify-content-center'i>",
        });

        $(document).on(
            "change",
            ".check-area, .check-categ, .check-convenio, .radio-estado",
            function () {
                tablaPersonal.ajax.reload();
            },
        );

        $("#btn-limpiar-filtros").on("click", function () {
            $(".check-area, .check-categ, .check-convenio").prop(
                "checked",
                false,
            );
            $('input[name="estado"][value=""]').prop("checked", true);
            $("#toggleAreas span").text("Áreas");
            $("#toggleCateg span").text("Categorías");
            $("#toggleConvenios span").text("Convenios");
            $("#toggleEstados span").text("Estados");
            tablaPersonal.search("").draw();
            tablaPersonal.ajax.reload();
        });

        $("#searchPersonal").on("keyup", function () {
            tablaPersonal.search($(this).val()).draw();
        });

        $("#btnClearSearch").on("click", function () {
            $("#searchPersonal").val("");
            tablaPersonal.search("").draw();
        });

        $(document).on("click", "#tb_personal tbody tr", function (event) {
            if ($(event.target).closest(".dropdown").length) {
                return;
            }
            event.preventDefault();
            event.stopPropagation();
            const data = tablaPersonal.row(this).data();
            if (!data) return;
            verLegajo(data.id_personal, data.colaborador);
        });

        $(document).on("click", ".btn-DarBaja", function (event) {
            event.preventDefault();
            event.stopPropagation();
            darDeBaja($(this).data("id"), $(this).data("nombre"));
        });

        $(document).on("click", ".btn-RegNovedad", function (event) {
            event.preventDefault();
            event.stopPropagation();
            registrarNovedad($(this).data("id"));
        });

        $(document).on("click", ".btn-Editar", function (event) {
            event.preventDefault();
            event.stopPropagation();
            editEmpleados($(this).data("id"), $(this).data("nombre"));
        });

        $(document).on("click", ".btn-Prestamo", function (event) {
            event.preventDefault();
            event.stopPropagation();
            modalRegistrarPrestamo($(this).data("id"), $(this).data("nombre"));
        });
    }
});

//VER LEGAJO
function formatoLegajo(valor, formato) {
    if (valor === null || valor === undefined || String(valor).trim() === "")
        return null;
    if (formato === "fecha") {
        const [a, m, d] = String(valor).substring(0, 10).split("-");
        return `${d}/${m}/${a}`;
    }
    if (formato === "cuil" && String(valor).length === 11) {
        const c = String(valor);
        return `${c.slice(0, 2)}-${c.slice(2, 10)}-${c.slice(10)}`;
    }
    if (formato === "sino") return Number(valor) === 1 ? "SI" : "NO";
    return String(valor);
}

function llenarModalLegajo(res) {
    const d = res.data;
    const $modal = $("#modalVerLegajo");

    // campos simples
    $modal.find("[data-campo]").each(function () {
        const $el = $(this);
        let texto = formatoLegajo(d[$el.data("campo")], $el.data("formato"));
        if (texto !== null && $el.data("sufijo")) texto += $el.data("sufijo");
        $el.text(texto === null ? "Sin datos" : texto).toggleClass(
            "vacio",
            texto === null,
        );
    });

    // cabecera
    $("#legajoNumero").text(
        d.legajo
            ? "Legajo " + String(d.legajo).padStart(5, "0")
            : d.tipo_vinculo || "Sin vínculo",
    );
    const colores = {
        ACTIVO: "bg-success",
        "PERIODO DE PRUEBA": "bg-warning text-dark",
        BAJA: "bg-danger",
    };
    $("#legajoEstado")
        .attr("class", "badge " + (colores[d.estado] || "bg-secondary"))
        .text(d.estado);
    $("#legajoResumen").text(
        [d.puesto, d.area, d.servicio].filter(Boolean).join(" · "),
    );

    // foto o iniciales
    const $foto = $("#legajoFoto");
    if (d.url_foto_legajo) {
        $foto
            .css("background-image", `url('/storage/${d.url_foto_legajo}')`)
            .text("");
    } else {
        $foto
            .css("background-image", "none")
            .text((d.apellido || "?").charAt(0) + (d.nombre || "").charAt(0));
    }

    // bloques según tipo de vínculo y estado
    const esDep = d.tipo_vinculo === "DEPENDENCIA";
    $modal.find(".solo-dependencia").toggleClass("d-none", !esDep);
    $modal
        .find(".solo-externo")
        .toggleClass("d-none", esDep || !d.tipo_vinculo);
    $("#legajoBloqueBaja").toggleClass("d-none", d.estado !== "BAJA");

    // tablas
    const fila = (celdas) =>
        $("<tr>").append(celdas.map((c) => $("<td>").text(c ?? "—")));
    const vacia = (cols, texto) =>
        $("<tr>").append(
            $(`<td colspan="${cols}" class="text-muted fst-italic">`).text(
                texto,
            ),
        );

    const $cont = $("#legajoTbContactos").empty();
    (res.contactos || []).forEach((c) =>
        $cont.append(fila([c.nombre, c.parentesco, c.telefono])),
    );
    if (!(res.contactos || []).length)
        $cont.append(vacia(3, "Sin contactos cargados"));

    const $fam = $("#legajoTbFamiliares").empty();
    (res.familiares || []).forEach((f) =>
        $fam.append(
            fila([
                f.nombre,
                f.parentesco,
                f.dni,
                formatoLegajo(f.fecha_nacimiento, "fecha"),
                f.edad,
            ]),
        ),
    );
    if (!(res.familiares || []).length)
        $fam.append(vacia(5, "Sin familiares cargados"));

    const $his = $("#legajoTbHistorial").empty();
    (res.tramos || []).forEach((t) =>
        $his.append(
            fila([
                formatoLegajo(t.desde, "fecha"),
                t.hasta ? formatoLegajo(t.hasta, "fecha") : "Vigente",
                t.tipo_vinculo + (t.legajo ? " #" + t.legajo : ""),
                t.puesto,
                t.area,
                t.servicio,
                t.categoria,
                t.regimen ? t.regimen + " hs" : null,
            ]),
        ),
    );
    if (!(res.tramos || []).length) $his.append(vacia(8, "Sin historial"));

    // siempre abre en la primera pestaña
    bootstrap.Tab.getOrCreateInstance(
        $modal.find('[data-bs-target="#tabLegajoPersonales"]')[0],
    ).show();
    $modal.modal("show");
}

function verLegajo(idPersonal) {
    $.ajax({
        url: "/rrhh/personal/verLegajo",
        type: "GET",
        dataType: "json",
        data: { p_id_personal: idPersonal },
        success: function (res) {
            llenarModalLegajo(res);
        },
        error: function (xhr) {
            const msg =
                (xhr.responseJSON &&
                    (xhr.responseJSON.message || xhr.responseJSON.mensaje)) ||
                "No se pudo cargar el legajo.";
            Swal.fire("Error", msg, "error");
        },
    });
}

//VER COMPROBANTES DE NOVEDAD
$(document).on("click", ".btn-ver-archivo", function (e) {
    e.preventDefault();
    e.stopPropagation();

    const idNovedad = $(this).data("registro");
    verComprobantes(idNovedad);
});

function verComprobantes(idNovedad) {
    $.ajax({
        url: `/rrhh/novedades/${idNovedad}/comprobantes`,
        method: "GET",
        dataType: "json",
        success: function (response) {
            const contenedor = $("#contenedorComprobantes");
            contenedor.html("");

            if (!response || response.length === 0) {
                contenedor.html(
                    `<div class="alert alert-info">
                        Esta novedad no tiene comprobantes.
                    </div>`,
                );
            } else {
                response.forEach((comp) => {
                    const url = `/rrhh/comprobantes/${comp.id}/ver`;

                    contenedor.append(`
                        <div class="mb-4 border rounded p-2">
                            <iframe
                                src="${url}"
                                style="width:100%; height:600px; border:none;"
                                loading="lazy">
                            </iframe>
                        </div>
                    `);
                });
            }

            const modal = new bootstrap.Modal(
                document.getElementById("modalVerComprobantes"),
            );
            modal.show();
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            Swal.fire(
                "Error",
                "No se pudieron cargar los comprobantes",
                "error",
            );
        },
    });
}
