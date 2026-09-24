<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Product
 *
 * Responsabilidad de esta capa (Persistencia / Active Record):
 * Representa la tabla 'products' y sus reglas de mapeo objeto-relacional (ORM).
 * NO debe contener lógica de orquestación HTTP ni formateo de respuestas.
 */
class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * $fillable define qué atributos pueden asignarse masivamente (Mass Assignment).
     * Decisión técnica: Previene vulnerabilidades de asignación masiva donde un usuario
     * podría intentar inyectar campos como 'id', 'role' o modificar timestamps manualmente.
     */
    protected $fillable = [
        'name',
        'sku',
        'description',
        'price',
        'stock',
        'is_active',
    ];

    /**
     * $casts convierte automáticamente tipos de datos nativos de la base de datos a tipos de PHP.
     * Decisión técnica:
     * - 'decimal:2' asegura que siempre se devuelva con 2 decimales en string para evitar imprecisiones.
     * - 'integer' y 'boolean' garantizan tipado estricto en PHP.
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
