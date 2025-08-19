<!-- resources/views/partials/sidebar_docs.blade.php -->
<div class="p-3 bg-light" style="min-height: 100vh;"> {{-- fondo gris claro --}}
    <div class="list-group">
        <a href="#" class="list-group-item list-group-item-action active">
            Documentación
        </a>
        <a href="{{ route('armas.create') }}" class="list-group-item list-group-item-action">
            Volver a creación
        </a>
        <a href="#" class="list-group-item list-group-item-action">
            Manual de uso
        </a>
        <a href="#" class="list-group-item list-group-item-action">
            Políticas y seguridad
        </a>
    </div>
</div>
