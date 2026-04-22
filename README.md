<p align="center">
<a href="https://laravel.com" target="_blank">
<img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
</a>
</p>

<p align="center">
<a href="#"><img src="https://img.shields.io/badge/Laravel-13.x-red" alt="Laravel Version"></a>
<a href="#"><img src="https://img.shields.io/badge/PHP-8.5-blue" alt="PHP Version"></a>
<a href="#"><img src="https://img.shields.io/badge/API-REST-green" alt="API"></a>
</p>

<h1 align="center">🚀 E-Commerce REST API (Laravel)</h1>

<p align="center">
A scalable backend system built using Laravel with clean architecture.
</p>

---

# 📌 Project Overview

This project is a **production-ready backend E-commerce API system** that supports:

* Product management with multiple images
* Smart cart system with auto calculations
* Checkout & order lifecycle
* Secure payment integration using Razorpay
* Robust error handling & logging

---

# ⚙️ Tech Stack

* Laravel 13
* PHP 8+
* MySQL
* Laravel Sanctum (Authentication)
* Razorpay (Payment Gateway)
* Laravel Storage (File handling)
* Logging system (Laravel Log)

---

# 🧠 Architecture

This project follows a **clean layered architecture**:

* **Controller Layer** → Handles request/response
* **Service Layer** → Business logic
* **Repository Layer** → DB abstraction
* **Request Layer** → Validation & Authorization
* **Resource Layer** → API response formatting

👉 Benefits:

* Scalable codebase
* Clean separation of concerns
* Easy testing & maintenance

---

# 📦 Features

## 🔐 Authentication

* Register / Login / Logout
* Token-based authentication (Sanctum)
* Single device login (old tokens revoked)
* Secure API access

---

## 🛍️ Product Module

* Full CRUD operations
* Multiple image upload
* Add/remove images dynamically
* File upload abstraction via Trait
* Stored using Laravel Storage

---

## 🛒 Cart Module

* Add to cart
* Update quantity
* Remove item
* Auto recalculation (total items & amount)
* Secure user-based cart isolation
* Request validation separated
* Error logging implemented

---

## 📦 Order Module

* Checkout (Cart → Order)
* Order history (pagination enabled)
* Secure order access (user-based)
* Cancel order (only pending & unpaid)
* Prevent duplicate or invalid operations

---

## 💳 Payment Module

* Razorpay order creation
* Payment signature verification
* Payment success & failure handling
* Duplicate payment prevention
* Secure ownership validation

---

# 🔄 Payment Flow

1. User performs checkout → Order created
2. `/payment/pay` → Razorpay order generated
3. Frontend completes payment
4. `/payment/success` → Signature verification
5. Payment marked as **success**
6. Order marked as **paid & processed**

---

# 🗄️ Database Tables

* users
* products
* product_images
* carts
* cart_items
* orders
* order_items
* payments

---

# 🛠️ Installation

```bash
git clone <repo-url>
cd project

composer install
cp .env.example .env
php artisan key:generate
```

### Configure DB

```env
DB_DATABASE=your_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

```bash
php artisan migrate
php artisan storage:link
php artisan serve
```

---

# 📬 API Endpoints (Updated)

---

## 🔐 Auth

| Method | Endpoint              | Description |
|--------|----------------------|-------------|
| POST   | /api/auth/register   | Register user |
| POST   | /api/auth/login      | Login user |
| POST   | /api/auth/logout     | Logout user (Auth required) |
| GET    | /api/auth/me         | Get authenticated user |

---

## 🛍️ Products

| Method | Endpoint              | Description |
|--------|----------------------|-------------|
| GET    | /api/products        | List all products |
| POST   | /api/products        | Create product |
| GET    | /api/products/{id}   | Get single product |
| PUT    | /api/products/{id}   | Update product |
| DELETE | /api/products/{id}   | Delete product |

---

## 🛒 Cart

| Method | Endpoint            | Description |
|--------|--------------------|-------------|
| GET    | /api/cart          | Get user cart |
| POST   | /api/cart/add      | Add product to cart |
| POST   | /api/cart/update   | Update cart item quantity |
| DELETE | /api/cart/remove   | Remove item from cart |

---

## 📦 Checkout

| Method | Endpoint        | Description |
|--------|----------------|-------------|
| POST   | /api/checkout  | Convert cart to order |

---

## 📦 Orders

| Method | Endpoint                     | Description |
|--------|-----------------------------|-------------|
| GET    | /api/orders                 | Get user orders (paginated) |
| GET    | /api/orders/{id}            | Get single order |
| POST   | /api/orders/{id}/cancel     | Cancel order |

---

## 💳 Payment

| Method | Endpoint                  | Description |
|--------|--------------------------|-------------|
| POST   | /api/payment/pay         | Initiate payment (Razorpay order) |
| POST   | /api/payment/success     | Verify payment & mark success |
| POST   | /api/payment/failed      | Mark payment as failed |

---

## 🧑‍💼 Admin (CMS)

| Method | Endpoint                              | Description |
|--------|----------------------------------------|-------------|
| GET    | /api/admin/carts                      | Get all carts (paginated) |
| GET    | /api/admin/carts/{id}                 | Get single cart |
| GET    | /api/admin/users/{user}/cart          | Get specific user cart |

---

# 📌 Notes

- 🔐 All protected routes require **Bearer Token (Sanctum)**
- 📦 Orders are created only after successful **checkout**
- 💳 Payment uses **Razorpay integration with signature verification**
- 🚫 Duplicate payments and duplicate checkout are prevented
- 🛒 Cart is **user-specific and isolated**
- 📄 Pagination implemented for **orders & admin carts**
- ⚠️ Proper error handling & logging implemented for debugging
- 🧠 Business logic handled via **Service Layer**
- ✅ Request validation handled via **FormRequest classes**

---

# 👨‍💻 Author

**Abhishek Jha**  
Backend Developer (Laravel)  

🔹 Skilled in building scalable REST APIs  
🔹 Focused on clean architecture & performance  
🔹 Experience with payment integration (Razorpay)  

📧 Email: codingthunder1997@gmail.com  
🔗 GitHub: https://github.com/govind663  
🔗 LinkedIn: https://www.linkedin.com/in/abhishek-laravel-developer

---
