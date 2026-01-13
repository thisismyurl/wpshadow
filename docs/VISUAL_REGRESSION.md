# Visual Regression Update Guard

## Overview

The Visual Regression Update Guard is a safety feature that automatically detects visual layout changes after WordPress core, theme, or plugin updates. It helps prevent "silent" updates that break the site's layout without obvious errors.

## How It Works

1. **Before Update**: Captures visual fingerprints of key pages (homepage, sample page, category archive)
2. **After Update**: Re-captures the same pages and compares them
3. **Analysis**: Calculates visual difference percentage based on:
   - DOM structure changes
   - CSS class and style modifications
   - HTML content differences
   - Page length variations
4. **Action**: If changes exceed the threshold (default 5%), flags for manual review or triggers automatic rollback

## Features

- **No External Services**: Uses WordPress HTTP API and pure PHP analysis
- **Lightweight**: Stores fingerprints and hashes, not actual images
- **Configurable**: Adjustable threshold and customizable page list
- **Integrated**: Works with existing Auto-Rollback feature
- **Secure**: SSL verification enabled by default

## Configuration

### Enable the Feature

1. Go to **WordPress Support** → **Settings**
2. Find **Safety Features** section
3. Enable **Visual Regression Update Guard**

### Adjust Threshold

The default threshold is 5% visual difference. To customize:

```php
// In your theme's functions.php or a custom plugin
update_option( 'WPS_visual_regression_threshold', 10.0 ); // 10% threshold
```

### Customize Request Args (e.g., for local development)

```php
add_filter( 'wps_visual_regression_request_args', function( $args ) {
    // Disable SSL verification for local development only
    $args['sslverify'] = false;
    return $args;
} );
```

## How to Test

1. Enable the feature in Settings
2. Navigate to **Plugins** → **Updates**
3. Update a plugin or theme
4. Check the admin dashboard for visual regression notices
5. If threshold exceeded, you'll see a warning message with the difference percentage

## Admin Notices

### Success Notice
> **Visual Regression Update Guard:** Visual Check Passed: plugin update resulted in 2.50% visual difference. No significant layout changes detected.

### Warning Notice
> **Visual Regression Update Guard:** Visual Regression Detected: theme update resulted in 12.30% visual difference (threshold: 5.00%). Manual review recommended.

## Technical Details

### Pages Analyzed

By default, the following pages are captured:
- Homepage
- Sample Page (`/sample-page`)
- Category Archive (`/category/uncategorized`)

### Visual Fingerprint Components

1. CSS classes (layout indicators)
2. Inline styles (direct visual changes)
3. Structural tags (div, section, article, etc.)
4. Visible text content (first 5000 characters)
5. Stylesheet links (CSS file changes)

### Comparison Algorithm

The feature calculates difference based on weighted factors:
- **Fingerprint Match**: 30% weight (most important)
- **HTML Hash Match**: 20% weight
- **Length Difference**: Up to 50% weight (capped)

Average difference is compared against the threshold to determine if the update should be flagged.

## Integration with Auto-Rollback

When Visual Regression Guard detects a threshold violation, it sets a transient flag that can be checked by the Auto-Rollback feature. If Auto-Rollback is also enabled, it can automatically restore the site to its pre-update state.

## Constants

The following constants control behavior:

- `DEFAULT_THRESHOLD`: 5.0 (%)
- `RESULTS_TRANSIENT_TIMEOUT`: 300 seconds
- `STABILIZATION_DELAY`: 3 seconds (wait after update before capture)
- `MAX_TEXT_LENGTH`: 5000 characters
- `FINGERPRINT_DIFF_WEIGHT`: 30
- `HTML_HASH_DIFF_WEIGHT`: 20
- `LENGTH_DIFF_MAX_WEIGHT`: 50

## Troubleshooting

### False Positives

If you're getting too many false positives:
1. Increase the threshold percentage
2. Check if dynamic content (ads, random widgets) is causing differences
3. Consider excluding pages with highly dynamic content

### Missing Screenshots

If post-update screenshots aren't captured:
1. Check that the site is accessible via HTTP/HTTPS
2. Verify there are no firewall rules blocking local requests
3. Check error logs for connection issues

### SSL Verification Issues

If you see SSL verification errors in local development:
1. Use the `wps_visual_regression_request_args` filter to disable SSL verification
2. Only disable SSL verification in development environments
3. Never disable SSL verification in production

## Security Considerations

- SSL verification is **enabled by default** for security
- Only disable SSL verification in trusted development environments
- The feature uses WordPress's built-in HTTP API with proper security practices
- No external services are used, keeping data on your server
- Transient data expires after 5 minutes to avoid clutter

## Performance Impact

- **Minimal**: Only runs during updates, not on regular page loads
- **3-second delay**: Built-in stabilization period after updates
- **Timeout protection**: 30-second timeout on HTTP requests
- **Automatic cleanup**: Old fingerprints are cleaned up after comparison

## Limitations

1. **Visual Changes Only**: Doesn't detect functional bugs or JavaScript errors
2. **Static Content**: Works best with static or semi-static pages
3. **Dynamic Content**: May produce false positives on pages with highly dynamic content
4. **No Screenshot Storage**: Stores fingerprints only, not actual images

## Future Enhancements

Potential improvements for future versions:
- Screenshot storage options
- More sophisticated image comparison algorithms
- Machine learning-based detection
- Integration with external visual regression services
- Customizable page list in settings UI
