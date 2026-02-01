# Phase 3 Optimization - Developer Quick Reference

## Quick Links
- **Full Documentation:** [PHASE_3_OPTIMIZATION_COMPLETE.md](PHASE_3_OPTIMIZATION_COMPLETE.md)
- **Cache Manager API:** [Cache_Manager Class](includes/core/class-cache-manager.php)
- **Previous Phases:** Phase 1 (Conditional Assets, Cache Manager) + Phase 2 (81+ Transient Migrations)

---

## What Is Phase 3?

Advanced performance optimization focused on dashboard and page-level caching:

1. **Query Batch Optimizer** - Batch database queries (5-10% improvement)
2. **Lazy Widget Loader** - Load widgets asynchronously (15-20% improvement)
3. **Dashboard Cache** - Page-level HTML caching (30-50% improvement)
4. **AJAX Cache Management** - Clear caches on data changes

**Combined Impact: 40-60% faster dashboard loads** (repeated visits)

---

## Component Reference

### Query_Batch_Optimizer
```php
namespace WPShadow\Core;

// Initialize (automatic in bootstrap)
Query_Batch_Optimizer::init();

// Queue queries for batching
$cache_key = Query_Batch_Optimizer::queue_query(
    "SELECT * FROM {$wpdb->posts} LIMIT 10",
    'get_results'
);

// Get result
$results = Query_Batch_Optimizer::get_result( $cache_key );

// Force execution
Query_Batch_Optimizer::execute_pending_batches();

// Get statistics
$stats = Query_Batch_Optimizer::get_stats();
// Returns: ['pending' => 0, 'cached' => 45, 'total' => 450]

// Configure batch size
Query_Batch_Optimizer::set_batch_size( 20 );
```

### Lazy_Widget_Loader
```php
namespace WPShadow\Dashboard;

// Initialize (automatic in bootstrap)
Lazy_Widget_Loader::init();

// Setup lazy loading on specific page
Lazy_Widget_Loader::setup_lazy_loading();

// Get list of lazy-loaded widgets
$widgets = Lazy_Widget_Loader::get_lazy_widgets();
// Returns: [
//   'diagnostics' => ['title' => '...', 'priority' => 10],
//   'performance' => ['title' => '...', 'priority' => 20],
//   ...
// ]

// Cache a widget
Lazy_Widget_Loader::cache_widget(
    'diagnostics',
    '<div>Widget HTML</div>',
    HOUR_IN_SECONDS
);

// Get cached widget
$html = Lazy_Widget_Loader::get_cached_widget( 'diagnostics' );

// Invalidate widget cache
Lazy_Widget_Loader::invalidate_widget( 'diagnostics' );

// Invalidate all widget cache
Lazy_Widget_Loader::invalidate_all_widgets();
```

### Dashboard_Cache
```php
namespace WPShadow\Core;

// Initialize (automatic in bootstrap)
Dashboard_Cache::init();

// Check if cached output exists
$cached_html = Dashboard_Cache::get_cached_output();

if ( $cached_html ) {
    echo $cached_html;
    exit;
}

// Render dashboard
ob_start();
// ... render dashboard HTML ...
$output = ob_get_clean();

// Cache the output
Dashboard_Cache::set_cached_output( $output );
echo $output;

// Manual cache invalidation
Dashboard_Cache::invalidate_cache();
Dashboard_Cache::invalidate_widget_cache( 'diagnostics' );
Dashboard_Cache::invalidate_all_caches();

// Get statistics
$stats = Dashboard_Cache::get_cache_stats();
// Returns: [
//   'cache_exists' => true,
//   'cache_size' => 45678,
//   'ttl' => 3600,
//   'hit_rate' => 87.5,
//   'total_hits' => 35,
//   'total_misses' => 5
// ]

// Configure TTL
Dashboard_Cache::set_cache_ttl( 2 * HOUR_IN_SECONDS );
```

### Lazy Widget Loader (JavaScript)
```javascript
// Initialize lazy loading on dashboard
document.addEventListener( 'DOMContentLoaded', function() {
    if ( window.wpshadowLazyWidgets ) {
        wpshadowLazyWidgets.load();
    }
});

// Listen for widget load events
document.addEventListener( 'wpshadow-widget-loaded', function( e ) {
    console.log( 'Widget loaded: ' + e.detail.widgetId );
    console.log( 'Time taken: ' + e.detail.loadTime + 'ms' );
    console.log( 'From cache: ' + e.detail.fromCache );
});

// Listen for all widgets loaded
document.addEventListener( 'wpshadow-widgets-all-loaded', function( e ) {
    console.log( 'All widgets loaded in: ' + e.detail.totalTime + 'ms' );
});
```

