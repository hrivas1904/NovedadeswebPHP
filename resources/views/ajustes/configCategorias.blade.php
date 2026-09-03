<div class="card p-2">
    <div class="row g-3">
        <div class="col-12 col-md-6">
            <div class="card p-2 h-100">
                <div class="d-flex flex-column gap-3">
                    <div class="section-divider">
                        <span>CATEGORÍAS COLABORADORES</span>
                    </div>
                    <form id="formNuevaCategoria">
                        @csrf
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-lg-7">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="nombreCateg" id="nombreCateg" required placeholder="Nombre categoría">
                                    <label for="nombreCateg">Nombre categoría</label>
                                </div>
                            </div>

                            <div class="col-12 col-lg-5">
                                <button type="submit" class="btn btn-primary w-100" id="btnCrearCateg">
                                    Crear nueva categoría
                                </button>
                            </div>
                        </div>
                    </form>

                    <table id="tb_categ" class="table table-striped table-hover align-middle table-header-hp3c">
                        <thead>
                            <tr>
                                <th class="text-start">ID</th>
                                <th class="text-start">CATEGORÍA</th>
                                <th class="text-start">ESTADO</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card p-2 h-100">
                <div class="d-flex flex-column gap-3">
                    <div class="section-divider">
                        <span>REGÍMENES</span>
                    </div>

                    <form id="formNuevoRegimen">
                        @csrf
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-lg-4">
                                <div class="form-floating">
                                    <input type="number" id="inputRegimen" name="regimen" class="form-control" placeholder="Régimen">
                                    <label for="inputRegimen">Régimen</label>
                                </div>
                            </div>
                            <div class="col-12 col-lg-4">
                                <div class="form-floating">
                                    <input type="number" id="inputHorasDiarias" name="horasDiarias" class="form-control" placeholder="Horas diarias">
                                    <label for="inputHorasDiarias">Horas diarias</label>
                                </div>
                            </div>

                            <div class="col-12 col-lg-4">
                                <button type="button" id="btnCrearRegimen" class="btn btn-primary w-100">
                                    Crear nuevo régimen
                                </button>
                            </div>
                        </div>
                    </form>

                    <table id="tbRegimenes" class="table table-hover align-middle table-header-hp3c">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>RÉGIMEN</th>
                                <th>HORAS DIARIAS</th>
                                <th>ACTIVO</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>