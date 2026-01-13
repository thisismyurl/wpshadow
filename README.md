# WordPress Support (thisismyurl)

**The Complete WordPress Support Solution - Standalone Core + Optional Module Ecosystem**

## Description

WordPress Support is a comprehensive WordPress support and diagnostics plugin that works perfectly as a **standalone core** or optionally extends with specialized modules (Image Hub, Media Hub, Vault Storage, and more). It acts as the foundational platform for managing features, diagnostics, emergency recovery, backup verification, and documentation.

This plugin provides complete functionality on its own and can optionally load hub and spoke plugins for extended capabilities.

### Standalone Core Features (Always Included)

- **Health Diagnostics:** Real-time WordPress health status, plugin/theme compatibility checks
- **Emergency Support:** Critical error recovery, one-click emergency isolation mode
- **Backup Verification:** Automated backup integrity testing and recovery drills
- **Site Documentation:** Plugin/theme inventory, protected plugins tracking, API documentation export
- **Activity Logging:** Comprehensive audit trail of all plugin operations and changes
- **Guided Walkthroughs:** Step-by-step task assistance for complex WordPress operations
- **Video Walkthroughs:** Auto-generated video tutorials of site functionality (requires external service)
- **Update Simulator:** Safe testing of plugin/theme updates before production deployment
- **Visual Regression Update Guard:** Automatic screenshot capture before/after updates with visual change detection
- **Diagnostic API:** Hidden diagnostic tokens for automated client support access
- **Spoke Collection System:** Gamified interface for discovering and managing format-specific plugins with milestones, achievements, and visual progression tracking (see [SPOKE_COLLECTION.md](docs/SPOKE_COLLECTION.md))
- **Script Loading Optimization:** Comprehensive performance optimization system (see [SCRIPT_OPTIMIZATION.md](SCRIPT_OPTIMIZATION.md))
  - Enhanced Script Deferral with auto-detection
  - Conditional Script Loading for plugin-specific pages
  - Google Fonts Disabler for privacy and performance
  - Critical CSS Inline for instant rendering
  - Resource Preloading for fonts, scripts, and images
  - Script Optimization Analyzer with recommendations

### Optional Hub & Spoke Architecture (When Modules Installed)

- **Media Hub:** Shared media optimization and processing infrastructure
- **Image Hub:** Hub for image format support (AVIF, WebP, HEIC, RAW, etc.)
- **Vault Storage:** Secure original file storage with encryption, journaling, and cloud offload
- **Surgical Scrubbing:** EXIF stripping and brand metadata injection
- **Smart Focus-Point:** Entropy-based subject detection for mobile cropping
- **Pixel-Sovereign:** Steganographic fingerprinting for invisible ownership

## Requirements

- **WordPress:** 6.4 or higher
- **PHP:** 8.1.29 or higher
- **Multisite:** Full support with Network Governance

## Installation

1. Upload the plugin files to `/wp-content/plugins/plugin-wp-support-thisismyurl/`
2. Activate through the 'Plugins' menu in WordPress
3. Configure settings under 'WordPress Support' in the admin menu
4. (Optional) Install additional modules from the Modules page for extended functionality

### Standalone Setup (Core Only)

The plugin works perfectly without any modules installed. Simply activate and enjoy:
- Full diagnostics and health monitoring
- Emergency recovery tools
- Backup verification and testing
- Complete documentation management

No modules directory or additional installations required.

### Multisite Installation

For network-wide activation, activate from the Network Admin panel. Global settings will override individual site settings unless explicitly allowed.

## Using the Support Dashboard

The Support Dashboard provides quick access to all core features.

### Navigation
- **Dashboard (menu):** High-level overview and quick access to features
- **Modules (optional):** Install and manage optional modules when needed
- **Settings / Network Settings:** Core and module configuration

### Core Features

**Health Diagnostics Dashboard:**
- Real-time WordPress version and compatibility status
- Plugin and theme health indicators
- System requirements verification
- One-click access to WordPress Site Health

**Emergency Support:**
- Activate emergency isolation mode (disable all plugins except WP Support)
- Automated error log monitoring
- Quick recovery procedures
- Network-wide emergency controls (in Network Admin)

**Backup Verification:**
- Run automated backup integrity tests
- Perform recovery drills without affecting live site
- Verify data consistency and completeness
- Schedule regular backup verification

**Site Documentation:**
- View installed plugins and themes with version info
- Mark critical plugins as protected from deactivation
- Export API documentation
- Create plugin/theme inventory reports

**Activity Log:**
- Complete audit trail of all operations
- Filter by event type and date range
- Export activity reports
- Network-wide activity viewing (in Network Admin)

**Cookie Consent Management:**
- Enable from Settings → Features → Privacy & Compliance
- Displays consent banner to visitors before setting non-essential cookies
- Granular consent options for analytics, marketing, and essential cookies
- Blocks third-party cookies until consent given
- Customize banner text and blocked cookie patterns in settings
- Built-in patterns for Google Analytics, Facebook Pixel, DoubleClick, etc.
- No external CDN dependencies - fully local implementation
- GDPR and privacy law compliant

### Modules Page (When Modules Installed)

- **Stat cards:** Total, Enabled, Available modules
- **Modules table:** Hub and spoke modules with version and status
- **Actions:** Install, update, activate/deactivate modules
- **Network info:** "Network Active" badges and network management controls

### Troubleshooting
- If counts look off, ensure the plugin is active network-wide (for network modules) or locally
- If actions are missing, check capabilities: `manage_options` (site) or `manage_network_options` (network)
- Core features work perfectly without any modules installed

## Multisite Configuration

### Network Admin

**Network Settings** allow global policies:
- Set default module visibility across all sites (if modules installed)
- Configure network-wide licensing and authentication
- Manage global caching and performance thresholds

### Site Admin

Each site can:
- Override network defaults (where allowed)
- Enable/disable modules locally
- Configure site-specific settings

**Default Behavior:** Network policies take precedence unless the network admin allows site overrides.

## Privacy & User Data

Core Support includes built-in privacy and GDPR compliance:

- **Cookie Consent Management:** Built-in consent banner for GDPR compliance (no external CDNs)
  - Detects and blocks third-party cookies until consent is given
  - Granular consent options (essential, analytics, marketing)
  - Local banner with customizable messaging
  - Auto-blocks common trackers (Google Analytics, Facebook Pixel, DoubleClick)
  - Dark mode and mobile responsive
- **Export Personal Data:** WordPress Tools → Export Personal Data includes WordPress Support and module data
- **Erase Personal Data:** Requests securely erase user data; Vault originals are purged
- **Data Retention:** Admins set retention policies; old data auto-purges per schedule
- **Audit Trail:** All export/erase operations are logged

- **PHP 8.4+** with strict typing, Enums, and Property Hooks
- **SVE Protocol:** Sanitize inputs, Validate capabilities, Escape outputs
- **Late Escaping:** Security at point of output
- **DB Hygiene:** Mandatory `$wpdb->prepare()` for all queries
- **i18n Ready:** All strings wrapped in translation functions

## Development

### Directory Structure

```
plugin-wp-support-thisismyurl/
├── plugin-wp-support-thisismyurl.php  # Main plugin file
├── uninstall.php                      # Cleanup on uninstall
├── includes/                          # Core functionality
├── assets/                            # CSS/JS/Images
│   ├── css/
│   ├── js/
│   └── images/
└── languages/                         # Translation files
```

### Coding Standards

- WordPress Coding Standards (WordPress-Extra)
- PHPStan Level 8
- PHPUnit for testing

## Documentation

### User Guides

- **[Dashboard User Guide](DASHBOARD_USER_GUIDE.md)** — Complete admin dashboard documentation with workflows, settings, and FAQ
- **Using the Support Dashboard** (in-plugin) — Help text and inline documentation
- **Multisite Guide** — Network Admin policies and site-level overrides

### Developer Resources

- **README.md** — This file; architecture and installation
- **CHANGELOG.md** — Version history and release notes
- **[VAULT_PRIVACY_ERASER.md](VAULT_PRIVACY_ERASER.md)** — Privacy and data erasure documentation

### External Links

- **Online Documentation:** https://thisismyurl.com/plugin-wp-support-thisismyurl/
- **Support:** https://thisismyurl.com/plugin-wp-support-thisismyurl/#support
- **GitHub Issues:** https://github.com/thisismyurl/plugin-plugin-wp-support-thisismyurl

## Support

- **Documentation:** https://thisismyurl.com/plugin-wp-support-thisismyurl/
- **Support:** https://thisismyurl.com/plugin-wp-support-thisismyurl/#support
- **GitHub:** https://github.com/thisismyurl/plugin-plugin-wp-support-thisismyurl

## License

GPL2 or later. See LICENSE file for details.

## Author

**Christopher Ross**
Website: https://thisismyurl.com/?source=core-support-thisismyurl

---

*Part of the @thisismyurl Support Suite*
