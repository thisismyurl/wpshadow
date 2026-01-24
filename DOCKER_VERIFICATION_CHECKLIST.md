# Docker Testing Environment - Verification Checklist

## Pre-Startup Checklist

Before starting the Docker environment, verify:

- [ ] Docker Desktop is running (local testing) OR you're in GitHub Codespaces
- [ ] Port 9000 is not currently in use: `lsof -i :9000`
- [ ] Port 3306 is not currently in use: `lsof -i :3306`
- [ ] At least 2GB free disk space: `df -h | grep -i tmp`
- [ ] `/workspaces/wpshadow` directory exists and contains wpshadow.php
- [ ] `/workspaces/wpshadow/docker-compose.yml` exists

## Startup Process

### Step 1: Start Services
```bash
cd /workspaces/wpshadow
docker-compose up -d
```

**Expected output:**
```
Creating wpshadow-mysql ... done
Creating wpshadow-wordpress ... done
```

- [ ] Both services created successfully
- [ ] No error messages in output

### Step 2: Wait for Readiness (2-3 minutes)

Monitor the services:
```bash
docker-compose logs -f
```

**MySQL is ready when you see:**
```
[System] [MY-015015] [Server] MySQL Server has started
```

**WordPress is ready when you see:**
```
[...]
AH00558: apache2: Could not reliably determine the server's fully qualified domain name
```

- [ ] MySQL initialized
- [ ] WordPress initialized
- [ ] No critical error messages

### Step 3: Access WordPress

Determine your URL:

**If in GitHub Codespaces:**
```
http://$CODESPACE_NAME-9000.$GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN
```

**If local (Docker Desktop):**
```
http://localhost:9000
```

Navigate to the URL in your browser.

- [ ] WordPress loads in browser
- [ ] No ERR_CONNECTION_REFUSED error
- [ ] No timeout errors
- [ ] WordPress installation screen appears (first time) or dashboard (subsequent)

### Step 4: Complete WordPress Setup (First Time Only)

If WordPress shows the installation wizard:

1. **Select Language**
   - [ ] Choose preferred language
   - [ ] Click "Continue"

2. **Welcome Screen**
   - [ ] Click "Let's go!"

3. **Database Configuration**
   - Database Name: `wordpress`
   - User: `wordpress`
   - Password: `wordpress`
   - Host: `mysql:3306`
   - [ ] All fields pre-filled (should auto-detect from docker-compose)
   - [ ] Click "Submit"

4. **Run the installation**
   - [ ] Click "Run the installation"

5. **Site Information**
   - Site Title: `wpshadow Development` (or your preference)
   - Username: `admin` (or your preference)
   - Password: Create a strong password
   - Email: Your email address
   - [ ] Check "Search engine visibility"
   - [ ] Click "Install WordPress"

6. **Success**
   - [ ] See "Success!" message
   - [ ] Click "Log in"
   - [ ] Log in with your credentials

## Post-Setup Verification

### Check URL Configuration

The WordPress installation should be using the correct URL (not localhost).

**In WordPress admin:**
1. Go to **Settings** → **General**
2. Check these values:

**WordPress Address (URL):**
- Codespaces: Should show `http://$CODESPACE_NAME-9000.$GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN`
- Local: Should show `http://localhost:9000` or similar
- [ ] NOT showing localhost:80 or localhost:8000
- [ ] Port 9000 is present in the URL

**Site Address (URL):**
- [ ] Should match WordPress Address (URL)
- [ ] Both using HTTP (not HTTPS unless explicitly configured)

### Check Plugin Mount

1. Go to **Plugins** → **Installed Plugins**
2. Look for **wpshadow** in the list

- [ ] wpshadow plugin appears in plugins list
- [ ] Status shows "Inactive"
- [ ] Version number is displayed
- [ ] Description shows "WordPress Security & Diagnostics Plugin"

### Check MySQL Connection

In WordPress admin:
1. Look at the bottom footer
2. You should see database information (optional, depending on WordPress setup)

**Alternative check via command line:**
```bash
docker exec wpshadow-wordpress wp option get siteurl --allow-root
```

Expected output:
```
http://localhost:9000
# or
http://$CODESPACE_NAME-9000.$GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN
```

- [ ] Command executed successfully
- [ ] Output shows correct URL
- [ ] No "Error" messages

### Activate wpshadow Plugin

1. Go to **Plugins** → **Installed Plugins**
2. Find **wpshadow**
3. Click **Activate**

**Expected result:**
- [ ] No error message after activation
- [ ] Plugin shows "Active" status
- [ ] No fatal PHP errors in browser

**Check error logs:**
```bash
docker-compose logs wordpress | grep -i error
```

- [ ] No PHP fatal errors
- [ ] No "undefined variable" errors
- [ ] No "undefined function" errors

### Verify Diagnostics Dashboard (if menu added)

