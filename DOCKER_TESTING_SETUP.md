# WShadow Docker Testing Environment Setup

## Overview

This guide helps you set up the Docker testing environment for wpshadow plugin development and testing. The setup uses Docker Compose to orchestrate MySQL and WordPress services.

## Prerequisites

- GitHub Codespaces (or Docker Desktop for local testing)
- Docker and Docker Compose installed
- At least 2GB available disk space
- Port 9000 available

## Automatic Setup (GitHub Codespaces)

When you open this workspace in GitHub Codespaces, the environment automatically:

1. **Post-Create Phase** (`post-create.sh`):
   - Waits for Docker services to be ready
   - Installs Composer dependencies
   - Creates plugin directory structure
   - Verifies wpshadow plugin mount

2. **Post-Start Phase** (`post-start.sh`):
   - Verifies Docker daemon status
   - Checks MySQL container health
   - Confirms WordPress container running
   - Validates port 9000 availability
   - Displays connection information

## Manual Setup

If automatic setup doesn't run, execute manually:

```bash
# Start services
cd /workspaces
docker-compose up -d

# Run verification script
./docker-startup.sh

# View status
docker-compose ps

# Check logs
docker-compose logs -f wordpress
```

## Service Configuration

### MySQL (Port 3306)
```
Service: wpshadow-mysql
Container: wpshadow-mysql
Database: wordpress
User: wordpress
Password: wordpress
Version: 8.0
```

### WordPress (Port 9000)
```
Service: wpshadow-wordpress
Container: wpshadow-wordpress
URL: http://$CODESPACE_NAME-9000.$GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN
Port: 9000 (mapped to container port 80)
PHP Version: 8.2
Web Server: Apache
Volume Mount: /workspaces/wpshadow → /var/www/html/wp-content/plugins/wpshadow
```

## Important: Full URL vs Localhost

The WordPress configuration uses environment variables to ensure full domain URLs work in Codespaces:

```php
// In docker-compose.yml WORDPRESS_CONFIG_EXTRA:
define('WP_HOME', 'http://' . getenv('CODESPACE_NAME') . '-9000.' . getenv('GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN'));
define('WP_SITEURL', 'http://' . getenv('CODESPACE_NAME') . '-9000.' . getenv('GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN'));
```

**This ensures:**
- ✅ Full Codespaces domain is used (not localhost)
- ✅ Port 9000 is explicitly configured
- ✅ No redirects to other ports
- ✅ Proper cookie handling for authentication

## First-Time Setup Steps

### 1. Wait for Services to Be Ready

```bash
# Monitor service health
docker-compose logs -f
```

Expected healthy state:
```
wordpress_1  | WordPress not found in /var/www/html - copying now...
wordpress_1  | WordPress installed!
...
mysql_1      | [System] [MY-015015] [Server] MySQL Server has started
```

### 2. Access WordPress

Navigate to your Codespaces URL (format shown in `post-start.sh` output):
```
http://$CODESPACE_NAME-9000.$GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN
```

### 3. Complete WordPress Setup

1. Choose language
2. Set site title: "wpshadow Development"
3. Create admin account (e.g., admin/password123)
4. Confirm installation

### 4. Verify Plugin Mount

In WordPress admin:
1. Go to **Plugins** → **Installed Plugins**
2. Look for **wpshadow** plugin
3. Should show version and description

### 5. Test Plugin Activation

1. Click **Activate** on wpshadow plugin
2. Check for error messages
3. Navigate to **Tools** → **wpshadow** (if main menu added)

## Verification Checklist

After setup completes, verify these items:

- [ ] WordPress loads without errors
- [ ] MySQL is connected (check admin dashboard)
- [ ] wpshadow plugin appears in Plugins list
- [ ] Plugin can be activated without errors
- [ ] No error_log entries about localhost redirects
- [ ] Port 9000 is exclusive (no 80/443 redirects)
- [ ] Can access WordPress admin panel
- [ ] Can view plugin diagnostics dashboard

## Port 9000 Verification

Confirm port 9000 is correctly mapped and exclusive:

```bash
# Check port mapping
docker-compose ps
# Should show: 0.0.0.0:9000->80/tcp

# Verify no other services on 9000
docker ps | grep -v wpshadow-wordpress | grep 9000 || echo "✓ No other services on 9000"

# Test connectivity
curl -I http://localhost:9000 2>&1 | head -5
# Should show HTTP/1.1 200 or 302 (redirect to WordPress setup)
```

## Troubleshooting

