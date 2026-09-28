<?php

namespace App\Http\Requests\Edd;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarPoblacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edd.administrar') ?? false;
    }

    public function rules(): array
    {
        if ($this->routeIs('rrhh.edd.poblacion.store')) {
            return ['legajos' => ['required', 'array', 'min:1', 'max:100'], 'legajos.*' => ['required', 'integer', 'min:1', 'distinct']];
        }
        if ($this->routeIs('rrhh.edd.competencias.update')) {
            return [
                'revision' => ['required', 'integer', 'min:0'],
                'competencias' => ['required', 'string', 'max:30000'],
            ];
        }
        $evaluador = [
            'evaluador_user_id' => ['required', 'integer', 'min:1'],
            'funcion' => ['required', Rule::in(['jefe', 'coordinador', 'responsable', 'gerente'])],
        ];
        if ($this->routeIs('rrhh.edd.evaluadores.update')) {
            return array_merge($evaluador, [
                'participantes' => ['required', 'array', 'min:1', 'max:100'],
                'participantes.*' => ['required', 'integer', 'min:0'],
            ]);
        }

        return array_merge($evaluador, [
            'evaluador_user_id' => ['nullable', 'integer', 'min:1'],
            'revision' => ['required', 'integer', 'min:0'],
            'area_id' => ['required', 'integer', 'min:1'],
            'incluido' => ['required', 'boolean'],
            'motivo_exclusion' => ['nullable', 'required_if:incluido,0', 'string', 'max:2000'],
            'modo_competencias' => ['required', Rule::in(['area', 'personal'])],
            'competencias' => ['nullable', 'required_if:modo_competencias,personal', 'string', 'max:30000'],
        ]);
    }

    public function messages(): array
    {
        return [
            'required' => 'Completá :attribute.',
            'required_if' => 'Completá :attribute para esta opción.',
            'integer' => 'Seleccioná un valor válido para :attribute.',
            'max' => ':attribute supera el tamaño permitido.',
            'min' => 'Seleccioná al menos un elemento válido en :attribute.',
            'in' => 'Seleccioná una opción válida para :attribute.',
        ];
    }

    public function attributes(): array
    {
        return [
            'legajos' => 'colaboradores', 'participantes' => 'colaboradores',
            'evaluador_user_id' => 'evaluador', 'funcion' => 'función',
            'motivo_exclusion' => 'motivo de exclusión', 'area_id' => 'área',
            'modo_competencias' => 'origen de competencias',
        ];
    }
}
