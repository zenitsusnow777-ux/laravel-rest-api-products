<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class ProductService
 *
 * Responsabilidad de esta capa (Lógica de Negocio y Transacciones):
 * - Contiene las reglas operacionales independientes del protocolo HTTP.
 * - Maneja transacciones de base de datos (DB::transaction) para consistencia ACID.
 * - Puede ser invocado indistintamente por Controladores API, Comandos CLI de Artisan, Jobs en cola o Webhooks.
 */
class ProductService
{
    /**
     * Obtener listado paginado con soporte opcional para filtros.
     */
    public function getPaginatedProducts(int $perPage = 15): LengthAwarePaginator
    {
        // Buena práctica: Limitar perPage para evitar ataques de DoS solicitando 100,000 registros
        $clampedPerPage = min(max($perPage, 1), 100);

        return Product::query()
            ->latest('id')
            ->paginate($clampedPerPage);
    }

    /**
     * Crear un nuevo producto encapsulado en una transacción.
     *
     * @param array<string, mixed> $data
     * @throws Throwable
     */
    public function createProduct(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            // Ejemplo de lógica de negocio: Si no se especifica is_active, por defecto es true
            $data['is_active'] = $data['is_active'] ?? true;

            $product = Product::create($data);

            Log::info('Producto creado exitosamente', [
                'product_id' => $product->id,
                'sku' => $product->sku
            ]);

            return $product;
        });
    }

    /**
     * Buscar un producto por ID o lanzar excepción ModelNotFoundException.
     */
    public function getProductById(int|string $id): Product
    {
        return Product::findOrFail($id);
    }

    /**
     * Actualizar producto existente bajo transacción.
     *
     * @param Product $product
     * @param array<string, mixed> $data
     * @throws Throwable
     */
    public function updateProduct(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $product->update($data);

            Log::info('Producto actualizado', [
                'product_id' => $product->id
            ]);

            return $product->fresh();
        });
    }

    /**
     * Eliminar producto (soft delete).
     *
     * @param Product $product
     * @throws Throwable
     */
    public function deleteProduct(Product $product): bool
    {
        return DB::transaction(function () use ($product) {
            $deleted = (bool) $product->delete();

            Log::info('Producto eliminado (SoftDelete)', [
                'product_id' => $product->id
            ]);

            return $deleted;
        });
    }
}
