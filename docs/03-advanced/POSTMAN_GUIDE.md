# 📮 Postman Collection - Quick Start Guide

## Import Collection ke Postman

### Method 1: Direct Import

1. Buka Postman
2. Click **Import** button (top left)
3. Drag & drop file `postman_collection.json`
4. Collection akan muncul di sidebar

### Method 2: Import from File

1. Buka Postman
2. Click **Import** → **File** → **Upload Files**
3. Pilih `postman_collection.json`
4. Click **Import**

---

## 🎯 Collection Overview

Collection ini berisi **semua CRUD endpoints** untuk framework:

| Folder             | Endpoints  | Auth Required   |
| ------------------ | ---------- | --------------- |
| **Authentication** | 4 requests | No (except /me) |
| **Users**          | 5 requests | Yes\*           |
| **Posts**          | 5 requests | Yes\*           |
| **Tags**           | 5 requests | Yes\*           |
| **Comments**       | 6 requests | Yes\*           |
| **Post Tags**      | 3 requests | Yes\*           |

\*GET requests tidak perlu auth, POST/PUT/DELETE perlu auth

**Total:** 28 requests siap pakai!

---

## 🚀 Quick Start Workflow

### Step 1: Register User

```
POST /auth/register
```

- Otomatis save token ke variable
- Otomatis save user_id

### Step 2: Login (Optional)

```
POST /auth/login
```

- Jika sudah register, bisa langsung pakai
- Token akan auto-saved

### Step 3: Create Resources

Jalankan requests ini berurutan:

1. **Create Tag** → Saves tag_id
2. **Create Post** → Saves post_id
3. **Link Post to Tag** → Uses saved IDs
4. **Create Comment** → Attach to post
5. **Create Nested Comment** → Reply to comment

### Step 4: Test GET Endpoints

- Get All Posts
- Get Post by ID
- Get All Comments
- dll.

### Step 5: Test UPDATE/DELETE

- Update Post
- Delete Comment
- Unlink Tag
- dll.

---

## 🔑 Environment Variables

Collection menggunakan variables otomatis:

| Variable     | Description              | Auto-Saved? |
| ------------ | ------------------------ | ----------- |
| `base_url`   | API URL (localhost:8085) | Manual      |
| `auth_token` | JWT Token                | ✅ Yes      |
| `user_id`    | Current user ID          | ✅ Yes      |
| `post_id`    | Last created post        | ✅ Yes      |
| `tag_id`     | Last created tag         | ✅ Yes      |
| `comment_id` | Last created comment     | ✅ Yes      |

**Note:** Variables di-save otomatis setelah sukses create!

---

## 📝 Test Scripts Included

Collection sudah include **test scripts** yang otomatis:

### Register/Login

```javascript
// Auto-save token setelah login
if (pm.response.code === 201) {
  const response = pm.response.json();
  pm.collectionVariables.set("auth_token", response.data.token);
  pm.collectionVariables.set("user_id", response.data.user.id);
}
```

### Create Post/Tag/Comment

```javascript
// Auto-save ID setelah create
if (pm.response.code === 201) {
  const response = pm.response.json();
  pm.collectionVariables.set("post_id", response.data.id);
}
```

---

## 🎨 Request Examples

### 1. Authentication

#### Register

```json
POST /auth/register
{
    "name": "Test User",
    "email": "test@example.com",
    "password": "Test123!@#",
    "password_confirmation": "Test123!@#"
}
```

#### Login

```json
POST /auth/login
{
    "email": "test@example.com",
    "password": "Test123!@#"
}
```

---

### 2. Create Post

```json
POST /posts
Headers: Authorization: Bearer {{auth_token}}

{
    "user_id": 1,
    "title": "My First Blog Post",
    "slug": "my-first-blog-post",
    "content": "This is the content of my first blog post.",
    "excerpt": "A brief introduction",
    "status": "published",
    "published_at": "2026-01-22 10:00:00"
}
```

---

### 3. Create Tag

```json
POST /tags
Headers: Authorization: Bearer {{auth_token}}

{
    "name": "Technology",
    "slug": "technology",
    "description": "Posts about technology"
}
```

---

### 4. Link Post to Tag

```json
POST /post-tags
Headers: Authorization: Bearer {{auth_token}}

{
    "post_id": {{post_id}},   // Auto-filled!
    "tag_id": {{tag_id}}      // Auto-filled!
}
```

