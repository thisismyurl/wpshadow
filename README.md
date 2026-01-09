# WordPress Support (thisismyurl)

**The Foundation Plugin for the @thisismyurl Plugin Suite**

## Description

WordPress Support is the foundational plugin for all @thisismyurl plugin-* repositories. It acts as the backbone that other plugins extend and require to be installed first.

This plugin manages all hub and spoke plugins, providing a means to install all modules and features.

### The Hub & Spoke Architecture

- **The Foundation (WordPress Support):** Plugin management, Multi-Engine Fallback (Imagick/GD), Encryption, Cloud Bridge
- **The Hubs:** Category-specific management plugins (Image, Media, etc.)
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

1. Upload the plugin files to `/wp-content/plugins/wordpress-support-thisismyurl/`
2. Activate through the 'Plugins' menu in WordPress
3. Configure settings under 'WordPress Support' in the admin menu

### Multisite Installation

For network-wide activation, activate from the Network Admin panel. Global settings will override individual site settings unless explicitly allowed.

## Using the Support Dashboard

The Support Dashboard links to the **Modules** page, where all management now happens (stat cards + table). Dashboard itself is a lightweight overview.

### Navigation
- **Dashboard (menu):** High-level intro with a button to Modules
- **Modules (primary workspace):** Stat cards + modules table
- **Settings / Network Settings:** Vault and policy configuration (scope depends on context)

### Modules page layout
- **Stat cards (top):** Total, Enabled, Available, Updates, Hubs, Spokes; numbers use `number_format_i18n()` and respect multisite activation (site + network-active)
- **Modules table:** Hubs and spokes grouped by hub; hub rows can collapse/expand spokes
- **Badges:** "Network Active" appears when a plugin is network-activated; override notices show when the Super Admin has locked settings

### Actions
- **Activate/Deactivate:** Toggle installed modules. If network-active, only Network Admin sees Deactivate Network.
- **Install/Update:** (when catalog download_url is available) install missing modules or update when a newer version is detected.
- **External links:** Catalog/details links open in a new tab with `rel="noopener noreferrer"`.

### Multisite behavior
- **Network Admin:** Sees network-active badges and network deactivate links; controls global policies.
- **Site Admin:** Sees network-active modules as enabled; cannot deactivate network-active modules. Override allowance is dictated by Super Admin policy.

### Troubleshooting
- If counts look off, ensure the plugin is active network-wide (for network modules) or locally; counts are derived from `is_plugin_active()` + `is_plugin_active_for_network()`.
- If actions are missing, check capabilities: `manage_options` (site) or `manage_network_options` (network) are required.

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
wordpress-support-thisismyurl/
├── wordpress-support-thisismyurl.php  # Main plugin file
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

- **Online Documentation:** https://thisismyurl.com/core-support-thisismyurl/
- **Support:** https://thisismyurl.com/core-support-thisismyurl/#support
- **GitHub Issues:** https://github.com/thisismyurl/plugin-wordpress-support-thisismyurl

## Support

- **Documentation:** https://thisismyurl.com/wordpress-support-thisismyurl/
- **Support:** https://thisismyurl.com/wordpress-support-thisismyurl/#support
- **GitHub:** https://github.com/thisismyurl/plugin-wordpress-support-thisismyurl

## License

GPL2 or later. See LICENSE file for details.

## Author

**Christopher Ross**
Website: https://thisismyurl.com/?source=core-support-thisismyurl

---

*Part of the @thisismyurl Support Suite*
