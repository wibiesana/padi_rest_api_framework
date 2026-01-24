# 📚 API Reference

**Padi REST API Framework v2.0**

---

## Standard Response Format

All API responses follow a consistent JSON structure.

### Success Response

```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Response data here
  }
}
```

### Error Response

```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

---

## HTTP Status Codes

| Code    | Status                | Usage                              |
| ------- | --------------------- | ---------------------------------- |
| **200** | OK                    | Successful GET, PUT, DELETE        |
| **201** | Created               | Successful POST (resource created) |
| **400** | Bad Request           | Invalid request format             |
| **401** | Unauthorized          | Missing or invalid authentication  |
| **403** | Forbidden             | Authenticated but not authorized   |
| **404** | Not Found             | Resource not found                 |
| **422** | Unprocessable Entity  | Validation errors                  |
| **429** | Too Many Requests     | Rate limit exceeded                |
| **500** | Internal Server Error | Server error                       |

---

## Authentication Endpoints

### Register New User

**Endpoint:** `POST /auth/register`

**Request:**

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePass123!",
  "password_confirmation": "SecurePass123!"
}
```

**Response (201):**

```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "created_at": "2026-01-23 09:50:00"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
  }
}
```

### Login

**Endpoint:** `POST /auth/login`

**Request:**

```json
{
  "username": "john@example.com",
  "password": "SecurePass123!",
  "remember_me": "true"
}
```

**Parameters:**

- `username` (required): Email or username
- `password` (required): User password
- `remember_me` (optional): Set to `"true"`, `"1"`, `"yes"`, or `"on"` for extended session (365 days). Default session is 1 hour.

**Response (200):**

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
}
```

### Get Current User

**Endpoint:** `GET /auth/me`

**Headers:**

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Response (200):**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2026-01-23 09:50:00"
  }
}
```

### Logout

**Endpoint:** `POST /auth/logout`

**Headers:**

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Response (200):**

```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

### Refresh Token

**Endpoint:** `POST /auth/refresh`

**Headers:**

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Response (200):**

```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
  }
}
```

---

## CRUD Endpoints

All auto-generated resources follow this pattern.

### List All Resources

**Endpoint:** `GET /resources`

**Query Parameters:**

- `page` (integer): Page number (default: 1)
- `per_page` (integer): Items per page (default: 20)
- `search` (string): Search keyword

**Example:**

```
GET /products?page=1&per_page=20&search=laptop
```

**Response (200):**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Product 1",
      "price": 99.99,
      "created_at": "2026-01-23 09:50:00"
    },
    {
      "id": 2,
      "name": "Product 2",
      "price": 149.99,
      "created_at": "2026-01-23 10:00:00"
    }
  ],
  "pagination": {
    "total": 100,
    "page": 1,
    "per_page": 20,
    "total_pages": 5
  }
}
```

### Get Single Resource

**Endpoint:** `GET /resources/{id}`

**Example:**

```
GET /products/1
```

**Response (200):**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Product 1",
    "price": 99.99,
    "description": "Product description",
    "created_at": "2026-01-23 09:50:00",
    "updated_at": "2026-01-23 09:50:00"
  }
}
```

**Response (404):**

```json
{
  "success": false,
  "message": "Resource not found"
}
```

### Create Resource

**Endpoint:** `POST /resources`

**Headers:**

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: application/json
```

**Request:**

```json
{
  "name": "New Product",
  "price": 99.99,
  "description": "Product description"
}
```

**Response (201):**

```json
{
  "success": true,
  "message": "Resource created successfully",
  "data": {
    "id": 3,
    "name": "New Product",
    "price": 99.99,
    "description": "Product description",
    "created_at": "2026-01-23 11:00:00"
  }
}
```

**Response (422):**

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required"],
    "price": ["The price must be a number"]
  }
}
```

### Update Resource

**Endpoint:** `PUT /resources/{id}`

**Headers:**

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: application/json
```

**Request:**

