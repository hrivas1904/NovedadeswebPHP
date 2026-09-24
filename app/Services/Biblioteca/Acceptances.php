<?php

namespace App\Services\Biblioteca;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Acceptances
{
    public const STATEMENT = 'Declaro que leí y comprendí este descriptivo de puesto y acepto las tareas, responsabilidades y compromisos detallados en esta versión.';

    public function context($user, bool $lock = false): array
    {
        $result = ['employee' => null, 'category' => null, 'entry' => null, 'acceptance' => null,
            'token' => null, 'hash' => null, 'origin' => null, 'status' => '', 'canSign' => false];
        $read = static function ($query) use ($lock) {
            return ($lock ? $query->lockForUpdate() : $query)->first();
        };
        $account = $read(DB::table('users')->where('id', $user->getAuthIdentifier()));
        if (! $account || $account->estado !== 'ACTIVO') {
            $result['status'] = 'Tu cuenta no está activa.';

            return $result;
        }
        if (! $account->legajo || $account->legajo < 1) {
            $result['status'] = 'Tu usuario no tiene un legajo vinculado. Solicitá a Administración que lo complete.';

            return $result;
        }
        $employee = $read(DB::table('empleados')->where('LEGAJO', $account->legajo));
        if (! $employee || $employee->ESTADO !== 'ACTIVO') {
            $result['status'] = 'No se encontró un colaborador activo vinculado a tu usuario.';

            return $result;
        }
        // Do not infer identity from username, DNI or a name; use the system's authoritative legajo.
        $result['employee'] = $employee;
        if (DB::table('users')->where('legajo', $account->legajo)->where('estado', 'ACTIVO')->count() !== 1) {
            $result['status'] = 'Hay varias cuentas activas con tu legajo. Administración debe revisar ese vínculo antes de firmar.';

            return $result;
        }
        $category = $read(DB::table('categ_empleados')->where('ID_CATEG', $employee->ID_CATEG));
        $result['category'] = $category;
        if (! $category || (int) $category->estado !== 1) {
            $result['status'] = 'Tu categoría no está activa o no está registrada. Solicitá su revisión a Administración.';

            return $result;
        }
        $base = $read(DB::table('bib_category_documents')->where('category_id', $category->ID_CATEG));
        $individual = $read(DB::table('bib_employee_documents')->where('legajo', $employee->LEGAJO));
        $documentId = $individual?->document_id ?: $base?->document_id;
        if (! $documentId) {
            $result['status'] = 'Todavía no tenés un descriptivo asignado. Administración debe vincularlo a tu categoría o a tu legajo.';

            return $result;
        }
        $doc = $read(DB::table('bib_documents')->where('id', $documentId));
        $version = $read(DB::table('bib_versions')->where('document_id', $documentId)->where('current_slot', 1));
        if (! $doc || $doc->kind !== 'descriptivos' || ! Assignments::published($version)) {
            $result['status'] = 'Tu descriptivo está asignado, pero todavía no tiene una versión publicada y validada para firmar.';

            return $result;
        }
        $visibility=$read(DB::table('bib_visibility')->where('key','document:'.$documentId));
        if(!Library::manages($user) && $visibility && !$visibility->visible) {
            $result['status']='Tu descriptivo está temporalmente oculto. Consultá a Administración.';
            return $result;
        }
        $result['entry'] = (new Library)->present((array) $doc, (array) $version);
        $result['token'] = Assignments::token((int) $category->ID_CATEG, $base, $individual);
        $result['origin'] = $individual?->document_id ? 'individual' : 'category';
        $result['hash'] = hash('sha256', Content::json(Assignments::snapshot($result['entry'])));
        $ack = DB::table('bib_acceptances')->where('user_id', $account->id)->where('legajo', $employee->LEGAJO)
            ->where('version_id', $version->id)->where('assignment_token', $result['token'])->first();
        if ($ack && ! hash_equals($ack->content_hash, $result['hash'])) {
            $result['status'] = 'El contenido no coincide con la constancia guardada. Administración debe revisar esta versión antes de continuar.';

            return $result;
        }
        $result['acceptance'] = $ack;
        $result['canSign'] = ! $ack;
        $result['status'] = $ack ? 'Firmado' : 'Pendiente de firma';

        return $result;
    }

    public function accept($user, array $input): object
    {
        if (empty($input['confirmed'])) {
            Content::fail('Marcá la declaración de lectura y aceptación para firmar.');
        }

        return DB::transaction(function () use ($user, $input) {
            $context = $this->context($user, true);
            abort_unless($context['entry'] && ($context['canSign'] || $context['acceptance']), 409, $context['status']);
            $entry = $context['entry'];
            abort_unless($input['version_id'] === $entry['version']['id']
                && hash_equals($context['token'], $input['assignment_token'])
                && hash_equals($context['hash'], $input['content_hash']), 409,
                'El descriptivo o su asignación cambió. Volvé a Mi descriptivo y leé la versión actual antes de firmar.');
            if ($context['acceptance']) {
                return $context['acceptance'];
            }
            $employee = $context['employee'];
            $row = ['id' => (string) Str::uuid(), 'user_id' => $user->getAuthIdentifier(),
                'legajo' => $employee->LEGAJO, 'category_id' => $context['category']->ID_CATEG,
                'category_name' => $context['category']->NOMBRE, 'employee_name' => $employee->COLABORADOR,
                'user_name' => (string) $user->name, 'document_id' => $entry['document']['id'],
                'version_id' => $entry['version']['id'], 'version_number' => $entry['version']['number'],
                'assignment_token' => $context['token'], 'assignment_origin' => $context['origin'],
                'content_hash' => $context['hash'], 'content_snapshot' => Content::json(Assignments::snapshot($entry)),
                'statement' => self::STATEMENT, 'accepted_at' => Library::now()];
            DB::table('bib_acceptances')->insert($row);
            (new Library)->event($row['document_id'], $row['version_id'], 'firmar_descriptivo', Library::actor($user), null,
                array_intersect_key($row, array_flip(['id', 'user_id', 'legajo', 'version_id', 'content_hash', 'accepted_at'])));

            return (object) $row;
        }, 3);
    }
}
