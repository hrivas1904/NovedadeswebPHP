@extends('layouts.app')

@section('title', 'Encuestas de Calidad')

@section('content')

    <div class="container-fluid">

        <div class="d-flex justify-content-between ms-auto align-item-end gap-3 my-2">
            <h3 class="pill-heading tituloVista">ENCUESTAS DE CALIDAD</h3>
        </div>

        <div class="row d-flex align-item-center justify-content-start">
            <div class="col-2">
                <button type="button" class="btn-primario">
                    <i class="fa-solid fa-upload"></i> 
                    Cargar archivo
                </button>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
    <script src="{{ asset('js/home/mensajes.js') }}"></script>

    <script>
        const USER_ROLE = "{{ Auth::user()->rol }}";
    </script>
@endpush
