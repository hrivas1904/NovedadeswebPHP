<div class="col-lg-6 col-12">
                <div class="card p-2" style="height: 350px";>
                    <div class="row d-flex">
                        <div class="col-xl-7 col-12 mb-3">
                            <div class="card" style="border-radius:15px;">
                                <div class="card-body">
                                    <table id="tb_categ" class="table table-striped table-hover align-middle table-header-hp3c">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>CATEGORÍA</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-5 col-12">
                            <div class="card" style="border-radius:15px;">
                                <div class="card-body">

                                    <div class="col-12 mb-4">
                                        <div class="section-divider">
                                            <span>Crear nueva categoría</span>
                                        </div>
                                    </div>
                                    <form id="formNuevaCategoria">
                                        @csrf

                                        <div class="row g-3 mb-3">
                                            <div class="col-12">
                                                <label class="form-label">CATEGORÍA</label>
                                                <input type="text" class="form-control" name="nombreCateg" required />
                                            </div>

                                            <div class="col-12 text-end">
                                                <button type="submit" class="btn btn-primary" id="btnCrearCateg">
                                                    Crear nueva categoría
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