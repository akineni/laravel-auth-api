# Laravel Auth API

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

Example response:

```json
{
  "status": "error",
  "message": "This username is already taken.",
  "errors": {
    "username": [
      "This username is already taken."
    ]
  },
  "meta": {
    "username_suggestions": [
      "eniola_akinlonu92",
      "eniola.wave",
      "realeniola",
      "pixel_eniola",
      "eniola_core"
    ]
  }
}
```

---

### How It Works

The system uses:

* **Candidate pooling** (not first-match selection)
* **Multiple strategy families**

  * Classic (name-based)
  * Compact (short forms)
  * Branded (prefix/suffix styles)
  * Word-based (creative combinations)
* **Randomized selection from available usernames**
* **Fallback generation with high entropy**

This ensures:

* No repetitive patterns
* High variation even for identical names
* Practically inexhaustible username space

---

### Reusability

The username system is:

* Implemented via a **dedicated service (`UsernameService`)**
* Attached to models using a reusable **`HasUsername` trait**
* Easily extendable for other entities beyond users

---

## Verification System (Auth Challenges)

This project implements a **challenge-based verification system** used for:

* Email verification
* Password reset
* Login OTP
* Two-factor authentication

All verification flows are handled through a unified **`auth_challenges`** table.

### Supported Verification Methods

| Method      | Description                                           |
| ----------- | ----------------------------------------------------- |
| `otp_email` | Email-based OTP                                       |
| `otp_sms`   | SMS OTP (ready for integration)                       |
| `totp`      | Authenticator apps (Google Authenticator, Authy, etc) |

Future methods can be added easily:

* Passkeys (WebAuthn)
* Push authentication
* Hardware keys

---

## Two-Factor Authentication

Users can enable:

### Default OTP (Email)

OTP sent to the user email.

### Authenticator Apps (TOTP)

Supports apps like:

* Google Authenticator
* Microsoft Authenticator
* Authy
* 1Password

Features:

* QR code enrollment
* Recovery codes
* Challenge-based verification
* Secure TOTP validation

---

# OTP & Verification Protection

To ensure security and prevent abuse, the API implements **three layers of protection** for verification flows.

---

## 1. OTP Resend Cooldown

Users must wait a configurable amount of time before requesting another OTP.

Default configuration:

```
OTP_RESEND_COOLDOWN_SECONDS=120
```

---

## 2. API Rate Limiting

Example route protection:

```php
Route::post('resend-otp', 'resendOtp')
    ->middleware('throttle:3,15');
```

---

## 3. Verification Attempt Limit

Default configuration:

```
OTP_MAX_VERIFICATION_ATTEMPTS=5
```

---

## Combined Protection

| Protection      | Purpose                         |
| --------------- | ------------------------------- |
| Resend cooldown | Prevent rapid OTP resend abuse  |
| API throttle    | Prevent endpoint flooding       |
| Attempt limit   | Prevent OTP brute-force attacks |

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
* Account activation flow

---

## File Uploads

User avatars are stored using **Cloudinary**.

---

# API Architecture

```
app/
├── Data/
├── Enums/
├── Exceptions/
├── Helpers/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
├── Models/
│   └── Concerns/
├── Notifications/
├── Providers/
├── Repositories/
│   ├── Contracts/
│   └── Eloquent/
├── Services/
│   ├── Auth/
│   ├── OTP/
│   └── Username/
├── Traits/
```

---

# Tech Stack

| Technology         | Purpose                   |
| ------------------ | ------------------------- |
| Laravel            | Application framework     |
| MySQL              | Database                  |
| JWT Auth           | Authentication            |
| Spatie Permission  | RBAC                      |
| Laravel Socialite  | SSO authentication        |
| PragmaRX Google2FA | Authenticator app support |
| Scribe             | API documentation         |
| Cloudinary         | Media storage             |

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

# Project Goals

* A **production-ready auth foundation**
* A **clean architecture reference**
* A **reusable Laravel authentication module**

---

# License

MIT License.
