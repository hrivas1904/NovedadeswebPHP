<?php

namespace App\Services\Biblioteca;

use Illuminate\Support\Facades\DB;

class Assignments
{
    public static function token(int $category, ?object $base, ?object $individual): string
    {
        // A removed override retains its revision so an old acceptance cannot become current again.
        return hash('sha256', Content::json([
            'category' => $category,
            'base' => ! empty($individual?->document_id) ? null : [$base?->document_id, $base?->revision ?? 0],
            'individual' => [$individual?->document_id, $individual?->revision ?? 0],
        ]));
    }

    public static function snapshot(array $entry): array
    {
        return array_intersect_key($entry, array_flip(['title', 'area', 'content', 'parsed']));
    }

    public static function published(?object $version): bool
    {
        return $version && (int) $version->current_slot === 1 && $version->state === 'Vigente'
            && $version->review_state === 'Validado' && ! empty($version->published_at);
    }

    public function save(string $scope, int $id, ?string $document, int $revision, string $actor): void
    {
        abort_unless(in_array($scope, ['category', 'employee'], true), 404);
        DB::transaction(function () use ($scope, $id, $document, $revision, $actor) {
            $category = $scope === 'category';
            $master = DB::table($category ? 'categ_empleados' : 'empleados')
                ->where($category ? 'ID_CATEG' : 'LEGAJO', $id)->lockForUpdate()->first();
            abort_unless($master, 404);
            if ($document) {
                $d = DB::table('bib_documents')->where('id', $document)->first();
                if (! $d || $d->kind !== 'descriptivos') {
                    Content::fail('Seleccioná un descriptivo de puesto.');
                }
                if (($category && (int) $master->estado !== 1) || (! $category && $master->ESTADO !== 'ACTIVO')) {
                    Content::fail('La categoría o el colaborador están inactivos.');
                }
            }
            $table = $category ? 'bib_category_documents' : 'bib_employee_documents';
            $key = $category ? 'category_id' : 'legajo';
            $before = DB::table($table)->where($key, $id)->lockForUpdate()->first();
            abort_if((int) ($before?->revision ?? 0) !== $revision, 409, 'La asignación cambió. Actualizá la página antes de guardar.');
            if (($before?->document_id ?? null) === $document) {
                return;
            }
            $after = [$key => $id, 'document_id' => $document, 'revision' => $revision + 1,
                'updated_at' => Library::now(), 'updated_by' => $actor];
            DB::table($table)->updateOrInsert([$key => $id], $after);
            (new Library)->event($document ?: $before->document_id, null, 'asignar_descriptivo_'.$scope, $actor, $before, $after);
        });
    }

    public function dashboard(): array
    {
        $categories = DB::table('categ_empleados')->orderBy('NOMBRE')->get();
        $employees = DB::table('empleados')->where('ESTADO', 'ACTIVO')
            ->select('LEGAJO', 'COLABORADOR', 'ID_CATEG', 'ESTADO')->orderBy('COLABORADOR')->get();
        $users = DB::table('users')->where('estado', 'ACTIVO')->select('id', 'legajo')->get()->groupBy('legajo');
        $base = DB::table('bib_category_documents')->get()->keyBy('category_id');
        $individual = DB::table('bib_employee_documents')->get()->keyBy('legajo');
        $docs = DB::table('bib_documents')->where('kind', 'descriptivos')->get()->keyBy('id');
        $versions = DB::table('bib_versions')->where('current_slot', 1)->get()->keyBy('document_id');
        $acceptances = DB::table('bib_acceptances')->select('id', 'user_id', 'legajo', 'version_id', 'assignment_token', 'content_hash', 'accepted_at')->get()->keyBy(fn ($a) => $a->user_id.'|'.$a->legajo.'|'.$a->version_id.'|'.$a->assignment_token);
        $byCategory = $employees->groupBy('ID_CATEG');
        $categoryRows = [];
        foreach ($categories as $c) {
            $mapping = $base->get($c->ID_CATEG);
            $doc = $docs->get($mapping?->document_id);
            $v = $versions->get($doc?->id);
            $categoryRows[] = ['category' => $c, 'mapping' => $mapping, 'document' => $doc,
                'version' => self::published($v) ? $v : null,
                'employees' => $byCategory->get($c->ID_CATEG, collect())->count(),
                'status' => ! $doc ? 'Sin descriptivo vinculado' : (self::published($v) ? 'Con versión vigente' : 'Pendiente de publicación')];
        }
        $categoryIndex = $categories->keyBy('ID_CATEG');
        $hashes = [];
        foreach ($versions as $v) {
            if ($docs->has($v->document_id) && self::published($v)) {
                $entry = (new Library)->present((array) $docs[$v->document_id], (array) $v);
                $hashes[$v->id] = hash('sha256', Content::json(self::snapshot($entry)));
            }
        }
        $employeeRows = [];
        foreach ($employees as $e) {
            $c = $categoryIndex->get($e->ID_CATEG);
            $b = $base->get($e->ID_CATEG);
            $i = $individual->get($e->LEGAJO);
            $doc = $docs->get($i?->document_id ?: $b?->document_id);
            $v = $versions->get($doc?->id);
            $accounts = $users->get($e->LEGAJO, collect());
            $token = self::token((int) $e->ID_CATEG, $b, $i);
            $ack = $v ? $acceptances->get(($accounts->first()?->id ?? 0).'|'.$e->LEGAJO.'|'.$v->id.'|'.$token) : null;
            $validAck = $ack && $accounts->count() === 1 && (int) $ack->user_id === (int) $accounts->first()->id && $ack->content_hash === ($hashes[$v->id] ?? null);
            $status = match (true) {
                ! $c || (int) $c->estado !== 1 => 'Sin categoría activa',
                ! $doc => 'Sin descriptivo asignado',
                ! self::published($v) => 'Pendiente de publicación',
                $accounts->isEmpty() => 'Sin usuario activo',
                $accounts->count() > 1 => 'Legajo con varias cuentas activas',
                $ack && ! $validAck => 'Revisar integridad de constancia',
                $validAck => 'Firmado',
                default => 'Pendiente de firma',
            };
            $employeeRows[] = ['employee' => $e, 'category' => $c, 'individual' => $i, 'document' => $doc,
                'origin' => $i?->document_id ? 'Individual' : 'Categoría', 'status' => $status,
                'accountCount' => $accounts->count(), 'acceptance' => $validAck ? $ack : null];
        }

        return ['categories' => $categoryRows, 'employees' => $employeeRows,
            'documents' => $docs->sortBy(fn ($d) => Content::normalize($d->canonical_name ?: $d->title))->values()->all()];
    }
}
