# WPShadow Docker Development Environment

This Docker setup provides a complete development environment for both the WPShadow plugin and the WPShadow.com website.

## 🏗️ Architecture

### Two WordPress Instances:

1. **WPShadow.com Site** (Port 8080)
   - Main website with theme-wpshadow
   - Database: `wpshadow_site`
   - URL: http://localhost:8080

2. **Plugin Testing** (Port 9000)
   - Clean WordPress for plugin testing
   - Database: `wpshadow_test`
   - URL: http://localhost:9000

### Supporting Services:

3. **phpMyAdmin** (Port 8081)
   - Database management UI
   - URL: http://localhost:8081
   - Can access both databases

4. **MailHog** (Port 8025)
   - Email testing (catches all emails)
   - URL: http://localhost:8025
   - SMTP: localhost:1025

## 🚀 Quick Start

### 1. Start Everything:
```bash
docker-compose up -d
```

### 2. Access Sites:
- **Main Site:** http://localhost:8080
- **Test Site:** http://localhost:9000
- **phpMyAdmin:** http://localhost:8081
- **MailHog:** http://localhost:8025

### 3. WordPress Setup:
First time only - complete WordPress installation:

**Main Site (8080):**
```bash
# Open http://localhost:8080 and complete setup
# Recommended credentials:
#   Username: admin
#   Password: admin (change in production!)
#   Email: admin@wpshadow.local
```

**Test Site (9000):**
```bash
# Open http://localhost:9000 and complete setup
# Use different credentials to avoid confusion
```

## 📁 Volume Mounts

### Main Site (wpshadow-site):
- **Plugin:** `./` → `/var/www/html/wp-content/plugins/wpshadow`
- **Theme:** `../theme-wpshadow` → `/var/www/html/wp-content/themes/theme-wpshadow`
- **Config:** `./wp-config-extra.php` → `/var/www/html/wp-config-extra.php`

### Test Site (wpshadow-test):
- **Plugin:** `./` → `/var/www/html/wp-content/plugins/wpshadow`
- **Config:** `./wp-config-extra.php` → `/var/www/html/wp-config-extra.php`

## 🛠️ Common Commands

### Start/Stop Services:
```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# Stop and remove volumes (DESTRUCTIVE - deletes databases)
docker-compose down -v

# Restart a specific service
docker-compose restart wordpress-site
docker-compose restart wordpress-test
```

### View Logs:
```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f wordpress-site
docker-compose logs -f wordpress-test
docker-compose logs -f db-site
```

### Execute WP-CLI Commands:
```bash
# Main site
docker-compose exec wordpress-site wp --allow-root plugin list
docker-compose exec wordpress-site wp --allow-root theme activate theme-wpshadow

# Test site
docker-compose exec wordpress-test wp --allow-root plugin activate wpshadow
docker-compose exec wordpress-test wp --allow-root user create testuser test@example.com --role=administrator
```

### Database Access:
```bash
# Via phpMyAdmin: http://localhost:8081
# Server: db-site or db-test
# Username: wordpress
# Password: wordpress

# Via command line:
docker-compose exec db-site mysql -u wordpress -pwordpress wpshadow_site
docker-compose exec db-test mysql -u wordpress -pwordpress wpshadow_test
```

### Import/Export Database:
```bash
# Export main site database
docker-compose exec db-site mysqldump -u wordpress -pwordpress wpshadow_site > backup-site.sql

# Import to main site
docker-compose exec -T db-site mysql -u wordpress -pwordpress wpshadow_site < backup-site.sql

# Export test database
docker-compose exec db-test mysqldump -u wordpress -pwordpress wpshadow_test > backup-test.sql
```

## 🎨 Theme Development

The WPShadow.com theme is mounted at `../theme-wpshadow`.

### Initial Theme Setup:
```bash
# Create theme directory if it doesn't exist
mkdir -p /workspaces/theme-wpshadow

# Add your theme files there, or clone from repository:
cd /workspaces
git clone https://github.com/thisismyurl/theme-wpshadow.git

# Activate theme in main site
docker-compose exec wordpress-site wp --allow-root theme activate theme-wpshadow
```

### Theme Development Workflow:
1. Edit theme files in `/workspaces/theme-wpshadow`
2. Changes appear immediately (no rebuild needed)
3. Refresh browser to see changes
4. Use browser DevTools for CSS/JS debugging

## 🔌 Plugin Development

The WPShadow plugin is mounted in both WordPress instances.

### Activate Plugin:
```bash
# In main site
docker-compose exec wordpress-site wp --allow-root plugin activate wpshadow

# In test site
docker-compose exec wordpress-test wp --allow-root plugin activate wpshadow
```

### Plugin Development Workflow:
1. Edit plugin files in `/workspaces/wpshadow`
2. Changes appear immediately
3. Refresh browser or use WP-CLI to test

## 📧 Email Testing with MailHog

All emails sent from WordPress are caught by MailHog.

### Configure WordPress:
```php
// Already configured in wp-config-extra.php
// No additional setup needed
```

### View Emails:
- Open http://localhost:8025
- All emails appear here (registration, password reset, etc.)

