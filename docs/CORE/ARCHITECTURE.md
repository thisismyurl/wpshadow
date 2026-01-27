# WPShadow Architecture Guide

## Overview

WPShadow is a WordPress plugin providing comprehensive site health diagnostics, automated fixes, workflow automation, and performance optimization. Built with strict separation between detection and remediation layers.

**Plugin**: wpshadow  
**Version**: 1.2601.2112  
**Minimum PHP**: 8.1  
**Minimum WordPress**: 6.4  
**License**: GPL v2 or later  

---

## Core Design Philosophy

### Separation of Concerns
```
DETECT (Diagnostics) → ORGANIZE (Kanban) → FIX (Treatments) → TRACK (KPIs)
```

**Diagnostics:**
- Read-only checks
- Return structured findings
- Never modify anything
- Include threat levels (0-100)

**Treatments:**
- Apply safe fixes
- Create automatic backups
- Fully reversible
- Log KPI metrics

---

## Directory Structure

```
/wpshadow
├── wpshadow.php                          # Main plugin file (bootstrap)
├── composer.json                         # PHP dependencies & autoloader
├── assets/
│   ├── css/                             # Admin styles
│   ├── js/                              # Admin scripts (tooltips, dashboard, etc.)
│   └── images/                          # Icons and graphics
├── docs/                                # Documentation (architecture, guides, etc.)
│   └── archive/                         # Historical documentation
├── includes/
│   ├── admin/                           # Admin UI (dashboard, AJAX, screens)
│   ├── core/                            # Base classes (registries, interfaces, helpers)
│   ├── data/                            # JSON data files (tooltips, KB mappings)
│   ├── diagnostics/                     # Detection layer (57 diagnostic classes)
│   ├── treatments/                      # Solution layer (44 treatment classes)
│   ├── workflow/                        # Workflow automation system (39 files)
│   ├── views/                           # PHP view templates
│   ├── detectors/                       # Environment detection utilities
│   └── helpers/                         # Shared helper functions
├── wpshadow-pro/                        # Pro addon (separate repository)
│   └── See: https://github.com/thisismyurl/wpshadow-pro
└── vendor/                              # Composer dependencies (gitignored)
```

**Key Directories:**
- `includes/diagnostics/` - 57 read-only checks (security, performance, config)
- `includes/treatments/` - 44 safe, reversible fixes with backup/undo
- `includes/workflow/` - Workflow automation with triggers and actions
- `includes/data/` - Tooltip JSON files with KB URL mappings
- Pro addon in separate repository: https://github.com/thisismyurl/wpshadow-pro

---

## Core Systems

### 1. Diagnostics System

**Location:** `includes/diagnostics/`  
**Count:** 57 checks  
**Pattern:** Each diagnostic extends `\WPShadow\Core\Diagnostic_Base`

**Example Diagnostic:**
```php
namespace WPShadow\Diagnostics;

class Diagnostic_Memory_Limit extends \WPShadow\Core\Diagnostic_Base {
    public function run(): array {
        $limit = ini_get( 'memory_limit' );
        $limit_bytes = $this->convert_to_bytes( $limit );
        
        if ( $limit_bytes < 256 * 1024 * 1024 ) {
            return $this->create_finding(
                id: 'memory-limit-low',
                title: 'PHP Memory Limit Too Low',
                description: "Current: {$limit}, Recommended: 256M+",
                threat_level: 25,
                severity: 'medium',
                auto_fixable: true
            );
        }
        
        return []; // No issue found
    }
}
```

**Key Diagnostics:**
- `Diagnostic_Memory_Limit` - PHP memory configuration
- `Diagnostic_SSL` - HTTPS/SSL status
- `Diagnostic_Debug_Mode` - WP_DEBUG settings
- `Diagnostic_Outdated_Plugins` - Plugin updates needed
- `Diagnostic_Post_Via_Email` - Post via Email security
- `Diagnostic_File_Permissions` - File/directory permissions
- `Diagnostic_Admin_Username` - Checks for 'admin' username
- `Diagnostic_Backup` - Backup solution detection
- ... (57 total)

**Registry:**
```php
// includes/diagnostics/class-diagnostic-registry.php
namespace WPShadow\Diagnostics;

class Diagnostic_Registry extends \WPShadow\Core\Abstract_Registry {
    public static function init(): void {
        self::register( 'memory-limit', Diagnostic_Memory_Limit::class );
        self::register( 'ssl', Diagnostic_SSL::class );
        // ... all 57 diagnostics registered
    }
}
```

### 2. Treatments System

**Location:** `includes/treatments/`  
**Count:** 44 treatments  
**Pattern:** Each treatment implements `\WPShadow\Treatments\Interface_Treatment`

