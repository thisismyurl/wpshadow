# Diagnostic Migration Issues

## Summary

Three WPShadow utility tools currently use inline analysis functions instead of the diagnostic system. They need to be refactored to use diagnostic classes for consistency with the 2,179 existing diagnostics and to enable reuse across Dashboard, CLI, Workflows, and Guardian.

**Tools to Migrate:**
1. **Mobile Friendliness** (7 diagnostics needed)
2. **Accessibility Audit** (8 diagnostics needed)  
3. **Broken Link Checker** (6 diagnostics needed)

**Total:** 21 new diagnostic classes

---

## Issue 1: Mobile Friendliness

### Title
```
Create diagnostic classes for Mobile Friendliness checks
```

### Labels
```
enhancement, diagnostics, mobile, architecture
```

### Body

## Overview
The Mobile Friendliness tool currently uses inline analysis in `wpshadow_analyze_mobile_html()` helper function. These checks should be refactored into diagnostic classes to enable reuse across Dashboard, CLI, Workflows, and Guardian.

## Current Implementation
- **Location:** `includes/utils/class-analysis-helpers.php` - `wpshadow_analyze_mobile_html()`  
- **AJAX Handler:** `includes/admin/ajax/Mobile_Check_Handler.php`  
- **Tool Page:** `includes/views/tools/mobile-friendliness.php`

## Checks to Convert

### 1. Viewport Meta Tag (`Diagnostic_Viewport_Meta_Tag`)
- **Check:** Page has `<meta name="viewport">` tag  
- **Current Logic:** Regex match for viewport meta tag  
- **Severity:** Critical (fail if missing)  
- **Auto-fixable:** No (requires theme/header modification)  

### 2. Viewport Device Width (`Diagnostic_Viewport_Device_Width`)
- **Check:** Viewport includes `width=device-width`  
- **Current Logic:** Parse viewport content attribute  
- **Severity:** Medium (warn if missing)  
- **Auto-fixable:** No  

### 3. Viewport Initial Scale (`Diagnostic_Viewport_Initial_Scale`)
- **Check:** Viewport includes `initial-scale` setting  
- **Current Logic:** Parse viewport content attribute  
- **Severity:** Low (warn if missing)  
- **Auto-fixable:** No  

### 4. Zoom Disabled (`Diagnostic_Zoom_Not_Disabled`)
- **Check:** Zoom NOT disabled via `user-scalable=no` or `maximum-scale=1`  
- **Current Logic:** Parse viewport for zoom restrictions  
- **Severity:** Medium (warn if disabled - accessibility issue)  
- **Auto-fixable:** No  

### 5. Readable Font Sizes (`Diagnostic_Mobile_Font_Size`)
- **Check:** No font-size declarations under 14px  
- **Current Logic:** Regex scan for `font-size: Xpx` under 14px  
- **Severity:** Low (warn if found)  
- **Auto-fixable:** No  

### 6. No Fixed Wide Elements (`Diagnostic_Mobile_Wide_Elements`)
- **Check:** No tables/elements with fixed widths >= 960px  
- **Current Logic:** Check for wide tables and min-width CSS  
- **Severity:** Medium (warn if found)  
- **Auto-fixable:** No  

### 7. Tap Targets Sized (`Diagnostic_Mobile_Tap_Targets`)
- **Check:** Links/buttons have adequate spacing  
- **Current Logic:** Detect overlapping interactive elements  
- **Severity:** Medium (warn if issues)  
- **Auto-fixable:** No  

## Benefits of Converting to Diagnostics
✅ Reuse across Dashboard health checks  
✅ CLI can run mobile checks: `wp wpshadow diagnostic check mobile-viewport`  
✅ Workflows can auto-check mobile friendliness  
✅ Guardian can monitor for viewport changes  
✅ Consistent with 2,179 existing diagnostics  
✅ Better caching and performance  

