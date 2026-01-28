#!/bin/bash
REPO="thisismyurl/wpshadow"

echo "=== Creating 50 WordPress Theme System Diagnostics ==="

# CATEGORY 1: Theme Installation & Activation (10 diagnostics)
gh issue create --repo "$REPO" --title "[Diagnostic] Active Theme Validity" \
  --body "**Purpose:** Validates currently active theme exists, has required files, and loads without errors.

**What to Test:**
- Check if theme directory exists and is readable
- Verify style.css and index.php are present (required files)
- Test if theme header information is properly formatted
- Check for PHP errors during theme load

**Why It Matters:** Broken or missing themes cause fatal errors, white screen of death, and complete site failure.

**Expected Detection:** Deleted theme folders, missing required files, corrupted theme headers, theme loading errors.

**Threat Level:** 85" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Child Theme Configuration" \
  --body "**Purpose:** For sites using child themes, validates child theme is properly configured and references valid parent.

**What to Test:**
- Check if child theme has Template header pointing to parent
- Verify parent theme exists and is installed
- Test if child style.css enqueues after parent
- Check for parent theme dependency version

**Why It Matters:** Broken child theme configuration causes site crashes. Updates to parent themes can break improperly configured child themes.

**Expected Detection:** Missing parent theme, incorrect Template header, stylesheet loading order issues, version mismatches.

**Threat Level:** 75" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Theme Update Availability" \
  --body "**Purpose:** Checks if active theme has updates available and validates theme can be safely updated.

**What to Test:**
- Query WordPress.org or theme provider for updates
- Check current version vs available version
- Verify theme changelog for breaking changes
- Test if update notifications are working

**Why It Matters:** Outdated themes contain security vulnerabilities and compatibility issues. Missing update notifications leave sites vulnerable.

**Expected Detection:** Themes multiple major versions behind, disabled update checks, broken update notifications.

**Threat Level:** 65" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Inactive Theme Cleanup" \
  --body "**Purpose:** Identifies unused inactive themes that should be removed to reduce attack surface.

**What to Test:**
- Count total installed themes
- Identify themes not active or parent of active child
- Check last activation date for inactive themes
- Flag default WordPress themes if unused

**Why It Matters:** Inactive themes with vulnerabilities can still be exploited. Each installed theme is an attack vector. Clean sites = secure sites.

**Expected Detection:** 5+ inactive themes, old default themes (TwentyFifteen, etc.), abandoned premium themes with known vulnerabilities.

**Threat Level:** 60" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Theme License Validity" \
  --body "**Purpose:** For premium themes, validates license keys are active and theme has update access.

**What to Test:**
- Detect premium theme frameworks (Divi, Avada, Elementor Pro themes)
- Check if license key is registered
- Verify license hasn't expired
- Test if update API is accessible

**Why It Matters:** Expired licenses prevent security updates. Sites running premium themes without valid licenses cannot receive critical patches.

**Expected Detection:** Expired theme licenses, missing license keys, update API failures, nulled theme installations.

**Threat Level:** 70" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Theme Installation Source Verification" \
  --body "**Purpose:** Detects themes installed from untrusted sources (nulled themes, unofficial repositories).

**What to Test:**
- Check theme author URL matches official source
- Scan for known nulled theme signatures
- Verify theme version matches official release
- Check for malicious code injection patterns

**Why It Matters:** Nulled themes contain malware, backdoors, and vulnerabilities. They compromise site security from installation.

**Expected Detection:** Nulled themes, themes from unknown sources, modified official themes, backdoor code.

**Threat Level:** 90" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Theme Switching Safety" \
  --body "**Purpose:** Validates theme switching won't cause data loss or widget configuration loss.

**What to Test:**
- Check if current theme uses custom post types that might break
- Verify widget areas are compatible with potential new themes
- Test for theme-specific options that would be lost
- Check for hardcoded content in theme templates

**Why It Matters:** Theme switches often cause layout breaks, lost widgets, and broken functionality without backup systems.

**Expected Detection:** Theme-locked content, incompatible widget areas, custom post types at risk, no theme settings backup.

