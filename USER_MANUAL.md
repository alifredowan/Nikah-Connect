# Nikah Connect - User & Administrator Manual
**Islamic Matrimonial Platform User Guide & Database Configuration Handbook**

Welcome to **Nikah Connect**! This platform is purpose-built to facilitate Islamic marriage (*Nikah*) under strict adherence to Islamic Shariah, modesty (*Haya*), family involvement (*Wali* / Guardian oversight), and deen-centric compatibility. Unlike conventional dating apps, Nikah Connect emphasizes privacy, character, and religious commitment.

---

## Table of Contents
1. [User Roles & Demo Logins](#1-user-roles--demo-logins)
2. [Step-by-Step Feature Walkthrough](#2-step-by-step-feature-walkthrough)
   - [A. Account Registration & Mandatory 18+ Verification](#a-account-registration--mandatory-18-verification)
   - [B. Deen & Islamic Profile Setup](#b-deen--islamic-profile-setup)
   - [C. Photo Upload & Server-Side Modesty Protection (FR-2.2)](#c-photo-upload--server-side-modesty-protection-fr-22)
   - [D. KYC & Government ID Verification](#d-kyc--government-id-verification)
   - [E. Candidate Discovery & Deen Compatibility Scoring](#e-candidate-discovery--deen-compatibility-scoring)
   - [F. Expressing Halal Interest & Guardian (Wali) Workflow](#f-expressing-halal-interest--guardian-wali-workflow)
   - [G. Chaperoned Halal Messaging & Safety Scanner](#g-chaperoned-halal-messaging--safety-scanner)
   - [H. Membership Tiers & Promotional Coupons](#h-membership-tiers--promotional-coupons)
   - [I. Administration & Moderation Console](#i-administration--moderation-console)
   - [J. REST API V1 for Mobile Applications](#j-rest-api-v1-for-mobile-applications)
3. [Database Configuration & Switching Guide (Postgres to MySQL)](#3-database-configuration--switching-guide-postgres-to-mysql)
4. [Essential CLI Commands](#4-essential-cli-commands)

---

## 1. User Roles & Demo Logins

For immediate demonstration and end-to-end evaluation, a quick-access **Demo Role Switcher** bar is fixed at the top of the interface. You can switch between any persona with a single click:

| Role | Demo Credentials | Password | Permissions & Capabilities |
|---|---|---|---|
| **🧔 Groom (Seeker)** | `seeker.groom@nikahconnect.test` | `password` | Search candidate brides, send halal interest, initiate chaperoned chat upon mutual acceptance |
| **🧕 Bride (Seeker)** | `seeker.bride@nikahconnect.test` | `password` | Search candidates, review received interest, link father/wali, configure chaperone level |
| **🛡️ Guardian (Wali / Father)** | `wali.father@nikahconnect.test` | `password` | Review pending suitor requests, approve/reject introductions, observe conversations as chaperone |
| **⚖️ Moderation Staff** | `moderator@nikahconnect.test` | `password` | Review KYC identity submissions, handle reported candidate flags, enforce platform safety |
| **👑 Platform Administrator** | `admin@nikahconnect.test` | `password` | Full system control: live metrics, fee configuration, discount coupons, immutable audit logs |

---

## 2. Step-by-Step Feature Walkthrough

### A. Account Registration & Mandatory 18+ Verification
1. Navigate to **"Create Profile"** (`/register`) from the navigation bar.
2. Provide your legal name, email, phone number, and gender (Brother / Sister).
3. **Date of Birth Validation**: The platform strictly enforces an **18+ age requirement**. Registrations with an age under 18 are rejected automatically with a clear validation error.
4. Select marital status (*Never Married, Divorced, Widowed*), agree to the Islamic Code of Conduct, and complete registration.

### B. Deen & Islamic Profile Setup
After signing in, visit **Profile -> Edit Deen & Preferences** (`/profile/edit`) to complete your matrimonial biodata:
- **Sect & Madhhab**: Sunni (Hanafi, Shafi'i, Maliki, Hanbali), Salafi, etc.
- **Prayer Frequency**: Always 5 daily prayers on time, usually on time, striving to improve.
- **Modesty & Sunnah**: Hijab/Niqab practice for sisters; Sunnah beard practice for brothers.
- **Quran Literacy**: Hafiz/Hafiza, fluent Tajweed reciter, basic reading, or beginner student.
- **Halal Dietary Adherence**: Strict Zabiha Halal, Halal only, or striving.
- **Family Structure**: Living preference (*Independent residence vs. Joint/Extended family*), number of siblings, and family religiosity.
- **Partner Preferences**: Desired age range, preferred education level, acceptable madhhabs, and relocation flexibility.

### C. Photo Upload & Server-Side Modesty Protection (FR-2.2)
1. Go to your **Profile** page and upload up to 6 photos.
2. **Server-Side Modesty Security**:
   - By default, candidate photos are **blurred on the server side** across public search, discovery, and showcase feeds.
   - Unlike basic client-side CSS blur filters that can be bypassed by inspecting HTML devtools or hovering over image tags, Nikah Connect passes protected images through a secure server-side GD transformation (`IMG_FILTER_PIXELATE` and multi-pass `IMG_FILTER_GAUSSIAN_BLUR`).
   - The original raw image file path is strictly hidden from unapproved visitors. Even if an inspector extracts the image URL, the server streams only the blurred byte stream or an encrypted privacy silhouette until **mutual consent** or an explicit **Photo Access Grant** is issued.

### D. KYC & Government ID Verification
1. Access the **Government ID & KYC Verification** module from your profile dashboard.
2. Upload an official identification document (National ID card, Passport, or Driver's License) along with an optional selfie.
3. Moderation staff review the document in the administrative queue (`/admin/verifications`).
4. Once verified, a blue **"Verified Candidate"** badge (`✓ Verified`) is displayed on your public profile, increasing trust and match response rates.

### E. Candidate Discovery & Deen Compatibility Scoring
1. Visit **"Find Matches"** (`/discover`) from the top navigation.
2. **Deen-Weighted Compatibility Algorithm**:
   - The matching engine dynamically calculates a personalized compatibility percentage (e.g., **94% Match**) based on multi-dimensional criteria:
     - **Deen & Religious Practice (45% Weight)**: Madhhab harmony, prayer consistency, halal dietary commitment, and Quranic knowledge.
     - **Family & Lifestyle (30% Weight)**: Desired living arrangement (independent vs. joint), family values, and children preferences.
     - **Demographics (25% Weight)**: Age alignment, educational background, and location.
3. Filter by age range, madhhab, location, prayer frequency, and Wali requirement.
4. Save your preferred filter sets for one-click access via **"Save this Search"**.

### F. Expressing Halal Interest & Guardian (Wali) Workflow
1. When you identify a prospective match, click **"Express Halal Interest"** and compose an introductory note.
2. **The Islamic Wali Safeguard**:
   - If the sister has registered a linked Guardian (Wali), her acceptance alone does not immediately unlock private communication.
   - An introductory notification is routed to the Wali's dedicated dashboard (`/wali/dashboard`).
   - The Wali reviews the suitor's biodata, religious credentials, and background, and makes an informed decision (**Approve** or **Decline**).
   - Only after the Wali approves the proposal does the secure messaging channel unlock!

### G. Chaperoned Halal Messaging & Safety Scanner
1. Once mutual consent and Wali clearance are obtained, access the conversation from **Messages** (`/messages`).
2. **Chaperone Banner**: If a Wali is assigned, a prominent notice reminds both parties: **"🛡️ Islamic Chaperone (Wali) Active"**—the guardian has live oversight of the discussion.
3. **Automated Safety Scanner**:
   - The chat engine actively monitors incoming messages for unauthorized external contact disclosures before verified clearance.
   - Sharing raw phone numbers, private email addresses, or off-platform handles (*WhatsApp, Telegram, Snapchat, Instagram*) triggers an instant warning notice.
4. If either party observes disrespectful conduct, they can tap **"Report"** to flag the chat directly to the moderation team.

### H. Membership Tiers & Promotional Coupons
1. Visit **Plans & Pricing** (`/pricing`) to view available subscriptions:
   - **Free Seeker**: 5 interest requests per day, standard discovery filters.
   - **Premium Believer ($19.99/mo)**: Unlimited daily interests, full deen compatibility breakdown, advanced search filters.
   - **VIP Family Pack ($39.99/mo)**: Priority profile ranking, up to 3 linked guardian accounts, and fast-track KYC verification.
2. Test the coupon engine by applying promotional codes during checkout:
   - **`HALAL20`**: 20% discount on any plan.
   - **`BARAKAH50`**: 50% discount on any plan.

### I. Administration & Moderation Console
Authorized staff can sign in to the Administrative Suite (`/admin`) to oversee platform health:
- **System Dashboard**: View total candidates, verified percentage, active conversations, and platform revenue.
- **KYC Verification Queue** (`/admin/verifications`): Inspect submitted documents, approve verified badges, or request resubmission.
- **Reports & Safety Center** (`/admin/reports`): Review reported profiles/messages, issue formal warnings, suspend accounts, or ban violators.
- **Policy Settings** (`/admin/settings`): Dynamically modify subscription fees, daily limits, and coupon codes without editing source code.
- **Immutable Audit Logs** (`/admin/audit-logs`): Maintain a complete chronological trail of all critical system actions for regulatory compliance.

### J. REST API V1 for Mobile Applications
Nikah Connect provides a complete, versioned RESTful API under `/api/v1/` for native iOS (Swift), Android (Kotlin), or cross-platform Flutter / React Native clients:
- `POST /api/v1/auth/register`: Create candidate account with age validation.
- `POST /api/v1/auth/login`: Sanctum Bearer token authentication.
- `GET /api/v1/profile`: Retrieve authenticated candidate profile with deen attributes.
- `GET /api/v1/discover`: Retrieve candidate profiles with compatibility scores and search filters.
- `POST /api/v1/interests/{userId}`: Dispatch halal interest request.
- `POST /api/v1/conversations/{id}/messages`: Send real-time chaperoned messages through the safety scanner.

---

## 3. Database Configuration & Switching Guide (Postgres to MySQL)

The platform currently operates on **PostgreSQL 16**. However, all migrations, query builders, and models adhere strictly to **ANSI SQL / Database-Agnostic** design patterns. You can transition the entire database from PostgreSQL to MySQL in less than 60 seconds without modifying a single line of application code.

### Step 1: Open the Environment Configuration File
Open the [`.env`](file:///e:/Alif/OWN/Muslim%20Matrimonial/.env) file located in the project root directory.

### Step 2: Update the Database Driver & Connection Parameters
Locate the database section. The current PostgreSQL configuration is:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nikah_connect
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

**To switch to MySQL, update the block as follows:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nikah_connect
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
```

*(Note: For rapid unit testing or local zero-setup environments, SQLite can also be enabled simply by specifying `DB_CONNECTION=sqlite`)*.

### Step 3: Run Database Migrations & Seeders
After updating your `.env` credentials, execute the following command in your terminal:
```bash
php artisan migrate:fresh --seed
```

This single command will:
1. Provision all 13 core database tables with proper indexing, foreign keys, and unique constraints.
2. Initialize default administrative policies, fee tiers, and discount coupons.
3. Populate realistic demonstration profiles (grooms, brides, wali guardians, administrators).

---

## 4. Essential CLI Commands

- **Start Local Web Server**:
  ```bash
  php artisan serve --port=8000
  ```
  *Open `http://127.0.0.1:8000` in your web browser.*

- **Execute Test Suite**:
  ```bash
  php vendor/phpunit/phpunit/phpunit tests/Unit
  php vendor/phpunit/phpunit/phpunit tests/Feature/MatrimonialFeatureTest.php
  ```

- **Enforce PSR-12 / Laravel Pint Code Style**:
  ```bash
  php vendor/bin/pint --format agent
  ```

- **Rebuild Frontend Assets (Tailwind CSS & JavaScript)**:
  ```bash
  npm run build
  ```

- **Clear System Caches (Config, Routes, Views)**:
  ```bash
  php artisan optimize:clear
  ```
