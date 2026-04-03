# Backend Fix for Hostinger Deployment

The error `SyntaxError: Unexpected token '<'` and the `500 Internal Server Error` on your API endpoints are caused by two main issues:
1. **Incorrect Paths**: The `index.php` file in your `api/` folder cannot find the CodeIgniter core files because it's looking in `../app/` instead of `../ci-core/app/`.
2. **Routing Mismatch**: Since your backend is located in a subfolder named `api/`, the URI passed to CodeIgniter might not include the `api/` prefix, causing it to skip your route group.

## 1. Apply Local Changes
I have already updated the following files in your workspace:
- `server/public/index.php`: Now checks for the `ci-core` folder.
- `server/app/Config/Routes.php`: Now handles routes both with and without the `/api` prefix.

**Action**: Upload these updated files to your Hostinger server.
- Upload `server/public/index.php` to `public_html/stagging/api/index.php`.
- Upload `server/app/Config/Routes.php` to `public_html/stagging/ci-core/app/Config/Routes.php`.

---

## 2. Update .htaccess Files (Critical)

You need three `.htaccess` files to handle the root website, the admin dashboard, and the API correctly.

### Root .htaccess (`public_html/stagging/.htaccess`)
This ensures that the main website React app works, while ignoring requests meant for the API or Admin folders.

```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /stagging/
  
  # 1. Ignore existing files and directories
  RewriteCond %{REQUEST_FILENAME} -f [OR]
  RewriteCond %{REQUEST_FILENAME} -d
  RewriteRule ^ - [L]

  # 2. Let /api/ and /admin/ handle their own routing
  RewriteRule ^api/ - [L]
  RewriteRule ^admin/ - [L]

  # 3. Handle main website SPA routing
  RewriteRule . index.html [L]
</IfModule>
```

### Admin .htaccess (`public_html/stagging/admin/.htaccess`)
This ensures the Admin React app handles its own internal routing (like `/admin/dashboard`). Create this file if it doesn't exist.

```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /stagging/admin/
  
  RewriteCond %{REQUEST_FILENAME} -f [OR]
  RewriteCond %{REQUEST_FILENAME} -d
  RewriteRule ^ - [L]

  # Handle Admin SPA routing
  RewriteRule . index.html [L]
</IfModule>
```

### API .htaccess (`public_html/stagging/api/.htaccess`)
This ensures requests to `/api/popup` are correctly sent to CodeIgniter's `index.php`.

```apache
<IfModule mod_rewrite.c>
    Options +FollowSymlinks
    RewriteEngine On
    RewriteBase /stagging/api/

    # Redirect Trailing Slashes...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Rewrite to index.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^([\s\S]*)$ index.php/$1 [L,NC,QSA]
</IfModule>
