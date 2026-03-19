@extends('layouts.app')

@section('content')
<div class="container">

    <h2 class="mb-4">Registrar Rayos X</h2>

    {{-- Mensaje éxito --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Errores --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card p-4 shadow-sm">
        <form action="{{ route('rayosx.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label">Nombre:</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Cédula:</label>
                <input type="text" name="cedula" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Fecha Rayos X:</label>
                <input type="date" name="fecha_rx" class="form-control" required>
            </div>

            {{-- 🔥 ESTE ES EL CAMPO IMPORTANTE --}}
            <div class="mb-3">
                <label class="form-label">Archivo (PDF / DOCX):</label>
                <input type="file" name="archivo" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-success">Guardar</button>
            <a href="{{ route('rayosx.index') }}" class="btn btn-secondary">Ver registros</a>
        </form>
    </div>

</div>
@endsection