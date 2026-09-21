@extends('layouts.app')

@section('title', 'Cruce copagos IPS')

@section('content')

<h3 class="tituloVista mb-1">REPORTE DE DEUDA POR COPAGO IPS</h3>
<small class="text-muted">Liquidación IPS (HAD's) cruzada contra cobranzas por caja, concepto "Facturación Copago (exento)".</small>

<div class="row g-3 mt-3 mb-2">
    <div class="col-12 col-lg-4">
        <div class="card p-3">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="fs-3 fa-solid fa-file-lines"></i>
                    <div class="d-flex flex-column">
                        <label class="fw-bold fs-6">Liquidación IPS (Copagos)</label>
                        <label class="text-muted">Subí el Excel con una hoja por mes (columnas FIN, NOMBRE, PRACTICAS, INTERNAC., HAD's)</label>
                    </div>
                </div>
                <input class="form-control" type="file" id="inputFileLiqIps">
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card p-3">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="fs-3 fa-solid fa-file-lines"></i>
                    <div class="d-flex flex-column">
                        <label class="fw-bold fs-6">Comprobantes de caja</label>
                        <label class="text-muted">Subí el archivo de comprobantes de caja actualizado a la fecha</label>
                    </div>
                </div>
                <input class="form-control" type="file" id="inputFileComprobantes">
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card p-3">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="fs-3 fa-solid fa-file-lines"></i>
                    <div class="d-flex flex-column">
                        <label class="fw-bold fs-6">Listado de pacientes</label>
                        <label class="text-muted">Subí el listado (Excel o PDF) con nombre y teléfono/celular</label>
                    </div>
                </div>
                <input class="form-control" type="file" id="inputFilePacientes">
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('js/administracion/cobranzas/copagosIps.js') }}"></script>
@endsection