**Example Treatment:**
```php
namespace WPShadow\Treatments;

class Treatment_Memory_Limit extends \WPShadow\Core\Treatment_Base {
    public function apply(): bool {
        // Create backup first
        $this->create_wp_config_backup();
        
        // Apply fix
        $result = $this->update_wp_config_constant(
            'WP_MEMORY_LIMIT',
            '256M'
        );
        
        if ( $result ) {
            // Track KPI metrics
            $this->log_kpi( 'memory-limit-increased', 15 ); // 15 min saved
            return true;
        }
        
        return false;
    }
    
    public function undo(): bool {
        // Restore from backup
        return $this->restore_wp_config_from_backup();
    }
}
```

**Key Treatments:**
- `Treatment_Permalinks` - SEO-friendly URL structure
- `Treatment_Memory_Limit` - PHP memory increase
- `Treatment_File_Editors` - Disable theme/plugin editors
- `Treatment_SSL` - Force HTTPS
- `Treatment_Debug_Mode` - Disable debug output
- `Treatment_Outdated_Plugins` - Safe plugin updates
- `Treatment_Head_Cleanup` - Remove <head> bloat
- `Treatment_Emoji_Scripts` - Remove emoji scripts
- ... (44 total)

**Registry:**
```php
// includes/treatments/class-treatment-registry.php
namespace WPShadow\Treatments;

class Treatment_Registry extends \WPShadow\Core\Abstract_Registry {
    public static function init(): void {
        self::register( 'memory-limit', Treatment_Memory_Limit::class );
        self::register( 'file-editors', Treatment_File_Editors::class );
        // ... all 44 treatments registered
    }
}
```

### 3. Workflow System

**Location:** `includes/workflow/`  
**Files:** 11 workflow components

**Components:**
- `class-workflow-manager.php` - Central workflow engine
- `class-workflow-wizard.php` - Step-by-step workflow builder UI
- `class-workflow-triggers.php` - Trigger definitions
- `class-workflow-actions.php` - Action definitions
- Trigger/action implementations

**Workflow Structure:**
```
Trigger (IF) → Conditions (optional) → Actions (THEN)
```

**Example Triggers:**
- Schedule (daily, weekly, hourly)
- Page load (frontend, admin, specific pages)
- Events (user login, post published, plugin activated)
- Conditions (high memory, debug mode, SSL issues)

**Example Actions:**
- Apply treatment
- Send notification
- Log event
- Run diagnostic

**Example Workflow:**
```
IF: User logs in (Event Trigger)
AND: Username is 'admin' (Condition)
THEN: Send security alert + Log warning
```

### 4. Tooltip System

**Location:** `includes/data/tooltips-*.json`  
**Files:** 8 JSON files (general, settings, people, etc.)

**Features:**
- Context-sensitive help tooltips
- KB article integration
- Page-specific filtering
- Admin bar exclusions

**KB URL Format:**
```
https://wpshadow.com/kb/{context}-{slug}

Examples:
- settings-general-site-title
- user-new-user-password
- profile-personal-options
```

**Implementation:**
```javascript
// assets/js/tooltips.js
// Loads JSON, filters by page, adds ? icons with KB links
```

### 5. Dashboard & Kanban Board

**Location:** `includes/admin/` + `includes/views/kanban-board.php`

**Dashboard Features:**
- Site Health Summary
- Recent Diagnostics
- Quick Actions (auto-fix buttons)
- Finding categorization
- Crisis-mode alerting

**Kanban Board:**
- Organize findings by status (Detected → Fixed)
- Drag-and-drop interface
- Priority ordering
- Quick-fix actions
- KPI Metrics display

### 6. KPI Tracking

**Location:** `includes/core/class-kpi-tracker.php`

**Metrics Tracked:**
- Findings detected (count, severity)
- Fixes applied (auto vs manual)
- Time saved (15 min per fix default)
- Success rate (% fixes successful)

**Example:**
```php
// After applying treatment
\WPShadow\Core\KPI_Tracker::log_fix(
    finding_id: 'memory-limit-low',
    method: 'auto',
    time_saved: 15,
    success: true
);
```

---

## Naming Conventions

### Files
```
includes/diagnostics/class-diagnostic-{name}.php
includes/treatments/class-treatment-{name}.php
includes/workflow/class-workflow-{name}.php
includes/core/class-{name}.php
```

### Classes & Namespaces
```php
namespace WPShadow\Diagnostics;
class Diagnostic_Memory_Limit { }

namespace WPShadow\Treatments;
class Treatment_Memory_Limit { }

namespace WPShadow\Core;
class Abstract_Registry { }
```

