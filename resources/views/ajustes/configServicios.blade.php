<div class="col-12">
                <div class="card p-2 mb-3" style="height: 350px";>
                    <div class="row d-flex">
                        <div class="col-xl-6 col-12 mb-3">
                            <div class="card" style="border-radius:15px;">
                                <div class="card-body">
                                    <table id="tb_servicios" class="table table-striped table-hover align-middle table-header-hp3c">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>SERVICIO</th>
                                                <th>ID ÁREA</th>
                                                <th>ÁREA VINCULADA</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6 col-12">
                            <div class="card" style="border-radius:15px;">
                                <div class="card-body">

                                    <div class="col-12 mb-4">
                                        <div class="section-divider">
                                            <span>Crear nuevo servicio</span>
                                        </div>
                                    </div>
                                    <form id="formNuevoServicio">
                                        @csrf

                                        <div class="row g-3 mb-3">

                                            <div class="col-12 col-md-5 col-xl-5">
                                                <label class="form-label">ÁREA</label>
                                                <select id="selectAreaServicios" name="area" class="form-select w-100" required></select>
                                            </div>

                                            <div class="col-12 col-md-7 col-xl-7">
                                                <label class="form-label">SERVICIO</label>
                                                <input type="text" class="form-control" name="servicio" required />
                                            </div>

                                            <div class="col-12 text-end">
                                                <button type="submit" class="btn btn-primary" id="btnCrearServicio">
                                                    Crear nuevo servicio
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>