# 🐳 WordPress Docker Testing Environment

Complete setup for testing WPShadow plugins and themes in a containerized WordPress environment.

## 🚀 Quick Start

### Option 1: Automated Setup (Recommended)
Run the setup script to automatically configure everything:

```bash
cd /workspaces/wpshadow
./docker-setup.sh
```

This will:
- ✅ Start all Docker containers
- ✅ Wait for WordPress to be ready
- ✅ Install WP-CLI
- ✅ Configure WordPress with admin credentials
- ✅ Activate WPShadow and WPShadow Pro plugins

### Option 2: Manual Setup

```bash
# Start containers
docker-compose up -d

# Wait for containers to be healthy (check status)
docker-compose ps

# Install WordPress (first time only)
docker exec wpshadow-dev wp core install \
  --url="http://localhost:8000" \
  --title="WPShadow Test" \
  --admin_user="admin" \
  --admin_password="admin" \
  --admin_email="admin@test.com" \
  --allow-root

# Activate plugins
docker exec wpshadow-dev wp plugin activate wpshadow --allow-root
docker exec wpshadow-dev wp plugin activate wpshadow-pro --allow-root
```

## 🌐 Access Points

| Service | URL | Credentials |
|---------|-----|-------------|
| **WordPress** | http://localhost:8000 | admin / admin |
| **phpMyAdmin** | http://localhost:8080 | wordpress / wordpress |
| **WP Admin** | http://localhost:8000/wp-admin | admin / admin |

## 📂 Container Structure

Your workspace is mounted into the WordPress container:

```
Container Path                              → Workspace Path
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
/var/www/html/wp-content/plugins/wpshadow     → /workspaces/wpshadow
/var/www/html/wp-content/plugins/wpshadow-pro → /workspaces/wpshadow-pro
/var/www/html/wp-content/themes/              → Docker volume (wp_themes)
```

**This means:**
- ✅ Edit files in VS Code → Changes instantly reflected in WordPress
- ✅ No need to rebuild containers after code changes
- ✅ Full debugging capabilities with `WORDPRESS_DEBUG` enabled

## 🔧 Essential Docker Commands

### Container Management
```bash
# Start containers
docker-compose up -d

# Stop containers (preserves data)
docker-compose stop

# Restart containers
docker-compose restart

# Stop and remove containers (preserves volumes)
docker-compose down

# Stop, remove containers AND delete all data
docker-compose down -v

# View running containers
docker-compose ps

# View container logs (follow mode)
docker-compose logs -f

# View logs for specific service
docker-compose logs -f wordpress
docker-compose logs -f mysql
```

### WordPress Shell Access
```bash
# Access WordPress container shell
docker exec -it wpshadow-dev bash

# Execute single command
docker exec wpshadow-dev ls -la /var/www/html/wp-content/plugins
```

## 🔨 WP-CLI Commands

WP-CLI is pre-installed for advanced WordPress management:

### Plugin Management
```bash
# List all plugins
docker exec wpshadow-dev wp plugin list --allow-root

# Activate/deactivate plugins
docker exec wpshadow-dev wp plugin activate wpshadow --allow-root
docker exec wpshadow-dev wp plugin deactivate wpshadow --allow-root

# Install and activate a plugin from WordPress.org
docker exec wpshadow-dev wp plugin install akismet --activate --allow-root

# Update plugins
docker exec wpshadow-dev wp plugin update --all --allow-root
```

### Theme Management
```bash
# List themes
docker exec wpshadow-dev wp theme list --allow-root

# Install and activate a theme
docker exec wpshadow-dev wp theme install twentytwentyfour --activate --allow-root

# Switch themes
docker exec wpshadow-dev wp theme activate twentytwentythree --allow-root
```

### Database Operations
```bash
# Export database
docker exec wpshadow-dev wp db export /var/www/html/backup.sql --allow-root

# Import database
docker exec wpshadow-dev wp db import /var/www/html/backup.sql --allow-root

# Database search and replace (useful when changing URLs)
docker exec wpshadow-dev wp search-replace 'oldurl.com' 'localhost:8000' --allow-root

# Reset database (WARNING: deletes all data)
docker exec wpshadow-dev wp db reset --yes --allow-root
```

### User Management
```bash
# Create new admin user
docker exec wpshadow-dev wp user create testuser test@example.com \
  --role=administrator \
  --user_pass=password123 \
  --allow-root

# List all users
docker exec wpshadow-dev wp user list --allow-root

# Update user password
docker exec wpshadow-dev wp user update admin --user_pass=newpassword --allow-root
```

### Content Management
```bash
# Create test posts
docker exec wpshadow-dev wp post generate --count=10 --allow-root

# Create test pages
docker exec wpshadow-dev wp post generate --count=5 --post_type=page --allow-root

# Delete all posts
docker exec wpshadow-dev wp post delete $(docker exec wpshadow-dev wp post list --post_type=post --format=ids --allow-root) --force --allow-root
```

### WordPress Core
```bash
# Check WordPress version
docker exec wpshadow-dev wp core version --allow-root

# Update WordPress
docker exec wpshadow-dev wp core update --allow-root

# Verify core files integrity
docker exec wpshadow-dev wp core verify-checksums --allow-root
```

### Cache and Transients
```bash
# Flush all caches
docker exec wpshadow-dev wp cache flush --allow-root

# Delete all transients
docker exec wpshadow-dev wp transient delete --all --allow-root

# Regenerate .htaccess
docker exec wpshadow-dev wp rewrite flush --allow-root
```

