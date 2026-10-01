<?php

namespace App\Http\Requests\Edd;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarPlanificacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edd.administrar') ?? false;
    }

    public function rules(): array
    {
        if ($this->routeIs('rrhh.edd.evaluador-areas.store')) {
            return ['evaluador_user_id' => ['required', 'integer', 'min:1'], 'area_id' => ['required', 'integer', 'min:1']];
        }
        if ($this->routeIs('rrhh.edd.evaluador-areas.destroy')) {
            return [];
        }
        if ($this->routeIs('rrhh.edd.competencias.aplicar')) {
            return [
                'participantes' => ['required', 'array', 'min:1', 'max:100'],
                'participantes.*' => ['required', 'integer', 'min:0'],
                'modo_competencias' => ['required', Rule::in(['area', 'personal'])],
                'competencias' => ['nullable', 'required_if:modo_competencias,personal', 'string', 'max:30000'],
            ];
        }

        return [
            'revision' => ['required', 'integer', 'min:0'],
            'lista_id' => [$this->routeIs('rrhh.edd.generales.update') ? 'prohibited' : 'nullable', 'integer', 'min:1'],
            'nombre' => [$this->routeIs('rrhh.edd.generales.update') ? 'nullable' : 'required', 'string', 'max:160'],
            'competencias' => ['required', 'string', 'max:30000'],
        ];
    }
}