**Threat Level:** 50" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Theme Author Support Status" \
  --body "**Purpose:** Checks if theme is actively maintained and supported by author.

**What to Test:**
- Check theme last updated date (<6 months = active)
- Verify theme author support forum is active
- Test if theme author responds to support tickets
- Check theme repository activity

**Why It Matters:** Abandoned themes don't receive security updates or compatibility fixes. Sites using abandoned themes face increasing security risk over time.

**Expected Detection:** Themes not updated in >1 year, closed support forums, unresponsive authors, archived repositories.

**Threat Level:** 65" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Default Theme Availability" \
  --body "**Purpose:** Validates a default WordPress theme is installed as fallback if active theme fails.

**What to Test:**
- Check if at least one default Twenty* theme is installed
- Verify fallback theme has no errors
- Test automatic fallback to default theme on errors
- Check fallback theme is compatible with current WP version

**Why It Matters:** Without a fallback theme, site crashes become unrecoverable. WordPress automatically switches to default theme on errors if available.

**Expected Detection:** No default themes installed, fallback theme incompatible, automatic fallback disabled.

**Threat Level:** 70" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Theme Installation Permissions" \
  --body "**Purpose:** Validates theme directory file permissions are secure (not 777).

**What to Test:**
- Check themes directory permissions
- Verify individual theme folders aren't world-writable
- Test if theme files can be modified by web server
- Check for .git or .svn directories in theme folders

**Why It Matters:** World-writable theme directories allow attackers to inject malicious code. Exposed version control directories leak sensitive information.

**Expected Detection:** 777 permissions, world-writable themes, exposed .git directories, insecure file ownership.

**Threat Level:** 75" && sleep 2

# CATEGORY 2: Theme Functionality & Features (15 diagnostics)
gh issue create --repo "$REPO" --title "[Diagnostic] Theme Support Features" \
  --body "**Purpose:** Validates theme properly declares support for core WordPress features.

**What to Test:**
- Check for add_theme_support() calls (post-thumbnails, menus, etc.)
- Verify theme supports required features for site functionality
- Test if post formats are properly supported if used
- Check for HTML5 support declaration

**Why It Matters:** Missing theme support declarations break core WordPress features. Sites lose functionality like featured images or menus.

**Expected Detection:** Missing post-thumbnail support, no menu support, missing HTML5 support, incomplete feature declarations.

**Threat Level:** 55" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Navigation Menu Registration" \
  --body "**Purpose:** Validates theme properly registers and displays navigation menu locations.

**What to Test:**
- Check if menus are registered via register_nav_menus()
- Verify menu locations are displayed in theme
- Test if theme handles empty menu locations gracefully
- Check for excessive menu depth limitations

**Why It Matters:** Missing or broken menu registration breaks site navigation, affecting UX and SEO (site structure).

**Expected Detection:** No registered menu locations, menus not displayed, broken fallback for empty menus, depth limitations too restrictive.

**Threat Level:** 60" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Widget Area Registration" \
  --body "**Purpose:** Validates theme properly registers and displays widget areas (sidebars).

**What to Test:**
- Check if widget areas are registered via register_sidebar()
- Verify widget areas display correctly on frontend
- Test widget area HTML structure for accessibility
- Check for consistent widget area behavior

**Why It Matters:** Broken widget areas prevent using sidebar content, breaking layouts and losing content display functionality.

**Expected Detection:** No registered widget areas, widget areas not displayed, poor HTML structure, inconsistent behavior.

**Threat Level:** 50" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Custom Header Functionality" \
  --body "**Purpose:** If theme uses custom headers, validates functionality works correctly.

**What to Test:**
- Check if custom header is properly registered
- Verify header image uploads and crops correctly
- Test header video functionality if supported
- Check for header text color customization

**Why It Matters:** Broken custom header prevents branding customization and can cause layout issues.

**Expected Detection:** Non-functional header uploads, broken cropping, video header errors, missing color controls.

**Threat Level:** 35" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Custom Background Functionality" \
  --body "**Purpose:** If theme uses custom backgrounds, validates functionality works correctly.

**What to Test:**
- Check if custom background is properly registered
- Verify background image uploads work
- Test background color customization
- Check background position/size options