### AJAX_Dashboard_Cache
```php
namespace WPShadow\Admin;

// These AJAX endpoints are registered automatically:

// Invalidate dashboard cache
wp.ajax.post( 'wpshadow_invalidate_dashboard_cache', {
    nonce: wpshadowData.nonce,
    // Response: { success: true, message: '...' }
});

// Invalidate specific widget cache
wp.ajax.post( 'wpshadow_invalidate_widget_cache', {
    nonce: wpshadowData.nonce,
    widget_id: 'diagnostics',
    // Response: { success: true, message: '...' }
});

// Get cache statistics
wp.ajax.post( 'wpshadow_get_cache_stats', {
    nonce: wpshadowData.nonce,
    // Response: {
    //   success: true,
    //   data: { cache_exists, cache_size, ttl, hit_rate, ... }
    // }
});

// Clear all caches
wp.ajax.post( 'wpshadow_invalidate_all_caches', {
    nonce: wpshadowData.nonce,
    // Response: { success: true, message: '...' }
});
```

---

## Cache Invalidation Hooks

Automatically invalidate cache when data changes by firing these hooks:

```php
// After running diagnostics
do_action( 'wpshadow_diagnostics_completed' );

// After applying treatments
do_action( 'wpshadow_treatment_applied' );
do_action( 'wpshadow_treatment_failed' );

// After updating settings
do_action( 'wpshadow_setting_updated' );

// After dismissing notices
do_action( 'wpshadow_notice_dismissed' );

// After logging activity
do_action( 'wpshadow_activity_logged' );

// After widget data updates
do_action( 'wpshadow_widget_data_updated' );

// Custom invalidation hook
do_action( 'wpshadow_dashboard_cache_invalidated' );

// Query executed hook
do_action( 'wpshadow_query_executed', $key, $result, $query );
```

---

## Common Scenarios

### Scenario 1: Add a New Lazy-Loadable Widget
```php
// Extend get_lazy_widgets()
add_filter( 'wpshadow_lazy_widgets', function( $widgets ) {
    $widgets['my_widget'] = array(
        'title'    => 'My Widget',
        'priority' => 25,  // Load order
    );
    return $widgets;
});
```

### Scenario 2: Manual Cache Invalidation
```php
// When your code makes significant changes
\WPShadow\Core\Dashboard_Cache::invalidate_cache();

// Or just the widget
\WPShadow\Core\Dashboard_Cache::invalidate_widget_cache( 'diagnostics' );
```

### Scenario 3: Monitor Cache Performance
```php
// Get hit rate
$stats = \WPShadow\Core\Dashboard_Cache::get_cache_stats();
echo 'Cache hit rate: ' . $stats['hit_rate'] . '%';

// Get query stats
$query_stats = \WPShadow\Core\Query_Batch_Optimizer::get_stats();
echo 'Cached queries: ' . $query_stats['cached'];
```

### Scenario 4: Disable Lazy Loading for Specific Widget
```php
// Remove from lazy loading
add_filter( 'wpshadow_lazy_widgets', function( $widgets ) {
    unset( $widgets['activity'] );  // Load synchronously
    return $widgets;
});
```

### Scenario 5: Custom Dashboard Caching
```php
// Get cached dashboard
$cached = \WPShadow\Core\Dashboard_Cache::get_cached_output();

if ( $cached ) {
    return $cached;  // Use cache
}

// Render fresh
$output = render_dashboard();

// Cache result
\WPShadow\Core\Dashboard_Cache::set_cached_output( $output );

return $output;
```

---

## Performance Metrics

### Expected Improvements

| Metric | Before | After | Improvement |
|--------|--------|-------|-------------|
| Dashboard Initial Load | 2.5s | 2.1s | 15-20% |
| Dashboard Repeat Load | 2.5s | 1.25s | 30-50% |
| Database Queries | 100 | 90-95 | 5-10% |
| **Combined Impact** | **2.5s** | **1.0-1.2s** | **40-60%** |

### Monitoring

```php
// Check if optimizations are working
$cache_stats = \WPShadow\Core\Dashboard_Cache::get_cache_stats();
$query_stats = \WPShadow\Core\Query_Batch_Optimizer::get_stats();

// Log to Activity Logger
\WPShadow\Core\Activity_Logger::log(
    'phase3_optimization_check',
    array(
        'cache_hit_rate' => $cache_stats['hit_rate'],
        'cached_queries' => $query_stats['cached'],
        'cache_exists'   => $cache_stats['cache_exists'],
    )
);
```

