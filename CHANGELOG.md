# Changelog

All notable changes to WordPress Support (thisismyurl) will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2601.73001] - 2026-01-09

### Changed
- **BREAKING:** Renamed plugin from "Core Support" to "WordPress Support"
- Plugin file renamed from `core-support-thisismyurl.php` to `wordpress-support-thisismyurl.php`
- Text domain changed from `core-support-thisismyurl` to `wordpress-support-thisismyurl`
- Updated plugin purpose: now acts as foundational plugin for all plugin-* repositories
- Repository renamed to `plugin-wordpress-support-thisismyurl`
- Updated all documentation to reflect new name and purpose
- Plugin now explicitly manages hub and spoke plugins as the backbone

### Note
- Internal architecture terms (module, hub, spoke) remain unchanged
- Existing functionality and features remain the same
- Migration guide: Deactivate old plugin, delete it, install new one with same settings

## [1.2601.71701] - 2026-01-07

### Added
- Initial plugin structure with Hub architecture
- Suite ID handshake (`thisismyurl-media-suite-2026`)
- Multisite support with Network Governance
- Vault directory setup with security measures
- PHP 8.4+ and WordPress 6.4+ requirement enforcement
- Strict typing and SVE protocol implementation
- i18n support with proper text domain
- Admin menu and settings pages
- Network admin menu for multisite installations

### Security
- Implemented vault protection with .htaccess
- Added capability checks for all admin pages
- Created secure directory structure with index.php protection
