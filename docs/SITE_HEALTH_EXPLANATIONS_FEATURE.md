# Site Health Explanations Feature

## Overview

The Site Health Explanations feature enhances WordPress's native Site Health checks with user-friendly, non-technical explanations and links to WPShadow's knowledge base. Instead of technical jargon, users see simple explanations of what each check means and why it matters.

## What's New

### 1. Enhanced Site Health Checks
When users visit **Tools → Site Health** in WordPress admin, they now see:
- **User-friendly explanations** for each WordPress Site Health check
- **Knowledge base links** to WPShadow's comprehensive guides
- **Beautiful styling** with color-coded sections that match each issue's status

### 2. Comprehensive Site Health Guide
A new help article at **WPShadow Help → Site Health Guide** that explains:
- REST API functionality
- Loopback requests
- PHP version requirements
- SSL/HTTPS security
- WordPress and plugin updates
- Memory limits
- Debug mode
- Object caching
- Scheduled events (cron)
- File permissions
- Plugin count optimization

### 3. Non-Technical Language
All explanations use plain language suitable for site owners, not developers:
- "Your site can talk to WordPress.com and other services..." instead of "REST API"
- "Your server needs to 'talk to itself'..." instead of "Loopback requests"
- "PHP is the language WordPress is built on..." instead of "PHP version"

## File Structure

### New Files Created

1. **includes/core/class-site-health-explanations.php**
   - Main class that hooks into WordPress Site Health
   - Maps WordPress tests to user-friendly explanations
   - Handles filtering Site Health test results

2. **assets/css/site-health-explanations.css**
   - Styling for explanation boxes
   - Color-coded sections (green for good, blue for recommended, red for critical)
   - Responsive design with gradient backgrounds

3. **includes/views/help/site-health-guide.php**
   - Comprehensive knowledge base article
   - Explains each Site Health check in detail
   - Includes how-to fix instructions for each issue
   - Quick reference summary

### Updated Files

1. **wpshadow.php**
   - Added `require_once` for new Site Health Explanations class
   - Initialize class on `plugins_loaded` hook
   - Enqueue Site Health CSS on Site Health page
   - Added Site Health Guide to Help menu

## How It Works

### WordPress Site Health Hook
The system uses WordPress's native `site_status_test_result` filter:

```php
add_filter( 'site_status_test_result', array( __CLASS__, 'add_explanations' ) );
```

This filter intercepts each test result **before it's displayed** to add our explanations.

### Test Mapping
The class maps WordPress test names to explanations:

```php
$explanations = array(
    'rest_api_test' => 'User-friendly explanation + KB link',
    'loopback_requests' => 'User-friendly explanation + KB link',
    // ... more tests
);
```

### Display
Explanations are appended to the test description with a `div.wpshadow-site-health-explanation` class for styling.

## Covered Tests

The system currently provides explanations for these WordPress Site Health tests:

- **REST API Test** - How modern WordPress communicates
- **Loopback Requests** - Server-to-self communication for background tasks
- **PHP Version** - Language version and security
- **SSL/HTTPS** - Security encryption
- **WordPress Updates** - Core WordPress maintenance
- **Plugin Updates** - Plugin security and compatibility
- **Theme Updates** - Theme maintenance
- **Database** - Data storage and integrity
- **Backups** - Disaster recovery
- **File Integrity** - Security and permissions
- **Plugin Count** - Performance optimization
- **Debug Mode** - Development settings
- **Object Cache** - Performance caching
- **Memory Limit** - Server resource allocation
- **Scheduled Events** - Background task processing
- **Comments** - User engagement settings
- **Environment Type** - Production vs. development

## Styling Details

### Explanation Box Styling
Each explanation box includes:
- **Gradient background** - Subtle visual interest
- **Left border** - Color-coded (purple default, green for good, blue for recommended, red for critical)
- **Readable text** - 13px font size with 1.5 line-height
- **Hover links** - Interactive knowledge base links

### Responsive Design
- Mobile-friendly with proper padding and margins
- Maintains readability on all screen sizes
- Gradient backgrounds adapt to different resolutions

## CSS Classes

The main styling class is `wpshadow-site-health-explanation`. Status-specific classes:
- `.site-status-good .wpshadow-site-health-explanation`
- `.site-status-recommended .wpshadow-site-health-explanation`
- `.site-status-critical .wpshadow-site-health-explanation`

