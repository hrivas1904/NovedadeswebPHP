<div class="card p-1">
    <div class="d-flex flex-column gap-3">
        <form id="formNuevoPuesto">
            @csrf
            <div class="row align-items-end g-3">
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="form-floating">
                        <input type="text" class="form-control" name="nombre" id="nombre" required placeholder="Nombre">
                        <label for="nombre">Nombre</label>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="form-floating">
                        <input type="text" class="form-control" name="descripcion" id="descripcion" placeholder="Descripcion">
                        <label for="descripcion">Descripción</label>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="form-floating">
                        <input type="text" class="form-control" name="codigo" id="codigo" required placeholder="Codigo">
                        <label for="codigo">Codigo</label>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="form-floating">
                        <select type="text" class="form-select" name="area" id="selectorArea" required placeholder="Área"></select>
                        <label for="area">Área</label>
                    </div>
                </div>
                <div class="col-12 col-md-5 col-xl-3">
                    <div class="form-floating">
                        <select type="text" class="form-select" name="servicio" id="selectorServicio" required placeholder="Servicio"></select>
                        <label for="servicio">Servicio</label>
                    </div>
                </div>
                <div class="col-12 col-md-2 col-xl-1">
                    <div class="form-floating">
                        <select type="text" class="form-select" name="coordinador" id="selectorCoordinador" required placeholder="Coordinador">
                            <option value="">Seleccione opción</option>
                            <option value="1">SI</option>
                            <option value="0">NO</option>
                        </select>
                        <label for="coordinador">Es coordinador</label>
                    </div>
                </div>
                <div class="col-12 col-md-5 col-xl-3">
                    <div class="form-floating">
                        <select type="text" class="form-select" name="superior" id="selectorSuperior" required placeholder="Superior"></select>
                        <label for="superior">Superior</label>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-1">
                    <button type="submit" class="btn btn-primary w-100">Guardar</button>
                </div>
            </div>
        </form>

        <div class="table-card">
            <table class="table table-striped table-hover" id="tbPuestos">
                <thead>
                    <tr>
                        <th>NOMBRE</th>
                        <th>DESCRIPCIÓN</th>
                        <th>CÓDIGO</th>
                        <th>ÁREA</th>
                        <th>SERVICIO</th>
                        <th>ES COORDINADOR</th>
                        <th>SUPERIOR</th>
                        <th>ACTIVO</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>