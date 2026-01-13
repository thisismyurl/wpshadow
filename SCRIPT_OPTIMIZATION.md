# Script Loading Optimization System

This document describes the Script Loading Optimization System features added to WP Support (thisismyurl).

## Overview

The Script Loading Optimization System provides a comprehensive set of features to improve website performance by optimizing how JavaScript and CSS files are loaded.

## Features

### 1. Enhanced Script Deferral

**Location:** Performance → Features → Enable "Script Deferral System"

**What it does:**
- Defers non-critical JavaScript to load after HTML parsing
- Supports auto-detection mode (defers all local scripts automatically)
- Supports manual mode (defer only specified scripts)
- Automatically excludes critical scripts (jQuery, jQuery Migrate)

**Configuration:**
```php
// Set deferral mode: 'auto', 'manual', or 'disabled'
update_option( 'wps_defer_mode', 'auto' );

// Add custom exclusions (in addition to default jQuery exclusions)
update_option( 'wps_defer_excluded_handles', array( 'my-critical-script' ) );

// For manual mode: specify which scripts to defer
update_option( 'wps_defer_script_handles', array( 'script-to-defer-1', 'script-to-defer-2' ) );
```

**Filters:**
```php
// Customize excluded handles
add_filter( 'wps_defer_excluded_handles', function( $excluded ) {
    $excluded[] = 'my-custom-script';
    return $excluded;
} );
```

### 2. Conditional Script Loading

**Location:** Performance → Features → Enable "Conditional Script Loading"

**What it does:**
- Loads plugin scripts only on pages where they are needed
- Reduces page weight by removing unnecessary scripts
- Supports page ID, slug, and pattern matching

**Configuration:**
```php
// Define conditional loading rules
$rules = array(
    array(
        'plugin'  => 'contact-form-7',
        'pages'   => array( 'contact', 123 ), // Slug or page ID
        'handles' => array( 'contact-form-7', 'wpcf7-recaptcha' ),
    ),
    array(
        'plugin'  => 'woocommerce',
        'pages'   => array( 'shop' ), // Special pattern
        'handles' => array( 'woocommerce', 'wc-cart-fragments' ),
    ),
);
update_option( 'wps_conditional_loading_rules', $rules );
```

**Special Page Patterns:**
- `'*'` - Load everywhere
- `'home'` - Front page only
- `'shop'` - WooCommerce shop pages
- `'archive'` - Archive pages
- `'single'` - Single posts
- `'page'` - All pages

**Filters:**
```php
// Customize conditional loading rules
add_filter( 'wps_conditional_loading_rules', function( $rules ) {
    // Add your custom rules
    return $rules;
} );
```

### 3. Google Fonts Disabler

**Location:** Performance → Features → Enable "Disable Google Fonts"

**What it does:**
- Removes all Google Fonts from the website
- Improves privacy (no requests to Google servers)
- Reduces external dependencies and load times
- Useful when themes load Google Fonts but you prefer system fonts

**No configuration needed** - simply enable the feature.

### 4. Critical CSS Inline

**Location:** Performance → Features → Enable "Inline Critical CSS"

**What it does:**
- Inlines above-the-fold CSS in the page head
- Defers full stylesheets to load after page render
- Improves perceived performance with instant rendering

**Configuration:**
```php
// Set critical CSS (above-the-fold styles)
update_option( 'wps_critical_css', 'body { margin: 0; } .header { display: block; }' );

// Specify which stylesheets to defer (optional)
update_option( 'wps_defer_stylesheets', array( 'my-theme-style' ) );

// Enable auto-defer for all non-critical CSS
update_option( 'wps_auto_defer_css', true );
```

**Filters:**
```php
// Customize critical CSS
add_filter( 'wps_critical_css', function( $css ) {
    // Modify or replace critical CSS
    return $css;
} );

// Customize deferred stylesheets
add_filter( 'wps_defer_stylesheets', function( $styles ) {
    $styles[] = 'another-style';
    return $styles;
} );
```

### 5. Enhanced Resource Hints & Preload

**Location:** Performance → Features → Enable "DNS Prefetch & Resource Hints Management"

