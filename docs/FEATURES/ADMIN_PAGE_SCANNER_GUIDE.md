# Admin Page Scanner for Diagnostics

**Date:** January 27, 2026  
**Feature:** Admin page output buffering for HTML/SEO/Accessibility diagnostics  
**Helper Class:** `WPShadow\Diagnostics\Helpers\Admin_Page_Scanner`

---

## Overview

The Admin Page Scanner helper allows diagnostics to capture and analyze WordPress admin page HTML output. This is particularly useful for HTML/SEO/Accessibility diagnostics that need to check:

- Meta tags (title, description, Open Graph, Twitter Cards)
- Heading hierarchy (H1-H6)
- Image alt text
- Link structure
- Schema markup
- ARIA attributes
- Color contrast
- Semantic HTML structure

---

## Helper Class Location

**File:** `/includes/diagnostics/helpers/class-admin-page-scanner.php`  
**Namespace:** `WPShadow\Diagnostics\Helpers`  
**Class:** `Admin_Page_Scanner`

---

## Key Methods

### 1. `capture_admin_page( string $page_slug, array $query_args = array() )`

Captures the HTML output of a specific admin page.

**Parameters:**
- `$page_slug` (string) - Admin page slug (e.g., 'index.php', 'options-general.php')
- `$query_args` (array) - Optional query parameters

**Returns:** `string|false` - HTML content or false on failure

**Example:**
```php
use WPShadow\Diagnostics\Helpers\Admin_Page_Scanner;

// Capture dashboard
$html = Admin_Page_Scanner::capture_admin_page( 'index.php' );

// Capture settings page with tab
$html = Admin_Page_Scanner::capture_admin_page(
    'options-general.php',
    array( 'tab' => 'discussion' )
);
```

### 2. `analyze_html( string $html )`

Performs common HTML analysis checks.

**Returns:** `array` with analysis results:
- `has_doctype` (bool) - Has DOCTYPE declaration
- `has_html_tag` (bool) - Has <html> tag
- `has_head_tag` (bool) - Has <head> tag
- `has_body_tag` (bool) - Has <body> tag
- `has_title_tag` (bool) - Has <title> tag
- `has_meta_charset` (bool) - Has meta charset
- `has_meta_viewport` (bool) - Has meta viewport
- `title_length` (int) - Title length in characters
- `h1_count` (int) - Number of H1 tags
- `missing_alt_images` (int) - Images without alt text
- `external_links` (int) - Number of external links
- `inline_styles_count` (int) - Elements with inline styles

**Example:**
```php
$html = Admin_Page_Scanner::capture_admin_page( 'index.php' );
$analysis = Admin_Page_Scanner::analyze_html( $html );

if ( ! $analysis['has_title_tag'] ) {
    // Report missing title tag
}

if ( $analysis['title_length'] > 60 ) {
    // Report title too long
}

if ( $analysis['h1_count'] > 1 ) {
    // Report multiple H1 tags
}
```

### 3. `extract_meta_tags( string $html )`

Extracts all meta tags from HTML.

**Returns:** `array` of meta tag attributes

**Example:**
```php
$html = Admin_Page_Scanner::capture_admin_page( 'index.php' );
$meta_tags = Admin_Page_Scanner::extract_meta_tags( $html );

foreach ( $meta_tags as $meta ) {
    if ( isset( $meta['property'] ) && $meta['property'] === 'og:title' ) {
        // Found Open Graph title
        $og_title = $meta['content'];
    }
}
```

### 4. `extract_headings( string $html )`

Extracts heading structure (H1-H6).

**Returns:** `array` of headings with level and text

**Example:**
```php
$html = Admin_Page_Scanner::capture_admin_page( 'index.php' );
$headings = Admin_Page_Scanner::extract_headings( $html );

$previous_level = 0;
foreach ( $headings as $heading ) {
    if ( $heading['level'] > $previous_level + 1 ) {
        // Skipped heading level (e.g., H1 → H3)
    }
    $previous_level = $heading['level'];
}
```

---

## Usage in Diagnostics

### Example: Title Tag Check

