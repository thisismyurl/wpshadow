# WPShadow Testing Environment Setup Guide

## Overview

This document describes the complete setup for a WordPress testing environment running in Docker on GitHub Codespaces. This environment allows you to test the WPShadow plugin against a live WordPress installation with a real database.

**Current Setup:**
- WordPress 6.9 (latest)
- MySQL 8.0
- Port: 9000
- Access: `https://stunning-fishstick-j69p5j559jqcpw79-9000.app.github.dev/`
- Admin: `admin` / `admin123`

## Quick Start (If Everything Already Works)

```bash
cd /workspaces/wpshadow
docker-compose -f docker-compose-test.yml up -d
# Wait 15 seconds for MySQL to initialize
sleep 15
# Access via VS Code PORTS tab -> Port 9000
```

## Complete Setup from Scratch

### Step 1: Create docker-compose-test.yml

This is the core configuration file. Create it at `/workspaces/wpshadow/docker-compose-test.yml`:

```yaml
version: '3.8'

services:
  wordpress:
    image: wordpress:latest
    container_name: wpshadow-test-wordpress
    ports:
      - "9000:80"
    environment:
      WORDPRESS_DB_HOST: db:3306
      WORDPRESS_DB_USER: wordpress
      WORDPRESS_DB_PASSWORD: wordpress
      WORDPRESS_DB_NAME: wordpress
      WORDPRESS_DEBUG: 'true'
      WORDPRESS_DEBUG_DISPLAY: 'false'
    volumes:
      - ./:/var/www/html/wp-content/plugins/wpshadow
      - ./wp-config-extra.php:/var/www/html/wp-config-extra.php
      - wp_test_data:/var/www/html
    depends_on:
      db:
        condition: service_healthy

  db:
    image: mysql:8.0
    container_name: wpshadow-test-db
    environment:
      MYSQL_DATABASE: wordpress
      MYSQL_ROOT_PASSWORD: wordpress
      MYSQL_USER: wordpress
      MYSQL_PASSWORD: wordpress
    volumes:
      - wp_test_db:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 5s
      timeout: 3s
      retries: 5

volumes:
  wp_test_data:
  wp_test_db:
```

**Key Points:**
- Port 9000 is used (not 8888, which may have caching issues)
- Plugin is mounted at `/var/www/html/wp-content/plugins/wpshadow` (live code changes)
- `wp-config-extra.php` is mounted to override WordPress configuration
- MySQL has a healthcheck to ensure it's ready before WordPress starts

### Step 2: Create wp-config-extra.php

This file handles GitHub Codespaces port forwarding and URL configuration. Create it at `/workspaces/wpshadow/wp-config-extra.php`:

```php
<?php
// Handle GitHub Codespaces port forwarding
// The proxy sends HTTPS traffic to port 80, so we need to preserve the original port
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    $_SERVER['HTTPS'] = ($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'on' : 'off';
}

// Preserve the original port from the forwarded host header
if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
}

// Set the correct site URL for GitHub Codespaces (with explicit port)
define('WP_HOME', 'https://stunning-fishstick-j69p5j559jqcpw79-9000.app.github.dev');
define('WP_SITEURL', 'https://stunning-fishstick-j69p5j559jqcpw79-9000.app.github.dev');
```

**⚠️ CRITICAL:** Replace `stunning-fishstick-j69p5j559jqcpw79` with YOUR Codespaces domain name. You can find this in:
- VS Code: Look at the port forwarding URL in the PORTS tab
- Terminal: Check the CODESPACE_NAME environment variable

### Step 3: Start Docker Containers

```bash
cd /workspaces/wpshadow
docker-compose -f docker-compose-test.yml down --volumes  # Clean start (optional)
docker-compose -f docker-compose-test.yml up -d
sleep 15  # Wait for MySQL to initialize
```

**Expected Output:**
```
✔ Network wpshadow_default Created
✔ Volume wpshadow_wp_test_db Created
✔ Volume wpshadow_wp_test_data Created
✔ Container wpshadow-test-db Healthy (after ~6 seconds)
✔ Container wpshadow-test-wordpress Started (after ~6 seconds)
```

### Step 4: Verify Database Connection

Test that WordPress can connect to MySQL:

