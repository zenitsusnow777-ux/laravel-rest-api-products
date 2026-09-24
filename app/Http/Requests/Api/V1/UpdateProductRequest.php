<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateProductRequest
 *
 * Decisión técnica y arquitectónica clave:
 * ¿Por qué separar StoreRequest de UpdateRequest?
 * 1. En Update (PUT/PATCH), permitimos actualizaciones parciales usando 'sometimes'.
 * 2. La regla 'unique' para el SKU debe ignorar el ID del producto que se está actualizando;
 *    de lo contrario, fallaría diciendo "el SKU ya existe" al intentar guardar el mismo registro.
 */
class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Obtenemos el ID del producto desde la ruta (ej. /api/v1/products/{product})
        $productId = $this->route('product');
        if (is_object($productId)) {
            $productId = $productId->id;
        }

        return [
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'sku' => [
                'sometimes',
                'required',
                'string',
                'max:64',
                Rule::unique('products', 'sku')->ignore($productId),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0', 'max:99999999.99'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Si se envía el campo nombre, no puede estar vacío.',
            'sku.unique' => 'El código SKU especificado ya está en uso por otro producto.',
            'price.numeric' => 'El precio debe ser un número válido.',
            'price.min' => 'El precio no puede ser negativo.',
        ];
    }
}