### Send Test Email:
```bash
docker-compose exec wordpress-site wp --allow-root eval "wp_mail('test@example.com', 'Test Email', 'This is a test');"
```

## 🗄️ Database Management

### phpMyAdmin Access:
1. Open http://localhost:8081
2. Click "New Server"
3. Enter:
   - **Server:** `db-site` or `db-test`
   - **Username:** `wordpress`
   - **Password:** `wordpress`

### Direct MySQL Access:
```bash
# Main site database
docker-compose exec db-site mysql -u wordpress -pwordpress wpshadow_site

# Test database
docker-compose exec db-test mysql -u wordpress -pwordpress wpshadow_test

# Root access
docker-compose exec db-site mysql -u root -pwordpress
```

## 🧹 Cleanup & Reset

### Reset Main Site (keeps theme/plugin files):
```bash
docker-compose down
docker volume rm wpshadow_wp_site_data wpshadow_wp_site_db
docker-compose up -d wordpress-site db-site
# Complete WordPress setup again
```

### Reset Test Site:
```bash
docker-compose down
docker volume rm wpshadow_wp_test_data wpshadow_wp_test_db
docker-compose up -d wordpress-test db-test
# Complete WordPress setup again
```

### Complete Reset (DESTRUCTIVE):
```bash
docker-compose down -v
docker-compose up -d
# Setup both WordPress instances again
```

## 🐛 Troubleshooting

### Container won't start:
```bash
# Check logs
docker-compose logs wordpress-site
docker-compose logs db-site

# Common issue: Port already in use
# Solution: Stop other services or change port in docker-compose.yml
```

### Database connection errors:
```bash
# Wait for database to be ready
docker-compose logs db-site | grep "ready for connections"

# Restart WordPress after database is ready
docker-compose restart wordpress-site
```

### Theme not showing:
```bash
# Check if theme directory is mounted
docker-compose exec wordpress-site ls -la /var/www/html/wp-content/themes/

# Activate theme via WP-CLI
docker-compose exec wordpress-site wp --allow-root theme activate theme-wpshadow

# Check theme status
docker-compose exec wordpress-site wp --allow-root theme list
```

### Plugin not visible:
```bash
# Check if plugin is mounted
docker-compose exec wordpress-site ls -la /var/www/html/wp-content/plugins/wpshadow/

# Activate plugin
docker-compose exec wordpress-site wp --allow-root plugin activate wpshadow

# Check plugin status
docker-compose exec wordpress-site wp --allow-root plugin list
```

### Permission issues:
```bash
# Fix WordPress file permissions
docker-compose exec wordpress-site chown -R www-data:www-data /var/www/html
docker-compose exec wordpress-test chown -R www-data:www-data /var/www/html
```

## 📊 Performance Tips

### Enable Object Caching:
```bash
# Install Redis
docker-compose up -d redis

# Install Redis Object Cache plugin via WP-CLI
docker-compose exec wordpress-site wp --allow-root plugin install redis-cache --activate
docker-compose exec wordpress-site wp --allow-root redis enable
```

### Enable Debug Mode:
```php
// In wp-config-extra.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);
```

### View Debug Log:
```bash
# Main site
docker-compose exec wordpress-site tail -f /var/www/html/wp-content/debug.log

# Test site
docker-compose exec wordpress-test tail -f /var/www/html/wp-content/debug.log
```

## 🌐 Accessing from Outside Codespaces

If you're using GitHub Codespaces, ports are automatically forwarded:

1. Go to "Ports" tab in VS Code
2. Find ports 8080, 9000, 8081, 8025
3. Click globe icon to make public (or lock for private)
4. Use the forwarded URL

## 📝 Notes

- **Databases persist** across restarts (in Docker volumes)
- **WordPress files persist** (except core, which reinstalls)
- **Plugin/theme changes are immediate** (no container rebuild)
- **Use separate instances** to avoid conflicts during development
- **Main site (8080)** for production-like environment
- **Test site (9000)** for experimental features

## 🎯 Workflow Example

```bash
# 1. Start environment
docker-compose up -d

# 2. Setup main site (first time only)
open http://localhost:8080
# Complete WordPress installation
# Username: admin, Password: admin

# 3. Activate theme and plugin
docker-compose exec wordpress-site wp --allow-root theme activate theme-wpshadow
docker-compose exec wordpress-site wp --allow-root plugin activate wpshadow

# 4. Develop theme
cd /workspaces/theme-wpshadow
# Edit files, refresh browser

# 5. Develop plugin
cd /workspaces/wpshadow
# Edit files, test in both instances

# 6. Test emails
open http://localhost:8025

# 7. Manage databases
open http://localhost:8081

# 8. When done
docker-compose down
```

## 🔗 Quick Links

- **Main Site:** http://localhost:8080/wp-admin
- **Test Site:** http://localhost:9000/wp-admin
- **phpMyAdmin:** http://localhost:8081
- **MailHog:** http://localhost:8025

## 📚 Additional Resources

- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [WordPress Docker Images](https://hub.docker.com/_/wordpress)
- [WP-CLI Commands](https://developer.wordpress.org/cli/commands/)
- [MailHog Documentation](https://github.com/mailhog/MailHog)
