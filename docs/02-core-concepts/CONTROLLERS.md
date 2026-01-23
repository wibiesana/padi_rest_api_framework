# 🎮 Controllers Guide

**Padi REST API Framework v2.0**

---

## Overview

Controllers in Padi REST API handle HTTP requests and responses, using a **Base/Concrete pattern** similar to models.

---

## Controller Structure

### Directory Organization

```
app/Controllers/
├── Base/
│   ├── ProductController.php  # Auto-generated, always overwritten
│   ├── UserController.php     # Auto-generated, always overwritten
│   └── CategoryController.php # Auto-generated, always overwritten
├── ProductController.php      # Custom logic, never overwritten
├── UserController.php         # Custom logic, never overwritten
├── CategoryController.php     # Custom logic, never overwritten
└── AuthController.php         # Special controller (no base)
```

### Base vs Concrete Controllers

| Type                    | Location                | Purpose             | Overwritten? |
| ----------------------- | ----------------------- | ------------------- | ------------ |
| **Base Controller**     | `app/Controllers/Base/` | Auto-generated CRUD | ✅ Yes       |
| **Concrete Controller** | `app/Controllers/`      | Custom endpoints    | ❌ Never     |

---

## Base Controller Methods

### Standard CRUD Endpoints

All auto-generated controllers have these methods:

```php
class ProductController extends BaseProductController
{
    // GET /products
    public function index(): void
    {
        // List all products with pagination
    }

    // GET /products/{id}
    public function show(): void
    {
        // Show single product
    }

    // POST /products
    public function store(): void
    {
        // Create new product
    }

    // PUT /products/{id}
    public function update(): void
    {
        // Update existing product
    }

    // DELETE /products/{id}
    public function destroy(): void
    {
        // Delete product
    }
}
```

---

## Creating Controllers

### Method 1: Auto-Generate

```bash
# Generate controller for existing model
php scripts/generate.php controller Product

# This creates:
# - app/Controllers/Base/ProductController.php (auto-generated)
# - app/Controllers/ProductController.php (if doesn't exist)
```

### Method 2: Generate Complete CRUD

```bash
# Generate Model + Controller + Routes
php scripts/generate.php crud products --write

# This creates:
# - app/Models/Base/Product.php
# - app/Models/Product.php
# - app/Controllers/Base/ProductController.php
# - app/Controllers/ProductController.php
# - Updates routes/api.php
```

---

## Request Handling

### Get Request Data

```php
class ProductController extends BaseProductController
{
    public function customAction(): void
    {
        // Get all request data
        $data = $this->getRequestData();

        // Get specific field
        $name = $data['name'] ?? null;

        // Get query parameters
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
    }
}
```

### Get Route Parameters

```php
// Route: GET /products/{id}
public function show(): void
{
    // Get ID from URL
    $id = $this->getRouteParam('id');

    $product = $this->model->find($id);

    if (!$product) {
        $this->jsonResponse(['message' => 'Not found'], 404);
        return;
    }

    $this->jsonResponse($product);
}
```

### Get Authenticated User

```php
public function myProducts(): void
{
    // Get current authenticated user
    $user = $this->getAuthUser();

    $userId = $user['id'];
    $userName = $user['name'];

    // Get user's products
    $products = $this->model->where(['user_id' => $userId])->get();

    $this->jsonResponse($products);
}
```

---

## Response Methods

### JSON Response

```php
// Success response
$this->jsonResponse([
    'message' => 'Success',
    'data' => $products
]);

// Error response
$this->jsonResponse([
    'message' => 'Not found'
], 404);

// With custom status
$this->jsonResponse([
    'message' => 'Created'
], 201);
```

### Standard Response Format

```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

### Error Response Format

```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Validation error"]
  }
}
```

---

## Validation

### Define Validation Rules

```php
class ProductController extends BaseProductController
{
    protected function getValidationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'string',
            'status' => 'required|in:active,inactive',
            'category_id' => 'required|exists:categories,id'
        ];
    }
}
```

### Validation is Automatic

Validation runs automatically in:

- `store()` method (POST)
- `update()` method (PUT)

### Manual Validation

```php
public function customAction(): void
{
    $data = $this->getRequestData();

    $rules = [
        'email' => 'required|email',
        'password' => 'required|min:8'
    ];

    $errors = $this->validate($data, $rules);

    if (!empty($errors)) {
        $this->jsonResponse([
            'message' => 'Validation failed',
            'errors' => $errors
        ], 422);
        return;
    }

    // Process valid data
}
```

---

## Custom Endpoints

### Add Custom Methods

```php
<?php

