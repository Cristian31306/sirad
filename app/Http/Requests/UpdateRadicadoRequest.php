<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateRadicadoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('radicados.editar');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'numero_radicado' => 'required|string|max:255|unique:radicados,numero_radicado,'.$this->route('radicado')->id,
            'fecha_radicacion' => 'required|date',
            'remitente' => 'required|string|max:255',
            'empresa' => 'nullable|string|max:255',
            'tipo_tramite_id' => 'required|exists:tipo_tramites,id',
            'medio' => 'required|string|max:255',
            'prioridad' => 'required|in:Alta,Media,Baja',
            'asunto' => 'required|string',
            'observaciones' => 'nullable|string',
            'responsables' => 'required|array|min:1',
            'responsables.*' => 'exists:responsables,id',
            'estado' => 'required|in:pendiente,completado,alerta,vencido,anulado',
        ];
    }
}
