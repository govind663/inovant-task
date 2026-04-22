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

This project is a **backend E-commerce API system** that allows:

* Product management with multiple images
* Cart management with real-time calculations
* Checkout & Order processing
* Payment integration using Razorpay

The system is designed using **clean architecture principles** to ensure scalability and maintainability.

---

# ⚙️ Tech Stack

* Laravel 13
* PHP 8+
* MySQL
* Laravel Sanctum (Authentication)
* Razorpay (Payment Gateway)

---

# 🧠 Architecture

This project follows a **clean layered architecture**:

* **Controller Layer** → Handles request/response
* **Service Layer** → Business logic
* **Repository Layer** → Database operations
* **Resource Layer** → API response formatting

👉 This ensures:

* Clean code
* Scalability
* Easy maintenance

---

# 📦 Features

## 🔐 Authentication

* Register / Login / Logout
* Token-based authentication (Sanctum)

## 🛍️ Product Module

* CRUD operations
* Multiple image upload
* Add/Delete images during update

## 🛒 Cart Module

* Add to cart
* Update quantity
* Remove item
* Auto calculation (total items & amount)

## 📦 Order Module

* Checkout (Cart → Order)
* Order history
* Cancel order

## 💳 Payment Module

* Razorpay order creation
* Payment verification
* Success & failure handling

---

# 🔄 Payment Flow

1. User creates order via checkout
2. `/payment/pay` → Razorpay order created
3. Frontend completes payment
4. `/payment/success` → Signature verification
5. Order marked as **paid & processed**

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
