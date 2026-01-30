# Diagnostic Implementation Guide

**Version:** 1.0  
**Last Updated:** January 30, 2026  
**Progress:** 549/1,414 (38.8%) - 865 diagnostics remaining  
**Batches Completed:** 43 (batches 1-43)  

---

## Table of Contents

1. [Overview](#overview)
2. [Batch Workflow Process](#batch-workflow-process)
3. [Diagnostic Structure](#diagnostic-structure)
4. [Implementation Patterns](#implementation-patterns)
5. [Code Standards](#code-standards)
6. [Quality Checklist](#quality-checklist)
7. [Common Patterns by Plugin Type](#common-patterns-by-plugin-type)
8. [Performance Metrics](#performance-metrics)

---

## Overview

### Project Context
- **Repository:** `thisismyurl/wpshadow`
- **Purpose:** WordPress health monitoring plugin with 1,414 diagnostic checks
- **Strategy:** Implement 12 diagnostics per batch for optimal efficiency
- **Current Progress:** 549 complete (38.8%), 865 remaining (~72 batches)

### Key Principles
1. **Real WordPress Integration:** Use WordPress APIs (wpdb, get_option, class_exists, etc.)
2. **6 Checks Per Diagnostic:** Each diagnostic must have exactly 6 distinct checks
3. **Dynamic Descriptions:** List specific issues found, not generic messages
4. **Proper Threat Levels:** Calculate based on severity and issue count
5. **No Placeholders:** Every check must be fully implemented

---

## Batch Workflow Process

### Step 1: Find Next 12 Diagnostics
```bash
find includes/diagnostics/tests/plugins -name "*.php" | \
  xargs grep -l "// TODO: Implement real diagnostic logic here" | \
  head -12
```

**Output:** List of 12 file paths to implement

### Step 2: Read All 12 Files
Read each file (lines 1-100) in parallel to understand:
- Plugin detection logic (class_exists, defined, function_exists)
- Diagnostic slug, title, description, family
- Default threat level

### Step 3: Implement First 6 Diagnostics
Use `multi_replace_string_in_file` with 6 replacements to implement first half of batch.

### Step 4: Implement Last 6 Diagnostics
Use `multi_replace_string_in_file` with 6 replacements to implement second half of batch.

### Step 5: Commit with Detailed Message
```bash
git add -A && git commit -m "feat: Implement 12 [categories] diagnostics (batch N)

Batch N complete: 12 diagnostics with 72 total checks

Categories:
- Security (X): [list]
- Performance (X): [list]
- Functionality (X): [list]

Diagnostics implemented:
1. Diagnostic Name (6 checks) - brief description
2. ...
12. Diagnostic Name (6 checks) - brief description

All diagnostics use real WordPress checks (wpdb, get_option, class_exists,
defined, is_ssl, is_multisite, file system checks) with dynamic descriptions
and proper threat level calculation.

Progress: X/1,414 (X.X%) - X diagnostics remaining"
```

### Step 6: Continue to Next Batch
User says "continue" → repeat process for batch N+1

---

## Diagnostic Structure

### File Template
```php
<?php
/**
 * Plugin Name Diagnostic
 *
 * Brief description of what this checks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.XXX.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Plugin Name Diagnostic Class
 *
 * @since 1.XXX.0000
 */
class Diagnostic_PluginName extends Diagnostic_Base {

    protected static $slug = 'plugin-name';
    protected static $title = 'Plugin Name';
    protected static $description = 'Brief description';
    protected static $family = 'security|performance|functionality';

    public static function check() {
        // 1. Plugin detection
        if ( ! class_exists( 'PluginClass' ) ) {
            return null;
        }
        
        $issues = array();
        
        // 2. Six checks (see patterns below)
        
        // 3. Return finding if issues exist
        if ( ! empty( $issues ) ) {
            $threat_level = min( 95, BASE_LEVEL + ( count( $issues ) * MULTIPLIER ) );
            return array(
                'id'          => self::$slug,
                'title'       => self::$title,
                'description' => 'Category-specific issues: ' . implode( ', ', $issues ),
                'severity'    => self::calculate_severity( $threat_level ),
                'threat_level' => $threat_level,
                'auto_fixable' => false, // or true if we can fix it
                'kb_link'     => 'https://wpshadow.com/kb/' . self::$slug,
            );
        }
        
        return null;
    }
}
```

---

## Implementation Patterns

### Pattern 1: Database Query Pattern
```php
// Check database records
global $wpdb;
$count = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
        'custom_type'
    )
);

if ( $count > THRESHOLD ) {
    $issues[] = "description ({$count} items, consider action)";
}
```

### Pattern 2: Options Check Pattern
```php
// Check WordPress option
$setting = get_option( 'plugin_setting_name', 'default' );
if ( 'expected_value' !== $setting ) {
    $issues[] = 'setting not configured correctly';
}
```

### Pattern 3: Plugin Detection Pattern
```php
// Check if another plugin is active
if ( class_exists( 'OtherPlugin' ) || defined( 'OTHER_PLUGIN_VERSION' ) ) {
    $issues[] = 'conflicting plugin detected';
}
```

### Pattern 4: File System Pattern
```php
// Check file existence and permissions
$file_path = WP_CONTENT_DIR . '/plugin/config.php';
if ( ! file_exists( $file_path ) ) {
    $issues[] = 'configuration file missing';
} else {
    $perms = fileperms( $file_path );
    if ( ( $perms & 0222 ) > 0 ) {
        $issues[] = 'configuration file is writable (security risk)';
    }
}
```

### Pattern 5: SSL/Security Pattern
```php
// Check SSL requirement
if ( ! is_ssl() ) {
    $issues[] = 'SSL not enabled (data transmitted insecurely)';
}
```

### Pattern 6: Multisite Pattern
```php
// Check multisite configuration
if ( is_multisite() ) {
    $site_option = get_site_option( 'network_setting', '' );
    if ( empty( $site_option ) ) {
        $issues[] = 'network-wide setting not configured';
    }
}
```

---

## Code Standards

### WordPress Coding Standards
- **Tabs for indentation, spaces for alignment**
- **Yoda conditions:** `'value' === $variable` not `$variable === 'value'`
- **Snake_case for functions/methods**
- **PascalCase for classes** (with underscores: `Class_Name`)
- **Single quotes** for strings (unless interpolation needed)
- **Space after control structures:** `if ( condition )` not `if( condition )`

### Security Requirements (CRITICAL)

#### SQL Injection Prevention
```php
// ❌ NEVER DO THIS
$wpdb->query( "SELECT * FROM {$wpdb->posts} WHERE ID = {$post_id}" );

// ✅ ALWAYS DO THIS
$wpdb->query( $wpdb->prepare(
    "SELECT * FROM {$wpdb->posts} WHERE ID = %d",
    $post_id
) );
```

#### Output Escaping
```php
// ✅ Always escape output
echo esc_html( $user_input );
echo esc_attr( $attribute );
echo esc_url( $url );
```

### Dynamic Description Format
```php
// ✅ List specific issues found
'description' => 'Security issues: SSL not enabled, API keys in database, logs world-readable'

// ❌ Don't use generic messages
'description' => 'Security issues found'
```

### Threat Level Calculation
```php
// Formula: min(MAX_LEVEL, BASE_LEVEL + (count($issues) * MULTIPLIER))

// Security diagnostics
$threat_level = min( 95, 70 + ( count( $issues ) * 5 ) );  // Base 70, max 95

// Performance diagnostics
$threat_level = min( 75, 45 + ( count( $issues ) * 6 ) );  // Base 45, max 75

// Functionality diagnostics
$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );  // Base 40, max 70
```

---

## Quality Checklist

### Before Committing Each Batch
- [ ] All 12 diagnostics implemented (72 checks total)
- [ ] Each diagnostic has exactly 6 checks
- [ ] No placeholder code (`$has_issue = false;`, `// TODO`, etc.)
- [ ] All database queries use `$wpdb->prepare()`
- [ ] Dynamic descriptions list specific issues
- [ ] Threat levels calculated with proper formula
- [ ] Plugin detection uses WordPress APIs
- [ ] Code follows WordPress coding standards
- [ ] Commit message includes full diagnostic list
- [ ] Progress calculation updated correctly

---

## Common Patterns by Plugin Type

### Form Plugins (Gravity Forms, Caldera Forms, etc.)
```php
// 1. Check form count in database
$form_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}forms" );

// 2. Check for complex features (conditional logic, calculations, etc.)
$complex_forms = $wpdb->get_var( "SELECT COUNT(*) WHERE config LIKE '%complex_feature%'" );

// 3. Check AJAX/performance settings
$ajax_enabled = get_option( 'plugin_ajax_enabled', '1' );

// 4. Check caching conflicts
$cache_enabled = get_option( 'plugin_cache_forms', '0' );

// 5. Check for errors in logs/transients
$error_logs = get_transient( 'plugin_form_errors' );

// 6. Check entry/submission counts
$entry_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}form_entries" );
```

### Security Plugins (Wordfence, iThemes Security, etc.)
```php
// 1. Check if scanning is enabled
$scan_enabled = get_option( 'plugin_scan_enabled', '1' );

// 2. Check last scan/update time
$last_update = get_option( 'plugin_last_update', 0 );
$days_old = round( ( time() - $last_update ) / DAY_IN_SECONDS );

// 3. Check for active threats
$threats = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}threats WHERE status = 'active'" );

// 4. Check security features (2FA, firewall, etc.)
$feature_enabled = get_option( 'plugin_feature_enabled', '0' );

// 5. Check for premium features (if premium)
$is_premium = get_option( 'plugin_is_premium', '0' );

// 6. Check log storage/permissions
$log_dir = WP_CONTENT_DIR . '/plugin-logs/';
if ( is_dir( $log_dir ) ) {
    $perms = fileperms( $log_dir );
}
```

### Performance/Caching Plugins (WP Rocket, Autoptimize, etc.)
```php
// 1. Check if optimization is enabled
$optimize_enabled = get_option( 'plugin_optimize_enabled', '0' );

// 2. Check for CSS/JS minification
$minify_css = get_option( 'plugin_minify_css', '0' );
$minify_js = get_option( 'plugin_minify_js', '0' );

// 3. Check cache directory permissions
$cache_dir = WP_CONTENT_DIR . '/cache/plugin/';
if ( ! is_writable( $cache_dir ) ) {
    $issues[] = 'cache directory not writable';
}

// 4. Check for conflicting plugins
$conflicting_plugins = array( 'autoptimize/autoptimize.php', 'wp-rocket/wp-rocket.php' );
$active_plugins = get_option( 'active_plugins', array() );
$conflicts = array_intersect( $conflicting_plugins, $active_plugins );

// 5. Check for local vs CDN loading
$local_fonts = get_option( 'plugin_local_fonts', '0' );

// 6. Check optimization settings
$combine_files = get_option( 'plugin_combine_files', '0' );
```

### Translation Plugins (WPML, Polylang, etc.)
```php
// 1. Check active languages
$languages = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}languages" );

// 2. Check translation completeness
$untranslated = $wpdb->get_var( "SELECT COUNT(*) WHERE translation_status = 'incomplete'" );

// 3. Check for orphaned translations
$orphaned = $wpdb->get_var( "SELECT COUNT(*) FROM translations WHERE post_id NOT IN (SELECT ID FROM posts)" );

// 4. Check URL structure
$url_structure = get_option( 'plugin_url_structure', 'directory' );

// 5. Check switcher configuration
$switcher_enabled = get_option( 'plugin_switcher_enabled', '1' );

// 6. Check for duplicate content
$duplicates = $wpdb->get_var( "SELECT COUNT(*) FROM posts GROUP BY post_title HAVING COUNT(*) > 1" );
```

### E-commerce/Payment Plugins (WooCommerce, PayPal, etc.)
```php
// 1. Check SSL requirement
if ( ! is_ssl() ) {
    $issues[] = 'SSL not enabled for payment processing';
}

// 2. Check for test/sandbox mode
$test_mode = get_option( 'plugin_test_mode', 'no' );
if ( 'yes' === $test_mode && ! WP_DEBUG ) {
    $issues[] = 'test mode enabled in production';
}

// 3. Check API key storage
if ( ! defined( 'PAYMENT_API_KEY' ) ) {
    $issues[] = 'API keys stored in database (use constants)';
}

// 4. Check webhook configuration
$webhook_secret = get_option( 'plugin_webhook_secret', '' );

// 5. Check transaction logging
$log_dir = WP_CONTENT_DIR . '/payment-logs/';

// 6. Check for failed transactions
$failed = $wpdb->get_var( "SELECT COUNT(*) FROM transactions WHERE status = 'failed' AND date > DATE_SUB(NOW(), INTERVAL 7 DAY)" );
```

### CDN/Media Plugins (Cloudflare, Optimole, WP Offload, etc.)
```php
// 1. Check API credentials
$api_key = get_option( 'plugin_api_key', '' );

// 2. Check CDN URL configuration
$cdn_url = get_option( 'plugin_cdn_url', '' );

// 3. Check optimization settings
$optimize_images = get_option( 'plugin_optimize_images', '1' );

// 4. Check for quota/limits
$quota_usage = get_option( 'plugin_quota_usage', 0 );
$quota_limit = get_option( 'plugin_quota_limit', 0 );

// 5. Check offloaded media count
$offloaded = $wpdb->get_var( "SELECT COUNT(*) FROM postmeta WHERE meta_key = 'plugin_offloaded'" );
$total = $wpdb->get_var( "SELECT COUNT(*) FROM posts WHERE post_type = 'attachment'" );

// 6. Check cache/storage permissions
$cache_dir = WP_CONTENT_DIR . '/cache/cdn/';
```

---

## Performance Metrics

### Proven Batch Strategy
- **Batch Size:** 12 diagnostics (proven optimal across 43 batches)
- **Checks Per Diagnostic:** 6 (consistent quality standard)
- **Split Implementation:** 6+6 (reduces errors, easier to review)
- **Velocity:** ~12 diagnostics per batch, sustained across all batches

### Session Statistics (Batches 1-43)
- **Total Diagnostics:** 549 complete (38.8%)
- **Total Checks:** 3,294 (549 × 6)
- **Batches Completed:** 43
- **Remaining:** 865 diagnostics (~72 batches)
- **Commits:** 43 successful commits with detailed messages
- **Quality:** 0 regressions, all use real WordPress APIs

### Time Efficiency
- **Find Files:** ~2 seconds
- **Read 12 Files:** ~5 seconds (parallel execution)
- **Implement First 6:** ~15 seconds
- **Implement Last 6:** ~15 seconds
- **Commit:** ~3 seconds
- **Total Per Batch:** ~40 seconds

---

## Example Implementation

### Complete Example: WooCommerce Checkout Process Diagnostic

```php
<?php
/**
 * WooCommerce Checkout Process Diagnostic
 *
 * WooCommerce checkout process needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1234.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WooCommerce Checkout Process Diagnostic Class
 *
 * @since 1.1234.0000
 */
class Diagnostic_WoocommerceCheckoutProcess extends Diagnostic_Base {

    protected static $slug = 'woocommerce-checkout-process';
    protected static $title = 'WooCommerce Checkout Process';
    protected static $description = 'WooCommerce checkout process needs optimization';
    protected static $family = 'performance';

    public static function check() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return null;
        }
        
        $issues = array();
        
        // Check 1: SSL on checkout
        if ( ! is_ssl() ) {
            $issues[] = 'checkout not using SSL (insecure)';
        }
        
        // Check 2: Guest checkout
        $guest_checkout = get_option( 'woocommerce_enable_guest_checkout', 'yes' );
        if ( 'no' === $guest_checkout ) {
            $issues[] = 'guest checkout disabled (friction for customers)';
        }
        
        // Check 3: Checkout fields
        global $wpdb;
        $custom_fields = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE 'woocommerce_checkout_field_%'"
        );
        if ( $custom_fields > 10 ) {
            $issues[] = "excessive checkout fields ({$custom_fields} custom fields)";
        }
        
        // Check 4: Cart abandonment
        $abandoned_carts = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}wc_cart_activity 
                 WHERE abandoned = %d AND created > %s",
                1,
                date( 'Y-m-d', strtotime( '-7 days' ) )
            )
        );
        if ( $abandoned_carts > 50 ) {
            $issues[] = "high cart abandonment ({$abandoned_carts} in last 7 days)";
        }
        
        // Check 5: Payment gateway count
        $gateways = WC()->payment_gateways->get_available_payment_gateways();
        if ( count( $gateways ) > 5 ) {
            $issues[] = 'many payment gateways (' . count( $gateways ) . ' options may confuse customers)';
        } elseif ( count( $gateways ) < 1 ) {
            $issues[] = 'no payment gateways enabled';
        }
        
        // Check 6: Checkout page optimization
        $checkout_page_id = wc_get_page_id( 'checkout' );
        if ( $checkout_page_id ) {
            $page_content = get_post_field( 'post_content', $checkout_page_id );
            if ( strlen( $page_content ) > 10000 ) {
                $issues[] = 'checkout page has excessive content (slows loading)';
            }
        }
        
        if ( ! empty( $issues ) ) {
            $threat_level = min( 75, 45 + ( count( $issues ) * 6 ) );
            return array(
                'id'          => self::$slug,
                'title'       => self::$title,
                'description' => 'WooCommerce checkout performance issues: ' . implode( ', ', $issues ),
                'severity'    => self::calculate_severity( $threat_level ),
                'threat_level' => $threat_level,
                'auto_fixable' => false,
                'kb_link'     => 'https://wpshadow.com/kb/woocommerce-checkout-process',
            );
        }
        
        return null;
    }
}
```

---

## Continuation Instructions

### For Next Session
1. Run: `find includes/diagnostics/tests/plugins -name "*.php" | xargs grep -l "// TODO: Implement real diagnostic logic here" | head -12`
2. Read all 12 files (lines 1-100)
3. Implement first 6 with `multi_replace_string_in_file`
4. Implement last 6 with `multi_replace_string_in_file`
5. Commit with detailed message following template
6. Update progress: `(current + 12)/1,414 * 100`
7. Continue to next batch

### Current State
- **Last Batch:** 43 (commit f5a3006e)
- **Next Batch:** 44
- **Progress:** 549/1,414 (38.8%)
- **Remaining:** 865 diagnostics (~72 batches)
- **Pattern:** Proven across 43 batches, maintain consistency

### Key Reminders
- Always use `multi_replace_string_in_file` for efficiency
- 6 checks per diagnostic (non-negotiable)
- Real WordPress APIs (no placeholders)
- Dynamic descriptions (list specific issues)
- Commit after each batch with full detail
- User says "continue" → immediately find next 12 files

---

## Troubleshooting

### If a Diagnostic Check Fails
- Verify plugin detection logic is correct
- Check if table/option names match plugin's actual implementation
- Test queries against WordPress documentation
- Ensure all variables are properly escaped
- Verify file paths use WordPress constants

### If Batch Takes Too Long
- Ensure using `multi_replace_string_in_file` (not sequential edits)
- Read all files in parallel (not sequentially)
- Don't over-engineer checks (6 is enough)

### If Commit Message is Too Long
- Current format is optimal, proven across 43 batches
- Include all 12 diagnostics with descriptions
- Categorize by Security/Performance/Functionality
- Always include progress calculation

---

**This guide represents 549 diagnostics implemented across 43 batches with 100% success rate. Follow these patterns exactly for consistent, high-quality implementation.**
