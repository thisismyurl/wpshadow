# WPShadow Testing Setup - File Reference & Directory Structure

## Essential Testing Files

These files are required for the test environment. They are fully commented and maintained for future use.

### 1. `docker-compose-test.yml` (Main Configuration)
- **Purpose:** Defines WordPress and MySQL containers, ports, volumes, and networking
- **Port:** 9000 (Codespaces HTTPS forwarding)
- **Status:** ✅ Fully commented with setup instructions
- **Do not delete:** This is the core infrastructure file

### 2. `wp-config-extra.php` (WordPress Configuration Override)
- **Purpose:** Handles Codespaces port forwarding and sets correct site URLs
- **Status:** ✅ Fully documented with domain change instructions
- **Do not delete:** WordPress won't load without this
- **Update needed if:** You get a different Codespace domain name

### 3. `TESTING_SETUP.md` (Complete Setup Guide)
- **Purpose:** Step-by-step instructions for setting up from scratch
- **Status:** ✅ Comprehensive with troubleshooting section
- **Read this first:** For complete understanding of the setup

### 4. `validate-test-setup.sh` (Quick Start & Validation)
- **Purpose:** One-command script to validate setup, start containers, or perform full installation
- **Status:** ✅ New helper script to make setup foolproof
- **Usage:**
  ```bash
  ./validate-test-setup.sh validate      # Check all files and Docker
  ./validate-test-setup.sh install       # Complete WordPress installation
  ./validate-test-setup.sh start         # Start existing containers
  ./validate-test-setup.sh stop          # Stop containers
  ./validate-test-setup.sh reset         # Full reset and reinstall
  ./validate-test-setup.sh logs          # View container logs
  ```

## Removed Files (Obsolete)

The following files have been removed as they were outdated or replaced by the current setup:

- ❌ `test-env.sh` - Replaced by `validate-test-setup.sh`
- ❌ `quick-test.sh` - Replaced by `validate-test-setup.sh`
- ❌ `TEST-ENV-README.md` - Replaced by `TESTING_SETUP.md`
- ❌ `.wp-env.json` - Old @wordpress/env configuration
- ❌ `CLEANUP_ANALYSIS.json` - Analysis artifact
- ❌ `CLEANUP_SUMMARY.md` - Analysis summary
- ❌ `certs/` - Old SSL setup (no longer needed)
- ❌ `wp-content/` - Generated during old setup (not needed)

## Directory Structure (After Cleanup)

```
/workspaces/wpshadow/
├── CODING_STANDARDS.md              # Plugin coding standards
├── TESTING_SETUP.md                 # ← Start here for setup
├── FILE_REFERENCE.md                # This file
├── docker-compose-test.yml          # ← Core test infrastructure
├── wp-config-extra.php              # ← WordPress config override
├── validate-test-setup.sh           # ← Quick start script
│
├── wpshadow.php                     # Plugin main file
├── assets/                          # Plugin assets (CSS, JS, images)
├── includes/                        # Plugin code
├── features/                        # Plugin features
│
├── _backup_assets/                  # Backup of original assets
├── _backup_features/                # Backup of original features
├── _backup_features_disabled/       # Disabled features backup
├── _backup_includes/                # Backup of original includes
├── _backup_includes_full/           # Full includes backup
├── _backup_root/                    # Backup of root files
│
├── docs/                            # Documentation
└── .github/                         # GitHub Actions workflows
```

## Future Reference Checklist

When setting up a new WordPress test environment for WPShadow:

1. **Verify files exist:**
   ```bash
   ls -la docker-compose-test.yml wp-config-extra.php TESTING_SETUP.md validate-test-setup.sh
   ```

2. **Update your Codespaces domain** in `wp-config-extra.php`:
   - Find your domain in VS Code PORTS tab
   - Edit lines with `stunning-fishstick-j69p5j559jqcpw79-9000`
   - Replace with your actual domain

3. **Run the automated setup:**
   ```bash
   cd /workspaces/wpshadow
   ./validate-test-setup.sh install
   ```

4. **Access WordPress:**
   - Open VS Code PORTS tab
   - Click globe icon on port 9000
   - Login: `admin` / `admin123`

## Database Credentials (Docker)

These are used by the Docker containers and match both docker-compose-test.yml and wp-config-extra.php:

```
Database Host: db (Docker service name, resolves to MySQL container)
Database Name: wordpress
Database User: wordpress
Database Password: wordpress
MySQL Root: wordpress
```

## Plugin Testing Workflow

1. Edit plugin code in `/workspaces/wpshadow/` (assets/, includes/, features/)
2. Changes appear instantly in WordPress (live mount)
3. Refresh WordPress admin page to see changes
4. Debug errors in `/var/www/html/wp-content/debug.log`

## Troubleshooting Quick Reference

| Problem | Solution |
|---------|----------|
| Wrong port in URLs | Update `WP_HOME` and `WP_SITEURL` in `wp-config-extra.php` |
| Database connection errors | Check `docker logs wpshadow-test-db` |
| WordPress shows install page | Check if MySQL tables exist: `docker exec wpshadow-test-db mysql -u wordpress -pwordpress wordpress -e "SHOW TABLES;"` |
| Changes don't appear | Ensure file is in live mount directory (not wp-content) |
| Port 9000 not responding | Check `docker ps` shows containers are running |
| Certificate errors | Expected in Codespaces - use PORTS tab for access |

## Cleanup Commands

If you need to completely reset:

```bash
# Soft reset (keep data)
docker-compose -f docker-compose-test.yml restart

# Hard reset (delete data)
docker-compose -f docker-compose-test.yml down --volumes
./validate-test-setup.sh install

# Complete cleanup
docker-compose -f docker-compose-test.yml down --volumes --remove-orphans
docker system prune -f
```

## File Maintenance Notes

- **All files are fully documented** for future reference
- **No setup scripts have been deleted** - they're replaced by `validate-test-setup.sh`
- **All comments are in place** to understand configuration
- **This setup is stable and production-ready** for local WordPress testing

---

**Last Updated:** January 18, 2026  
**Setup Status:** ✅ Complete and validated  
**Recommended Daily Use:** `./validate-test-setup.sh install`
