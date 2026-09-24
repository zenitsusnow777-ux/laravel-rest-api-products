# 🚀 API RESTful en Laravel 12: Arquitectura Limpia por Capas (CRUD de Productos)

API RESTful profesional construida con **Laravel 12** siguiendo estrictamente el principio de **Separación de Responsabilidades (SRP)** y las mejores prácticas de la industria:
`Ruta -> Form Request -> Controlador -> Servicio -> Modelo / DB -> API Resource`.

---

## 🏛️ 1. Arquitectura y Separación de Responsabilidades

Para evitar el antipatrón del *Controlador Dios* (donde validación, lógica de negocio y base de datos se mezclan en un solo archivo), el sistema desacopla cada fase del ciclo de vida de la petición:

```
[ Cliente HTTP (Postman / Frontend) ]
                │
                ▼ (Petición HTTP con Headers y Body JSON)
        routes/api.php
                │
                ▼
  [ App\Http\Requests ] ───(¿Falla validación?)──► [ HTTP 422 Unprocessable Content ]
   (Store / Update)
                │ (Datos 100% seguros y validados)
                ▼
 [ App\Http\Controllers ] ───(Orquestación pura)
                │
                ▼ (Inyección de dependencias)
     [ App\Services ] ──────(Lógica de negocio, Transacciones ACID, Logs)
                │
                ▼
      [ App\Models ] ────────(Mapeo ORM Eloquent, Mass Assignment, Casts)
                │
                ▼ (Entidad o Paginador)
    [ App\Http\Resources ] ──(Transformación y DTO de salida)
                │
                ▼
       [ Respuesta JSON ] ───► [ HTTP 200, 201, 204, etc. ]
```

### ¿Por qué existe cada capa?

| Capa | Ubicación en el Código | Responsabilidad Única | Qué problema evita |
| :--- | :--- | :--- | :--- |
| **Rutas** | `routes/api.php` | Mapeo de verbos HTTP a controladores con versionamiento (`/api/v1`). | Evita rutas desordenadas o sin semántica RESTful. |
| **Validación** | `app/Http/Requests/Api/V1/` | Autorización previa y validación estricta del contrato de entrada. | Impide que datos sucios alcancen la lógica de negocio o la base de datos. |
| **Controlador** | `app/Http/Controllers/Api/V1/` | **Orquestador puro**. Recibe datos validados, delega al servicio y responde con el Resource. | Evita el *Fat Controller* y facilita pruebas unitarias. |
| **Servicio** | `app/Services/` | Lógica de negocio, reglas de dominio y transacciones (`DB::transaction`). | Permite reutilizar la lógica desde comandos CLI, Jobs o Webhooks. |
| **Modelo** | `app/Models/` | Persistencia, relaciones, `$fillable` (protección Mass Assignment) y `$casts`. | Desacopla el almacenamiento físico del procesamiento de negocio. |
| **Resource** | `app/Http/Resources/Api/V1/` | DTO de presentación. Serializa y formatea los datos al JSON final. | Evita exponer columnas internas o cambiar contratos públicos si la DB cambia. |

---

## 📋 2. Catálogo de Endpoints de la API (V1)

**URL Base:** `http://127.0.0.1:8000/api/v1`

### Headers Obligatorios:
- `Accept: application/json`
- `Content-Type: application/json` *(para peticiones POST y PUT/PATCH)*

| Método | Endpoint | Acción / Descripción | Código Éxito | Códigos Error |
| :---: | :--- | :--- | :---: | :---: |
| `GET` | `/api/v1/products` | Listar productos paginados (`?page=1&per_page=10`) | `200 OK` | `500` |
| `POST` | `/api/v1/products` | Crear un nuevo producto en la base de datos | `201 Created` | `422`, `500` |
| `GET` | `/api/v1/products/{id}` | Obtener detalle de un producto específico | `200 OK` | `404 Not Found` |
| `PUT` | `/api/v1/products/{id}` | Actualizar un producto existente | `200 OK` | `422`, `404 Not Found` |
| `DELETE`| `/api/v1/products/{id}` | Eliminar producto de forma lógica (Soft Delete) | `204 No Content`| `404 Not Found` |

---

## 📦 3. Ejemplos de Peticiones y Respuestas

### Crear Producto (`POST /api/v1/products`)
**Payload (JSON):**
```json
{
  "name": "Teclado Mecánico RGB Pro",
  "sku": "KB-MECH-001",
  "description": "Teclado mecánico switches táctiles programables",
  "price": 129.99,
  "stock": 45,
  "is_active": true
}
```

**Respuesta (`201 Created`):**
```json
{
  "data": {
    "id": 1,
    "sku": "KB-MECH-001",
    "name": "Teclado Mecánico RGB Pro",
    "description": "Teclado mecánico switches táctiles programables",
    "price": 129.99,
    "stock": 45,
    "is_active": true,
    "created_at": "2026-09-24T19:53:13Z",
    "updated_at": "2026-09-24T19:53:13Z"
  }
}
```

### Validación de Errores (`422 Unprocessable Content`)
Si se envía el cuerpo vacío (`{}`), la capa de validación responde automáticamente:
```json
{
  "message": "El nombre del producto es obligatorio. (and 2 more errors)",
  "errors": {
    "name": ["El nombre del producto es obligatorio."],
    "sku": ["El código SKU es obligatorio."],
    "price": ["El precio del producto es obligatorio."]
  }
}
```

---

## 🛠️ 4. Guía de Instalación y Puesta en Marcha

### Prerrequisitos
- **PHP** >= 8.2 (con extensiones `pdo_sqlite` o `pdo_mysql`, `curl`, `mbstring`, `openssl`, `zip`)
- **Composer** instalado

### Pasos:
1. Clonar el repositorio:
   ```bash
   git clone https://github.com/zenitsusnow777-ux/laravel-rest-api-products.git
   cd laravel-rest-api-products
   ```
2. Instalar dependencias de PHP:
   ```bash
   composer install
   ```
3. Configurar variables de entorno:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. Ejecutar las migraciones:
   ```bash
   php artisan migrate
   ```
5. Iniciar el servidor local:
   ```bash
   php artisan serve
   ```
   La API estará lista y escuchando en `http://127.0.0.1:8000`.

---

## 🧪 5. Pruebas con Postman

En la raíz del proyecto se incluye el archivo listo para importar:
👉 **`postman_collection.json`**

### Características de la colección:
- Configurada con la variable de entorno `{{base_url}}` = `http://127.0.0.1:8000/api/v1`.
- Headers obligatorios `Accept` y `Content-Type` preconfigurados.
- Script automático en la petición de creación que guarda el nuevo `id` en `{{product_id}}` para encadenar las pruebas de detalle, actualización y eliminación sin copiar y pegar manualmente.
