<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use XBase\TableReader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Carbon\Carbon;

class ImportarDbf extends Command
{
    //protected $signature = 'importar:dbf {--sync : Sincronizar (actualizar registros existentes)} {--solo-empresas : Procesar solo empresas}';
    protected $signature = 'importar:dbf {--sync : Sincronizar (actualizar registros existentes)} 
                                        {--solo-empresas : Procesar solo empresas} 
                                        {--solo-citas : Procesar solo citas}';
    protected $description = 'Importar y sincronizar DBF a MySQL (dinámico)';
public function handle()
{
    set_time_limit(0);
    
    // Aumentar memoria si es necesario
    ini_set('memory_limit', '2048M');

    $modo = $this->option('sync') ? 'SINCRONIZANDO' : 'INSERTANDO SOLO NUEVOS';
    $this->info("📌 MODO: $modo");

    // Procesar según opciones
if ($this->option('solo-empresas')) {
    $this->importarTabla('empresa.dbf', 'empresas');
} elseif ($this->option('solo-citas')) {
    $this->importarTabla('cita.dbf', 'citas_recibidas');  // ← Cambiado a citas_recibidas
} else {
    $this->importarTabla('empresa.dbf', 'empresas');
    $this->importarTabla('cita.dbf', 'citas_recibidas');  // ← Cambiado a citas_recibidas
}

    $this->info('✅ PROCESO COMPLETO');
}