## Knowledge Base Integration

All explanations link to **WPShadow Help → Site Health Guide** with specific anchors:
- `/admin.php?page=wpshadow-help&help_page=site-health-guide#rest-api`
- `/admin.php?page=wpshadow-help&help_page=site-health-guide#loopback-requests`
- ... etc.

Users can:
1. See quick explanation on Site Health page
2. Click link to read comprehensive guide
3. Learn how to fix each issue
4. Get best practice recommendations

## Usage

### For Site Owners
1. Visit **Tools → Site Health** in WordPress admin
2. Scroll through checks and read the WPShadow explanations
3. Click knowledge base links for more detailed guidance
4. Visit **WPShadow Help → Site Health Guide** for comprehensive reference

### For Developers
The system is extensible. To add explanations for custom Site Health tests:

```php
// In a plugin or custom code
add_filter( 'wpshadow_site_health_explanations', function( $explanations ) {
    $explanations['custom_test'] = sprintf(
        '<p><strong>Why this matters:</strong> Your custom explanation...</p>'
        . '<p><a href="%s" target="_blank" rel="noopener noreferrer">Learn more →</a></p>',
        esc_url( admin_url( 'admin.php?page=wpshadow-help&help_page=site-health-guide' ) )
    );
    return $explanations;
} );
```

## Performance Considerations

- **Minimal overhead**: The filter runs only on Tools → Site Health page
- **CSS file**: Enqueued only on Site Health page (not site-wide)
- **String escaping**: All output properly escaped for security
- **No database queries**: Uses static arrays, no additional DB calls

## Security

- All output properly escaped using `esc_html()`, `esc_attr()`, and `esc_url()`
- No nonce required (read-only information display)
- Capabilities check: Display only to users with `read` capability
- Links are internal (WPShadow Help page)

## Accessibility

- Semantic HTML structure
- Proper heading hierarchy
- Color-coded boxes include text descriptions
- Links have clear purpose (`rel="noopener noreferrer"`)
- Readable contrast ratios

## Extensibility

The explanations can be extended through filters:

```php
// Filter all explanations
apply_filters( 'wpshadow_site_health_explanations', $explanations );

// Filter single test
apply_filters( 'wpshadow_site_health_explanation_' . $test, $explanation );

// Filter explanation HTML
apply_filters( 'wpshadow_site_health_explanation_html', $html, $test, $explanation );
```

## Future Enhancements

Potential additions:
- **Severity indicators** - Visual emphasis on critical vs. recommended
- **Auto-fix suggestions** - One-click fixes for common issues
- **Custom rules engine** - Allow hosts to add custom explanations
- **Video tutorials** - Embedded videos for complex fixes
- **Multi-language** - Translations for global audiences

## Testing Checklist

- [ ] Visit Tools → Site Health and verify explanations appear
- [ ] Check that all test results include explanations
- [ ] Click knowledge base links and verify they work
- [ ] Test on mobile to verify responsive styling
- [ ] Verify styling matches issue severity (green/blue/red)
- [ ] Test with different user roles (ensure non-admins can see)
- [ ] Check CSS loads only on Site Health page
- [ ] Verify no console errors or warnings
- [ ] Test in different browsers (Chrome, Firefox, Safari, Edge)
- [ ] Check that WordPress native functionality still works

## Troubleshooting

### Explanations not appearing
- Check that WPShadow is activated
- Verify `Site_Health_Explanations::init()` is called
- Check browser console for CSS/JS errors

### Styling issues
- Verify `site-health-explanations.css` loads
- Check for CSS conflicts with other plugins
- Clear browser cache

### Links not working
- Verify WPShadow Help menu exists
- Check user has `read` capability
- Verify help page filename matches (site-health-guide.php)

## Code Quality

- Follows WPShadow coding standards
- Uses proper PHP namespacing
- Adheres to WordPress escaping and sanitization guidelines
- Includes documentation comments
- No external dependencies
- Tested for syntax errors

## Support

For issues or questions about this feature:
1. Check the [Site Health Guide](includes/views/help/site-health-guide.php)
2. Review [class-site-health-explanations.php](includes/core/class-site-health-explanations.php)
3. Check WordPress Site Health documentation
4. Contact WPShadow support