## Implementation Pattern
```php
<?php
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_Viewport_Meta_Tag extends Diagnostic_Base {
    protected static $slug = 'viewport-meta-tag';
    protected static $title = 'Viewport Meta Tag';
    protected static $description = 'Checks for mobile viewport configuration';
    protected static $family = 'mobile';

    public static function check() {
        $home_html = wp_remote_retrieve_body( 
            wp_remote_get( home_url() ) 
        );
        
        $has_viewport = preg_match( 
            '/<meta[^>]+name=["\\\'\\']viewport["\\\'\\'][^>]*>/i', 
            $home_html 
        );
        
        if ( ! $has_viewport ) {
            return array(
                'id'          => self::$slug,
                'title'       => self::$title,
                'description' => __( 'Missing viewport meta tag for mobile devices', 'wpshadow' ),
                'severity'    => 'critical',
                'threat_level' => 80,
                'auto_fixable' => false,
                'kb_link'     => 'https://wpshadow.com/kb/viewport-meta-tag',
            );
        }
        
        return null;
    }
}
```

## Refactoring Steps
1. Create 7 diagnostic classes in `includes/diagnostics/tests/mobile/`
2. Register in `Diagnostic_Registry`
3. Update `Mobile_Check_Handler` to use `Diagnostic_Registry::get_by_family('mobile')`
4. Remove `wpshadow_analyze_mobile_html()` helper function
5. Test tool page still works
6. Add KB articles for each diagnostic

## Acceptance Criteria
- [ ] 7 diagnostic classes created and registered
- [ ] Mobile Friendliness tool uses diagnostic system
- [ ] Dashboard shows mobile diagnostics in health checks
- [ ] CLI can run individual mobile diagnostics
- [ ] All checks return same results as before
- [ ] Helper function removed to avoid duplication

## Related
- Part of architectural consistency initiative
- Enables Guardian monitoring for mobile issues
- Aligns with 2,179 existing diagnostic tests

---

## Issue 2: Accessibility Audit

### Title
```
Create diagnostic classes for Accessibility Audit checks
```

### Labels
```
enhancement, diagnostics, accessibility, architecture, wcag
```

### Body

## Overview
The Accessibility Audit tool currently uses a private `analyze_a11y_html()` method in the AJAX handler. These checks should be refactored into diagnostic classes to enable reuse across Dashboard, CLI, Workflows, and Guardian.

## Current Implementation
- **Location:** `includes/admin/ajax/A11y_Audit_Handler.php` - `analyze_a11y_html()` private method  
- **Tool Page:** `includes/views/tools/accessibility-audit.php`

## Checks to Convert

### 1. Image Alt Text (`Diagnostic_Image_Alt_Text`)
- **Check:** All `<img>` tags have alt attributes  
- **Current Logic:** Regex match images without alt text  
- **Severity:** Critical (fail if missing)  
- **Auto-fixable:** No (requires content/theme editing)  
- **WCAG:** Level A requirement  

### 2. Heading Hierarchy (`Diagnostic_Heading_Hierarchy`)
- **Check:** Proper H1-H6 structure without gaps  
- **Current Logic:** Parse all heading tags, detect skipped levels (H1→H3)  
- **Severity:** Medium (warn if issues)  
- **Auto-fixable:** No  
- **WCAG:** Level AA best practice  

### 3. H1 Tag Present (`Diagnostic_H1_Tag_Present`)
- **Check:** Page has exactly one H1 tag  
- **Current Logic:** Count H1 tags in HTML  
- **Severity:** Medium (warn if 0 or >1)  
- **Auto-fixable:** No  
- **SEO Impact:** Yes  

### 4. Form Labels & ARIA (`Diagnostic_Form_Labels_ARIA`)
- **Check:** Form elements have proper labels or ARIA attributes  
- **Current Logic:** Count form elements without `aria-label`, `aria-labelledby`, or `<label for>`  
- **Severity:** Critical (fail if >5 unlabeled)  
- **Auto-fixable:** No  
- **WCAG:** Level A requirement  

### 5. Language Attribute (`Diagnostic_HTML_Lang_Attribute`)
- **Check:** `<html>` tag has `lang` attribute  
- **Current Logic:** Regex match for `<html lang="...">`  
- **Severity:** Critical (fail if missing)  
- **Auto-fixable:** Yes (can modify theme template)  
- **WCAG:** Level A requirement  

### 6. Skip to Content Link (`Diagnostic_Skip_To_Content_Link`)
- **Check:** Page has skip navigation link  
- **Current Logic:** Look for `href="#content"` or `href="#main"`  
- **Severity:** Low (warn if missing)  
- **Auto-fixable:** No  
- **WCAG:** Level A best practice  

