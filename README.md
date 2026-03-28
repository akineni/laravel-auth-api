# Laravel Auth API

![Laravel](https://img.shields.io/badge/Laravel-10%2B-red)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue)
![License](https://img.shields.io/badge/license-MIT-green)
![Status](https://img.shields.io/badge/status-production--ready-success)

A **production-ready Laravel authentication API** designed as a reusable foundation for building secure backend services quickly.

This project provides a robust authentication system with support for:

* JWT authentication
* Role-based access control
* Email and Social SSO login
* One-Time Password (OTP) verification
* Authenticator app (TOTP) two-factor authentication
* Intelligent username generation & suggestions
* Extensible authentication challenge system
* User lifecycle management
* Event-driven notification system (email + database)
* API documentation

The architecture is built with **clean separation of concerns**, using service layers, repositories, and modular drivers to keep the system maintainable and extensible.

---

# Features

## Authentication

* JWT authentication using **tymon/jwt-auth**
* Email/password login
* Username or email login
* Google SSO
* Facebook SSO
* Token refresh
* Logout
* Account lockout protection
* Login rate limiting

---

## Username System

The API includes a **smart, production-grade username system** designed to be:

* Flexible
* Non-repetitive
* Highly scalable
* User-friendly

### Key Capabilities

* **Optional usernames** (auto-generated if not provided)
* **Username or email login support**
* **Advanced uniqueness handling**
* **Human-friendly suggestions when username is taken**
* **Reusable across multiple models via traits**

---

### Auto Username Generation

When a username is not provided during registration, the system automatically generates one using a **multi-strategy engine**.

Instead of predictable formats, the system:

* Combines multiple patterns (first name, last name, initials)
* Uses separators (`.`, `_`, none)
* Applies intelligent variations (reversed names, compact forms)
* Introduces controlled randomness
* Falls back to advanced generation strategies if needed

Example outputs:

```text
eniolaakinlonu
eniola.akinlonu
eniola_akinlonu
akinlonueniola
eakinlonu
eniola26
realeniola
eniola.wave
pixel_eniola
```

---

### Username Suggestions

When a username is already taken, the API returns **alternative suggestions**.

---

## Verification System (Auth Challenges)

Unified system for:

* Email verification
* Password reset
* Login OTP
* Two-factor authentication

---

## Two-Factor Authentication

Supports:

* Email OTP
* Authenticator apps (Google Authenticator, Authy, etc.)

---

# OTP & Verification Protection

* Resend cooldown
* Rate limiting
* Attempt limits

---

## Role-Based Access Control

Powered by **Spatie Laravel Permission**.

---

## User Management

* Create users
* Update user profile
* Activate / deactivate accounts
* Soft delete users
* Password changes

---

## File Uploads

User avatars are stored using **Cloudinary**.

---

# Notifications System

Event-driven notification system supporting:

* Email + database notifications
* Security alerts
* Role modification alerts
* Admin awareness

---

## Admin Endpoint

```
GET /users/admins
```

---

# API Architecture

```
Controllers → Services → Repositories → Models
                     ↓
                 Events → Listeners → Notifications
```

---

# Tech Stack

| Technology         | Purpose |
|------------------|--------|
| Laravel          | Framework |
| MySQL            | Database |
| JWT Auth         | Authentication |
| Spatie Permission| RBAC |
| Socialite        | SSO |
| Google2FA        | 2FA |
| Scribe           | API Docs |
| Cloudinary       | Media |

---

# Installation

```bash
git clone https://github.com/akineni/laravel-auth-api.git
cd laravel-auth-api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan jwt:secret
php artisan scribe:generate
php artisan serve
```

---

# License

MIT License.
