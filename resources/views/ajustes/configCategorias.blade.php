<div class="card p-1">
    <div class="d-flex flex-column gap-3">
        <div class="card p-1">
            <div class="col-12">
                <div class="section-divider">
                    <span>Crear nueva categoría</span>
                </div>
            </div>
            <form id="formNuevaCategoria">
                @csrf
                <div class="row d-flex aling-items-end">
                    <div class="col-3">
                        <div class="form-floating">
                            <input type="text" class="form-control" name="nombreCateg" required placeholder="Nombre categoría" />
                            <label for="">Nombre categoría</label>
                        </div>
                    </div>
                    <div class="col-3">
                        <button type="submit" class="btn btn-primary w-100" id="btnCrearCateg">
                            Crear nueva categoría
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <table id="tb_categ" class="table table-striped table-hover align-middle table-header-hp3c mt-2">
            <thead>
                <tr>
                    <th class="text-start">ID</th>
                    <th class="text-start">CATEGORÍA</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>