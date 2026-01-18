# WPShadow Coding Standards

This document establishes consistent naming conventions and coding patterns for the WPShadow plugin. Following these standards ensures code readability, maintainability, and predictability across all files.

## 1. Naming Conventions

### Constants
**Pattern:** `SCREAMING_SNAKE_CASE`

Used for plugin-wide constants that don't change during runtime.

```php
define( 'WPSHADOW_VERSION', '1.2601.75000' );
define( 'WPSHADOW_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPSHADOW_MIN_PHP', '8.1.29' );
define( 'WPSHADOW_TEXT_DOMAIN', 'wpshadow' );
```

**Rationale:** Immediately identifies immutable values; standard WordPress convention.

---

### Global Functions
**Pattern:** `wpshadow_verb_noun()` (lowercase prefix)

Used for all publicly-callable functions and action/filter callbacks.

```php
function wpshadow_init(): void { ... }
function wpshadow_admin_menu(): void { ... }
function wpshadow_render_tab_router(): void { ... }
function wpshadow_register_module_submenus( string $capability ): void { ... }
function wpshadow_guard_disabled_modules(): void { ... }
function wpshadow_filter_parent_file( string $parent_file ): string { ... }
```

**Rationale:** 
- Lowercase prefix prevents namespace collision
- Verb-first naming clarifies function purpose
- Consistent across WordPress plugins
- Easy to grep/search for all WPShadow functions

---

### AJAX Handler Functions
**Pattern:** `WPSHADOW_verb_noun()` (SCREAMING_SNAKE_CASE prefix)

Used specifically for AJAX callbacks registered via `add_action( 'wp_ajax_...' )`.

```php
function WPSHADOW_ajax_toggle_module(): void { ... }
function WPSHADOW_ajax_install_module(): void { ... }
function WPSHADOW_ajax_update_module(): void { ... }
function WPSHADOW_ajax_save_metabox_state(): void { ... }
```

**Rationale:**
- Matches the AJAX action hook pattern: `wp_ajax_WPSHADOW_*`
- Visually distinct from regular functions
- Indicates these are callback functions, not normal utilities
- Consistent with the action name for easy cross-referencing

---

### Classes
**Pattern:** `WPSHADOW_Noun_Style` (SCREAMING_SNAKE + PascalCase words)

Main plugin classes use the `WPSHADOW_` prefix with PascalCase words.

```php
class WPSHADOW_Module_Registry { ... }
class WPSHADOW_Settings_Cache { ... }
class WPSHADOW_Dashboard_Widgets { ... }
class WPSHADOW_Feature_Registry { ... }
class WPSHADOW_Session_Manager { ... }
class WPSHADOW_Site_Health { ... }
class WPSHADOW_Activity_Logger { ... }
```

**Rationale:**
- Globally unique (WPSHADOW_ prefix prevents collisions)
- Descriptive (Noun_Style clarifies purpose)
- Consistent across similar systems (all registry classes use Registry suffix)
- Easy to locate in filesystem: `class-wps-module-registry.php` → `WPSHADOW_Module_Registry`

---

### Namespaces
**Pattern:** `WPShadow\Subspace` (PascalCase only, no SCREAMING_SNAKE)

Namespaces use PascalCase without screaming case or underscores.

```php
namespace WPShadow;
namespace WPShadow\CoreSupport;
namespace WPShadow\Admin;
namespace WPShadow\API;
namespace WPShadow\HealthSupport; // For health features
```

**Rationale:**
- Standard PHP namespace convention
- Logical hierarchy mirrors folder structure
- Cleanly separates namespaced vs global scope
- Easier to read `WPShadow\CoreSupport\WPSHADOW_Registry` than if namespace used underscores

---

### Class Methods
**Pattern:** `verb_noun()` (snake_case, standard PHP)

All class methods use standard snake_case.

```php
class WPSHADOW_Module_Registry {
    public static function get_catalog_with_status(): array { ... }
    public static function is_enabled( string $slug ): bool { ... }
    private static function load_catalog(): array { ... }
}

class WPSHADOW_Settings_Cache {
    public static function get( string $key, $default = null ): mixed { ... }
    public static function set( string $key, $value ): void { ... }
    public static function load_batch( array $keys ): array { ... }
}
```

**Rationale:** Standard PHP convention; consistent with WordPress core; all team members expect this.

---

### Properties & Variables
**Pattern:** `$snake_case` (standard PHP)

```php
private static $catalog_cache = array();
private $plugin_version = '';
$module_id = sanitize_key( $slug );
$is_enabled = $registry->is_enabled( $module );
```

**Rationale:** Standard PHP convention; improves readability through consistent use of `$` prefix.

---

## 2. Hook Naming

### Action Hooks
**Pattern:** `wpshadow_verb_noun` (lowercase, no trailing `()`; underscore-separated)

```php
// Registration phase
do_action( 'wpshadow_register_features' );
do_action( 'wpshadow_init_admin' );

// Activation/deactivation
add_action( 'activated_plugin', 'wpshadow_clear_plugins_cache' );
add_action( 'deactivated_plugin', 'wpshadow_clear_plugins_cache' );

// AJAX-specific
add_action( 'wp_ajax_WPSHADOW_toggle_module', 'WPSHADOW_ajax_toggle_module' );
add_action( 'wp_ajax_WPSHADOW_install_module', 'WPSHADOW_ajax_install_module' );
```

**Rationale:** Clear prefix prevents collisions; verb-first naming clarifies intent.

---

### Filter Hooks
**Pattern:** `wpshadow_noun_action` (lowercase, underscore-separated)

```php
apply_filters( 'wpshadow_health_check_results', $results );
apply_filters( 'wpshadow_dashboard_widget_content', $content, $widget_id );
apply_filters( 'wpshadow_module_enabled', true, $module_slug );
```

