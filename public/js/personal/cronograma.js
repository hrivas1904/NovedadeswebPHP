document.addEventListener("DOMContentLoaded", () => {
    inicializarCronograma();
});

new datatable('#tb_cronograma', {
    screenX: true
});

function inicializarCronograma() {
    const filas = document.querySelectorAll(".cronograma-table tbody tr");

    filas.forEach(fila => {
        recalcularTotalFila(fila);

        const inputs = fila.querySelectorAll(".day-cell");
        inputs.forEach(input => {
            input.addEventListener("input", () => {
                normalizarCelda(input);
                recalcularTotalFila(fila);
            });

            input.addEventListener("dblclick", () => {
                abrirModalObservaciones(input);
            });
        });
    });
}

function recalcularTotalFila(fila) {
    let total = 0;
    const inputs = fila.querySelectorAll(".day-cell");

    inputs.forEach(input => {
        const val = input.value.trim().toUpperCase();

        if (!isNaN(val) && val !== "") {
            total += parseFloat(val);
        }
    });

    const totalSpan = fila.querySelector(".total-horas");
    if (totalSpan) {
        totalSpan.textContent = total.toFixed(1);
    }
}

function normalizarCelda(input) {
    let val = input.value.trim().toUpperCase();

    const codigosPermitidos = ["VAC", "FR", "GU", ""];

    if (codigosPermitidos.includes(val)) {
        input.value = val;
        return;
    }

    if (!isNaN(val)) {
        input.value = val;
        return;
    }

    input.value = "";
}

function abrirModalObservaciones(input) {
    const fechaCol = input.closest("td").cellIndex;
    const empleado = input.closest("tr").querySelector(".col-nombre").innerText;

    alert(
        `Observaciones\n\nEmpleado: ${empleado}\nDía columna: ${fechaCol}\n\n(Aquí luego va el modal real)`
    );
}