**Why It Matters:** Broken custom background prevents design customization and can cause layout/contrast issues.

**Expected Detection:** Non-functional background uploads, broken color controls, missing position options.

**Threat Level:** 30" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Post Thumbnail Functionality" \
  --body "**Purpose:** Validates featured image/post thumbnail functionality works correctly.

**What to Test:**
- Verify add_theme_support('post-thumbnails') is present
- Check if thumbnails display on archives and single posts
- Test image size registration and regeneration
- Verify thumbnail HTML includes proper alt text

**Why It Matters:** Featured images are critical for SEO, social sharing, and modern layouts. Broken thumbnails damage visual appeal and search rankings.

**Expected Detection:** Missing post thumbnail support, thumbnails not displaying, wrong image sizes, missing alt text.

**Threat Level:** 55" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Theme Customizer Functionality" \
  --body "**Purpose:** Validates WordPress Customizer integration works properly for theme options.

**What to Test:**
- Check if Customizer options are registered
- Test live preview functionality
- Verify Customizer changes save correctly
- Check for Customizer JavaScript errors

**Why It Matters:** Broken Customizer prevents site owners from customizing their sites, forcing code edits or theme changes.

**Expected Detection:** Customizer options not working, broken live preview, save failures, JavaScript errors.

**Threat Level:** 50" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Block Editor (Gutenberg) Support" \
  --body "**Purpose:** Validates theme properly supports WordPress block editor with styles and features.

**What to Test:**
- Check for add_theme_support('wp-block-styles')
- Verify add_theme_support('align-wide') for wide/full alignment
- Test if editor styles match frontend styles
- Check block pattern registration

**Why It Matters:** Poor block editor support creates WYSIWYG mismatch, frustrating content creators when editor doesn't match live site.

**Expected Detection:** Missing block styles support, no wide alignment, editor/frontend mismatch, broken block patterns.

**Threat Level:** 55" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Classic Editor Theme Compatibility" \
  --body "**Purpose:** For sites still using Classic Editor, validates theme compatibility.

**What to Test:**
- Check if theme forces block editor
- Verify TinyMCE editor styles are present
- Test classic widgets vs block widgets compatibility
- Check for editor style conflicts

**Why It Matters:** Forcing block editor on sites preferring classic creates workflow disruption. Missing editor styles cause WYSIWYG issues.

**Expected Detection:** Forced block editor, missing classic editor styles, widget incompatibilities.

**Threat Level:** 40" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Page Template Availability" \
  --body "**Purpose:** Validates theme provides useful page templates and they work correctly.

**What to Test:**
- Check for registered page templates
- Test if templates display correctly
- Verify template naming is clear and descriptive
- Check for broken template files

**Why It Matters:** Missing or broken page templates limit design flexibility and can cause fatal errors when assigned pages are viewed.

**Expected Detection:** No page templates available, broken template files, unclear template names, template errors.

**Threat Level:** 50" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Archive Template Functionality" \
  --body "**Purpose:** Validates theme archive templates (category, tag, author, date) work correctly.

**What to Test:**
- Test category archive displays correctly
- Verify tag archives work
- Check author archives function properly
- Test date-based archives

**Why It Matters:** Broken archive templates cause 404 errors, hurt SEO (site structure), and prevent content discovery.

**Expected Detection:** Missing archive templates, broken archive displays, pagination issues, empty archive pages with content.

**Threat Level:** 55" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Search Results Template" \
  --body "**Purpose:** Validates theme search results template displays results correctly.

**What to Test:**
- Check if search.php or search template exists
- Test search results display properly
- Verify search form functionality
- Check for search pagination

**Why It Matters:** Broken search templates prevent users from finding content, increasing bounce rates and damaging UX.

**Expected Detection:** Missing search template, broken results display, non-functional search form, missing pagination.

**Threat Level:** 50" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] 404 Error Page Functionality" \
  --body "**Purpose:** Validates theme has a proper 404 error template that helps users navigate.

**What to Test:**
- Check if 404.php template exists
- Verify 404 page returns correct HTTP status (404)
- Test if 404 page includes search/navigation
- Check for helpful 404 page content