---

### 5. Create Comment

```json
POST /comments
Headers: Authorization: Bearer {{auth_token}}

{
    "post_id": {{post_id}},
    "user_id": {{user_id}},
    "content": "Great article!",
    "status": "approved"
}
```

---

### 6. Create Nested Comment (Reply)

```json
POST /comments
Headers: Authorization: Bearer {{auth_token}}

{
    "post_id": {{post_id}},
    "user_id": {{user_id}},
    "parent_id": {{comment_id}},  // Reply to comment!
    "content": "Thank you!",
    "status": "approved"
}
```

---

## 🔧 Modify Collection Variables

### View Variables

1. Click collection name
2. Click **Variables** tab
3. Lihat semua variables

### Edit Base URL

1. Variables tab
2. Find `base_url`
3. Change to your URL (e.g., `https://api.yourdomain.com`)

### Manual Token Input

Jika token tidak auto-save:

1. Variables tab
2. Find `auth_token`
3. Paste token manually

---

## 🎯 Testing Scenarios

### Scenario 1: Complete Blog Post Flow

```
1. Register User ✓
2. Create Tag "Technology" ✓
3. Create Post "My Tech Post" ✓
4. Link Post to Tag ✓
5. Create Comment on Post ✓
6. Create Reply to Comment ✓
7. Get All Posts → See your post
8. Get Post by ID → See with tags & comments
```

### Scenario 2: Update & Delete Flow

```
1. Create Post ✓
2. Update Post ✓
3. Get Post → See changes
4. Delete Post ✓
5. Get Post → 404 Not Found
```

### Scenario 3: Many-to-Many Relationship

```
1. Create Multiple Tags (Tech, News, Tutorial)
2. Create One Post
3. Link Post to all 3 Tags
4. Get Post → See all attached tags
5. Unlink one Tag
6. Get Post → See remaining tags
```

---

## 🐛 Troubleshooting

### "Unauthorized" Error

**Problem:** Request butuh auth tapi token tidak ada

**Solution:**

1. Run **Register** or **Login** request first
2. Check `auth_token` variable is set
3. Check request has Authorization header

### Variables Not Saved

**Problem:** IDs tidak auto-save setelah create

**Solution:**

1. Check response code = 201
2. Check test script ada
3. Manual save: Variables tab → paste ID

### 404 Not Found

**Problem:** Endpoint tidak ditemukan

**Solution:**

1. Check server running: `php -S localhost:8085 -t public`
2. Check `base_url` variable correct
3. Check endpoint path

### Invalid JSON

**Problem:** Request body format salah

**Solution:**

1. Check JSON syntax (commas, brackets)
2. Use Postman's JSON validator
3. Copy from examples di collection

---

## 📚 Additional Tips

### 1. Run Collection with Runner

1. Click collection → **Run**
2. Select requests
3. Click **Run Collection**
4. Semua requests dijalankan otomatis!

### 2. Export Results

- Runner → Export Results
- Share hasil testing

### 3. Create Environment

- Lebih baik: Buat env untuk dev/staging/prod
- Duplik collection variables ke environment
- Switch environment sesuai kebutuhan

### 4. Use Pre-request Scripts

Tambahkan logic sebelum request:

```javascript
// Pre-request Script
const timestamp = Date.now();
pm.collectionVariables.set("timestamp", timestamp);
```

---

## ✅ Checklist

Sebelum testing:

- [ ] Server running (`php -S localhost:8085 -t public`)
- [ ] Database migrated (`php scripts/migrate.php migrate`)
- [ ] Collection imported to Postman
- [ ] `base_url` variable correct

Workflow:

- [ ] Register/Login untuk get token
- [ ] Create resources (Post, Tag, etc.)
- [ ] Test GET endpoints
- [ ] Test UPDATE endpoints
- [ ] Test DELETE endpoints
- [ ] Verify cascade deletes work

---

## 🎉 Ready to Test!

1. **Import** `postman_collection.json`
2. **Run** Register request
3. **Start** creating resources
4. **Test** all endpoints!

**Happy Testing! 🚀**

---

## 📖 Documentation

- **API Docs:** http://localhost:8085/docs
- **Complete Guide:** [docs/README.md](docs/README.md)
- **API Testing:** [docs/API_TESTING.md](docs/API_TESTING.md)