### 7. Color Contrast (`Diagnostic_Color_Contrast`)
- **Check:** Text meets WCAG AA contrast ratios (4.5:1 normal, 3:1 large)  
- **Current Logic:** **Not currently implemented** - should parse CSS and calculate ratios  
- **Severity:** Critical (fail if insufficient)  
- **Auto-fixable:** No  
- **WCAG:** Level AA requirement  

### 8. Focus Indicators (`Diagnostic_Focus_Indicators`)
- **Check:** Interactive elements have visible focus styles  
- **Current Logic:** **Not currently implemented** - should check for `:focus` CSS rules  
- **Severity:** Medium (warn if missing)  
- **Auto-fixable:** No  
- **WCAG:** Level AA requirement  

## Benefits of Converting to Diagnostics
✅ Reuse across Dashboard health checks  
✅ CLI can run a11y checks: `wp wpshadow diagnostic check image-alt-text`  
✅ Workflows can auto-check accessibility  
✅ Guardian can monitor for new accessibility issues  
✅ Consistent with 2,179 existing diagnostics  
✅ WCAG compliance tracking  
✅ Aligns with **CANON pillars** (Accessibility First)  

## Implementation Pattern
```php
<?php
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_Image_Alt_Text extends Diagnostic_Base {
    protected static $slug = 'image-alt-text';
    protected static $title = 'Image Alt Text (WCAG Level A)';
    protected static $description = 'Ensures all images have descriptive alt attributes';
    protected static $family = 'accessibility';

    public static function check() {
        $home_html = wp_remote_retrieve_body( 
            wp_remote_get( home_url() ) 
        );
        
        preg_match_all( '/<img[^>]*>/i', $home_html, $img_matches );
        $images_without_alt = 0;
        
        if ( ! empty( $img_matches[0] ) ) {
            foreach ( $img_matches[0] as $img_tag ) {
                if ( ! preg_match( '/alt\s*=\s*["\\\'\\'][^\"\\'\\\'\\']["\\'\\\'\\']/', $img_tag ) ) {
                    ++$images_without_alt;
                }
            }
        }
        
        if ( $images_without_alt > 0 ) {
            return array(
                'id'          => self::$slug,
                'title'       => self::$title,
                'description' => sprintf(
                    __( 'Found %d images without alt text', 'wpshadow' ),
                    $images_without_alt
                ),
                'severity'    => 'critical',
                'threat_level' => 85,
                'auto_fixable' => false,
                'kb_link'     => 'https://wpshadow.com/kb/image-alt-text',
                'wcag_level'  => 'A',
            );
        }
        
        return null;
    }
}
```

## Refactoring Steps
1. Create 8 diagnostic classes in `includes/diagnostics/tests/accessibility/`
2. Register in `Diagnostic_Registry`
3. Update `A11y_Audit_Handler` to use `Diagnostic_Registry::get_by_family('accessibility')`
4. Remove private `analyze_a11y_html()` method
5. Add 2 new checks: Color Contrast and Focus Indicators
6. Test tool page still works
7. Add KB articles for each diagnostic

## Acceptance Criteria
- [ ] 8 diagnostic classes created and registered
- [ ] Accessibility Audit tool uses diagnostic system
- [ ] Dashboard shows accessibility diagnostics in health checks
- [ ] CLI can run individual accessibility diagnostics
- [ ] All checks return same results as before
- [ ] New color contrast and focus indicator checks implemented
- [ ] WCAG level tagged on each diagnostic

## Related
- **CANON Pillar:** Accessibility First ("No feature complete until accessible")
- Part of architectural consistency initiative
- Enables Guardian monitoring for accessibility regressions
- Aligns with 2,179 existing diagnostic tests
- WCAG 2.1 Level AA compliance goal

---

## Issue 3: Broken Link Checker

### Title
```
Create diagnostic classes for Broken Link Checker
```

### Labels
```
enhancement, diagnostics, seo, architecture
```

### Body

## Overview
The Broken Link Checker tool currently uses a private `check_links_in_html()` method in the AJAX handler. This should be refactored into diagnostic classes to enable reuse across Dashboard, CLI, Workflows, and Guardian.

