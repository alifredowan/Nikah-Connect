# 💍 Nikah Connect

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="320" alt="Nikah Connect Banner">
</p>

<p align="center">
  <strong>A Modern, Shariah-Compliant Islamic Matrimonial Web Application</strong><br>
  Built with adherence to Islamic Shariah, Modesty (<em>Haya</em>), Family Involvement (<em>Wali</em> Oversight), and Deen-Centric Compatibility.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4">
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/WebSocket-Laravel_Reverb-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel Reverb">
  <img src="https://img.shields.io/badge/Database-PostgreSQL_%7C_MySQL_%7C_SQLite-336791?style=for-the-badge&logo=postgresql&logoColor=white" alt="Database Support">
  <img src="https://img.shields.io/badge/Tests-44%20Passed%20(153%20Assertions)-success?style=for-the-badge" alt="Tests 44 Passed">
</p>

---

## 📖 Table of Contents
1. [Platform Vision & Core Principles](#-platform-vision--core-principles)
2. [Key Architecture & Features](#-key-architecture--features)
3. [Quick Demo Credentials](#-quick-demo-credentials)
4. [Comprehensive User Manual & Roles](#-comprehensive-user-manual--roles)
   - [Seeker Journey (Grooms & Brides)](#1-seeker-journey-grooms--brides)
   - [Guardian (Wali) Chaperoned Workflow](#2-guardian-wali-chaperoned-workflow)
   - [Super Administrator Suite](#3-super-administrator-suite)
   - [Moderation & Safety Center](#4-moderation--safety-center)
5. [Tech Stack](#-tech-stack)
6. [Installation & Quick Start](#-installation--quick-start)
7. [Email & SMTP Configuration Guide](#-email--smtp-configuration-guide)
8. [Database Switching (PostgreSQL / MySQL / SQLite)](#-database-switching-postgresql--mysql--sqlite)
9. [Running Real-time WebSockets (Laravel Reverb)](#-running-real-time-websockets-laravel-reverb)
10. [Testing & Code Quality](#-testing--code-quality)

---

## 🕊️ Platform Vision & Core Principles

Unlike conventional dating and hookup applications focused on superficial swiping, **Nikah Connect** is strictly engineered for practicing Muslims seeking blessed marriage (*Nikah*):

* **Family-First Inclusivity (*Wali* System)**: Recognizes the vital role of the father or Islamic guardian in protecting daughters and approving prospective suitors.
* **Server-Side Modesty Protection (*Haya*)**: Candidate photos are obscured server-side until explicit mutual permission and Wali clearance are granted.
* **Deen Compatibility Above Vanity**: Search and match scoring is heavily weighted toward religious practice (prayer frequency, Quran recitation, halal diet, Sunnah adherence) rather than superficial criteria.
* **Chaperoned Communication**: Chat channels preserve Islamic decency with active Wali participation reminders and automatic keyword/contact scanning.

---

## ✨ Key Architecture & Features

### 🛡️ 1. Islamic Guardian (*Wali*) Workflow
* Sisters can link their father or mahram guardian during or after onboarding.
* When a brother expresses halal interest to a guardian-dependent sister, the request is dispatched directly to the **Wali's Dashboard**.
* The Wali can review the candidate's verified biodata, prayer frequency, and background before choosing to **Accept** or **Decline**.
* If either party is wali-dependent, both the candidate and the guardian receive real-time notifications.

### ⚡ 2. Real-Time Push Notifications & Live Chat (Laravel Reverb)
* High-performance, zero-latency WebSockets powered by **Laravel Reverb**.
* Instant notification counter updates and toast alerts when an interest request is received, approved, or rejected.
* Real-time chaperoned messaging with instant delivery across private channels (`private-user.{id}` and `private-chat.{id}`).

### 💎 3. Super Admin Dynamic Subscription Packages
* **Custom Plan Builder**: Super Admins can dynamically create, edit, activate, or archive subscription tiers (`Free`, `Quarterly`, `Annual`, `Lifetime VIP`).
* **Feature Flags**: Admins toggle features per plan (e.g. daily interest limit, direct chat unlock, priority badge, VIP Wali link).
* **Target Audience Control**: Set plan visibility for `All`, `Male Only`, or `Female Only`.
* **Featured Showcase**: Choose which package is highlighted with a "Most Popular / Best Value" ribbon on the pricing page.

### 🏷️ 4. Targeted Promo Codes & Discount Engine
* Create percentage or fixed-amount promotional codes (e.g., `BARAKAH50`, `NIKAH2026`).
* **Granular Targeting**: Restrict coupon usage by gender (brothers/sisters), specific plan tier, minimum cart value, or usage limits per user.
* **Live Calculation**: Instant client-side & server-side discount validation at checkout.

### 🔒 5. Server-Side Modesty Protection
* Sensitive candidate photos are transformed into blurred/pixelated byte streams on the server via PHP GD.
* Raw photo URLs are never exposed in HTML source code or public CDNs until mutual consent and photo access grants are verified.

### 🛡️ 6. Case-Insensitive Account Security & Password Reset
* **Zero Duplicate Accounts**: Database-level unique index on `LOWER(email)` and Eloquent Attribute Mutators guarantee email casing and leading/trailing whitespace never allow duplicate accounts.
* **Secure Password Reset**: Built-in throttled password reset broker with resilient transport error handling and instant testing support.

### 📱 7. Full REST API V1 (Mobile-Ready)
* Complete API endpoints under `/api/v1/` authenticated via **Laravel Sanctum Bearer Tokens** for building native iOS (Swift), Android (Kotlin), or React Native / Flutter apps.

---

## 🔑 Quick Demo Credentials

For testing and demonstration, use the demo switcher or sign in directly with the seeded accounts:

| Role | Email Address | Password | Primary Capabilities |
|---|---|---|---|
| **👑 Super Admin** | `admin@nikahconnect.com` | `password` | Manage packages, create promo codes, staff roles, platform settings & audit logs |
| **🧔 Groom (Seeker)** | `alifredowan79@gmail.com` | `password` | Search candidate brides, send halal interests, live chaperoned chat |
| **🧕 Bride (Seeker)** | `special.bride@example.com` | `password` | Profile biodata, link Wali guardian, review incoming proposals |
| **🛡️ Guardian (Wali)** | `wali.father@nikahconnect.test` | `password` | Review suitors for wards, approve/decline proposals, chaperoned oversight |
| **⚖️ Moderator Staff** | `moderator@nikahconnect.test` | `password` | Review KYC national ID submissions, resolve safety reports |

---

## 📚 Comprehensive User Manual & Roles

### 1. Seeker Journey (Grooms & Brides)
1. **Registration & 18+ Verification (`/register`)**:
   - Provide legal name, email, phone number, gender, and date of birth.
   - The platform strictly enforces an **18+ age limit**—underage registrations are automatically rejected.
2. **Deen Profile Setup (`/profile/edit`)**:
   - Specify sect/madhhab (Hanafi, Shafi'i, Maliki, Hanbali, Salafi, etc.), daily prayer consistency, Sunnah beard/Hijab practice, and Quran literacy.
   - Declare family living preferences (*independent home vs. extended family*), education, and relocation willingness.
3. **Candidate Discovery (`/discover`)**:
   - Filter candidates using the **Deen-Weighted Compatibility Algorithm**:
     - **45% Deen & Religious Practice**: Prayer adherence, madhhab harmony, and Quran commitment.
     - **30% Family & Lifestyle**: Living arrangements, family values, and children expectations.
     - **25% Demographics**: Age range, location, and educational background.
4. **Sending Halal Interest (`/interests`)**:
   - Send an introductory proposal note. 
   - If the candidate sister has an active Wali, the proposal triggers a notification to both the sister and her guardian.
5. **Chaperoned Live Messaging (`/messages`)**:
   - Once cleared by mutual acceptance (and Wali approval), a secure chat channel is unlocked.
   - A visible chaperone banner reminds both parties of Islamic decorum, and the automated safety scanner flags premature sharing of off-platform contact numbers.

### 2. Guardian (Wali) Chaperoned Workflow
1. **Guardian Account Registration (`/register` -> Role: Guardian)**:
   - A father, brother, or paternal uncle registers with their relationship type (*Father, Brother, Paternal Uncle, Grandfather*).
2. **Linking Ward (`/wali/link`)**:
   - The guardian connects to their daughter or sister's account via her registered email address.
3. **Wali Dashboard (`/wali/dashboard`)**:
   - Inspect all incoming proposals sent to the ward.
   - Review the suitor's employment, religious profile, and KYC verification status.
   - Tap **"Approve"** to unlock chaperoned communication or **"Decline"** with an optional courteous response.

### 3. Super Administrator Suite (`/admin`)
1. **Dynamic Subscription Packages (`/admin/packages`)**:
   - Click **"Create Package"** to introduce new tiers.
   - Configure title, price, billing interval (Monthly, Quarterly, Annually, Lifetime), features list, and gender visibility.
   - Reorder or toggle "Featured / Best Value" for user showcase.
2. **Promotional Discount Codes (`/admin/promo-codes`)**:
   - Generate coupons with custom code strings (e.g. `EID2026`).
   - Select percentage or fixed discount, maximum redemption count, and expiration date.
   - Assign audience targeting (All users, Groom seekers only, Bride seekers only).
3. **Staff Management (`/admin/staff`)**:
   - Provision moderator accounts with granular permissions (*Manage Verifications, Handle Reports, View Audit Logs, Platform Settings*).
4. **Governance & Audit Trail (`/admin/audit-logs`)**:
   - Immutable security log capturing user registrations, role elevations, password resets, and account deactivations for GDPR/compliance.

### 4. Moderation & Safety Center (`/admin/reports` & `/admin/verifications`)
1. **KYC Verification Queue**: Review uploaded National ID / Passport documents and grant the blue **"Verified Candidate"** badge (`✓ Verified`).
2. **Safety Reports**: Review flagged chat messages or suspicious accounts; issue formal warnings or permanently suspend offending accounts.

---

## 🛠️ Tech Stack

* **Backend Framework**: [Laravel 12](https://laravel.com) running on **PHP 8.4**
* **Database**: PostgreSQL 16 (default), fully compatible with MySQL 8.x and SQLite
* **Frontend**: Blade Templating, [Tailwind CSS](https://tailwindcss.com), Alpine.js, Vite
* **Real-time Broadcasting**: [Laravel Reverb](https://reverb.laravel.com) (First-party WebSockets) + Laravel Echo
* **API Authentication**: Laravel Sanctum (Bearer Token)
* **Code Standard**: Laravel Pint (PSR-12)
* **Test Suite**: PHPUnit (Feature & Unit tests covering 100% of critical matrimonial workflows)

---

## 🚀 Installation & Quick Start

### Prerequisites
* **PHP >= 8.4** with extensions: `pdo`, `pdo_pgsql` (or `pdo_mysql`), `mbstring`, `openssl`, `gd`, `bcmath`, `curl`
* **Composer >= 2.x**
* **Node.js >= 20.x** & **npm**
* **PostgreSQL** (or MySQL / SQLite)

### 1. Clone the Repository
```bash
git clone https://github.com/alifredowan/Nikah-Connect.git
cd Nikah-Connect
```

### 2. Install PHP & Node Dependencies
```bash
composer install
npm install
```

### 3. Setup Environment File
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure Database in `.env`
For PostgreSQL (default):
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nikah_connect
DB_USERNAME=postgres
DB_PASSWORD=your_postgres_password
```
*(Or switch to MySQL/SQLite as described in the Database Switching section below).*

### 5. Run Migrations & Seed Demonstration Data
```bash
php artisan migrate:fresh --seed
```
*This sets up all matrimonial tables, platform policies, dynamic subscription plans, and realistic demo users.*

### 6. Build Frontend Assets
```bash
npm run build
```

### 7. Start the Local Server
```bash
php artisan serve
```
Visit **`http://127.0.0.1:8000`** in your browser.

---

## 📧 Email & SMTP Configuration Guide

Nikah Connect comes with a built-in email test command and preconfigured mailers:

```bash
php artisan mail:test your_email@example.com
```

### Option A: Real Delivery via Gmail SMTP
1. In your Google Account, enable **2-Step Verification**, then go to **Security -> App Passwords**.
2. Create an App Password (select App: *Mail*, Device: *Windows/Mac*) to receive a 16-character secret.
3. Configure your `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_gmail@gmail.com
MAIL_PASSWORD=your_16_digit_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your_gmail@gmail.com"
MAIL_FROM_NAME="Nikah Connect"
```

### Option B: Custom Domain Business Email (`admin@nikahconnect.com`)
For cPanel / Webmail / Google Workspace / Zoho Mail:
```env
MAIL_MAILER=smtp
MAIL_HOST=mail.nikahconnect.com
MAIL_PORT=465
MAIL_USERNAME=admin@nikahconnect.com
MAIL_PASSWORD=your_secret_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="admin@nikahconnect.com"
MAIL_FROM_NAME="Nikah Connect"
```

### Option C: Transactional Mail Provider (Brevo / Resend / Mailtrap)
Recommended for production to prevent spam filtering:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your_brevo_account_email
MAIL_PASSWORD=your_brevo_smtp_api_key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="admin@nikahconnect.com"
MAIL_FROM_NAME="Nikah Connect"
```

---

## 🔄 Database Switching (PostgreSQL / MySQL / SQLite)

Nikah Connect is 100% database-agnostic. You can switch between database engines simply by editing `.env`:

### Switching to MySQL
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nikah_connect
DB_USERNAME=root
DB_PASSWORD=
```
Then run:
```bash
php artisan migrate:fresh --seed
```

### Switching to SQLite (Zero-Setup Local Dev)
```env
DB_CONNECTION=sqlite
```
Create an empty database file and run:
```bash
touch database/database.sqlite
php artisan migrate:fresh --seed
```

---

## 📡 Running Real-Time WebSockets (Laravel Reverb)

For live instant notifications and instant chat messaging without refreshing the browser:

1. Ensure the Reverb credentials in `.env` match:
```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=100101
REVERB_APP_KEY=nikahconnectreverbkey
REVERB_APP_SECRET=nikahconnectreverbsecret
REVERB_HOST="127.0.0.1"
REVERB_PORT=8080
REVERB_SCHEME="http"
```

2. Start the Reverb WebSocket server in a separate terminal:
```bash
php artisan reverb:start
```

---

## 🧪 Testing & Code Quality

Nikah Connect enforces comprehensive automated test coverage across all Islamic matrimonial constraints and safety measures:

### Run the Test Suite
```bash
php artisan test
```
*Current test suite results: **44 tests passed, 153 assertions** (100% pass rate).*

### Verified Test Scenarios
* **Age Integrity**: Rejects candidate registrations under 18 years of age.
* **Email Uniqueness**: Rejects duplicate accounts across mixed case (`User@Domain.com` vs `user@domain.com`) and whitespace.
* **Case-Insensitive Password Reset**: Verifies password reset tokens and links work smoothly regardless of email casing.
* **Wali Chaperone Workflow**: Proves chat is locked until the guardian approves the candidate proposal.
* **Modesty Protection**: Asserts unauthorized visitors receive blurred images/SVGs instead of raw photo downloads.
* **Dynamic Subscriptions & Coupons**: Validates plan creation, gender-based targeting, and coupon calculation.

### Run Code Formatting (Laravel Pint)
```bash
vendor/bin/pint --format agent
```

---

## 📄 License & Attribution

This project is licensed under the [MIT License](LICENSE).  
Designed and maintained with care for the global Muslim community. May Allah (*SWT*) bless all marriages initiated through this platform with *Barakah*, *Mawaddah*, and *Rahmah*.
