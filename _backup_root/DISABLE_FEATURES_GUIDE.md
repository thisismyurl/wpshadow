# Disable All Features Except Asset Version Removal

This guide helps you focus on the Asset Version Removal feature by temporarily disabling all other features.

## Quick Start

### Option 1: Via Docker (Recommended)

1. **Start your Docker environment:**
   ```bash
   docker-compose up -d
   ```

2. **Run the update script through Docker:**
   ```bash
   docker exec wpshadow-dev php /var/www/html/wp-content/plugins/wpshadow/disable-features-except-asset-version.php
   ```

### Option 2: Direct Database Connection

If Docker containers aren't running, you can connect directly to the database:

```bash
php update-feature-toggles.php
```

Or with custom database credentials:

```bash
DB_HOST=localhost \
DB_NAME=wordpress \
DB_USER=wordpress \
DB_PASS=wordpress \
DB_PORT=3306 \
php update-feature-toggles.php
```

### Option 3: Via WP-CLI

If you have WP-CLI available:

```bash
wp eval-file disable-features-except-asset-version.php
```

## What Gets Disabled

The script will:

✅ **Keep ENABLED:**
- `asset-version-removal` (parent feature)
- `remove_css_versions` sub-feature (ON)
- `remove_js_versions` sub-feature (ON)
- `preserve_plugin_versions` sub-feature (OFF)

❌ **Disable ALL other features:**
- All 63+ other plugin features
- This allows complete focus on perfecting one feature

## What This Means

With only Asset Version Removal enabled:

1. **Minimal plugin footprint** - Only one feature running
2. **Clear testing** - Easy to see exactly what's happening
3. **Perfect prototype** - Can refine this feature as a template
4. **Easy debugging** - No interference from other features

## Testing the Asset Version Removal Feature

After running the script, test:

1. **Check CSS version removal:**
   - View page source
   - Look for `<link>` tags
   - Verify no `?ver=` parameters on CSS files

2. **Check JS version removal:**
   - View page source
   - Look for `<script>` tags
   - Verify no `?ver=` parameters on JS files

3. **Check plugin version preservation (if enabled):**
   - Plugin assets should still have versions
   - Core WordPress assets should have versions removed

## Re-enabling All Features

When you're done testing, you can re-enable features via:

1. **WordPress Admin:**
   - Go to WPShadow > Features
   - Toggle features back on individually

2. **Via Script:**
   Create a reverse script or manually update in database

## Files Included

- `disable-features-except-asset-version.php` - WordPress-aware script
- `update-feature-toggles.php` - Standalone database script
- `disable-features.sql` - SQL reference (not directly runnable)

## Troubleshooting

### Script fails with "database connection error"

Make sure your database credentials match your environment:
- Check `docker-compose.yml` for correct credentials
- Verify Docker containers are running: `docker ps`

### Script reports "no feature toggles found"

The plugin may not be activated. Check:
1. Plugin is installed at correct path
2. Plugin is activated in WordPress admin
3. Database table prefix is correct (default: `wp_`)

### Changes don't take effect

1. Clear WordPress object cache
2. Clear any page cache plugins
3. Check if features are reading from network-wide options (multisite)

## Development Workflow

1. Start containers: `docker-compose up -d`
2. Disable features: Run one of the scripts above
3. Make changes to Asset Version Removal feature
4. Test thoroughly
5. Apply learnings to other features
6. Re-enable features when ready

## Next Steps

With only Asset Version Removal active, you can:

1. ✅ Review the feature code structure
2. ✅ Test all sub-features independently
3. ✅ Refine the UI/UX
4. ✅ Perfect the documentation
5. ✅ Optimize performance
6. ✅ Use as template for other features

---

**Created:** January 17, 2026  
**Purpose:** Focus development on Asset Version Removal as prototype feature