---

## Testing Checklist

- [ ] Dashboard loads with placeholder widgets
- [ ] Widgets load in priority order (Diagnostics first)
- [ ] Loading animations display during widget load
- [ ] Cache hit on second dashboard load
- [ ] Repeated loads show "from-cache" indicator
- [ ] Running diagnostics invalidates cache
- [ ] Applying treatments invalidates cache
- [ ] AJAX cache endpoints respond correctly
- [ ] Query count reduced on dashboard load
- [ ] Cache statistics show increasing hit rate

---

## Troubleshooting

### Cache Not Working
```php
// Check if cache exists
$cached = \WPShadow\Core\Dashboard_Cache::get_cached_output();
if ( ! $cached ) {
    echo 'Cache miss - checking why...';

    // Check if there's a POST request (prevents caching)
    var_dump( $_POST );

    // Check if there's an action parameter
    var_dump( isset( $_GET['action'] ) );
}
```

### Widgets Not Loading
```javascript
// Check if lazy loader is initialized
console.log( window.wpshadowLazyWidgets );

// Monitor AJAX requests
wp.ajax.send( 'wpshadow_load_widget', {
    data: { widget_id: 'diagnostics', nonce: '...' },
    success: function( response ) {
        console.log( 'Widget loaded:', response );
    },
    error: function( error ) {
        console.error( 'Widget load failed:', error );
    }
});
```

### Query Batching Not Working
```php
// Force immediate execution
\WPShadow\Core\Query_Batch_Optimizer::execute_pending_batches();

// Check pending queries
$stats = \WPShadow\Core\Query_Batch_Optimizer::get_stats();
echo 'Pending: ' . $stats['pending'];
```

---

## Files Reference

| File | Purpose | Lines |
|------|---------|-------|
| [class-query-batch-optimizer.php](includes/core/class-query-batch-optimizer.php) | Database query batching | 237 |
| [class-dashboard-cache.php](includes/core/class-dashboard-cache.php) | Page-level caching | 287 |
| [class-lazy-widget-loader.php](includes/dashboard/class-lazy-widget-loader.php) | AJAX widget loader | 216 |
| [lazy-widget-loader.js](assets/js/lazy-widget-loader.js) | Frontend loader | 176 |
| [lazy-widgets.css](assets/css/lazy-widgets.css) | Loading animations | 239 |
| [class-ajax-dashboard-cache.php](includes/admin/class-ajax-dashboard-cache.php) | AJAX endpoints | 121 |
| **TOTAL** | **Phase 3 Code** | **1,462 lines** |

---

## API Reference

### Query_Batch_Optimizer
- `init()` - Initialize optimizer
- `queue_query(query, output)` - Queue for batch execution
- `get_result(cache_key)` - Get queued result
- `execute_pending_batches()` - Execute all pending
- `get_stats()` - Get statistics
- `clear()` - Clear all queues
- `set_batch_size(size)` - Configure threshold

### Lazy_Widget_Loader
- `init()` - Initialize loader
- `setup_lazy_loading()` - Enqueue JS/CSS
- `get_lazy_widgets()` - Get widget list
- `cache_widget(id, html, ttl)` - Cache widget
- `get_cached_widget(id)` - Retrieve cached
- `invalidate_widget(id)` - Clear specific
- `invalidate_all_widgets()` - Clear all

### Dashboard_Cache
- `init()` - Initialize cache system
- `get_cached_output()` - Get cached HTML
- `set_cached_output(output)` - Cache HTML
- `invalidate_cache()` - Clear page cache
- `invalidate_widget_cache(id)` - Clear widget
- `invalidate_all_caches()` - Clear all
- `get_cache_stats()` - Get statistics
- `set_cache_ttl(seconds)` - Configure TTL

---

## Need Help?

1. **Full Documentation:** See [PHASE_3_OPTIMIZATION_COMPLETE.md](PHASE_3_OPTIMIZATION_COMPLETE.md)
2. **Cache Manager API:** See [class-cache-manager.php](includes/core/class-cache-manager.php)
3. **Previous Phases:** See README.md and phase documentation
4. **Code Examples:** See usage above in "Common Scenarios"

---

*Phase 3 Infrastructure - Ready for Integration & Testing*
*Version: 1.2601.2148*