**What it does:**
- Preloads critical resources (fonts, scripts, images)
- Improves loading of key assets
- Supports DNS prefetch and preconnect

**Configuration:**
```php
// Define resources to preload
$resources = array(
    array(
        'url'  => '/wp-content/themes/my-theme/fonts/main-font.woff2',
        'type' => 'font',
        'mime_type' => 'font/woff2', // For fonts
    ),
    array(
        'url'  => '/wp-content/themes/my-theme/js/critical.js',
        'type' => 'script',
    ),
    array(
        'url'  => '/wp-content/themes/my-theme/images/hero.jpg',
        'type' => 'image',
    ),
);
update_option( 'wps_preload_resources', $resources );

// Add custom DNS prefetch hints
update_option( 'wps_custom_resource_hints', array(
    'https://cdn.example.com',
    'https://analytics.example.com',
) );
```

**Filters:**
```php
// Customize preload resources
add_filter( 'wps_preload_resources', function( $resources ) {
    // Add or modify resources
    return $resources;
} );
```

### 6. Script Optimization Analyzer

**Location:** Performance → Features → Enable "Script Optimization Analyzer"

**What it does:**
- Analyzes enqueued scripts across pages
- Provides optimization recommendations
- Identifies conditional loading opportunities
- Tracks optimization statistics

**Usage:**
```php
// Get optimization suggestions
$feature = WPS\CoreSupport\Features\WPS_Feature_Script_Optimizer;
$suggestions = $feature->get_optimization_suggestions();

// Get optimization statistics
$stats = $feature->get_optimization_stats();
echo "Total scripts: " . $stats['total_scripts'];
echo "Deferred scripts: " . $stats['deferred_scripts'];
echo "Estimated savings: " . $stats['estimated_savings'] . " KB";
```

The analyzer automatically collects data when logged-in admins visit pages. Data is stored for 7 days for analysis.

## Best Practices

1. **Test in staging first** - Always test optimizations in a staging environment before applying to production.

2. **Start with conservative settings** - Enable features one at a time to identify any conflicts.

3. **Monitor for JavaScript errors** - Check browser console after enabling deferral to ensure no dependencies are broken.

4. **Use manual mode for complex sites** - If auto-defer causes issues, switch to manual mode and defer scripts selectively.

5. **Exclude jQuery by default** - jQuery and its dependencies should always be excluded from deferral unless you're certain they're not needed immediately.

6. **Test mobile performance** - Script optimization often has a larger impact on mobile devices.

7. **Measure performance impact** - Use tools like Google PageSpeed Insights or WebPageTest to measure improvements.

## Troubleshooting

### JavaScript not working after enabling deferral

**Solution:** Add the problematic script to the exclusion list:
```php
add_filter( 'wps_defer_excluded_handles', function( $excluded ) {
    $excluded[] = 'problematic-script-handle';
    return $excluded;
} );
```

### Styles not loading correctly with Critical CSS

**Solution:** Ensure critical CSS includes all above-the-fold styles, or disable auto-defer and manually specify which styles to defer.

### Conditional loading removing scripts from needed pages

**Solution:** Review your page patterns and ensure they match correctly. Use specific page IDs instead of patterns for precision.

### Google Fonts still appearing

**Solution:** Some themes hard-code Google Fonts in templates. Check your theme files and remove manual font links.

## Filters Reference

All optimization features support filtering for customization:

- `wps_defer_excluded_handles` - Exclude scripts from deferral
- `wps_defer_script_handles` - Scripts to defer (manual mode)
- `wps_conditional_loading_rules` - Conditional loading rules
- `wps_critical_css` - Critical CSS content
- `wps_defer_stylesheets` - Stylesheets to defer
- `wps_preload_resources` - Resources to preload

## Performance Impact

Expected improvements with optimizations enabled:

- **Render time:** -200ms to -1s (defer + inline CSS)
- **Page size:** -50KB to -200KB (conditional loading)
- **Caching:** +10-20% cache hit rate (no query strings)
- **Perceived performance:** Faster above-the-fold render

## Support

For issues or questions, please visit:
- Plugin repository: https://github.com/thisismyurl/plugin-wp-support-thisismyurl
- Support: https://thisismyurl.com/support
