# LeadFlow — Lead Management System

> A full-stack **Lead Management System** built with Core PHP, MySQL, Bootstrap 5 and Chart.js.  
> Developed as a college submission project demonstrating MVC architecture, session-based authentication, role-based access control and real-time data visualization.

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Technologies Used](#2-technologies-used)
3. [Features](#3-features)
4. [Project Structure](#4-project-structure)
5. [Requirements](#5-requirements)
6. [Installation & Setup](#6-installation--setup)
7. [Database Configuration](#7-database-configuration)
8. [Demo Credentials](#8-demo-credentials)
9. [How to Run](#9-how-to-run)
10. [Testing Checklist](#10-testing-checklist)
11. [Known Limitations](#11-known-limitations)

---

## 1. Project Overview

**LeadFlow** is a web-based Lead Management System (LMS) designed for small sales teams.  
It provides a centralized platform to capture, track and convert sales leads through an organized pipeline — from initial contact to closed deal.

**Key objectives:**
- Track leads through pipeline stages (New → Contacted → Interested → Converted / Lost).
- Schedule and manage follow-up activities (calls, emails, meetings).
- Manage team members with role-based access control (Admin vs Employee).
- Generate real-time reports and analytics from live MySQL data.
- Export data to CSV for external reporting.

---

## 2. Technologies Used

| Technology | Purpose |
|---|---|
| PHP 8.x (Core PHP) | Backend server-side logic, MVC controllers |
| MySQL / MariaDB | Relational database (via XAMPP) |
| PDO (PHP Data Objects) | Secure database queries with prepared statements |
| HTML5 + CSS3 | Page structure and styling |
| Bootstrap 5.3 | Responsive dark-themed UI components |
| Bootstrap Icons 1.11 | Icon library |
| Chart.js 4.4 | Interactive data visualization charts |
| JavaScript (Vanilla) | Client-side interactivity and AJAX |
| Apache (XAMPP) | Local web server with URL rewriting |
| phpMyAdmin | Database administration |
| VS Code | Code editor |

---

## 3. Features

### ✅ Authentication
- User registration with validation and password hashing (`password_hash`)
- Login with `password_verify` and PHP session management
- Session security: session ID regeneration on login
- Logout destroys session completely
- All private pages protected from unauthenticated access

### ✅ Dashboard
- Live KPI cards: Total Leads, New Leads, Contacted, Converted, Pending Follow-ups
- Conversion Rate calculation from real MySQL data
- Recent Leads table (latest 5 leads from database)
- Upcoming Follow-ups (next 5 due tasks)
- Chart.js line chart (lead trends) and doughnut chart (pipeline status)

### ✅ Lead Management
- Add leads via modal form (AJAX, no page reload)
- Auto-generated Lead Codes (LF-XXXX format)
- Edit leads inline with modal
- Delete leads (single and batch)
- Search leads by name, email, company, phone, lead code
- Filter by pipeline status, priority and assigned representative
- Export all leads to CSV
- Activity log: every create/update action is recorded

### ✅ Follow-up Management
- Schedule follow-up activities (Phone Call, Video Call, Email, Meeting, Demo)
- Link each follow-up to a specific lead
- Auto-detect overdue tasks (past due date → overdue status)
- Mark complete / reopen with one click (AJAX toggle)
- Edit and delete follow-ups
- Filter by tab: All / Upcoming / Overdue / Completed
- Export follow-ups to CSV

### ✅ User Management (Admin only)
- View all registered team members with status and role
- Add new users with password and role assignment
- Edit name, email and role
- Activate / Deactivate accounts (toggle)
- Delete users
- Admin cannot delete or deactivate their own account
- Employees see a read-only view; all management actions are Admin-only

### ✅ Reports & Analytics
- Live KPI cards from MySQL (Total Leads, Converted, New, Top Source)
- Follow-up summary stats (Total, Upcoming, Overdue, Completed)
- Leads by Pipeline Status — Doughnut chart (real MySQL data)
- Leads by Acquisition Source — Horizontal bar chart (real MySQL data)
- Sales Rep Performance — Grouped bar chart (real MySQL data)
- Detailed leads table with all records
- Sales Rep Leaderboard with win rates
- Export Leads report as CSV
- Export Follow-ups report as CSV

### ✅ Settings
- Update profile name and email (persisted to MySQL and session)
- Change password (with current password verification)
- Password change forces re-login for security
- Display preferences panel

### ✅ Security
- PDO prepared statements throughout (no raw SQL string interpolation)
- Password hashing with `PASSWORD_DEFAULT` (`bcrypt`)
- `htmlspecialchars()` output escaping in all views
- Session-based authentication with session regeneration
- Role-based access control (`requireAdmin()` for sensitive routes)
- Cannot delete/deactivate own account

---

## 4. Project Structure

```
leadflow/
├── app/
│   ├── config/
│   │   └── routes.php              # URL route definitions
│   ├── controllers/
│   │   ├── AuthController.php      # Login, Register, Logout
│   │   ├── DashboardController.php # Dashboard KPIs & charts
│   │   ├── LeadController.php      # Lead CRUD, export
│   │   ├── FollowUpController.php  # Follow-up CRUD, export
│   │   ├── UserController.php      # User management (Admin)
│   │   ├── ReportController.php    # Analytics & CSV reports
│   │   └── SettingController.php   # Profile & password settings
│   ├── core/
│   │   ├── Controller.php          # Base controller (renderView, auth helpers)
│   │   ├── Database.php            # PDO singleton connection
│   │   ├── Model.php               # Base model
│   │   └── Router.php              # URL router dispatcher
│   ├── models/
│   │   ├── User.php                # User queries
│   │   ├── Lead.php                # Lead queries with filters
│   │   ├── FollowUp.php            # Follow-up queries
│   │   └── Activity.php            # Activity log queries
│   └── views/
│       ├── layouts/
│       │   ├── header.php          # HTML head, CSS links
│       │   ├── sidebar.php         # Left navigation sidebar
│       │   ├── topbar.php          # Top navigation bar
│       │   └── footer.php          # JS scripts, closing tags
│       ├── auth/
│       │   ├── login.php
│       │   └── register.php
│       ├── dashboard/
│       │   └── index.php
│       ├── leads/
│       │   └── index.php
│       ├── followups/
│       │   └── index.php
│       ├── users/
│       │   └── index.php
│       ├── reports/
│       │   └── index.php
│       └── settings/
│           └── index.php
├── assets/
│   ├── css/
│   │   └── style.css               # Custom dark theme CSS
│   ├── js/
│   │   ├── app.js                  # Global utilities (toast, escapeHtml)
│   │   ├── auth.js                 # Login/register UI helpers
│   │   ├── charts.js               # Chart.js initialization
│   │   ├── leads.js                # Leads AJAX/table management
│   │   └── followups.js            # Follow-ups AJAX management
│   └── images/
├── database/
│   └── leadflow_db.sql             # Full database schema + seed data
├── index.php                       # Application entry point
├── .htaccess                       # Apache URL rewriting rules
└── README.md                       # This file
```

---

## 5. Requirements

### Software Requirements
| Software | Version | Purpose |
|---|---|---|
| XAMPP | 8.2+ | Apache + MySQL + PHP environment |
| PHP | 8.0+ | Server-side scripting |
| MySQL / MariaDB | 10.4+ | Database |
| Web Browser | Chrome / Firefox / Edge | Accessing the application |

### Hardware Requirements
| Component | Minimum |
|---|---|
| RAM | 4 GB |
| Storage | 200 MB free |
| Processor | Intel Core i3 or equivalent |
| OS | Windows 10 / 11 (or Linux/macOS with XAMPP) |

---

## 6. Installation & Setup

### Step 1 — Install XAMPP
1. Download XAMPP from [https://www.apachefriends.org](https://www.apachefriends.org)
2. Install it (default path: `C:\xampp`)
3. Open **XAMPP Control Panel**
4. Start **Apache** and **MySQL** services (both must show green/running)

### Step 2 — Copy Project Files
1. Copy the entire `leadflow` folder into your XAMPP `htdocs` directory:
   ```
   C:\xampp\htdocs\leadflow\
   ```
2. Verify that `C:\xampp\htdocs\leadflow\index.php` exists.

### Step 3 — Enable Apache URL Rewriting
The project uses `.htaccess` for clean URLs.

1. Open `C:\xampp\apache\conf\httpd.conf` in a text editor.
2. Find the line:
   ```
   #LoadModule rewrite_module modules/mod_rewrite.so
   ```
3. Remove the `#` to uncomment it:
   ```
   LoadModule rewrite_module modules/mod_rewrite.so
   ```
4. Find the `<Directory "C:/xampp/htdocs">` block and change `AllowOverride None` to:
   ```
   AllowOverride All
   ```
5. Save and restart Apache from XAMPP Control Panel.

---

## 7. Database Configuration

### Step 1 — Create the Database
1. Open your browser and go to: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Click **"New"** in the left sidebar.
3. Enter database name: `leadflow_db`
4. Select collation: `utf8mb4_unicode_ci`
5. Click **Create**.

### Step 2 — Import the SQL File
1. Click on `leadflow_db` in the left sidebar.
2. Click the **Import** tab at the top.
3. Click **"Choose File"** and select:
   ```
   leadflow/database/leadflow_db.sql
   ```
4. Click **"Go"** to run the import.
5. You should see 4 tables created: `users`, `leads`, `followups`, `lead_activities`.

### Step 3 — Configure Database Connection
Open `app/config/database.php` and verify these settings match your XAMPP:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'leadflow_db');
define('DB_USER', 'root');
define('DB_PASS', '');        // Default XAMPP has no password
define('DB_CHARSET', 'utf8mb4');
```

> **Note:** Default XAMPP MySQL has username `root` and an empty password. If you have set a MySQL password, update `DB_PASS` accordingly.

---

## 8. Demo Credentials

Two demo accounts are pre-seeded in the database:

| Role | Name | Email | Password |
|---|---|---|---|
| **Admin** | Alex Morgan | `admin@leadflow.io` | `LeadFlow2026!` |
| **Employee** | University Reviewer | `reviewer@university.edu` | `Graduation2026!` |

> **Important:** The Admin account has full access to all modules including User Management. The Employee account has read and create access but cannot manage users.

---

## 9. How to Run

1. Start **XAMPP** — ensure Apache and MySQL are running.
2. Open your browser and navigate to:
   ```
   http://localhost/leadflow/
   ```
3. You will be redirected to the **Login page**.
4. Sign in with the Admin credentials above.
5. Explore the full system:
   - **Dashboard** — [http://localhost/leadflow/dashboard](http://localhost/leadflow/dashboard)
   - **Leads** — [http://localhost/leadflow/leads](http://localhost/leadflow/leads)
   - **Follow-ups** — [http://localhost/leadflow/followups](http://localhost/leadflow/followups)
   - **Users** — [http://localhost/leadflow/users](http://localhost/leadflow/users) *(Admin only)*
   - **Reports** — [http://localhost/leadflow/reports](http://localhost/leadflow/reports)
   - **Settings** — [http://localhost/leadflow/settings](http://localhost/leadflow/settings)

---

## 10. Testing Checklist

### Authentication
- [x] Register a new user → redirects to login with success message
- [x] Duplicate email is rejected during registration
- [x] Login with correct credentials → redirects to dashboard
- [x] Login with wrong password → shows error message
- [x] Inactive account cannot log in
- [x] Accessing any page without login redirects to login
- [x] Logout destroys session and redirects to login

### Dashboard
- [x] KPI cards show real counts from MySQL
- [x] Conversion rate is calculated correctly
- [x] Recent leads table shows latest 5 leads
- [x] Upcoming follow-ups section works
- [x] Charts render with real data

### Lead Management
- [x] Add lead modal opens and validates required fields
- [x] Lead is saved to MySQL and appears in table
- [x] Lead Code is auto-generated (LF-XXXX format)
- [x] Search works (by name, email, company, code)
- [x] Status filter works
- [x] Priority filter works
- [x] Edit lead saves changes to MySQL
- [x] Delete lead removes it from MySQL
- [x] CSV export downloads file with correct data
- [x] Activity is logged when lead is created/updated

### Follow-up Management
- [x] Schedule follow-up links to an existing lead
- [x] Follow-up appears in correct tab (Upcoming/Overdue/Completed)
- [x] Overdue detection: past due date shows Overdue badge
- [x] Checkbox toggle marks task as completed
- [x] Edit modal pre-fills existing data
- [x] Delete removes follow-up from MySQL
- [x] CSV export downloads follow-ups data

### User Management (Admin)
- [x] Users page shows all registered members
- [x] Add user with name, email, password, role
- [x] Duplicate email rejected
- [x] Edit user updates name, email, role
- [x] Toggle status Activates/Deactivates account
- [x] Delete user removes them permanently
- [x] Admin cannot delete/deactivate own account
- [x] Employee sees read-only view (cannot access management features)
- [x] Employee receives 403 when accessing `/users` directly

### Reports
- [x] Pipeline status doughnut chart shows real MySQL data
- [x] Source breakdown bar chart shows real MySQL data
- [x] Rep performance chart shows real MySQL data
- [x] All leads detailed table renders from MySQL
- [x] Sales rep leaderboard with win rates
- [x] Export Leads CSV downloads correctly
- [x] Export Follow-ups CSV downloads correctly

### Settings
- [x] Profile tab shows current name and email
- [x] Save profile updates name and email in MySQL
- [x] Session is updated immediately (no re-login needed for profile)
- [x] Change password verifies current password first
- [x] Wrong current password is rejected
- [x] Mismatched new passwords are rejected
- [x] Successful password change forces re-login

### Security
- [x] All database queries use PDO prepared statements
- [x] Passwords hashed with `password_hash()` (bcrypt)
- [x] Output escaped with `htmlspecialchars()` in all views
- [x] Session regenerated on login
- [x] Admin-only routes protected with `requireAdmin()`
- [x] 403 returned for unauthorized role access

---

## 11. Known Limitations

| Limitation | Notes |
|---|---|
| **No email sending** | No SMTP integration; password reset requires Admin action |
| **No file uploads** | Profile photos not supported in this version |
| **Static notifications** | In-app notifications are UI-only (not stored in database) |
| **Display preferences** | Currency/date format toggles are UI-only (not persisted to database) |
| **No pagination in reports** | All leads are shown in one table; performance may degrade with very large datasets |
| **Local environment only** | Configured for XAMPP `localhost`; production deployment would require server config changes |

---

*LeadFlow — Built with Core PHP & MySQL | College Submission Project 2026*
