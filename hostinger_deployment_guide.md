# Hostinger Deployment & Database Setup Guide

This guide will walk you through deploying your React frontends (Admin & Website) and the CodeIgniter 4 backend to your Hostinger server, as well as updating your database schema.

## 1. Prepare Frontend Builds

### Admin Dashboard (client/)
1. Open a terminal in the root folder.
2. Run the build command for the admin panel:
   ```bash
   cd client
   pnpm build
   ```
3. This creates a `dist` folder inside `client/`. Rename or move its contents to an `admin` folder for upload.

### Client Website (website/)
1. Open a terminal in the root folder.
2. Run the build command for the website:
   ```bash
   cd website
   pnpm build
   ```
3. This creates a `dist` folder inside `website/`. These contents will go directly into the root `public_html`.

---

## 2. Upload Backend (server/)

Upload your local `server/` folder to `public_html/server/` on Hostinger.

### Important: Update Production .env
Once uploaded, editing the file `public_html/server/.env` via the Hostinger File Manager:
1. Set `CI_ENVIRONMENT = production`.
2. Update the database credentials to match your Hostinger MySQL database:
   ```ini
   database.default.hostname = localhost
   database.default.database = u686584126_photography
   database.default.username = u686584126_user
   database.default.password = [YOUR_DB_PASSWORD]
   ```
3. Update the `app.baseURL`:
   ```ini
   app.baseURL = 'https://thepatilphotography.com/'
   ```

---

## 3. Database Schema Update

I have updated the `server/init_db.php` script to automatically apply all missing columns (order, thumbnail, gallery, etc.).

### Option A: Via Browser (Easiest)
1. Ensure the database credentials in `server/init_db.php` (lines 4-7) match your Hostinger DB.
2. Visit the following URL in your browser:
   `https://thepatilphotography.com/server/init_db.php`
3. You should see a success message: `"Users, Services, Sliders, Stories, Testimonials, Team and Popups tables updated."`

### Option B: Via phpMyAdmin (Manual)
If you prefer manual SQL, run these queries in your Hostinger phpMyAdmin:
```sql
ALTER TABLE sliders ADD COLUMN IF NOT EXISTS `order` INT DEFAULT 0;
ALTER TABLE sliders ADD COLUMN IF NOT EXISTS `subtitle` VARCHAR(255) NULL;
ALTER TABLE love_stories ADD COLUMN IF NOT EXISTS `gallery` TEXT NULL;
ALTER TABLE love_stories CHANGE COLUMN IF EXISTS `display_order` `order` INT DEFAULT 0;
ALTER TABLE testimonials CHANGE COLUMN IF EXISTS `clientName` `name` VARCHAR(100) NULL;
ALTER TABLE testimonials CHANGE COLUMN IF EXISTS `review` `text` TEXT NULL;
ALTER TABLE testimonials ADD COLUMN IF NOT EXISTS `role` VARCHAR(100) NULL;
ALTER TABLE testimonials ADD COLUMN IF NOT EXISTS `thumbnail` TEXT NULL;
ALTER TABLE testimonials ADD COLUMN IF NOT EXISTS `order` INT DEFAULT 0;
ALTER TABLE team_members ADD COLUMN IF NOT EXISTS `order` INT DEFAULT 0;
ALTER TABLE popups ADD COLUMN IF NOT EXISTS `subtitle` TEXT NULL;
ALTER TABLE popups ADD COLUMN IF NOT EXISTS `link` VARCHAR(255) NULL;
ALTER TABLE popups ADD COLUMN IF NOT EXISTS `status` ENUM('Active', 'Inactive') DEFAULT 'Active';
```

---

## 4. Routing & .htaccess

Ensure your `public_html/.htaccess` is set up correctly to bridge the frontends and backend.

### Root .htaccess (`public_html/.htaccess`)
```apache
<IfModule mod_rewrite.c>
  RewriteEngine On

  # 1. API: Route /api to the CI4 server
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule ^api/(.*)$ server/public/index.php/$1 [L,QSA]

  # 2. Uploads: Bridge /uploads to server uploads
  RewriteRule ^uploads/(.*)$ server/public/uploads/$1 [L]

  # 3. Admin: Support SPA routing for /admin/
  RewriteCond %{REQUEST_URI} ^/admin/
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule ^admin/(.*)$ admin/index.html [L,QSA]

  # 4. Website: Support SPA routing for main site
  RewriteCond %{REQUEST_URI} !^/api/
  RewriteCond %{REQUEST_URI} !^/admin/
  RewriteCond %{REQUEST_URI} !^/server/
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule . /index.html [L]
</IfModule>
```

---

## 5. Folder Permissions
Ensure the following folders are writable by the server (CHMOD 755 or 777):
- `public_html/server/writable/`
- `public_html/server/public/uploads/` (Create this folder if it doesn't exist)
