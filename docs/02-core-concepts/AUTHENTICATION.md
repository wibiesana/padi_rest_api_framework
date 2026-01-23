# 🔐 Authentication & Security

**Padi REST API Framework v2.0**

---

## Overview

Padi REST API uses **JWT (JSON Web Tokens)** for stateless authentication, providing secure and scalable user authentication.

### Security Score: 9.0/10 🛡️

---

## JWT Authentication Flow

### 1. Registration/Login Flow

```
┌─────────┐                  ┌─────────┐                  ┌──────────┐
│ Client  │                  │   API   │                  │ Database │
└────┬────┘                  └────┬────┘                  └────┬─────┘
     │                            │                            │
     │  POST /auth/login          │                            │
     │  {email, password}         │                            │
     ├───────────────────────────>│                            │
     │                            │                            │
     │                            │  Verify credentials        │
     │                            ├───────────────────────────>│
     │                            │                            │
     │                            │  User data                 │
     │                            │<───────────────────────────┤
     │                            │                            │
     │                            │  Generate JWT token        │
     │                            │                            │
     │  {token, user}             │                            │
     │<───────────────────────────┤                            │
     │                            │                            │
     │  Store token in            │                            │
     │  localStorage              │                            │
     │                            │                            │
```

### 2. Authenticated Request Flow

```
┌─────────┐                  ┌─────────┐
│ Client  │                  │   API   │
└────┬────┘                  └────┬────┘
     │                            │
     │  GET /protected-route      │
     │  Authorization: Bearer ... │
     ├───────────────────────────>│
     │                            │
     │                            │  Validate JWT token
     │                            │  - Check signature
     │                            │  - Check expiry
     │                            │  - Extract user ID
     │                            │
     │  {data}                    │
     │<───────────────────────────┤
     │                            │
```

---

## Authentication Endpoints

### 1. Register New User

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

**Response:**

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

### 2. Login

**Endpoint:** `POST /auth/login`

**Request:**

```json
{
  "email": "john@example.com",
  "password": "SecurePass123!"
}
```

**Response:**

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

### 3. Get Current User

**Endpoint:** `GET /auth/me`

**Headers:**

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Response:**

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

### 4. Logout

**Endpoint:** `POST /auth/logout`

**Headers:**

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Response:**

```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

### 5. Refresh Token

**Endpoint:** `POST /auth/refresh`

**Headers:**

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Response:**

```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
  }
}
```

---

## Password Requirements

### Validation Rules

New passwords must meet these requirements:

- ✅ **Minimum 8 characters**
- ✅ **At least 1 uppercase letter** (A-Z)
- ✅ **At least 1 lowercase letter** (a-z)
- ✅ **At least 1 number** (0-9)
- ✅ **At least 1 special character** (@$!%\*?&#)

### Valid Examples

```
✅ Admin123!
✅ SecurePass@2024
✅ MyP@ssw0rd
✅ Test#User99
```

### Invalid Examples

```
❌ password         (no uppercase, number, special char)
❌ PASSWORD123      (no lowercase, special char)
❌ Pass1!           (too short)
❌ MyPassword       (no number, special char)
```

### Password Hashing

Passwords are hashed using **bcrypt** with cost factor 10:

```php
// Hashing
$hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

// Verification
password_verify($inputPassword, $hashedPassword);
```

---

## JWT Configuration

### Environment Variables

```env
JWT_SECRET=your-64-character-random-secret-here
JWT_ALGORITHM=HS256
JWT_EXPIRY=3600
```

- **JWT_SECRET**: Secret key for signing tokens (64+ characters)
- **JWT_ALGORITHM**: Signing algorithm (HS256, HS384, HS512)
- **JWT_EXPIRY**: Token expiry time in seconds (3600 = 1 hour)

### Generate Strong JWT Secret

```bash
# Method 1: PHP
php -r "echo bin2hex(random_bytes(32));"

# Method 2: OpenSSL
openssl rand -hex 32

