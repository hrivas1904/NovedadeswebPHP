let tablaAsistencia = null;

function verAsistencia() {
    if ($.fn.DataTable.isDataTable("#tbAsistencias")) {
        tablaAsistencia.ajax.reload(null, false);
    } else {
        tablaAsistencia = $("#tbAsistencias").DataTable({
            ajax: {
                url: "/rrhh/listarAsistencias",
                type: "GET",
                dataSrc: "data",
            },

            processing: true,
            responsive: true,
            autoWidth: false,
            scrollX: true,
            pageLength: 10,
            order: [[0, "desc"]],

            layout: {
                topStart: {
                    buttons: [
                        {
                            extend: "excelHtml5",
                            text: '<i class="bi bi-file-earmark-excel"></i> Exportar Excel',
                            className: "btn btn-success btn-sm",
                            title: "Confirmaciones de asistencia",
                            filename: "Confirmaciones_Asistencia_HP3C",
                            exportOptions: {
                                columns: ":visible",
                                orthogonal: "export",
                            },
                        },
                    ],
                },
            },

            columns: [
                {
                    title: "Confirmación",
                    data: "id_confirmacion",
                    visible: false,
                },

                {
                    title: "Titular",
                    data: "titular",
                    render: function (titular, type) {
                        if (!titular) return "-";

                        if (type !== "display") {
                            return titular.nombre;
                        }

                        return `
                            <strong>${escaparHTML(titular.nombre)}</strong>
                            <div class="small text-muted">
                                DNI: ${escaparHTML(titular.dni)}
                            </div>
                            <div class="small text-muted">
                                ${escaparHTML(titular.sector)}
                            </div>
                        `;
                    },
                },

                {
                    title: "Acompañantes",
                    data: "acompanantes",
                    render: function (acompanantes, type) {
                        if (!acompanantes?.length) {
                            return type === "display"
                                ? '<span class="text-muted">Sin acompañantes</span>'
                                : "";
                        }

                        if (type !== "display") {
                            return acompanantes.map((a) => a.nombre).join(", ");
                        }

                        return acompanantes
                            .map(
                                (a) => `
                            <div class="mb-1">
                                • ${escaparHTML(a.nombre)}
                            </div>
                        `,
                            )
                            .join("");
                    },
                },

                {
                    title: "Requerimientos",
                    data: null,
                    render: function (data, type) {
                        const personas = [
                            ...(data.titular ? [data.titular] : []),
                            ...data.acompanantes,
                        ];

                        if (type !== "display") {
                            return personas
                                .map(
                                    (p) =>
                                        `${p.nombre}: ${p.requerimientos.join(", ")}`,
                                )
                                .join(" | ");
                        }

                        return personas
                            .map((persona) => {
                                const requerimientos = persona.requerimientos
                                    .length
                                    ? persona.requerimientos.join(", ")
                                    : "No informado";

                                return `
                                <div class="mb-1">
                                    <strong>${escaparHTML(persona.nombre)}:</strong>
                                    ${escaparHTML(requerimientos)}
                                </div>
                            `;
                            })
                            .join("");
                    },
                },

                {
                    title: "Canción",
                    data: "cancion",
                    defaultContent: "",
                    render: function (data, type) {
                        const cancion = data || "No especificada";

                        if (type !== "display") {
                            return cancion;
                        }

                        return `
                            <span class="text-wrap">
                                ${escaparHTML(cancion)}
                            </span>
                        `;
                    },
                },

                {
                    title: "Transporte",
                    data: "transporte",
                    render: function (data) {
                        return escaparHTML(data || "-");
                    },
                },

                {
                    title: "Fecha",
                    data: "fecha_confirmacion",
                    render: function (data, type) {
                        if (!data) return "-";

                        if (type !== "display") {
                            return data;
                        }

                        const fecha = new Date(data.replace(" ", "T"));

                        return fecha.toLocaleString("es-AR");
                    },
                },
            ],

            language: {
                url: "/js/es-ES.json",
            },
        });
    }

    const modal = bootstrap.Modal.getOrCreateInstance(
        document.getElementById("modalAsistencia"),
    );

    modal.show();

    $("#modalAsistencia").one("shown.bs.modal", function () {
        tablaAsistencia.columns.adjust();
    });
}

// Evitar inyección HTML en datos ingresados por usuarios.
function escaparHTML(valor) {
    return String(valor ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
