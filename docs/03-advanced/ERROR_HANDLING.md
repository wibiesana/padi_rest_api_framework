# Error Handling & Message Codes

Complete guide to error handling and response codes in Padi REST API Framework.

## Table of Contents

- [Overview](#overview)
- [Response Structure](#response-structure)
- [Message Codes Reference](#message-codes-reference)
- [Success Codes](#success-codes)
- [Error Codes](#error-codes)
- [Frontend Integration](#frontend-integration)
- [Custom Error Handling](#custom-error-handling)
- [Role-Based Access Control (RBAC)](#role-based-access-control-rbac)

## Overview

All API responses include a standardized `message_code` field to help frontend applications identify and handle specific scenarios without parsing error messages.

### Key Benefits

- ✅ **Easy error identification** - No need to parse message strings
- ✅ **Internationalization ready** - Display custom messages per locale
- ✅ **Type-safe handling** - Use constants/enums in frontend
- ✅ **Consistent API** - Same structure across all endpoints

## Response Structure

### Success Response

```json
{
  "success": true,
  "message": "Operation completed successfully",
  "message_code": "SUCCESS",
  "data": {
    // Response data here
  }
}
```

### Error Response

```json
{
  "success": false,
  "message": "Human-readable error message",
  "message_code": "ERROR_CODE_HERE"
}
```

### Validation Error Response

```json
{
  "success": false,
  "message": "Validation failed",
  "message_code": "VALIDATION_FAILED",
  "errors": {
    "email": ["Email is required", "Email must be valid"],
    "password": ["Password must be at least 8 characters"]
  }
}
```

## Message Codes Reference

### Success Codes

| Code         | HTTP Status | Description                              | Usage                          |
| ------------ | ----------- | ---------------------------------------- | ------------------------------ |
| `SUCCESS`    | 200         | Request successful                       | GET, general success responses |
| `CREATED`    | 201         | Resource created successfully            | POST - new resource created    |
| `NO_CONTENT` | 204         | Request successful, no content to return | DELETE - resource deleted      |

**Examples:**

```php
// GET /api/users/1
$this->success($user, 'User retrieved successfully'); // 200, SUCCESS

// POST /api/users
$this->success($user, 'User created successfully', 201); // 201, CREATED
```

## Error Codes

### Authentication & Authorization Errors (401, 403)

| Code                  | HTTP Status | Description                                     | When It Occurs                                        |
| --------------------- | ----------- | ----------------------------------------------- | ----------------------------------------------------- |
| `UNAUTHORIZED`        | 401         | Authentication required (generic)               | Default 401 error                                     |
| `INVALID_CREDENTIALS` | 401         | Login failed - wrong username/email or password | Login endpoint with wrong credentials                 |
| `NO_TOKEN_PROVIDED`   | 401         | No authentication token provided                | Protected route accessed without Bearer token         |
| `INVALID_TOKEN`       | 401         | Invalid or expired token                        | Protected route accessed with invalid/expired token   |
| `FORBIDDEN`           | 403         | Access denied                                   | User doesn't have permission for the requested action |

**Security Note:** `INVALID_CREDENTIALS` uses a generic message "Invalid credentials" to prevent username enumeration attacks, but the `message_code` allows frontend to display specific user-friendly messages.

**Examples:**

```json
// Login with wrong password
POST /api/auth/login
{
  "success": false,
  "message": "Invalid credentials",
  "message_code": "INVALID_CREDENTIALS"
}

// Access protected route without token
GET /api/users
{
  "success": false,
  "message": "Unauthorized - No token provided",
  "message_code": "NO_TOKEN_PROVIDED"
}

// Access with expired token
GET /api/users
{
  "success": false,
  "message": "Unauthorized - Invalid or expired token",
  "message_code": "INVALID_TOKEN"
}
```

### Validation & Client Errors (400, 422)

| Code                | HTTP Status | Description               | When It Occurs                     |
| ------------------- | ----------- | ------------------------- | ---------------------------------- |
| `BAD_REQUEST`       | 400         | Invalid request           | Malformed request, missing headers |
| `VALIDATION_FAILED` | 422         | Request validation failed | Input validation errors            |

**Examples:**

```json
// Validation error
POST /api/auth/register
{
  "success": false,
  "message": "Validation failed",
  "message_code": "VALIDATION_FAILED",
  "errors": {
    "email": ["Email is required"],
    "password": ["Password must be at least 8 characters"]
  }
}
```

### Resource Errors (404)

| Code              | HTTP Status | Description            | When It Occurs                   |
| ----------------- | ----------- | ---------------------- | -------------------------------- |
| `NOT_FOUND`       | 404         | Resource not found     | Requested resource doesn't exist |
| `ROUTE_NOT_FOUND` | 404         | API endpoint not found | Invalid API endpoint             |

**Examples:**

```json
// Resource not found
GET /api/users/99999
{
  "success": false,
  "message": "User not found",
  "message_code": "NOT_FOUND"
}

// Invalid endpoint
GET /api/invalid-endpoint
{
  "success": false,
  "message": "Route not found",
  "message_code": "ROUTE_NOT_FOUND"
}
```

### Rate Limiting (429)

| Code                  | HTTP Status | Description       | When It Occurs      |
| --------------------- | ----------- | ----------------- | ------------------- |
| `RATE_LIMIT_EXCEEDED` | 429         | Too many requests | Rate limit exceeded |

**Example:**

```json
{
  "success": false,
  "message": "Too many requests. Please try again later.",
  "message_code": "RATE_LIMIT_EXCEEDED"
}
```

### Server Errors (500)

| Code                    | HTTP Status | Description  | When It Occurs                |
| ----------------------- | ----------- | ------------ | ----------------------------- |
| `INTERNAL_SERVER_ERROR` | 500         | Server error | Unhandled exceptions, crashes |

**Example:**

```json
{
  "success": false,
  "message": "Internal Server Error",
  "message_code": "INTERNAL_SERVER_ERROR"
}
```

### Generic Error

| Code    | HTTP Status | Description   | When It Occurs             |
| ------- | ----------- | ------------- | -------------------------- |
| `ERROR` | Various     | Generic error | Custom error with any code |

## Frontend Integration

### React Example

```javascript
// API service
const handleApiError = (data) => {
  switch (data.message_code) {
    case "INVALID_CREDENTIALS":
      return "Wrong username or password. Please try again.";

    case "INVALID_TOKEN":
    case "NO_TOKEN_PROVIDED":
      // Redirect to login
      localStorage.removeItem("token");
      window.location.href = "/login";
      return "Session expired. Please login again.";

    case "VALIDATION_FAILED":
      // Handle validation errors
      return Object.values(data.errors).flat().join(", ");

    case "RATE_LIMIT_EXCEEDED":
      return "Too many attempts. Please wait a moment.";

    case "NOT_FOUND":
      return "Resource not found.";

    case "FORBIDDEN":
      return "You do not have permission to perform this action.";

    default:
      return data.message || "An error occurred";
  }
};

// Usage in component
const login = async (credentials) => {
  try {
    const response = await fetch("/api/auth/login", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(credentials),
    });

    const data = await response.json();

    if (data.success) {
      // Handle success
      localStorage.setItem("token", data.data.token);
      navigate("/dashboard");
    } else {
      // Handle error with message_code
      const errorMessage = handleApiError(data);
      setError(errorMessage);
    }
  } catch (error) {
    setError("Network error. Please try again.");
  }
};
```

### Vue 3 Example

```javascript
// composables/useApi.js
export const useApi = () => {
  const handleError = (data) => {
    const messages = {
      INVALID_CREDENTIALS: 'Username atau password salah',
      INVALID_TOKEN: 'Sesi Anda telah berakhir',
      NO_TOKEN_PROVIDED: 'Silakan login terlebih dahulu',
      VALIDATION_FAILED: 'Data yang Anda masukkan tidak valid',
      RATE_LIMIT_EXCEEDED: 'Terlalu banyak percobaan, tunggu sebentar',
      NOT_FOUND: 'Data tidak ditemukan',
      FORBIDDEN: 'Anda tidak memiliki akses',
    };

    return messages[data.message_code] || data.message;
  };

  return { handleError };
};

// Usage in component
<script setup>
import { ref } from 'vue';
import { useApi } from '@/composables/useApi';

const { handleError } = useApi();
const error = ref('');

const login = async (credentials) => {
  const response = await fetch('/api/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(credentials)
  });

  const data = await response.json();

  if (!data.success) {
    error.value = handleError(data);
  }
};
</script>
```

### Angular Example

```typescript
// error-handler.service.ts
import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class ErrorHandlerService {
  handleApiError(data: any): string {
    const errorMessages: { [key: string]: string } = {
      INVALID_CREDENTIALS: 'Invalid username or password',
      INVALID_TOKEN: 'Your session has expired',
      NO_TOKEN_PROVIDED: 'Please login to continue',
      VALIDATION_FAILED: 'Please check your input',
      RATE_LIMIT_EXCEEDED: 'Too many requests. Please wait.',
      NOT_FOUND: 'Resource not found',
      FORBIDDEN: 'Access denied'
    };

    return errorMessages[data.message_code] || data.message;
  }
}

// auth.service.ts
login(credentials: any) {
  return this.http.post('/api/auth/login', credentials).pipe(
    map((data: any) => {
      if (!data.success) {
        throw new Error(this.errorHandler.handleApiError(data));
      }
      return data;
    })
  );
}
```

### TypeScript Constants

```typescript
// constants/message-codes.ts
export enum MessageCode {
  // Success
  SUCCESS = "SUCCESS",
  CREATED = "CREATED",
  NO_CONTENT = "NO_CONTENT",

  // Auth errors
  UNAUTHORIZED = "UNAUTHORIZED",
  INVALID_CREDENTIALS = "INVALID_CREDENTIALS",
  NO_TOKEN_PROVIDED = "NO_TOKEN_PROVIDED",
  INVALID_TOKEN = "INVALID_TOKEN",
  FORBIDDEN = "FORBIDDEN",

  // Client errors
  BAD_REQUEST = "BAD_REQUEST",
  VALIDATION_FAILED = "VALIDATION_FAILED",
  NOT_FOUND = "NOT_FOUND",
  ROUTE_NOT_FOUND = "ROUTE_NOT_FOUND",

  // Rate limit
  RATE_LIMIT_EXCEEDED = "RATE_LIMIT_EXCEEDED",

  // Server error
  INTERNAL_SERVER_ERROR = "INTERNAL_SERVER_ERROR",
  ERROR = "ERROR",
}

export interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  message_code: MessageCode;
  data?: T;
  errors?: Record<string, string[]>;
}

// Usage
const handleResponse = (response: ApiResponse) => {
  switch (response.message_code) {
    case MessageCode.INVALID_CREDENTIALS:
      // Handle invalid credentials
      break;
    case MessageCode.INVALID_TOKEN:
      // Redirect to login
      break;
    // ... etc
  }
};
```

## Custom Error Handling

### In Controllers

You can pass custom `message_code` to error methods:

```php
<?php

namespace App\Controllers;

use Core\Controller;

class ProductController extends Controller
{
    public function show(int $id): void
    {
        $product = $this->model->find($id);

        if (!$product) {
            // Custom message code for specific scenario
            $this->error('Product not found', 404, null, 'PRODUCT_NOT_FOUND');
        }

        $this->success($product);
    }

    public function purchase(int $id): void
    {
        $product = $this->model->find($id);

        if (!$product) {
            $this->notFound('Product not found');
        }

        if ($product['stock'] < 1) {
            // Custom error for out of stock
            $this->error('Product is out of stock', 400, null, 'OUT_OF_STOCK');
        }

        // Process purchase...
        $this->success($order, 'Purchase successful', 201);
    }
}
```

### Available Controller Methods

```php
// Success responses
$this->success($data, 'Message', 200);           // message_code: SUCCESS
$this->success($data, 'Created', 201);           // message_code: CREATED

// Error responses (automatic message_code)
$this->error('Message', 400);                    // message_code: BAD_REQUEST
$this->unauthorized('Message');                  // message_code: UNAUTHORIZED
$this->forbidden('Message');                     // message_code: FORBIDDEN
$this->notFound('Message');                      // message_code: NOT_FOUND

// Error with custom message_code
$this->error('Message', 400, null, 'CUSTOM_CODE');
$this->unauthorized('Message', 'CUSTOM_AUTH_CODE');

// Validation (automatic)
$this->validate([...]);                          // message_code: VALIDATION_FAILED
```

### Direct Response Usage

```php
use Core\Response;

$response = new Response();

// Custom error with specific code
$response->json([
    'success' => false,
    'message' => 'Payment processing failed',
    'message_code' => 'PAYMENT_FAILED',
    'data' => [
        'transaction_id' => '12345',
        'reason' => 'Insufficient funds'
    ]
], 402);
```

## Best Practices

### 1. Use Specific Codes When Appropriate

```php
// ❌ Generic
$this->error('Error', 400);

// ✅ Specific
$this->error('Product out of stock', 400, null, 'OUT_OF_STOCK');
```

### 2. Keep Messages User-Friendly

```php
// ❌ Technical
$this->error('Foreign key constraint violation', 400);

// ✅ User-friendly
$this->error('Cannot delete user with active orders', 400, null, 'HAS_DEPENDENCIES');
```

### 3. Security-First for Auth Errors

```php
// ✅ Good - Generic message, specific code
$this->unauthorized('Invalid credentials', 'INVALID_CREDENTIALS');

// ❌ Bad - Reveals user existence
$this->error('User not found', 404);
```

### 4. Document Custom Codes

If you add custom `message_code` values, document them in your API documentation.

```php
/**
 * Purchase product
 *
 * @throws 400 OUT_OF_STOCK - Product is out of stock
 * @throws 402 PAYMENT_FAILED - Payment processing failed
 * @throws 404 PRODUCT_NOT_FOUND - Product not found
 */
public function purchase(int $id): void { }
```

## Debugging

In development mode (`APP_DEBUG=true`), responses include debug information:

```json
{
  "data": {
    "success": false,
    "message": "Internal Server Error",
    "message_code": "INTERNAL_SERVER_ERROR"
  },
  "debug": {
    "execution_time": "45.23ms",
    "memory_usage": "12.45MB",
    "query_count": 3,
    "queries": [
      // SQL queries if DEBUG_SHOW_QUERIES=true
    ]
  }
}
```

## Role-Based Access Control (RBAC)

Implement role-based authorization to control access to resources based on user roles.

### Overview

Padi REST API provides built-in support for role-based access control with:

- ✅ **RoleMiddleware** for route-level protection
- ✅ **Controller helper methods** for granular permission checks
- ✅ **Owner-based access** for resource ownership validation
- ✅ **Standardized error responses** with proper message codes

### Common Use Cases

1. **Admin**: Full access to all resources
2. **Teacher**: Can view/manage students, cannot manage other teachers
3. **Student**: Can only view/update own data

### RoleMiddleware Usage

Protect routes at the middleware level:

```php
// routes/api.php
use Core\Router;

$router = new Router();

// Require authentication only
$router->get('/profile', [UserController::class, 'getProfile'])
    ->middleware(['AuthMiddleware']);

// Require admin role
$router->get('/admin/dashboard', [AdminController::class, 'index'])
    ->middleware(['AuthMiddleware', 'RoleMiddleware:admin']);

// Require either admin or teacher role
$router->get('/reports', [ReportController::class, 'index'])
    ->middleware(['AuthMiddleware', 'RoleMiddleware:admin,teacher']);

// Multiple roles with comma-separated list
$router->post('/students', [StudentController::class, 'create'])
    ->middleware(['AuthMiddleware', 'RoleMiddleware:admin,teacher']);
```

### Controller Helper Methods

Use built-in helper methods for fine-grained authorization:

#### 1. `hasRole(string $role): bool`

Check if user has specific role:

```php
if ($this->hasRole('admin')) {
    // Admin-specific logic
}

if ($this->hasRole('teacher')) {
    // Teacher-specific logic
}
```

#### 2. `hasAnyRole(array $roles): bool`

Check if user has any of the specified roles:

```php
if ($this->hasAnyRole(['admin', 'teacher'])) {
    // Logic for admin or teacher
}
```

#### 3. `requireRole(string $role, ?string $message = null): void`

Require specific role or throw 403 error:

```php
public function adminDashboard(): void
{
    $this->requireRole('admin');
    // Only admins reach here

    $stats = [...];
    $this->success($stats);
}
```

#### 4. `requireAnyRole(array $roles, ?string $message = null): void`

Require any of specified roles:

```php
public function viewReports(): void
{
    $this->requireAnyRole(['admin', 'teacher'], 'Only admin and teachers can view reports');
    // Admins and teachers reach here

    $reports = [...];
    $this->success($reports);
}
```

#### 5. `isOwner(int $resourceUserId): bool`

Check if current user owns the resource:

```php
public function updateProfile(): void
{
    $userId = (int)$this->request->param('id');

    if (!$this->isOwner($userId)) {
        $this->forbidden('You can only update your own profile');
    }

    // Update logic
}
```

#### 6. `isAdmin(): bool`

Quick check for admin role:

```php
if ($this->isAdmin()) {
    // Show all data including sensitive info
} else {
    // Filter sensitive data
}
```

#### 7. `requireAdminOrOwner(int $resourceUserId, ?string $message = null): void`

Require admin role or resource ownership:

```php
public function update(): void
{
    $userId = (int)$this->request->param('id');
    $user = $this->model->find($userId);

    if (!$user) {
        $this->notFound('User not found');
    }

    // Only admin or the user themselves can update
    $this->requireAdminOrOwner((int)$user['id']);

    // Update logic
}
```

### Real-World Examples

#### Example 1: Student Can Only View Own Data

```php
public function viewStudent(): void
{
    $studentId = (int)$this->request->param('id');
    $student = $this->model->find($studentId);

    if (!$student || $student['role'] !== 'student') {
        $this->notFound('Student not found');
    }

    // Permission check
    if ($this->isAdmin()) {
        // Admin can view any student
    } elseif ($this->hasRole('teacher')) {
        // Teacher can view their students
        // (You would check teacher_id relationship here)
    } elseif ($this->hasRole('student')) {
        // Student can only view their own data
        if (!$this->isOwner($studentId)) {
            $this->forbidden('Students can only view their own data');
        }
    } else {
        $this->forbidden('You do not have permission to view student data');
    }

    $this->success($student);
}
```

#### Example 2: Teacher Cannot Update Student, Only Student or Admin Can

```php
public function updateStudent(): void
{
    $studentId = (int)$this->request->param('id');
    $student = $this->model->find($studentId);

    if (!$student || $student['role'] !== 'student') {
        $this->notFound('Student not found');
    }

    // Permission check
    if ($this->hasRole('student')) {
        // Student can only update their own data
        if (!$this->isOwner($studentId)) {
            $this->forbidden('You can only update your own data');
        }
    } elseif ($this->hasRole('teacher')) {
        // Teacher CANNOT update student data
        $this->forbidden('Teachers cannot update student data');
    } elseif (!$this->isAdmin()) {
        // Must be admin or the student themselves
        $this->forbidden('You do not have permission to update student data');
    }

    // Validation and update logic
    $validated = $this->validate([...]);

    // Students cannot change their role
    if ($this->hasRole('student')) {
        unset($validated['role'], $validated['status']);
    }

    $this->model->update($studentId, $validated);
    $this->success($this->model->find($studentId));
}
```

#### Example 3: Only Admin Can Update Teacher

```php
public function updateTeacher(): void
{
    $teacherId = (int)$this->request->param('id');
    $teacher = $this->model->find($teacherId);

    if (!$teacher || $teacher['role'] !== 'teacher') {
        $this->notFound('Teacher not found');
    }

    // Permission check
    if ($this->hasRole('teacher')) {
        // Teacher can update their own profile only
        if (!$this->isOwner($teacherId)) {
            $this->forbidden('Teachers can only update their own profile');
        }
    } elseif ($this->hasRole('student')) {
        // Student cannot update any teacher
        $this->forbidden('Students cannot update teacher data');
    } elseif (!$this->isAdmin()) {
        // Only admin can update other teachers
        $this->forbidden('Only administrators can update teacher data');
    }

    // Validation and update logic
    $validated = $this->validate([...]);

    // Teachers cannot change their own role
    if ($this->hasRole('teacher') && !$this->isAdmin()) {
        unset($validated['role'], $validated['status']);
    }

    $this->model->update($teacherId, $validated);
    $this->success($this->model->find($teacherId));
}
```

#### Example 4: Different Data Based on Role

```php
public function listUsers(): void
{
    $this->requireAnyRole(['admin', 'teacher']);

    $query = $this->model::findQuery();

    // Teachers can only see students
    if ($this->hasRole('teacher')) {
        $query->where('role', 'student');
    }
    // Admin can see everyone (no filter)

    $users = $query->all();

    // Filter sensitive data for non-admins
    if (!$this->isAdmin()) {
        $users = array_map(function ($user) {
            unset($user['password'], $user['remember_token']);
            return $user;
        }, $users);
    }

    $this->success(['data' => $users]);
}
```

#### Example 5: Admin and Teacher Can Create Students

```php
public function createStudent(): void
{
    // Admin and Teacher can create students
    $this->requireAnyRole(['admin', 'teacher'], 'Only admin and teachers can create students');

    $validated = $this->validate([
        'username' => 'max:50|unique:users,username',
        'email' => 'required|max:255|email|unique:users,email',
        'password' => 'required|max:255',
    ]);

    // Force role to student
    $validated['role'] = 'student';
    $validated['status'] = 'active';

    $id = $this->model->create($validated);

    $this->success([
        'id' => $id,
        'student' => $this->model->find($id)
    ], 'Student created successfully', 201);
}
```

### Error Responses

RBAC errors return standardized message codes:

```json
// No authentication token
{
  "success": false,
  "message": "Authentication required",
  "message_code": "UNAUTHORIZED"
}

// Wrong role
{
  "success": false,
  "message": "You do not have permission to access this resource",
  "message_code": "FORBIDDEN"
}

// Custom message
{
  "success": false,
  "message": "Only admin and teachers can create students",
  "message_code": "FORBIDDEN"
}

// Owner check failed
{
  "success": false,
  "message": "You can only update your own data",
  "message_code": "FORBIDDEN"
}
```

### Best Practices

#### 1. Use Middleware for Route-Level Protection

```php
// ✅ Good - Protect entire route
$router->get('/admin/users', [AdminController::class, 'index'])
    ->middleware(['AuthMiddleware', 'RoleMiddleware:admin']);

// ❌ Avoid - Checking in every controller method
public function index(): void {
    if (!$this->hasRole('admin')) {
        $this->forbidden();
    }
    // ...
}
```

#### 2. Use Helper Methods for Granular Control

```php
// ✅ Good - Clean and readable
public function update(): void
{
    $this->requireAdminOrOwner($resourceId);
    // Update logic
}

// ❌ Avoid - Verbose and hard to maintain
public function update(): void
{
    if (!$this->request->user) {
        $this->unauthorized();
    }
    $isAdmin = $this->request->user->role === 'admin';
    $isOwner = $this->request->user->user_id == $resourceId;
    if (!$isAdmin && !$isOwner) {
        $this->forbidden();
    }
    // Update logic
}
```

#### 3. Fail Secure (Deny by Default)

```php
// ✅ Good - Explicit allow
if ($this->isAdmin() || $this->isOwner($resourceId)) {
    // Allow action
} else {
    $this->forbidden();
}

// ❌ Avoid - Implicit allow
if (!$this->isAdmin() && !$this->isOwner($resourceId)) {
    $this->forbidden();
}
// Implicit allow (dangerous)
```

#### 4. Check Resource Existence Before Authorization

```php
// ✅ Good - Check existence first
$resource = $this->model->find($id);
if (!$resource) {
    $this->notFound();
}
$this->requireAdminOrOwner((int)$resource['user_id']);

// ❌ Avoid - Reveals if resource exists
$this->requireAdminOrOwner($id); // If fails, user knows resource exists
$resource = $this->model->find($id);
```

#### 5. Use Custom Messages for Better UX

```php
// ✅ Good - Descriptive message
$this->requireRole('admin', 'Only administrators can delete users');

// ⚠️ Okay - Generic message
$this->requireRole('admin');
```

#### 6. Filter Data Based on Role

```php
// ✅ Good - Different data for different roles
$user = $this->model->find($id);
unset($user['password']); // Always remove password

if (!$this->isAdmin()) {
    unset($user['remember_token']); // Remove sensitive data
    unset($user['email_verified_at']);
}

$this->success($user);
```

#### 7. Document Permissions

```php
/**
 * Update student data
 *
 * @permission admin - Can update any student
 * @permission student - Can only update own data
 * @permission teacher - Cannot update student data
 */
public function updateStudent(): void
{
    // Implementation
}
```

### Complete Example Controller

See [app/Controllers/ExampleRBACController.php](../../app/Controllers/ExampleRBACController.php) for a complete working example with all scenarios.

### Testing RBAC

```bash
# Test as student (can only access own data)
curl -H "Authorization: Bearer STUDENT_TOKEN" \
  http://localhost:8085/students/1

# Test as teacher (can view students, cannot update)
curl -H "Authorization: Bearer TEACHER_TOKEN" \
  http://localhost:8085/students

# Test as admin (full access)
curl -H "Authorization: Bearer ADMIN_TOKEN" \
  -X PUT http://localhost:8085/teachers/5 \
  -d '{"status":"inactive"}'
```

## Related Documentation

- [API Testing Guide](API_TESTING.md)
- [Frontend Integration](FRONTEND_INTEGRATION.md)
- [Security Best Practices](SECURITY.md)
- [Postman Collections](POSTMAN_GUIDE.md)

---

**Last Updated:** 2026-01-24  
**Version:** 1.0.0
