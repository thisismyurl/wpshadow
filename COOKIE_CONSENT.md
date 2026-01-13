# Cookie Consent Checks Feature

## Overview

The Cookie Consent Checks feature provides GDPR-compliant cookie consent management without relying on external CDNs or third-party services. It detects and blocks third-party cookies before user consent is given, displays a local consent banner, and manages granular cookie preferences.

## Features

- **Cookie Blocking**: Intercepts `document.cookie` setter to block unauthorized cookies
- **Local Banner**: Fully self-contained consent UI (no external dependencies)
- **Granular Control**: Separate preferences for essential, analytics, and marketing cookies
- **Smart Detection**: Pre-configured patterns for common trackers (GA, Facebook, DoubleClick)
- **Secure**: Proper cookie attributes including Secure flag on HTTPS
- **Accessible**: WCAG compliant with focus states, reduced motion support
- **Responsive**: Mobile-friendly design with dark mode support

## How It Works

### 1. Early Cookie Interception

The feature injects JavaScript in `wp_head` (priority 1) to override `document.cookie` before any third-party scripts load:

```javascript
Object.defineProperty(document, 'cookie', {
    set: function(value) {
        // Check cookie name against blocked patterns
        // Block if no consent given
    }
});
```

### 2. Consent Banner Display

A banner appears at the bottom of the page when:
- No prior consent decision exists
- User hasn't accepted or rejected cookies
- Not in WordPress admin area

### 3. Consent Storage

User preferences are stored in:
- **localStorage**: `wps_cookie_consent` and `wps_cookie_preferences`
- **Cookie**: Mirrored to cookie for server-side detection
- **Secure Flag**: Automatically added on HTTPS sites

### 4. Status Types

- `accepted`: All cookies allowed
- `rejected`: Only essential cookies allowed
- `custom`: User-defined preferences respected

## Installation & Activation

### Enable the Feature

1. Navigate to **WordPress Admin → Settings → Features**
2. Find **Privacy & Compliance** widget
3. Toggle **Cookie Consent Checks** to ON
4. Click Save

### Configure Settings

1. Go to **Settings → Features → Cookie Consent Checks**
2. Customize:
   - **Banner Text**: Message shown to visitors
   - **Custom Blocked Patterns**: Additional regex patterns to block

## Default Blocked Patterns

The feature includes patterns for common tracking cookies:

- `^_ga` - Google Analytics
- `^_gid` - Google Analytics
- `^_gat` - Google Analytics
- `^__utm` - Google Analytics (legacy)
- `^_fbp` - Facebook Pixel
- `^fr$` - Facebook
- `^IDE$` - DoubleClick
- `^_gcl_` - Google Ads
- `^DSID$` - DoubleClick
- `^NID$` - Google
- `^ANID$` - Google
- `^test_cookie$` - DoubleClick

## Essential Cookies (Always Allowed)

These WordPress cookies are never blocked:
- `wordpress_*` - WordPress authentication
- `wp-*` - WordPress session and settings
- `PHPSESSID` - PHP session

## Customization

### Add Custom Blocked Patterns

In the feature settings, add regex patterns (one per line):

```
^custom_cookie_prefix
^third_party_tracker_
^analytics_session
```

### Customize Banner Text

Replace the default message with your own:

```
We value your privacy. This site uses cookies to enhance your experience. 
You can choose which cookies to allow.
```

### Filter Hook for Patterns

Use the filter hook to programmatically add patterns:

```php
add_filter('wps_consent_blocked_patterns', function($patterns) {
    $patterns[] = '^my_custom_cookie';
    return $patterns;
});
```

## Frontend Implementation

### User Journey

1. **First Visit**: Banner appears at bottom
2. **Choose Option**:
   - **Accept All**: Allows all cookies, hides banner
   - **Reject Non-Essential**: Blocks analytics/marketing
   - **Manage Preferences**: Shows granular options
3. **Save Preferences**: Stored for 1 year
4. **Page Reload**: If cookies were blocked, page reloads to apply consent

### Banner Actions

- **Accept All Cookies**: Sets all permissions to true
- **Reject Non-Essential**: Only essential cookies allowed
- **Manage Preferences**: Toggle individual categories
  - Essential (always on)
  - Analytics (toggleable)
  - Marketing (toggleable)

## Technical Details

### Files Structure

```
includes/features/
  └── class-wps-feature-consent-checks.php  # Feature class

assets/
  ├── css/
  │   └── consent-banner.css                # Banner styles
  └── js/
      └── consent-manager.js                # Consent logic
```

### Hooks

**Actions:**
- `wp_head` (priority 1): Inject cookie checker
- `wp_enqueue_scripts` (priority 1): Load assets
- `wp_footer` (priority 1): Render banner

**Filters:**
- `wps_consent_blocked_patterns`: Modify blocked cookie patterns
- `wps_feature_settings_consent-checks`: Add custom settings

### Class Methods

```php
// Check if feature is enabled
$feature->is_enabled()

// Get blocked patterns
$feature->get_blocked_patterns()

// Render settings UI
$feature->render_settings()
```

### JavaScript API

```javascript
// Check consent status
window.wpsConsentManager.hasConsent()

// Get preferences
window.wpsConsentManager.getPreferences()

// Manual consent (advanced)
window.wpsConsentManager.acceptAll()
window.wpsConsentManager.rejectAll()
```

## Browser Compatibility

- Modern browsers: Full support
- IE11: Partial support (basic functionality)
- Mobile Safari: Full support
- Chrome/Firefox/Edge: Full support

## Privacy Compliance

### GDPR Compliance

✅ Explicit consent before setting non-essential cookies
✅ Clear information about cookie purposes
✅ Easy opt-out mechanism
✅ Granular consent options
✅ Persistent storage of preferences
✅ No data transferred to third parties

### CCPA Compliance

✅ Clear disclosure of data collection
✅ Opt-out option provided
✅ No sale of personal information

## Performance Impact

- **First Load**: ~5KB CSS + ~6KB JS
- **Cached**: Minimal impact
- **Cookie Check**: <1ms per cookie operation
- **Page Load**: No blocking resources

## Accessibility

- **Keyboard Navigation**: Full support
- **Screen Readers**: ARIA labels and roles
- **Focus Indicators**: Visible focus states
- **Reduced Motion**: Respects user preferences
- **Color Contrast**: WCAG AA compliant

## Troubleshooting

### Banner Not Showing

1. Check feature is enabled in Settings → Features
2. Verify you're not in admin area
3. Clear localStorage: `localStorage.removeItem('wps_cookie_consent')`
4. Hard refresh browser (Ctrl+Shift+R)

### Cookies Still Being Set

1. Check blocked patterns include the cookie name
2. Ensure feature is enabled on frontend
3. Verify cookie isn't in essential whitelist
4. Check browser console for blocking messages

### Custom Patterns Not Working

1. Test regex pattern in browser console:
   ```javascript
   /^pattern/.test('cookie_name')
   ```
2. Check for syntax errors in pattern
3. Patterns are case-insensitive by default

## Support

For issues or questions:
- GitHub Issues: https://github.com/thisismyurl/plugin-wp-support-thisismyurl/issues
- Documentation: See README.md
- Support: https://thisismyurl.com/support

## License

GPL-2.0-or-later (same as WordPress)
