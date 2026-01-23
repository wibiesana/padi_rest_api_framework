// ============================================
// VUE.JS API CLIENT - READY TO USE
// ============================================
// Copy this file to: src/services/api.js
// ============================================

import axios from "axios";

// Configuration
const config = {
  // Automatically uses environment variable
  // Development: http://localhost:8085 (from .env.development)
  // Production: https://api.yourdomain.com (from .env.production)
  baseURL: import.meta.env.VITE_API_URL || "http://localhost:8085",

  timeout: 15000, // 15 seconds

  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },

  // Set to true ONLY if using cookies for auth
  withCredentials: false,
};

// Create axios instance
const api = axios.create(config);

// ============================================
// REQUEST INTERCEPTOR - Add JWT Token
// ============================================
api.interceptors.request.use(
  (config) => {
    // Get token from localStorage
    const token = localStorage.getItem("access_token");

    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    // Optional: Add timestamp to prevent caching
    if (config.method === "get") {
      config.params = {
        ...config.params,
        _t: Date.now(),
      };
    }

    return config;
  },
  (error) => {
    console.error("Request Error:", error);
    return Promise.reject(error);
  },
);

// ============================================
// RESPONSE INTERCEPTOR - Handle Success & Errors
// ============================================
api.interceptors.response.use(
  // Success Handler
  (response) => {
    // Extract data from nested structure
    // Backend returns: { data: {...}, debug: {...} }
    // We extract just the data part
    if (response.data && response.data.data !== undefined) {
      return response.data.data;
    }

    return response.data;
  },

  // Error Handler
  (error) => {
    if (error.response) {
      const { status, data } = error.response;

      // CORS Error (403)
      if (status === 403 && typeof data === "string" && data.includes("CORS")) {
        console.error("❌ CORS Error: Origin not allowed");
        console.error(
          "💡 Solution: Add your domain to CORS_ALLOWED_ORIGINS in backend .env",
        );

        return Promise.reject({
          message: "CORS Error: Your origin is not allowed to access this API",
          type: "CORS_ERROR",
          status: 403,
        });
      }

      // Unauthorized (401)
      if (status === 401) {
        console.warn("⚠️  Unauthorized: Token invalid or expired");

        // Clear token
        localStorage.removeItem("access_token");
        localStorage.removeItem("user");

        // Redirect to login (adjust route as needed)
        if (window.location.pathname !== "/login") {
          window.location.href = "/login?redirect=" + window.location.pathname;
        }

        return Promise.reject({
          message: "Unauthorized. Please login again.",
          type: "AUTH_ERROR",
          status: 401,
        });
      }

      // Forbidden (403)
      if (status === 403) {
        return Promise.reject({
          message: data.message || "Forbidden: You do not have permission",
          type: "PERMISSION_ERROR",
          status: 403,
        });
      }

      // Validation Error (422)
      if (status === 422) {
        return Promise.reject({
          message: data.message || "Validation failed",
          errors: data.errors || {},
          type: "VALIDATION_ERROR",
          status: 422,
        });
      }

      // Not Found (404)
      if (status === 404) {
        return Promise.reject({
          message: data.message || "Resource not found",
          type: "NOT_FOUND",
          status: 404,
        });
      }

      // Server Error (500)
      if (status >= 500) {
        console.error("❌ Server Error:", data);

        return Promise.reject({
          message: "Server error. Please try again later.",
          debug: data.debug || null,
          type: "SERVER_ERROR",
          status: status,
        });
      }

      // Other errors
      return Promise.reject({
        message: data.message || "An error occurred",
        type: "API_ERROR",
        status: status,
      });
    }

    // Network error (no response)
    if (error.request) {
      console.error("❌ Network Error: No response from server");
      console.error("💡 Check:");
      console.error("   1. Backend server is running");
      console.error("   2. API URL is correct:", config.baseURL);
      console.error("   3. Internet connection");

      return Promise.reject({
        message: "Network error. Cannot connect to server.",
        type: "NETWORK_ERROR",
        status: 0,
      });
    }

    // Unknown error
    return Promise.reject({
      message: error.message || "Unknown error occurred",
      type: "UNKNOWN_ERROR",
      status: 0,
    });
  },
);

