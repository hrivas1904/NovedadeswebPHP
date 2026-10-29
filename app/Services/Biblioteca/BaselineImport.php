<?php

namespace App\Services\Biblioteca;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class BaselineImport
{
    public function inspect(string $folder, int $expected = 59): array
    {
        $root = realpath($folder);
        if (!$root || !is_dir($root)) throw new \RuntimeException('Carpeta de origen no disponible.');
        $items = []; $ids = []; $attachments = [];
        foreach (File::allFiles($root) as $file) {
            $extension = strtolower($file->getExtension());
            if (str_starts_with($file->getFilename(), '~$')) continue;
            if (!in_array($extension, ['docx', 'xlsx'], true)) continue;
            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
            $sha = hash_file('sha256', $file->getPathname());
            if ($extension === 'xlsx') {
                $attachments[] = ['relative' => $relative, 'sha256' => $sha];
                continue;
            }
            if (!preg_match('/^(\d+)\s*-\s*/u', $file->getFilename(), $match)) throw new \RuntimeException('Falta identificador de origen: '.$relative);
            $sourceId = (int)$match[1];
            if (isset($ids[$sourceId])) throw new \RuntimeException('Identificador de origen repetido: '.$sourceId);
            $ids[$sourceId] = true;
            $process = new Process([config('biblioteca.python'), resource_path('biblioteca/parse_document.py'), $file->getPathname()]);
            $process->setTimeout(90)->setEnv(['PYTHONIOENCODING' => 'utf-8']);
            $process->mustRun();
            $parsed = Content::decode($process->getOutput());
            $content = Content::validateJob(Content::fromParsed($parsed));
            foreach (['purpose', 'tasks'] as $required) {
                if (empty($content['groups'][$required])) throw new \RuntimeException('Falta interpretar '.$required.' en '.$relative);
            }
            if ($sha !== hash_file('sha256', $file->getPathname())) throw new \RuntimeException('El archivo cambió durante la lectura: '.$relative);
            $items[] = ['source_id' => $sourceId, 'relative' => $relative, 'sha256' => $sha, 'parsed' => $parsed, 'content' => $content];
        }
        usort($items, fn($a, $b) => $a['source_id'] <=> $b['source_id']);
        usort($attachments, fn($a, $b) => strcmp($a['relative'], $b['relative']));
        if (count($items) !== $expected) throw new \RuntimeException('Se esperaban '.$expected.' descriptivos; se encontraron '.count($items).'.');
        $manifest = array_map(fn($i) => array_intersect_key($i, array_flip(['source_id', 'relative', 'sha256'])), $items);
        $id = hash('sha256', Content::json(['documents' => $manifest, 'attachments' => $attachments]));
        return compact('id', 'root', 'items', 'attachments');
    }

