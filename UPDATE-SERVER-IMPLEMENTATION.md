# Update Server Implementation Guide

## Overview
This guide explains how to implement the **thisismyurl.com update server** that provides automatic updates for all TIMU plugins (WP Support Core + Image/Video Hubs + Format Spokes).

## Server Requirements
- **Endpoint**: `https://thisismyurl.com/api/updates/check.json`
- **Method**: POST
- **Response**: JSON
- **Authentication**: License key validation
- **Rate Limiting**: Recommended (per site/IP)

---

## Request Format

The WordPress plugin sends a POST request with the following data:

```php
{
    "site_url": "https://example.com",
    "license_key": "ABC123-DEF456-GHI789",
    "plugins": {
        "plugin-wp-support-thisismyurl": "1.2601.73001",
        "plugin-image-hub-thisismyurl": "1.0.5",
        "plugin-avif-support-thisismyurl": "1.0.2"
    },
    "wp_version": "6.9",
    "php_version": "8.3.0"
}
```

**Request Fields:**
- `site_url` - Customer's WordPress site URL
- `license_key` - Customer's license key (for validation)
- `plugins` - Currently installed TIMU plugins with their versions
- `wp_version` - WordPress version
- `php_version` - PHP version

---

## Response Format

### Successful Response (Valid License)

```json
{
    "success": true,
    "license_valid": true,
    "license_expires": "2026-12-31",
    "message": "License active",
    "plugins": {
        "plugin-wp-support-thisismyurl": {
            "name": "WP Support",
            "version": "1.3.0",
            "download_url": "https://thisismyurl.com/downloads/wp-support/1.3.0/plugin-wp-support-thisismyurl-1.3.0.zip?key=ABC123",
            "homepage": "https://thisismyurl.com/wp-support/",
            "author": "Christopher Ross",
            "requires": "6.4",
            "requires_php": "8.1",
            "tested": "6.9",
            "last_updated": "2026-01-11 12:00:00",
            "description": "<p>Core support functionality for the TIMU Media Suite.</p><p>Features include multi-engine fallback, encryption, cloud bridge, and vault management.</p>",
            "changelog": "<h3>1.3.0 - 2026-01-11</h3><ul><li><strong>New:</strong> Self-hosted update server integration</li><li><strong>New:</strong> License key management UI</li><li><strong>Improved:</strong> Performance optimizations</li></ul>",
            "banners": {
                "high": "https://thisismyurl.com/assets/banners/wp-support-banner-772x250.png",
                "low": "https://thisismyurl.com/assets/banners/wp-support-banner-772x250.png"
            },
            "icons": {
                "1x": "https://thisismyurl.com/assets/icons/wp-support-128x128.png",
                "2x": "https://thisismyurl.com/assets/icons/wp-support-256x256.png",
                "svg": "https://thisismyurl.com/assets/icons/wp-support.svg"
            }
        },
        "plugin-image-hub-thisismyurl": {
            "name": "Image Hub",
            "version": "1.1.0",
            "download_url": "https://thisismyurl.com/downloads/image-hub/1.1.0/plugin-image-hub-thisismyurl-1.1.0.zip?key=ABC123",
            "homepage": "https://thisismyurl.com/image-hub/",
            "author": "Christopher Ross",
            "requires": "6.4",
            "requires_php": "8.1",
            "tested": "6.9",
            "last_updated": "2026-01-10 10:30:00",
            "description": "<p>Image processing hub for the TIMU Media Suite.</p>",
            "changelog": "<h3>1.1.0</h3><ul><li>Bug fixes and improvements</li></ul>",
            "banners": {},
            "icons": {}
        }
    }
}
```

### Response for Invalid/Expired License

```json
{
    "success": false,
    "license_valid": false,
    "license_expires": "",
    "message": "Invalid or expired license key",
    "plugins": {}
}
```

### Response for No License Key

```json
{
    "success": true,
    "license_valid": false,
    "message": "No license key provided",
    "plugins": {}
}
```

---

## Response Fields Explained

### Top-Level Fields
- `success` (bool) - Whether the request was processed successfully
- `license_valid` (bool) - Whether the license key is valid and active
- `license_expires` (string) - Expiration date (YYYY-MM-DD format), empty if invalid
- `message` (string) - Human-readable status message
- `plugins` (object) - Available updates for installed plugins

### Plugin Object Fields
Each plugin slug (e.g., `plugin-wp-support-thisismyurl`) contains:

