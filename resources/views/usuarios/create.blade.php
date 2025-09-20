@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Registrar Usuario</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

<form method="POST" action="{{ route('usuarios.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="form-group mb-3">
        <label>Nombre:</label>
        <input type="text" name="name" class="form-control" required>
    </div>

    <div class="form-group mb-3">
        <label>Correo electrónico:</label>
        <input type="email" name="email" class="form-control" required>
    </div>

    <div class="form-group mb-3">
        <label>Contraseña:</label>
        <input type="password" name="password" class="form-control" required>
    </div>

    <div class="form-group mb-3">
        <label>Confirmar contraseña:</label>
        <input type="password" name="password_confirmation" class="form-control" required>
    </div>

    <div class="form-group mb-3">
        <label>Perfil:</label>
        <select name="profile_id" class="form-control" required>
            <option value="">-- Selecciona un perfil --</option>
            @foreach($perfiles as $perfil)
                <option value="{{ $perfil->id }}">{{ $perfil->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group mb-3">
        <label>Avatar:</label>
        <input type="file" name="avatar" class="form-control">
    </div>

    <button type="submit" class="btn btn-primary">Registrar</button>
</form>

{{-- Mensaje de éxito --}}
@if(session('success'))
    <div class="alert alert-success mt-3">
        {{ session('success') }}
    </div>
@endif

