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
                                    <label>Nombre área</label>                                
                                </div> 
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

                <div class="card p-1 mb-1">
                    <form id="formNuevaTurno">
                        @csrf
                        <div class="row d-flex align-items-end">
                            <div class="col-6 col-md-4 col-xl-2">
                                <div class="form-floating">                                
                                    <input type="text" class="form-control" name="nombreTurno" id="inputNombreTurno" placeholder="Nombre turno">
                                    <label for="inputNombreTurno">Nombre turno</label>                                
                                </div> 
                            </div>
                            <div class="col-6 col-md-2 col-xl-2">
                                <div class="form-floating">                                
                                    <input type="text" class="form-control" name="codigoTurno" id="inputCodigoTurno" placeholder="Código turno">
                                    <label for="inputCodigoTurno">Código turno</label>
                                </div> 
                            </div>
                            <div class="col-6 col-md-3 col-xl-2">
                                <div class="form-floating">
                                    <input type="time" class="form-control" name="incioTurno" id="inputInicioTurno" placeholder="Hora inicio">
                                    <label for="inputInicioTurno">Hora inicio</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-xl-2">
                                <div class="form-floating">
                                    <input type="time" class="form-control" name="finTurno" id="inputFinTurno" placeholder="Hora fin">
                                    <label for="inputFinTurno">Hora fin</label>
                                </div> 
                            </div>
                            <div class="col-6 col-md-3 col-xl-2">
                                <div class="form-floating">
                                    <input type="number" class="form-control" name="toleranciaTurno" id="inputToleranciaTurno" placeholder="Tolerancia ingreso">
                                    <label for="inputToleranciaTurno">Tolerancia ingreso</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-xl-2">
                                <button type="button" class="btn btn-primary w-100" id="btnCrearTurno">Crear turno</button>
                            </div>
                        </div>
                    </form>
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

                <div class="card p-1 mb-1">
                    <form id="formNuevoServicio">
                        @csrf
                        <div class="row d-flex align-items-end">
                            <div class="col-6 col-md-4">
                                <div class="form-floating">                                
                                    <input id="selectAreaServicios" name="area" class="form-control w-100" placeholder="Área vinculada" required readonly>
                                    <label>Área vinculada</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="form-floating">                                
                                    <input type="text" class="form-control" name="servicio" placeholder="Nombre servicio" required>
                                    <label>Servicio</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-floating">                                
                                    <button type="submit" class="btn btn-primary w-100" id="btnCrearServicio">
                                        Crear nuevo servicio
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
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

                <div class="card p-1 mb-1">
                    <form id="formNuevaFuncionAdicional">
                        @csrf
                        <div class="row d-flex align-items-end">
                            <div class="col-6 col-md-4 col-xl-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputNombreFuncion" name="nombreFuncion" placeholder="Nombre función">
                                    <label for="inputNombreFuncion">Nombre función</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-xl-3">
                                <div class="form-floating">
                                    <input class="form-control" id="inputCodigoFuncion" name="codigoFuncion" placeholder="Código función">
                                    <label for="inputCodigoFuncion">Código función</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-xl-3">
                                <div class="form-floating">
                                    <select class="form-select" id="selectNovedadFuncion" name="novedadFuncion" placeholder="Novedad función"></select>
                                    <label for="selectNovedadFuncion">Novedad vinculada</label>
                                    <input type="hidden" id="inputIdNovedad">
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-xl-3">
                                <button type="button" class="btn btn-primary w-100" id="btnCrearFuncionAdicional">Crear función adicional</button>
                            </div>
                        </div>
                    </form>
                </div>

                <table id="tb_funciones_adic" class="table table-striped table-hover align-middle table-header-hp3c">
                    <thead>
                        <tr>
                            <th class="text-start">ID</th>
                            <th class="text-start">FUNCIÓN</th>
                            <th class="text-start">MARCA</th>
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