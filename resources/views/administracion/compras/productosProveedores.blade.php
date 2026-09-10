@extends('layouts.app')

@section('title', 'Productos y Proveedores')

@section('content')
<div class="container-fluid">
    <div>
        <h3 class="tituloVista mb-3">GESTIÓN DE PRODUCTOS Y PROVEEDORES</h3>
    </div>

    <div class="d-flex justify-content-start align-items-center gap-3">
        <button type="button" class="btn btn-sm btn-analisis btnParametro active" style="color: var(--color-default);" data-url="{{ route('administracion.productosView') }}" data-vista="productos">Productos</button>
        <button type="button" class="btn btn-sm btn-analisis btnParametro" style="color: var(--color-default);" data-url="{{ route('administracion.proveedoresView') }}" data-vista="proveedores">Proveedores</button>
    </div>
    <hr style="color: var(--color-default); border: 1px solid;" />
    <div class="renderDivParametros">
    </div>
</div>


@endsection

@push('scripts')
<script src="{{ asset('js/administracion/compras/productosProveedores.js') }}"></script>
<script src="{{ asset('js/administracion/compras/productos.js') }}"></script>
<script src="{{ asset('js/administracion/compras/proveedores.js') }}"></script>
@endpush