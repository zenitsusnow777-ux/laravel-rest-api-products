<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreProductRequest
 *
 * Responsabilidad de esta capa:
 * 1. Autorización: ¿Tiene el cliente permiso para ejecutar esta acción?
 * 2. Validación de entrada: Asegurar que los datos HTTP cumplan el contrato antes de llegar al controlador.
 * 3. Normalización: Limpieza y casteos iniciales si son necesarios.
 */
class StoreProductRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta petición.
     * En sistemas con Auth/Policies, aquí se integraría `$this->user()->can('create', Product::class)`.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para la creación (POST).
     *
     * Decisión técnica:
     * - 'required': Al crear, los campos indispensables deben estar presentes.
     * - 'unique:products,sku': Garantiza unicidad del código SKU a nivel de aplicación (además de la DB).
     * - 'numeric|min:0': Impide precios negativos o valores no monetarios.
     * - 'integer|min:0': El stock debe ser discreto y no negativo.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'sku' => ['required', 'string', 'max:64', 'unique:products,sku'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Mensajes descriptivos y amigables para el consumidor de la API.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del producto es obligatorio.',
            'sku.required' => 'El código SKU es obligatorio.',
            'sku.unique' => 'El código SKU especificado ya se encuentra registrado.',
            'price.required' => 'El precio del producto es obligatorio.',
            'price.numeric' => 'El precio debe ser un valor numérico válido.',
            'price.min' => 'El precio no puede ser negativo.',
        ];
    }
}
