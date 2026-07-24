<div class="card p-3" style="overflow:hidden;">
    <label class="fw-bold" style="color: var(--color-default)">
        Importar movimientos de Caja
    </label>

    <div class="my-2 p-2 justify-content-between align-items-center" style="
                --color-second-rgb: 0, 137, 199;
                background-color: rgba(var(--color-second-rgb), 0.15);
                border: 2px solid var(--color-default);
                border-radius: 10px;
                color: var(--color-default);
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);">
    </div>

    <div>
        <input type="file" class="form-control">
    </div>

    <div class="mt-2">
        <textarea class="form-control" id="contenidoIvaVentas" name="contenidoIvaVentas"
            placeholder="Pegá las filas de caja..." rows="5"></textarea>
    </div>
</div>