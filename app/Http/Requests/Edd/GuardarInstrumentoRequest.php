<?php

namespace App\Http\Requests\Edd;

use Illuminate\Foundation\Http\FormRequest;

class GuardarInstrumentoRequest extends FormRequest
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
        if (! $this->route('instrumento')) {
            return [
                'codigo' => ['required', 'string', 'max:40', 'regex:/^[A-Z0-9][A-Z0-9_-]*$/'],
                'nombre' => ['required', 'string', 'max:160'],
            ];
        }

        return [
            'nombre' => ['required', 'string', 'max:160'],
            'revision' => ['required', 'integer', 'min:0'],
            'formulario_completo' => ['required', 'accepted'],
            'bloques' => ['required', 'array:'.implode(',', array_keys(config('edd.bloques'))), 'size:5'],
            'bloques.*' => ['required', 'array:activo,peso_porcentaje,items'],
            'bloques.*.activo' => ['required', 'boolean'],
            'bloques.*.peso_porcentaje' => ['nullable', 'numeric', 'decimal:0,2', 'between:0,100'],
            'bloques.*.items' => ['sometimes', 'array', 'max:100'],
            'bloques.*.items.*' => ['array:id,titulo,descripcion,competencia_codigo,peso_relativo,obligatorio'],
            'bloques.*.items.*.id' => ['nullable', 'integer', 'min:1', 'distinct'],
            'bloques.*.items.*.titulo' => ['required', 'string', 'max:200'],
            'bloques.*.items.*.descripcion' => ['nullable', 'string', 'max:2000'],
            'bloques.*.items.*.competencia_codigo' => ['nullable', 'string', 'max:60', 'regex:/^[A-Za-z0-9][A-Za-z0-9_-]*$/'],
            'bloques.*.items.*.peso_relativo' => ['required', 'numeric', 'decimal:0,2', 'between:0.01,1000'],
            'bloques.*.items.*.obligatorio' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Completá este campo.', 'max' => 'El contenido supera la longitud o cantidad permitida.',
            'codigo.regex' => 'Usá letras sin acentos, números, guiones o guiones bajos en el código.',
            'array' => 'La estructura enviada no es válida. Recargá el formulario.',
            'bloques.size' => 'Deben enviarse los cinco bloques del instrumento.',
            'formulario_completo.required' => 'El formulario llegó incompleto. Reducí la cantidad de criterios o contactá a la administración antes de volver a guardar.',
            'bloques.*.peso_porcentaje.between' => 'El porcentaje debe estar entre 0 y 100.',
            'bloques.*.items.*.peso_relativo.between' => 'El peso relativo debe estar entre 0,01 y 1000.',
            'decimal' => 'Usá un número con hasta dos decimales.', 'numeric' => 'Ingresá un número válido.',
            'distinct' => 'Un mismo criterio no puede enviarse más de una vez.',
        ];
    }
}
