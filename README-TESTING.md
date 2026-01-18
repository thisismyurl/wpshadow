# 🚀 WPShadow Testing Environment - Quick Start

## One-Command Setup

```bash
cd /workspaces/wpshadow
./validate-test-setup.sh install
```

This creates a full WordPress testing environment with:
- ✅ WordPress 6.9+ with WPShadow plugin
- ✅ MySQL 8.0 database
- ✅ Live code mounting (changes appear instantly)
- ✅ Access via GitHub Codespaces HTTPS URL

**Time to working test environment: ~30 seconds**

## Access WordPress

1. Open VS Code → Command Palette (`Cmd+Shift+P`)
2. Search: `Ports: Focus on Ports View`
3. Find **Port 9000** → Click globe icon
4. Login: `admin` / `admin123`

## What's Included

| File | Purpose |
|------|---------|
| `docker-compose-test.yml` | Docker infrastructure (WordPress + MySQL) |
| `wp-config-extra.php` | WordPress URL configuration for Codespaces |
| `validate-test-setup.sh` | One-command setup and management script |
| `TESTING_SETUP.md` | Complete setup guide with troubleshooting |
| `FILE_REFERENCE.md` | File documentation and maintenance notes |

## Available Commands

```bash
# Validate setup (checks all files and Docker)
./validate-test-setup.sh validate

# Full WordPress installation
./validate-test-setup.sh install

# Start existing containers
./validate-test-setup.sh start

# Stop containers
./validate-test-setup.sh stop

# Reset everything (delete data and reinstall)
./validate-test-setup.sh reset

# View live logs
./validate-test-setup.sh logs
```

## Plugin Testing Workflow

1. Edit plugin code in `/workspaces/wpshadow/` (includes/, features/, assets/)
2. Changes appear **instantly** in WordPress (live volume mount)
3. Refresh WordPress admin to see changes
4. Check errors in `/var/www/html/wp-content/debug.log`

```bash
# Monitor errors in real-time
docker exec wpshadow-test-wordpress tail -f /var/www/html/wp-content/debug.log
```

## Important: Update Codespaces Domain

The setup uses a default Codespaces domain. If you're using a different Codespace:

1. Find your domain in VS Code PORTS tab (should show `https://xxx-9000.app.github.dev/`)
2. Edit `wp-config-extra.php`:
   - Find: `stunning-fishstick-j69p5j559jqcpw79-9000`
   - Replace with: Your actual Codespaces domain (without `https://` or `-9000`)

```php
// Example if your port 9000 URL is: https://my-codespace-name-9000.app.github.dev/
define('WP_HOME', 'https://my-codespace-name-9000.app.github.dev');
define('WP_SITEURL', 'https://my-codespace-name-9000.app.github.dev');
```

Then restart: `docker restart wpshadow-test-wordpress`

## Database Credentials

- Host: `db` (Docker DNS, resolves to MySQL container)
- Database: `wordpress`
- User: `wordpress`
- Password: `wordpress`
- MySQL Root: `wordpress`

## Cleanup & Reset

```bash
# Soft reset (keep data)
./validate-test-setup.sh stop && ./validate-test-setup.sh start

# Hard reset (delete all data)
./validate-test-setup.sh reset

# Complete cleanup
docker-compose -f docker-compose-test.yml down --volumes --remove-orphans
```

## Troubleshooting

**Q: Wrong URL after login?**  
A: Update `WP_HOME` and `WP_SITEURL` in `wp-config-extra.php` with your Codespaces domain, then run `docker restart wpshadow-test-wordpress`

**Q: Database connection failed?**  
A: Check MySQL status: `docker logs wpshadow-test-db`

**Q: Still seeing WordPress install page?**  
A: WordPress tables may not exist. Run: `./validate-test-setup.sh reset`

**Q: Changes not appearing in WordPress?**  
A: Ensure files are in the plugin directory (not inside WordPress). Refresh browser.

For complete troubleshooting, see [TESTING_SETUP.md](TESTING_SETUP.md)

## File Organization

```
✅ Testing Infrastructure (fully documented)
   - docker-compose-test.yml
   - wp-config-extra.php
   - validate-test-setup.sh
   - TESTING_SETUP.md
   - FILE_REFERENCE.md (this guide)

✅ Plugin Code (live mounted)
   - wpshadow.php
   - assets/
   - includes/
   - features/

✅ Backups (preserved)
   - _backup_assets/
   - _backup_features/
   - _backup_includes/
   - etc.

❌ Removed (obsolete)
   - test-env.sh (replaced by validate-test-setup.sh)
   - quick-test.sh (replaced by validate-test-setup.sh)
   - .wp-env.json (old setup)
   - certs/ (not needed)
   - wp-content/ (generated)
```

## Next Time

To recreate this test environment later:

1. Check that all files exist: `ls docker-compose-test.yml wp-config-extra.php validate-test-setup.sh`
2. Update Codespaces domain if needed (see above)
3. Run: `./validate-test-setup.sh install`
4. Done! ✅

---

**Version:** 1.0  
**Status:** Production-ready and fully documented  
**Last tested:** January 18, 2026
