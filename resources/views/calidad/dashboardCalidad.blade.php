@extends('layouts.app')

@section('title', 'Dashboard de Calidad')

@section('content')

    <div class="container-fluid">

        <div class="d-flex justify-content-between ms-auto align-item-end gap-3 my-2">
            <h3 class="pill-heading tituloVista">DASHBOARD DE SISTEMA DE GESTIÓN DE CALIDAD</h3>
        </div>

    </div>

@endsection

@push('scripts')
    <script src="{{ asset('js/home/mensajes.js') }}"></script>

    <script>
        const USER_ROLE = "{{ Auth::user()->rol }}";
    </script>
@endpush