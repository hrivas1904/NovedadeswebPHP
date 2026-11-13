let tablaPersonal = null;

//TB PERSONAL
$(document).ready(function () {
    if ($("#tb_personal").length > 0) {
        tablaPersonal = $("#tb_personal").DataTable({
            ajax: {
                url: "/rrhh/personal/listarColaboradoresDatatable",
                type: "GET",
                dataSrc: "data",
                data: function (d) {
                    if (
                        USER_ROLE !== "Administrador/a" &&
                        USER_ROLE !== "Coordinador/a L2"
                    ) {
                        d.area_id = USER_AREA_ID;
                    } else {
                        d.area_id = getAreasSeleccionadas().join(",") || null;
                    }
                    d.categ_id = getCategoriasSeleccionadas().join(",") || null;
                    d.p_convenio =
                        getConveniosSeleccionados().join(",") || null;
                    d.p_estado = getEstadosSeleccionado();
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
                    data: "LEGAJO",
                    width: "5%",
                    className: "text-start",
                    render: function (data, type, row) {
                        return data.toString().padStart(5, "0");
                    },
                },
                { data: "COLABORADOR", width: "auto", className: "text-start" },
                { data: "DNI", width: "5%", className: "text-start" },
                { data: "AREA", width: "auto", className: "text-start" },
                { data: "CATEGORIA", width: "auto", className: "text-start" },
                {
                    data: "CONVENIO",
                    width: "auto",
                    className: "text-start",
                    visible: false,
                },
                {
                    data: "ESTADO",
                    width: "3%",
                    orderable: true,
                    className: "text-center",
                    render: function (data) {
                        let clase =
                            data === "ACTIVO" ? "bg-success" : "bg-danger";

                        return `<span class="badge ${clase}">${data}</span>`;
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

                        // SOLO COLABORADORES ACTIVOS
                        if (data.ESTADO !== "ACTIVO") {
                            return `<span class="text-muted">—</span>`;
                        }

                        // SOLO ADMIN
                        if (esAdmin) {
                            opciones += `
                                <li>
                                    <button
                                        type="button"
                                        class="dropdown-item btn-Prestamo"
                                        data-id="${data.LEGAJO}"
                                        data-nombre="${data.COLABORADOR}">
                                        <i class="fa-solid fa-file-invoice-dollar me-2"
                                        style="color: var(--color-accent-green)"></i>
                                        Registrar préstamo
                                    </button>
                                </li>

                                <li>
                                    <button
                                        type="button"
                                        class="dropdown-item btn-DarBaja"
                                        data-id="${data.LEGAJO}"
                                        data-nombre="${data.COLABORADOR}">
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
                                    <button
                                        type="button"
                                        class="dropdown-item btn-RegNovedad"
                                        data-id="${data.LEGAJO}">
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

                                <button
                                    type="button"
                                    class="btn btn-acciones"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                    title="Acciones"
                                    aria-label="Mostrar acciones">

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

        setTimeout(function () {
            if ($("#area").val() !== "" && $("#area").val() !== null) {
                tablaPersonal.ajax.reload();
            }
        }, 500);

        $("#searchPersonal").on("keyup", function () {
            let valor = $(this).val();
            tablaPersonal.search(valor).draw();
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
            const tabla = $("#tb_personal").DataTable();
            const data = tabla.row(this).data();
            if (!data) return;
            const legajo = data.LEGAJO;
            const nombre = data.COLABORADOR;
            console.log("Legajo recibido: " + legajo);
            verLegajo(legajo, nombre);
        });

        $(document).on("click", ".btn-DarBaja", function (event) {
            event.preventDefault();
            event.stopPropagation();
            const legajoColaborador = $(this).data("id");
            const nombre = $(this).data("nombre");
            console.log("Id recibido: " + legajoColaborador);
            darDeBaja(legajoColaborador, nombre);
        });

        $(document).on("click", ".btn-RegNovedad", function (event) {
            event.preventDefault();
            event.stopPropagation();
            const legajoColaborador = $(this).data("id");
            console.log("Id recibido: " + legajoColaborador);
            registrarNovedad(legajoColaborador);
        });

        $(document).on("click", ".btn-Editar", function (event) {
            event.preventDefault();
            event.stopPropagation();
            const legajoColaborador = $(this).data("id");
            const nombre = $(this).data("nombre");
            console.log("Id recibido: " + legajoColaborador);
            editEmpleados(legajoColaborador, nombre);
        });

        $(document).on("click", ".btn-Prestamo", function (event) {
            event.preventDefault();
            event.stopPropagation();
            const legajoColaborador = $(this).data("id");
            const nombre = $(this).data("nombre");
            console.log("Id recibido: " + legajoColaborador);
            modalRegistrarPrestamo(legajoColaborador, nombre);
        });
    }
});

//VER LEGAJO
function verLegajo(legajoColaborador, nombre) {
    console.log("Mostrando legajo:", legajoColaborador);
    legajoActivo = legajoColaborador;

    $("#tituloLegajo").html(`
        <i class="fa-solid fa-id-card me-2"></i>
        Legajo de <strong>${nombre}</strong>
    `);

    listarCuentasBancarias(legajoColaborador);
    cargarRequerimientosAlimentarios();
    cargarReqSeleccionados(legajoColaborador);
    cargarHijoColab(legajoColaborador);

    $.ajax({
        url: `/rrhh/personal/ver-legajo/${legajoColaborador}`,
        type: "GET",
        success: function (response) {
            if (response.success) {
                const d = response.data;

                $("#inputLegajo").val(d.LEGAJO);
                $("#inputNombre").val(d.COLABORADOR);
                $("#inputEstado").val(d.ESTADO);
                $("#inputDni").val(d.DNI);
                $("#inputCuil").val(d.CUIL);
                ($("#inputFechaNacimiento").val(d.FECHA_NAC),
                    $("#inputEdad").val(calcularEdad(d.FECHA_NAC)));
                $("#inputEmail").val(d.CORREO);
                $("#inputTelefono").val(d.TELEFONO);
                $("#inputDomicilio").val(d.DOMICILIO);
                if (d.LOCALIDAD) {
                    let option = new Option(
                        d.LOCALIDAD,
                        d.LOCALIDAD,
                        true,
                        true,
                    );
                    $("#selectLocalidadEdit").append(option).trigger("change");
                }
                $("#inputEstadoCivil").val(d.ESTADO_CIVIL);
                $("#inputGenero").val(d.GENERO);
                $("#selectObraSocialEdit").val(d.ID_OS).trigger("change");

                $("#inputCodigoOS").val(d.COD_OS);
                $("#inputTitulo").val(d.TITULO);
                $("#inputDescripTitulo").val(d.DESCRIP_TITULO);
                $("#inputMatricula").val(d.MAT_PROF);

                $("#inputTipoContrato").val(d.TIPO_CONTRATO);
                $("#inputFechaIngreso").val(d.FECHA_INGRESO);
                $("#inputFechaFinPrueba").val(
                    formatearFechaArgentina(d.FECHA_FIN_PRUEBA),
                );

                $("#inputAntiguedad").val(calcularAntiguedad(d.FECHA_INGRESO));
                $("#inputFechaEgreso").val(d.FECHA_EGRESO);
                $("#inputArea").val(d.ID_AREA);
                cargarServiciosSimple(d.ID_SERVICIOS);
                $("#inputConvenio").val(d.CONVENIO);
                $("#inputCategoria").val(d.ID_CATEG);
                cargarRolesSimple(d.ID_ROL);
                $("#inputRegimen").val(d.REGIMEN);
                $("#inputHorasDiarias").val(d.HORAS_DIARIAS);
                $("#inputCordinador").val(d.COORDINADOR);
                $("#inputAfiliado").val(d.AFILIADO);

                $("#personaEmergencia1Edit").val(d.PERSONA_EMERG1);
                $("#contactoEmergencia1Edit").val(d.CONTACTO_EMERG1);
                $("#parentescoEmergencia1Edit").val(d.PARENTESCO_EMERG1);
                $("#personaEmergencia2Edit").val(d.PERSONA_EMERG2);
                $("#contactoEmergencia2Edit").val(d.CONTACTO_EMERG2);
                $("#parentescoEmergencia2Edit").val(d.PARENTESCO_EMERG2);
                $("#padreColaboradorEdit").val(d.PADRE);
                $("#madreColaboradorEdit").val(d.MADRE);

                $("#inputNoche").val(d.NOCHE);
                $("#inputUti").val(d.UTI);

                $("#inputMotivoBaja").val(d.MOTIVO_BAJA);
                $("#inputObservBaja").val(d.OBSERV_BAJA);

                $("#modalLegajoColaborador").modal("show");

                $("#modalLegajoColaborador")
                    .off("shown.bs.modal")
                    .on("shown.bs.modal", function () {
                        inicializarORefrescarHistorial();
                    });
            } else {
                Swal.fire("Error", response.mensaje, "error");
            }
        },
        error: function () {
            Swal.fire("Error", "No se pudo cargar el legajo", "error");
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
