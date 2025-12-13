@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Panel de Administración</h2>
    
    <div class="row mt-4">
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h1 class="display-4">
                        <i class="fas fa-users"></i>
                    </h1>
                    <h5>Usuarios</h5>
                    <p class="card-text">{{ \App\Models\User::count() }} usuarios</p>
                    <a href="{{ route('usuarios.index') }}" class="btn btn-primary">Gestionar</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h1 class="display-4">
                        <i class="fas fa-hard-hat"></i>
                    </h1>
                    <h5>Trabajadores</h5>
                    <p class="card-text">{{ \App\Models\Trabajador::count() }} trabajadores</p>
                    <a href="{{ route('trabajadores.index') }}" class="btn btn-primary">Gestionar</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h1 class="display-4">
                        <i class="fas fa-certificate"></i>
                    </h1>
                    <h5>Certificados</h5>
                    <p class="card-text">Gestión de certificados</p>
                    <a href="{{ route('certificados_e.index') }}" class="btn btn-primary">Ver</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h1 class="display-4">
                        <i class="fas fa-archive"></i>
                    </h1>
                    <h5>Archivos</h5>
                    <p class="card-text">Gestión de archivos</p>
                    <a href="{{ route('archivos.index') }}" class="btn btn-primary">Ver</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection