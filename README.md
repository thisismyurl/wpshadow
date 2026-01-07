# Core Support (thisismyurl)

**The Hub of the thisismyurl Media Suite**

## Description

Core Support is the foundational Hub of the thisismyurl Media Suite, providing essential architecture and killer features for all format-specific Spoke plugins.

### The Hub & Spoke Architecture

- **The Hub (Core):** Multi-Engine Fallback (Imagick/GD), Encryption, Cloud Bridge
- **The Spokes:** Format-specific transcoders (AVIF, WebP, HEIC, etc.)
- **Suite Identifier:** `thisismyurl-media-suite-2026` handshake ensures only relevant modules load

### Killer Features

1. **Pixel-Sovereign™** - Steganographic fingerprinting (LSB) for invisible ownership
2. **Smart Focus-Point™** - Entropy-based subject detection for mobile cropping
3. **The Vault™** - Secure original storage in `/vault/` (Zipped/Raw)
4. **Surgical Scrubbing™** - Strip GPS/Privacy EXIF; inject Brand Metadata
5. **Broken Link Guardian™** - Database ID-to-Vault mapping for self-healing 404s

## Requirements

- **WordPress:** 6.4 or higher
- **PHP:** 8.4 or higher
- **Multisite:** Full support with Network Governance

## Installation

1. Upload the plugin files to `/wp-content/plugins/core-support-thisismyurl/`
2. Activate through the 'Plugins' menu in WordPress
3. Configure settings under 'Core Support' in the admin menu

### Multisite Installation

For network-wide activation, activate from the Network Admin panel. Global settings will override individual site settings unless explicitly allowed.

## Using the Support Dashboard

The **Support Dashboard** provides a central hub for managing your Media Suite modules:

### Dashboard Overview

**Module Statistics:**
- **Total Modules** - All installed hubs and spokes
- **Enabled** - Active modules on this site
- **Available** - Modules in the catalog not yet installed
- **Updates** - Modules with newer versions available
- **Hubs/Spokes** - Count of Hub vs. Spoke modules

### Module Management

**Module Cards** display:
- **Module Name & Type** - Hub or Spoke designation
- **Status** - Installed, Available, or Update Available
- **Enable/Disable Toggle** - Turn modules on/off without uninstalling
- **Install Button** - Add available modules (if not installed)
- **Update Button** - Install newer versions when available

**Actions:**
- **Toggle Enable/Disable:** Quickly enable or disable any installed module
- **Install Module:** Download and activate modules from the catalog
- **Update Module:** Update to the latest version with one click

### Filters & Search

- **Type Filter** - Show only Hubs, Spokes, or All
- **Status Filter** - Show Installed, Available, or All
- **Search** - Find modules by name or keyword

### Module Types

- **Hubs** - Core infrastructure (Multi-Engine Fallback, Encryption, Cloud Bridge)
- **Spokes** - Format-specific transcoders (AVIF, WebP, HEIC, etc.)

## Multisite Configuration

### Network Admin

**Network Settings** allow global policies:
- Set default module visibility across all sites
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

- **Export Personal Data:** WordPress Tools → Export Personal Data includes Core and module data
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
core-support-thisismyurl/
├── core-support-thisismyurl.php  # Main plugin file
├── uninstall.php                  # Cleanup on uninstall
├── includes/                      # Core functionality
├── assets/                        # CSS/JS/Images
│   ├── css/
│   ├── js/
│   └── images/
└── languages/                     # Translation files
```

### Coding Standards

- WordPress Coding Standards (WordPress-Extra)
- PHPStan Level 8
- PHPUnit for testing

## Support

- **Documentation:** https://thisismyurl.com/core-support-thisismyurl/
- **Support:** https://thisismyurl.com/core-support-thisismyurl/#support
- **GitHub:** https://github.com/thisismyurl/core-support-thisismyurl

## License

GPL2 or later. See LICENSE file for details.

## Author

**Christopher Ross**  
Website: https://thisismyurl.com/?source=core-support-thisismyurl

---

*Part of the thisismyurl Media Suite*