    public function apply(array $plan, string $actor): array
    {
        if (!$plan['items']) throw new \RuntimeException('La base está vacía.');
        // A changed source must never be associated with an already extracted payload.
        foreach (array_merge($plan['items'], $plan['attachments']) as $item) {
            $path = $plan['root'].DIRECTORY_SEPARATOR.$item['relative'];
            if (!is_file($path) || hash_file('sha256', $path) !== $item['sha256']) throw new \RuntimeException('Cambió el origen; repetir revisión: '.$item['relative']);
        }
        $folder = config('biblioteca.storage').'/bases/'.$plan['id'];
        File::ensureDirectoryExists($folder);
        foreach ($plan['items'] as $item) $this->snapshot($plan['root'].'/'.$item['relative'], $item['sha256'], 'docx');
        foreach ($plan['attachments'] as $item) $this->snapshot($plan['root'].'/'.$item['relative'], $item['sha256'], 'xlsx');

        return DB::transaction(function () use ($plan, $actor, $folder) {
            // This existing settings row serializes concurrent baseline imports.
            $lock = DB::table('bib_visibility')->where('key', 'section:descriptivos')->lockForUpdate()->first();
            if (!$lock) throw new \RuntimeException('Falta la configuración de descriptivos.');
            $oldBatch = DB::table('bib_baseline_batches')->where('id', $plan['id'])->first();
            if ($oldBatch) return Content::decode($oldBatch->manifest_json)['report'] + ['alreadyImported' => true];
            if (DB::table('bib_baseline_batches')->exists()) throw new \RuntimeException('Ya hay una base importada. Los cambios posteriores requieren una nueva revisión explícita.');
            $previous = DB::table('bib_documents')->where('kind', 'descriptivos')->orderBy('id')->lockForUpdate()->get();
            $previousIds = $previous->pluck('id')->all();
            $versions = DB::table('bib_versions')->whereIn('document_id', $previousIds)->orderBy('id')->lockForUpdate()->get();
            $backup = $folder.'/antes-'.Str::uuid().'.json';
            $tables = [];
            foreach (['bib_documents','bib_versions','bib_sources','bib_findings','bib_events','bib_requests','bib_uploads','bib_visibility','bib_policy_approvals','bib_category_documents','bib_employee_documents','bib_acceptances','bib_baseline_batches'] as $table) {
                $tables[$table] = DB::table($table)->lockForUpdate()->get()->map(fn($row) => (array)$row)->all();
            }
            $encoded = Content::json(['created_at' => Library::now(), 'batch' => $plan['id'], 'tables' => $tables]);
            if (file_put_contents($backup, $encoded) !== strlen($encoded) || hash_file('sha256', $backup) !== hash('sha256', $encoded)) throw new \RuntimeException('No se pudo verificar el respaldo previo.');

            $library = new Library;
            foreach ($versions as $version) {
                DB::table('bib_versions')->where('id', $version->id)->update(['state' => 'Histórico / retirado', 'current_slot' => null, 'revision' => $version->revision + 1, 'updated_at' => Library::now()]);
                $library->event($version->document_id, $version->id, 'archivar_antecedente_base', $actor, (array)$version, ['batch' => $plan['id'], 'state' => 'Histórico / retirado', 'original_number' => $version->number]);
            }
            foreach ($previous as $document) {
                DB::table('bib_documents')->where('id', $document->id)->update(['baseline_batch' => $plan['id'], 'baseline_role' => 'legacy', 'revision' => $document->revision + 1]);
            }
            $created = [];
            foreach ($plan['items'] as $item) {
                $content = Content::validateJob($item['content']);
                $doc = (string)Str::uuid(); $source = (string)Str::uuid();
                DB::table('bib_documents')->insert(['id' => $doc, 'kind' => 'descriptivos', 'title' => $content['name'], 'area' => $content['area'], 'code' => 'DP-BASE-2026-'.str_pad($item['source_id'], 2, '0', STR_PAD_LEFT), 'aliases' => '[]', 'origin' => 'imported', 'created_at' => Library::now(), 'baseline_batch' => $plan['id'], 'baseline_role' => 'current', 'baseline_source_id' => $item['source_id']]);
                DB::table('bib_sources')->insert(['id' => $source, 'document_id' => $doc, 'source_id' => $source, 'name' => basename($item['relative']), 'format' => 'DOCX', 'sha256' => $item['sha256'], 'snapshot' => 'originales/'.$item['sha256'].'.docx', 'read_state' => empty($item['parsed']['warnings']) ? 'Correcta' : 'Con advertencias', 'raw_json' => Content::json(['batch' => $plan['id'], 'relative_path' => $item['relative'], 'source_id' => $item['source_id'], 'source' => ['relationship' => 'version', 'present' => 1, 'ingestion' => 'baseline']]), 'extraction_json' => Content::json($item['parsed'])]);
                $v = $library->insertVersion($doc, $content, $actor, null, ['number' => 0, 'origin' => 'imported', 'state' => 'Borrador', 'review_state' => 'Pendiente', 'source_version_id' => $source, 'change_reason' => 'Nueva base documental. Pendiente de revisión.']);
                foreach ($item['parsed']['warnings'] ?? [] as $warning) DB::table('bib_findings')->insert(['id' => (string)Str::uuid(), 'document_id' => $doc, 'version_id' => $source, 'status' => 'Pendiente', 'resolution' => '', 'payload_json' => Content::json($warning)]);
                $library->event($doc, $v['id'], 'importar_version_base_0', $actor, null, ['batch' => $plan['id'], 'source_id' => $item['source_id'], 'sha256' => $item['sha256'], 'version' => $v['id']]);
                $created[] = ['source_id' => $item['source_id'], 'relative' => $item['relative'], 'sha256' => $item['sha256'], 'document_id' => $doc, 'version_id' => $v['id'], 'title' => $content['name'], 'area' => $content['area']];
            }
            $report = ['batch' => $plan['id'], 'imported' => count($created), 'archived_documents' => count($previous), 'archived_versions' => count($versions), 'backup' => $backup, 'acceptances_preserved' => DB::table('bib_acceptances')->count()];
            $manifest = ['report' => $report, 'documents' => $created, 'attachments' => $plan['attachments'], 'previous_documents' => $previousIds];
            DB::table('bib_baseline_batches')->insert(['id' => $plan['id'], 'created_at' => Library::now(), 'actor' => $actor, 'source_root' => $plan['root'], 'manifest_json' => Content::json($manifest), 'backup_path' => $backup]);
            return $report + ['alreadyImported' => false];
        });
    }

    private function snapshot(string $source, string $sha, string $extension): void
    {
        $path = config('biblioteca.storage').'/originales/'.$sha.'.'.$extension;
        File::ensureDirectoryExists(dirname($path));
        if (!is_file($path) && !copy($source, $path)) throw new \RuntimeException('No se pudo copiar una fuente.');
        if (hash_file('sha256', $path) !== $sha) throw new \RuntimeException('La copia fuente no coincide con el original.');
    }
}
