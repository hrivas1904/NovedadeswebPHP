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
                    d.area_id = getAreasSeleccionadas() || null;
                    d.categ_id = getCategoriasSeleccionadas() || null;
                    d.p_convenio = getConveniosSeleccionados || null;
                    d.p_estado = getEstadosSeleccionado() || null;
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

