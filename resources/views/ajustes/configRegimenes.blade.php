<div class="d-flex">
    <div class="col-lg-6 col-12">
        <div class="card p-2">
            <div class="d-flex flex-column gap-3">
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
</div>