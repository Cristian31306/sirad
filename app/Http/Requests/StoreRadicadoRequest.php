<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRadicadoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'numero_radicado' => 'required|string|max:255|unique:radicados,numero_radicado',
            'fecha_radicacion' => 'required|date|before_or_equal:today',
            'remitente' => 'required|string|max:255',
            'empresa' => 'nullable|string|max:255',
            'tipo_tramite_id' => 'required|exists:tipo_tramites,id',
            'asunto' => 'required|string',
            'medio' => 'required|string|max:255',
            'prioridad' => 'required|in:Alta,Media,Baja',
            'observaciones' => 'nullable|string',
            'responsables' => 'required|array|min:1',
            'responsables.*' => 'exists:responsables,id',
            'archivo_entrada' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,zip,jpg,jpeg,png',
        ];
    }
}
