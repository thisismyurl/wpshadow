# Codespaces Localhost Redirect Issue - Quick Fix

## Problem
WordPress redirects to `https://localhost:9000/` instead of the Codespaces domain after login.

## Root Cause
Docker Compose environment variables in `docker-compose.yml` hardcode `WP_HOME` and `WP_SITEURL` to `localhost:9000`, which overrides all other configuration including `wp-config-extra.php` and database values.

## Solution (Do This First!)

### Step 1: Remove Hardcoded URLs from docker-compose.yml

Edit `docker-compose.yml` and remove the `WORDPRESS_CONFIG_EXTRA` section from `wordpress-test`:

**BEFORE:**
```yaml
wordpress-test:
  environment:
    WORDPRESS_DB_HOST: db-test:3306
    WORDPRESS_DB_USER: wordpress
    WORDPRESS_DB_PASSWORD: wordpress
    WORDPRESS_DB_NAME: wpshadow_test
    WORDPRESS_DEBUG: 'true'
    WORDPRESS_DEBUG_DISPLAY: 'false'
    WORDPRESS_CONFIG_EXTRA: |
      define('WP_HOME', 'http://localhost:9000');
      define('WP_SITEURL', 'http://localhost:9000');
```

**AFTER:**
```yaml
wordpress-test:
  environment:
    WORDPRESS_DB_HOST: db-test:3306
    WORDPRESS_DB_USER: wordpress
    WORDPRESS_DB_PASSWORD: wordpress
    WORDPRESS_DB_NAME: wpshadow_test
    WORDPRESS_DEBUG: 'true'
    WORDPRESS_DEBUG_DISPLAY: 'false'
```

### Step 2: Update wp-config-extra.php

Edit `wp-config-extra.php` and force the correct server variables:

```php
// ============================================================
// GitHub Codespaces Port Forwarding Configuration
// ============================================================
// Force HTTPS and set the correct host

// Always force HTTPS for Codespaces
$_SERVER['HTTPS'] = 'on';

// Force the correct Codespaces hostname
$_SERVER['HTTP_HOST'] = 'YOUR-CODESPACE-NAME-9000.app.github.dev';
$_SERVER['SERVER_NAME'] = 'YOUR-CODESPACE-NAME-9000.app.github.dev';
$_SERVER['SERVER_PORT'] = '443';

// ============================================================
// WordPress Site URL Configuration
// ============================================================
define('WP_HOME', 'https://YOUR-CODESPACE-NAME-9000.app.github.dev');
define('WP_SITEURL', 'https://YOUR-CODESPACE-NAME-9000.app.github.dev');
```

**Replace `YOUR-CODESPACE-NAME-9000.app.github.dev` with your actual Codespaces domain!**

### Step 3: Update Database URLs

```bash
cd /workspaces/wpshadow

# Update database with correct URL
docker-compose exec db-test mysql -uwordpress -pwordpress wpshadow_test -e "UPDATE wp_options SET option_value='https://YOUR-CODESPACE-NAME-9000.app.github.dev' WHERE option_name IN ('siteurl', 'home')"

# Search-replace any remaining localhost references
docker-compose exec -T wordpress-test wp --allow-root search-replace "http://localhost:9000" "https://YOUR-CODESPACE-NAME-9000.app.github.dev" --all-tables
```

### Step 4: Add wp-config-extra.php Include to WordPress

The WordPress container needs to load `wp-config-extra.php`:

```bash
docker-compose exec -T wordpress-test bash -c "sed -i '/wp-settings.php/i\
// Load custom Codespaces configuration\
if (file_exists(__DIR__ . \"/wp-config-extra.php\")) {\
    require_once(__DIR__ . \"/wp-config-extra.php\");\
}\
' /var/www/html/wp-config.php"
```

### Step 5: Recreate Container

```bash
docker-compose up -d wordpress-test
```

### Step 6: Clear Sessions & Transients

```bash
docker-compose exec db-test mysql -uwordpress -pwordpress wpshadow_test -e "DELETE FROM wp_usermeta WHERE meta_key LIKE '%session%'; DELETE FROM wp_options WHERE option_name LIKE '%transient%';"
```

### Step 7: Restore Admin Capabilities (if needed)

If you deleted capabilities during troubleshooting:

```bash
docker-compose exec db-test mysql -uwordpress -pwordpress wpshadow_test -e "INSERT INTO wp_usermeta (user_id, meta_key, meta_value) VALUES (1, 'wp_capabilities', 'a:1:{s:13:\"administrator\";b:1;}'), (1, 'wp_user_level', '10');"
```

### Step 8: Clear Browser Cache

**IMPORTANT:** Use incognito/private window for first login after fix. Your browser has cached cookies with the old localhost URL.

## Verification

After all steps, check:

```bash
# Verify database URLs
docker-compose exec db-test mysql -uwordpress -pwordpress wpshadow_test -e "SELECT option_name, option_value FROM wp_options WHERE option_name IN ('siteurl', 'home')"

# Should show: https://YOUR-CODESPACE-NAME-9000.app.github.dev
```

## Quick Command Reference

```bash
# Get your Codespaces domain (from PORTS tab in VS Code)
# Format: https://UNIQUE-NAME-9000.app.github.dev/

# One-liner to update everything (replace YOUR-DOMAIN):
DOMAIN="YOUR-CODESPACE-NAME-9000.app.github.dev" && \
docker-compose exec db-test mysql -uwordpress -pwordpress wpshadow_test -e "UPDATE wp_options SET option_value='https://$DOMAIN' WHERE option_name IN ('siteurl', 'home')" && \
docker-compose restart wordpress-test
```

## Prevention

**NEVER** hardcode URLs in `WORDPRESS_CONFIG_EXTRA` environment variables. Let `wp-config-extra.php` handle URL configuration.

## The 443 vs 9000 Issue

If WordPress redirects to port 443 instead of 9000, it's because:
- Codespaces uses HTTPS proxy (443) in front of your container (9000)
- GitHub's proxy automatically handles the port translation
- Just manually change `443` to `9000` in the URL once
- This is expected behavior and doesn't break functionality

## Order of Precedence (What Overrides What)

1. **Docker environment `WORDPRESS_CONFIG_EXTRA`** (highest priority - THIS WAS THE PROBLEM!)
2. `wp-config-extra.php` constants (`WP_HOME`, `WP_SITEURL`)
3. Database `wp_options` table values
4. WordPress defaults (lowest priority)

**Solution:** Remove #1 so #2 can work properly.

## Test Site Credentials

After fix, log in at:
- URL: https://YOUR-CODESPACE-NAME-9000.app.github.dev/wp-admin
- Username: `testadmin`
- Password: `Test@Shadow2026!`