```php
<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Admin_Page_Scanner;

class Diagnostic_Html_Title_Tag_Exists_Test extends Diagnostic_Base {
    
    protected static $slug = 'html-title-tag-exists-test';
    protected static $title = 'Title Tag Presence Test';
    protected static $description = 'Tests title tag existence';
    protected static $family = 'seo';
    
    public static function check() {
        // Load helper if needed
        if ( ! class_exists( 'WPShadow\Diagnostics\Helpers\Admin_Page_Scanner' ) ) {
            require_once WPSHADOW_PATH . 'includes/diagnostics/helpers/class-admin-page-scanner.php';
        }
        
        $pages_to_check = array(
            'index.php'           => 'Dashboard',
            'options-general.php' => 'General Settings',
            'plugins.php'         => 'Plugins Page',
        );
        
        $missing_titles = array();
        
        foreach ( $pages_to_check as $page_slug => $page_name ) {
            $html = Admin_Page_Scanner::capture_admin_page( $page_slug );
            
            if ( false === $html ) {
                continue;
            }
            
            $analysis = Admin_Page_Scanner::analyze_html( $html );
            
            if ( ! $analysis['has_title_tag'] || $analysis['title_length'] === 0 ) {
                $missing_titles[] = $page_name;
            }
        }
        
        if ( ! empty( $missing_titles ) ) {
            return array(
                'id'          => self::$slug,
                'title'       => self::$title,
                'description' => sprintf(
                    _n(
                        '%d admin page missing title tag: %s',
                        '%d admin pages missing title tags: %s',
                        count( $missing_titles ),
                        'wpshadow'
                    ),
                    count( $missing_titles ),
                    implode( ', ', $missing_titles )
                ),
                'severity'    => 'medium',
                'threat_level' => 30,
                'auto_fixable' => false,
                'kb_link'     => 'https://wpshadow.com/kb/html-title-tag-exists-test',
                'meta'        => array(
                    'missing_pages' => $missing_titles,
                ),
            );
        }
        
        return null;
    }
}
```

### Example: Alt Text Check

```php
public static function check() {
    if ( ! class_exists( 'WPShadow\Diagnostics\Helpers\Admin_Page_Scanner' ) ) {
        require_once WPSHADOW_PATH . 'includes/diagnostics/helpers/class-admin-page-scanner.php';
    }
    
    $html = Admin_Page_Scanner::capture_admin_page( 'index.php' );
    
    if ( false === $html ) {
        return null;
    }
    
    $analysis = Admin_Page_Scanner::analyze_html( $html );
    
    if ( $analysis['missing_alt_images'] > 0 ) {
        return array(
            'id'          => 'html-verify-images-have-alt-text',
            'title'       => 'Images Missing Alt Text',
            'description' => sprintf(
                _n(
                    '%d image is missing alt text',
                    '%d images are missing alt text',
                    $analysis['missing_alt_images'],
                    'wpshadow'
                ),
                $analysis['missing_alt_images']
            ),
            'severity'    => 'high',
            'threat_level' => 70,
            'auto_fixable' => false,
            'kb_link'     => 'https://wpshadow.com/kb/accessibility-alt-text',
        );
    }
    
    return null;
}
```

### Example: Heading Hierarchy Check

```php
public static function check() {
    if ( ! class_exists( 'WPShadow\Diagnostics\Helpers\Admin_Page_Scanner' ) ) {
        require_once WPSHADOW_PATH . 'includes/diagnostics/helpers/class-admin-page-scanner.php';
    }
    
    $html = Admin_Page_Scanner::capture_admin_page( 'index.php' );
    
    if ( false === $html ) {
        return null;
    }
    
    $headings = Admin_Page_Scanner::extract_headings( $html );
    
    $issues = array();
    $previous_level = 0;
    
    foreach ( $headings as $heading ) {
        // Check for skipped levels (e.g., H1 → H3)
        if ( $heading['level'] > $previous_level + 1 ) {
            $issues[] = sprintf(
                'Skipped from H%d to H%d: "%s"',
                $previous_level,
                $heading['level'],
                $heading['text']
            );
        }
        $previous_level = $heading['level'];
    }
    
    if ( ! empty( $issues ) ) {
        return array(
            'id'          => 'html-verify-logical-heading-hierarchy',
            'title'       => 'Heading Hierarchy Issues',
            'description' => implode( '; ', $issues ),
            'severity'    => 'medium',
            'threat_level' => 40,
            'auto_fixable' => false,
            'kb_link'     => 'https://wpshadow.com/kb/accessibility-heading-hierarchy',
        );
    }
    
    return null;
}
```

---

## Pages Commonly Checked

### Core Admin Pages
```php
$admin_pages = array(
    'index.php'              => 'Dashboard',
    'options-general.php'    => 'General Settings',
    'options-writing.php'    => 'Writing Settings',
    'options-reading.php'    => 'Reading Settings',
    'options-discussion.php' => 'Discussion Settings',
    'options-media.php'      => 'Media Settings',
    'options-permalink.php'  => 'Permalink Settings',
    'plugins.php'            => 'Plugins',
    'themes.php'             => 'Themes',
    'users.php'              => 'Users',
    'tools.php'              => 'Tools',
    'profile.php'            => 'Profile',
);
```

