@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Porte y Tenencia de Armas - <small class="text-muted">Carcar Información</small></h3>
        <span class="text-end text-secondary"><i class="fas fa-user-circle me-2"></i>Administrador</span>
    </div>

    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-light px-3 py-2 rounded">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item"><a href="#">Armas</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <strong>Crear</strong>
            <button class="btn btn-light btn-sm">
                <i class="fas fa-list me-1"></i> Lista
            </button>
        </div>
        <div class="card-body">
           <form action="{{ route('armas.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- Nombre del Trabajo --}}
    <div class="row mb-3 align-items-center">
        <label class="col-sm-3 col-form-label text-danger fw-bold">* Nombre</label>
        <div class="col-sm-9 d-flex align-items-center">
            <i class="fas fa-pen me-2"></i>
            <input type="text" name="nombre" class="form-control" placeholder="Entrada Nombre">
        </div>
    </div>

    {{-- Cédula --}}
    <div class="row mb-3 align-items-center">
        <label class="col-sm-3 col-form-label text-danger fw-bold">* Cédula Trabajador</label>
        <div class="col-sm-9 d-flex align-items-center">
            <i class="fas fa-pen me-2"></i>
            <input type="text" name="cedula" class="form-control" placeholder="Entrada Cédula Trabajador">
        </div>
    </div>

    {{-- Código --}}
    <div class="row mb-3 align-items-center">
        <label class="col-sm-3 col-form-label text-danger fw-bold">* Código Número Control</label>
        <div class="col-sm-9 d-flex align-items-center">
            <i class="fas fa-pen me-2"></i>
            <input type="text" name="codigo_control" class="form-control" placeholder="Entrada Código Número Control">
        </div>
    </div>

    {{-- Apto - No Apto --}}
    <div class="row mb-3 align-items-center">
        <label class="col-sm-3 col-form-label">Activo</label>
        <div class="col-sm-9">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="activo" checked>
            </div>
        </div>
    </div>

    {{-- Carga de Certificado --}}
    <div class="row mb-3 align-items-center">
        <label class="col-sm-3 col-form-label">Certificado</label>
        <div class="col-sm-9">
            <input type="file" name="certificado" class="form-control">
        </div>
    </div>

    {{-- Fecha --}}
    <div class="row mb-3 align-items-center">
        <label class="col-sm-3 col-form-label">Fecha de Atención</label>
        <div class="col-sm-9 d-flex align-items-center">
            <i class="fas fa-calendar-alt me-2"></i>
            <input type="date" name="fecha_atencion" class="form-control">
        </div>
    </div>

    {{-- Botón Enviar --}}
    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">Enviar</button>
    </div>
</form>

        </div>
    </div>

    {{-- Botones de acción abajo --}}
    <div class="d-flex justify-content-between align-items-center mt-4">
        <button class="btn btn-warning">Restablecer</button>

        <div class="d-flex gap-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="editar">
                <label class="form-check-label" for="editar">Continua editando</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="crear">
                <label class="form-check-label" for="crear">Sigue creando</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="ver">
                <label class="form-check-label" for="ver">Ver</label>
            </div>
        </div>

        <button class="btn btn-primary">Enviar</button>
    </div>
</div>
@endsection
