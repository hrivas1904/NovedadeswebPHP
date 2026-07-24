@extends('layouts.app')

@section('title', 'Gestión Financiera')

@section('content')
<div class="container-fluid">
    <h3 class="tituloVista mb-3">INTERBANKING</h3>
    <label class="text-muted my-2" style="color: var(--color-default); font-size: 0.8rem;">
        Importar el archivo DNCTASCSV de Interbanking
    </label>

    <div>
        <input type="file" class="form-control">
    </div>

    <div class="mt-2">
        <textarea class="form-control" id="contenidoIvaVentas" name="contenidoIvaVentas"
            placeholder="O pegá el contenido acá..." rows="5"></textarea>
    </div>
</div>
@endsection