// ============================================
// AUTHENTICATION ENDPOINTS
// ============================================
export const auth = {
  /**
   * Login user
   * @param {string} email
   * @param {string} password
   * @returns {Promise<{user: object, token: string}>}
   */
  async login(email, password) {
    const response = await api.post("/auth/login", { email, password });

    // Store token
    if (response.token) {
      localStorage.setItem("access_token", response.token);
    }

    // Store user info
    if (response.user) {
      localStorage.setItem("user", JSON.stringify(response.user));
    }

    return response;
  },

  /**
   * Register new user
   * @param {object} userData - {name, email, password, password_confirmation}
   * @returns {Promise<{user: object, token: string}>}
   */
  async register(userData) {
    const response = await api.post("/auth/register", userData);

    // Store token
    if (response.token) {
      localStorage.setItem("access_token", response.token);
    }

    // Store user info
    if (response.user) {
      localStorage.setItem("user", JSON.stringify(response.user));
    }

    return response;
  },

  /**
   * Logout user
   */
  async logout() {
    try {
      await api.post("/auth/logout");
    } finally {
      // Clear local storage even if API call fails
      localStorage.removeItem("access_token");
      localStorage.removeItem("user");
      window.location.href = "/login";
    }
  },

  /**
   * Get current authenticated user
   * @returns {Promise<object>}
   */
  async me() {
    return await api.get("/auth/me");
  },

  /**
   * Check if user is authenticated
   * @returns {boolean}
   */
  isAuthenticated() {
    return !!localStorage.getItem("access_token");
  },

  /**
   * Get stored user data
   * @returns {object|null}
   */
  getUser() {
    const user = localStorage.getItem("user");
    return user ? JSON.parse(user) : null;
  },

  /**
   * Get stored token
   * @returns {string|null}
   */
  getToken() {
    return localStorage.getItem("access_token");
  },
};

// ============================================
// CRUD HELPER FACTORY
// ============================================
/**
 * Create CRUD methods for a resource
 * @param {string} resource - Resource name (e.g., 'products', 'users')
 * @returns {object} CRUD methods
 */
export const createCrud = (resource) => ({
  /**
   * Get all items with pagination
   * @param {object} params - Query parameters {page, per_page, search}
   */
  getAll(params = {}) {
    return api.get(`/${resource}`, { params });
  },

  /**
   * Get single item by ID
   * @param {number|string} id
   */
  getOne(id) {
    return api.get(`/${resource}/${id}`);
  },

  /**
   * Create new item
   * @param {object} data
   */
  create(data) {
    return api.post(`/${resource}`, data);
  },

  /**
   * Update existing item
   * @param {number|string} id
   * @param {object} data
   */
  update(id, data) {
    return api.put(`/${resource}/${id}`, data);
  },

  /**
   * Delete item
   * @param {number|string} id
   */
  delete(id) {
    return api.delete(`/${resource}/${id}`);
  },

  /**
   * Search items
   * @param {string} query
   */
  search(query) {
    return api.get(`/${resource}`, { params: { search: query } });
  },
});

// ============================================
// RESOURCE ENDPOINTS (Ready to Use)
// ============================================
export const products = createCrud("products");
export const users = createCrud("users");
export const posts = createCrud("posts");
export const comments = createCrud("comments");
export const tags = createCrud("tags");

// Add custom methods to specific resources
products.getLowStock = () => api.get("/products/low-stock");

// ============================================
// EXPORT DEFAULT API CLIENT
// ============================================
export default api;

// ============================================
// USAGE EXAMPLES:
// ============================================

/*
// 1. Authentication
import { auth } from '@/services/api';

// Login
const loginUser = async () => {
  try {
    const response = await auth.login('user@example.com', 'password');
    console.log('Logged in:', response.user);
  } catch (error) {
    console.error('Login failed:', error.message);
  }
};

// Logout
auth.logout();

// Check if authenticated
if (auth.isAuthenticated()) {
  console.log('User is logged in');
}


// 2. Using CRUD Resources
import { products } from '@/services/api';

// Get all products with pagination
const fetchProducts = async () => {
  try {
    const data = await products.getAll({ page: 1, per_page: 10 });
    console.log('Products:', data.data);
    console.log('Pagination:', data.pagination);
  } catch (error) {
    console.error('Error:', error.message);
  }
};

// Get single product
const product = await products.getOne(1);

// Create product
const newProduct = await products.create({
  name: 'Laptop',
  price: 1500,
  stock: 10
});

// Update product
const updated = await products.update(1, { price: 1400 });

// Delete product
await products.delete(1);

// Search products
const results = await products.search('laptop');


// 3. Custom Requests
import api from '@/services/api';

// Custom GET
const data = await api.get('/custom-endpoint');

// Custom POST with params
const result = await api.post('/custom-endpoint', { 
  key: 'value' 
});


// 4. In Vue Component
<script setup>
import { ref, onMounted } from 'vue';
import { products } from '@/services/api';

const productList = ref([]);
const loading = ref(false);
const error = ref(null);

const loadProducts = async () => {
  loading.value = true;
  try {
    const response = await products.getAll({ page: 1, per_page: 20 });
    productList.value = response.data;
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadProducts();
});
</script>


// 5. Error Handling
try {
  await products.create(invalidData);
} catch (error) {
  if (error.type === 'VALIDATION_ERROR') {
    // Show validation errors
    console.log('Validation errors:', error.errors);
  } else if (error.type === 'AUTH_ERROR') {
    // User not authenticated
    console.log('Please login');
  } else if (error.type === 'CORS_ERROR') {
    // CORS configuration issue
    console.log('CORS error - check backend config');
  }
}

*/