namespace App\Controllers;

use App\Controllers\Base\ProductController as BaseProductController;

class ProductController extends BaseProductController
{
    /**
     * GET /products/featured
     */
    public function featured(): void
    {
        $products = $this->model->where(['is_featured' => 1])
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get();

        $this->jsonResponse($products);
    }

    /**
     * GET /products/category/{categoryId}
     */
    public function byCategory(): void
    {
        $categoryId = $this->getRouteParam('categoryId');

        $products = $this->model->where(['category_id' => $categoryId])->get();

        $this->jsonResponse($products);
    }

    /**
     * POST /products/{id}/publish
     */
    public function publish(): void
    {
        $id = $this->getRouteParam('id');

        $updated = $this->model->update($id, [
            'status' => 'published',
            'published_at' => date('Y-m-d H:i:s')
        ]);

        if ($updated) {
            $this->jsonResponse([
                'message' => 'Product published successfully'
            ]);
        } else {
            $this->jsonResponse([
                'message' => 'Failed to publish product'
            ], 500);
        }
    }
}
```

### Register Custom Routes

Edit `routes/api.php`:

```php
// Custom routes
$router->get('/products/featured', [ProductController::class, 'featured']);
$router->get('/products/category/{categoryId}', [ProductController::class, 'byCategory']);
$router->post('/products/{id}/publish', [ProductController::class, 'publish']);
```

---

## Override Base Methods

### Customize Index Method

```php
class ProductController extends BaseProductController
{
    /**
     * Override index to add custom filtering
     */
    public function index(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 20);
        $status = $_GET['status'] ?? null;
        $categoryId = $_GET['category_id'] ?? null;

        // Build query
        $query = $this->model;

        if ($status) {
            $query = $query->where(['status' => $status]);
        }

        if ($categoryId) {
            $query = $query->where(['category_id' => $categoryId]);
        }

        // Get paginated results
        $result = $query->paginate($page, $perPage);

