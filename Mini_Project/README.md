# 🚦 Dynamic API Rate Limiting & Throttling

### Prepared By
* **Muhammad Qadr**, **Miran Abdulqadr**, and **Diyar Nzar**
* **Course:** Information Systems Architecture (ISA)

**Laravel REST API Mini-Project** for ISA Seminar Presentation  
Erbil Polytechnic University – Information Systems Architecture

---

## 📋 What This Project Demonstrates

This project shows how to implement **dynamic, role-based API rate limiting** in Laravel using Sanctum authentication and custom throttle middleware:

| Tier          | Rate Limit       | How It's Identified          |
|---------------|------------------|------------------------------|
| 🔴 Guest      | 3 requests/min   | No token (by IP address)     |
| 🟡 Normal User | 6 requests/min   | Bearer token (role: `user`)  |
| 🟢 VIP User   | 20 requests/min  | Bearer token (role: `vip`)   |
| 🔒 Login      | 3 attempts/min   | By email + IP (brute-force)  |

---

## ⚡ Quick Setup

```bash
# 1. Navigate to the project folder
cd Mini_Prject

# 2. Install dependencies (if not already done)
composer install

# 3. Run migrations and seed the database
php artisan migrate:fresh --seed

# 4. Start the development server
php artisan serve
```

The API will be available at: **http://127.0.0.1:8000**

---

## 👤 Test Accounts

| Account      | Email               | Password      | Role   | Rate Limit  |
|-------------|---------------------|---------------|--------|-------------|
| Normal User | `user@example.com`  | `password123` | `user` | 6 req/min   |
| VIP User    | `vip@example.com`   | `password123` | `vip`  | 20 req/min  |

---

## 🔗 API Endpoints

| Method | Endpoint                    | Auth Required | Rate Limiter     |
|--------|-----------------------------|---------------|------------------|
| GET    | `/api/products-unprotected` | No            | None             |
| GET    | `/api/products-protected`   | No*           | `tiered-api`     |
| POST   | `/api/login`                | No            | `login-attempts` |
| GET    | `/api/me`                   | Yes (Sanctum) | `tiered-api`     |

*Works without auth (Guest tier), but dynamically adjusts limits when a Bearer token is provided.

---

## 🧪 Postman Demo Scenarios

### 🔧 Before Each Demo
```bash
php artisan cache:clear
```
> This resets all rate-limit counters so you start fresh.

### Import the Collection
1. Open Postman
2. Click **Import** → select `postman_collection.json` from the project root
3. The `base_url` variable is pre-set to `http://127.0.0.1:8000`

---

### Scenario A: Burst Comparison (Before vs After)

**Goal:** Show the difference between an unprotected and a protected endpoint.

1. Open **Collection Runner** (▶️ button on the collection)
2. Select **"Scenario A - Burst Comparison"** folder
3. Set **Iterations: 15**, Delay: **0 ms**
4. Click **Run**

**Expected Results:**
- `products-unprotected`: All 15 requests → ✅ **200 OK**
- `products-protected` (Guest): First 3 → ✅ **200 OK**, Requests 4-15 → ❌ **429 Too Many Requests**

---

### Scenario B: Dynamic Tiers (User vs VIP)

**Goal:** Show how the same endpoint gives different limits based on user role.

1. Send **"POST Login - Normal User"** → token auto-saved to `{{user_token}}`
2. Send **"POST Login - VIP User"** → token auto-saved to `{{vip_token}}`
3. Send **"GET Products - Normal User"** → Check `X-RateLimit-Limit: 6` header
4. Send **"GET Products - VIP User"** → Check `X-RateLimit-Limit: 20` header
5. Send **"GET /me"** → See authenticated user profile

**Key Headers to Observe:**
- `X-RateLimit-Limit` → Maximum requests allowed
- `X-RateLimit-Remaining` → Requests left in the window

---

### Scenario C: Brute-Force Protection

**Goal:** Show how login attempts are throttled to prevent brute-force attacks.

1. Send **"POST Login - Wrong Password"** rapidly 4+ times
2. First 3 attempts → **401** `{"status": "error", "message": "Invalid credentials."}`
3. 4th attempt → **429** `{"status": "blocked", "message": "Too many login attempts..."}`

---

## 📁 Key Files to Explain in Presentation

| File | Purpose |
|------|---------|
| `app/Providers/AppServiceProvider.php` | Defines `tiered-api` and `login-attempts` rate limiters |
| `routes/api.php` | Maps endpoints to controllers and applies throttle middleware |
| `app/Http/Controllers/AuthController.php` | Login + profile endpoints |
| `app/Http/Controllers/ProductController.php` | Product listing (protected vs unprotected) |
| `bootstrap/app.php` | Forces JSON responses for all API requests |
| `app/Models/User.php` | User model with `role` field and Sanctum `HasApiTokens` |

---

## 🛠 Useful Commands

```bash
# Reset rate limit counters (run before live demo)
php artisan cache:clear

# Reset database and re-seed
php artisan migrate:fresh --seed

# List all routes
php artisan route:list

# Start server
php artisan serve
```

---

## 📌 Architecture Summary

```
Client (Postman)
     │
     ▼
 ┌─────────────────────┐
 │   Laravel API        │
 │                      │
 │  ┌────────────────┐  │
 │  │ Throttle       │  │
 │  │ Middleware      │◄─┼── Rate limiter checks:
 │  │                │  │    Guest? User? VIP?
 │  └───────┬────────┘  │
 │          │           │
 │  ┌───────▼────────┐  │
 │  │ Controller     │  │
 │  │ (Business      │  │
 │  │  Logic)        │  │
 │  └───────┬────────┘  │
 │          │           │
 │  ┌───────▼────────┐  │
 │  │ Database       │  │
 │  │ (SQLite)       │  │
 │  └────────────────┘  │
 └─────────────────────┘
```

**Cache Store = File** → Rate limit counters are stored in `storage/framework/cache/` (no Redis/Memcached needed).
