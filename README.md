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

---

# 🚀 E-Commerce REST API (Laravel)

This is a **Laravel-based E-commerce REST API Project** developed as part of a machine test.

The project is fully backend-driven and follows a **clean, scalable architecture using Service + Repository pattern**.

---

# ⚙️ Tech Stack

* **Laravel 13.6.0**
* **PHP 8.5.4**
* MySQL
* Laravel Sanctum (API Authentication)
* Razorpay (Payment Gateway)
* RESTful API Architecture

---

# 📦 Features

## 🔐 Authentication

* User Register
* User Login
* Logout (Token Revoke)
* Get Authenticated User

## 🛍️ Product Module

* Create Product (Multiple Images Support)
* Update Product (Add/Delete Images)
* Delete Product (Soft Delete)
* Product Listing

## 🛒 Cart Module

* Add Product to Cart
* Update Quantity
* Remove Item
* Auto Calculation (Total Amount & Items)

## 📦 Order Module

* Checkout (Cart → Order)
* Order History
* Order Details
* Cancel Order (Only Pending)

## 💳 Payment Module

* Payment Initiation
* Razorpay Integration
* Payment Success Handling
* Payment Failure Handling

---

# 🗄️ Database Structure

### Main Tables:

* users
* products
* product_images
* carts
* cart_items
* orders
* order_items
* payments
* personal_access_tokens
* sessions
* password_reset_tokens

---

# 🛠️ Installation & Setup

```bash
git clone <your-repo-url>
cd project-folder

composer install

cp .env.example .env

php artisan key:generate
```

### 📌 Configure Database (.env)

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

## 🔐 Auth

* POST /api/auth/register
* POST /api/auth/login
* POST /api/auth/logout
* GET /api/auth/me

## 🛍️ Products

* GET /api/products
* POST /api/products
* GET /api/products/{id}
* PUT /api/products/{id}
* DELETE /api/products/{id}

## 🛒 Cart

* GET /api/cart
* POST /api/cart/add
* POST /api/cart/update
* DELETE /api/cart/remove

## 📦 Checkout

* POST /api/checkout

## 📦 Orders

* GET /api/orders
* GET /api/orders/{id}
* POST /api/orders/{id}/cancel

## 💳 Payment

* POST /api/payment/pay
* POST /api/payment/success
* POST /api/payment/failed

---

# 📁 Additional Files

* ✔️ Postman Collection (Included)
* ✔️ Database SQL File (Included)

---

# 📌 Notes

* CMS functionality is implemented via API endpoints
* Images are stored using Laravel storage system
* Soft Deletes are used for data safety
* Audit Trail (created_by, updated_by, deleted_by) implemented
* Clean architecture: Service + Repository Pattern

---

# 👨‍💻 Author

**Abhishek Jha**
Laravel Developer
