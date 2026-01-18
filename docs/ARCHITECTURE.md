# WPShadow Architecture Guide

## Overview

WPShadow is a WordPress plugin designed to provide comprehensive site health diagnostics, emergency recovery features, and performance optimization tools. The plugin follows WordPress coding standards and uses a feature-based architecture pattern.

**Plugin**: wpshadow  
**Version**: 1.2601.75000+  
**Minimum PHP**: 8.1.29  
**Minimum WordPress**: 6.4.0  
**License**: GPL v2 or later  

---

## Core Design Principles

### 1. **Feature Registry Pattern**
Features are self-contained, independently toggleable modules. The plugin loads features on-demand based on settings.

**Key Classes**:
- `WPSHADOW_Feature_Registry` - Central registry for all features
- `WPSHADOW_Abstract_Feature` - Base class for all features
- `WPSHADOW_Feature_Interface` - Contract for feature implementations

**Feature Lifecycle**:
```
register_feature() → enabled? → initialize() → register_hooks() → active
```

### 2. **Settings Inheritance**
Network-aware settings system with automatic fallback to network settings for multisite installations.

- `WPSHADOW_Settings` - Central settings manager
- `get_option_with_network_fallback()` - Hierarchical option retrieval
- Per-site overrides with network defaults

### 3. **Health System**
Integrated health checks provide site diagnostic data through WordPress Site Health integration.

- `WPSHADOW_Site_Health` - Core health checks manager
- Individual feature health contributions
- REST API health endpoints

### 4. **Developer Filters**
Strategic action and filter hooks for extensibility without modifying core code.

- Action hooks: `wpshadow_feature_initialized`, `wpshadow_settings_saved`
- Filter hooks: `wpshadow_asset_version_preserve`, `wpshadow_feature_enabled`

---

## Directory Structure

```
/wpshadow
├── wpshadow.php                          # Main plugin file
├── composer.json                         # PHP dependencies & autoloader
├── phpunit.xml                           # PHPUnit configuration
├── assets/
│   ├── css/
│   │   ├── dashboard-widgets.css        # Common UI styles
│   │   └── debug-mode.css               # Debug panel styles
│   ├── js/
│   │   └── admin.js                     # Admin interactivity
│   └── images/
├── docs/
│   ├── ARCHITECTURE.md                  # This file
│   ├── DEVELOPER_FILTERS.md             # Developer hook documentation
│   └── README.md                        # Feature guide
├── includes/
│   ├── abstracts/
│   │   ├── class-wps-feature-abstract.php       # Base feature class
│   │   └── class-wps-feature-validator.php      # Validation utilities
│   ├── admin/
│   │   ├── class-wps-dashboard-assets.php       # Asset management
│   │   ├── class-wps-dashboard-widgets.php      # Dashboard widgets
│   │   └── class-wps-feature-details-page.php   # Feature editor
│   ├── api/
│   │   ├── class-wps-rest-controller-base.php   # Base REST controller
│   │   └── class-wps-rest-settings-controller.php # Settings API
│   ├── helpers/
│   │   ├── wps-capability-helpers.php           # Permission checks
│   │   ├── wps-feature-functions.php            # Feature utilities
│   │   └── wps-asset-version-helpers.php        # Asset version logic
│   ├── views/
│   │   └── class-wps-view-components.php        # Reusable UI components
│   ├── traits/
│   │   └── trait-wps-ajax-security.php          # AJAX security patterns
│   ├── class-wps-feature-registry.php           # Feature management
│   ├── class-wps-settings.php                   # Settings API
│   ├── class-wps-site-health.php                # Health system
│   ├── class-wps-health-renderer.php            # Health UI renderer
│   └── [other core classes]
├── features/
│   ├── class-wps-feature-asset-version-removal.php     # Asset versioning
│   ├── class-wps-feature-hardening.php                 # Security hardening
│   ├── class-wps-feature-uptime-monitor.php            # Uptime tracking
│   └── [66+ feature implementations]
├── modules/
│   ├── hubs/                            # Hub module integrations
│   └── missing-modules.json             # Feature catalog
└── tests/
    └── [PHPUnit test suite]
```

---

## Key Components

### **1. Feature System**

#### Feature Registry (`class-wps-feature-registry.php`)
Central registry that manages all features:

