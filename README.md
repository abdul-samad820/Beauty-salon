<div align="center">

<img src="https://img.shields.io/badge/LUMIÈRE-Beauty%20SaaS%20Platform-c9a96e?style=for-the-badge&labelColor=0d0a07&color=c9a96e" height="45"/>

<h1>LUMIÈRE — Multi-Tenant Beauty Parlour SaaS</h1>

<p><strong>Production-grade SaaS platform built for beauty parlours &amp; salons across India</strong></p>
<p>Multi-Tenant Architecture · Role-Based Access Control · Razorpay Payments · Automated Inventory · Tiered Staff Commissions</p>

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://mysql.com)
[![Razorpay](https://img.shields.io/badge/Razorpay-Integrated-02042B?style=flat-square&logo=razorpay&logoColor=white)](https://razorpay.com)
[![Tests](https://img.shields.io/badge/Tests-198%20Tests%20%7C%2028%20Suites-brightgreen?style=flat-square)](#-testing)
[![License](https://img.shields.io/badge/License-MIT-gold?style=flat-square)](LICENSE)

<br/>

</div>

---

## 📖 Table of Contents

- [Overview](#-overview)
- [Platform Architecture](#-platform-architecture)
- [Complete Feature List](#-complete-feature-list)
- [Tech Stack](#-tech-stack)
- [Database Schema](#-database-schema)
- [Email System](#-email-system)
- [Roles & Guards](#-roles--guards)
- [Quick Start](#-quick-start)
- [Environment Setup](#-environment-setup)
- [Local Subdomain Setup](#-local-subdomain-setup)
- [Database & Seeding](#-database--seeding)
- [Subscription Plans](#-subscription-plans)
- [Queue Workers](#-queue-workers)
- [Scheduled Tasks](#-scheduled-tasks)
- [Testing](#-testing)
- [Security Implementation](#-security-implementation)
- [REST API](#-rest-api)
- [API & Health Check](#-api--health-check)
- [Project Structure](#-project-structure)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🌟 Overview

**LUMIÈRE** is a fully multi-tenant SaaS platform purpose-built for beauty parlours and salons. Every salon runs on its own isolated subdomain (e.g., `naturelle.lumiere.app`) with completely separate data, staff, customers, appointments, inventory, and settings — zero cross-tenant leakage by design.

A **Super Admin** governs the entire platform — provisioning tenants, managing subscription plans, and monitoring platform-wide revenue and analytics. Each **Salon Owner** independently operates their full business: appointments, inventory, staff commissions, customer reviews, and subscription billing — all within their isolated workspace.

**Key design decisions:**
- `BelongsToTenant` trait auto-scopes every Eloquent query to `tenant_id` — no manual filtering needed
- `lockForUpdate()` pessimistic locking prevents double bookings and duplicate inventory deductions
- Separate `web` and `customer` auth guards with isolated session cookies prevent session bleed
- Observer-driven automation for commissions and inventory keeps controllers lean

---

## 🏗 Platform Architecture

```
lumiere.app                          ← Super Admin Platform Console
  ├── /superadmin/dashboard          ← Platform KPIs & live metrics
  ├── /superadmin/tenants            ← Tenant lifecycle management
  ├── /superadmin/analytics          ← Cross-tenant revenue & booking analytics
  ├── /superadmin/revenue            ← Platform MRR & revenue tracking
  ├── /superadmin/subscriptions      ← Plan & subscription management
  ├── /superadmin/queue              ← Background job queue monitor
  └── /superadmin/settings           ← Platform settings & cache management

naturelle.lumiere.app                ← Tenant Workspace (Salon 1)
  ├── /owner/dashboard               ← Salon management panel
  ├── /landing                       ← Public-facing salon landing page
  ├── /login  /register              ← Customer auth (subdomain-scoped)
  └── /  (customer portal)           ← Booking, appointments, reviews

bliss.lumiere.app                    ← Tenant Workspace (Salon 2 — fully isolated)
```

### Multi-Tenancy Request Flow

```
Incoming Request (subdomain.lumiere.app)
          ↓
  TenantMiddleware / CustomerTenantMiddleware
          ↓
  Resolve subdomain → Fetch Tenant → Bind to app container
          ↓
  BelongsToTenant trait → Auto-scope all Eloquent queries to tenant_id
          ↓
  Zero cross-tenant data leakage guaranteed
```

---

## ✨ Complete Feature List

### 🔱 Super Admin Panel (`/superadmin`)

| Feature | Details |
|---|---|
| **Dashboard** | Live KPIs — total tenants, revenue, active subscriptions, trials, suspended; AJAX-polled real-time stats |
| **Tenant Management** | Provision new tenants via multi-step form, view/edit/suspend/activate, per-tenant analytics |
| **Tenant Detail View** | Staff list, services, recent appointments, revenue chart, full activity log tab |
| **Subscription Plans** | Create/edit Free, Basic, Premium plans with feature constraints (staff seats, service count, bookings/month, module toggles) |
| **Subscriptions** | View all active contracts, cancel or renew subscriptions across tenants |
| **Platform Analytics** | Cross-tenant revenue trends, booking volumes, tenant growth charts |
| **Revenue Tracking** | Gross platform MRR, per-plan revenue breakdown |
| **Appointment Monitor** | Platform-wide appointment tracking across all tenants |
| **Queue Monitor** | View pending/failed jobs, retry or delete failed jobs, flush all failed — no CLI required |
| **Platform Settings** | Global platform variables, cache management |
| **Notifications** | Audit-log-based notification bell with unread badge count |

### 💼 Owner Panel (`/owner`)

| Feature | Details |
|---|---|
| **Dashboard** | Today's bookings, revenue KPIs, recent activity feed, live booking count via AJAX polling |
| **Today's Bookings** | Chronological appointment view with inline status update buttons |
| **All Appointments** | Paginated list with filters by staff/status/date/customer; CSV export |
| **New Booking** | Create appointment with real-time slot conflict detection & pessimistic DB locking |
| **Services** | Add/edit/delete services with category, price, duration; plan-enforced service count limits |
| **Staff Management** | Add/edit/delete staff, set availability, assign flat commission percentage |
| **Tiered Commissions** | Revenue-bracket-based commission slabs (e.g. ₹0–10K = 20%, ₹10K+ = 30%) |
| **Commission Ledger** | Per-staff commission summary; mark individual commissions as paid/settled |
| **Customers** | Customer directory with full appointment history and lifetime spend tracking |
| **Inventory** | Add products, stock-in, stock-out, low-stock alerts, image upload support |
| **Inventory Valuation** | Stock value report (quantity × cost price per product) |
| **Service-Product Mapping** | Map products to services for automatic inventory deduction on appointment completion |
| **Gallery** | Upload, reorder, and delete salon photos (displayed on public landing page) |
| **Reviews** | View customer reviews; approve or reject before publishing |
| **Analytics** | Revenue trends, booking heatmaps, staff performance comparison charts |
| **Settings** | Salon info, working hours, social links, password management |
| **Subscription & Billing** | View current plan limits, upgrade via Razorpay, billing history |

### 👤 Customer Portal (`/{subdomain}`)

| Feature | Details |
|---|---|
| **Public Landing Page** | Hero section, services, gallery, team, about — accessible without login |
| **Auth System** | Register, login, forgot password, reset password — all fully subdomain-scoped |
| **Service Booking** | Browse services, pick staff and time slot, book appointment |
| **Real-Time Slot Check** | AJAX slot availability based on staff schedule and existing bookings |
| **Online Payment** | Razorpay checkout embedded in the booking flow |
| **Booking Confirmation** | Confirmation page with complete booking details |
| **Appointment History** | View all past and upcoming appointments; cancel pending bookings |
| **Invoice Download** | PDF invoice per completed appointment (generated via DomPDF) |
| **Reviews** | Submit star rating and review after a completed appointment |
| **Products** | Browse salon retail products |
| **Gallery** | View salon photo gallery |
| **Profile** | Update personal info and password |

### 👷 Staff Panel (`/staff`)

| Feature | Details |
|---|---|
| **Dashboard** | Today's assigned appointments, quick stats overview |
| **Appointments** | Full list of all assigned appointments |
| **Commission Earnings** | View own commission history and payment status |
| **Profile** | Update profile info and change password |

---

## 🛠 Tech Stack

| Layer | Technology | Version | Purpose |
|---|---|---|---|
| **Framework** | Laravel | 12.x | Core application framework |
| **Language** | PHP | 8.2+ | Server-side logic |
| **Database** | MySQL | 8.0 | Primary data store |
| **Auth** | Laravel Multi-Guard | — | Separate `web` + `customer` auth flows |
| **Authorization** | Spatie Laravel Permission | 6.25 | RBAC across all 4 roles |
| **Payments** | Razorpay SDK + Webhooks | 2.9 | Online booking payments & subscriptions |
| **PDF** | barryvdh/laravel-dompdf | 3.x | Invoice PDF generation |
| **Backups** | Spatie Laravel Backup | 9.3 | Automated database backups |
| **Frontend** | Blade + Bootstrap + Chart.js | — | Server-rendered UI with charts |
| **Sessions** | Database-backed, encrypted | — | Secure, isolated session storage |
| **Queue** | Laravel Database Queue | — | Async jobs for alerts & reminders |
| **Email** | SMTP (configurable) | — | 4 transactional email templates |
| **Testing** | PHPUnit 11 | — | 28 feature test suites, SQLite in-memory |

---

## 🗄 Database Schema

**43 migrations** — schema built progressively from May to June 2026:

| Model | Key Fields |
|---|---|
| `Tenant` | name, subdomain, slug, email, phone, address, description, plan, status, trial_ends_at, social links |
| `User` | tenant_id, name, email, phone, password, is_active, profile_photo (soft deletes) |
| `Staff` | tenant_id, user_id, commission_percent, is_available (soft deletes) |
| `CommissionTier` | tenant_id, staff_id, min_revenue, max_revenue, commission_percent (null max_revenue = top tier / no upper limit) |
| `Service` | tenant_id, name, category, price, duration_minutes, is_active (soft deletes) |
| `Appointment` | tenant_id, customer_id, staff_id, service_id, amount, gst_rate (default 18%), gst_amount, status [`pending` / `confirmed` / `checked_in` / `completed` / `cancelled` / `no_show`], appointment_date, start_time, end_time, notes, reminder_sent, payment_method, payment_status, razorpay fields |
| `Commission` | tenant_id, staff_id, appointment_id, service_price, commission_percent, commission_amount, status |
| `Product` | tenant_id, name, category, price, cost_price, quantity, low_stock_threshold, image |
| `InventoryTransaction` | tenant_id, product_id, type (in/out/appointment_deduct), quantity, reference_id, reason |
| `ServiceProduct` | tenant_id, service_id, product_id, quantity_used, unit |
| `Plan` | name, slug, price_monthly, price_yearly, max_staff, max_services, max_appointments_per_month, feature flags |
| `Subscription` | tenant_id, plan_id, status, billing_cycle, amount, starts_at, expires_at |
| `SubscriptionPayment` | tenant_id, subscription_id, razorpay_order_id, razorpay_payment_id, amount, status |
| `Review` | tenant_id, appointment_id, customer_id, rating, comment, is_approved (unique per appointment) |
| `GalleryImage` | tenant_id, path, sort_order |
| `AuditLog` | action, auditable_type, auditable_id, payload, tenant_id, is_read |
| `PlatformSetting` | key, value |

> Soft deletes are enabled on `appointments`, `users`, `staff`, `services`, `commissions`, `reviews`, `gallery_images`, and `inventory_transactions`. Composite indexes are added on high-traffic query columns for performance.

---

## 📧 Email System

4 transactional emails dispatched via Laravel Queue:

| Mail Class | Trigger |
|---|---|
| `AppointmentBookedMail` | Fired when a customer successfully books an appointment |
| `AppointmentReminderMail` | Scheduled via `ReminderJob` — sent to customer before appointment time |
| `LowStockMail` | Dispatched by `LowStockAlertJob` when product quantity falls below threshold |
| `NewReviewMail` | Notifies salon owner when a customer submits a review |

---

## 👥 Roles & Guards

| Role | Guard | Cookie | Scope |
|---|---|---|---|
| `superadmin` | `web` | `laravel_session` | Full platform — all tenants |
| `owner` | `web` | `laravel_session` | Full salon — own tenant only |
| `staff` | `web` | `laravel_session` | Appointments & commissions — own tenant only |
| `customer` | `customer` | `customer_session` | Booking portal — subdomain-scoped tenant only |

**Custom auth components:**
- `TenantAwareUserProvider` — resolves `web`-guard users scoped to their tenant context
- `CustomerAuthMiddleware` — guards customer routes per subdomain
- `CustomerTenantMiddleware` — resolves and binds tenant for customer routes
- `TenantMiddleware` / `TenantWebMiddleware` — binds tenant to request lifecycle for owner/staff routes
- `SuperAdminMiddleware` — gates all `/superadmin/*` routes
- `CheckSubscriptionActive` — blocks owner routes if subscription is expired/suspended (except `/billing`)
- `SecurityHeaders` — appends security HTTP headers globally on all web responses

---

## 🚀 Quick Start

### Prerequisites

- PHP 8.2+
- Composer 2.x
- MySQL 8.0+
- Node.js *(optional — only needed if modifying frontend assets)*

### 1. Clone the repository

```bash
git clone https://github.com/your-username/Beauty_Parlour.git
cd Beauty_Parlour
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Create database & run migrations

```bash
# Create the database
mysql -u root -p -e "CREATE DATABASE lumiere;"

# Run migrations and seeders
php artisan migrate --seed
```

### 5. Link storage

```bash
php artisan storage:link
```

### 6. Start queue worker *(for emails & background jobs)*

```bash
php artisan queue:work --queue=default
```

### 7. Serve the application

```bash
php artisan serve
```

> **Tip:** Use `composer dev` to start the server, queue worker, log watcher, and Vite all in one command.

---

## ⚙️ Environment Setup

Key `.env` variables to configure:

```env
APP_NAME=LUMIÈRE
APP_ENV=local
APP_URL=http://lumiere.test:8000
APP_DOMAIN=lumiere.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lumiere
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_DOMAIN=.lumiere.test

RAZORPAY_KEY_ID=your_razorpay_key
RAZORPAY_KEY_SECRET=your_razorpay_secret

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@lumiere.app
MAIL_FROM_NAME=LUMIÈRE

QUEUE_CONNECTION=database
```

> `SESSION_DOMAIN=.lumiere.test` (with leading dot) is required so session cookies are shared across all subdomains under the same root domain.

---

## 🌐 Local Subdomain Setup

Since LUMIÈRE uses subdomains for tenant isolation, you must add entries to your system hosts file for local development.

**Windows:** `C:\Windows\System32\drivers\etc\hosts`  
**Linux / macOS:** `/etc/hosts`

```
127.0.0.1   lumiere.test
127.0.0.1   naturelle.lumiere.test
127.0.0.1   bliss.lumiere.test
```

> For every new tenant you provision via the Super Admin panel, add its subdomain to the hosts file during local development.

---

## 🌱 Database & Seeding

```bash
# Fresh start (drops all tables, re-runs all migrations + seeders)
php artisan migrate:fresh --seed

# Run individual seeders
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan db:seed --class=PlanSeeder
php artisan db:seed --class=DatabaseSeeder
```

### Default credentials after seeding

| Role | URL | Email | Password |
|---|---|---|---|
| Super Admin | `lumiere.test/superadmin/dashboard` | `superadmin@lumiere.app` | `password@123` |
| Owner | `{subdomain}.lumiere.test/owner/dashboard` | *(seeded per tenant)* | `password@123` |
| Customer | `{subdomain}.lumiere.test/login` | *(seeded per tenant)* | `password@123` |

> ⚠️ Change all default passwords immediately in any non-local environment.

---

## 💳 Subscription Plans

Three plans are seeded out of the box. New tenants start on a **14-day free trial** automatically on registration.

| | Free | Basic | Premium |
|---|---|---|---|
| **Price (monthly)** | ₹0 | ₹999 | ₹2,499 |
| **Price (yearly)** | ₹0 | ₹9,999 | ₹24,999 |
| **Max Staff** | 2 | 10 | 50 |
| **Max Services** | 5 | 25 | 100 |
| **Bookings/month** | 50 | 500 | 9,999 |
| **Inventory** | ✗ | ✔ | ✔ |
| **Commissions** | ✗ | ✔ | ✔ |
| **Analytics** | ✗ | ✗ | ✔ |

> Plans are enforced at runtime by `CheckSubscriptionActive` middleware and `AppointmentService` plan-limit checks. Upgrades are processed via Razorpay and activated instantly via webhook.

---

## ⚡ Queue Workers

Two background jobs run via Laravel's database queue:

| Job | Trigger | Action |
|---|---|---|
| `LowStockAlertJob` | Product quantity ≤ `low_stock_threshold` | Sends email alert to salon owner |
| `ReminderJob` | Scheduled before appointment time | Sends reminder email to customer |

```bash
# Start the queue worker
php artisan queue:work --queue=default

# Monitor failed jobs
php artisan queue:failed

# Retry all failed jobs
php artisan queue:retry all
```

> The Super Admin Queue Monitor at `/superadmin/queue` provides a UI to view, retry, or delete failed jobs without needing CLI access.

---

## 🕐 Scheduled Tasks

Three cron-driven tasks run via Laravel Scheduler (`routes/console.php`). Add this single entry to your server crontab:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

| Task | Schedule | What It Does |
|---|---|---|
| **Appointment Reminders** | Every 15 minutes | Finds appointments starting in 2–2:15 hours where `reminder_sent = false`, dispatches `ReminderJob` for each, uses `withoutOverlapping()` |
| **Subscription Expiry Check** | Daily | Finds all `active` subscriptions past their `expires_at` and marks them `expired` |
| **Audit Log Cleanup** | Weekly | Deletes `is_read = true` audit log entries older than 30 days |
| **Database Backup** | Daily at 02:00 | Runs `backup:run` via Spatie Laravel Backup |

---

## 🧪 Testing

**28 feature test suites · 198 total test methods** (194 Feature + 4 Unit) using PHPUnit 11 with SQLite in-memory database (no MySQL required for tests).

```bash
# Run all tests
php artisan test

# Run a specific test suite
php artisan test --filter TieredCommissionTest

# Run with coverage report
php artisan test --coverage
```

| Test Suite | Tests | What It Covers |
|---|:-:|---|
| `AppointmentTest` | 18 | Booking CRUD, status transitions |
| `WebauthroutesTest` | 17 | Route-level auth protection across all roles |
| `DashboardRedirectTest` | 15 | Role-based dashboard redirect per guard |
| `CrossTenantIsolationTest` | 10 | Complete data isolation between tenants |
| `SecurityTest` | 10 | HTTP security headers, XSS, CSRF |
| `TieredCommissionTest` | 9 | Revenue-bracket tier commission logic |
| `FileuploadvalidationTest` | 8 | File type & size upload security |
| `IDORTest` | 7 | Insecure Direct Object Reference prevention |
| `CustomerHistoryTest` | 7 | Customer appointment history |
| `InventoryValuationTest` | 7 | Valuation calculation accuracy |
| `AuthTest` | 6 | Login, logout, brute-force throttling |
| `SlotconflictTest` | 6 | Concurrent booking race conditions & pessimistic locking |
| `LowstockalertJobTest` | 6 | Low-stock job dispatch |
| `ObserverCommissionTest` | 6 | Observer-triggered commission on status change |
| `RazorpayWebhookTest` | 6 | Webhook signature verification & event handling |
| `ServiceTest` | 6 | Service management |
| `PasswordconfirmationTest` | 6 | Sensitive action re-authentication |
| `SuperadmintenantsqlTest` | 6 | SQL injection prevention in super admin |
| `StaffTest` | 5 | Staff CRUD & availability |
| `SubscriptionexpiryTest` | 5 | Plan expiry enforcement |
| `CommissionTest` | 3 | Commission calculation correctness |
| `RolePermissionTest` | 3 | RBAC enforcement per role |
| `TenantMiddlewareTest` | 3 | Subdomain resolution logic |
| `TenantRegisterTest` | 3 | Full tenant registration flow |
| `InventoryTest` | 4 | Stock-in, stock-out, auto-deduction on appointment complete |
| `PlanlimitEnforcementTest` | 4 | Feature gating by subscription plan |
| `ProductTest` | 4 | Product CRUD |
| `SessionandremembermeTest` | 4 | Session security & remember-me behavior |
| **Unit / ExampleTest** | 4 | Login page, guest redirect, health check, superadmin page |
| **Total** | **198** | |

---

## 🔒 Security Implementation

| Threat | Mitigation |
|---|---|
| **Cross-Tenant Data Leakage (IDOR)** | `BelongsToTenant` trait adds a global Eloquent scope scoping all queries to `tenant_id`. Fail-closed: if tenant context is missing (and not console/test/superadmin), the query is hard-blocked with a 401 exception. `tenant_id` is auto-injected on `creating` and made immutable on `updating` — any attempt to change it throws an exception. |
| **Race Conditions (Double Booking)** | `lockForUpdate()` pessimistic locking on appointment slot checks and inventory deductions |
| **Duplicate Inventory Deduction** | Idempotency enforced via `InventoryTransaction.reference_id` + `appointment_id` before any write |
| **Commission Abuse** | Hard 50% commission cap enforced in `AppointmentObserver` |
| **Data Exposure in Bulk Queries** | Selective column hydration — sensitive fields never loaded in list queries |
| **Brute Force** | `throttle:5,1` rate limiting on all login and auth endpoints |
| **Session Hijacking** | `SESSION_ENCRYPT=true` + database session driver; separate `customer_session` cookie isolates customer sessions |
| **CSRF** | Laravel CSRF middleware on all state-changing routes; Razorpay webhook explicitly exempted |
| **SQL Injection** | Eloquent ORM with parameterized queries throughout; dedicated SQL injection test suite |
| **XSS / Clickjacking** | `SecurityHeaders` middleware sets `X-Frame-Options`, `X-XSS-Protection`, and `Content-Security-Policy` headers |
| **Insecure File Upload** | File type and size validation covered by `FileuploadvalidationTest` |
| **Subscription Bypass** | `CheckSubscriptionActive` middleware gates all owner routes except `/billing` |

---

## 🔌 REST API

A versioned REST API (`/api/v1`) is available alongside the web interface, secured via **Laravel Sanctum** tokens. All authenticated routes require `Authorization: Bearer {token}` and are rate-limited to `throttle:60,1`.

### Authentication

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `POST` | `/api/v1/auth/register` | Public (`throttle:3,1`) | Register new tenant + owner (returns Sanctum token) |
| `POST` | `/api/v1/auth/login` | Public (`throttle:5,1`) | Login, returns Sanctum token |
| `POST` | `/api/v1/auth/logout` | Sanctum | Revoke current token |
| `POST` | `/api/v1/auth/customer/register` | Public + Tenant | Register customer under a tenant subdomain |

### Owner Endpoints (`role:owner`)

| Method | Endpoint | Description |
|---|---|---|
| CRUD | `/api/v1/owner/services` | Services management (apiResource) |
| CRUD | `/api/v1/owner/staff` | Staff management (apiResource) |
| CRUD | `/api/v1/owner/products` | Product management (apiResource) |
| `GET` | `/api/v1/owner/appointments` | All appointments (paginated) |
| `GET` | `/api/v1/owner/appointments/today` | Today's appointments |
| `PATCH` | `/api/v1/owner/appointments/{id}/status` | Update appointment status |
| `GET` | `/api/v1/owner/products-low-stock` | Products below threshold |
| `POST` | `/api/v1/owner/inventory/stock-in` | Record stock intake |
| `POST` | `/api/v1/owner/inventory/stock-out` | Record manual stock out |
| `GET` | `/api/v1/owner/commissions` | All commission records |
| `GET` | `/api/v1/owner/commissions/staff-summary` | Per-staff commission summary |
| `PATCH` | `/api/v1/owner/commissions/{staffId}/mark-paid` | Mark commissions as settled |
| `GET` | `/api/v1/owner/analytics/summary` | Revenue & booking summary |
| `GET` | `/api/v1/owner/analytics/revenue` | Revenue trends |
| `GET` | `/api/v1/owner/analytics/services` | Service performance |
| `GET` | `/api/v1/owner/analytics/customers` | Customer analytics |

### Customer Endpoints (`role:customer`)

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/v1/customer/slots` | Real-time slot availability check |
| `GET` | `/api/v1/customer/appointments` | Customer's appointment history |
| `POST` | `/api/v1/customer/appointments` | Book a new appointment |
| `PATCH` | `/api/v1/customer/appointments/{id}/cancel` | Cancel a pending appointment |

### Super Admin Endpoints (`role:superadmin`)

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/v1/admin/tenants` | Paginated list of all tenants |

---

## 🌐 API & Health Check

```bash
# Health check endpoint — no authentication required
GET /up

# Response
{
  "status": "healthy",
  "timestamp": "2026-06-27T10:00:00.000000Z",
  "services": {
    "database": "ok",
    "cache": "ok"
  }
}
```

**Internal AJAX endpoints:**

| Endpoint | Auth | Purpose |
|---|---|---|
| `GET /owner/live-stats` | Owner | Dashboard live booking count polling |
| `GET /customer/slots` | Customer | Real-time slot availability check |
| `GET /inventory/service-mapping/for-service` | Owner | AJAX product list per service |
| `GET /superadmin/notifications` | Super Admin | Notification bell unread count |
| `POST /razorpay/webhook` | CSRF-exempt | Handles `payment.captured` (activates subscription), `payment.failed` (logs), `refund.processed` (expires subscription); signature verified via `hash_hmac` |

---

## 📁 Project Structure

```
Beauty_Parlour/
├── app/
│   ├── Auth/
│   │   └── TenantAwareUserProvider.php       # Custom auth provider (web guard, tenant-scoped)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/                         # AuthController, CustomerAuthController, TenantRegisterController
│   │   │   ├── SuperAdmin/                   # 8 platform admin controllers
│   │   │   ├── Owner/                        # Owner API controllers (analytics, appointments, etc.)
│   │   │   ├── Customer/                     # Customer slot & appointment API controllers
│   │   │   └── Web/
│   │   │       ├── Owner/                    # 18 owner web controllers
│   │   │       ├── Customer/                 # 7 customer web controllers
│   │   │       └── Staff/                    # Staff panel controller
│   │   ├── Middleware/                       # 8 custom middlewares
│   │   │   ├── TenantMiddleware.php
│   │   │   ├── TenantWebMiddleware.php
│   │   │   ├── CustomerAuthMiddleware.php
│   │   │   ├── CustomerTenantMiddleware.php
│   │   │   ├── SuperAdminMiddleware.php
│   │   │   ├── CheckSubscriptionActive.php
│   │   │   ├── SecurityHeaders.php
│   │   │   └── EnsureEmailIsVerified.php
│   │   └── Requests/                         # Form request validation classes
│   ├── Jobs/
│   │   ├── LowStockAlertJob.php
│   │   └── ReminderJob.php
│   ├── Mail/                                 # 4 transactional email Mailable classes
│   ├── Models/                               # 17 Eloquent models
│   ├── Observers/
│   │   └── AppointmentObserver.php           # Drives commission calculation + inventory deduction
│   ├── Policies/
│   │   ├── AppointmentPolicy.php
│   │   └── ReviewPolicy.php
│   ├── Providers/
│   │   └── AppServiceProvider.php            # Observer registration, custom auth provider binding
│   ├── Services/
│   │   └── AppointmentService.php            # Slot availability & conflict detection logic
│   ├── Traits/
│   │   └── BelongsToTenant.php               # Auto tenant-scoping for all models
│   └── View/
│       └── Composers/SidebarComposer.php
├── database/
│   ├── migrations/                           # 43 migrations
│   ├── factories/                            # 7 Eloquent factories for testing
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── PlanSeeder.php
│       └── RolesAndPermissionsSeeder.php
├── resources/views/
│   ├── superadmin/                           # dashboard, tenants, analytics, revenue, queue, settings, subscriptions
│   ├── owner/                                # dashboard, appointments, services, staff, customers,
│   │                                         # inventory, commissions, gallery, reviews, analytics, settings, subscription
│   ├── customer/                             # landing, auth, home, appointments, services, products, gallery, reviews, profile
│   │   └── invoice.blade.php                 # PDF invoice template
│   ├── staff/                                # dashboard, appointments, commissions, profile
│   ├── layouts/                              # 5 base Blade layouts
│   ├── components/                           # Reusable Blade components (buttons, cards, forms, tables, skeletons)
│   ├── emails/                               # 4 transactional email templates
│   └── errors/                              # 403, 404, 500 custom error pages
├── routes/
│   ├── web.php                               # 324 lines — all web routes
│   └── api.php                               # 171 lines — API routes
├── tests/
│   └── Feature/                              # 28 feature test suites
├── config/
│   ├── auth.php                              # Multi-guard configuration
│   ├── backup.php                            # Spatie backup config
│   └── permission.php                        # Spatie permission config
└── public/
    └── frontend/
        ├── css/                              # app.css, auth.css, owner.css, customer.css, superadmin.css, landing.css
        └── js/                               # app.js, owner.js, customer.js, superadmin.js
```

---

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch: `git checkout -b feature/your-feature`
3. Commit your changes: `git commit -m "feat: describe your change"`
4. Push to your branch: `git push origin feature/your-feature`
5. Open a Pull Request against `main`

Follow [Conventional Commits](https://www.conventionalcommits.org/) — `feat:`, `fix:`, `docs:`, `refactor:`, `test:`, `chore:`

---

## 📄 License

This project is licensed under the [MIT License](LICENSE).

---

<div align="center">

Built with ❤️ using **Laravel 12** · **PHP 8.2** · **MySQL 8.0** · **Razorpay**

</div>
