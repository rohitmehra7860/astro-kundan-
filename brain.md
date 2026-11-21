# 🧠 Code Trendly — Comprehensive Project Architecture & Logic Database

This database file contains the complete blueprint of the **Website backend** system. It stores all technical contexts, data models, custom business rules, scheduling logic, and views across the entire workspace from the beginning to the end of the project for ongoing referencing.

---

## 🏗️ 1. Core Stack & Infrastructure
* **Framework:** Laravel 11.x (PHP-FPM 8.2+ environment).
* **Docker Environment:**
  * Container `website_backend_app` (executes PHP, Artisan, and background processes).
  * Container `website_backend_nginx` (directs public traffic on port `8000`).
  * Container `website_backend_mysql` (manages database transactions on port `3307`).
  * Container `website_backend_redis` (provides caching and session variables on port `6379`).
  * Container `website_backend_phpmyadmin` (manages direct database records on port `8080`).
* **Admin Dashboard:** Filament Admin v3 Panel Builder.

---

## 🔐 2. Security, Roles, & Permissions
Permissions are managed using **Spatie Laravel-Permission** and mapped in `RolePermissionSeeder`. 

### Access Roles:
1. **Admin:** Full read, write, edit, delete access (`*` wildcard permissions on all modules).
2. **Editor:** Has standard panel access to manage content and view dashboard analytics.
3. **User:** Restricted read-only view of posts, categories, media, pages, services, and core dashboards.

### Module Permissions List:
* **Contact Leads:** `view contact leads`, `create contact leads`, `edit contact leads`, `delete contact leads`

---

## 📦 3. Core Platforms & SEO System Models
These models handle public content, routing rules, site settings, and search engines.

### A. Pages (`Page.php`)
* Custom content page generator (e.g. Terms, Policy, dynamic custom landing layouts).
* Fields: `id`, `title`, `slug`, `content`, `status`, `deleted_at`.

### B. SEO Metadata (`SeoMetadata.php`)
* Morphic metadata mapper binding to pages, categories, and service objects.
* Fields: `meta_title`, `meta_description`, `meta_keywords`, `canonical_url`, `robots_instructions`, `faqs` (JSON Array), `schema_type` (e.g. product, event, local business), `schema_data` (JSON Array).

### C. Redirect Rules (`RedirectRule.php`)
* Handles old URLs routing automatically to prevent 404 errors.
* Fields: `old_path`, `new_path`, `status_code` (301/302), `is_active`.
* **Normalization Logic:** Auto-formats query variables, enforces starting absolute prefixes, and prevents loop configurations (throwing exceptions if path targets match origin).

### D. Broken Links Monitoring (`BrokenLink.php`)
* Logs bad URLs hit by visitors.
* Fields: `path`, `referrer`, `user_agent`, `ip_address`, `hits_count`, `last_seen_at`.

### E. Newsletter Subscribers (`NewsletterSubscriber.php`)
* Newsletter lists manager.
* Fields: `email`, `name`, `status`, `token`, `subscribed_at`.

### F. Settings (`Setting.php`)
* Central parameters store (social links, contact configurations, API tokens).

---

## 📦 4. Custom Service Module
A bespoke content and services module engineered to match the layout and features of the native Blog/Post modules.

### Database Schema (`services` table)
* `id` (bigint auto-increment, Primary Key)
* `title` (string) - Service name
* `slug` (string, unique) - URI address
* `description` (text, nullable) - Short summary
* `content` (longText, nullable) - Full descriptive content
* `status` (string, default "draft") - draft, published
* `is_featured` (boolean, default false)
* `is_pinned` (boolean, default false)
* `author_id` (foreign key to `users` table)
* `category_id` (foreign key to `categories` table, nullable)
* `featured_image_id` (foreign key to `media_uploads` table, nullable)
* `deleted_at` (soft deletes timestamp)
* `created_at` / `updated_at` (timestamps)


### Models & Traits
* **Activity Tracking:** Model utilizes Spatie `LogsActivity` to track database changes and records revisions in the logs.
* **Media Handling:** Extends Spatie `InteractsWithMedia` to automatically compress and store featured service graphics.
* **SEO Metadata:** Model binds dynamically to polymorphous metadata objects through `HasSeoFallbacks` to generate tags, descriptions, and schemas.

### Web Routing, Controllers, & Blade Layouts
* **Endpoints:**
  * Listing: `GET /services` -> mapped to `WebPageController@services`
  * Details: `GET /services/{slug}` -> mapped to `WebPageController@service`
  * API: `GET /api/services`, `GET /api/services/categories`, and `GET /api/services/{slug}` -> mapped to `ServiceApiController`
* **Views:**
  * `resources/views/front/services.blade.php` (List Layout)
  * `resources/views/front/service.blade.php` (Individual Layout)

---

## 📞 5. ContactLeads Module (`app/Modules/ContactLeads`)
This module handles public user form submissions from the frontend website contact forms, storing entries as actionable leads.

### Database Schema (`contact_leads` table)
* `id` (bigint auto-increment, Primary Key)
* `name` (string) - Sender's name
* `email` (string, indexed) - Sender's contact email
* `phone` (string, nullable) - Contact number
* `subject` (string, nullable) - Inquiry title
* `message` (text) - Text body
* `status` (enum: `new`, `read`, `replied`, `archived`) - Status check
* `source` (string, nullable) - e.g., `contact_form`, `landing_page`, `chatbot`
* `ip_address` (string, nullable) - Sender's source IP address
* `notes` (text, nullable) - Internal admin comments
* `replied_at` (timestamp, nullable) - Log of when response was sent
* `deleted_at` (soft deletes)
* `created_at` / `updated_at`

### Features
* Integrates directly into Filament sidebar.
* Enables admin notes, replies logs, and soft deletes.

---

## ⚙️ Core Application Utilities

### E. Sidebar Clear Cache Utility
* **Path:** `app/Providers/Filament/AdminPanelProvider.php` & `routes/web.php`
* **Operation:** Registers an instant-clearing action button in the sidebar (Admin only). Clicking it triggers Laravel's `optimize:clear`, `view:clear`, `config:clear`, `route:clear`, and `cache:clear` commands, then redirects back to the dashboard with an success notification banner.