**Why It Matters:** Poor 404 pages increase bounce rates. Wrong HTTP status codes (200 instead of 404) hurt SEO by indexing error pages.

**Expected Detection:** Missing 404 template, wrong HTTP status, unhelpful 404 pages, no navigation options.

**Threat Level:** 55" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Comment Template Functionality" \
  --body "**Purpose:** Validates theme comment template works correctly if comments are enabled.

**What to Test:**
- Check if comments.php exists
- Test comment form displays and submits correctly
- Verify threaded comments work if enabled
- Check comment list displays properly

**Why It Matters:** Broken comment templates prevent user engagement, break social proof, and can cause fatal errors on posts with comments.

**Expected Detection:** Missing comment template, broken comment form, non-functional threading, display errors.

**Threat Level:** 45" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Pagination Functionality" \
  --body "**Purpose:** Validates theme pagination (next/previous, numbered pages) works correctly.

**What to Test:**
- Check if pagination displays on archives
- Test pagination links work correctly
- Verify pagination doesn't cause infinite loops
- Check for pagination accessibility (ARIA labels)

**Why It Matters:** Broken pagination prevents content discovery, creates crawl errors for search engines, and frustrates users.

**Expected Detection:** Missing pagination, broken pagination links, infinite pagination loops, inaccessible pagination.

**Threat Level:** 50" && sleep 2

# CATEGORY 3: Theme Performance & Optimization (10 diagnostics)
gh issue create --repo "$REPO" --title "[Diagnostic] Theme Asset Loading Optimization" \
  --body "**Purpose:** Validates theme loads CSS and JavaScript efficiently without performance issues.

**What to Test:**
- Check if assets are properly enqueued (not hardcoded)
- Verify CSS/JS are minified for production
- Test for render-blocking resources
- Check asset loading strategy (defer, async)

**Why It Matters:** Inefficient asset loading slows page speed, affecting SEO rankings and user experience. Render-blocking CSS delays visual display.

**Expected Detection:** Hardcoded assets, unminified files, render-blocking CSS/JS, excessive HTTP requests.

**Threat Level:** 60" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Theme Image Optimization" \
  --body "**Purpose:** Validates theme uses appropriately sized images and modern image formats.

**What to Test:**
- Check if theme registers proper image sizes
- Test for lazy loading implementation
- Verify WebP or AVIF format support
- Check for excessive image dimensions in theme

**Why It Matters:** Oversized images are the #1 cause of slow page speeds. Modern formats reduce file sizes by 30-50%.

**Expected Detection:** No custom image sizes, missing lazy loading, no modern format support, oversized default images.

**Threat Level:** 55" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Theme Database Query Optimization" \
  --body "**Purpose:** Validates theme doesn't make excessive or inefficient database queries.

**What to Test:**
- Profile theme templates for query counts
- Check for N+1 query problems in loops
- Verify proper use of WP_Query vs get_posts()
- Test for queries that could be cached

**Why It Matters:** Inefficient queries slow page loads and increase server load. N+1 queries can cause 100+ database calls per page.

**Expected Detection:** 50+ queries per page, N+1 problems, inefficient WP_Query arguments, missing query caching.

**Threat Level:** 65" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Theme Caching Compatibility" \
  --body "**Purpose:** Validates theme works correctly with caching plugins and doesn't break cached pages.

**What to Test:**
- Test theme with WP Super Cache, W3 Total Cache
- Verify dynamic content is excluded from cache appropriately
- Check for cache-breaking elements (time-based content)
- Test cache purging on theme updates

**Why It Matters:** Themes incompatible with caching prevent performance optimization. Cache-breaking elements reduce caching effectiveness.

**Expected Detection:** Dynamic content cached incorrectly, cache purge not working, incompatibility with major caching plugins.

**Threat Level:** 50" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Theme External Resource Dependencies" \
  --body "**Purpose:** Identifies external resources (fonts, scripts, APIs) that affect performance and privacy.

**What to Test:**
- Detect Google Fonts loaded from CDN
- Check for external JavaScript libraries
- Identify API calls during page load
- Verify third-party resources use https