# Output example:
# 9a6d4f7ebe57a4ebd702e6108f4e5bd1722fa2812ae4b9ae696ce68739e06b18b
```

---

## Security Features

### Implemented Security Measures

| Feature                      | Status | Description                                 |
| ---------------------------- | ------ | ------------------------------------------- |
| **SQL Injection Protection** | ✅     | PDO prepared statements + column validation |
| **XSS Protection**           | ✅     | X-XSS-Protection header                     |
| **CSRF Protection**          | ✅     | Stateless JWT (no cookies)                  |
| **Clickjacking Protection**  | ✅     | X-Frame-Options: DENY                       |
| **MIME Sniffing Protection** | ✅     | X-Content-Type-Options: nosniff             |
| **Password Hashing**         | ✅     | Bcrypt with cost 10                         |
| **Rate Limiting**            | ✅     | 60 requests/minute per IP                   |
| **CORS Whitelist**           | ✅     | Environment-based configuration             |
| **HTTPS Enforcement**        | ✅     | HSTS header (production)                    |
| **Input Validation**         | ✅     | Required for all endpoints                  |

### Security Headers

Automatically added to all responses:

```
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Strict-Transport-Security: max-age=31536000; includeSubDomains
```

---

## Rate Limiting

### Configuration

```env
RATE_LIMIT_MAX=60
RATE_LIMIT_WINDOW=60
```

- **RATE_LIMIT_MAX**: Maximum requests allowed
- **RATE_LIMIT_WINDOW**: Time window in seconds

### Default Limits

- **60 requests per minute** per IP address
- Applies to all endpoints
- Returns `429 Too Many Requests` when exceeded

### Rate Limit Headers

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1706000000
```

### Bypass Rate Limiting (Development)

Set in `.env`:

```env
APP_ENV=development
RATE_LIMIT_MAX=1000
```

---

## CORS Configuration

### Development Setup

```env
APP_ENV=development
CORS_ALLOWED_ORIGINS=
```

**Empty value = Allow all origins** (for local development)

### Production Setup

```env
APP_ENV=production
CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://app.yourdomain.com
```

**Comma-separated list** of allowed origins

### CORS Headers

```
Access-Control-Allow-Origin: https://yourdomain.com
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
Access-Control-Max-Age: 86400
```

---

## Using Authentication in Controllers

### Protect Routes

Routes in `routes/api.php`:

```php
// Public routes
$router->post('/auth/register', [AuthController::class, 'register']);
$router->post('/auth/login', [AuthController::class, 'login']);

// Protected routes (require authentication)
$router->get('/auth/me', [AuthController::class, 'me']);
$router->get('/products', [ProductController::class, 'index']);
$router->post('/products', [ProductController::class, 'store']);
```

### Get Current User in Controller

```php
class ProductController extends BaseController
{
    public function index(): void
    {
        // Get authenticated user
        $user = $this->getAuthUser();

        // User is automatically available if route is protected
        $userId = $user['id'];
        $userName = $user['name'];

        // Your logic here
    }
}
```

---

## Frontend Integration

### Store Token

```javascript
// After login
const response = await api.post("/auth/login", {
  email: "user@example.com",
  password: "SecurePass123!",
});

// Store token
localStorage.setItem("access_token", response.data.token);
```

### Send Token with Requests

```javascript
// Axios interceptor
axios.interceptors.request.use((config) => {
  const token = localStorage.getItem("access_token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});
```

### Handle Token Expiry

```javascript
// Response interceptor
axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Token expired or invalid
      localStorage.removeItem("access_token");
      window.location.href = "/login";
    }
    return Promise.reject(error);
  },
);
```

See [FRONTEND_INTEGRATION.md](../03-advanced/FRONTEND_INTEGRATION.md) for complete examples.

---

## Security Best Practices

### Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate new JWT_SECRET (64+ characters)
- [ ] Configure CORS_ALLOWED_ORIGINS with specific domains
- [ ] Enable HTTPS (SSL/TLS)
- [ ] Use strong database password
- [ ] Set appropriate rate limits
- [ ] Enable response compression
- [ ] Disable DEBUG_SHOW_QUERIES

### JWT Best Practices

1. **Never expose JWT_SECRET**
2. **Use different secrets** for dev/staging/production
3. **Set appropriate expiry** (1-24 hours)
4. **Implement token refresh** for long sessions
5. **Validate tokens** on every request
6. **Store tokens securely** (localStorage or httpOnly cookies)

### Password Best Practices

1. **Enforce strong passwords** (8+ chars, mixed case, numbers, symbols)
2. **Never store plain passwords**
3. **Use bcrypt** with cost 10+
4. **Implement password reset** with email verification
5. **Rate limit login attempts**

---

## Troubleshooting

### Common Issues

| Issue                 | Solution                                    |
| --------------------- | ------------------------------------------- |
| 401 Unauthorized      | Check if token is valid and not expired     |
| Invalid JWT signature | Verify JWT_SECRET matches                   |
| Token expired         | Refresh token or login again                |
| CORS error            | Add frontend domain to CORS_ALLOWED_ORIGINS |
| 429 Too Many Requests | Wait or increase rate limit                 |

---

## Next Steps

1. **Models** - [MODELS.md](MODELS.md)
2. **Controllers** - [CONTROLLERS.md](CONTROLLERS.md)
3. **Security Best Practices** - [../03-advanced/SECURITY.md](../03-advanced/SECURITY.md)

---

**Previous:** [← First Steps](../01-getting-started/FIRST_STEPS.md) | **Next:** [Models Guide →](MODELS.md)
