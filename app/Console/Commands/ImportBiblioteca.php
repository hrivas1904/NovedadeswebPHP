<?php

namespace App\Console\Commands;

use App\Services\Biblioteca\Importer;
use Illuminate\Console\Command;

class ImportBiblioteca extends Command
{
    protected $signature='biblioteca:importar {bundle : Ruta al bundle.json} {--solo-validar : Comprueba archivos sin escribir en MySQL}';
    protected $description='Migra y verifica la biblioteca institucional conservando fuentes e historial.';
    public function handle(Importer $importer): int {
        try { $this->line(json_encode($importer->import($this->argument('bundle'),$this->option('solo-validar')),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));return self::SUCCESS; }
        catch(\Throwable $error){$this->error($error->getMessage());return self::FAILURE;}
    }
}
