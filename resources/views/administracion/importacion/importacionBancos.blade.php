<div class="card p-3" style="overflow:hidden;">
    <label class="fw-bold" style="color: var(--color-default)">
        Importar movimientos bancarios
    </label>

    <div class="my-2 p-2 justify-content-between align-items-center"
        style="
                --color-second-rgb: 0, 137, 199;
                background-color: rgba(var(--color-second-rgb), 0.15);
                border: 2px solid var(--color-default);
                border-radius: 10px;
                color: var(--color-default);
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
            ">
        <label class="fw-bold">Banco:</label>
        <button type="button" class="btn btn-outline-secondary btn-sm active btn-opcion-banco">
            MACRO
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm btn-opcion-banco">
            NACIÓN
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm btn-opcion-banco">
            FRANCÉS (986)
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm btn-opcion-banco">
            FRANCÉS (1001)
        </button>
    </div>

    <div class="my-1 p-2 justify-content-between align-items-center">
        <label class="fw-bold">Formato:</label>
        <button type="button" class="btn btn-outline-secondary btn-sm active btn-opcion-import">
            <i class="fa-solid fa-paste"></i>
            Pegado directamente del homebanking
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm btn-opcion-import">
            <i class="fa-solid fa-file-excel"></i>
            Conciliación Excel
        </button>
    </div>

    <div class="my-2 p-2 justify-content-between align-items-center" style="
                --color-second-rgb: 0, 137, 199;
                background-color: rgba(var(--color-second-rgb), 0.15);
                border: 2px solid var(--color-default);
                border-radius: 10px;
                color: var(--color-default);
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
            ">

    </div>

    <div>
        <input type="file" class="form-control">
    </div>

    <div class="mt-2">
        <textarea class="form-control" id="contenidoIvaVentas" name="contenidoIvaVentas"
            placeholder="Pegá los movimientos de MACRO..." rows="5"></textarea>
    </div>
</div>