        $this->jsonResponse($result);
    }
}
```

### Customize Store Method

```php
public function store(): void
{
    $data = $this->getRequestData();

    // Validate
    $errors = $this->validate($data, $this->getValidationRules());
    if (!empty($errors)) {
        $this->jsonResponse(['errors' => $errors], 422);
        return;
    }

    // Add user ID
    $user = $this->getAuthUser();
    $data['user_id'] = $user['id'];

    // Add slug
    $data['slug'] = $this->generateSlug($data['name']);

    // Create product
    $product = $this->model->create($data);

    if ($product) {
        $this->jsonResponse([
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    } else {
        $this->jsonResponse([
            'message' => 'Failed to create product'
        ], 500);
    }
}
```

---

## Working with Models

### Access Model

```php
class ProductController extends BaseProductController
{
    // $this->model is automatically available

    public function index(): void
    {
        // Use model methods
        $products = $this->model->all();
        $active = $this->model->where(['status' => 'active'])->get();
        $one = $this->model->find(1);
    }
}
```

### Use Multiple Models

```php
use App\Models\Product;
use App\Models\Category;

class ProductController extends BaseProductController
{
    public function withCategories(): void
    {
        $categoryModel = new Category();

        $products = $this->model->all();

        // Attach category to each product
        foreach ($products as &$product) {
            $product['category'] = $categoryModel->find($product['category_id']);
        }

        $this->jsonResponse($products);
    }
}
```

---

## Error Handling

### Try-Catch Pattern

```php
public function store(): void
{
    try {
        $data = $this->getRequestData();

        // Validate
        $errors = $this->validate($data, $this->getValidationRules());
        if (!empty($errors)) {
            $this->jsonResponse(['errors' => $errors], 422);
            return;
        }

        // Create
        $product = $this->model->create($data);

        $this->jsonResponse([
            'message' => 'Product created',
            'data' => $product
        ], 201);

    } catch (\Exception $e) {
        $this->jsonResponse([
            'message' => 'Server error',
            'error' => $e->getMessage()
        ], 500);
    }
}
```

---

## Complete Example

### ProductController with Custom Logic

```php
<?php

namespace App\Controllers;

use App\Controllers\Base\ProductController as BaseProductController;
use App\Models\Category;
use Core\Query;

class ProductController extends BaseProductController
{
    /**
     * Override validation rules
     */
    protected function getValidationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'string',
            'category_id' => 'required|exists:categories,id',
            'status' => 'in:active,inactive,draft'
        ];
    }

    /**
     * Override index with filtering
     */
    public function index(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 20);
        $search = $_GET['search'] ?? '';
        $categoryId = $_GET['category_id'] ?? null;
        $status = $_GET['status'] ?? 'active';

        $query = Query::table('products');

        // Search
        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // Filter by category
        if ($categoryId) {
            $query->where('category_id', '=', $categoryId);
        }

        // Filter by status
        $query->where('status', '=', $status);

        // Order by
        $query->orderBy('created_at', 'DESC');

        // Paginate
        $offset = ($page - 1) * $perPage;
        $products = $query->limit($perPage)->offset($offset)->get();

        // Get total count
        $total = Query::table('products')
            ->where('status', '=', $status)
            ->count();

        $this->jsonResponse([
            'data' => $products,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ]
        ]);
    }

    /**
     * Override store to add user_id
     */
    public function store(): void
    {
        $data = $this->getRequestData();

        // Validate
        $errors = $this->validate($data, $this->getValidationRules());
        if (!empty($errors)) {
            $this->jsonResponse(['errors' => $errors], 422);
            return;
        }

        // Add user ID
        $user = $this->getAuthUser();
        $data['user_id'] = $user['id'];

        // Create
        $product = $this->model->create($data);

        $this->jsonResponse([
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }

    /**
     * Custom: Get featured products
     * GET /products/featured
     */
    public function featured(): void
    {
        $limit = (int)($_GET['limit'] ?? 10);

        $products = $this->model->where(['is_featured' => 1])
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();

        $this->jsonResponse($products);
    }

    /**
     * Custom: Get products by category
     * GET /products/category/{categoryId}
     */
    public function byCategory(): void
    {
        $categoryId = $this->getRouteParam('categoryId');

        $products = $this->model->where(['category_id' => $categoryId])->get();

        $this->jsonResponse($products);
    }
}
```

---

## Best Practices

### 1. Use Base/Concrete Pattern

✅ **DO:**

- Put custom logic in concrete controller
- Override base methods when needed

❌ **DON'T:**

- Edit base controllers (will be overwritten)

### 2. Validate All Input

✅ **DO:**

```php
$errors = $this->validate($data, $rules);
if (!empty($errors)) {
    $this->jsonResponse(['errors' => $errors], 422);
    return;
}
```

❌ **DON'T:**

```php
// Skip validation
$this->model->create($data);
```

### 3. Use Proper HTTP Status Codes

| Code | Usage                 |
| ---- | --------------------- |
| 200  | OK (GET, PUT, DELETE) |
| 201  | Created (POST)        |
| 400  | Bad Request           |
| 401  | Unauthorized          |
| 403  | Forbidden             |
| 404  | Not Found             |
| 422  | Validation Error      |
| 500  | Server Error          |

### 4. Handle Errors Gracefully

✅ **DO:**

```php
try {
    // Logic
} catch (\Exception $e) {
    $this->jsonResponse(['message' => 'Error'], 500);
}
```

---

## Next Steps

1. **Code Generator** - [CODE_GENERATOR.md](CODE_GENERATOR.md)
2. **Query Builder** - [QUERY_BUILDER.md](QUERY_BUILDER.md)
3. **API Testing** - [../03-advanced/API_TESTING.md](../03-advanced/API_TESTING.md)

---

**Previous:** [← Models Guide](MODELS.md) | **Next:** [Code Generator →](CODE_GENERATOR.md)
