document.addEventListener('DOMContentLoaded', () => {

    const selectCategoria = document.getElementById('selectCategoria');
    const selectNovedad = document.getElementById('selectNovedad');
    const inputCodigo = document.getElementById('codigoFinnegans');

    cargarCategorias();

    selectCategoria.addEventListener('change', () => {
        const idCategoria = selectCategoria.value;

        selectNovedad.innerHTML = '<option value="">Seleccionar novedad</option>';
        selectNovedad.disabled = true;
        inputCodigo.value = '';

        if (!idCategoria) return;

        fetch(`/novedades/lista/${idCategoria}`)
            .then(res => res.json())
            .then(data => {
                if (!Array.isArray(data)) return;

                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.CODIGO_NOVEDAD;
                    option.textContent = item.NOMBRE;
                    option.dataset.codigo = item.CODIGO_NOVEDAD;

                    selectNovedad.appendChild(option);
                });

                selectNovedad.disabled = false;
            });
    });

    selectNovedad.addEventListener('change', () => {
        const selected = selectNovedad.options[selectNovedad.selectedIndex];
        inputCodigo.value = selected.dataset.codigo || '';
    });
});

function cargarCategorias() {
    fetch('/categorias-novedad/lista')
        .then(res => res.json())
        .then(data => {
            const select = document.getElementById('selectCategoria');

            data.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.ID_CATEG;
                option.textContent = cat.NOMBRE;
                select.appendChild(option);
            });
        });
}