```json
{
  "name": "Updated Product",
  "price": 89.99
}
```

**Response (200):**

```json
{
  "success": true,
  "message": "Resource updated successfully",
  "data": {
    "id": 1,
    "name": "Updated Product",
    "price": 89.99,
    "updated_at": "2026-01-23 11:30:00"
  }
}
```

### Delete Resource

**Endpoint:** `DELETE /resources/{id}`

**Headers:**

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Response (200):**

```json
{
  "success": true,
  "message": "Resource deleted successfully"
}
```

**Response (404):**

```json
{
  "success": false,
  "message": "Resource not found"
}
```

---

## Validation Rules

### Available Rules

| Rule                  | Example              | Description                               |
| --------------------- | -------------------- | ----------------------------------------- |
| `required`            | `required`           | Field must be present and not empty       |
| `string`              | `string`             | Must be a string                          |
| `numeric`             | `numeric`            | Must be a number                          |
| `integer`             | `integer`            | Must be an integer                        |
| `email`               | `email`              | Must be valid email format                |
| `min:n`               | `min:8`              | Minimum length (string) or value (number) |
| `max:n`               | `max:255`            | Maximum length (string) or value (number) |
| `in:a,b,c`            | `in:active,inactive` | Must be one of the specified values       |
| `exists:table,column` | `exists:users,id`    | Must exist in database table              |
| `unique:table,column` | `unique:users,email` | Must be unique in database table          |

### Example Validation

```php
protected function getValidationRules(): array
{
    return [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8',
        'age' => 'required|numeric|min:18|max:120',
        'status' => 'required|in:active,inactive',
        'category_id' => 'required|exists:categories,id'
    ];
}
```

---

## Rate Limiting

### Headers

Every response includes rate limit headers:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1706000000
```

### Rate Limit Exceeded

**Response (429):**

```json
{
  "success": false,
  "message": "Too many requests. Please try again later."
}
```

---

## Error Responses

### Validation Error (422)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required"],
    "password": ["The password must be at least 8 characters"]
  }
}
```

### Unauthorized (401)

```json
{
  "success": false,
  "message": "Unauthorized. Please login."
}
```

### Not Found (404)

```json
{
  "success": false,
  "message": "Resource not found"
}
```

### Server Error (500)

```json
{
  "success": false,
  "message": "Internal server error"
}
```

---

## Pagination

### Request

```
GET /products?page=2&per_page=20
```

### Response

```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "total": 100,
    "page": 2,
    "per_page": 20,
    "total_pages": 5
  }
}
```

---

## Search

### Request

```
GET /products?search=laptop
```

Searches all text fields in the resource.

### Response

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Gaming Laptop",
      "description": "High-performance laptop"
    }
  ]
}
```

---

## cURL Examples

### Register

```bash
curl -X POST http://localhost:8085/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "SecurePass123!",
    "password_confirmation": "SecurePass123!"
  }'
```

### Login

```bash
curl -X POST http://localhost:8085/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "SecurePass123!"
  }'
```

### List Resources

```bash
curl -X GET http://localhost:8085/products \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Create Resource

```bash
curl -X POST http://localhost:8085/products \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "New Product",
    "price": 99.99
  }'
```

### Update Resource

```bash
curl -X PUT http://localhost:8085/products/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "price": 89.99
  }'
```

### Delete Resource

```bash
curl -X DELETE http://localhost:8085/products/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Next Steps

1. **API Testing** - [../03-advanced/API_TESTING.md](../03-advanced/API_TESTING.md)
2. **Postman Guide** - [../03-advanced/POSTMAN_GUIDE.md](../03-advanced/POSTMAN_GUIDE.md)
3. **Frontend Integration** - [../03-advanced/FRONTEND_INTEGRATION.md](../03-advanced/FRONTEND_INTEGRATION.md)

---

**Previous:** [← Troubleshooting](../04-deployment/TROUBLESHOOTING.md) | **Home:** [Documentation Index →](../INDEX.md)