**Required Fields:**
- `name` (string) - Plugin display name
- `version` (string) - Latest available version
- `download_url` (string) - **Authenticated download URL** (includes license key or token)
- `homepage` (string) - Plugin homepage URL
- `author` (string) - Plugin author name
- `requires` (string) - Minimum WordPress version
- `requires_php` (string) - Minimum PHP version
- `tested` (string) - Tested up to WordPress version
- `last_updated` (string) - Last update timestamp (YYYY-MM-DD HH:MM:SS)
- `description` (string) - HTML description for update modal
- `changelog` (string) - HTML changelog for update modal

**Optional Fields:**
- `banners` (object) - Banner images for WordPress admin
  - `high` - 772x250px image URL
  - `low` - 772x250px image URL
- `icons` (object) - Plugin icons
  - `1x` - 128x128px image URL
  - `2x` - 256x256px image URL
  - `svg` - SVG icon URL (preferred)

---

## Server-Side Implementation

### PHP Example (Laravel/Symfony/WordPress)

```php
<?php
// Example: /api/updates/check.json endpoint

// 1. Validate request
$site_url = $_POST['site_url'] ?? '';
$license_key = $_POST['license_key'] ?? '';
$installed_plugins = json_decode($_POST['plugins'] ?? '{}', true);
$wp_version = $_POST['wp_version'] ?? '';
$php_version = $_POST['php_version'] ?? '';

// 2. Validate license key against database
$license = validate_license($license_key, $site_url);

if (!$license || !$license->is_active()) {
    echo json_encode([
        'success' => false,
        'license_valid' => false,
        'license_expires' => '',
        'message' => 'Invalid or expired license key',
        'plugins' => new stdClass()
    ]);
    exit;
}

// 3. Get available plugins for this license
$available_plugins = get_plugins_for_license($license);

// 4. Build response with download URLs
$plugins_response = [];
foreach ($available_plugins as $plugin) {
    // Generate authenticated download URL
    $download_token = generate_download_token($license_key, $plugin->slug);
    $download_url = sprintf(
        'https://thisismyurl.com/downloads/%s/%s/%s-%s.zip?token=%s',
        $plugin->slug,
        $plugin->version,
        $plugin->slug,
        $plugin->version,
        $download_token
    );

    $plugins_response[$plugin->slug] = [
        'name' => $plugin->name,
        'version' => $plugin->version,
        'download_url' => $download_url,
        'homepage' => $plugin->homepage,
        'author' => $plugin->author,
        'requires' => $plugin->requires_wp,
        'requires_php' => $plugin->requires_php,
        'tested' => $plugin->tested_wp,
        'last_updated' => $plugin->updated_at,
        'description' => $plugin->description_html,
        'changelog' => $plugin->changelog_html,
        'banners' => [
            'high' => $plugin->banner_url,
            'low' => $plugin->banner_url,
        ],
        'icons' => [
            '1x' => $plugin->icon_url,
            '2x' => $plugin->icon_2x_url,
            'svg' => $plugin->icon_svg_url,
        ],
    ];
}

// 5. Log update check for analytics
log_update_check($site_url, $license_key, $installed_plugins);

// 6. Return response
echo json_encode([
    'success' => true,
    'license_valid' => true,
    'license_expires' => $license->expires_at->format('Y-m-d'),
    'message' => 'License active',
    'plugins' => $plugins_response
], JSON_PRETTY_PRINT);
```

### Download Authentication

The `download_url` should include authentication to prevent unauthorized downloads:

**Option 1: Token in URL**
```
https://thisismyurl.com/downloads/wp-support/1.3.0/plugin-wp-support-1.3.0.zip?token=xyz123
```

**Option 2: License Key in URL**
```
https://thisismyurl.com/downloads/wp-support/1.3.0/plugin-wp-support-1.3.0.zip?key=ABC123-DEF456
```

The download endpoint should:
1. Validate the token/key
2. Check license expiration
3. Log the download
4. Stream the ZIP file

---

## Database Schema Example

### licenses table
```sql
CREATE TABLE licenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    license_key VARCHAR(255) UNIQUE NOT NULL,
    site_url VARCHAR(255),
    status ENUM('active', 'expired', 'suspended') DEFAULT 'active',
    expires_at DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (license_key),
    INDEX (site_url)
);
```

### plugins table
```sql
CREATE TABLE plugins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    slug VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    version VARCHAR(50) NOT NULL,
    author VARCHAR(255),
    homepage VARCHAR(255),
    description_html TEXT,
    changelog_html TEXT,
    requires_wp VARCHAR(10),
    requires_php VARCHAR(10),
    tested_wp VARCHAR(10),
    file_path VARCHAR(500), -- path to ZIP file
    file_size INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (slug)
);
```

