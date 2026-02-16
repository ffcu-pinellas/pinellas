# 🏦 Pinellas FCU: Platform Workflow Deep-Dive (v2.0)

This document provides a granular, step-by-step technical walkthrough of the member registration lifecycle, from the first click on the Home Page to the final dashboard redirection.

---

## 🧭 Phase 1: The Entry & Initial Identity (Step 1)

### 1.1 Landing & Routing
*   **Action**: User clicks "Register" on the homepage.
*   **Route**: `GET /register` (Name: `register.step1`)
*   **Controller**: `RegisteredUserController@step1`
*   **Action**: Logic checks if `account_creation` is enabled in settings, retrieves the registration page metadata, and serves the `register.blade.php` view.

### 1.2 Step 1 Data Entry & Frontend Logic
*   **Inputs**: Email, Country, Phone, **Date of Birth**, and **SSN**.
*   **Adaptive UX**: As soon as a country is selected:
    *   **JavaScript**: The `countrySelect` change handler splits the value to update the dial code UI.
    *   **Persistence**: It saves the `user_country` to the browser's `localStorage`. This is critical for the "Smart Residency" logic in the next phase.

### 1.3 Step 1 Validation & Session Bridge
*   **Action**: User clicks "Next".
*   **Route**: `POST /register/step1` (Name: `register.step1.store`)
*   **Controller**: `RegisteredUserController@step1Store`
*   **Backend Validation**: 
    ```php
    'email' => ['required', 'email', 'unique:users'],
    'date_of_birth' => ['required', 'date'],
    'ssn' => ['required', 'string', 'max:20'],
    ```
*   **Session Handshake**: If valid, the entire input array is persisted to the server-side session: `Session::put('step1', $request->all())`.
*   **Routing**: Redirects to `register.step2`.

---

## 🏠 Phase 2: Residency & Verification (Step 2)

### 2.1 Contextual UI Loading
*   **Route**: `GET /register/step2` (Name: `register.step2`)
*   **Controller**: `RegisteredUserController@create`
*   **View**: `register2.blade.php`
*   **Smart Placement**: A jQuery handler reads `localStorage.getItem('user_country')`. If it's "USA", it dynamically swaps the address placeholder to an American format (e.g., *"123 Main St, Largo, FL"*) for a premium, local feel.

### 2.2 Full Profile Capture
*   **Inputs**: First Name, Last Name, Branch, Gender, **Residential Address**, **City**, **Zip Code**, and **Security (reCaptcha)**.

### 2.3 Comprehensive Data Merge & Final Store
*   **Action**: User clicks "Register Now".
*   **Route**: `POST /register` (Name: `register.store`)
*   **Controller**: `RegisteredUserController@store`
*   **Ultimate Validation**: The controller validates Step 2 inputs.
*   **Data Aggregation**: 
    ```php
    $formData = array_merge(Session::get('step1', []), $request->all());
    ```
*   **Database Execution**: The `User::create()` method is called, mapping all 12+ fields including the new identity markers.

---

## 🔐 Phase 6: Member Authentication & Access (Login)

### 6.1 Authentication Entry
*   **Action**: User clicks "Sign In" or "Login".
*   **Route**: `GET /login` (Name: `login`)
*   **Controller**: `AuthenticatedSessionController@create`
*   **View**: `login.blade.php`
*   **Logic**: Retrieves metadata (title, bg, description) from the `Page` table. Displays Google reCaptcha if enabled globally.

### 6.2 Intelligent Multi-ID Login
*   **Inputs**: Username or Email, Password.
*   **Logic Check**: `LoginRequest@authenticate`
    *   **Heuristic Detection**: The system checks if the input is a valid email format. 
    *   **Username Fallback**: If it's not an email, the system automatically treats it as a `username` and pivots its query. This provides maximum flexibility for members.
*   **Performance Guard**: `ensureIsNotRateLimited` checks if the IP address has exceeded 5 login attempts, triggering a lockout if necessary.