### Functions
```php
// Global functions (lowercase prefix)
function wpshadow_init(): void { }
function wpshadow_admin_menu(): void { }

// AJAX handlers (SCREAMING_SNAKE_CASE prefix)
function WPSHADOW_ajax_toggle_module(): void { }
function WPSHADOW_ajax_save_settings(): void { }
```

### Constants
```php
define( 'WPSHADOW_VERSION', '1.2601.2112' );
define( 'WPSHADOW_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPSHADOW_MIN_PHP', '8.1' );
```

---

## Multisite Support

### Network Admin
- Network-wide settings
- Default configurations for all sites
- Bulk operations

### Site Admin
- Inherits network defaults
- Can override per-site
- Respects network restrictions

### Capability Checks
```php
// Network context
if ( current_user_can( 'manage_network_options' ) ) {
    // Network admin actions
}

// Site context
if ( current_user_can( 'manage_options' ) ) {
    // Site admin actions
}
```

---

## Extension Points

### Hooks & Filters

**Action Hooks:**
```php
do_action( 'wpshadow_diagnostic_registered', $diagnostic_id );
do_action( 'wpshadow_treatment_applied', $treatment_id, $success );
do_action( 'wpshadow_workflow_executed', $workflow_id );
```

**Filter Hooks:**
```php
apply_filters( 'wpshadow_diagnostic_enabled', true, $diagnostic_id );
apply_filters( 'wpshadow_treatment_targets', [], $treatment_id );
apply_filters( 'wpshadow_kb_url_format', $url, $context, $slug );
```

### Custom Diagnostics

```php
namespace MyPlugin\Diagnostics;

class Custom_Check extends \WPShadow\Core\Diagnostic_Base {
    public function run(): array {
        // Your logic here
        return $this->create_finding( /* ... */ );
    }
}

// Register it
add_action( 'wpshadow_diagnostics_init', function() {
    \WPShadow\Diagnostics\Diagnostic_Registry::register(
        'my-custom-check',
        Custom_Check::class
    );
} );
```

### Custom Treatments

```php
namespace MyPlugin\Treatments;

class Custom_Fix extends \WPShadow\Core\Treatment_Base {
    public function apply(): bool {
        // Your fix logic
    }
    
    public function undo(): bool {
        // Revert logic
    }
}

// Register it
add_action( 'wpshadow_treatments_init', function() {
    \WPShadow\Treatments\Treatment_Registry::register(
        'my-custom-fix',
        Custom_Fix::class
    );
} );
```

---

## Performance Considerations

### Diagnostic Execution
- Run on-demand (not every page load)
- Quick Scan: ~10 critical checks (~2 seconds)
- Full Scan: All 57 checks (~5-10 seconds)
- Results cached for 5 minutes

### Treatment Application
- One at a time (not batched by default)
- Backup before each change
- Rollback on failure
- KPI logging is async

### Asset Loading
- Admin assets only on WPShadow pages
- Tooltips loaded conditionally by page
- Dashboard widgets lazy-loaded
- No frontend impact

---

## Security

### Capability Requirements
- View diagnostics: `read` (basic access)
- Apply treatments: `manage_options` (site admin)
- Network settings: `manage_network_options` (network admin)

### Nonce Verification
All AJAX handlers and form submissions verify nonces:
```php
check_ajax_referer( 'wpshadow-action' );
```

### Input Sanitization
```php
$value = sanitize_text_field( $_POST['input'] );
$slug = sanitize_key( $_POST['slug'] );
```

### Output Escaping
```php
echo esc_html( $title );
echo esc_attr( $class );
echo wp_kses_post( $description );
```

### File Operations
- Backups before wp-config.php changes
- Atomic file writes
- Permission checks before modification

---

## Testing

### Development Environment
```bash
# Install dependencies
composer install

# Run PHPCS
composer phpcs

# Run PHPStan
composer phpstan

# Run tests (if configured)
composer test
```

### Manual Testing
1. Activate plugin
2. Navigate to WPShadow menu
3. Run Quick Scan
4. Review findings
5. Apply treatment (test undo)
6. Check KPI tracking

### Multisite Testing
1. Enable multisite
2. Network activate plugin
3. Test network settings
4. Test site-level overrides
5. Verify capability checks

---

## Related Documentation

- [SYSTEM_OVERVIEW.md](SYSTEM_OVERVIEW.md) - High-level system design
- [CODING_STANDARDS.md](CODING_STANDARDS.md) - Code style guide
- [FILE_STRUCTURE_GUIDE.md](FILE_STRUCTURE_GUIDE.md) - File organization
- [WORKFLOW_BUILDER.md](WORKFLOW_BUILDER.md) - Workflow automation
- [TOOLTIP_QUICK_REFERENCE.md](TOOLTIP_QUICK_REFERENCE.md) - Tooltip system
- [README.md](README.md) - Feature overview

---

*Last Updated: January 21, 2026*
