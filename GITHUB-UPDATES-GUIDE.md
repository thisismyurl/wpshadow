# GitHub Automatic Updates - Implementation Guide

**Version:** 1.0  
**Date:** January 11, 2026  
**Status:** ✅ Production Ready

---

## Overview

WordPress Support now includes **automatic update support for private GitHub repositories**. The plugin will automatically check for new releases on GitHub and enable one-click updates directly from the WordPress admin dashboard, just like WordPress.org plugins.

This works seamlessly with **private repositories** by supporting optional GitHub personal access tokens for authentication.

---

## How It Works

### 1. Automatic Update Checks

The plugin checks for new releases from the GitHub repository every **12 hours** (controlled by WordPress's update schedule).

**Process:**
- WordPress triggers update check via `site_transient_update_plugins` hook
- Plugin fetches latest release from GitHub API: `https://api.github.com/repos/thisismyurl/plugin-wp-support-thisismyurl/releases/latest`
- Version comparison: If GitHub release is newer than installed version, update becomes available
- Update transient is cached for 6 hours to optimize API usage
- Admin dashboard displays "Update available" badge and version info

### 2. Version Comparison

Versions are compared using standard `version_compare()` logic:
- GitHub tag: `v1.2602.10015` → stripped to `1.2602.10015`
- Installed: `1.2601.73001`
- Result: Newer version available ✓

### 3. One-Click Updates

Users click "Update now" in the Plugins dashboard, WordPress:
1. Downloads the ZIP from GitHub's zipball URL
2. Extracts to a temporary directory
3. Replaces plugin files
4. Deactivates and reactivates the plugin
5. Displays success message

---

## Setup Instructions

### For Private Repositories

**Step 1: Create a GitHub Personal Access Token**

1. Visit: https://github.com/settings/tokens
2. Click "Generate new token" → "Generate new token (classic)"
3. Configure:
   - **Name:** "WordPress Support Updates"
   - **Expiration:** No expiration (recommended for production)
   - **Scopes:** Check only `repo` (full control of private repositories)
4. Click "Generate token"
5. Copy the token (appears once only!)

**Step 2: Add Token to WordPress**

1. Go to: WordPress Admin → WP Support → GitHub Updates
2. Paste your token in the "Personal Access Token" field
3. Click "Save Token"
4. Confirmation: "GitHub token saved successfully"

**That's it!** The plugin will now use the token for all GitHub API calls.

### For Public Repositories (Optional)

If your repository is public, you **don't need a token**. The plugin will work without authentication but will have lower GitHub API rate limits (60 requests/hour vs 5,000 with token).

---

## Configuration Options

### GitHub Token Storage

**Location:** WordPress option `wps_github_token`

Tokens can be configured via:

1. **WordPress Admin UI** (Recommended)
   - Dashboard: WP Support → GitHub Updates
   - Paste token and save
   - Displayed as dots for security

2. **Environment Variable** (Hosting/Server Level)
   ```bash
   export GITHUB_TOKEN="ghp_xxxxxxxxxxxxxxxxxxxx"
   ```
   Useful for:
   - WordPress VIP / Managed hosting
   - Docker / Kubernetes deployments
   - CI/CD pipelines

3. **Direct Option Update** (Database)
   ```php
   update_option( 'wps_github_token', 'ghp_xxxxxxxxxxxxxxxxxxxx' );
   ```

### Manual Cache Clearing

If you want to force an immediate update check (for testing):

```php
// In WordPress CLI or admin code
\WPS\CoreSupport\WPS_GitHub_Updater::clear_cache();
```

This clears:
- Latest release transient
- WordPress plugin update transient

### Manual Version Check

```php
$release = \WPS\CoreSupport\WPS_GitHub_Updater::manual_check();
if ( $release ) {
    echo 'Latest version: ' . $release['tag_name'];
}
```

---

## Technical Details

### Classes & Methods

#### `WPS_GitHub_Updater`

**Public Methods:**

```php
// Initialize updater (called automatically in wp_support_init)
WPS_GitHub_Updater::init( string $plugin_basename ): void

// Clear update cache
WPS_GitHub_Updater::clear_cache(): void

// Manually check for updates
WPS_GitHub_Updater::manual_check(): ?array
```

**Hooks Used:**

- `site_transient_update_plugins` - Injects update info into WordPress transient
- `plugins_api` - Provides plugin info for update details modal
- `plugin_action_links_{basename}` - Adds "GitHub Updates" settings link

#### `WPS_GitHub_Settings`

Provides admin UI and token management:

```php
// Render GitHub Updates settings page
wp_support_render_github_updates(): void

// Handle token form submission
wp_support_handle_github_token_submission(): void
```

### API Endpoints

**Get Latest Release:**
```
GET https://api.github.com/repos/thisismyurl/plugin-wp-support-thisismyurl/releases/latest
```

Optional Authentication:
```
Authorization: token ghp_xxxxxxxxxxxxxxxxxxxx
```

**Response Fields Used:**
- `tag_name` - Version tag (e.g., "v1.2602.10015")
- `html_url` - Link to release page
- `zipball_url` - Direct download link for ZIP
- `body` - Release notes (displayed in modal)
- `published_at` - Release date

### Caching Strategy

**Update Transient:**
- Key: `wps_github_latest_release`
- Expiration: 6 hours (21,600 seconds)
- Cleared on: Token save, manual cache clear, plugin update

**WordPress Update Transient:**
- Key: `update_plugins`
- Expiration: WordPress default (usually 12 hours)
- Controls update check frequency

---

## Security Considerations

### Token Security

✅ **What We Do:**
- Tokens stored in WordPress options table (database)
- Never logged or displayed in plain text
- Admin UI shows "••••••••••" placeholders
- Tokens only transmitted to official GitHub API (`api.github.com`)
- No third-party services involved

⚠️ **Best Practices:**
- Use token with minimal scope (`repo` only)
- Set token expiration if possible (optional)
- Regenerate token if compromised
- Use environment variables for production servers
- Limit database access to trusted users

### GitHub API Security

- All requests use HTTPS only
- Tokens transmitted in secure Authorization header
- No data cached except release metadata (public info)
- API responses validated before use

---

## Troubleshooting

### Updates Not Showing

**Check 1: GitHub Token**
```
WordPress Admin → WP Support → GitHub Updates
```
Verify token is saved and valid.

**Check 2: Release Published**
- Ensure your GitHub repo has official Releases (not just commits)
- Go to: GitHub Repo → Releases → Verify "Latest release"
- Release must have a tag (e.g., "v1.2602.10015")

**Check 3: Force Update Check**
```php
// In WordPress CLI or WP-CLI
wp eval 'WPS\CoreSupport\WPS_GitHub_Updater::clear_cache();'

// Or via admin: Plugins page, refresh browser
```

**Check 4: Debug Log**
Check `/wp-content/debug.log` for:
```
WPS GitHub updater fetch failed: [error message]
WPS GitHub API error: HTTP 401  # Token invalid
WPS GitHub API error: HTTP 404  # Repo not found
```

### "Update failed" Error

Usually means:
1. **Private repo but no token** - Solution: Add GitHub token
2. **Token expired** - Solution: Generate new token
3. **No write permissions** - Solution: Check plugin directory permissions
4. **GitHub down** - Solution: Try again later

### Rate Limit Exceeded

**Without token:** 60 API calls/hour
**With token:** 5,000 API calls/hour

If you see rate limit errors:
1. Add GitHub token (increases to 5,000/hour)
2. Wait 1 hour (rate limit resets automatically)

---

## Repository Requirements

For automatic updates to work, your GitHub repository must:

1. ✅ Have **Releases** section with official tags
2. ✅ Tags follow semantic versioning (e.g., `v1.0.0`, `1.2.3`)
3. ✅ Be publicly visible (private repos need token)
4. ✅ Have ZIP file generation enabled (GitHub default)

**Example Release:**
```
Tag: v1.2602.10015
Name: Version 1.2602.10015
Description: New features, bug fixes, security updates
```

---

## WordPress Compatibility

- **Minimum WordPress:** 6.4
- **Maximum WordPress:** 6.9+ (tested, likely works on newer)
- **Update System:** Uses standard WordPress plugin update hooks
- **Transients:** Compatible with all object caching backends
- **Multisite:** Works on single-site and multisite installations

---

## Examples

### Check if Updates Are Available

```php
// Refresh WordPress update transient
wp_update_plugins();

// Get plugin update info
$updates = get_site_transient( 'update_plugins' );
$basename = 'plugin-wp-support-thisismyurl/plugin-wp-support-thisismyurl.php';

if ( isset( $updates->response[ $basename ] ) ) {
    $update = $updates->response[ $basename ];
    echo 'New version available: ' . esc_html( $update->new_version );
}
```

### Programmatically Set Token

```php
// Set GitHub token
update_option( 'wps_github_token', 'ghp_xxxxxxxxxxxxxxxxxxxx' );

// Verify it works
$release = WPS_GitHub_Updater::manual_check();
if ( $release ) {
    echo 'Connected to GitHub! Latest: ' . $release['tag_name'];
}
```

### Schedule Periodic Checks

```php
// Clear cache every 4 hours to force fresh checks
add_action( 'init', function() {
    if ( ! wp_next_scheduled( 'wps_refresh_github_updates' ) ) {
        wp_schedule_event( time(), '4_hours', 'wps_refresh_github_updates' );
    }
});

add_action( 'wps_refresh_github_updates', function() {
    WPS_GitHub_Updater::clear_cache();
});
```

---

## Version History

**v1.0** - January 11, 2026
- Initial implementation
- GitHub API integration
- Private repo support with tokens
- Admin UI for token management
- Full WordPress update system integration

---

## Support & Maintenance

### Monitoring

Monitor these files for health:
- `/wp-content/debug.log` - API errors and warnings
- GitHub Actions (on GitHub repo) - Release publishing

### Maintenance Tasks

- **Monthly:** Check that releases publish correctly
- **Quarterly:** Review token security and rotate if needed
- **Annually:** Update GitHub API integration if breaking changes

### Future Enhancements

Potential improvements:
- Automatic token rotation
- Webhook-triggered force-checks (when release published)
- Update scheduling (check at specific times)
- Changelog display in update modal
- Pre-release version support

---

## Questions?

The GitHub updater is production-ready and fully tested. All errors are logged to WordPress debug.log for troubleshooting.

For issues:
1. Check debug.log
2. Verify GitHub token is valid
3. Confirm release exists on GitHub
4. Check repository is public or token has `repo` scope
