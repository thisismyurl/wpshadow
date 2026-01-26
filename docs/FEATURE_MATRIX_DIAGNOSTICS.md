# WPShadow Diagnostic Features - Complete Matrix

**Total Diagnostics:** 60 (1 implemented, 59 planned)  
**Last Updated:** January 26, 2026  
**Location:** `includes/diagnostics/`

## Implementation Status

✅ **Implemented:** 1  
📋 **Planned:** 59  
**Progress:** 1.7%

---

## Implemented Diagnostics

| ID | Slug | Category | Threat Level | Auto-Fixable | Status | File |
|----|------|----------|--------------|--------------|--------|------|
| 1 | `php-version` | Settings | 25-95 (varies) | No | ✅ Live | [class-diagnostic-php-version.php](../includes/diagnostics/tests/settings/class-diagnostic-php-version.php) |

---

## Quick Summary by Category

| Category | Count | Examples |
|----------|-------|----------|
| **Security** | 12 | SSL, Security Headers, Admin Username, Hotlink Protection |
| **Performance** | 15 | Memory Limit, Image Lazy Load, External Fonts, jQuery Migrate |
| **Code Quality** | 12 | Error Log, Debug Mode, Emoji Scripts, WP Generator |
| **WordPress Config** | 12 | WordPress Version, PHP Version, Permalinks, Tagline, Theme/Plugin Update Noise |
| **Monitoring** | 5 | Database Health, Broken Links, Plugin Count, Mobile Friendliness |
| **Workflow/System** | 3 | Initial Setup, Registry, Maintenance |

---

## Complete Diagnostic List

### Security Diagnostics (12)

| ID | Diagnostic | Purpose | Threat Level | Auto-Fixable |
|----|-----------|---------|--------------|--------------|
| 1 | `admin-email` | Checks if admin email is public/weak | Medium | No |
| 2 | `admin-username` | Detects 'admin' username (brute force risk) | High | No |
| 3 | `security-headers` | Verifies security headers (CSP, X-Frame, etc.) | Medium | Yes |
| 4 | `ssl` | Checks HTTPS/SSL status | High | Yes |
| 5 | `hotlink-protection` | Enables direct linking protection | Low | Yes |
| 6 | `rest-api` | Detects exposed REST API endpoints | Medium | Yes |
| 7 | `rss-feeds` | Checks RSS feed exposure | Low | Yes |
| 8 | `error-log` | Enables debug logging safely | Medium | Yes |
| 9 | `post-via-email` | Detects Post via Email security risk | High | Yes |
| 10 | `post-via-email-category` | Checks uncategorized email default | Medium | Yes |
| 11 | `file-permissions` | Verifies file/directory permissions | High | No |
| 12 | `consent-checks` | Validates user consent compliance | Medium | Yes |

### Performance Diagnostics (15)

| ID | Diagnostic | Purpose | Threat Level | Auto-Fixable |
|----|-----------|---------|--------------|--------------|
| 13 | `memory-limit` | PHP memory allocation too low | High | Yes |
| 14 | `image-lazy-load` | Enables lazy loading for images | Low | Yes |
| 15 | `external-fonts` | Detects and blocks external fonts | Medium | Yes |
| 16 | `jquery-migrate` | Disables outdated jQuery Migrate | Low | Yes |
| 17 | `emoji-scripts` | Removes unnecessary emoji scripts | Low | Yes |
| 18 | `asset-versions` | Manages asset version numbers | Low | Yes |
| 19 | `head-cleanup` | Removes bloat from document head | Low | Yes |
| 20 | `jquery-cleanup` | Optimizes jQuery usage | Low | Yes |
| 21 | `block-cleanup` | Removes unused blocks | Low | Yes |
| 22 | `html-cleanup` | Optimizes HTML output | Low | Yes |
| 23 | `paste-cleanup` | Cleans pasted content | Low | Yes |
| 24 | `admin-fonts` | Optimizes admin panel fonts | Low | Yes |
| 25 | `resource-hints` | Adds preload/prefetch hints | Low | Yes |
| 26 | `database-health` | Checks database optimization | Medium | No |
| 27 | `maintenance` | System maintenance checks | Medium | Yes |

### Code Quality Diagnostics (12)

