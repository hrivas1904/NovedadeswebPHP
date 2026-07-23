let servicio;
let turno;

$("#btnIngresarAsistencia").on("click", function () {
    turno = $("input[name='radioTurno']:checked").next("label").text().trim();
    servicio = $("input[name='radioServicio']:checked")
        .next("label")
        .text()
        .trim();

    if (!turno || !servicio){
        Swal.fire({
            title: "Atención!",
            icon: "warning",
            text: "Debe seleccionar turno y/o servicio.",
            confirmButtonColor: "#1DAC8A",
        });
        return;
    }

    navigator.geolocation.getCurrentPosition(function (position) {
        console.log(position.coords.latitude);
        console.log(position.coords.longitude);
        console.log(position.coords.accuracy);

        $.ajax({
            url: "/rrhh/asistencia/ingreso",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: {
                turno: turno,
                servicio: servicio,
                latitud: position.coords.latitude,
                longitud: position.coords.longitude,
            },
            success: function (res) {
                Swal.fire({
                    title: res.success ? "Operación exitosa" : "Atención",
                    icon: res.success ? "success" : "warning",
                    text: res.message,
                    confirmButtonColor: "#1DAC8A",
                });
            },
            error: function () {
                Swal.fire({
                    title: "Error",
                    icon: "error",
                    text: "No se pudo registrar el ingreso.",
                    confirmButtonColor: "#1DAC8A",
                });
            },
        });
    });
});

$("#btnEgresarAsistencia").on("click", function () {
    let turno = $("input[name='radioTurno']:checked")
        .next("label")
        .text()
        .trim();

    let servicio = $("input[name='radioServicio']:checked")
        .next("label")
        .text()
        .trim();

    if (!turno || !servicio){
        Swal.fire({
            title: "Atención!",
            icon: "warning",
            text: "Debe seleccionar turno y/o servicio.",
            confirmButtonColor: "#1DAC8A",
        });
        return;
    }

    navigator.geolocation.getCurrentPosition(function (position) {
        console.log(position.coords.latitude);
        console.log(position.coords.longitude);
        console.log(position.coords.accuracy);

        $.ajax({
            url: "/rrhh/asistencia/egreso",
            type: "POST",

            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },

            data: {
                turno: turno,
                servicio: servicio,
                latitud: position.coords.latitude,
                longitud: position.coords.longitude,
            },

            success: function (res) {
                Swal.fire({
                    title: res.success ? "Operación exitosa" : "Atención",
                    icon: res.success ? "success" : "warning",
                    text: res.message,
                    confirmButtonColor: "#1DAC8A",
                });
            },

            error: function (xhr) {
                console.log(xhr.responseText);

                Swal.fire({
                    title: "Error",
                    icon: "error",
                    text: "No se pudo registrar el egreso.",
                    confirmButtonColor: "#1DAC8A",
                });
            },
        });
    });
});
