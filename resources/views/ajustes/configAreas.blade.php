<div class="card p-1">
    <div class="row d-flex">
        <div class="col-12 col-xl-4">
            <div class="d-flex flex-column gap-1">
                <div class="card p-2">
                    <div class="col-12">
                        <div class="section-divider">
                            <span>Crear nueva área</span>
                        </div>
                    </div>
                    <form id="formNuevaArea">
                        @csrf
                        <div class="row d-flex justify-content-center align-items-end">
                            <div class="col-12 col-md-7">
                                <input type="text" class="form-control" name="nombreArea" placeholder="Ingrese nombre del área" required />
                            </div>
                            <div class="col-12 col-md-5">
                                <button type="submit" class="btn btn-primary w-100" id="btnCrearArea">
                                    Crear nueva área
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <table id="tb_areas" class="table table-striped table-hover align-middle table-header-hp3c">
                    <thead>
                        <tr>
                            <th class="text-start">ID</th>
                            <th class="text-start">ÁREA</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div class="col-12 col-xl-8">
            <div class="card p-2">
                <label class="text-muted fs-6 text-uppercase">CONFIGURACIÓN SERVICIOS Y TURNOS
                    <span class="fw-bold" id="nombreAreaTurnos"></span>
                </label>

                <div class="col-12 mt-3 mb-1">
                    <div class="section-divider">
                        <span>TURNOS</span>
                    </div>
                </div>

                <table id="tb_turnos_areas" class="table table-striped table-hover align-middle table-header-hp3c">
                    <thead>
                        <tr>
                            <th class="text-start">ID</th>
                            <th class="text-start">NOMBRE</th>
                            <th class="text-start">DESDE</th>
                            <th class="text-start">HASTA</th>
                            <th class="text-start">TOLERANCIA</th>
                            <th class="text-start">CRUZA</th>
                            <th class="text-start">HS REALES</th>
                            <th class="text-start">HS QUE COMPUTA</th>
                            <th class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <div class="col-12 mt-3 mb-1">
                    <div class="section-divider">
                        <span>SERVICIOS</span>
                    </div>
                </div>

                <table id="tb_servicios_areas" class="table table-striped table-hover align-middle table-header-hp3c">
                    <thead>
                        <tr>
                            <th class="text-start">ID</th>
                            <th class="text-start">NOMBRE</th>
                            <th class="text-start">ACTIVO</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <div class="col-12 mt-3 mb-1">
                    <div class="section-divider">
                        <span>FUNCIONES ADICIONALES</span>
                    </div>
                </div>

                <table id="tb_funciones_adic" class="table table-striped table-hover align-middle table-header-hp3c">
                    <thead>
                        <tr>
                            <th class="text-start">ID</th>
                            <th class="text-start">FUNCIÓN</th>
                            <th class="text-start">CÓD LIQUIDACIÓN</th>
                            <th class="text-start">UNIDAD</th>
                            <th class="text-start"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

            </div>
        </div>
    </div>
</div>