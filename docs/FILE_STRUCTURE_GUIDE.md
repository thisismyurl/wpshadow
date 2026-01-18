# WPShadow Plugin - New File Structure Guide

## Quick Reference: Where to Find Things

### Admin / Dashboard Features
**Location:** `/includes/admin/`
- Dashboard widgets and rendering
- Dashboard settings and layout
- Tab navigation and menu structure
- Admin assets and screens
- Widget groups and registry
- Wizard interfaces

**Key Files:**
- `class-wps-dashboard-widgets.php` - Widget definitions and rendering
- `class-wps-dashboard-layout.php` - Dashboard page layout
- `class-wps-tab-navigation.php` - Tab switching system
- `assets.php` - Admin CSS/JS registration
- `screens.php` - Admin screen setup

### Core System
**Location:** `/includes/core/`
- Foundation classes required by everything else
- Feature registration and management
- Settings caching and retrieval
- Router guards and access control
- Capability management

**Key Files:**
- `class-wps-feature-registry.php` - Feature discovery and registration
- `class-wps-capabilities.php` - Role-based access control
- `class-wps-settings-cache.php` - Settings API with caching
- `class-wps-router-guard.php` - Prevent access to disabled features
- `class-wps-spoke-base.php` - Multi-plugin spoke system

### Health & Audits
**Location:** `/includes/health/`
- Site health monitoring and diagnostics
- Site audit functionality
- Health scoring and reporting
- System report generation

**Key Files:**
- `class-wps-site-health.php` - Health integration
- `class-wps-site-audit.php` - Complete site audits
- `class-wps-health-renderer.php` - Health data display
- `class-wps-health-score-widget.php` - Dashboard widget

### Monitoring & Performance
**Location:** `/includes/monitoring/`
- Performance metrics and tracking
- Activity logging
- Environment information
- Server limits checking
- Data retention and privacy

**Key Files:**
- `class-wps-performance-monitor.php` - Performance tracking
- `class-wps-activity-logger.php` - Event and task logging
- `class-wps-environment-checker.php` - Server environment info
- `class-wps-server-limits.php` - PHP limits and warnings

### Support & Recovery
**Location:** `/includes/support/`
- Backup verification and restoration
- Snapshot management and rollback
- Staging environment creation
- Emergency support access
- White screen recovery
- SOS support features

**Key Files:**
- `class-wps-backup-verification.php` - Backup integrity checks
- `class-wps-snapshot-manager.php` - Site snapshot management
- `class-wps-magic-link-support.php` - Time-limited support tokens
- `class-wps-emergency-support.php` - Emergency protocols
- `class-wps-white-screen-recovery.php` - White screen fixes

### Onboarding & Documentation
**Location:** `/includes/onboarding/`
- User registration and setup
- Guided walkthroughs
- Smart suggestions
- Site documentation

**Key Files:**
- `class-wps-registration.php` - User registration
- `class-wps-guided-walkthroughs.php` - Step-by-step guides
- `class-wps-smart-suggestions.php` - Context-aware suggestions
- `class-wps-site-documentation-manager.php` - Documentation

### Utilities & Tools
**Location:** `/includes/utilities/`
- Command-line interface (WP-CLI)
- Debug mode toggle
- Diagnostic API
- Help content

**Key Files:**
- `class-wps-cli.php` - WP-CLI commands
- `class-wps-debug-mode.php` - Debug mode management
- `class-wps-hidden-diagnostic-api.php` - Secure diagnostics
- `class-wps-help-content-api.php` - Dynamic help content

### Features (Optional/Paid)
**Location:** `/includes/features/`
- Feature implementations and modules
- Performance features
- Customization options
- Advanced capabilities

**Key Files:**
- `class-wps-achievement-badges.php` - Achievement system
- `class-wps-video-walkthroughs.php` - Video tutorials
- `class-wps-customization-audit.php` - Customization detection

### Other Existing Directories

| Directory | Purpose |
|-----------|---------|
| `/includes/api/` | REST API endpoints and controllers |
| `/includes/helpers/` | Utility functions (input, AJAX, arrays, etc.) |
| `/includes/traits/` | Shared trait definitions |
| `/includes/views/` | HTML templates and components |
| `/includes/settings/` | Settings configuration and UI |
| `/includes/audits/` | Audit helper functions |
| `/includes/widgets/` | Widget rendering utilities |

## How the Plugin Boots Up

1. **wpshadow.php** loads on `plugins_loaded` hook
2. Calls `wpshadow_init()` function
3. Loads helper functions from `/includes/helpers/`
4. Loads core system from `/includes/core/`
5. Initializes admin features from `/includes/admin/`
6. Loads health, support, and utility systems
7. Registers features and modules
8. Sets up hooks and filters

## File Naming Conventions

- **Classes:** `class-wps-{name}.php` → `class WPShadow\CoreSupport\WPSHADOW_{Name}`
- **Functions:** `wps-{purpose}.php` → global functions like `wpshadow_get_setting()`
- **Traits:** `trait-wps-{name}.php` → `trait WPSHADOW_{Name}`
- **Views:** `{purpose}-page.php` or `{purpose}.php` → HTML templates

## Navigation Tips

1. **Find a class by name:** Use basename, then check appropriate directory
2. **Related files:** Look in the same subdirectory
3. **System dependencies:** Check `/includes/core/`
4. **Admin features:** Check `/includes/admin/`
5. **Utilities/helpers:** Check `/includes/utilities/` or `/includes/helpers/`

## Adding New Features

1. Create class file in appropriate subdirectory
2. Add `require_once` in `wpshadow.php` around line 500-700
3. Call `register_WPSHADOW_feature()` in `WPSHADOW_register_core_features()`
4. Test plugin activation: `wp plugin activate wpshadow`

---

**Last Updated:** January 18, 2026
**Plugin Version:** 1.2601.75000
