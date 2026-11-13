let selector = null;

function obtenerAreasSelector(idSelector) {
    $.ajax({
        url: "/rrhh/obtenerAreasSelector",
        type: "GET",
        dateType: "JSON",

        success: function (res) {
            selector = idSelector;
            $.each(res.data, function (index, item) {
                selector.append(
                    `
                        <option value="${item.id_area}">${item.nombre}<option/>
                    `,
                );
            });
        },
    });
}
