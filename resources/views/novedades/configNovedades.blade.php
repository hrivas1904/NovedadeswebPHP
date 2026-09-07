<div class="card p-1">
    <form id="formNuevaNovedad"> @csrf
        <div class="row g-3 mb-3">
            <div class="col-12 col-md-3 col-xl-3">
                <label class="form-label">CÓDIGO</label>
                <input type="text" class="form-control" name="codigoNovedad" required />
            </div>

            <div class="col-12 col-md-7 col-xl-7">
                <label class="form-label">CONCEPTO</label>
                <input type="text" class="form-control" name="nombreNovedad" required />
            </div>

            <div class="col-12 col-md-2 col-xl-2">
                <label class="form-label">TIPO DE VALOR</label>
                <select class="form-select" name="tipoValor" required>
                    <option value="Días">DÍAS</option>
                    <option value="Horas">HORAS</option>
                    <option value="Pesos">PESOS</option>
                    <option value="Unidades">UNIDADES</option>
                </select>
            </div>

            <div class="col-12 col-md-2 col-lg-3 col-xl-2">
                <label class="form-label">LIMITE</label>
                <input type="number" class="form-control" name="limiteNovedad" />
            </div>

            <div class="col-12 col-md-3 col-lg-3 col-xl-3">
                <label class="form-label">PARA FINNEGANS</label>
                <select class="form-select" name="paraFinnegans" required>
                    <option value=1>SI</option>
                    <option value=0>NO</option>
                </select>
            </div>
        </div>
        <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary" id="btnCrearNovedad">
                Crear nuevo concepto
            </button>
        </div>
    </form>
    <table id="tb_configuracion" class="table table-striped table-hover align-middle table-header-hp3c">
        <thead>
            <tr>
                <th>ID</th>
                <th>CÓDIGO</th>
                <th>NOVEDAD</th>
                <th>LÍMITE</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>