## Current Implementation
- **Location:** `includes/admin/ajax/Check_Broken_Links_Handler.php` - `check_links_in_html()` private method  
- **Tool Page:** `includes/views/tools/broken-link-checker.php`

## Checks to Convert

### 1. Broken Internal Links (`Diagnostic_Broken_Internal_Links`)
- **Check:** All internal links (`/path` or same domain) return 200 status  
- **Current Logic:** Extract links, validate with `wp_remote_head()`  
- **Severity:** Medium (broken links harm UX and SEO)  
- **Auto-fixable:** No (requires content editing)  
- **Family:** `seo` or `content-quality`  

### 2. Broken External Links (`Diagnostic_Broken_External_Links`)
- **Check:** All external links return valid status codes  
- **Current Logic:** Extract links, validate with `wp_remote_head()`  
- **Severity:** Low (external links may be temporary)  
- **Auto-fixable:** No  
- **Family:** `content-quality`  

### 3. Slow External Resources (`Diagnostic_Slow_External_Resources`)
- **Check:** External resources (images, scripts, stylesheets) load within threshold  
- **Current Logic:** Measure response time for external assets  
- **Severity:** Medium (slow resources harm performance)  
- **Auto-fixable:** No (suggest caching/CDN)  
- **Family:** `performance`  

### 4. Redirect Chains (`Diagnostic_Redirect_Chains`)
- **Check:** Links don't have multiple 301/302 redirects  
- **Current Logic:** Follow redirects, count hops  
- **Severity:** Medium (redirect chains harm SEO and performance)  
- **Auto-fixable:** Yes (update link to final destination)  
- **Family:** `seo`  

### 5. Mixed Content (`Diagnostic_Mixed_Content_Links`)
- **Check:** HTTPS pages don't link to HTTP resources (security issue)  
- **Current Logic:** If site is HTTPS, detect HTTP links/images  
- **Severity:** Critical (browser warnings, security)  
- **Auto-fixable:** Yes (upgrade to HTTPS)  
- **Family:** `security`  

