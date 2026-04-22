<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="#"><img src="https://img.shields.io/badge/Laravel-10-red" alt="Laravel Version"></a>
<a href="#"><img src="https://img.shields.io/badge/PHP-8.x-blue" alt="PHP Version"></a>
<a href="#"><img src="https://img.shields.io/badge/API-REST-green" alt="API"></a>
</p>

---

## 🚀 About Project

This is a **Laravel-based E-commerce REST API Project** developed as part of a machine test.

The system provides complete backend functionality for:

- User Authentication (Register/Login using Sanctum)
- Product Management (CRUD with multiple images)
- Cart System (Add, Update, Remove items)
- Order Management (Checkout & Order History)
- Payment Integration (Razorpay)

---

## ⚙️ Tech Stack

- Laravel 10
- PHP 8+
- MySQL
- Laravel Sanctum (Authentication)
- Razorpay (Payment Gateway)

---

## 📦 Features

### 🔐 Authentication
- Register
- Login
- Logout
- Get Authenticated User

### 🛍️ Product Module
- Create Product with Multiple Images
- Update Product (Add/Delete Images)
- Delete Product
- List Products

### 🛒 Cart Module
- Add to Cart
- Update Quantity
- Remove Item
- Auto Calculation (Total Amount & Items)

### 📦 Order Module
- Checkout (Cart → Order)
- Order Listing
- Order Details
- Cancel Order

### 💳 Payment Module
- Initiate Payment
- Razorpay Integration
- Payment Success/Failure Handling

---

## 🛠️ Installation & Setup

```bash
git clone <your-repo-url>
cd project-folder

composer install

cp .env.example .env

php artisan key:generate

# Configure DB in .env

php artisan migrate

php artisan storage:link

php artisan serve