### Services Won't Start

```bash
# Check for port conflicts
lsof -i :3306 -i :9000 || echo "✓ Ports free"

# Clean up containers and restart
docker-compose down
docker-compose up -d

# View detailed logs
docker-compose logs mysql wordpress
```

### WordPress Shows Localhost URLs

```bash
# Check environment variables
docker-compose config | grep -A 5 "WP_HOME\|WP_SITEURL"

# If showing localhost, the environment variables aren't set
# In Codespaces, CODESPACE_NAME and GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN 
# are automatically available

# For local testing, set manually:
export CODESPACE_NAME=mylocal
export GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN=localhost
docker-compose up -d
```

### Plugin Not Mounting

```bash
# Verify volume mount
docker exec wpshadow-wordpress ls -la /var/www/html/wp-content/plugins/

# Should show wpshadow directory with wpshadow.php inside
# If missing, check:
# 1. /workspaces/wpshadow exists
# 2. /workspaces/wpshadow/wpshadow.php exists
# 3. No permission issues
```

### MySQL Connection Issues

```bash
# Check MySQL service logs
docker-compose logs mysql

# Test MySQL connection
docker exec wpshadow-mysql mysql -u wordpress -pwordpress -e "SELECT 1;" wordpress

# If fails, check credentials in docker-compose.yml
```

## Common Tasks

### View WordPress Error Log

```bash
docker exec wpshadow-wordpress tail -f /var/www/html/wp-content/debug.log
```

### Access MySQL CLI

```bash
docker exec -it wpshadow-mysql mysql -u wordpress -pwordpress wordpress
```

### Restart Services

```bash
docker-compose restart

# Or restart specific service
docker-compose restart wordpress
docker-compose restart mysql
```

### Stop Services

```bash
docker-compose down
# Data is preserved in Docker volumes
```

### Clear Everything (Fresh Start)

```bash
docker-compose down -v
# WARNING: This deletes database and volumes
```

### View All Logs

```bash
# All services
docker-compose logs

# Specific service
docker-compose logs wordpress

# Follow in real-time
docker-compose logs -f

# Last 100 lines
docker-compose logs --tail=100
```

## Testing Workflow

### Standard Test Cycle

1. **Start Services**
   ```bash
   docker-compose up -d
   ```

2. **Make Code Changes**
   - Edit PHP files in `/workspaces/wpshadow/`
   - Files automatically sync to container

3. **Test in Browser**
   - Navigate to WordPress admin
   - Activate/deactivate plugin
   - Run diagnostics

4. **Check Logs**
   ```bash
   docker-compose logs wordpress | grep -i error
   ```

5. **Commit Changes**
   ```bash
   cd /workspaces/wpshadow
   git add .
   git commit -m "Your message"
   git push
   ```

### Parallel Testing (Multiple Codespaces)

You can run multiple codespaces with different port mappings:

```bash
# Codespace 1: Uses port 9000 (default)
docker-compose up -d

# Codespace 2: Modify docker-compose.yml for different port
# Change ports section: 9001:80
docker-compose up -d
```

## Performance Notes

- **First run**: May take 2-3 minutes for WordPress to initialize
- **Volume sync**: Local code changes appear in container within 1-2 seconds
- **Database**: MySQL needs ~10 seconds to fully initialize
- **WordPress**: Displays setup page within 30 seconds of MySQL ready

## Security Notes

### Development Only

These credentials and settings are **for development only**:
- Default password: wordpress
- Debugging enabled
- No HTTPS
- Public database access

**Never use these settings in production.**

### For Production Testing

If you need production-like setup:
1. Disable debugging (remove WP_DEBUG)
2. Use strong random passwords
3. Add HTTPS/SSL configuration
4. Restrict database access
5. Disable debug.log display

## Next Steps

After verification:

1. **Run diagnostics**: Test all 648 wpshadow diagnostics
2. **Check performance**: Monitor Docker resource usage
3. **Test modules**: If using pro modules, verify loading
4. **Integration testing**: Test with other plugins

## Additional Resources

- [Docker Compose Docs](https://docs.docker.com/compose/)
- [WordPress Docker Hub](https://hub.docker.com/_/wordpress)
- [wpshadow Plugin Docs](../README.md)
- [Architecture Guide](../docs/ARCHITECTURE.md)

## Support

For issues or questions:
1. Check troubleshooting section above
2. Review docker-compose.yml configuration
3. Check devcontainer.json settings
4. View comprehensive logs: `docker-compose logs`
