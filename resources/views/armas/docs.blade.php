@extends('layouts.docs')

@section('title', 'Documentación de Certificados')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Certificados SOMEDIAG - <small class="text-muted">Cargar Información</small></h3>
        <span class="text-end text-secondary"><i class="fas fa-user-circle me-2"></i>Administrador</span>
    </div>

    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-light px-3 py-2 rounded">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item"><a href="#">Certificados</a></li>
            <li class="breadcrumb-item active" aria-current="page">Crear</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <strong>Cargar Certificado</strong>
            <a href="#" class="btn btn-light btn-sm">
                <i class="fas fa-list me-1"></i> Lista
            </a>
        </div>

        <div class="card-body">
      <form id="form" method="POST" action="{{ route('certificados.store') }}" enctype="multipart/form-data">

                @csrf

                {{-- Nombre Persona --}}
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-3 col-form-label text-danger fw-bold">* Nombre</label>
                    <div class="col-sm-9 d-flex align-items-center">
                        <i class="fas fa-user me-2"></i>
                        <input type="text" name="nombre" class="form-control" placeholder="Nombre de la persona" required>
                    </div>
                </div>

                {{-- Cédula --}}
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-3 col-form-label text-danger fw-bold">* Cédula</label>
                    <div class="col-sm-9 d-flex align-items-center">
                        <i class="fas fa-id-card me-2"></i>
                        <input type="text" name="cedula" class="form-control" placeholder="Número de cédula" required>
                    </div>
                </div>

                {{-- Tipo de Certificado --}}
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-3 col-form-label">Tipo de Certificado</label>
                    <div class="col-sm-9">
                        <select name="tipo_certificado" class="form-select">
                            <option value="pdf">PDF</option>
                            <option value="imagen">Imagen</option>
                            <option value="video">Video</option>
                        </select>
                    </div>
                </div>

                {{-- Carga de Archivos --}}
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-3 col-form-label">Archivo</label>
                    <div class="col-sm-9">
                        <input type="file" name="archivo_certificado" class="form-control" accept=".pdf,image/*,video/*" required>
                    </div>
                </div>

                {{-- Fecha de Expedición --}}
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-3 col-form-label">Fecha de Expedición</label>
                    <div class="col-sm-9 d-flex align-items-center">
                        <i class="fas fa-calendar-alt me-2"></i>
                        <input type="date" name="fecha_expedicion" class="form-control" required>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Botones y acciones --}}
    <div class="d-flex justify-content-between align-items-center mt-4">
        <button type="reset" class="btn btn-warning">Restablecer</button>

        <div class="d-flex gap-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="continuar_editando">
                <label class="form-check-label" for="continuar_editando">Continuar editando</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="crear_nuevo">
                <label class="form-check-label" for="crear_nuevo">Seguir creando</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="ver_certificado">
                <label class="form-check-label" for="ver_certificado">Ver</label>
            </div>
        </div>

        <button type="submit" form="form" class="btn btn-primary">Enviar</button>
    </div>
</div>
@endsection
