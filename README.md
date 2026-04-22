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

# 📬 API Endpoints

## Auth

POST /api/auth/register
POST /api/auth/login
POST /api/auth/logout
GET /api/auth/me

## Products

GET /api/products
POST /api/products
GET /api/products/{id}
PUT /api/products/{id}
DELETE /api/products/{id}

## Cart

GET /api/cart
POST /api/cart/add
POST /api/cart/update
DELETE /api/cart/remove

## Checkout

POST /api/checkout

## Orders

GET /api/orders
GET /api/orders/{id}
POST /api/orders/{id}/cancel

## Payment

POST /api/payment/pay
POST /api/payment/success
POST /api/payment/failed

---

# 📌 Notes

* Soft Deletes implemented
* Audit Trail (created_by, updated_by, deleted_by)
* Images stored via Laravel Storage
* API built with scalability in mind

---

# 👨‍💻 Author

**Abhishek Jha**
Laravel Developer

---