**Rationale:** Consistent with WordPress conventions; noun-first for filters (data being modified).

---

## 3. File Organization

### Filenames
**Pattern:** `class-wps-noun-descriptor.php` or `wps-function-group.php`

Classes:
```
includes/core/class-wps-module-registry.php → class WPSHADOW_Module_Registry
includes/admin/class-wps-dashboard-widgets.php → class WPSHADOW_Dashboard_Widgets
includes/helpers/class-wps-session-manager.php → class WPSHADOW_Session_Manager
```

Function files:
```
includes/helpers/wps-file-helpers.php → wpshadow_file_exists(), wpshadow_safe_get_contents(), etc.
includes/helpers/wps-array-helpers.php → wpshadow_array_validate(), etc.
includes/wps-widget-functions.php → wpshadow_render_widget(), etc.
```

**Rationale:** Follows WordPress plugin standards; clear distinction between classes and functions.

---

## 4. Architecture Pattern

### Module Loading
All modules follow this pattern in `wpshadow_init()`:

```php
// Load class (always first)
require_once WPSHADOW_PATH . 'includes/core/class-wps-module-registry.php';

// Call static init method (if class has one)
\WPShadow\CoreSupport\WPSHADOW_Module_Registry::init();
```

**Rationale:** Predictable initialization flow; easy to trace dependencies.

---

### Static Class Pattern
Public utility classes use static methods exclusively:

```php
class WPSHADOW_Module_Registry {
    // ✅ Good: Static utility
    public static function is_enabled( string $slug ): bool { ... }
    
    // ✅ Good: Cached data
    private static $cache = array();
    
    // ❌ Avoid: Instance properties on static-only classes
    private $instance_data;
}
```

**Rationale:** Clear signal that this is a utility/singleton, not instance-based; prevents accidental instantiation.

---

## 5. Function Documentation

### PHPDoc Format

```php
/**
 * Short description on one line.
 *
 * Longer description if needed, explaining the purpose, behavior, and
 * any important side effects or interactions.
 *
 * @param string $slug Module slug (e.g., 'vault-wpshadow').
 * @param int    $ttl  Cache time-to-live in seconds. Default 3600.
 * 
 * @return bool True if module is enabled, false otherwise.
 * 
 * @since 1.2601.75000
 * 
 * @example
 *   if ( WPSHADOW_Module_Registry::is_enabled( 'vault-wpshadow' ) ) {
 *       // Load vault features
 *   }
 */
public static function is_enabled( string $slug, int $ttl = 3600 ): bool { ... }
```

**Rationale:** Full IDE autocomplete support; clear contract for callers; type hints in doc blocks aid static analysis.

---

## 6. Quick Reference

| Category | Pattern | Example | Where Used |
|----------|---------|---------|-----------|
| Constants | `SCREAMING_SNAKE_CASE` | `WPSHADOW_VERSION` | Plugin-wide |
| Global Functions | `wpshadow_verb_noun()` | `wpshadow_init()` | Callbacks, utilities |
| AJAX Functions | `WPSHADOW_verb_noun()` | `WPSHADOW_ajax_toggle_module()` | AJAX handlers only |
| Classes | `WPSHADOW_Noun_Style` | `WPSHADOW_Module_Registry` | Main plugin classes |
| Namespaces | `WPShadow\Subspace` | `WPShadow\CoreSupport` | File organization |
| Methods | `verb_noun()` | `get_catalog_with_status()` | Class methods |
| Variables | `$snake_case` | `$module_id` | All scopes |
| Files (Class) | `class-wps-noun.php` | `class-wps-module-registry.php` | Classes |
| Files (Functions) | `wps-noun-helpers.php` | `wps-file-helpers.php` | Function groups |

---

## 7. Implementation Checklist

When adding new code, verify:

- [ ] **Constants** use `WPSHADOW_` prefix and SCREAMING_SNAKE_CASE
- [ ] **Functions** use `wpshadow_` prefix (or `WPSHADOW_` for AJAX handlers) and snake_case
- [ ] **Classes** use `WPSHADOW_` prefix and PascalCase words separated by underscores
- [ ] **Namespaces** use `WPShadow\Subspace` format (PascalCase, no underscores)
- [ ] **Methods** use snake_case (standard PHP convention)
- [ ] **Files** follow naming pattern: `class-wps-*.php` or `wps-*-helpers.php`
- [ ] **PHPDoc** includes parameter types, return type, and @since version
- [ ] **Hooks** follow `wpshadow_*` naming with appropriate verb/noun order

---

## 8. Migration Notes (January 2026)

### Changes Made
1. **Removed:** `wpshadow_hide_disabled_submenus()` function and associated JavaScript
   - **Reason:** Submenus now only register when enabled via conditional logic
   - **Benefit:** Cleaner DOM, no JS overhead, better performance

2. **Updated:** `wpshadow_register_module_submenus()` to check `is_enabled()` before registration
   - **Before:** Registered all modules, hid disabled via JavaScript
   - **After:** Only registers enabled modules (cleaner, semantic, performant)

3. **Established:** Consistent naming across all core functions
   - **Before:** Mix of `wpshadow_`, `WPSHADOW_`, `WPShadow` prefixes
   - **After:** Clear rules for each context (functions, AJAX, classes, namespaces)

---

## 9. Future Updates

This document is a living standard. Updates should be made when:
- New architectural patterns emerge
- Team consensus shifts on naming
- WordPress standards change
- Performance patterns are discovered

Update this file alongside code changes that introduce new patterns.

---

**Last Updated:** January 18, 2026  
**Version:** 1.0  
**Authored by:** WPShadow Development Team
