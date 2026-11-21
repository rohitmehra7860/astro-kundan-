# 🚀 Website Backend (Laravel Monolith)

A clean, highly optimized monolithic backend built from scratch using **Laravel** and **PHP 8.4-FPM**, running in a high-performance **Docker** environment. This backend provides complete backoffice management for **Blogs (Posts)**, **Services**, **Pages**, and **Shops (Products)**, alongside secure token-based REST APIs and contact lead management.

---

## 🛠️ The Premium Stack

* **Modular System Design**: A clean core monolith combined with custom self-contained modules (`app/Modules`) extending base classes to build plug-and-play features.
* **Backoffice Panel (Filament)**: A premium admin panel that handles resources recursively, consumes model policies automatically, and provides high-end layout tables with unified actions.
* **Visual Gallery & Media Picker**: Replaced standard selector dropdowns with a custom visual `ImagePicker` card displaying previews, details, and selection action links.
* **Direct AJAX File Uploads**: Integrated drag-and-drop / select file uploads inside the image selector modal. Displays live upload progress (`Uploading 42%`) using XMLHttpRequest and updates the parent form state dynamically.
* **View-Mode UX Safeguards**: Actions on the `ImagePicker` are state-aware. In read-only View mode:
  - Clicking "Choose Image" pops up a clean warning modal: *Please edit the record to select or change the image.*
  - Clicking "Remove" displays a warning toast: *Cannot remove image. Please edit the record first to make changes.*
* **Git-Safe Storage (Hostinger Deployment Fix)**: Custom storage configuration redirecting all public uploads to the root `/uploads` directory. This keeps your uploads immune to Git deployment wipes (common on Hostinger) and maps them cleanly via a `public/uploads` symlink.
* **Dynamic XML Sitemap (`/sitemap.xml`)**: Serves search-engine-ready XML sitemaps with image node extensions. Supports:
  - Published Pages (`/{slug}`)
  - Published Posts (`/blog/{slug}`)
  - Published Services (`/services/{slug}`)
  - Blog & Service Categories (`/blog/category/{slug}` or `/services/category/{slug}`)
* **Collapsible Panel Sections**: The Site Settings form is split into 8 organized collapsible cards that start collapsed by default for a clean, modern user dashboard.
* **Activity Logger (Spatie Activitylog)**: Automated database audit logs for all CRUD operations.

---

## 📦 Installed Feature Modules

The backend includes a self-contained contact leads module located in `app/Modules/ContactLeads`.

### Contact Leads Module (`app/Modules/ContactLeads`)
Manages incoming public form submissions. Key components:
* **Public Form Endpoint:** `POST /api/contact-leads` (with a rate-limiter of 5 requests/min).
* **Asynchronous Queue Mailing:** Automatically queues notifications in the background using `ContactLeadNotificationMail` to send details to receiver emails without blocking the request.
* **Safe Fallback:** Looks up receiver email lists in settings, falls back to `site_email` if empty, and wraps sending in a try-catch to keep submissions safe if the SMTP server is down.

---

## 📂 Core Structure Mapping

```
app/
├── Filament/
│   ├── Pages/
│   │   └── SiteSettings.php  (Branding, Favicon, Social, SMTP, and Script configurations)
│   ├── Shared/
│   │   ├── ImagePicker.php   (Custom visual gallery picker with View-mode locks)
│   │   └── SeoForm.php       (Unified SEO form layout schema)
│   └── Resources/
│       ├── Categories/       (BlogCategory & ServiceCategory resources)
│       ├── Media/            (Central media repository upload resource)
│       ├── Pages/            (CMS Page resource - with Dynamic Page Sections)
│       ├── Posts/            (Blog post resource)
│       └── Services/         (Service resource)
├── Http/
│   └── Controllers/
│       ├── Admin/
│       │   └── MediaUploadController.php (Handles AJAX uploads in modal picker)
│       └── Api/
│           ├── PageController.php        (Retrieves Pages & Dynamic Sections JSON)
│           ├── ServiceApiController.php  (Retrieves Services & Categories JSON)
│           └── SitemapController.php     (Generates dynamic XML sitemap payload)
├── Models/
│   ├── Setting.php           (Fast key-value cache settings)
│   ├── MediaUpload.php       (Stores media records linked via Spatie Media Library)
│   ├── Post.php / Service.php / Page.php (Models using Spatie HasMedia)
├── Modules/
│   └── ContactLeads/         (Contact form submissions, event-driven emails)
├── routes/
│   ├── api.php               (Sanctum token-based REST endpoints)
│   └── web.php               (Admin uploads, sitemap.xml, and page routes)
docker-compose.yml             (Nginx, PHP 8.4-FPM, MySQL 8.0, Redis, phpMyAdmin)
```

---

## 🚀 Step-by-Step Installation Guide

### 1. Clone the Repository & Configure Env
Copy the template `.env` file to create your environment configuration:
```bash
cp .env.example .env
```

### 2. Boot up the Docker Containers
Spin up Nginx, PHP 8.4-FPM, MySQL 8.0, Redis, and phpMyAdmin:
```bash
docker compose up -d --build
```

### 3. Run Core Migrations & Seeders
Generate your application key, run database migrations, and seed core roles and permissions:
```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

### 4. Seed Module-Specific Permissions
Our modules use a custom Artisan command to scan and seed their unique permissions:
```bash
docker compose exec app php artisan module:seed
```

### 5. Configure Storage Permissions & Symlink
Create the root uploads folder, grant storage permissions, and link them to the public directory:
```bash
mkdir -p uploads
docker compose exec app chmod -R 777 uploads storage bootstrap/cache
docker compose exec app php artisan storage:link
```

---

## 🧪 Testing & Development Tools

### 1. Running the Queue Worker
Because lead notifications and other emails are queued in the background, run this worker to process pending jobs locally:
```bash
docker compose exec app php artisan queue:work
```
*(To run emails instantly without a worker in local development, change `.env` to `QUEUE_CONNECTION=sync`).*

### 2. local Email Preview
You can view the rendering of the contact lead email in your browser by visiting:
`http://localhost:8001/preview-contact-mail`
*(Only accessible in local environment).*

### 3. Secure Browser Migration Router
If you do not have SSH terminal access (e.g. on Hostinger production servers), you can trigger migrations from your browser:
* **Local:** `http://localhost:8001/run-migrations`
* **Production:** `https://yourdomain.com/run-migrations?key=YOUR_SECRET_KEY`
*(Configure `MIGRATION_KEY` in `.env` to secure it).*

### 4. Running PHPUnit Tests
Run the automated test suite inside Docker:
```bash
docker compose exec app php artisan test
```

---

## 🌐 Hostinger Production Deployment

To prevent Git deployments from wiping out uploaded media, we route uploads outside the public deployment directory:

1. **Uploads Location:** File uploads are saved in `/uploads` in the project root.
2. **Symlink Setup:** Run the following command via SSH inside the project root on Hostinger:
   ```bash
   php artisan storage:link
   ```
   *(Ensure any existing `public/uploads` folder or broken link in the `public` directory is deleted first).*

This connects the browser-facing `public/uploads` path directly to the root `/uploads` folder, securing all user-uploaded images during code deployments.