```php
// Register a feature
register_WPSHADOW_feature( new WPSHADOW_Feature_Custom() );

// Check if feature is enabled
if ( has_WPSHADOW_feature( 'custom' ) ) {
    // Feature is active
}

// Enable/disable feature
WPSHADOW_Feature_Registry::set_feature_enabled( 'custom', true );
```

#### Abstract Feature Class (`class-wps-feature-abstract.php`)
Base class for all features:

```php
final class WPSHADOW_Feature_Custom extends WPSHADOW_Abstract_Feature {
    public function register_hooks(): void {
        add_action( 'wp_loaded', [ $this, 'initialize' ] );
    }
    
    public function initialize(): void {
        // Feature initialization
    }
}
```

**Naming Convention**:
- Files: `class-wps-feature-{name}.php`
- Classes: `WPSHADOW_Feature_{CamelCase}`
- Namespace: `WPShadow\CoreSupport`

### **2. Settings System**

#### Settings Manager (`class-wps-settings.php`)
Hierarchical settings with network fallback:

```php
// Get setting with network fallback
$value = WPSHADOW_Settings::get( 'feature_id', 'setting_key' );

// Update setting
WPSHADOW_Settings::update( 'feature_id', 'setting_key', $value );

// Delete setting
WPSHADOW_Settings::delete( 'feature_id', 'setting_key' );
```

#### Multisite Inheritance
Network administrators can set defaults; individual sites can override:

```php
// In helpers: get_option_with_network_fallback()
$value = get_option_with_network_fallback( 
    'wpshadow_setting_key',
    null,  // site value
    true   // fall back to network
);
```

### **3. Admin Interface**

#### Dashboard Widgets (`class-wps-dashboard-widgets.php`)
- Performance metrics display
- Recent activity log
- Health status summary
- Database statistics

#### Feature Details Page (`class-wps-feature-details-page.php`)
- Feature enablement toggles
- Feature-specific settings UI
- Activity logging
- Allow-list management (for features like Asset Version Removal)

#### View Components (`class-wps-view-components.php`)
Reusable UI rendering:

```php
$renderer = new WPSHADOW_View_Components();
$renderer->render_health_badge( $score );
$renderer->render_alert( $message, 'warning' );
$renderer->render_feature_row( $name, $status );
```

### **4. Health System**

#### Site Health Integration (`class-wps-site-health.php`)
Registers health checks with WordPress Site Health:

```php
// Each feature can contribute health check
add_filter( 'site_status_tests', function( $tests ) {
    $tests['direct'][] = [
        'test' => 'wpshadow_feature_check',
    ];
    return $tests;
});
```

#### Health Renderer (`class-wps-health-renderer.php`)
Centralized health UI rendering:

```php
$score = 85;
$class = $renderer->get_score_class( $score );    // 'good', 'warning', etc.
$color = $renderer->get_score_color( $score );    // Hex color
$label = $renderer->get_score_label( $score );    // Localized label
```

### **5. REST API**

#### Base Controller (`class-wps-rest-controller-base.php`)
Abstract base for all REST endpoints:

```php
protected function check_permission( string $capability ): bool | WP_Error
protected function validate_slug( string $slug ): string | WP_Error  
protected function check_rate_limit( string $operation ): bool | WP_Error
protected function success_response( array $data, string $message = '' ): WP_REST_Response
protected function error_response( string $code, string $message, int $status ): WP_Error
```

#### Settings Controller (`class-wps-rest-settings-controller.php`)
Handles settings CRUD operations:

- `GET /wp-json/wpshadow/v1/settings` - Retrieve settings
- `POST /wp-json/wpshadow/v1/settings` - Update settings
- `POST /wp-json/wpshadow/v1/settings/reset` - Reset to defaults
- `GET /wp-json/wpshadow/v1/health` - Health check

---

## Data Flow

### **Feature Initialization Flow**

```
wpshadow_init()
├── Load core classes (requires_once)
├── Fire: wpshadow_core_loaded hook
├── Register features (WPSHADOW_register_core_features)
│   └── For each feature:
│       ├── Instantiate WPSHADOW_Feature_* class
│       ├── Call register_hooks()
│       └── Fire: wpshadow_feature_registered hook
├── Initialize active features
│   └── Call initialize() for enabled features
└── Fire: wpshadow_initialized hook
```

