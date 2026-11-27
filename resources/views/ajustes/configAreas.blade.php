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
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="nombreArea" placeholder="Ingrese nombre del área" required />
                                    <label>Nombre</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-5">
                                <button type="submit" class="btn btn-primary w-100" id="btnCrearArea">
                                    Guardar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <table id="tb_areas" class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="text-start">ID</th>
                            <th class="text-start">ÁREA</th>
                            <th class="text-center">ESTADO</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div class="col-12 col-xl-8">
            <div class="card p-2">
                <label class="text-muted fs-6 text-uppercase">CONFIGURACIÓN SERVICIOS, TURNOS Y FUNCIONES ADICIONALES
                    <span class="fw-bold" id="nombreAreaTurnos"></span>
                </label>

                <div class="col-12 mt-3 mb-1">
                    <div class="section-divider">
                        <span>Turnos</span>
                    </div>
                </div>

                <div class="card p-1 mb-1">
                    <form id="formNuevaTurno">
                        @csrf
                        <input type="hidden" name="idArea" id="inputIdAreaTurno">
                        <div class="row d-flex align-items-end">
                            <div class="col-6 col-md-4 col-xl-2">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="nombre" id="inputNombreTurno" placeholder="Nombre turno">
                                    <label for="inputNombreTurno">Nombre</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-2 col-xl-2">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="codigo" id="inputCodigoTurno" placeholder="Código turno">
                                    <label for="inputCodigoTurno">Código</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-xl-2">
                                <div class="form-floating">
                                    <input type="time" class="form-control" name="horaInicio" id="inputInicioTurno" placeholder="Hora inicio">
                                    <label for="inputInicioTurno">Hora inicio</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-xl-2">
                                <div class="form-floating">
                                    <input type="time" class="form-control" name="horaFin" id="inputFinTurno" placeholder="Hora fin">
                                    <label for="inputFinTurno">Hora fin</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-xl-2">
                                <div class="form-floating">
                                    <input type="number" class="form-control" name="toleranciaIngreso" id="inputToleranciaTurno" placeholder="Tolerancia ingreso">
                                    <label for="inputToleranciaTurno">Tolerancia ingreso</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-xl-2">
                                <button type="submit" class="btn btn-primary w-100" id="btnCrearTurno">Guardar</button>
                            </div>
                        </div>
                    </form>
                </div>

                <table id="tb_turnos_areas" class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="text-start">ID</th>
                            <th class="text-start">NOMBRE</th>
                            <th class="text-start">DESDE</th>
                            <th class="text-start">HASTA</th>
                            <th class="text-start">TOLERANCIA</th>
                            <th class="text-start">HS REALES</th>
                            <th class="text-start">HS QUE COMPUTA</th>
                            <th class="text-center">ESTADO</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <div class="col-12 mt-3 mb-1">
                    <div class="section-divider">
                        <span>Servicios</span>
                    </div>
                </div>

                <div class="card p-1 mb-1">
                    <form id="formNuevoServicio">
                        @csrf
                        <input type="hidden" id="inputIdAreaServ" name="area">
                        <div class="row d-flex align-items-end">
                            <div class="col-6 col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="servicio" placeholder="Nombre" required>
                                    <label>Servicio</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <button type="submit" class="btn btn-primary w-100" id="btnCrearServicio">
                                    Guardar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <table id="tb_servicios_areas" class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="text-start">ID</th>
                            <th class="text-start">NOMBRE</th>
                            <th class="text-center">ESTADO</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <div class="col-12 mt-3 mb-1">
                    <div class="section-divider">
                        <span>Funciones adicionales</span>
                    </div>
                </div>

                <div class="card p-1 mb-1">
                    <form id="formNuevaFuncionAdicional">
                        @csrf
                        <input type="hidden" id="inputIdAreaFuncAdic" name="area">
                        <input type="hidden" id="inputIdNovedadFuncAdic" name="idNovedad">
                        <div class="row d-flex align-items-end">
                            <div class="col-6 col-md-4 col-xl-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputNombreFuncion" name="nombreFuncion" placeholder="Nombre">
                                    <label for="inputNombreFuncion">Nombre</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-xl-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputCodigoFuncion" name="codigoFuncion" placeholder="Código">
                                    <label for="inputCodigoFuncion">Código</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-xl-3">
                                <div class="select2-floating">
                                    <label for="selectNovedadFuncion">
                                        Novedad vinculada
                                    </label>
                                    <select class="form-select" id="selectNovedadFuncion">
                                        <option></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-xl-3">
                                <button type="submit" class="btn btn-primary w-100" id="btnCrearFuncionAdicional">Guardar</button>
                            </div>
                        </div>
                    </form>
                </div>

                <table id="tb_funciones_adic" class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="text-start">ID</th>
                            <th class="text-start">FUNCIÓN</th>
                            <th class="text-start">MARCA</th>
                            <th class="text-start">NOVEDAD</th>
                            <th class="text-start">CÓD LIQUIDACIÓN</th>
                            <th class="text-start">UNIDAD</th>
                            <th class="text-center">ESTADO</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>