# Diagnostic: Year References Updateable

**Slug:** `pub-year-references-updateable`  
**Category:** Content Publishing  
**Threat Level:** 25 (Low)  
**Auto-fixable:** No  
**Status:** ✅ Implemented

## Purpose

This diagnostic checks if content contains year-specific references (e.g., "Best of 2025", "in 2024") and whether those references are in an easily updateable format rather than hard-coded text that requires manual updates annually.

## Problem Statement

Many websites create content with year-specific references like:
- "Best Tools of 2024"
- "2025 Marketing Trends"
- "Updated in 2024"

When hard-coded in content, these references require manual find-and-replace operations each year, which is:
- Time-consuming
- Error-prone
- Easy to miss or forget
- Creates outdated content if not updated

## Solution

Use updateable patterns that make annual updates easier:

### Recommended Patterns

1. **Shortcodes:**
   - `[current_year]` - Displays current year
   - `[year]` - Custom year shortcode
   - `[date format="Y"]` - Date shortcode with year format

2. **Custom Fields:**
   - `year` - Post meta field for year
   - `publication_year` - Reference year in post meta
   - `reference_year` - Custom year field

### Example: Hard-coded (❌)
```html
<h1>Best WordPress Plugins of 2024</h1>
<p>Updated: January 2024</p>
```

### Example: Updateable (✅)
```html
<h1>Best WordPress Plugins of [current_year]</h1>
<p>Updated: [date format="F Y"]</p>
```

## Detection Logic

1. Scans the 50 most recent published posts
2. Looks for year patterns matching:
   - Current year
   - Last year (current - 1)
   - Two years ago (current - 2)
3. For posts with year references, checks for:
   - Shortcode patterns: `[year`, `[current_year`, `[date`
   - Custom field metadata: `year`, `publication_year`, `reference_year`
4. Flags if more than 70% of posts with year references are hard-coded

## Finding Structure

When issues are detected, returns:

```php
array(
    'id'            => 'pub-year-references-updateable',
    'title'         => 'Year References Not Easily Updateable',
    'description'   => 'Found X posts with year-specific content (Y% hard-coded)...',
    'category'      => 'general',
    'severity'      => 'low',
    'threat_level'  => 25,
    'kb_link'       => 'https://wpshadow.com/kb/pub-year-references-updateable',
    'training_link' => 'https://wpshadow.com/training/content-publishing-year-references',
    'auto_fixable'  => false,
)
```

## Implementation Details

**File:** `includes/diagnostics/tests/class-diagnostic-pub-year-references-updateable.php`  
**Class:** `Diagnostic_Pub_Year_References_Updateable`  
**Namespace:** `WPShadow\Diagnostics`  
**Extends:** `Diagnostic_Base`

### Key Methods

- `check(): ?array` - Main diagnostic logic
- `run(): array` - Wrapper for backward compatibility
- `test_live_pub_year_references_updateable(): array` - Live test validation

### Performance Considerations

- Limited to 50 most recent posts for performance
- Only checks published posts
- Uses efficient regex pattern matching
- No database writes, read-only operation

## Testing

### WP-CLI Command
```bash
wp wpshadow diagnostic run pub-year-references-updateable
```

### Manual Test Cases

1. **No posts:** Should return null (no issues)
2. **Posts without years:** Should return null
3. **Posts with shortcodes:** Should return null (healthy)
4. **Mostly hard-coded years:** Should return finding array
5. **Mix of patterns:** Should calculate percentage and flag if > 70%

## WPShadow Philosophy Alignment

This diagnostic aligns with:

- **Commandment #7 (Ridiculously Good for Free):** Provides valuable content maintainability advice
- **Commandment #8 (Inspire Confidence):** Clear feedback on what to fix and why
- **Commandment #9 (Everything Has a KPI):** Measurable impact on content maintenance time

## User Impact

**Before:** 
- Manual find-replace operations each year
- Risk of missing content updates
- Outdated content damaging credibility

**After:**
- One-time shortcode setup
- Automatic year updates site-wide
- Always current, fresh content
- Significant time savings annually

## Related Diagnostics

- `pub-year-references-check` - Detects if content has year references
- `pub-outdated-references-detected` - Finds old event/stat references
- `pub-update-date-recent` - Checks if posts are updated regularly

## Security Considerations

✅ Read-only operation  
✅ No user input processed  
✅ No database modifications  
✅ Uses WordPress core functions (`get_posts`, `get_post_meta`)  
✅ Follows WordPress coding standards

## Future Enhancements

Potential improvements:
1. Suggest specific shortcode plugins if none installed
2. Detect year patterns in titles and excerpts
3. Provide per-post breakdown in detailed view
4. Integration with popular page builders
5. Track annual update completion rate