**Why It Matters:** External resources create privacy issues (GDPR), single points of failure, and performance bottlenecks. CDN failures break sites.

**Expected Detection:** Multiple external fonts, third-party scripts, API dependencies, non-https resources.

**Threat Level:** 55" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Theme Mobile Performance" \
  --body "**Purpose:** Validates theme performs well on mobile devices with limited resources.

**What to Test:**
- Check mobile page size (<1MB recommended)
- Test for mobile-specific optimization (responsive images)
- Verify mobile scripts don't block rendering
- Check for mobile-first loading strategy

**Why It Matters:** Mobile devices are 60%+ of traffic. Poor mobile performance directly impacts rankings (mobile-first indexing) and conversions.

**Expected Detection:** Large mobile page sizes, no mobile optimization, render-blocking mobile scripts, desktop-first loading.

**Threat Level:** 65" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Theme Font Loading Strategy" \
  --body "**Purpose:** Validates theme loads fonts efficiently without causing FOIT/FOUT issues.

**What to Test:**
- Check for font-display: swap in CSS
- Verify fonts are preloaded if critical
- Test for FOIT (Flash of Invisible Text) issues
- Check font subset loading

**Why It Matters:** Inefficient font loading causes invisible text during page load, damaging user experience and perceived performance.

**Expected Detection:** Missing font-display, no preloading, excessive font variants, full font sets loading unnecessarily.

**Threat Level:** 50" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Theme Render Performance" \
  --body "**Purpose:** Validates theme renders quickly without excessive DOM manipulation or layout thrashing.

**What to Test:**
- Profile theme JavaScript for performance
- Check for excessive DOM queries
- Test for layout thrashing (forced reflows)
- Verify efficient event listeners

**Why It Matters:** JavaScript-heavy themes slow rendering and interaction, affecting Core Web Vitals and user experience.

**Expected Detection:** Excessive DOM queries, layout thrashing, inefficient event listeners, JavaScript bottlenecks.

**Threat Level:** 55" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Theme Third-Party Framework Optimization" \
  --body "**Purpose:** For themes using frameworks (Bootstrap, Foundation), validates optimal configuration.

**What to Test:**
- Check if entire framework loads or just needed components
- Verify framework version is current
- Test for duplicate framework loading (plugin conflict)
- Check for unused framework CSS/JS

**Why It Matters:** Loading entire frameworks (100KB+) when only 20KB is needed wastes bandwidth and slows pages.

**Expected Detection:** Full framework loading, outdated framework versions, duplicate frameworks, unused components.

**Threat Level:** 50" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Theme Prefetching and Preloading" \
  --body "**Purpose:** Validates theme uses DNS prefetching and resource preloading for performance.

**What to Test:**
- Check for dns-prefetch for external resources
- Verify critical resources are preloaded
- Test for preconnect to CDNs
- Check for prefetch of likely next pages

**Why It Matters:** Proper prefetching reduces perceived load time by preparing connections in advance.

**Expected Detection:** Missing dns-prefetch, no resource preloading, no CDN preconnect, unused optimization opportunities.

**Threat Level:** 40" && sleep 2

# CATEGORY 4: Theme Security & Code Quality (10 diagnostics)
gh issue create --repo "$REPO" --title "[Diagnostic] Theme Code Injection Vulnerabilities" \
  --body "**Purpose:** Scans theme code for common injection vulnerabilities (XSS, SQL injection).

**What to Test:**
- Check for unescaped output (echo \$variable without esc_*)
- Verify user input is sanitized
- Test for SQL queries without \$wpdb->prepare()
- Check for eval() or similar dangerous functions

**Why It Matters:** Theme vulnerabilities are exploited to inject malware, steal data, and deface sites. Most exploited WordPress vulnerabilities are theme-based.

**Expected Detection:** Unescaped output, unsanitized input, direct SQL queries, eval() usage, dangerous function calls.

**Threat Level:** 85" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Theme Data Validation" \
  --body "**Purpose:** Validates theme properly validates and sanitizes all user-supplied data.

**What to Test:**
- Check theme option sanitization callbacks
- Verify customizer settings have validation
- Test form inputs for validation
- Check file upload validation if present

