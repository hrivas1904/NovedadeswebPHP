<div class="card p-1">
    <form id="formNuevaNovedad"> @csrf
        <div class="row d-flex align-items-end mb-1">
            <div class="col-5 col-md-3 col-xl-2">
                <div class="form-floating">
                    <input type="text" class="form-control" name="codigoNovedad" required />
                    <label>CÓDIGO</label>
                </div>
            </div>

            <div class="col-7 col-md-4 col-xl-3">
                <div class="form-floating">
                    <input type="text" class="form-control" name="nombreNovedad" required />
                    <label>CONCEPTO</label>
                </div>
            </div>

            <div class="col-3 col-md-3 col-xl-1">
                <div class="form-floating">
                    <input type="text" class="form-control" name="abreviaturaNovedad" />
                    <label>ABREVIATURA</label>
                </div>
            </div>

            <div class="col-3 col-md-2 col-xl-1">
                <div class="form-floating">
                    <select class="form-select" name="tipoValor" required>
                        <option value="Días">DÍAS</option>
                        <option value="Horas">HORAS</option>
                        <option value="Pesos">PESOS</option>
                        <option value="Unidades">UNIDADES</option>
                    </select>
                    <label>TIPO DE VALOR</label>
                </div>
            </div>

            <div class="col-3 col-md-2 col-xl-1">
                <div class="form-floating">
                    <input type="number" class="form-control" name="limiteNovedad" />
                    <label>LIMITE</label>
                </div>
            </div>

            <div class="col-3 col-md-4 col-xl-2">
                <div class="form-floating">
                    <select class="form-select" name="paraFinnegans" required>
                        <option value=1>SI</option>
                        <option value=0>NO</option>
                    </select>
                    <label>PARA FINNEGANS</label>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-2">
                <button type="submit" class="btn btn-primary w-100" id="btnCrearNovedad">
                    Crear concepto
                </button>
            </div>
        </div>
    </form>
    <table id="tb_configuracion" class="table table-striped table-hover align-middle table-header-hp3c">
        <thead>
            <tr>
                <th>ID</th>
                <th>CÓDIGO</th>
                <th>NOVEDAD</th>
                <th>TIPO DE VALOR</th>
                <th>LÍMITE</th>
                <th class="text-center">FINNEGANS</th>
                <th>ABREVIATURA</th>
                <th class="text-center">ESTADO</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>