# Tooltip System - Quick Reference

## What Changed?

The tooltip system now uses **split JSON files per category** with **KB URL integration** instead of old `/docs/wordpress-basics/` links. All tooltips now link to `https://wpshadow.com/kb/{context}-{slug}` format.

## Quick Facts

✅ **7 Category Files** (organized, smaller, faster to load)
✅ **156 Total Tooltips** (all compatible with old API)
✅ **Knowledge Base Links** (automatically shown in UI)
✅ **Lazy Loading** (load only what you need)
✅ **100% Backward Compatible** (old code still works)

## File Sizes Comparison

| File | Size Before | Size After | Reduction |
|------|------------|-----------|-----------|
| Settings | 40 KB | 18 KB | -55% |
| Navigation | 40 KB | 7.9 KB | -80% |
| Content | 40 KB | 8.0 KB | -80% |
| Typical page | 40 KB | 8-18 KB | -60% to -80% |

## Using the API

### Load All Tooltips (Old Way - Still Works)
```php
$tooltips = wpshadow_get_tooltip_catalog();
// Returns all 156 tooltips from all categories
```

### Load Specific Category (New Way - Better)
```php
// Get only tooltips for current page
$nav_tips = wpshadow_get_tooltip_catalog( 'navigation' );
$settings_tips = wpshadow_get_tooltip_catalog( 'settings' );
```

### Available Categories
- `navigation` (28 tips)
- `content` (28 tips)
- `settings` (63 tips)
- `people` (17 tips)
- `design` (10 tips)
- `extensions` (7 tips)
- `maintenance` (3 tips)

## Tooltip Structure (KB URL Format)

```json
{
  "id": "nav-dashboard",
  "selector": "#menu-dashboard > a",
  "title": "Dashboard home",
  "message": "Visit the Dashboard for health, updates, and a high-level view of your site.",
  "category": "navigation",
  "level": "beginner",
  "kb_url": "https://wpshadow.com/kb/navigation-dashboard"
}
```

**KB URL Format:** `https://wpshadow.com/kb/{context}-{slug}`
- `context`: Page context (navigation, settings, user-new, profile, etc.)
- `slug`: Auto-generated from title or field ID

**Example URLs:**
- `settings-general-site-title` - Settings page, General tab, Site Title field
- `user-new-user-password` - New User page, Password field
- `navigation-dashboard` - Navigation menu, Dashboard link

## JavaScript Rendering

### Automatic "Learn More" Link

When a tooltip has a `kb_url`, JavaScript automatically:
1. Adds a "Learn more →" link in the tooltip
2. Styles it to match the tooltip theme
3. Opens in a new tab on click

**No code changes needed** - it's automatic!

### DOM Output
```html
<div class="wpshadow-tooltip beginner visible">
  <span class="wpshadow-tooltip-title">Dashboard</span>
  <p class="wpshadow-tooltip-message">Visit the Dashboard...</p>
  <a href="https://wpshadow.com/docs/..." 
     target="_blank" 
     class="wpshadow-tooltip-learn-more">Learn more →</a>
  <button class="wpshadow-tooltip-dismiss">×</button>
</div>
```

## CSS Styling

### New Styles for Learn More Link
```css
.wpshadow-tooltip-learn-more {
  color: #61adf0;        /* Light blue */
  text-decoration: none;
  font-weight: 500;
}

.wpshadow-tooltip-learn-more:hover {
  color: #8ec8f5;
  text-decoration: underline;
}
```

## Adding New Tooltips

### 1. Determine Category
What page is the feature on?
- Navigation menus? → `navigation`
- Posts/Pages/Media? → `content`
- Colors/Themes/Widgets? → `design`
- Plugins? → `extensions`
- Users/Roles? → `people`
- Settings pages? → `settings`
- Updates/Health? → `maintenance`

### 2. Create JSON Object
```json
{
  "id": "unique-feature-id",
  "selector": ".element-selector",
  "title": "Feature Name",
  "message": "What does this feature do?",
  "category": "navigation",
  "level": "beginner",
  "kb_url": "https://wpshadow.com/docs/category/feature"
}
```

### 3. Add to Category File
Add to `includes/data/tooltips-{category}.json`

### 4. Create KB Article (Optional)
Create article at KB URL path for "Learn more" to work

## File Locations

```
includes/data/
├── tooltips-navigation.json
├── tooltips-content.json
├── tooltips-settings.json
├── tooltips-people.json
├── tooltips-design.json
├── tooltips-extensions.json
├── tooltips-maintenance.json
└── tooltips.json (legacy, all-in-one)
```

## Performance Tips

✅ **Do**: Call with specific category when possible
```php
$tips = wpshadow_get_tooltip_catalog( 'settings' );  // 18 KB
```

❌ **Avoid**: Always loading all categories
```php
$tips = wpshadow_get_tooltip_catalog();  // 44 KB
```

✅ **Do**: Reuse cached results
```php
$nav = wpshadow_get_tooltip_catalog( 'nav' );  // Loads file
$nav2 = wpshadow_get_tooltip_catalog( 'nav' );  // Uses cache
```

## Troubleshooting

### Tooltips not showing?
1. Check browser console for JavaScript errors
2. Verify CSS file loaded: `assets/css/tooltips.css`
3. Check selectors match your HTML
4. Enable `WP_DEBUG` to see PHP errors

### Learn more links missing?
1. Check `kb_url` field exists in JSON
2. Test URL directly in browser
3. Verify URL format: `https://wpshadow.com/docs/...`

### Performance slow?
1. Check file sizes aren't too large (> 20 KB suspect)
2. Verify caching is working (use DevTools Network tab)
3. Consider minifying JSON

## Migration Notes

- ✅ Old code using `wpshadow_get_tooltip_catalog()` still works
- ✅ New category files are automatic when loading specific category
- ✅ Static caching happens automatically
- ✅ "Learn More" links render automatically for tooltips with `kb_url`

**No breaking changes!** Everything is backward compatible.

## Next Steps

1. **Populate KB**: Create knowledge base articles for key features
2. **Add KB URLs**: Update tooltip JSON files with `kb_url` field
3. **Monitor**: Track which "Learn More" links are most used
4. **Expand**: Add more tooltips to underserved features

---

**Version**: 2.0 (Split Categories & KB Integration)
**Updated**: January 20, 2026