### update_checks table (Analytics)
```sql
CREATE TABLE update_checks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    site_url VARCHAR(255),
    license_key VARCHAR(255),
    installed_plugins JSON,
    wp_version VARCHAR(10),
    php_version VARCHAR(10),
    ip_address VARCHAR(45),
    checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (site_url),
    INDEX (license_key),
    INDEX (checked_at)
);
```

---

## Security Considerations

### 1. Rate Limiting
Implement rate limiting to prevent abuse:
- **Per License Key**: 10 requests per hour
- **Per IP**: 20 requests per hour
- **Per Site URL**: 15 requests per hour

### 2. Download Authentication
- Generate time-limited download tokens (valid for 1 hour)
- Validate license on every download request
- Log all download attempts

### 3. License Validation
- Check license expiration date
- Verify site URL matches (optional, for single-site licenses)
- Support domain wildcards for multi-site licenses
- Track activation count for multi-site limits

### 4. HTTPS Required
- Enforce HTTPS on all endpoints
- Use secure download URLs with tokens

### 5. Input Validation
```php
// Sanitize all inputs
$site_url = filter_var($_POST['site_url'], FILTER_VALIDATE_URL);
$license_key = preg_replace('/[^A-Z0-9\-]/', '', strtoupper($_POST['license_key']));
```

---

## Testing

### Test Request (cURL)
```bash
curl -X POST https://thisismyurl.com/api/updates/check.json \
  -d "site_url=https://test.com" \
  -d "license_key=TEST-LICENSE-KEY" \
  -d 'plugins={"plugin-wp-support-thisismyurl":"1.2601.73001"}' \
  -d "wp_version=6.9" \
  -d "php_version=8.3"
```

### WordPress Test
1. Install WP Support plugin
2. Go to **WP Support → Updates**
3. Enter license key
4. Click "Check for Updates Now"
5. Verify update appears in Plugins page

---

## Analytics & Tracking

Track the following metrics:
- **Update checks per day** (by license/site)
- **Download counts** (by plugin/version)
- **Active installations** (unique sites checking in last 30 days)
- **WordPress/PHP version distribution**
- **License renewal dates** (send reminders)

---

## Changelog & Version Management

### Update Process
1. **Release new version** on GitHub (private repo)
2. **Build distribution ZIP** (run composer install --no-dev, exclude dev files)
3. **Upload ZIP** to thisismyurl.com server
4. **Update plugins table** with new version/changelog
5. **Customer sites automatically detect** update within 6 hours

### Version Numbering
Follow semantic versioning: `MAJOR.MINOR.PATCH`
- **Major**: Breaking changes (2.0.0)
- **Minor**: New features (1.3.0)
- **Patch**: Bug fixes (1.2.1)

---

## Support & Maintenance

### Handling Expired Licenses
- Return `license_valid: false`
- Still allow downloads for 7-day grace period
- Send renewal reminder emails

### Handling Suspended Licenses
- Return HTTP 403 with message
- Block all downloads immediately

### Handling Refunds
- Immediately invalidate license key
- Return "License suspended" message

---

## Migration from GitHub Updates

For existing customers using the GitHub updater:
1. Deploy update server at thisismyurl.com
2. Push new plugin version with WPS_Update_Client
3. Customers update once from GitHub (gets new updater)
4. Future updates come from thisismyurl.com server

---

## Cost Optimization

### Caching Strategy
- Cache license validation for 1 hour (Redis/Memcached)
- Cache plugin version data for 5 minutes
- Use CDN for download files

### Bandwidth Optimization
- Serve ZIP files via CDN
- Implement delta updates (future enhancement)
- Compress changelog/description HTML

---

## Example Server Stack

**Recommended Setup:**
- **Server**: DigitalOcean/AWS ($20-50/month)
- **Framework**: Laravel or WordPress REST API
- **Database**: MySQL/PostgreSQL
- **Cache**: Redis
- **CDN**: Cloudflare or BunnyCDN
- **Monitoring**: New Relic or Datadog

**Estimated Costs for 100 Customers:**
- Server: $20/month
- CDN: $5/month
- Monitoring: $10/month
- **Total**: ~$35/month

---

## Next Steps

1. **Set up server** at thisismyurl.com
2. **Implement `/api/updates/check.json` endpoint**
3. **Create license management** system
4. **Upload first release** ZIPs
5. **Test with development site**
6. **Roll out to customers**

---

## Support

For server-side implementation questions:
- Email: support@thisismyurl.com
- GitHub: https://github.com/thisismyurl/plugin-wp-support-thisismyurl/issues
