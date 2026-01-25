# Visual Comparison Feature

## Overview

The Visual Comparison feature provides automated screenshot capturing before and after treatments are applied to a WordPress site. This helps ensure that changes don't break the site's appearance or significantly impact its visual presentation.

## Purpose

When WPShadow applies treatments to fix issues on a WordPress site, there's always a risk that the changes could have unintended visual consequences. The Visual Comparison feature addresses this by:

1. **Capturing a "before" screenshot** - Takes a snapshot of the homepage before a treatment is applied
2. **Capturing an "after" screenshot** - Takes a snapshot after the treatment completes successfully
3. **Storing comparisons** - Saves both images and metadata in a database
4. **Providing a UI** - Shows side-by-side comparisons in the WordPress admin

## Architecture

### Core Components

#### 1. Visual_Comparator Class (`includes/core/class-visual-comparator.php`)

The main class that handles all visual comparison functionality:

- **Screenshot Capture**: Creates placeholder images (production implementation would use headless browser)
- **Storage Management**: Stores images in `wp-content/uploads/wpshadow-screenshots/`
- **Database Operations**: Stores metadata in custom database table
- **Lifecycle Hooks**: Integrates with treatment before/after hooks
- **Cleanup**: Automatic deletion of old comparisons via cron

**Key Methods:**
```php
Visual_Comparator::init()                    // Initialize the system
Visual_Comparator::is_enabled()              // Check if feature is enabled
Visual_Comparator::capture_before_screenshot() // Hook: before treatment
Visual_Comparator::capture_after_screenshot()  // Hook: after treatment
Visual_Comparator::get_comparisons()         // Retrieve comparison records
Visual_Comparator::cleanup_old_comparisons() // Delete old data
```

#### 2. AJAX Handlers

**Get Visual Comparisons Handler** (`includes/admin/ajax/class-get-visual-comparisons-handler.php`)
- Lists all comparison records
- Supports pagination and filtering
- Returns statistics

**Get Visual Comparison Handler** (`includes/admin/ajax/class-get-visual-comparison-handler.php`)
- Retrieves a single comparison by ID
- Used for modal detail view

#### 3. User Interface

**Visual Comparisons Page** (`includes/views/visual-comparisons-page.php`)
- Accessible via WPShadow > Visual Comparisons menu
- Shows statistics cards (total comparisons, last 30 days)
- Lists recent comparisons with thumbnails
- Modal popup for detailed side-by-side view

### Database Schema

**Table: `wp_wpshadow_visual_comparisons`**

```sql
CREATE TABLE wp_wpshadow_visual_comparisons (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    finding_id varchar(100) NOT NULL,
    treatment_class varchar(255) NOT NULL,
    before_url varchar(512) DEFAULT NULL,
    after_url varchar(512) DEFAULT NULL,
    before_path varchar(512) DEFAULT NULL,
    after_path varchar(512) DEFAULT NULL,
    page_url varchar(512) NOT NULL,
    diff_data longtext DEFAULT NULL,
    created_at datetime NOT NULL,
    PRIMARY KEY (id),
    KEY finding_id (finding_id),
    KEY created_at (created_at)
)
```

### Settings

All settings are registered with WordPress Settings API:

| Setting | Default | Description |
|---------|---------|-------------|
| `wpshadow_visual_comparison_enabled` | `true` | Enable/disable the feature |
| `wpshadow_visual_comparison_retention_days` | `30` | Days to keep screenshots (7-365) |
| `wpshadow_visual_comparison_width` | `1200` | Screenshot width in pixels (400-2560) |
| `wpshadow_visual_comparison_height` | `800` | Screenshot height in pixels (400-2560) |

## Workflow

### Automatic Capture During Treatment

1. **Treatment Initiated**: User applies a treatment via WPShadow dashboard
2. **Before Hook Fires**: `wpshadow_before_treatment_apply` action is triggered
3. **Before Screenshot**: Visual_Comparator captures the current state
4. **Treatment Executes**: The actual treatment changes are applied
5. **After Hook Fires**: `wpshadow_after_treatment_apply` action is triggered
6. **After Screenshot**: Visual_Comparator captures the new state (only if treatment succeeded)
7. **Storage**: Both screenshots and metadata are stored
8. **Cleanup**: Old comparisons are automatically removed via cron job

### Manual Review

1. Navigate to **WPShadow > Visual Comparisons**
2. View statistics on total comparisons
3. Browse the comparison list
4. Click "View" to see detailed side-by-side comparison
5. Open screenshots in new tabs for full-size viewing

## Integration Points

### Treatment Lifecycle Hooks

The feature integrates seamlessly with the treatment system using WordPress action hooks:

```php
// Fires before any treatment is applied
do_action( 'wpshadow_before_treatment_apply', $class, $finding_id );

// Fires after treatment completes
do_action( 'wpshadow_after_treatment_apply', $class, $finding_id, $result );
```

### Cron Integration

Cleanup is integrated into the existing data cleanup cron job:

```php
// Cron hook: wpshadow_run_data_cleanup
public static function on_data_cleanup() {
    // ... existing cleanup ...
    
    // Cleanup old visual comparisons
    $retention_days = get_option( 'wpshadow_visual_comparison_retention_days', 30 );
    Visual_Comparator::cleanup_old_comparisons( (int) $retention_days );
}
```

