<?php

namespace App\Http\Requests\Edd;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class GuardarPeriodoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edd.administrar') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('codigo'))) {
            $this->merge(['codigo' => strtoupper(trim($this->input('codigo')))]);
        }
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:40', 'regex:/^[A-Z0-9][A-Z0-9_-]*$/', Rule::unique('edd_periodos', 'codigo')->ignore($this->route('periodo'))],
            'nombre' => ['required', 'string', 'max:160'],
            'anio' => ['required', 'integer', 'between:2000,2100'],
            'revision' => [$this->route('periodo') ? 'required' : 'prohibited', 'integer', 'min:0'],
            'fecha_corte' => ['nullable', 'date_format:Y-m-d'],
            'inicio_evaluacion' => ['nullable', 'date_format:Y-m-d'],
            'fin_evaluacion' => ['nullable', 'date_format:Y-m-d'],
            'inicio_autoevaluacion' => ['nullable', 'date_format:Y-m-d'],
            'fin_autoevaluacion' => ['nullable', 'date_format:Y-m-d'],
            'limite_devolucion' => ['nullable', 'date_format:Y-m-d'],
            'autoevaluacion' => ['nullable', Rule::in(['obligatoria', 'opcional', 'deshabilitada'])],
            'visibilidad_autoevaluacion' => ['nullable', Rule::in(['al_enviar', 'al_completar_evaluador'])],
            'condiciones_cierre' => ['nullable', 'string', 'max:4000'],
            'excepciones' => ['nullable', 'string', 'max:4000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            foreach ([
                ['fecha_corte', 'inicio_evaluacion'],
                ['inicio_evaluacion', 'fin_evaluacion'],
                ['inicio_autoevaluacion', 'fin_autoevaluacion'],
                ['fin_autoevaluacion', 'limite_devolucion'],
                ['fin_evaluacion', 'limite_devolucion'],
            ] as [$desde, $hasta]) {
                if (! $validator->errors()->has($desde) && ! $validator->errors()->has($hasta)
                    && $this->filled($desde) && $this->filled($hasta) && $this->input($hasta) < $this->input($desde)) {
                    $validator->errors()->add($hasta, 'La fecha debe ser igual o posterior a '.$this->attributes()[$desde].'.');
                }
            }
            if ($this->input('autoevaluacion') === 'deshabilitada'
                && ($this->filled('inicio_autoevaluacion') || $this->filled('fin_autoevaluacion') || $this->filled('visibilidad_autoevaluacion'))) {
                $validator->errors()->add('autoevaluacion', 'Si la autoevaluación está deshabilitada, dejá sus fechas y visibilidad sin definir.');
            }
        }];
    }

    public function attributes(): array
    {
        return [
            'codigo' => 'código', 'nombre' => 'nombre', 'anio' => 'año', 'revision' => 'revisión',
            'fecha_corte' => 'la fecha de corte', 'inicio_evaluacion' => 'el inicio de evaluación',
            'fin_evaluacion' => 'el fin de evaluación', 'inicio_autoevaluacion' => 'el inicio de autoevaluación',
            'fin_autoevaluacion' => 'el fin de autoevaluación', 'limite_devolucion' => 'el límite de devolución',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Completá :attribute.', 'codigo.unique' => 'Ya existe un período con ese código.',
            'codigo.regex' => 'Usá letras sin acentos, números, guiones o guiones bajos en el código.',
            'date_format' => 'Ingresá una fecha válida en :attribute.', 'integer' => ':attribute debe ser un número entero.',
            'max' => ':attribute supera la longitud permitida.', 'in' => 'Seleccioná una opción válida para :attribute.',
        ];
    }
}