    /*

    solo empresas
    public function handle()
    {
        set_time_limit(0);
        
        // Aumentar memoria si es necesario
        ini_set('memory_limit', '2048M');

        $modo = $this->option('sync') ? 'SINCRONIZANDO' : 'INSERTANDO SOLO NUEVOS';
        $this->info("📌 MODO: $modo");

        // Procesar solo empresas si se especifica
        if ($this->option('solo-empresas')) {
            $this->importarTabla('empresa.dbf', 'empresas');
        } else {
            $this->importarTabla('empresa.dbf', 'empresas');
            $this->importarTabla('cita.dbf', 'citas');
        }

        $this->info('✅ PROCESO COMPLETO');
    }

    */

private function importarTabla($archivo, $tabla)
{
    $ruta = base_path("app/libraries/dbf/$archivo");
    
    if (!file_exists($ruta)) {
        $this->error("❌ Archivo no encontrado: $ruta");
        return;
    }
    
    $this->info("📁 Procesando: $archivo");
    
    // Para CITAS: siempre insertar sin verificar primary key
    $esCitas = ($tabla === 'citas');
    
    // Obtener clave primaria (solo para empresas)
    $primaryKey = null;
    $existentesMap = [];
    
    if (!$esCitas) {
        $primaryKey = $this->determinarPrimaryKey($tabla);
        
        // Cargar existentes solo para empresas
        if (Schema::hasTable($tabla)) {
            $this->info("📊 Cargando registros existentes de BD...");
            $existentes = DB::table($tabla)->pluck($primaryKey)->toArray();
            $existentesMap = array_flip($existentes);
            $this->info("📊 Registros existentes en BD: " . count($existentes));
        }
    }
    
    // Crear tabla si no existe
    if (!Schema::hasTable($tabla)) {
        if ($esCitas) {
            // Crear tabla citas con estructura adecuada
            Schema::create($tabla, function (Blueprint $table) {
                $table->id();
                $table->string('consecutivo', 50)->nullable();
                $table->string('nit_empresa', 50)->nullable();
                $table->string('documento', 50)->nullable();
                $table->string('cliente', 255)->nullable();
                $table->string('fecha', 20)->nullable();
                $table->string('hora', 20)->nullable();
                $table->string('estado', 50)->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::create($tabla, function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }
        $this->info("✅ Tabla $tabla creada");
    }
    
    // Leer el DBF y procesar registro por registro
    $table = new TableReader($ruta);
    
    $contadorInsert = 0;
    $contadorUpdate = 0;
    $contadorSkip = 0;
    $contadorError = 0;
    
    $lote = 1000; // Lote más grande para citas
    $datosInsert = [];
    $datosUpdate = [];
    
    $primeraFila = true;
    $totalRegistros = 0;
    $ultimoProgreso = 0;
    
    while ($record = $table->nextRecord()) {
        if ($record->isDeleted()) continue;
        
        $totalRegistros++;
        
        // Mostrar progreso cada 5000 registros
        if ($totalRegistros - $ultimoProgreso >= 5000) {
            $ultimoProgreso = $totalRegistros;
            $this->info("📖 Procesando registro #$totalRegistros - Insertados: $contadorInsert");
        }
        
        $fila = $this->normalizar($record->getData());
        
        // Crear columnas solo con la primera fila
        if ($primeraFila) {
            $this->crearColumnasSiNoExisten($tabla, $fila);
            $primeraFila = false;
        }
        
        $nuevaFila = [];
        
        foreach ($fila as $columna => $valor) {
            $columnaLimpia = $this->limpiarNombreColumna($columna);
            $valorLimpio = $this->limpiarValor($valor, $columnaLimpia);
            $nuevaFila[$columnaLimpia] = $valorLimpio;
        }
        
        // Para CITAS: insertar todo sin verificación
        if ($esCitas) {
            $datosInsert[] = $nuevaFila;
            $contadorInsert++;
            
            // Insertar por lotes
            if (count($datosInsert) >= $lote) {
                $this->insertarLote($tabla, $datosInsert);
                $datosInsert = [];
                $this->line("   💾 Insertados: $contadorInsert");
            }
        } 
        // Para EMPRESAS: verificar existencia
        else {
            // Obtener valor de la clave primaria
            $primaryValue = $nuevaFila[$primaryKey] ?? null;
            
            if (!$primaryValue) {
                $contadorSkip++;
                continue;
            }
            
            try {
                $existe = isset($existentesMap[$primaryValue]);
                
                if ($existe && $this->option('sync')) {
                    $datosUpdate[$primaryValue] = $nuevaFila;
                    $contadorUpdate++;
                } elseif (!$existe) {
                    $datosInsert[] = $nuevaFila;
                    $contadorInsert++;
                } else {
                    $contadorSkip++;
                }
                
                // Insertar por lotes
                if (count($datosInsert) >= $lote) {
                    $this->insertarLote($tabla, $datosInsert);
                    $datosInsert = [];
                    $this->line("   💾 Insertados: $contadorInsert");
                }
                
                // Actualizar cada cierto tiempo
                if ($existe && $this->option('sync') && count($datosUpdate) >= 100) {
                    $this->actualizarRegistrosIndividuales($tabla, $primaryKey, $datosUpdate);
                    $datosUpdate = [];
                    $this->line("   🔄 Actualizados: $contadorUpdate");
                }
                
            } catch (\Exception $e) {
                $contadorError++;
                if ($contadorError <= 5) {
                    $this->warn("❌ Error en registro $primaryValue: " . $e->getMessage());
                }
            }
        }
        
        // Limpiar memoria cada 10000 registros
        if ($totalRegistros % 10000 == 0) {
            gc_collect_cycles();
        }
    }
    
    // Insertar restantes
    if (!empty($datosInsert)) {
        $this->insertarLote($tabla, $datosInsert);
    }
    
    if (!empty($datosUpdate)) {
        $this->actualizarRegistrosIndividuales($tabla, $primaryKey, $datosUpdate);
    }
    
    $this->info("📊 [$tabla] RESUMEN FINAL:");
    $this->info("   - Registros DBF: $totalRegistros");
    $this->info("   - Insertados: $contadorInsert");
    $this->info("   - Actualizados: $contadorUpdate");
    $this->info("   - Omitidos: $contadorSkip");
    $this->info("   - Errores: $contadorError");
}
    
    private function insertarLote($tabla, $datos)
    {
        if (empty($datos)) return;
        
        try {
            // Dividir en lotes más pequeños si es necesario
            $chunks = array_chunk($datos, 500);
            foreach ($chunks as $chunk) {
                DB::table($tabla)->insert($chunk);
            }
        } catch (\Exception $e) {
            $this->warn("Error en insert: " . $e->getMessage());
        }
    }
    
    private function actualizarRegistrosIndividuales($tabla, $primaryKey, $datos)
    {
        if (empty($datos)) return;
        
        foreach ($datos as $id => $fila) {
            try {
                DB::table($tabla)->where($primaryKey, $id)->update($fila);
            } catch (\Exception $e) {
                $this->warn("Error actualizando $id: " . $e->getMessage());
            }
        }
    }
    
    private function determinarPrimaryKey($tabla)
    {
        try {
            if (Schema::hasTable($tabla)) {
                $columns = Schema::getColumnListing($tabla);
                if (in_array('nit', $columns)) {
                    return 'nit';
                }
                if (in_array('id', $columns)) {
                    return 'id';
                }
            }
        } catch (\Exception $e) {}
        
        return $tabla === 'empresas' ? 'nit' : 'id';
    }
    
    private function crearColumnasSiNoExisten($tabla, $fila)
    {
        foreach ($fila as $columna => $valor) {
            $columnaLimpia = $this->limpiarNombreColumna($columna);
            if ($columnaLimpia === '_primary') continue;
            
            if (!Schema::hasColumn($tabla, $columnaLimpia)) {
                try {
                    Schema::table($tabla, function (Blueprint $table) use ($columnaLimpia) {
                        $table->text($columnaLimpia)->nullable();
                    });
                    $this->info("🆕 Columna creada en $tabla: $columnaLimpia");
                } catch (\Exception $e) {
                    $this->warn("No se pudo crear columna $columnaLimpia: " . $e->getMessage());
                }
            }
        }
    }
    
    private function normalizar($fila)
    {
        $fila = array_change_key_case($fila, CASE_LOWER);
        $limpia = [];
        
        foreach ($fila as $key => $value) {
            $keyLimpia = trim($key);
            $keyLimpia = str_replace(['(', ')', '-', ' ', 'ñ', 'á', 'é', 'í', 'ó', 'ú'], 
                                     ['', '', '_', '_', 'n', 'a', 'e', 'i', 'o', 'u'], 
                                     $keyLimpia);
            $limpia[$keyLimpia] = $value;
        }
        
        return $limpia;
    }
    
    private function limpiarValor($valor, $columna)
    {
        if ($valor === null || $valor === '') return null;
        
        $valor = trim((string)$valor);
        if ($valor === '') return null;
        
        // Detectar fechas
        $esFecha = str_contains($columna, 'fecha') || 
                   str_contains($columna, 'date') ||
                   in_array($columna, ['fimpre', 'fechacon', 'fechacorte', 'fec_cre', 'fec_mod']);
        
        if ($esFecha) {
            $fecha = $this->convertirFecha($valor);
            return $fecha; // Si no es fecha válida, retorna null
        }
        
        // Limitar longitud para evitar errores
        if (strlen($valor) > 65535) {
            $valor = substr($valor, 0, 65535);
        }
        
        // Limpiar encoding
        $encoding = mb_detect_encoding($valor, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        if ($encoding !== 'UTF-8') {
            $valor = mb_convert_encoding($valor, 'UTF-8', $encoding ?: 'ISO-8859-1');
        }
        
        return $valor;
    }
    
    private function convertirFecha($valor)
    {
        if (!$valor || $valor === '0' || (is_numeric($valor) && $valor < 1000)) {
            return null;
        }
        
        // YYYYMMDD
        if (preg_match('/^\d{8}$/', $valor)) {
            $anio = substr($valor, 0, 4);
            $mes = substr($valor, 4, 2);
            $dia = substr($valor, 6, 2);
            if ($anio >= 1900 && $anio <= 2100 && checkdate((int)$mes, (int)$dia, (int)$anio)) {
                return "$anio-$mes-$dia";
            }
        }
        
        // DD/MM/YYYY
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $valor)) {
            try {
                $fecha = Carbon::createFromFormat('d/m/Y', $valor);
                if ($fecha && $fecha->year >= 1900) {
                    return $fecha->format('Y-m-d');
                }
            } catch (\Exception $e) {}
        }
        
        // YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
            try {
                $fecha = Carbon::createFromFormat('Y-m-d', $valor);
                if ($fecha && $fecha->year >= 1900) {
                    return $valor;
                }
            } catch (\Exception $e) {}
        }
        
        return null;
    }
    
    private function limpiarNombreColumna($columna)
    {
        $columna = strtolower(trim($columna));
        $columna = preg_replace('/[^a-z0-9_]/', '_', $columna);
        $columna = preg_replace('/_+/', '_', $columna);
        $columna = trim($columna, '_');
        
        if (empty($columna) || is_numeric($columna[0])) {
            $columna = 'col_' . $columna;
        }
        
        return substr($columna, 0, 64);
    }
}