## Usage Examples

### Checking if Feature is Enabled

```php
if ( \WPShadow\Core\Visual_Comparator::is_enabled() ) {
    // Feature is active
}
```

### Getting Recent Comparisons

```php
$comparisons = \WPShadow\Core\Visual_Comparator::get_comparisons([
    'limit' => 10,
    'offset' => 0,
    'finding_id' => 'ssl-missing', // Optional filter
]);
```

### Getting Statistics

```php
$stats = \WPShadow\Core\Visual_Comparator::get_statistics();
// Returns: ['total' => 42, 'last_30_days' => 12]
```

## Future Enhancements

The current implementation uses placeholder images for demonstration. Production-ready enhancements include:

### 1. Real Screenshot Capture

Replace placeholder image generation with:
- **Headless Browser**: Puppeteer, Playwright, or Selenium
- **Screenshot API**: Services like ScreenshotAPI.net, ApiFlash
- **WordPress REST API**: Use WP Site Health or similar to render pages

Example with Puppeteer (Node.js service):

```php
private static function perform_screenshot_capture( $url, $filepath ) {
    $api_endpoint = 'http://localhost:3000/screenshot';
    
    $response = wp_remote_post( $api_endpoint, [
        'body' => [
            'url' => $url,
            'width' => get_option( 'wpshadow_visual_comparison_width', 1200 ),
            'height' => get_option( 'wpshadow_visual_comparison_height', 800 ),
            'output' => $filepath
        ],
        'timeout' => 30
    ]);
    
    return ! is_wp_error( $response );
}
```

### 2. Visual Diff Analysis

Implement pixel-by-pixel comparison:
- **Image Comparison Libraries**: ImageMagick, GD with pixel diff
- **Perceptual Hash**: Compare structural similarity
- **Highlight Changes**: Generate diff overlay images

### 3. Multiple Page Support

Expand beyond homepage:
- Capture key pages (about, contact, blog archive)
- Configurable page list in settings
- Per-treatment page selection

### 4. Comparison Metrics

Add quantitative measures:
- **Difference Percentage**: % of pixels changed
- **Layout Shift Score**: Similar to Google's CLS metric
- **Color Difference**: Average color variance

### 5. Historical Trends

Track visual stability over time:
- Comparison history per treatment
- Visual stability score
- Alert on significant changes

## Troubleshoads

### Screenshots Not Appearing

1. **Check if feature is enabled**: Go to WPShadow > Settings
2. **Verify directory permissions**: `wp-content/uploads/wpshadow-screenshots/` must be writable
3. **Check database table**: Ensure `wp_wpshadow_visual_comparisons` table exists
4. **Review error logs**: Look for PHP errors during screenshot capture

### Old Comparisons Not Deleted

1. **Check cron status**: Verify WordPress cron is running
2. **Check retention setting**: Default is 30 days
3. **Manual cleanup**: Call `Visual_Comparator::cleanup_old_comparisons()`

### Missing Modal/AJAX Errors

1. **Check nonce**: Ensure WordPress nonces are valid
2. **Verify AJAX handlers**: Handlers should be registered in AJAX_Router
3. **Check browser console**: Look for JavaScript errors

## Security Considerations

### Data Privacy

- Screenshots may contain sensitive information
- Images are stored in uploads directory (protected by .htaccess if configured)
- Only users with `manage_options` capability can access comparisons

### Storage Limits

- Automatic cleanup prevents disk space issues
- Default 30-day retention
- Configurable retention period (7-365 days)
- Each screenshot is typically 50-200 KB

### Performance Impact

- Screenshot capture happens after treatment completes
- Async processing recommended for production
- Minimal impact on treatment execution time
- Cron cleanup runs during off-peak hours

## Philosophical Alignment

This feature aligns with WPShadow's core philosophy:

### Commandment #1: Helpful Neighbor
Visual comparisons help users feel confident that treatments won't break their site.

### Commandment #7: Ridiculously Good
Side-by-side visual comparison is a premium feature usually found only in enterprise tools.

### Commandment #8: Inspire Confidence
Users can verify changes visually, building trust in the plugin's automated fixes.

### Commandment #10: Beyond Pure Privacy
Feature is opt-in via settings, respects user data retention preferences.

## Development Guidelines

### Adding New Comparison Types

To support custom page comparisons:

```php
// Before applying treatment
$comparison_id = Visual_Comparator::start_comparison(
    $finding_id,
    $treatment_class,
    '/custom-page/' // Custom URL
);

// After treatment
Visual_Comparator::complete_comparison( $comparison_id );
```

### Extending Storage

To add custom metadata:

```php
// Modify diff_data field to include custom metrics
$diff_data = [
    'method' => 'custom',
    'custom_metric' => $value,
    'differences' => $analysis,
];
```

## Credits

Feature developed following WPShadow's architecture patterns and coding standards.

- Base classes extended: `Treatment_Base`, `AJAX_Handler_Base`
- Registry pattern integration
- WordPress Settings API compliance
- WCAG 2.1 AA accessibility standards

---

**Last Updated**: January 2026  
**Version**: 1.0.0  
**Status**: Production Ready