## 🧪 Testing Your Plugins

### Live Testing
1. Make changes to your plugin files in VS Code
2. Refresh your browser at http://localhost:8000
3. Changes are immediately reflected (no build step needed)

### Debug Logging
Debug mode is enabled. View logs:

```bash
# View debug log in real-time
docker exec wpshadow-dev tail -f /var/www/html/wp-content/debug.log

# View last 50 lines
docker exec wpshadow-dev tail -n 50 /var/www/html/wp-content/debug.log

# Clear debug log
docker exec wpshadow-dev bash -c "echo '' > /var/www/html/wp-content/debug.log"
```

### PHPUnit Tests (if configured)
```bash
# Run tests from workspace
cd /workspaces/wpshadow
composer test

# Or run inside container
docker exec wpshadow-dev vendor/bin/phpunit
```

## 🎨 Testing Themes

### Install a Theme for Testing
```bash
# Install from WordPress.org
docker exec wpshadow-dev wp theme install twentytwentyfour --activate --allow-root

# Or upload a custom theme
# 1. Copy theme to themes volume
docker cp /path/to/your-theme wpshadow-dev:/var/www/html/wp-content/themes/your-theme

# 2. Activate it
docker exec wpshadow-dev wp theme activate your-theme --allow-root
```

### Test with Multiple Themes
```bash
# Install several themes
docker exec wpshadow-dev wp theme install twentytwentyone --allow-root
docker exec wpshadow-dev wp theme install twentytwentytwo --allow-root
docker exec wpshadow-dev wp theme install twentytwentythree --allow-root

# Switch between them
docker exec wpshadow-dev wp theme activate twentytwentyone --allow-root
```

## 🗄️ Database Management

### Using phpMyAdmin
1. Open http://localhost:8080
2. Login with: `wordpress` / `wordpress`
3. Browse/edit database tables directly

### Using MySQL CLI
```bash
# Access MySQL shell
docker exec -it wpshadow-db mysql -u wordpress -pwordpress wordpress

# Run SQL query
docker exec wpshadow-db mysql -u wordpress -pwordpress wordpress \
  -e "SELECT * FROM wp_options WHERE option_name = 'siteurl';"

# Backup database to file
docker exec wpshadow-db mysqldump -u wordpress -pwordpress wordpress > backup.sql

# Restore database from file
cat backup.sql | docker exec -i wpshadow-db mysql -u wordpress -pwordpress wordpress
```

## 🔍 Troubleshooting

### Containers won't start
```bash
# Check Docker is running
docker info

# Check for port conflicts
lsof -i :8000
lsof -i :8080

# View detailed logs
docker-compose logs

# Remove and recreate containers
docker-compose down -v
docker-compose up -d
```

### Plugins not appearing
```bash
# Verify mounts
docker exec wpshadow-dev ls -la /var/www/html/wp-content/plugins

# Check plugin headers
docker exec wpshadow-dev wp plugin list --allow-root

# Check file permissions
docker exec wpshadow-dev ls -la /var/www/html/wp-content/plugins/wpshadow
```

### WordPress permissions errors
```bash
# Fix file ownership (if needed)
docker exec wpshadow-dev chown -R www-data:www-data /var/www/html/wp-content
```

### Can't access WordPress
```bash
# Check container health
docker-compose ps

# Check if WordPress is responding
curl -I http://localhost:8000

# Restart containers
docker-compose restart

# View real-time logs
docker-compose logs -f wordpress
```

### Database connection errors
```bash
# Check MySQL is running
docker-compose ps mysql

# Test database connection
docker exec wpshadow-dev wp db check --allow-root

# Restart database
docker-compose restart mysql
```

## 🧹 Cleanup & Reset

### Fresh WordPress Install
```bash
# Complete reset (deletes all data)
docker-compose down -v
./docker-setup.sh
```

### Keep data, restart containers
```bash
docker-compose restart
```

### Clear WordPress cache/transients
```bash
docker exec wpshadow-dev wp cache flush --allow-root
docker exec wpshadow-dev wp transient delete --all --allow-root
```

## 📝 Environment Variables

Customize WordPress in [docker-compose.yml](docker-compose.yml):

```yaml
environment:
  WORDPRESS_DEBUG: 'true'           # Enable debug mode
  WORDPRESS_DEBUG_LOG: 'true'       # Log to debug.log
  WORDPRESS_DEBUG_DISPLAY: 'false'  # Don't display errors on screen
  WP_MEMORY_LIMIT: '256M'           # Increase memory limit
  WP_MAX_MEMORY_LIMIT: '512M'       # Max memory for admin
```

## 🚦 Health Checks

Containers include health checks to ensure services are ready:

```bash
# Check health status
docker-compose ps

# Healthy containers show: Up (healthy)
# Unhealthy containers show: Up (unhealthy)
```

## 📚 Additional Resources

- [WP-CLI Commands](https://developer.wordpress.org/cli/commands/)
- [Docker Compose Docs](https://docs.docker.com/compose/)
- [WordPress Docker Image](https://hub.docker.com/_/wordpress)
- [MySQL Docker Image](https://hub.docker.com/_/mysql)

---

**Need help?** Run `./docker-setup.sh` for automated setup or check [TESTING.md](TESTING.md) for more testing guidance.
