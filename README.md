# Laravel Auth API

A **production-ready Laravel authentication API** designed as a reusable foundation for building secure backend services quickly.

This project provides a robust authentication system with support for:

- JWT authentication
- Role-based access control
- Email and Social SSO login
- One-Time Password (OTP) verification
- Authenticator app (TOTP) two-factor authentication
- Extensible authentication challenge system
- User lifecycle management
- API documentation

The architecture is built with **clean separation of concerns**, using service layers, repositories, and modular drivers to keep the system maintainable and extensible.

---

# Features

## Authentication

- JWT authentication using **tymon/jwt-auth**
- Email/password login
- Google SSO
- Facebook SSO
- Token refresh
- Logout
- Account lockout protection
- Login rate limiting

---

## Verification System (Auth Challenges)

This project implements a **challenge-based verification system** used for:

- Email verification
- Password reset
- Login OTP
- Two-factor authentication

All verification flows are handled through a unified **`auth_challenges`** table.

### Supported Verification Methods

| Method | Description |
|------|------|
| `otp_email` | Email-based OTP |
| `otp_sms` | SMS OTP (ready for integration) |
| `totp` | Authenticator apps (Google Authenticator, Authy, etc) |

Future methods can be added easily:

- Passkeys (WebAuthn)
- Push authentication
- Hardware keys

---

## Two-Factor Authentication

Users can enable:

### Default OTP (Email)

OTP sent to the user email.

### Authenticator Apps (TOTP)

Supports apps like:

- Google Authenticator
- Microsoft Authenticator
- Authy
- 1Password

Features:

- QR code enrollment
- Recovery codes
- Challenge-based verification
- Secure TOTP validation

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

Example response:

```json
{
  "status": "error",
  "message": "Please wait 98 seconds before requesting another code.",
  "errors": {
    "otp": [
      "Please wait 98 seconds before requesting another code."
    ]
  }
}
```

This prevents users from repeatedly clicking the **Resend OTP** button.

---

## 2. API Rate Limiting

Laravel's throttle middleware protects endpoints from abuse.

Example route protection:

```php
Route::post('resend-otp', 'resendOtp')
    ->middleware('throttle:3,15');
```

Meaning:

- Maximum **3 requests**
- Within **15 minutes**

If exceeded, the API returns:

```json
{
  "status": "error",
  "message": "Too many attempts. Please try again later."
}
```

---

## 3. Verification Attempt Limit

Each verification challenge tracks the number of failed verification attempts.

Default configuration:

```
OTP_MAX_VERIFICATION_ATTEMPTS=5
```

After exceeding the maximum attempts:

- the challenge becomes invalid
- the user must request a new verification code

Example response:

```json
{
  "status": "error",
  "message": "Too many invalid attempts. Please request a new code."
}
```

This prevents brute-force attacks on OTP or TOTP verification.

---

## Combined Protection

| Protection | Purpose |
|------|------|
| Resend cooldown | Prevent rapid OTP resend abuse |
| API throttle | Prevent endpoint flooding |
| Attempt limit | Prevent OTP brute-force attacks |

Together they ensure verification endpoints remain secure and stable.

---

## Role-Based Access Control

Powered by **Spatie Laravel Permission**.

Features:

- Roles
- Permissions
- Role assignment
- Role syncing
- Permission checks

---

## User Management

Endpoints for:

- Create users
- Update user profile
- Activate / deactivate accounts
- Soft delete users
- Password changes
- Account activation flow

---

## File Uploads

User avatars are stored using **Cloudinary**.

Features:

- Cloud image storage
- Avatar update
- Automatic URL generation

---

# API Architecture

The API is structured using a clean architecture pattern.

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
├── Notifications/
├── Providers/
├── Repositories/
│   ├── Contracts/
│   └── Eloquent/
├── Services/
│   ├── Auth/
│   │   ├── TwoFactor/
│   │   │   ├── Drivers/
│   │   │   └── TwoFactorDriverManager.php
│   └── OTP/
├── Traits/
```

### Core Architectural Patterns

- Service Layer
- Repository Pattern
- Driver-based authentication mechanisms
- DTOs for response structures
- Enum-driven domain logic

---

# Auth Challenge System

The API uses a **challenge-based verification model**.

This prevents:

- client manipulation of verification flows
- bypassing authentication steps
- context spoofing

All verification flows are driven by **server-issued challenge tokens**.

---

## Auth Challenge Table

```
auth_challenges
```

| Column | Description |
|------|------|
| `challenge_token` | Secure token returned to client |
| `user_id` | Challenge owner |
| `method` | Verification method |
| `context` | Challenge purpose |
| `code` | Stored OTP hash (nullable) |
| `attempts` | Number of failed verification attempts |
| `expires_at` | Challenge expiration |
| `verified_at` | When challenge is completed |

---

## Example Challenge Flow

### Login with Email OTP

1. User logs in with email + password
2. Server generates auth challenge
3. OTP sent to email
4. Client receives:

```json
{
  "message": "OTP sent to your email",
  "data": {
    "otp_required": true,
    "destination": "a***@mail.com",
    "challenge_token": "abc123",
    "expires_in": 300
  }
}
```

Client verifies:

```
POST /auth/verify-otp
```

```json
{
  "challenge_token": "abc123",
  "otp": "123456"
}
```

---

### Login with Authenticator App

1. User logs in
2. Server generates challenge
3. Client receives:

```json
{
  "message": "Enter the code from your authenticator app.",
  "data": {
    "otp_required": true,
    "challenge_token": "abc123",
    "expires_in": 300
  }
}
```

Verification:

```
POST /auth/verify-otp
```

```json
{
  "challenge_token": "abc123",
  "otp": "123456"
}
```

---

# Tech Stack

| Technology | Purpose |
|------|------|
| Laravel | Application framework |
| MySQL | Database |
| JWT Auth | Authentication |
| Spatie Permission | RBAC |
| Laravel Socialite | SSO authentication |
| PragmaRX Google2FA | Authenticator app support |
| Scribe | API documentation |
| Cloudinary | Media storage |

---

# Installation

Clone the repository.

```bash
git clone https://github.com/akineni/laravel-auth-api.git
cd laravel-auth-api
```

Install dependencies.

```bash
composer install
```

Copy environment configuration.

```bash
cp .env.example .env
```

Generate application key.

```bash
php artisan key:generate
```

Configure database.

```bash
php artisan migrate --seed
```

Install JWT secret.

```bash
php artisan jwt:secret
```

Generate API documentation.

```bash
php artisan scribe:generate
```

Start the server.

```bash
php artisan serve
```

---

# API Documentation

API documentation is generated using **Scribe**.

Generate docs:

```bash
php artisan scribe:generate
```

View documentation:

```
/docs
```

---

# Security Features

- OTP hashing
- Challenge expiration
- Verification attempt limits
- Account lockout protection
- Challenge-based verification
- OTP resend cooldown protection
- API rate limiting
- Single-use verification tokens
- JWT refresh mechanism
- Password reset protection

---

# Extending Authentication

Adding new authentication methods is simple.

Example additions:

- Passkeys
- Push approval
- Hardware keys

Steps:

1. Add new `OtpMethodEnum`
2. Implement new driver in

```
Services/Auth/TwoFactor/Drivers
```

3. Register driver in `TwoFactorDriverManager`

No database changes required.

---

# Project Goals

This project aims to provide:

- A **production-ready auth foundation**
- A **clean architecture reference**
- A **reusable Laravel authentication module**

---

# License

MIT License.