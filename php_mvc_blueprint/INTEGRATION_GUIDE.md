# LeadFlow — PHP MVC Backend Integration Architecture Guide

This guide describes how to decompose the static HTML/CSS/JS frontend of **LeadFlow** into an idiomatic custom **PHP + MySQL Model-View-Controller (MVC)** framework.

---

## 1. Target Directory Structure

```text
leadflow-mvc/
├── app/
│   ├── config/
│   │   ├── config.php          # Database credentials & app constants
│   │   └── routes.php          # Route definitions mapping URI to controllers
│   ├── core/
│   │   ├── Controller.php      # Base controller (view rendering, auth helpers)
│   │   ├── Database.php        # PDO database singleton connection
│   │   └── Model.php           # Base PDO query builder / active record
│   ├── controllers/
│   │   ├── AuthController.php      # login, register, logout, session check
│   │   ├── DashboardController.php # overview metrics, recent activity
│   │   ├── LeadController.php      # index, store, update, destroy, exportCsv
│   │   ├── FollowupController.php  # index, store, toggleComplete, destroy
│   │   ├── UserController.php      # team management, role provisioning
│   │   ├── ReportController.php    # analytics aggregation queries
│   │   └── SettingController.php   # profile update, password change
│   ├── models/
│   │   ├── User.php
│   │   ├── Lead.php
│   │   ├── Followup.php
│   │   └── ActivityLog.php
│   └── views/
│       ├── layouts/
│       │   ├── header.php          # HTML head, styles, fonts
│       │   ├── sidebar.php         # Reusable navigation menu
│       │   ├── topbar.php          # Global search, notification bell, user profile
│       │   └── footer.php          # Modals, toast container, script tags
│       ├── auth/
│       │   ├── login.php           # From login.html
│       │   └── register.php        # From register.html
│       ├── dashboard/
│       │   └── index.php           # From index.html main content
│       ├── leads/
│       │   ├── index.php           # From leads.html data grid
│       │   ├── modal_add_edit.php  # Reusable Add/Edit form
│       │   └── modal_view.php      # Lead detail drawer
│       ├── followups/
│       │   └── index.php           # From followups.html
│       ├── users/
│       │   └── index.php           # From users.html
│       ├── reports/
│       │   └── index.php           # From reports.html
│       └── settings/
│           └── index.php           # From settings.html
└── public/
    ├── index.php                   # Front controller / router entry point
    ├── .htaccess                   # Rewrite engine routing all traffic to index.php
    └── assets/
        ├── css/style.css
        ├── js/
        │   ├── app.js
        │   ├── auth.js
        │   ├── charts.js
        │   ├── leads.js
        │   └── followups.js
        └── images/
```

---

## 2. Recommended MySQL Schema

```sql
CREATE DATABASE IF NOT EXISTS leadflow_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE leadflow_db;

-- 1. Users & Team
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Employee') NOT NULL DEFAULT 'Employee',
    department VARCHAR(100) DEFAULT 'Inside Sales',
    avatar_url VARCHAR(255) NULL,
    status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Leads Table
CREATE TABLE leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_code VARCHAR(20) NOT NULL UNIQUE, -- e.g. LF-1042
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL,
    phone VARCHAR(40) NULL,
    company VARCHAR(150) NOT NULL,
    lead_source ENUM('Website Form', 'LinkedIn Outreach', 'Google Ads', 'Referral', 'Cold Outreach', 'Partner Event') DEFAULT 'Website Form',
    status ENUM('New', 'Contacted', 'Interested', 'Converted', 'Lost') NOT NULL DEFAULT 'New',
    priority ENUM('Low', 'Medium', 'High') NOT NULL DEFAULT 'Medium',
    assigned_to_id INT NULL,
    notes TEXT NULL,
    follow_up_date DATETIME NULL,
    avatar_url VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 3. Follow-ups Table
CREATE TABLE followups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id INT NULL,
    customer_name VARCHAR(120) NOT NULL,
    company VARCHAR(150) NULL,
    assigned_to_id INT NULL,
    activity_type ENUM('Video Call', 'Phone Call', 'Email', 'In-Person Meeting') NOT NULL DEFAULT 'Video Call',
    due_date DATETIME NOT NULL,
    status ENUM('upcoming', 'overdue', 'completed') NOT NULL DEFAULT 'upcoming',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to_id) REFERENCES users(id) ON DELETE SET NULL
);
```

---

## 3. Form Field Mapping & Expected Endpoints

| Frontend Form | Method | PHP MVC Endpoint | Target Controller & Action | Required `$_POST` Fields |
|---|---|---|---|---|
| Sign In (`login.html`) | `POST` | `/login` | `AuthController@login` | `email`, `password`, `remember_me` |
| Registration (`register.html`) | `POST` | `/register` | `AuthController@register` | `name`, `email`, `password`, `password_confirmation`, `terms` |
| Add Lead (`leads.html` / `index.html`) | `POST` | `/leads/store` | `LeadController@store` | `name`, `email`, `phone`, `company`, `lead_source`, `status`, `priority`, `assigned_to`, `follow_up_date`, `notes` |
| Edit Lead (`leads.html`) | `POST` | `/leads/update` | `LeadController@update` | `id`, `name`, `email`, `phone`, `company`, `lead_source`, `status`, `priority`, `assigned_to`, `follow_up_date`, `notes` |
| Delete Lead (`leads.html`) | `POST` | `/leads/delete` | `LeadController@destroy` | `id` |
| Batch Delete (`leads.html`) | `POST` | `/leads/batch-delete` | `LeadController@batchDestroy` | `ids[]` (JSON or array) |
| Schedule Follow-up (`followups.html`) | `POST` | `/followups/store` | `FollowupController@store` | `customer_name`, `company`, `type`, `assigned_to`, `due_date`, `notes` |
| Complete Follow-up (`followups.html`) | `POST` | `/followups/toggle` | `FollowupController@toggleComplete` | `id`, `status` |
| Add User (`users.html`) | `POST` | `/users/store` | `UserController@store` | `name`, `email`, `role`, `department`, `temp_password`, `send_invite` |
| Update Profile (`settings.html`) | `POST` | `/settings/profile` | `SettingController@updateProfile` | `name`, `email`, `phone`, `job_title`, `bio`, `avatar` |
| Change Password (`settings.html`) | `POST` | `/settings/password` | `SettingController@updatePassword` | `current_password`, `new_password`, `new_password_confirmation` |

---

## 4. How to Convert Layout into Partials

In your base `app/core/Controller.php`:

```php
public function renderView($viewName, $data = []) {
    extract($data);
    require_once __DIR__ . '/../views/layouts/header.php';
    require_once __DIR__ . '/../views/layouts/sidebar.php';
    require_once __DIR__ . '/../views/layouts/topbar.php';
    require_once __DIR__ . "/../views/{$viewName}.php";
    require_once __DIR__ . '/../views/layouts/footer.php';
}
```

This guarantees 100% DRY code with zero HTML duplication.