```bash
docker exec wpshadow-test-wordpress php -r "
\$mysqli = mysqli_connect('db', 'wordpress', 'wordpress', 'wordpress');
if (\$mysqli) {
  echo 'Connected successfully';
} else {
  echo 'Connection failed: ' . mysqli_connect_error();
}
"
```

**Expected Output:** `Connected successfully`

If this fails, the containers may not be on the same network. Check with:
```bash
docker network inspect wpshadow_default
```

### Step 5: Install WordPress

Complete the WordPress installation with two HTTP POST requests:

**Step 1 - Language selection:**
```bash
curl -s -X POST "http://localhost:9000/wp-admin/install.php?step=1" \
  -d "language=en_US" > /dev/null
```

**Step 2 - Site and admin setup:**
```bash
curl -s -X POST "http://localhost:9000/wp-admin/install.php?step=2" \
  -d "weblog_title=WPShadow+Test&user_name=admin&admin_email=admin@test.com&admin_password=admin123&admin_password2=admin123&pw_weak=on&submit=Install+WordPress" > /dev/null
```

**⚠️ CRITICAL:** Use `user_name` (not `user_login`) - older WordPress versions used different field names.

**Verify Installation:**
```bash
docker exec wpshadow-test-db mysql -u wordpress -pwordpress wordpress \
  -e "SELECT COUNT(*) FROM wp_users;"
```

Expected output: `1` (the admin user)

### Step 6: Update Database URLs (for GitHub Codespaces)

The database stores the site URL. Update it to match your Codespaces domain:

```bash
docker exec wpshadow-test-db mysql -u wordpress -pwordpress wordpress \
  -e "UPDATE wp_options SET option_value='https://YOUR-CODESPACE-DOMAIN-9000.app.github.dev' WHERE option_name IN ('siteurl', 'home');"
```

Replace `YOUR-CODESPACE-DOMAIN` with your actual Codespaces domain.

### Step 7: Add wp-config-extra.php to WordPress Config

WordPress generates its own `wp-config.php`. We need to inject a require statement for our `wp-config-extra.php`. This should happen automatically via the volume mount, but if it doesn't, run:

```bash
docker exec wpshadow-test-wordpress bash -c "
if ! grep -q 'wp-config-extra.php' /var/www/html/wp-config.php; then
  echo '' >> /var/www/html/wp-config.php
  echo '// Include extra configuration for Codespaces' >> /var/www/html/wp-config.php
  echo 'if (file_exists(\"/var/www/html/wp-config-extra.php\")) {' >> /var/www/html/wp-config.php
  echo '  require_once(\"/var/www/html/wp-config-extra.php\");' >> /var/www/html/wp-config.php
  echo '}' >> /var/www/html/wp-config.php
fi
"
```

Then restart:
```bash
docker restart wpshadow-test-wordpress
sleep 3
```

### Step 8: Verify Everything Works

```bash
curl -s http://localhost:9000/ | grep -o "<title>[^<]*</title>"
```

**Expected Output:** `<title>WPShadow Test</title>`

## Accessing WordPress

### Method 1: VS Code PORTS Tab (Recommended)

1. Open VS Code Command Palette: `Cmd+Shift+P`
2. Search for: "Ports: Focus on Ports View"
3. Find "Port 9000"
4. Click the globe icon to open in browser
5. The URL will be something like: `https://stunning-fishstick-j69p5j559jqcpw79-9000.app.github.dev`

### Method 2: Direct Access

The port is publicly accessible at:
```
https://stunning-fishstick-j69p5j559jqcpw79-9000.app.github.dev/
```

(Replace the domain with your Codespaces domain)

### Login Credentials

- **URL:** `https://YOUR-DOMAIN-9000.app.github.dev/wp-login.php`
- **Username:** `admin`
- **Password:** `admin123`

### Plugin Testing

After login:
1. Navigate to **Plugins** in the admin sidebar
2. Find **WPShadow**
3. Click **Activate**
4. Test plugin functionality

**Live Code Changes:** Any changes to `/workspaces/wpshadow` files automatically appear in the WordPress installation because the directory is mounted as a volume.

## Troubleshooting

### Issue: "Connection refused" when accessing WordPress

**Cause:** Database isn't ready yet or containers aren't communicating.

