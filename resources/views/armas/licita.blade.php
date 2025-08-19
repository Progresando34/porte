@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>LICITACION ALCALDIA DE CUCUTA SG-SAM-2025<small class="text-muted">Editar</small></h3>
        <span class="text-end text-secondary">
            <i class="fas fa-user-circle me-2"></i>Administrador
        </span>
    </div>

    <div class="card">
        <div class="card-header"> - DOCUMENTAR </div>
        <div class="card-body">
            {{-- Sección: Licencia --}}
            <div class="mb-4">
                <label class="form-label">DECLARACION DE INHABILIDAD E INCOMPATIBILIDAD</label>
                <div class="border p-3 rounded">
                    <div class="text-center mb-2">
                           {{--      <img src="{{ asset('icons/pdf-icon.png') }}" width="60" alt="PDF"> --}}
             <p class="mt-2 mb-1"><strong>DECLARACION DE INHABILIDAD E INCOMPATIBILIDAD</strong></p>

                        <div class="d-flex justify-content-center gap-2">
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i></button>
                            <button class="btn btn-sm btn-primary"><i class="fas fa-download"></i></button>
                            <button class="btn btn-sm btn-secondary"><i class="fas fa-arrows-alt"></i></button>
                        </div>
                    </div>
                    <input type="file" class="form-control mt-3" multiple>
                </div>
            </div>

            {{-- Sección: REPS --}}
            <div class="mb-4">
                <label class="form-label">CERTIFICACION REQUISITOS AMBIENTALE</label>
                <div class="border p-3 rounded">
                    <div class="text-center mb-2">
                       {{--   <img src="{{ asset('icons/pdf-icon.png') }}" width="60" alt="PDF"> --}}
                      <p class="mt-2 mb-1"><strong>CERTIFICACION REQUISITOS AMBIENTALES</strong></p>
                        <div class="d-flex justify-content-center gap-2">
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i></button>
                            <button class="btn btn-sm btn-primary"><i class="fas fa-download"></i></button>
                            <button class="btn btn-sm btn-secondary"><i class="fas fa-arrows-alt"></i></button>
                        </div>
                    </div>
                    <input type="file" class="form-control mt-3" multiple>
                </div>
            </div>

                        {{-- Sección: REPS --}}
            <div class="mb-4">
                <label class="form-label"><strong>CERTIFICACION DE COMPROMISO DE CAPACITACIONES</strong></label>

                <div class="border p-3 rounded">
                    <div class="text-center mb-2">
                        {{--     <img src="{{ asset('icons/pdf-icon.png') }}" width="60" alt="PDF"> --}}
                        <p class="mt-2 mb-1"><strong>CERTIFICACION DE COMPROMISO DE CAPACITACIONES</strong></p>
                        <div class="d-flex justify-content-center gap-2">
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i></button>
                            <button class="btn btn-sm btn-primary"><i class="fas fa-download"></i></button>
                            <button class="btn btn-sm btn-secondary"><i class="fas fa-arrows-alt"></i></button>
                        </div>
                    </div>
                    <input type="file" class="form-control mt-3" multiple>
                </div>
            </div>

            {{-- Botones adicionales si se necesitan --}}
            <div class="d-flex justify-content-end">
                <a href="#" class="btn btn-outline-secondary me-2"><i class="fas fa-list"></i> Lista</a>
                <a href="#" class="btn btn-info text-white me-2"><i class="fas fa-eye"></i> Ver</a>
                <button class="btn btn-danger"><i class="fas fa-trash"></i> Eliminar</button>
            </div>
        </div>
    </div>
</div>
@endsection
