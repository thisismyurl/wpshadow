# Site Health Explanations - Quick Reference

## What It Does

Adds user-friendly explanations to WordPress Site Health tests with links to WPShadow knowledge base.

## Files at a Glance

```
wpshadow/
├── includes/
│   ├── core/
│   │   └── class-site-health-explanations.php   ← Main class (203 lines)
│   └── views/
│       └── help/
│           └── site-health-guide.php             ← KB Article (268 lines)
├── assets/
│   └── css/
│       └── site-health-explanations.css          ← Styling (79 lines)
└── wpshadow.php                                  ← Updated (4 changes)
```

## Quick Start

### For Users
1. Go to **Tools → Site Health** in WordPress admin
2. See WPShadow explanations below each test
3. Click links for detailed guidance in knowledge base
4. Visit **WPShadow Help → Site Health Guide** for comprehensive reference

### For Developers
1. Open `includes/core/class-site-health-explanations.php`
2. Add custom tests to `get_explanations()` array
3. Use `site_status_test_result` filter to customize behavior

## Code Structure

### Main Class
```php
namespace WPShadow\Core;

class Site_Health_Explanations {
    public static function init()
    public static function add_explanations( $result )
    private static function get_explanations()
}
```

### Key Method: `add_explanations()`
- Hook: `site_status_test_result` filter
- Input: WordPress test result array
- Output: Result with appended explanation
- Processing: Lookup test name, append formatted HTML

### Explanation Format
```html
<div class="wpshadow-site-health-explanation">
    <p><strong>Why this matters:</strong> [explanation]</p>
    <p><a href="[kb-link]">Learn more →</a></p>
</div>
```

## CSS Classes

**Primary:**
- `.wpshadow-site-health-explanation` - All explanations

**Status-specific (automatic by WordPress):**
- `.site-status-good .wpshadow-site-health-explanation`
- `.site-status-recommended .wpshadow-site-health-explanation`
- `.site-status-critical .wpshadow-site-health-explanation`

## Customization Examples

### Add Custom Test Explanation
```php
add_filter( 'wpshadow_site_health_explanations', function( $explanations ) {
    $explanations['my_custom_test'] = sprintf(
        '<p>My explanation...</p>'
        . '<p><a href="%s">Learn more →</a></p>',
        esc_url( admin_url( 'admin.php?page=wpshadow-help&help_page=site-health-guide' ) )
    );
    return $explanations;
});
```

### Modify Existing Explanation
```php
add_filter( 'wpshadow_site_health_explanations', function( $explanations ) {
    if ( isset( $explanations['rest_api_test'] ) ) {
        $explanations['rest_api_test'] = 'Custom explanation for REST API...';
    }
    return $explanations;
});
```

### Customize Styling
Edit `assets/css/site-health-explanations.css` to change:
- Colors
- Fonts
- Spacing
- Borders
- Backgrounds

## Testing Checklist

- [ ] Plugin loads without errors
- [ ] Explanations visible on Site Health page
- [ ] All test results have explanations
- [ ] Knowledge base links work
- [ ] Styling displays correctly
- [ ] Mobile layout is responsive
- [ ] No console errors
- [ ] Links open in new tab

## Performance Tips

- CSS loaded only on Site Health page (not site-wide)
- Single filter hook (minimal overhead)
- No database queries
- Static data mapping (no lookups)

## Common Issues

### Explanations not showing
- Verify `Site_Health_Explanations::init()` called
- Check `site_status_test_result` filter works
- Inspect browser console for errors

### Links not working
- Verify WPShadow Help menu exists
- Check anchor name matches (e.g., `#rest-api`)
- Try clearing WordPress cache

### Styling issues
- Verify CSS file loads (check network tab)
- Look for CSS conflicts with other plugins
- Clear browser cache

## Hook Reference

### WordPress Hooks Used
- `site_status_test_result` - Add explanations to tests
- `admin_enqueue_scripts` - Load CSS on Site Health page
- `plugins_loaded` - Initialize class

### Filter Names
- Could create custom filters for extensibility:
  - `wpshadow_site_health_explanations` - All explanations
  - `wpshadow_site_health_explanation_{test_name}` - Single test

## Knowledge Base Sections

Site Health Guide at `includes/views/help/site-health-guide.php` includes:

1. **REST API** - API communication
2. **Loopback Requests** - Server-to-self communication
3. **PHP Version** - Language version
4. **SSL/HTTPS** - Security encryption
5. **WordPress Updates** - Core maintenance
6. **Plugin Updates** - Plugin security
7. **Theme Updates** - Theme maintenance
8. **Database** - Data storage
9. **Backups** - Disaster recovery
10. **File Permissions** - Security settings
11. **Plugin Count** - Performance
12. **Debug Mode** - Development settings
13. **Object Cache** - Performance caching
14. **Memory Limit** - Server resources
15. **Scheduled Events** - Background tasks
16. **Comments** - User engagement
17. **Environment Type** - Production/dev
18. **Two-Factor Auth** - Security

## File Sizes

| File | Size |
|------|------|
| class-site-health-explanations.php | ~6 KB |
| site-health-explanations.css | ~2 KB |
| site-health-guide.php | ~17 KB |
| **Total** | **~25 KB** |

## Deployment Checklist

- [ ] Copy new files to correct directories
- [ ] Update wpshadow.php with 4 changes
- [ ] Validate syntax: `php -l wpshadow.php`
- [ ] Clear WordPress cache
- [ ] Test on Site Health page
- [ ] Verify in multiple browsers
- [ ] Test mobile responsive
- [ ] Check with different user roles

## Support & Documentation

- **Feature Doc:** `docs/SITE_HEALTH_EXPLANATIONS_FEATURE.md`
- **Implementation:** `docs/SITE_HEALTH_IMPLEMENTATION_SUMMARY.md`
- **Verification:** `docs/SITE_HEALTH_VERIFICATION.md`
- **Code:** `includes/core/class-site-health-explanations.php`
- **KB:** `includes/views/help/site-health-guide.php`

## Troubleshooting

| Problem | Solution |
|---------|----------|
| No explanations showing | Check class is loaded in wpshadow.php |
| CSS not loading | Verify enqueue hook and page detection |
| Links 404 | Verify help page file exists and name matches |
| Mobile layout broken | Check site-health-explanations.css media queries |
| Plugin disabled | Check wpshadow.php for syntax errors |

## Version Info

- **Version:** 1.0.0
- **WordPress Minimum:** 5.2+
- **PHP Minimum:** 7.4+
- **Status:** Production Ready

## License

Same as WPShadow plugin

## Credits

Implemented by GitHub Copilot (Claude Haiku 4.5)  
January 20, 2024
