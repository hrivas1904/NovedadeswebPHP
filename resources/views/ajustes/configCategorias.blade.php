<div class="card p-1">
    <div class="row d-flex">
        <div class="card p-1">
            <div class="col-12 col-md-6">
                <div class="d-flex flex-column gap-3">
                    <div class="card p-1">
                        <div class="col-12">
                            <div class="section-divider">
                                <span>CATEGORÍAS COLABORADORES</span>
                            </div>
                        </div>
                        <form id="formNuevaCategoria">
                            @csrf
                            <div class="row d-flex justify-content-start align-items-end">
                                <div class="col-6">
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
                    <table id="tb_categ" class="table table-striped table-hover align-middle table-header-hp3c">
                        <thead>
                            <tr>
                                <th class="text-start">ID</th>
                                <th class="text-start">CATEGORÍA</th>
                                <th class="text-start">ESTADO</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card p-1">
            <div class="col-12 col-md-6">
                <div class="d-flex flex-column g-3">
                    <div class="col-12 mt-3 mb-1">
                        <div class="section-divider">
                            <span>REGIMENES</span>
                        </div>
                    </div>

                    <div class="card p-1">
                        <form id="formNuevoRegimen">
                            <div class="row d-flex align-items-end text-start">
                                <div class="col-6 col-md-4">
                                    <div class="form-floating">
                                        <input type="number" id="inputRegimen" class="form-control w-100" placeholder="Régimen">
                                        <label for="inputRegimen">Régimen</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="form-floating">
                                        <input type="number" id="inputHorasDiarias" class="form-control w-100" placeholder="Horas diarias">
                                        <label for="inputHorasDiarias">Horas diarias</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <button type="button" id="btnCrearRegimen" class="btn btn-primary w-100">Crear nuevo régimen</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <table id="tbRegimenes" class="table table-hover align-middle table-header-hp3c">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>REGIMEN</th>
                                <th>HORAS DIARIAS</th>
                                <th>ACTIVO</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                    <div class="col-12 mt-3 mb-1">
                        <div class="section-divider">
                            <span>TIPOS DE CONTRATOS</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>