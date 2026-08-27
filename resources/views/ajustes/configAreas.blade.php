<div class="card p-2">
    <div class="d-flex flex-column gap-1">
        <div class="col-12">
            <div class="section-divider">
                <span>Crear nueva área</span>
            </div>
        </div>
        <div class="card p-1">
            <form id="formNuevaArea">
                @csrf
                <div class="row d-flex justify-content-center align-items-end">
                    <div class="col-12 col-md-9">
                        <label class="form-label">ÁREA</label>
                        <input type="text" class="form-control" name="nombreArea" required />
                    </div>
                    <div class="col-12 col-md-3">
                        <button type="submit" class="btn btn-primary" id="btnCrearArea w-100">
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