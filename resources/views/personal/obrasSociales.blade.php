<div class="card p-1">
    <form id="formNuevaObraSocial">
        @csrf
        <div class="row g-3 d-flex align-items-end mb-1">
            <div class="col-12 col-md-4 col-xl-2">
                <div class="form-floating">            
                    <input type="number" class="form-control" name="codigoOs" required />
                    <label class="form-label">Código</label>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="form-floating">
                    <input type="text" class="form-control" name="nombreOs" required />
                    <label class="form-label">Obra social</label>
                </div>
            </div>
            <div class="col-12 col-md-2 col-xl-1">
                <button type="submit" class="btn btn-primary w-100" id="btnCrearOs">
                    Guardar
                </button>
            </div>
        </div>
    </form>
    <table id="tb_obraSocial" class="table table-striped table-hover align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>OBRA SOCIAL</th>
                <th>CÓDIGO OS</th>
                <th>ESTADO</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>