### 6. Malformed URLs (`Diagnostic_Malformed_URLs`)
- **Check:** All URLs are properly formatted  
- **Current Logic:** Use `wp_http_validate_url()` on each link  
- **Severity:** Medium (malformed URLs don't work)  
- **Auto-fixable:** No  
- **Family:** `content-quality`  

## Benefits of Converting to Diagnostics
✅ Reuse across Dashboard health checks  
✅ CLI can check links: `wp wpshadow diagnostic check broken-internal-links`  
✅ Workflows can auto-check links after content updates  
✅ Guardian can monitor for new broken links  
✅ Consistent with 2,179 existing diagnostics  
✅ Caching prevents re-checking same links  

## Implementation Considerations

### Performance Optimization
Link checking is slow (network requests). Diagnostics should:
- Cache results with longer TTL (1 hour+)
- Batch link checks
- Use `wp_remote_head()` not `wp_remote_get()` (faster)
- Implement timeout (5 seconds max per link)
- Skip checks if > 100 links (too slow)

### Scope Limitation
Only check:
- Homepage links (or specified URL)
- Not entire site (too slow for diagnostics)
- For full site scan, use scheduled cron job or workflow

## Implementation Pattern
```php
<?php
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_Broken_Internal_Links extends Diagnostic_Base {
    protected static $slug = 'broken-internal-links';
    protected static $title = 'Broken Internal Links';
    protected static $description = 'Checks for broken internal links on homepage';
    protected static $family = 'seo';

    public static function check() {
        $transient_key = 'wpshadow_broken_links_check';
        $cached = get_transient( $transient_key );
        
        if ( false !== $cached ) {
            return $cached;
        }
        
        $home_html = wp_remote_retrieve_body( 
            wp_remote_get( home_url() ) 
        );
        
        preg_match_all( '/<a[^>]+href=["\\\'\\']([^"\\'\\\'\\'][^>]*)["\\\'\\'\\'\\'][^>]*>/i', $home_html, $matches );
        $broken_links = array();
        
        foreach ( $matches[1] as $url ) {
            // Skip external links for this diagnostic
            if ( 0 !== strpos( $url, home_url() ) && 0 !== strpos( $url, '/' ) ) {
                continue;
            }
            
            $response = wp_remote_head( $url, array( 'timeout' => 5 ) );
            $code = wp_remote_retrieve_response_code( $response );
            
            if ( $code < 200 || $code >= 400 ) {
                $broken_links[] = $url;
            }
        }
        
        $result = null;
        if ( ! empty( $broken_links ) ) {
            $result = array(
                'id'          => self::$slug,
                'title'       => self::$title,
                'description' => sprintf(
                    __( 'Found %d broken internal links', 'wpshadow' ),
                    count( $broken_links )
                ),
                'severity'    => 'medium',
                'threat_level' => 60,
                'auto_fixable' => false,
                'kb_link'     => 'https://wpshadow.com/kb/broken-internal-links',
                'details'     => $broken_links,
            );
        }
        
        // Cache for 1 hour
        set_transient( $transient_key, $result, HOUR_IN_SECONDS );
        
        return $result;
    }
}
```

## Refactoring Steps
1. Create 6 diagnostic classes in `includes/diagnostics/tests/seo/` and `includes/diagnostics/tests/content-quality/`
2. Register in `Diagnostic_Registry`
3. Update `Check_Broken_Links_Handler` to use diagnostics
4. Remove private `check_links_in_html()` method
5. Implement caching for performance
6. Test tool page still works
7. Add KB articles for each diagnostic

## Acceptance Criteria
- [ ] 6 diagnostic classes created and registered
- [ ] Broken Link Checker tool uses diagnostic system
- [ ] Dashboard shows link diagnostics in health checks
- [ ] CLI can run link checks
- [ ] Caching prevents slow repeated checks
- [ ] All checks return same results as before
- [ ] Performance acceptable (<10 seconds for homepage check)

## Related
- Part of architectural consistency initiative
- Enables Guardian monitoring for broken links
- Aligns with 2,179 existing diagnostic tests
- SEO and user experience benefits

---

## Summary of Required Work

### Diagnostics to Create: 21 total

**Mobile (7):**
1. `Diagnostic_Viewport_Meta_Tag`
2. `Diagnostic_Viewport_Device_Width`
3. `Diagnostic_Viewport_Initial_Scale`
4. `Diagnostic_Zoom_Not_Disabled`
5. `Diagnostic_Mobile_Font_Size`
6. `Diagnostic_Mobile_Wide_Elements`
7. `Diagnostic_Mobile_Tap_Targets`

**Accessibility (8):**
1. `Diagnostic_Image_Alt_Text`
2. `Diagnostic_Heading_Hierarchy`
3. `Diagnostic_H1_Tag_Present`
4. `Diagnostic_Form_Labels_ARIA`
5. `Diagnostic_HTML_Lang_Attribute`
6. `Diagnostic_Skip_To_Content_Link`
7. `Diagnostic_Color_Contrast` (NEW)
8. `Diagnostic_Focus_Indicators` (NEW)

**SEO/Links (6):**
1. `Diagnostic_Broken_Internal_Links`
2. `Diagnostic_Broken_External_Links`
3. `Diagnostic_Slow_External_Resources`
4. `Diagnostic_Redirect_Chains`
5. `Diagnostic_Mixed_Content_Links`
6. `Diagnostic_Malformed_URLs`

### Files to Modify: 3
1. `includes/admin/ajax/Mobile_Check_Handler.php` - Use `Diagnostic_Registry::get_by_family('mobile')`
2. `includes/admin/ajax/A11y_Audit_Handler.php` - Use `Diagnostic_Registry::get_by_family('accessibility')`
3. `includes/admin/ajax/Check_Broken_Links_Handler.php` - Use diagnostics for link checking

### Files to Remove/Refactor: 1
- `includes/utils/class-analysis-helpers.php` - Remove `wpshadow_analyze_mobile_html()` and `wpshadow_analyze_a11y_html()`

### Benefits
- Consistent architecture across all 2,200 diagnostics
- CLI access: `wp wpshadow diagnostic check <slug>`
- Workflow automation capabilities
- Guardian monitoring for regressions
- Better caching and performance
- Reuse across Dashboard, Tools, CLI, Workflows, Guardian
