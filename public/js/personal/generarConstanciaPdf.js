let nombreColab;
let cuilColab;
let fechaIngreso;
let servicio;

function formatearFechaLarga(fecha) {

    const meses = [
        "enero",
        "febrero",
        "marzo",
        "abril",
        "mayo",
        "junio",
        "julio",
        "agosto",
        "septiembre",
        "octubre",
        "noviembre",
        "diciembre"
    ];

    const [anio, mes, dia] = fecha.split("-");

    return `${parseInt(dia)} de ${meses[parseInt(mes) - 1]} de ${anio}`;
}

function generarConstanciaTrabajo(datos) {
    const { nombre, cuil, fechaIngreso, servicio } = datos;

    return `
        <div class="row align-items-center mb-4">
            <div class="col-6 tex-end">
                <h4 style="font-family: Cambria; color: #1DAC8A;">
                    Medicina Humana
                </h4>
            </div>

            <div class="col-6 text-end">
                <img src="${logoHp3c}" height="40" alt="Logo de la empresa">
            </div>
        </div>

        <div class="text-center fw-bolder my-4">
            <h5 style="text-decoration: underline;">CONSTANCIA DE TRABAJO</h5>
        </div>

        <div class="row d-flex justify-content-center">
            <p style="text-align: justify; line-height: 1.8;">
                    Por medio de la presente, se deja constancia de que 
                <strong>${nombre}</strong>, CUIL N° <strong>${cuil}</strong>, 
                se desempeña en relación de dependencia como <strong>${servicio}</strong> 
                en las instalaciones del Hospital Privado Tres Cerritos SRL, 
                desde el día <strong>${formatearFechaLarga(fechaIngreso)}</strong>.
            </p>

            <p style="text-align: justify; line-height: 1.8;">
                Se extiende la presente a solicitud de la persona interesada, 
                para ser presentada ante las autoridades que así lo requieran.
            </p>
        </div>

        <div class="row d-flex mt-5">
            <div class="col-4 ms-auto text-center">
                <img src="${firma}" height="60" alt="Firma de Recursos Humanos">
                <p class="justify-content-center">
                    Francisco Fernández Ovide <br/>
                    Jefe de Recursos Humanos
                </p>             
            </div>
        </div>
    `;
}

$(document).on("click", "#btnGenerarConstancia", function () {
    let html = generarConstanciaTrabajo({
        nombre: $("#inputNombre").val(),
        cuil: $("#inputCuil").val(),
        fechaIngreso: $("#inputFechaIngreso").val(),
        servicio: $("#inputServicio option:selected").text(),
    });

    $("#contenedorConstancia").removeClass("d-none").html(html);

    setTimeout(() => {
        const elemento = document.getElementById("contenedorConstancia");

        const opciones = {
            margin: 0.5,

            filename: "constancia_trabajo.pdf",

            image: {
                type: "jpeg",
                quality: 1,
            },

            html2canvas: {
                scale: 3,
                useCORS: true,
            },

            jsPDF: {
                unit: "in",
                format: "letter",
                orientation: "portrait",
            },
        };

        html2pdf().set(opciones).from(elemento).save().then(() => {
            $("#contenedorConstancia").addClass("d-none");
        });
    }, 300);
});
