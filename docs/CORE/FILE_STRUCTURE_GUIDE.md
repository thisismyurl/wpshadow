# WPShadow File Structure Guide

Quick reference for navigating the WPShadow plugin codebase.

---

## Top-Level Structure

```
wpshadow/
├── wpshadow.php              # Main plugin file (hooks, menus, AJAX, rendering)
├── wpshadow-pro/             # Pro addon (separate repository)
├── includes/
│   ├── admin/                # Dashboard, AJAX handlers, screens
│   ├── core/                 # Base classes, registries, utilities
│   ├── diagnostics/          # Site health checks
│   ├── treatments/           # Auto-fix implementations
│   ├── workflow/             # Workflow engine, triggers, actions
│   ├── views/                # PHP templates
│   └── data/                 # JSON data files
├── assets/                   # CSS, JS, images
├── detectors/                # Detection utilities
├── helpers/                  # Helper functions
├── vendor/                   # Composer dependencies
└── docs/                     # Documentation
```

---

## Core Directories

### `/includes/diagnostics/` - Health Checks

Site health diagnostic classes (50+ checks).

**Pattern:** `class-diagnostic-{name}.php`  
**Registry:** `class-diagnostic-registry.php`  
**Namespace:** `WPShadow\Diagnostics\`

**Examples:**
- `class-diagnostic-ssl.php`
- `class-diagnostic-memory-limit.php`  
- `class-diagnostic-post-via-email.php`

### `/includes/treatments/` - Auto-Fix Solutions

Safe, reversible fixes for diagnostic findings.

**Pattern:** `class-treatment-{name}.php`  
**Registry:** `class-treatment-registry.php`  
**Interface:** `interface-treatment.php`  
**Namespace:** `WPShadow\Treatments\`

**Key Methods:** `get_finding_id()`, `can_apply()`, `apply()`, `undo()`

### `/includes/workflow/` - Workflow Engine

Automation with triggers and actions.

**Files:**
- `class-workflow-manager.php` - CRUD
- `class-workflow-executor.php` - Execution
- `class-workflow-wizard.php` - UI builder

### `/includes/data/` - JSON Data

Static data files (tooltips, passwords, etc.).

**Files:** `tooltips*.json`, `password-words.json`

### `/includes/views/` - Templates

PHP view templates for UI.

---

## Key Files

### `wpshadow.php` (Main Plugin, ~3700 lines)

**Contains:**
- Plugin header
- Constants (VERSION, PATH, URL)
- Admin menus
- AJAX handlers
- Rendering router
- Helper functions

**Key Functions:**
- `wpshadow_get_health_status()`
- `wpshadow_get_site_findings()`
- `wpshadow_attempt_autofix()`

### `wpshadow-pro/wpshadow-pro.php`

Pro addon loader with license, modules, GitHub settings.

---

## Naming Conventions

### Classes
```php
namespace WPShadow\{Module};
class Diagnostic_SSL extends Diagnostic_Base { }
class Treatment_SSL implements Treatment_Interface { }
```

### Functions
```php
wpshadow_{function_name}()
```

### Constants
```php
WPSHADOW_{CONSTANT}
```

---

## Registry Pattern

Central registries manage components:

```php
// Diagnostics
\WPShadow\Diagnostics\Diagnostic_Registry::run_quickscan_checks();

// Treatments
\WPShadow\Treatments\Treatment_Registry::apply_treatment( $finding_id );
```

---

## Quick Lookups

- **Add diagnostic:** Create `includes/diagnostics/class-diagnostic-{name}.php`, register
- **Add treatment:** Create `includes/treatments/class-treatment-{name}.php`, register
- **Add workflow trigger:** Edit `class-workflow-wizard.php` `$triggers`
- **Add workflow action:** Edit `class-workflow-wizard.php` `$action_groups`
- **Add tooltips:** Edit `includes/data/tooltips-*.json`
- **Add AJAX handler:** Add `wp_ajax_wpshadow_{action}` in `wpshadow.php`

---

*Last Updated: January 21, 2026*
