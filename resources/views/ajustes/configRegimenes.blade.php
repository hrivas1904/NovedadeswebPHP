<div class="col-lg-6 col-12">
                <div class="card p-2" style="height: 380px";>
                    <div class="d-flex flex-column gap-3">
                        <form id="formNuevoRegimen">
                            <div class="row d-flex align-items-end">
                                <div class="col-6 col-md-4">
                                    <label for="inputRegimen" class="form-label">RÉGIMEN</label>
                                    <input type="number" id="inputRegimen" class="form-control w-100">
                                </div>
                                <div class="col-6 col-md-4">
                                    <label for="inputHorasDiarias" class="form-label">HORAS DIARIAS</label>
                                    <input type="number" id="inputHorasDiarias" class="form-control w-100">
                                </div>
                                <div class="col-6 col-md-4">
                                    <button type="button" id="btnCrearRegimen" class="btn btn-primary w-100">Crear nuevo régimen</button>
                                </div>
                            </div>
                        </form>
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
                    </div>
                </div>
            </div>