**Why It Matters:** Missing validation allows malicious data injection, compromising site security and data integrity.

**Expected Detection:** Missing sanitization callbacks, no validation on customizer settings, unvalidated form inputs.

**Threat Level:** 70" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Theme Nonce Verification" \
  --body "**Purpose:** Validates theme implements nonce verification for all forms and AJAX requests.

**What to Test:**
- Check for wp_nonce_field() in theme forms
- Verify wp_verify_nonce() checks exist
- Test AJAX requests use check_ajax_referer()
- Check customizer nonce implementation

**Why It Matters:** Missing nonces enable CSRF attacks, allowing attackers to trigger actions on behalf of logged-in users.

**Expected Detection:** Forms without nonces, missing nonce verification, AJAX without nonce checks.

**Threat Level:** 75" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Theme File Include Security" \
  --body "**Purpose:** Validates theme safely includes files without allowing path traversal attacks.

**What to Test:**
- Check for get_template_part() vs direct includes
- Verify file paths are validated before including
- Test for ../ path traversal possibilities
- Check for eval() of file contents

**Why It Matters:** Unsafe file includes enable local file inclusion (LFI) attacks, allowing attackers to execute arbitrary code.

**Expected Detection:** User-controlled file includes, missing path validation, relative path vulnerabilities.

**Threat Level:** 80" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Theme Deprecated Function Usage" \
  --body "**Purpose:** Identifies deprecated WordPress functions used in theme code.

**What to Test:**
- Scan for deprecated function calls
- Check WordPress version when functions were deprecated
- Verify modern equivalents are available
- Test for deprecation warnings in debug mode

**Why It Matters:** Deprecated functions may be removed in future WordPress versions, breaking themes. They often lack security improvements.

**Expected Detection:** Use of deprecated functions, reliance on functions scheduled for removal, security vulnerabilities in old functions.

**Threat Level:** 55" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Theme Direct Database Access" \
  --body "**Purpose:** Detects inappropriate direct database access instead of using WordPress APIs.

**What to Test:**
- Check for direct \$wpdb queries instead of WP_Query
- Verify necessary database calls use \$wpdb->prepare()
- Test for raw mysql_* functions (extremely deprecated)
- Check for hardcoded table names vs \$wpdb->prefix

**Why It Matters:** Direct database access bypasses WordPress security, caching, and hooks. Raw queries are SQL injection vectors.

**Expected Detection:** Unnecessary direct queries, unprepared statements, mysql_* functions, hardcoded table names.

**Threat Level:** 75" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Theme Capability Checks" \
  --body "**Purpose:** Validates theme checks user capabilities before performing privileged actions.

**What to Test:**
- Check for current_user_can() before admin actions
- Verify file operations check capabilities
- Test if theme option saves check permissions
- Check for role/capability bypass possibilities

**Why It Matters:** Missing capability checks allow low-privileged users to perform admin actions, enabling privilege escalation.

**Expected Detection:** Admin actions without capability checks, missing current_user_can(), role bypass vulnerabilities.

**Threat Level:** 80" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Theme Malware Signatures" \
  --body "**Purpose:** Scans theme files for known malware signatures and backdoors.

**What to Test:**
- Check for base64_decode() usage (common obfuscation)
- Scan for eval(gzinflate(base64_decode())) patterns
- Detect hidden iframes or external script injections
- Check for unauthorized file_put_contents() calls

**Why It Matters:** Compromised themes contain backdoors allowing persistent attacker access. Malware often hides in theme files.

**Expected Detection:** Base64 encoded PHP, eval() obfuscation, hidden iframes, backdoor code, spam link injection.

**Threat Level:** 95" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Theme Coding Standards Compliance" \
  --body "**Purpose:** Validates theme follows WordPress coding standards for maintainability and security.

**What to Test:**
- Run WordPress Coding Standards checks (PHPCS)
- Check for consistent naming conventions
- Verify proper function prefixing
- Test for global namespace pollution

**Why It Matters:** Poor coding standards lead to conflicts with plugins, security vulnerabilities, and maintenance difficulties.

