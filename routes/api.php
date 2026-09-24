<?php

use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Versionamiento de API:
| Agrupar por prefijo 'v1' previene romper clientes legados cuando la API evoluciona.
| 'apiResource' genera automáticamente las 5 rutas RESTful:
| - GET    /api/v1/products           (index)
| - POST   /api/v1/products           (store)
| - GET    /api/v1/products/{product} (show)
| - PUT    /api/v1/products/{product} (update)
| - DELETE /api/v1/products/{product} (destroy)
| Excluyendo rutas innecesarias de vistas HTML (create, edit).
|
*/

Route::prefix('v1')->group(function () {
    Route::apiResource('products', ProductController::class);
});
