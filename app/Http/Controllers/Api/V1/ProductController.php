<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreProductRequest;
use App\Http\Requests\Api\V1\UpdateProductRequest;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ProductController
 *
 * Responsabilidad del Controlador:
 * - Puro ORQUESTADOR.
 * - NO ejecuta consultas SQL complejas.
 * - NO contiene reglas de negocio.
 * - Inyecta el Servicio mediante Dependency Injection.
 * - Retorna códigos HTTP semánticos y recursos transformados.
 */
class ProductController extends Controller
{
    /**
     * Inyección de dependencias en el constructor (Inversión de Control / DIP).
     */
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * Display a listing of the resource.
     * GET /api/v1/products
     * Código HTTP: 200 OK
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->query('per_page', 15);
        $products = $this->productService->getPaginatedProducts($perPage);

        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     * POST /api/v1/products
     * Código HTTP: 201 Created
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        // $request->validated() retorna únicamente los campos explícitamente autorizados y validados
        $product = $this->productService->createProduct($request->validated());

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     * GET /api/v1/products/{product}
     * Código HTTP: 200 OK (o 404 manejado por Model Binding si no existe)
     */
    public function show(Product $product): ProductResource
    {
        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     * PUT/PATCH /api/v1/products/{product}
     * Código HTTP: 200 OK
     */
    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $updatedProduct = $this->productService->updateProduct($product, $request->validated());

        return new ProductResource($updatedProduct);
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /api/v1/products/{product}
     * Código HTTP: 204 No Content
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->productService->deleteProduct($product);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
