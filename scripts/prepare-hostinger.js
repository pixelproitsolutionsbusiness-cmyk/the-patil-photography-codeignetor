import fs from 'fs/promises';
import { existsSync } from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const root = path.resolve(__dirname, '..');

// CONFIGURATION: Set your subfolder name here (e.g., 'stagging' or '' for root)
const SUBFOLDER = 'stagging'; 
const BASE_PATH = SUBFOLDER ? `/${SUBFOLDER}/` : '/';

const buildPath = path.join(root, 'dist/spa');
const serverPath = path.join(root, 'server');
const outPath = path.join(root, 'deploy-hostinger');

async function prepare() {
    console.log(`Preparing deployment for Hostinger (Subfolder: ${SUBFOLDER})...`);

    // 1. Clear output
    if (existsSync(outPath)) {
        await fs.rm(outPath, { recursive: true, force: true });
    }
    await fs.mkdir(outPath, { recursive: true });

    // 2. Copy React build to ROOT
    console.log("Copying React assets...");
    await fs.cp(buildPath, outPath, { recursive: true });

    // 3. Ensure website assets are present
    console.log("Ensuring website assets...");
    const websiteAssetsPath = path.join(root, 'website/assets');
    const outWebsiteAssetsPath = path.join(outPath, 'website/assets');
    if (existsSync(websiteAssetsPath)) {
        await fs.mkdir(outWebsiteAssetsPath, { recursive: true });
        await fs.cp(websiteAssetsPath, outWebsiteAssetsPath, { recursive: true });
    }

    // 4. Prepare CodeIgniter in 'api/'
    console.log("Preparing api folder...");
    const apiPath = path.join(outPath, 'api');
    await fs.mkdir(apiPath, { recursive: true });
    await fs.cp(path.join(serverPath, 'public'), apiPath, { recursive: true });

    // 5. Copy CI core files to a hidden folder 'ci-core/'
    console.log("Copying CodeIgniter core...");
    const corePath = path.join(outPath, 'ci-core');
    await fs.mkdir(corePath, { recursive: true });
    
    const foldersToCopy = ['app', 'system', 'vendor', 'writable', '.env'];
    for (const folder of foldersToCopy) {
        const src = path.join(serverPath, folder);
        if (existsSync(src)) {
            if (folder === '.env') {
                // Special case: Update baseURL in .env
                let envContent = await fs.readFile(src, 'utf8');
                envContent = envContent.replace(/app\.baseURL = '.*'/g, `app.baseURL = 'https://thepatilphotography.com/${SUBFOLDER}/'`);
                await fs.writeFile(path.join(corePath, folder), envContent);
            } else {
                await fs.cp(src, path.join(corePath, folder), { recursive: true });
            }
        }
    }

    // 6. Update api/index.php to point to ci-core
    console.log("Updating api/index.php paths...");
    const indexPath = path.join(apiPath, 'index.php');
    if (existsSync(indexPath)) {
        let indexContent = await fs.readFile(indexPath, 'utf8');
        indexContent = indexContent.replace(/\.\.\/app\/Config\/Paths\.php/g, "../ci-core/app/Config/Paths.php");
        await fs.writeFile(indexPath, indexContent);
    }
    
    // 6.1 Remove redundant .htaccess in api folder to avoid conflicts with root .htaccess
    const apiHtaccess = path.join(apiPath, '.htaccess');
    if (existsSync(apiHtaccess)) {
        await fs.unlink(apiHtaccess);
        console.log("Removed redundant api/.htaccess");
    }

    // 7. Fix index.html (Main Site) for subfolder hosting
    console.log("Finalizing index.html...");
    const mainIndexPath = path.join(outPath, 'index.html');
    if (existsSync(mainIndexPath)) {
        let content = await fs.readFile(mainIndexPath, 'utf8');
        
        // 7.1 Restore missing CSS tags
        if (!content.includes('website/assets/vendor/aos/aos.css')) {
            const cssTags = `
    <!-- Manual Website CSS -->
    <link href="${BASE_PATH}website/assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="${BASE_PATH}website/assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="${BASE_PATH}website/assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
    <link href="${BASE_PATH}website/assets/css/main.css" rel="stylesheet">`;
            content = content.replace(/<\/head>/i, `${cssTags}\n  </head>`);
        }

        // 7.2 Ensure manual JS is present (and NOT duplicated)
        if (!content.includes('website/assets/vendor/aos/aos.js')) {
            const jsTags = `
    <!-- Manual Website JS -->
    <script src="${BASE_PATH}website/assets/vendor/aos/aos.js"></script>
    <script src="${BASE_PATH}website/assets/vendor/glightbox/js/glightbox.min.js"></script>
    <script src="${BASE_PATH}website/assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="${BASE_PATH}website/assets/js/react-helpers.js"></script>`;
            content = content.replace(/<\/body>/i, `${jsTags}\n  </body>`);
        }

        // 7.3 Prefix any existing website/assets/ paths correctly
        content = content.split('src="/website/assets/').join(`src="${BASE_PATH}website/assets/`);
        content = content.split('href="/website/assets/').join(`href="${BASE_PATH}website/assets/`);
        content = content.split('src="website/assets/').join(`src="${BASE_PATH}website/assets/`);
        content = content.split('href="website/assets/').join(`href="${BASE_PATH}website/assets/`);

        await fs.writeFile(mainIndexPath, content);
    }

    // 8. Create root .htaccess for React SPA fallbacks
    console.log("Creating root .htaccess...");
    const htaccess = `# Hostinger Deployment .htaccess
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase ${BASE_PATH}

  # 1. Forward API requests to CodeIgniter (in /api folder)
  # Re-add api/ prefix and use index.php?/ for better compatibility
  # Added condition to prevent infinite recursion
  RewriteCond %{REQUEST_URI} !index\.php
  RewriteRule ^api/(.*)$ api/index.php?/api/$1 [L,NC,QSA]

  # 2. Redirect /admin to the admin subfolder's index.html
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_URI} ^${BASE_PATH}admin [NC]
  RewriteRule ^admin(.*)$ admin/index.html [L]

  # 3. Handle Main Website SPA (Strict Exclusion of API and Admin)
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  # Ensure we NEVER serve index.html for anything in api/ or admin/ folders
  RewriteCond %{REQUEST_URI} !^${BASE_PATH}api/ [NC]
  RewriteCond %{REQUEST_URI} !^${BASE_PATH}admin/ [NC]
  RewriteCond %{REQUEST_URI} !^${BASE_PATH}api$ [NC]
  RewriteCond %{REQUEST_URI} !^${BASE_PATH}admin$ [NC]
  RewriteRule . index.html [L]
</IfModule>
`;
    await fs.writeFile(path.join(outPath, '.htaccess'), htaccess);

    console.log("\nDone! Build ready in: " + outPath);
    console.log(`To deploy: Upload the contents of 'deploy-hostinger' to your /public_html/${SUBFOLDER} folder.`);
}

prepare().catch(console.error);
