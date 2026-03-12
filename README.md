# Laravel Auth API

A reusable **Laravel authentication API** designed as a solid foundation for building secure backend services quickly.  
It provides production-ready authentication flows, role-based access control, and user lifecycle management.

---

## Features

- JWT authentication
- Role-Based Access Control (RBAC)
- User management (create, update, activate, deactivate)
- Account activation via email
- Password management
- Multi-factor authentication ready
- Cloudinary avatar uploads
- Search, filtering, and pagination
- API resources and standardized responses
- API documentation with Scribe
- Modular service + repository architecture

---

## Stack

- **Laravel**
- **JWT Authentication (tymon/jwt-auth)**
- **Spatie Laravel Permission**
- **Scribe API Documentation**
- **Cloudinary File Storage**
- **MySQL**

---

## Authentication Flow

The API includes a full authentication lifecycle:

1. Admin creates user
2. User receives activation email
3. User activates account and sets password
4. User logs in via JWT
5. Protected endpoints require bearer token

Optional flows supported:

- Password change
- Account locking
- MFA-ready authentication

---

## Installation

```bash
git clone https://github.com/your-username/laravel-auth-api.git
cd laravel-auth-api

composer install
cp .env.example .env
php artisan key:generate