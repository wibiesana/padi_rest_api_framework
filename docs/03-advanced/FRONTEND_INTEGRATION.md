# 🌐 Frontend Integration Guide

**Supports all frameworks:** Vue, React, Angular, Next.js, Vanilla JavaScript

---

## 📋 TABLE OF CONTENTS

1. [Overview](#overview)
2. [Vue.js Integration](#vuejs-integration)
3. [React Integration](#react-integration)
4. [Angular Integration](#angular-integration)
5. [Next.js Integration](#nextjs-integration)
6. [Vanilla JavaScript](#vanilla-javascript)
7. [CORS Configuration](#cors-configuration)

---

## Overview

This Padi REST API framework is compatible with **all frontend frameworks** as it uses standard REST API with JSON responses and JWT authentication.

**What you need to do:**

1. Configure environment variables (API URL)
2. Setup HTTP client (axios, fetch, etc.)
3. Add JWT token to Authorization header
4. Handle responses

---

## Vue.js Integration

### Setup (Vue 3 + Vite/Vue CLI)

#### 1. Install Dependencies

```bash
npm install axios
```

#### 2. Environment Configuration

**`.env.development`:**

```env
VITE_API_URL=http://localhost:8085
# Or for Vue CLI:
VUE_APP_API_URL=http://localhost:8085
```

**`.env.production`:**

```env
VITE_API_URL=https://api.yourdomain.com
VUE_APP_API_URL=https://api.yourdomain.com
```

#### 3. Create API Service

**`src/services/api.js`:**

```javascript
import axios from "axios";

const API_URL = import.meta.env.VITE_API_URL || process.env.VUE_APP_API_URL;

const api = axios.create({
  baseURL: API_URL,
  headers: { "Content-Type": "application/json" },
});

// Add token to requests
api.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

// Handle errors
api.interceptors.response.use(
  (response) => response.data.data || response.data,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem("token");
      window.location = "/login";
    }
    return Promise.reject(error);
  },
);

export default api;
```

#### 4. Usage in Components

**Composition API:**

```vue
<script setup>
import { ref, onMounted } from "vue";
import api from "@/services/api";

const products = ref([]);
const loading = ref(false);

const fetchProducts = async () => {
  loading.value = true;
  try {
    const response = await api.get("/products");
    products.value = response.data;
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchProducts);
</script>
```

**Options API:**

```vue
<script>
import api from "@/services/api";

export default {
  data() {
    return {
      products: [],
      loading: false,
    };
  },

  async mounted() {
    await this.fetchProducts();
  },

  methods: {
    async fetchProducts() {
      this.loading = true;
      try {
        const response = await api.get("/products");
        this.products = response.data;
      } catch (error) {
        console.error(error);
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
```

---

## React Integration

### Setup (React 18 + Vite/CRA)

#### 1. Install Dependencies

```bash
npm install axios
```

#### 2. Environment Configuration

**`.env.development`:**

```env
VITE_API_URL=http://localhost:8085
# Or for CRA:
REACT_APP_API_URL=http://localhost:8085
```

#### 3. Create API Service

**`src/services/api.js`:**

```javascript
import axios from "axios";

const API_URL = import.meta.env.VITE_API_URL || process.env.REACT_APP_API_URL;

const api = axios.create({
  baseURL: API_URL,
  headers: { "Content-Type": "application/json" },
});

// Request interceptor
api.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

// Response interceptor
api.interceptors.response.use(
  (response) => response.data.data || response.data,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem("token");
      window.location = "/login";
    }
    return Promise.reject(error);
  },
);

export default api;
```

#### 4. Usage in Components

**Functional Component with Hooks:**

```javascript
import { useState, useEffect } from "react";
import api from "./services/api";

function ProductList() {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchProducts = async () => {
      try {
        const data = await api.get("/products");
        setProducts(data.data);
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    fetchProducts();
  }, []);

  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error}</div>;

  return (
    <div>
      {products.map((product) => (
        <div key={product.id}>
          {product.name} - ${product.price}
        </div>
      ))}
    </div>
  );
}

export default ProductList;
```

---

## Angular Integration

### Setup (Angular 15+)

#### 1. Environment Configuration

**`src/environments/environment.ts`:**

```typescript
export const environment = {
  production: false,
  apiUrl: "http://localhost:8085",
};
```

**`src/environments/environment.prod.ts`:**

```typescript
export const environment = {
  production: true,
  apiUrl: "https://api.yourdomain.com",
};
```

#### 2. Create API Service

**`src/app/services/api.service.ts`:**

```typescript
import { Injectable } from "@angular/core";
import {
  HttpClient,
  HttpHeaders,
  HttpErrorResponse,
} from "@angular/common/http";
import { Observable, throwError } from "rxjs";
import { catchError, map } from "rxjs/operators";
import { environment } from "../../environments/environment";

@Injectable({
  providedIn: "root",
})
export class ApiService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  private getHeaders(): HttpHeaders {
    const token = localStorage.getItem("token");
    return new HttpHeaders({
      "Content-Type": "application/json",
      ...(token && { Authorization: `Bearer ${token}` }),
    });
  }

  get<T>(endpoint: string): Observable<T> {
    return this.http
      .get<any>(`${this.apiUrl}${endpoint}`, {
        headers: this.getHeaders(),
      })
      .pipe(
        map((response) => response.data || response),
        catchError(this.handleError),
      );
  }

  // ... other methods (post, put, delete)

  private handleError(error: HttpErrorResponse) {
    if (error.status === 401) {
      localStorage.removeItem("token");
      window.location.href = "/login";
    }
    return throwError(() => new Error(error.message));
  }
}
```

---

## Next.js Integration

### Setup (Next.js 14+)

#### 1. Environment Configuration

**`.env.local`:**

```env
NEXT_PUBLIC_API_URL=http://localhost:8085
```

#### 2. Create API Service

**`lib/api.js`:**

```javascript
const API_URL = process.env.NEXT_PUBLIC_API_URL;

class ApiService {
  constructor() {
    this.baseURL = API_URL;
  }

  async request(endpoint, options = {}) {
    const token =
      typeof window !== "undefined" ? localStorage.getItem("token") : null;

    const config = {
      ...options,
      headers: {
        "Content-Type": "application/json",
        ...(token && { Authorization: `Bearer ${token}` }),
        ...options.headers,
      },
    };

    try {
      const response = await fetch(`${this.baseURL}${endpoint}`, config);
      const data = await response.json();

      if (!response.ok) {
        if (response.status === 401 && typeof window !== "undefined") {
          localStorage.removeItem("token");
          window.location.href = "/login";
        }
        throw new Error(data.message || "API Error");
      }

      return data.data || data;
    } catch (error) {
      throw error;
    }
  }

  get(endpoint) {
    return this.request(endpoint);
  }
  post(endpoint, body) {
    return this.request(endpoint, {
      method: "POST",
      body: JSON.stringify(body),
    });
  }
}

export default new ApiService();
```

---

## Vanilla JavaScript

### Pure JavaScript (No Framework)

#### 1. Create API Service (See lib/api.js above)

#### 2. Usage Example

```html
<script src="js/api.js"></script>
<script>
  async function loadProducts() {
    try {
      const response = await api.get("/products");
      const products = response.data;
      // Render components
    } catch (error) {
      console.error(error);
    }
  }
  document.addEventListener("DOMContentLoaded", loadProducts);
</script>
```

---

## CORS Configuration

By default, the API allowed all origins in development mode. In production, you must whitelist your domains in the `.env` file.

**`.env`:**

```env
CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://admin.yourdomain.com
```

[⬅️ Back to Docs Index](INDEX.md)
