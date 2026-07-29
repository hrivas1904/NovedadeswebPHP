@extends('layouts.app')

@section('title', 'Gestión Financiera')

@section('content')
<div class="container-fluid">
    <h3 class="tituloVista mb-3">INTERBANKING</h3>
    <label class="text-muted my-2" style="color: var(--color-default); font-size: 0.8rem;">
        Importar el archivo DNCTASCSV de Interbanking
    </label>

    <div>
        <input type="file" class="form-control" id="inputArchivoInterbanking" accept=".csv,.txt">
    </div>

    <div class="mt-2">
        <textarea class="form-control" id="contenidoInterbanking" name="contenidoInterbanking"
            placeholder="O pegá el contenido acá..." rows="5"></textarea>
    </div>

    <label class="mt-2" id="msgInterbanking"></label>
</div>
@endsection

@push('scripts')
<script>
    const INTERBANKING_ROUTES = {
        preview: @json(route('administracion.interbanking.preview')),
        confirmar: @json(route('administracion.interbanking.confirmar')),
    };
    const CONCEPTOS_CATALOGO = @json($conceptos);
</script>
<script src="{{ asset('js/administracion/interbanking/interbanking.js') }}"></script>
@endpush