### **Settings Retrieval Flow**

```
WPSHADOW_Settings::get('feature_id', 'key')
├── Check request cache (static)
├── Build option name: wpshadow_feature_id_key
├── get_option() - site level
├── If multisite && empty:
│   └── get_site_option() - network level
└── Return value with cache
```

### **Admin Request Flow**

```
WordPress Admin Load
├── Enqueue dashboard assets (wpshadow-dashboard-widgets.css, admin.js)
├── Output dashboard widgets
│   ├── Render via class-wps-dashboard-widgets.php
│   ├── Fetch data via WPSHADOW_Site_Health methods
│   └── Use WPSHADOW_View_Components for UI
├── Load feature details page on feature.php?page=wpshadow-feature-details
│   ├── Check nonce (wpshadow_feature_details)
│   ├── Render settings form
│   ├── Handle POST (save settings)
│   └── Output activity log
└── AJAX handlers (jQuery)
    ├── Toggle feature enabled/disabled
    ├── Update individual settings
    └── Refresh activity log
```

---

## Security Patterns

### **Nonce Verification**
All state-changing operations use nonces:

```php
// In form
wp_nonce_field( 'wpshadow_feature_details', 'nonce' );

// In handler
check_ajax_referer( 'wpshadow_feature_details', 'nonce' );
```

### **Capability Checking**
All admin operations require `manage_options`:

```php
if ( ! current_user_can( 'manage_options' ) ) {
    wp_send_json_error( 'Permission denied' );
}
```

### **Input Sanitization**
All user input is sanitized:

```php
$feature_id = sanitize_key( $_POST['feature_id'] ?? '' );
$patterns = sanitize_textarea_field( $_POST['patterns'] ?? '' );
$enable = rest_sanitize_boolean( $_POST['enable'] ?? false );
```

### **Output Escaping**
All output is escaped:

```php
echo esc_html( $text );           // HTML context
echo esc_url( $link );            // URL context
echo esc_attr( $attribute );      // HTML attribute
echo wp_kses_post( $html );       // Allow safe HTML
```

---

## Developer Extensibility

### **Developer Filters**

#### Asset Version Removal Filters

```php
// Override preserve logic
apply_filters( 'wpshadow_asset_version_preserve', $preserve, $src, $type )

// Override CSS allow patterns
apply_filters( 'wpshadow_asset_version_allow_css_patterns', $patterns )

// Override JS allow patterns
apply_filters( 'wpshadow_asset_version_allow_js_patterns', $patterns )

// Override selected plugins
apply_filters( 'wpshadow_asset_version_selected_plugins', $slugs )
```

#### Core Hooks

```php
// Fires after core is loaded
do_action( 'wpshadow_core_loaded' )

// Fires after all features registered
do_action( 'wpshadow_features_registered' )

// Fires when feature registered
do_action( 'wpshadow_feature_registered', $feature_id, $feature_class )

// Fires after initialization complete
do_action( 'wpshadow_initialized' )

// Fires when settings saved
do_action( 'wpshadow_settings_saved', $feature_id, $settings )
```

### **Adding Custom Hooks**

Feature developers should add strategic hooks:

```php
// Allow customization of feature behavior
$result = apply_filters( 'wpshadow_' . $this->feature_id . '_process', $data );

// Allow logging of actions
do_action( 'wpshadow_' . $this->feature_id . '_action', $action_type, $details );

// Allow validation before save
$valid = apply_filters( 'wpshadow_validate_' . $this->feature_id, true, $settings );
```

---

## WordPress.org Compliance

### **Required Restrictions**

The free plugin must strictly comply with WordPress.org guidelines:

✅ **Allowed**:
- GPL v2+ licensing
- WordPress.org API calls (updates)
- Optional external services (user opt-in)
- Documentation links to website

❌ **NOT Allowed**:
- External API calls without user consent
- Aggressive upgrade nags or fake warnings
- Loading CSS/JS from CDN
- Obfuscated or encoded code
- Affiliate links
- Phone-home or tracking without opt-in

### **Code Standards**

- Use `WordPress-Extra` PHPCS standard
- Add return type hints (PHP 8.1+)
- Use strict_types declaration: `declare(strict_types=1);`
- PSR-4 namespacing: `WPShadow\CoreSupport`
- All strings translatable: `__()`, `_e()`, `esc_html_e()`

