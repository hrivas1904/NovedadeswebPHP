function getServiciosSeleccionados() {
    return $(".check-servicio:checked")
        .map(function () {
            return $(this).val();
        })
        .get();
}

function getEstadoMonoSeleccionado() {
    return $('input[name="estadoMono"]:checked').val() || null;
}

$(document).ready(function () {
    if ($("#tb_monotributistas").length > 0) {
        tablaMonotributistas = $("#tb_monotributistas").DataTable({
            ajax: {
                url: "/rrhh/personal/obtenerPersonalMonotributista",
                type: "GET",
                dataSrc: "data",
                data: function (d) {
                    d.servicio_id =
                        getServiciosSeleccionados().join(",") || null;
                    d.p_estado = getEstadoMonoSeleccionado();
                },
            },
            autoWidth: false,
            paging: false,
            scrollCollapse: true,
            scrollY: getScrollY(),
            responsive: true,
            columnDefs: [
                { responsivePriority: 1, targets: 0 },
                { responsivePriority: 2, targets: 7 },
                { responsivePriority: 3, targets: 3 },
            ],
            language: {
                url: "/js/es-ES.json",
            },
            columns: [
                { data: "colaborador", className: "text-start" },
                { data: "dni", width: "8%", className: "text-start" },
                {
                    data: "cuil",
                    width: "10%",
                    className: "text-start",
                    render: function (data) {
                        if (!data || data.length !== 11) return data || "—";
                        return (
                            data.slice(0, 2) +
                            "-" +
                            data.slice(2, 10) +
                            "-" +
                            data.slice(10)
                        );
                    },
                },
                {
                    data: "razon_social",
                    className: "text-start",
                    render: function (data) {
                        return data
                            ? data
                            : '<span class="text-muted">—</span>';
                    },
                },
                {
                    data: "servicio",
                    className: "text-start",
                    render: function (data) {
                        return data
                            ? data
                            : '<span class="text-muted">Sin servicio</span>';
                    },
                },
                {
                    data: "tipo_contrato",
                    className: "text-start",
                    visible: false,
                },
                {
                    data: "fecha_desde",
                    width: "8%",
                    className: "text-start",
                    render: function (data, type) {
                        if (!data) return "—";
                        // para ordenar usa la fecha cruda; para mostrar, dd/mm/aaaa
                        if (type !== "display") return data;
                        const [a, m, d] = data.substring(0, 10).split("-");
                        return `${d}/${m}/${a}`;
                    },
                },
                {
                    data: "estado",
                    width: "3%",
                    className: "text-center",
                    render: function (data) {
                        const clase =
                            data === "ACTIVO" ? "bg-success" : "bg-danger";
                        return `<span class="badge ${clase}">${data}</span>`;
                    },
                },
            ],
            dom: "<'my-2'rt> \
                <'d-bottom d-flex justify-content-center'i>",
        });

        $(document).on(
            "change",
            ".check-servicio, .radio-estado-mono",
            function () {
                tablaMonotributistas.ajax.reload();
            },
        );

        $("#btn-limpiar-filtros-mono").on("click", function () {
            $(".check-servicio").prop("checked", false);
            $('input[name="estadoMono"][value=""]').prop("checked", true);
            tablaMonotributistas.search("").draw();
            tablaMonotributistas.ajax.reload();
        });

        $("#searchMonotributistas").on("keyup", function () {
            tablaMonotributistas.search($(this).val()).draw();
        });
    }
});