**Expected Detection:** Unprefixed functions, naming inconsistencies, coding standard violations, global namespace pollution.

**Threat Level:** 45" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Theme Error Handling" \
  --body "**Purpose:** Validates theme handles errors gracefully without exposing sensitive information.

**What to Test:**
- Check for proper error handling (try-catch)
- Verify errors don't expose file paths
- Test for graceful fallbacks on failures
- Check if WP_DEBUG output is suppressed on production

**Why It Matters:** Exposed error messages leak file paths, plugin info, and system configuration, helping attackers map attack surfaces.

**Expected Detection:** Unhandled exceptions, exposed file paths, verbose error messages on production, missing fallbacks.

**Threat Level:** 60" && sleep 2

# CATEGORY 5: Theme Compatibility & Standards (5 diagnostics)
gh issue create --repo "$REPO" --title "[Diagnostic] WordPress Version Compatibility" \
  --body "**Purpose:** Validates theme is compatible with current WordPress version.

**What to Test:**
- Check theme's Requires at least header
- Verify theme tested up to current WP version
- Test for version-specific feature usage
- Check for compatibility mode requirements

**Why It Matters:** Incompatible themes cause errors, broken features, and security vulnerabilities on newer WordPress versions.

**Expected Detection:** Theme requires old WP version, not tested with current version, version-specific breaking changes.

**Threat Level:** 65" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] PHP Version Compatibility" \
  --body "**Purpose:** Validates theme is compatible with current PHP version on server.

**What to Test:**
- Check theme's Requires PHP header
- Test for deprecated PHP functions
- Verify PHP 8+ compatibility
- Check for PHP version-specific syntax

**Why It Matters:** Themes using deprecated PHP functions break on modern servers. PHP version incompatibility causes fatal errors.

**Expected Detection:** Deprecated PHP functions, incompatible syntax, missing PHP version requirements, PHP 8 incompatibility.

**Threat Level:** 70" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Plugin Compatibility Issues" \
  --body "**Purpose:** Identifies known incompatibilities between active theme and installed plugins.

**What to Test:**
- Check for documented theme/plugin conflicts
- Test for CSS/JavaScript conflicts
- Verify theme doesn't break major plugins (WooCommerce, etc.)
- Check for overlapping functionality causing conflicts

**Why It Matters:** Theme/plugin conflicts cause broken features, JavaScript errors, and style issues that damage user experience.

**Expected Detection:** Known incompatibilities, JavaScript conflicts, CSS overrides breaking plugins, functional overlaps.

**Threat Level:** 55" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Multisite Compatibility" \
  --body "**Purpose:** For multisite installations, validates theme works correctly across network.

**What to Test:**
- Check if theme is network-activated properly
- Verify theme works on sub-sites
- Test if theme settings are site-specific or network-wide
- Check for multisite-specific issues

**Why It Matters:** Multisite-incompatible themes break network-wide, affecting all sites. Configuration issues cause inconsistent behavior.

**Expected Detection:** Network activation issues, sub-site incompatibilities, shared settings causing problems.

**Threat Level:** 60" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Accessibility Standards Compliance (WCAG)" \
  --body "**Purpose:** Validates theme meets WCAG 2.1 Level AA accessibility standards.

**What to Test:**
- Check color contrast ratios (4.5:1 for text)
- Verify keyboard navigation works completely
- Test screen reader compatibility
- Check for proper ARIA labels and semantic HTML
- Verify skip-to-content links exist

**Why It Matters:** Inaccessible themes violate ADA/accessibility laws, exclude users with disabilities, and create legal liability.

**Expected Detection:** Poor contrast, broken keyboard nav, missing ARIA labels, non-semantic HTML, no skip links.

**Threat Level:** 65" && sleep 2

echo ""
echo "=== Theme System Diagnostics Complete ==="
echo "Total Created: 50 diagnostics"
echo ""
echo "Categories:"
echo "  • Theme Installation & Activation: 10"
echo "  • Theme Functionality & Features: 15"
echo "  • Theme Performance & Optimization: 10"
echo "  • Theme Security & Code Quality: 10"
echo "  • Theme Compatibility & Standards: 5"