### **Accessibility**

- Use WordPress dashicons for UI icons
- Semantic HTML structure
- ARIA labels for interactive elements
- Color contrast compliance (WCAG AA minimum)

---

## Testing

### **Running Tests**

```bash
# Run all tests
composer test

# Run specific test file
composer test -- tests/test-feature-registry.php

# Run with coverage
composer test -- --coverage-html=coverage/
```

### **Code Quality Checks**

```bash
# PHPCS standards
composer phpcs

# Fix standards automatically
composer phpcbf

# PHPStan static analysis (Level 8)
composer phpstan
```

### **Required Test Coverage**

- All new functions must have unit tests
- Bug fixes should have regression tests
- Critical paths (settings, health) require integration tests
- REST endpoints require functional tests

---

## Module Integration

WPShadow features can integrate with optional module packages:

### **Module Pattern**

```php
// In feature initialize()
if ( class_exists( 'Module_Custom_Class' ) ) {
    // Use module functionality
    Module_Custom_Class::register_hooks();
}
```

### **Available Modules**

- `module-login-wpshadow` - Authentication/OAuth integration
- `module-vault-wpshadow` - Advanced backup/staging
- `module-license-wpshadow` - Premium licensing
- [See full module list in docs/]

---

## Performance Considerations

### **Request-Level Caching**

Features use static variables for request-level caching:

```php
public function matches_allow_patterns( $url, $patterns ): bool {
    static $cache = [];
    
    if ( isset( $cache[ $url ] ) ) {
        return $cache[ $url ];
    }
    
    $result = $this->expensive_matching_logic( $url, $patterns );
    $cache[ $url ] = $result;
    
    return $result;
}
```

### **Option Caching**

Network-aware options use transient caching:

```php
$value = get_transient( 'wpshadow_feature_data' );
if ( false === $value ) {
    $value = expensive_calculation();
    set_transient( 'wpshadow_feature_data', $value, 1 DAY_IN_SECONDS );
}
```

### **Database Query Optimization**

- Use `$wpdb->prepare()` for all queries
- Avoid N+1 queries in loops
- Use `wp_cache_set()` for repeated queries
- Consider batch operations for bulk updates

---

## Debugging

### **Error Logging**

The plugin logs to `wp-content/debug.log` when `WP_DEBUG` is enabled:

```php
if ( WP_DEBUG ) {
    error_log( '[WPShadow] Operation result: ' . $result );
}
```

### **Debug Bar Integration**

Features can integrate with WordPress Debug Bar:

```php
do_action( 'qm/debug', 'wpshadow_feature_data', $data );
```

### **Health Check Debugging**

The Site Health page shows feature health status and messages:

- Check WordPress Admin > Tools > Site Health
- Feature issues appear in "Critical" or "Recommended" sections
- Hover for detailed descriptions

---

## Troubleshooting

### **Feature Not Loading**

1. Check feature is registered in `wpshadow.php` (line ~730-931)
2. Verify `require_once` statement exists
3. Check feature is enabled: Settings → WPShadow → Feature name
4. Verify `register_hooks()` is called

### **Settings Not Saving**

1. Check nonce: `check_ajax_referer()` in handler
2. Verify `manage_options` capability
3. Check WordPress core `update_option()` returns true
4. Look for PHP errors in debug.log

### **REST API 403 Errors**

1. Verify user has `manage_options` capability
2. Check REST authentication (cookie/token)
3. Verify rate limit not exceeded (10 requests per 5 min default)
4. Check firewall isn't blocking `/wp-json/` routes

---

## Contributing

When contributing to WPShadow:

1. Follow this architecture guide
2. Use established patterns (feature registry, settings, health)
3. Add strategic hooks for extensibility
4. Write tests for new functionality
5. Run `composer phpcs` and `composer phpstan` before submitting
6. Update relevant documentation

---

## Resources

- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [PHPStan Handbook](https://phpstan.org/)
- [Site Health API](https://developer.wordpress.org/plugins/wordpress-org/how-your-plugin-gets-reviewed/#plugin-requirements)

---

**Last Updated**: January 2026  
**Maintained by**: WPShadow Development Team
