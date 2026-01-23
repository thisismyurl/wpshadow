# WPShadow Docker Testing Environment

## Quick Reference

**WordPress Test Site:**
- **Browser Access:** https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/wp-admin/
- **Internal Testing:** http://localhost:9000 (for curl/docker commands only)
- Admin Username: `admin`
- Admin Password: `admin`

⚠️ **IMPORTANT:** The localhost URL only works for curl/docker exec testing. Browser access requires the GitHub Codespaces URL with OAuth authentication.

## Docker Containers

```bash
# List running containers
docker ps | grep -E "(wordpress|mysql)"

# Container names:
# - wpshadow-test (WordPress on port 9000)
# - wpshadow-test-db (MySQL database)
# - wpshadow-site (WordPress on port 8080)
# - wpshadow-site-db (MySQL database)
```

## Testing Commands

### Check if site is working (internal testing only)
```bash
# This only works for curl/docker testing - NOT browser access
curl -s "http://localhost:9000/wp-admin/admin.php?page=wpshadow" | grep -i "critical error" || echo "✅ No errors"

# For browser testing, use:
# https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/wp-admin/admin.php?page=wpshadow
```

### Test with WP-CLI (in container)
```bash
docker exec wpshadow-test bash -c 'cd /var/www/html && php -r "define(\"WP_USE_THEMES\", false); require(\"./wp-load.php\"); echo \"WordPress loaded OK\n\";"'
```

### Check plugin files in container
```bash
docker exec wpshadow-test ls -la /var/www/html/wp-content/plugins/wpshadow/
```

### Restart container (to pick up file changes)
```bash
docker restart wpshadow-test && sleep 5
```

## File Locations

### Host (Development)
- Plugin files: `/workspaces/wpshadow/`
- Changes here are synced to container via volume mount

### Container (WordPress)
- WordPress root: `/var/www/html/`
- Plugin path: `/var/www/html/wp-content/plugins/wpshadow/`

## Testing WPShadow Pages

### Test specific admin page
```bash
# Using localhost
curl -s "http://localhost:9000/wp-admin/admin.php?page=wpshadow" | head -50

# Follow redirects
curl -L -s "http://localhost:9000/wp-admin/admin.php?page=wpshadow" | grep -E "<title>"
```

### Test plugin activation
```bash
docker exec wpshadow-test bash -c 'cd /var/www/html && php -r "require(\"./wp-load.php\"); echo is_plugin_active(\"wpshadow/wpshadow.php\") ? \"Active\" : \"Inactive\";"'
```

### Check for PHP errors
```bash
docker exec wpshadow-test bash -c 'cd /var/www/html && php -r "define(\"WP_USE_THEMES\", false); require(\"./wp-load.php\"); do_action(\"admin_init\");"' 2>&1 | grep -i "fatal\|error"
```

## Debugging

### View container logs
```bash
docker logs wpshadow-test --tail 50
```

### Access container shell
```bash
docker exec -it wpshadow-test bash
```

### Check PHP syntax
```bash
php -l /workspaces/wpshadow/path/to/file.php
```

### Verify file sync to container
```bash
# Check a specific file is updated in container
docker exec wpshadow-test cat /var/www/html/wp-content/plugins/wpshadow/wpshadow.php | head -20
```

## Common Issues

### Issue: Changes not reflected in browser
**Solution:** Restart the container
```bash
docker restart wpshadow-test
```

### Issue: File changes not syncing
**Cause:** Volume mount issue
**Check:** 
```bash
docker inspect wpshadow-test | grep -A 10 "Mounts"
```

### Issue: Critical error on page load
**Debug Steps:**
1. Check PHP syntax: `php -l file.php`
2. Check container logs: `docker logs wpshadow-test --tail 50`
3. Test WordPress loading: See "Check for PHP errors" above
4. Check namespace issues: Look for missing `\` before global functions

## WordPress Configuration

### Database Access
- Host: `wpshadow-test-db` (from container) or `localhost` (from host via port mapping if exposed)
- Database: `wordpress`
- User: `wordpress`
- Password: `wordpress` (check docker-compose.yml for actual values)

### wp-config.php location
```bash
docker exec wpshadow-test cat /var/www/html/wp-config.php | grep DB_NAME
```

## 🚨 CRITICAL ERROR PROTOCOL (MANDATORY)

**When ANY critical error is reported:**

1. **NEVER stop at first fix** - Continue testing until error is completely cleared
2. **Test after EVERY change** - Don't assume the fix worked
3. **Use progressive debugging:**
   ```bash
   # Step 1: Check PHP syntax
   php -l path/to/changed-file.php
   
   # Step 2: Test WordPress bootstrap
   docker exec wpshadow-test bash -c 'cd /var/www/html && php -r "require(\"./wp-load.php\"); echo \"WP loaded OK\\n\";"'
   
   # Step 3: Test admin_init hook
   docker exec wpshadow-test bash -c 'cd /var/www/html && php -r "define(\"WP_USE_THEMES\", false); require(\"./wp-load.php\"); do_action(\"admin_init\"); echo \"admin_init OK\\n\";"'
   
   # Step 4: Test WPShadow page (RECOMMENDED - most accurate)
   ./scripts/test-wpshadow-page.sh
   
   # OR manually test the full page:
   docker exec wpshadow-test bash -c 'cd /var/www/html && php -r "
   define(\"WP_USE_THEMES\", false);
   \$_SERVER[\"HTTP_HOST\"] = \"localhost:9000\";
   \$_SERVER[\"REQUEST_URI\"] = \"/wp-admin/admin.php?page=wpshadow\";
   \$_GET[\"page\"] = \"wpshadow\";
   require(\"./wp-load.php\");
   require_once(ABSPATH . \"wp-admin/includes/admin.php\");
   wp_set_current_user(1);
   do_action(\"toplevel_page_wpshadow\");
   echo \"✅ Page loaded OK\\n\";
   "'
   ```

4. **Restart container after fixes:**
   ```bash
   docker restart wpshadow-test && sleep 5
   ```

5. **Verify in browser:** https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/wp-admin/admin.php?page=wpshadow
   - **Login:** admin/admin

6. **Don't stop until:**
   - ✅ No PHP errors in logs
   - ✅ `./scripts/test-wpshadow-page.sh` passes all checks
   - ✅ Page loads successfully in browser
   - ✅ User confirms it's working

**Common Critical Error Patterns:**
- Missing `\` before global functions in namespaced classes
- Missing `function_exists()` checks for admin-only functions like `get_current_screen()`
- Missing `isset()` checks before accessing object properties (PHP 8.x strictness)
- Undefined methods in stub diagnostic classes
- Circular dependencies in asset loading

## Quick Testing Workflow

1. **Make code changes** in `/workspaces/wpshadow/`
2. **Verify PHP syntax:** `php -l path/to/changed-file.php`
3. **Restart container:** `docker restart wpshadow-test && sleep 5`
4. **Test via Docker:** `./scripts/test-wpshadow-page.sh`
5. **Test in browser:** https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/wp-admin/admin.php?page=wpshadow (admin/admin)

## Important Notes

- The GitHub Codespaces URL requires browser authentication
- Use `localhost:9000` for direct curl testing
- File changes are immediately synced via volume mount
- Some changes (like namespace fixes) may require container restart
- Admin credentials: `admin/admin` (for testing only)

---

**Last Updated:** January 23, 2026