**Fix:**
```bash
# Check container status
docker-compose -f docker-compose-test.yml ps

# Check MySQL is healthy
docker exec wpshadow-test-db mysqladmin ping -h localhost

# View logs
docker logs wpshadow-test-wordpress
docker logs wpshadow-test-db
```

### Issue: Constant redirection loops or wrong port

**Cause:** `wp-config-extra.php` URLs or database URLs don't match the Codespaces domain.

**Fix:**
1. Update `wp-config-extra.php` with correct domain
2. Update database URLs:
   ```bash
   docker exec wpshadow-test-db mysql -u wordpress -pwordpress wordpress \
     -e "SELECT option_name, option_value FROM wp_options WHERE option_name IN ('siteurl', 'home');"
   ```
3. Restart WordPress:
   ```bash
   docker restart wpshadow-test-wordpress
   ```

### Issue: WordPress installation page still shows after setup

**Cause:** Database tables weren't created.

**Fix:**
1. Check if tables exist:
   ```bash
   docker exec wpshadow-test-db mysql -u wordpress -pwordpress wordpress \
     -e "SHOW TABLES;" | head -5
   ```
2. If empty, re-run installation steps 1-2 above
3. Verify user was created:
   ```bash
   docker exec wpshadow-test-db mysql -u wordpress -pwordpress wordpress \
     -e "SELECT user_login FROM wp_users;"
   ```

### Issue: PHP warnings about undefined constants

**Cause:** Constants are defined multiple times in different config files.

**Fix:** Check `wp-config-extra.php` doesn't duplicate constants already in `wp-config.php`. Keep only unique configurations in wp-config-extra.php.

### Issue: "Cannot modify header information" errors

**Cause:** PHP output before headers are sent, usually from duplicate config definitions.

**Fix:** 
1. Ensure `wp-config-extra.php` has no output (no echo/print statements)
2. Remove duplicate `define()` statements
3. Restart WordPress:
   ```bash
   docker restart wpshadow-test-wordpress
   ```

## Reset / Clean Up

### Soft Reset (Keep data)
```bash
docker-compose -f docker-compose-test.yml restart
```

### Hard Reset (Delete all data)
```bash
cd /workspaces/wpshadow
docker-compose -f docker-compose-test.yml down --volumes
# Then run Step 3-7 above to rebuild
```

### Remove Everything
```bash
docker-compose -f docker-compose-test.yml down --volumes --remove-orphans
docker ps -a | grep wpshadow | awk '{print $1}' | xargs docker rm -f
docker network prune -f
docker volume prune -f
```

## Performance Tips

1. **Live Code Changes:** Edits to PHP files in `/workspaces/wpshadow` appear instantly in WordPress (no rebuild needed)
2. **Database:** MySQL data persists in `wp_test_db` volume, so you don't need to reinstall WordPress
3. **Port 9000:** Use this port, not 8888 (may have caching issues in Codespaces)

## Reference: Directory Structure

```
/workspaces/wpshadow/
├── docker-compose-test.yml          # Docker services config
├── wp-config-extra.php              # WordPress URL config override
├── wpshadow.php                     # Main plugin file
├── assets/                          # Plugin assets (live mounted)
├── includes/                        # Plugin code (live mounted)
├── features/                        # Plugin features (live mounted)
└── TESTING_SETUP.md                 # This file
```

## What Happens Behind the Scenes

1. **docker-compose-test.yml** starts two containers: WordPress and MySQL
2. **MySQL** initializes the database with WordPress tables (first run only)
3. **WordPress** container mounts the plugin directory as a volume, allowing live code changes
4. **wp-config-extra.php** handles GitHub Codespaces port forwarding and sets correct URLs
5. **Port 9000** is forwarded through Codespaces, accessible via the PORTS tab in VS Code

## Next Steps After Setup

- Activate WPShadow plugin
- Test plugin features
- Monitor `/var/www/html/wp-content/debug.log` for errors:
  ```bash
  docker exec wpshadow-test-wordpress tail -f /var/www/html/wp-content/debug.log
  ```
- Make code changes in the `wpshadow/` directory and refresh WordPress to see updates

---

**Last Updated:** January 18, 2026
**Tested with:** WordPress 6.9, MySQL 8.0, Docker Compose