### 6.3 Security Handshake & OTP
*   **Action**: User submits credentials.
*   **Route**: `POST /login`
*   **Controller**: `AuthenticatedSessionController@store`
*   **Process**:
    *   **Validation**: reCaptcha is verified server-side via the `Recaptcha` rule.
    *   **Authentication**: `Auth::attempt` verifies credentials against the hashed database values.
    *   **Multi-Factor (MFA)**: If `otp_verification` is enabled:
        *   A 4-digit token is generated.
        *   An SMS is dispatched via `smsNotify`.
        *   The user is redirected to an OTP verification screen before dashboard entry.
    *   **Activity Logging**: `LoginActivities::add()` records the login IP, device, and timestamp for security auditing.

### 6.4 Session Lifecycle
*   **Token Regeneration**: `session()->regenerate()` is called to prevent session fixation attacks.
*   **Landing**: The user is redirected to the `HOME` route defined in `RouteServiceProvider`, which is the member dashboard.
*   **Logout Mechanism**: `AuthenticatedSessionController@destroy` invalidates the session and resets the `phone_verified` flag for security.

---

## ⚙️ Phase 7: Infrastructure & Safety Nets

### 3.1 The Database Schema
*   **Migration**: `2026_02_15_052555_add_ssn_to_users_table.php` formally adds the column.
*   **Master Blueprint**: `DB/digibank.sql` is updated for fresh installs.
*   **Model Security**: `User.php` includes `ssn` in the `$fillable` array to permit mass-assignment while keeping sensitive fields protected.

### 3.2 Post-Registration Events
*   **Identity Hashing**: Passwords and passcode are automatically hashed via `Hash::make`.
*   **Notification Engine**: 
    *   `pushNotify`: Admin gets a pulse alert of a new member.
    *   `smsNotify`: The user receives a welcome SMS.
*   **Session Clean-up**: The `step1` temporary session data is flushed, and a `newly_registered` flag is set.

---

## 🏛️ Phase 8: Administrative Management

### 4.1 Admin User View
*   **Access**: Admin Portal > Customer Management > Edit.
*   **File**: `backend/user/include/__basic_info.blade.php`
*   **UI Update**: A dedicated "SSN" field and "Date of Birth" field are visible.
*   **Editing**: Admins can update these details if a member provides updated documentation.

### 4.2 Manual Creation Parity
*   **Controller**: `Backend\UserController@store`
*   **Logic**: The backend store method has been updated to include `$request->ssn`, ensuring staff-created accounts are as complete as self-registered ones.

---

## 🏁 Phase 9: The Dashboard Arrival

### 5.1 Success Transition
*   **Route**: `GET /register/final` (Name: `register.final`)
*   **Controller**: `RegisteredUserController@final`
*   **Action**: Displays a verified success screen. Upon reload or timeout, it checks the `newly_registered` flag.

### 5.2 Dashboard Landing
*   **Route**: `GET /user/dashboard`
*   **Result**: The user is fully authenticated. Their profile is 100% complete with SSN, Address, and DOB, enabling immediate access to all Pinellas FCU banking features.

---

## 🛠️ Deployment Troubleshooting & Fixes

### 1. SQL Import Error (#1146 - Table 'gateways' doesn't exist)
*   **Issue**: This occurs if the SQL dump contains `ALTER TABLE` commands for the deprecated `gateways` table.
*   **Resolution**: I have removed these orphaned commands from `digibank.sql`. The application now uses `deposit_methods` for all gateway-related logic.
*   **Action**: Simply re-import the updated `digibank.sql` provided in this latest build.

### 2. Namespace Detection Issues
*   **Issue**: The environment may fail to detect the app namespace during `php artisan` calls.
*   **Resolution**: Ensure `composer dump-autoload` has been run and that the `psr-4` mapping in `composer.json` correctly points to the `app/` directory.

### 3. Missing SSN/Address Columns
*   **Issue**: User profile fields missing after import.
*   **Resolution**: Ensure you have successfully run the migration `2026_02_15_052555_add_ssn_to_users_table.php` or that your `digibank.sql` import was applied to a clean database.

---
*Technical Documentation generated for the Pinellas Federal Credit Union Digital Remodel.*
