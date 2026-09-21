<?php

namespace App\Http\Controllers\Biblioteca;

use App\Http\Controllers\Controller;
use App\Services\Biblioteca\Acceptances;
use App\Services\Biblioteca\Assignments;
use App\Services\Biblioteca\Content;
use App\Services\Biblioteca\Library;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AcceptanceController extends Controller
{
    private function page(string $view, array $data)
    {
        return view('biblioteca.'.$view, $data + ['collections' => config('biblioteca.collections'),
            'canManage' => Library::manages(auth()->user())]);
    }

    public function mine(Request $request, Acceptances $service)
    {
        $context = $service->context($request->user());
        $history = DB::table('bib_acceptances')->where('user_id', $request->user()->getAuthIdentifier())
            ->select('id', 'employee_name', 'category_name', 'version_number', 'document_id', 'accepted_at')
            ->orderByDesc('accepted_at')->paginate(15);

        return $this->page('mine', compact('context', 'history'));
    }

    public function sign(Request $request, Acceptances $service)
    {
        $data = $request->validate(['version_id' => 'required|string|max:160',
            'assignment_token' => 'required|string|size:64', 'content_hash' => 'required|string|size:64',
            'confirmed' => 'accepted']);
        try {
            $acceptance = $service->accept($request->user(), $data);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            if ($e->getStatusCode() !== 409 || $request->expectsJson()) {
                throw $e;
            }

            return redirect()->route('biblioteca.mine')->withErrors(['acceptance' => $e->getMessage()]);
        }

        return redirect()->route('biblioteca.receipt', $acceptance->id)->with('status', 'Tu aceptación quedó registrada para esta versión.');
    }

    public function receipt(Request $request, string $acceptance)
    {
        $record = DB::table('bib_acceptances')->where('id', $acceptance)->first();
        abort_unless($record && (Library::manages($request->user()) || (int) $record->user_id === (int) $request->user()->getAuthIdentifier()), 404);
        abort_unless(hash_equals($record->content_hash, hash('sha256', $record->content_snapshot)), 409, 'La constancia requiere una revisión de integridad.');
        $entry = Content::decode($record->content_snapshot) + ['job' => true];

        return $this->page('receipt', compact('record', 'entry'));
    }

    public function coverage(Request $request, Assignments $service)
    {
        $data = $service->dashboard();
        $counts = ['activeCategories' => 0, 'missing' => 0, 'unpublished' => 0, 'covered' => 0,
            'employees' => count($data['employees']), 'signed' => 0, 'pending' => 0, 'withoutAccount' => 0, 'ambiguousAccounts' => 0];
        foreach ($data['categories'] as $row) {
            if ((int) $row['category']->estado === 1) {
                $counts['activeCategories']++;
                $counts[! $row['document'] ? 'missing' : (! $row['version'] ? 'unpublished' : 'covered')]++;
            }
        }
        foreach ($data['employees'] as $row) {
            if ($row['accountCount'] === 0) {
                $counts['withoutAccount']++;
            }
            if ($row['accountCount'] > 1) {
                $counts['ambiguousAccounts']++;
            }
            if ($row['status'] === 'Firmado') {
                $counts['signed']++;
            }
            if ($row['status'] === 'Pendiente de firma') {
                $counts['pending']++;
            }
        }
        $q = Content::normalize(mb_substr((string) $request->query('q', ''), 0, 200));
        $state = (string) $request->query('status', '');
        $filtered = array_values(array_filter($data['employees'], fn ($r) => (! $q || str_contains(Content::normalize($r['employee']->COLABORADOR.' '.$r['employee']->LEGAJO.' '.($r['category']?->NOMBRE ?? '')), $q))
            && (! $state || $r['status'] === $state)));
        $statuses = collect($data['employees'])->pluck('status')->unique()->sort()->all();
        $page = max(1, $request->integer('page', 1));
        $data['employees'] = new LengthAwarePaginator(array_slice($filtered, ($page - 1) * 20, 20), count($filtered), 20, $page,
            ['path' => $request->url(), 'query' => $request->query()]);

        if ($request->expectsJson()) {
            return response()->json(['html' => view('biblioteca.coverage-employees', $data)->render()]);
        }

        return $this->page('coverage', $data + compact('counts', 'q', 'state', 'statuses'));
    }

    public function assign(Request $request, Assignments $service, string $scope, int $id)
    {
        $data = $request->validate(['document_id' => 'nullable|string|max:160', 'revision' => 'required|integer|min:0']);
        $service->save($scope, $id, $data['document_id'] ?? null, (int) $data['revision'], Library::actor($request->user()));

        return redirect()->route('biblioteca.coverage')->with('status', 'Asignación guardada. Las aceptaciones anteriores se conservan como antecedentes.');
    }

    public function register(Request $request)
    {
        $q = mb_substr(trim((string) $request->query('q', '')), 0, 200);
        $records = DB::table('bib_acceptances')->select('id', 'employee_name', 'legajo', 'category_name', 'version_number', 'accepted_at')
            ->when($q !== '', fn ($query) => $query->where(fn ($filter) => $filter->where('employee_name', 'like', '%'.$q.'%')->orWhere('legajo', $q)))
            ->orderByDesc('accepted_at')->paginate(25)->withQueryString();

        if ($request->expectsJson()) return response()->json(['html' => view('biblioteca.acceptance-results', compact('records'))->render()]);
        return $this->page('acceptance-register', compact('records', 'q'));
    }
}