If wpshadow adds a menu item:

1. Look for **wpshadow** or **Diagnostics** in left WordPress menu
2. Click to access the diagnostics interface

**Expected result:**
- [ ] Dashboard loads without errors
- [ ] Can see diagnostic categories
- [ ] Can view list of available diagnostics
- [ ] Can trigger a single diagnostic run
- [ ] No JavaScript errors in browser console

### Port Verification

Verify that port 9000 is exclusively used:

```bash
# Check port mapping
docker-compose ps | grep "9000"
# Should show: 0.0.0.0:9000->80/tcp

# Check no other services on 9000
docker ps | grep -v wpshadow-wordpress | grep 9000
# Should return empty (nothing)

# Verify connectivity
curl -I http://localhost:9000
# Should return HTTP response, not connection refused
```

- [ ] Port 9000 correctly mapped in docker-compose ps
- [ ] No other services using port 9000
- [ ] curl returns HTTP response
- [ ] URL accessible in browser

## Troubleshooting During Verification

### "ERR_CONNECTION_REFUSED" in browser

```bash
# Check if WordPress container is running
docker-compose ps wordpress

# Should show status "Up"
# If exited, check logs
docker-compose logs wordpress | tail -50
```

**Likely causes:**
- WordPress still initializing (wait 1-2 more minutes)
- Port 9000 not forwarded correctly
- WordPress container crashed (check logs)

### "Can't connect to MySQL server"

```bash
# Check MySQL container
docker-compose ps mysql

# Should show status "Up"

# Test MySQL connection
docker exec wpshadow-wordpress mysql -h mysql -u wordpress -pwordpress -e "SELECT 1;" wordpress
```

**Likely causes:**
- MySQL still initializing
- Network connectivity between containers
- Wrong credentials in docker-compose

### WordPress showing wrong URL (localhost instead of codespace domain)

```bash
# Check environment variables
docker-compose config | grep -A 2 "WP_HOME\|WP_SITEURL"

# Check WordPress config in container
docker exec wpshadow-wordpress grep "WP_HOME\|WP_SITEURL" /var/www/html/wp-config.php
```

**Fix for Codespaces:**
- Ensure `CODESPACE_NAME` and `GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN` environment variables are set
- Redeploy with `docker-compose restart wordpress`

### Plugin doesn't appear in plugins list

```bash
# Check if plugin is mounted
docker exec wpshadow-wordpress ls -la /var/www/html/wp-content/plugins/

# Should show 'wpshadow' directory
docker exec wpshadow-wordpress ls -la /var/www/html/wp-content/plugins/wpshadow/

# Should show 'wpshadow.php'

# Check file permissions
docker exec wpshadow-wordpress stat /var/www/html/wp-content/plugins/wpshadow/wpshadow.php
```

**Likely causes:**
- Volume mount not configured correctly
- File permissions issue
- Plugin directory not created

## Performance Metrics

After successful startup, check these metrics:

```bash
# Docker resource usage
docker stats wpshadow-wordpress wpshadow-mysql

# MySQL performance
docker exec wpshadow-mysql mysqladmin -u wordpress -pwordpress ping
```

- [ ] WordPress container: <200MB RAM
- [ ] MySQL container: <100MB RAM
- [ ] Both containers showing steady CPU usage (not spiking)

## Successful Verification Summary

You have successfully set up the Docker testing environment when ALL of these are true:

- ✅ Both MySQL and WordPress containers running
- ✅ WordPress accessible at correct URL (not localhost)
- ✅ Port 9000 is exclusively used
- ✅ WordPress admin dashboard loads
- ✅ wpshadow plugin appears in Plugins list
- ✅ Plugin can be activated without errors
- ✅ Database connection working
- ✅ No PHP fatal errors in logs
- ✅ Correct URL in WordPress settings

## Next Steps

Once verified:

1. **Run Diagnostics**: Test the 648 available diagnostics
2. **Test Features**: Verify key plugin functionality
3. **Check Performance**: Monitor resource usage during testing
4. **Review Logs**: Check error log for any warnings
5. **Commit Your Work**: Save progress to Git

## Quick Start Shortcuts

Once environment is verified once, these commands are all you need:

```bash
# Start environment
docker-compose up -d

# View status
docker-compose ps

# View logs
docker-compose logs -f

# Stop environment
docker-compose down

# Full restart
docker-compose restart
```

## Getting Help

- See [DOCKER_TESTING_SETUP.md](../DOCKER_TESTING_SETUP.md) for comprehensive troubleshooting
- See [DOCKER_QUICKSTART.md](../DOCKER_QUICKSTART.md) for quick reference
- Check docker-compose.yml for configuration details
- Review .devcontainer/devcontainer.json for Codespaces settings

---

**Last Updated**: 2024
**Version**: 1.0
**Status**: Ready for Testing
