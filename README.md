# Products API - Laravel REST CRUD

API REST desarrollada en Laravel para la gestión de productos, diseñada con una arquitectura por capas desacoplada (Controller -> Form Request -> Service -> Model -> Resource).

---

## Estructura y Arquitectura

Para mantener el código ordenado, testeable y evitar controladores sobrecargados de responsabilidades (*fat controllers*), la lógica se dividió en las siguientes capas:

- **Rutas (`routes/api.php`)**: Expone los endpoints RESTful versionados (`/api/v1/products`) usando `Route::apiResource`.
- **Form Requests (`app/Http/Requests/Api/V1`)**: 
  - `StoreProductRequest`: Reglas para la creación (campos obligatorios, unicidad del SKU, tipos numéricos).
  - `UpdateProductRequest`: Permite actualización parcial con `sometimes` y excluye el ID del producto actual para evitar falsos positivos en la regla de unicidad del SKU.
- **Controlador (`app/Http/Controllers/Api/V1/ProductController.php`)**: Actúa únicamente como orquestador. Inyecta el servicio mediante inyección de dependencias, recibe la data validada y retorna el Resource correspondiente con el código HTTP apropiado.
- **Capa de Servicio (`app/Services/ProductService.php`)**: Concentra las operaciones y lógica de negocio. Utiliza transacciones (`DB::transaction`) para garantizar consistencia y acota la paginación a un límite seguro para evitar problemas de consumo de memoria.
- **Modelo y Migraciones (`app/Models/Product.php`)**: Utiliza `$fillable` para prevenir asignación masiva, casteo de tipos nativo (`casts`) y borrado lógico (`SoftDeletes`). El precio se maneja como `decimal(10,2)` en base de datos para evitar problemas de precisión en montos monetarios.
- **API Resource (`app/Http/Resources/Api/V1/ProductResource.php`)**: Define la estructura de salida en JSON desacoplando las columnas de la base de datos de la respuesta que recibe el cliente, normalizando fechas a formato ISO-8601.

---

## Endpoints

**URL base:** `http://127.0.0.1:8000/api/v1`

Todas las peticiones deben incluir el header:
- `Accept: application/json`
- `Content-Type: application/json` (en POST y PUT/PATCH)

| Método | Ruta | Descripción | Código de éxito |
|---|---|---|---|
| `GET` | `/products` | Listado de productos (paginado) | `200 OK` |
| `POST` | `/products` | Crear un producto | `201 Created` |
| `GET` | `/products/{id}` | Ver detalle de un producto | `200 OK` |
| `PUT` | `/products/{id}` | Actualizar producto | `200 OK` |
| `DELETE` | `/products/{id}` | Eliminar producto (soft delete) | `204 No Content` |

### Parámetros de consulta (Query Params)
En `GET /products` se puede paginar mediante:
- `page`: Número de página (ej. `?page=1`).
- `per_page`: Cantidad de registros por página (ej. `?per_page=10`, máximo 100).

---

## Ejemplos de uso

### Crear producto (`POST /api/v1/products`)
```json
{
  "name": "Teclado Mecánico RGB",
  "sku": "KB-MECH-001",
  "description": "Teclado mecánico switches táctiles",
  "price": 129.99,
  "stock": 45,
  "is_active": true
}
```

### Respuesta exitosa (`201 Created`)
```json
{
  "data": {
    "id": 1,
    "sku": "KB-MECH-001",
    "name": "Teclado Mecánico RGB",
    "description": "Teclado mecánico switches táctiles",
    "price": 129.99,
    "stock": 45,
    "is_active": true,
    "created_at": "2026-09-24T19:53:13Z",
    "updated_at": "2026-09-24T19:53:13Z"
  }
}
```

### Error de validación (`422 Unprocessable Content`)
Si se omiten campos requeridos o el SKU ya existe:
```json
{
  "message": "El nombre del producto es obligatorio. (and 2 more errors)",
  "errors": {
    "name": [
      "El nombre del producto es obligatorio."
    ],
    "sku": [
      "El código SKU es obligatorio."
    ],
    "price": [
      "El precio del producto es obligatorio."
    ]
  }
}
```

---

## Instalación y ejecución local

1. Clonar el repositorio:
```bash
git clone https://github.com/zenitsusnow777-ux/laravel-rest-api-products.git
cd laravel-rest-api-products
```

2. Instalar dependencias:
```bash
composer install
```

3. Crear el archivo de entorno y generar la clave de aplicación:
```bash
cp .env.example .env
php artisan key:generate
```

4. Correr las migraciones:
```bash
php artisan migrate
```

5. Levantar el servidor:
```bash
php artisan serve
```

La aplicación quedará corriendo en `http://127.0.0.1:8000`.

---

## Pruebas en Postman

El repositorio incluye el archivo `postman_collection.json`. 

Para usarlo:
1. Abrir Postman e importar `postman_collection.json`.
2. La colección viene con la variable `base_url` configurada (`http://127.0.0.1:8000/api/v1`).
3. La petición de creación almacena automáticamente el `id` retornado en la variable `product_id` para probar de forma consecutiva la consulta por ID, la actualización y la eliminación.