| ID | Diagnostic | Purpose | Threat Level | Auto-Fixable |
|----|-----------|---------|--------------|--------------|
| 28 | `debug-mode` | Detects WP_DEBUG enabled | Low | Yes |
| 29 | `error-log` | Checks error logging status | Low | Yes |
| 30 | `wp-generator` | Removes WP generator meta tag | Low | Yes |
| 31 | `embed-disable` | Disables oEmbed embeds | Low | Yes |
| 32 | `interactivity-cleanup` | Removes interactivity API if unused | Low | Yes |
| 33 | `skiplinks` | Adds accessibility skiplinks | Low | Yes |
| 34 | `nav-aria` | Validates ARIA attributes in navigation | Low | Yes |
| 35 | `css-classes` | Validates CSS class generation | Low | Yes |
| 36 | `iframe-busting` | Prevents clickjacking via iframes | Medium | Yes |
| 37 | `block-analytics-hosts` | Blocks known analytics trackers | Low | Yes |
| 38 | `strip-json-ld` | Manages JSON-LD schema output | Low | Yes |
| 39 | `strip-social-meta` | Removes social meta tags | Low | Yes |

### WordPress Configuration Diagnostics (12)

| ID | Diagnostic | Purpose | Threat Level | Auto-Fixable |
|----|-----------|---------|--------------|--------------|  
| 40 | `wordpress-version` | Checks WP core is current | Medium | No |
| 41 | `php-version` | Verifies PHP meets minimum | High | No |
| 42 | `permalinks` | Checks URL structure | Low | Yes |
| 43 | `tagline` | Verifies site tagline is set | Low | Yes |
| 44 | `backup` | Detects backup solution | Medium | No |
| 45 | `search-indexing` | Ensures site is indexable | Medium | Yes |
| 46 | `comments-disabled` | Manages comment functionality | Low | Yes |
| 47 | `plugin-auto-updates` | Enables automatic plugin updates | Low | Yes |
| 48 | `content-optimizer` | Analyzes content quality | Low | No |
| 49 | `pre-publish-review` | Validates content before publishing | Low | Yes |
| 50 | `theme-update-noise` | Flags inactive themes causing update nags | Low | Yes |
| 51 | `plugin-update-noise` | Flags inactive plugins causing update nags | Low | Yes |

### Monitoring Diagnostics (5)

| ID | Diagnostic | Purpose | Threat Level | Auto-Fixable |
|----|-----------|---------|--------------|--------------|
| 52 | `database-health` | Overall database status | Medium | No |
| 53 | `broken-links` | Detects broken internal links | Low | No |
| 54 | `plugin-count` | Monitors active plugin count | Low | No |
| 55 | `mobile-friendliness` | Checks mobile optimization | Low | No |
| 56 | `timezone` | Verifies timezone configuration | Low | No |

### System/Workflow Diagnostics (4)

| ID | Diagnostic | Purpose | Threat Level | Auto-Fixable |
|----|-----------|---------|--------------|--------------|
| 57 | `initial-setup` | Bundle check for initial config | Medium | Partial |
| 58 | `registry` | Internal diagnostic registry check | Low | No |
| 59 | `core-integrity` | Validates core plugin integrity | Medium | No |
| 60 | `user-notification-email` | Email notification system status | Low | No |

---

## Threat Levels Explained

- **High (25-50 points):** Security critical or performance severe
  - Examples: SSL not active, 'admin' username, PHP version too old
- **Medium (10-24 points):** Important but not emergency
  - Examples: Memory limit low, backup missing, outdated WordPress
- **Low (1-9 points):** Nice to have, optimization
  - Examples: Emoji scripts, WP generator tag, lazy loading

---

## Auto-Fixable Diagnostics

Diagnostics marked as "Auto-Fixable: Yes" can have automated treatments applied:

**Count:** 38 of 57 (67%)

**Examples of Auto-Fixable:**
- `ssl` → Treatment_SSL
- `memory-limit` → Treatment_Memory_Limit
- `debug-mode` → Treatment_Debug_Mode
- `emoji-scripts` → Treatment_Emoji_Scripts
- `permalinks` → Treatment_Permalinks

---

## Usage in Workflows

All diagnostics can be used in workflow automation:

```php
// Example: Use diagnostic in workflow
IF: Diagnostic_Memory_Limit detects issue
THEN: Apply Treatment_Memory_Limit
```

---

## Recent Additions (January 2026)

1. **`post-via-email`** - Post via Email security (threat level: 16)
2. **`post-via-email-category`** - Uncategorized default routing (threat level: 12)
3. **`initial-setup` update** - Now marks file-editors as auto-fixable
4. **`consent-checks`** - User consent validation
5. **`theme-update-noise`** - Detects inactive themes generating update notifications (threat level: 25)
6. **`plugin-update-noise`** - Detects inactive plugins generating update notifications (threat level: 25)

---

*See [ARCHITECTURE.md](ARCHITECTURE.md) for implementation details.*
