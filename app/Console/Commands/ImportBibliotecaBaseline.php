<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Biblioteca\BaselineImport;
use App\Services\Biblioteca\Library;
use Illuminate\Console\Command;

class ImportBibliotecaBaseline extends Command
{
    protected $signature = 'biblioteca:importar-base {folder} {--expected=59} {--actor-id=} {--apply : Incorpora la base y archiva los antecedentes después de verificar el respaldo}';
    protected $description = 'Inspecciona e incorpora una base de descriptivos como versión 0 pendiente de revisión.';

    public function handle(BaselineImport $importer): int
    {
        try {
            $plan = $importer->inspect($this->argument('folder'), (int)$this->option('expected'));
            if (!$this->option('apply')) {
                $result = ['batch' => $plan['id'], 'documents' => count($plan['items']), 'attachments' => count($plan['attachments']), 'preview' => array_map(fn($i) => ['id' => $i['source_id'], 'name' => $i['content']['name'], 'area' => $i['content']['area'], 'warnings' => count($i['parsed']['warnings'] ?? [])], $plan['items'])];
            } else {
                $actor = User::find($this->option('actor-id'));
                if (!Library::manages($actor)) throw new \RuntimeException('Se requiere el ID del administrador que autorizó la incorporación.');
                $result = $importer->apply($plan, Library::actor($actor).' · carga de base autorizada');
            }
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}
