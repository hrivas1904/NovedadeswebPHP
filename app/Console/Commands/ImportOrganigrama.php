<?php

namespace App\Console\Commands;

use App\Services\Organigrama\Importador;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

class ImportOrganigrama extends Command
{
    protected $signature = 'organigrama:importar {archivo? : JSON de origen} {--mapa= : JSON de vínculos por ID estable} {--dry-run : Simular sin escribir en la base} {--reporte= : Ruta del reporte JSON}';

    protected $description = 'Importa el organigrama inicial sin duplicados, con reporte de responsables y vínculos pendientes';

    public function handle(Importador $importador): int
    {
        try {
            $file = $this->argument('archivo') ?: base_path('organigrama/02_DATOS/organigrama-base.json');
            $data = json_decode(File::get($file), true, flags: JSON_THROW_ON_ERROR);
            $mapa = $this->option('mapa') ? json_decode(File::get($this->option('mapa')), true, flags: JSON_THROW_ON_ERROR) : [];
            if (! is_array($data) || ! is_array($mapa)) {
                throw ValidationException::withMessages(['archivo' => 'El origen y el mapa deben ser objetos JSON.']);
            }
            $report = $importador->importar($data, $mapa, (bool) $this->option('dry-run'));
            $path = $this->option('reporte') ?: storage_path('app/private/organigrama/importacion-'.now()->format('Ymd-His').'.json');
            File::ensureDirectoryExists(dirname($path));
            File::put($path, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
            $this->info(($report['simulacion'] ? 'Simulación' : 'Importación').': '.$report['total_posiciones'].' posiciones, '.$report['total_dependencias'].' dependencias.');
            $this->line(count($report['coincidencias']).' responsables resueltos; '.count($report['pendientes']).' pendientes.');
            if ($report['sin_cambios'] ?? false) {
                $this->info('El archivo ya fue importado. No se modificó la estructura ni se sobrescribieron ediciones.');
            }
            $this->line('Reporte: '.$path);

            return self::SUCCESS;
        } catch (ValidationException $e) {
            $this->error(implode(' ', $e->validator->errors()->all()));

            return self::FAILURE;
        } catch (\JsonException|\Illuminate\Contracts\Filesystem\FileNotFoundException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
