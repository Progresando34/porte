<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Models\certificados_e;

class SyncCertificados extends Command
{
    protected $signature = 'sync:certificados';
    protected $description = 'Sincroniza certificados desde storage/app/public/certificados';

    public function handle()
    {
        $basePath = storage_path('app/public/RESULTADOS');

        $carpetas = File::directories($basePath);

        foreach ($carpetas as $carpeta) {
            $cedula = basename($carpeta);
            $archivos = File::files($carpeta);

            foreach ($archivos as $archivo) {
                $nombreArchivo = $archivo->getFilename();
                $rutaRelativa = "RESULTADOS/{$cedula}/{$nombreArchivo}";

                certificados_e::updateOrCreate(
                    ['cedula' => $cedula, 'nombre_archivo' => $nombreArchivo],
                    ['ruta' => $rutaRelativa]
                );
            }

            $this->info("✔ Carpeta sincronizada: {$cedula}");
        }

        $this->info('✅ Sincronización completada.');
    }
}