### WPShadow Pages
```php
$wpshadow_pages = array(
    'admin.php?page=wpshadow'                => 'Dashboard',
    'admin.php?page=wpshadow-findings'   => 'Findings',
    'admin.php?page=wpshadow-guardian'       => 'Guardian',
    'admin.php?page=wpshadow-settings'       => 'Settings',
    'admin.php?page=wpshadow-automations'      => 'Workflows',
);
```

---

## Performance Considerations

### Caching
The helper uses WordPress HTTP API which respects caching. For intensive diagnostics, consider:

```php
// Cache results for 5 minutes
$cache_key = 'wpshadow_html_check_' . md5( $page_slug );
$cached = get_transient( $cache_key );

if ( false !== $cached ) {
    return $cached;
}

$html = Admin_Page_Scanner::capture_admin_page( $page_slug );
$analysis = Admin_Page_Scanner::analyze_html( $html );

set_transient( $cache_key, $analysis, 300 );
```

### Rate Limiting
For diagnostics checking multiple pages:

```php
foreach ( $pages as $page_slug => $page_name ) {
    $html = Admin_Page_Scanner::capture_admin_page( $page_slug );
    
    // Small delay to avoid overwhelming server
    usleep( 100000 ); // 100ms
}
```

---

## Security Considerations

1. **Authentication**: The helper preserves cookies to ensure authenticated requests
2. **Permissions**: Only runs for users with appropriate capabilities
3. **Validation**: All URLs are validated through `admin_url()`
4. **Sanitization**: All extracted content should be sanitized before display

```php
// Always escape output
$title = Admin_Page_Scanner::extract_title( $html );
echo esc_html( $title );
```

---

## Benefits of Using ob_start() Approach

### ✅ Advantages
1. **Real HTML Capture**: Gets actual rendered output, not just data
2. **Complete Analysis**: Can check meta tags, ARIA attributes, inline styles, etc.
3. **Plugin/Theme Compatibility**: Captures output from all active plugins/themes
4. **Accurate Testing**: Tests what users actually see

### ⚠️ Limitations
1. **Performance**: HTTP requests add overhead (use caching)
2. **JavaScript**: Can't test JavaScript-rendered content (use headless browser for that)
3. **Admin-Only**: Only works for admin pages (frontend requires different approach)
4. **Context**: May need specific user context/permissions

---

## Diagnostics That Can Use This

### HTML/SEO (91 diagnostics)
- Title tag checks
- Meta description checks
- Open Graph tags
- Twitter Cards
- Schema markup
- Canonical URLs
- Internal/external link analysis
- Image optimization checks

### Accessibility (1 diagnostic)
- WCAG compliance
- ARIA labels
- Alt text verification
- Color contrast (requires additional tools)
- Keyboard navigation (requires browser automation)

### Admin (34 diagnostics)
- Dashboard widget issues
- Menu structure problems
- Form accessibility
- Settings page validation

---

## Real-World Implementation Example

See the updated diagnostic:
**File:** `/includes/diagnostics/tests/html_seo/class-diagnostic-html-title-tag-exists-test.php`

This diagnostic now:
1. ✅ Checks both admin and frontend pages
2. ✅ Uses Admin_Page_Scanner helper
3. ✅ Analyzes multiple admin pages
4. ✅ Reports specific page names with issues
5. ✅ Provides detailed meta information
6. ✅ Maintains accessibility standards

---

## Next Steps

To implement admin page scanning in your diagnostic:

1. **Include the helper class**:
   ```php
   if ( ! class_exists( 'WPShadow\Diagnostics\Helpers\Admin_Page_Scanner' ) ) {
       require_once WPSHADOW_PATH . 'includes/diagnostics/helpers/class-admin-page-scanner.php';
   }
   ```

2. **Capture the page**:
   ```php
   $html = Admin_Page_Scanner::capture_admin_page( 'index.php' );
   ```

3. **Analyze or extract data**:
   ```php
   $analysis = Admin_Page_Scanner::analyze_html( $html );
   // OR
   $meta_tags = Admin_Page_Scanner::extract_meta_tags( $html );
   // OR
   $headings = Admin_Page_Scanner::extract_headings( $html );
   ```

4. **Report findings**:
   ```php
   if ( $analysis['missing_alt_images'] > 0 ) {
       return array( /* finding array */ );
   }
   ```

---

**Questions or suggestions?** Open an issue on GitHub or consult the development team.

