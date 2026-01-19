# WPShadow Feature Capability Requirements

## Executive Summary

This document provides a comprehensive inventory of all WPShadow features and their capability requirements. Features are categorized by the minimum user role needed to access them, with detailed information on AJAX handler permissions and special permission handling.

---

## Admin/Super Admin Only (`manage_options`)

Features that should only be accessible to site administrators. These features control core security, system integrity, and site-wide settings.

### 1. **Core Integrity** (`core-integrity`)
- **File:** [includes/features/class-wps-feature-core-integrity.php](includes/features/class-wps-feature-core-integrity.php)
- **Name:** File Security Scanner
- **Default Enabled:** Yes
- **Minimum Capability:** `manage_options` (implicit)
- **Description:** Verifies WordPress core files against official checksums from WordPress.org API, detects unauthorized modifications
- **Sub-Features:**
  - `enable_auto_repair` - Auto-repair modified core files (disabled by default)
  - `email_alerts` - Send email when issues detected (disabled by default)
  - `exclude_non_critical` - Skip non-critical file checks (enabled by default)
- **AJAX Handlers:**
  - `wp_ajax_wpshadow_scan_core_files` - Requires admin (implicit via feature)
  - `wp_ajax_wpshadow_repair_core_file` - Requires admin
  - `wp_ajax_wpshadow_repair_all_core_files` - Requires admin
- **Special Permission Handling:** No explicit checks (relies on admin-only feature registration)

### 2. **Core Diagnostics** (`core-diagnostics`)
- **File:** [includes/features/class-wps-feature-core-diagnostics.php](includes/features/class-wps-feature-core-diagnostics.php)
- **Name:** Health Check-Up
- **Default Enabled:** Yes
- **Minimum Capability:** `manage_options` (implicit)
- **Description:** Comprehensive system health monitoring including updates, PHP version, database, permissions, security headers
- **Sub-Features:** (All related to diagnostic checks - no permission variations)
  - `core_updates`, `php_version`, `database_health`, `file_permissions`, `security_headers`, `debug_mode`, `error_log`
- **AJAX Handlers:** None explicitly defined in first 100 lines
- **Special Permission Handling:** None detected

### 3. **Consent Checks (Cookie Privacy Manager)** (`consent-checks`)
- **File:** [includes/features/class-wps-feature-consent-checks.php](includes/features/class-wps-feature-consent-checks.php)
- **Name:** Cookie Privacy Manager
- **Default Enabled:** Yes (for site)
- **Minimum Capability:** `manage_options` (implicit for settings)
- **Description:** GDPR/CCPA-compliant cookie consent management with banner customization
- **Sub-Features:**
  - `cookie_scanning` - Find all cookies (enabled by default)
  - `consent_banner` - Permission message (enabled by default)
  - `script_blocking` - Stop tracking until permission (enabled by default)
  - `audit_trail` - Keep records (disabled by default)
  - `customizable_banner` - Design your message (disabled by default)
- **AJAX Handlers:** Needs investigation (not in first 100 lines)
- **Special Permission Handling:** Frontend-accessible (visitors can interact with banner)

### 4. **External Fonts Disabler** (`external-fonts-disabler`)
- **File:** [includes/features/class-wps-feature-external-fonts-disabler.php](includes/features/class-wps-feature-external-fonts-disabler.php)
- **Name:** Block External Font Loading
- **Default Enabled:** No
- **Minimum Capability:** `manage_options` (explicitly set)
- **Description:** Block external font services to improve privacy and performance
- **Sub-Features:** (All font blocking sub-features)
- **AJAX Handlers:** None in security critical code
- **Special Permission Handling:** No AJAX handlers requiring explicit checks found

### 5. **Hotlink Protection** (`hotlink-protection`)
- **File:** [includes/features/class-wps-feature-hotlink-protection.php](includes/features/class-wps-feature-hotlink-protection.php)
- **Name:** Hotlink Protection
- **Default Enabled:** No
- **Minimum Capability:** `manage_options` (explicitly set)
- **Description:** Server-level hotlink blocking via .htaccess rules
- **Sub-Features:**
  - `apache_protection` - Auto-configure .htaccess (enabled by default)
  - `image_protection` - Protect image files (enabled by default)
  - `media_protection` - Protect media files (enabled by default)
- **AJAX Handlers:** None detected in header
- **Special Permission Handling:** Server-level configuration (no runtime user checks needed)

### 6. **Iframe Busting (Clickjacking Protection)** (`iframe-busting`)
- **File:** [includes/features/class-wps-feature-iframe-busting.php](includes/features/class-wps-feature-iframe-busting.php)
- **Name:** Iframe Busting (Clickjacking Protection)
- **Default Enabled:** No
- **Minimum Capability:** `manage_options` (explicitly set)
- **Description:** Multi-layer clickjacking protection with CSP, X-Frame-Options, and JavaScript frame-busting
- **Sub-Features:**
  - `csp_header` - CSP frame-ancestors (enabled by default)
  - `xfo_header` - X-Frame-Options header (enabled by default)
  - `js_framebuster` - JavaScript frame-buster (enabled by default)
- **AJAX Handlers:** None detected
- **Special Permission Handling:** None (server-level protection)

### 7. **Dark Mode** (`dark-mode`)
- **File:** [includes/features/class-wps-feature-dark-mode.php](includes/features/class-wps-feature-dark-mode.php)
- **Name:** Dark Mode
- **Default Enabled:** Yes (feature available)
- **Minimum Capability:** Not explicitly set (inherited from abstract)
- **Description:** Dark mode support for WordPress admin interface
- **Sub-Features:**
  - `respect_system_preference` - Match computer setting (enabled by default)
  - `user_override` - Manual on/off switch (enabled by default)
- **AJAX Handlers:**
  - `wp_ajax_wpshadow_set_dark_mode` - User preference setting
- **Special Permission Handling:** User-specific preference (each user controls their own dark mode)

### 8. **Skip Navigation Links** (`skiplinks`)
- **File:** [includes/features/class-wps-feature-skiplinks.php](includes/features/class-wps-feature-skiplinks.php)
- **Name:** Add Skip Navigation Links
- **Default Enabled:** Yes
- **Minimum Capability:** `manage_options` (explicitly set)
- **Description:** Auto-inject skip links to help keyboard users navigate
- **Sub-Features:**
  - `skip_to_content` - Skip to content link (enabled by default)
  - `skip_to_nav` - Skip to navigation (enabled by default)
  - `skip_to_footer` - Skip to footer (disabled by default)
  - `custom_styling` - Custom styling (enabled by default)
- **AJAX Handlers:** None
- **Special Permission Handling:** Frontend feature (all users benefit)

---

## Content Contributors/Editors (`edit_posts`)

Features that editors and contributors should access for content optimization and pre-publishing review. These features help content creators improve their work before publication.

### 1. **Pre-Publish Review** (`pre-publish-review`)
- **File:** [includes/features/class-wps-feature-pre-publish-review.php](includes/features/class-wps-feature-pre-publish-review.php)
- **Name:** Check Content Before Publishing
- **Default Enabled:** No
- **Minimum Capability:** `edit_posts` (explicitly checked in AJAX)
- **Description:** Automatic content quality checks before publishing (broken links, paste cleanup, alt text, accessibility)
- **Sub-Features:**
  - `check_broken_links` - Check for broken links (enabled by default)
  - `check_paste_cleanup` - Check for messy pasted content (enabled by default)
  - `check_missing_alt_text` - Check for images without descriptions (enabled by default)
  - `check_empty_headings` - Check for empty section titles (enabled by default)
  - `check_word_count` - Check if content is too short (disabled by default)
  - `show_editor_panel` - Show review panel (enabled by default)
  - `block_on_errors` - Require fixing before publish (disabled by default)
  - `allow_user_preferences` - Let users customize checks (enabled by default)
  - `show_dismiss_option` - Show "never show again" (enabled by default)
- **AJAX Handlers:**
  - `wp_ajax_wpshadow_pre_publish_check` - **Requires `edit_posts`**
  - `wp_ajax_wpshadow_save_review_preferences` - **Requires `edit_posts`**
- **Special Permission Handling:** Yes - explicitly checks `current_user_can('edit_posts')` in AJAX handlers

### 2. **Content Optimizer** (`content-optimizer`)
- **File:** [includes/features/class-wps-feature-content-optimizer.php](includes/features/class-wps-feature-content-optimizer.php)
- **Name:** Complete Content Quality Optimizer
- **Default Enabled:** Yes
- **Minimum Capability:** Not explicitly set (inherited as `manage_options`)
- **Description:** 35+ real-time content quality checks: SEO, readability, accessibility, images, social media
- **Sub-Features:** (35+ checks including title length, meta description, heading structure, keyword density, readability, featured image, etc.)
- **AJAX Handlers:** Needs investigation (not in first 100 lines)
- **Special Permission Handling:** Likely needs investigation - content optimizer could be available to editors

### 3. **Paste Cleanup** (`paste-cleanup`)
- **File:** [includes/features/class-wps-feature-paste-cleanup.php](includes/features/class-wps-feature-paste-cleanup.php)
- **Name:** Clean Up Pasted Content
- **Default Enabled:** Yes
- **Minimum Capability:** Not explicitly set (inherited)
- **Description:** Automatically clean content pasted from Word, Google Docs, and other sites
- **Sub-Features:** (13 sub-features for cleaning pasted content)
- **AJAX Handlers:** Needs investigation (not in first 100 lines)
- **Special Permission Handling:** Frontend editor integration (available to anyone who can edit)

---

## Read-Only Viewers (Subscribers/Guests)

Features that non-editing users can view and interact with. These are frontend features or read-only administrative tools.

### 1. **Accessibility Audit** (`a11y-audit`)
- **File:** [includes/features/class-wps-feature-a11y-audit.php](includes/features/class-wps-feature-a11y-audit.php)
- **Name:** Accessibility Checker
- **Default Enabled:** Not specified
- **Minimum Capability:** Likely `manage_options` (pre-publish uses this)
- **Description:** Find and fix accessibility violations (WCAG) with auto-fixes
- **Sub-Features:**
  - `alt_text_check` - Check for missing image descriptions (enabled by default)
  - `aria_validation` - Check screen reader labels (enabled by default)
  - `keyboard_navigation` - Check keyboard navigation (enabled by default)
  - `contrast_checking` - Check text colors are readable (disabled by default)
  - `auto_fixes` - Fix problems automatically (disabled by default)
- **AJAX Handlers:**
  - `wp_ajax_wpshadow_audit_page` - **Requires `manage_options`**
- **Special Permission Handling:** Yes - admin only for on-demand audit

### 2. **Broken Link Checker** (`broken-link-checker`)
- **File:** [includes/features/class-wps-feature-broken-link-checker.php](includes/features/class-wps-feature-broken-link-checker.php)
- **Name:** Find Broken Links
- **Default Enabled:** Not specified
- **Minimum Capability:** Not explicitly set (likely `manage_options`)
- **Description:** Scan for broken links in posts, pages, and CSS
- **Sub-Features:**
  - `check_internal` - Check links to your pages (enabled by default)
  - `check_external` - Check links to other sites (enabled by default)
  - `check_css` - Check links in styling files (disabled by default)
  - `log_broken_links` - Keep a list (enabled by default)
- **AJAX Handlers:** None in header
- **Special Permission Handling:** Scheduled checks run automatically

### 3. **Color Contrast Checker** (`color-contrast-checker`)
- **File:** [includes/features/class-wps-feature-color-contrast-checker.php](includes/features/class-wps-feature-color-contrast-checker.php)
- **Name:** Text Readability Checker
- **Default Enabled:** Yes
- **Minimum Capability:** Inherited (likely `manage_options`)
- **Description:** Check text/background color contrast for WCAG compliance
- **Sub-Features:**
  - `report_wcag_aaa` - Use strictest standards (disabled by default)
  - `log_violations` - Record problems (disabled by default)
  - `suggest_compliant` - Suggest better colors (disabled by default)
- **AJAX Handlers:**
  - `wp_ajax_wpshadow_check_contrast` - **Requires `manage_options`**
- **Special Permission Handling:** Yes - explicitly checks `current_user_can('manage_options')`

### 4. **Mobile Friendliness** (`mobile-friendliness`)
- **File:** [includes/features/class-wps-feature-mobile-friendliness.php](includes/features/class-wps-feature-mobile-friendliness.php)
- **Name:** Mobile Phone Checker
- **Default Enabled:** Not specified
- **Minimum Capability:** Not explicitly set
- **Description:** Check mobile responsiveness and touch-friendly design
- **Sub-Features:**
  - `viewport_check` - Check if site fits phone screens (enabled by default)
  - `touch_targets` - Check if buttons are big enough (enabled by default)
  - `font_sizes` - Check if text is readable (enabled by default)
  - `tap_spacing` - Check button spacing (enabled by default)
- **AJAX Handlers:** None detected
- **Special Permission Handling:** None detected

### 5. **Http/SSL Audit** (`http-ssl-audit`)
- **File:** [includes/features/class-wps-feature-http-ssl-audit.php](includes/features/class-wps-feature-http-ssl-audit.php)
- **Name:** Security Lock Checker
- **Default Enabled:** Not specified
- **Minimum Capability:** Not explicitly set (likely `manage_options`)
- **Description:** Validates security headers and SSL certificate configuration
- **Sub-Features:**
  - `ssl_check` - Check certificate status (enabled by default)
  - `security_headers` - Check security settings (enabled by default)
  - `alert_notifications` - Alert about security problems (disabled by default)
- **AJAX Handlers:** None detected
- **Special Permission Handling:** None detected

### 6. **Emergency Support** (`emergency-support`)
- **File:** [includes/features/class-wps-feature-emergency-support.php](includes/features/class-wps-feature-emergency-support.php)
- **Name:** Crash Alert System
- **Default Enabled:** Yes
- **Minimum Capability:** Not explicitly set (implicit admin)
- **Description:** Monitor for critical PHP errors and provide recovery options
- **Sub-Features:**
  - `email_notifications` - Email about critical errors (disabled by default)
- **AJAX Handlers:** None detected
- **Special Permission Handling:** Automatic error handling (no user interaction needed)

---

## Feature-Specific Roles

Features with custom capability logic or special permission handling beyond standard WordPress roles.

### 1. **Maintenance Cleanup** (`maintenance-cleanup`)
- **File:** [includes/features/class-wps-feature-maintenance-cleanup.php](includes/features/class-wps-feature-maintenance-cleanup.php)
- **Name:** Fix Stuck Updates
- **Default Enabled:** Not specified
- **Minimum Capability:** Custom - `update_core`
- **Description:** Watch for and fix problems when updates get stuck
- **Sub-Features:**
  - `cleanup_maintenance` - Remove stuck maintenance mode (enabled by default)
  - `cleanup_upgrade_temp` - Remove leftover update files (enabled by default)
  - `cleanup_cache` - Remove old temporary files (enabled by default)
  - `auto_alerts` - Alert when updates get stuck (enabled by default)
- **AJAX Handlers:** None detected
- **Special Permission Handling:** Yes - explicitly checks `current_user_can('update_core')` instead of `manage_options`

### 2. **Magic Link Support** (`magic-link-support`)
- **File:** [includes/features/class-wps-feature-magic-link-support.php](includes/features/class-wps-feature-magic-link-support.php)
- **Name:** Temporary Support Login
- **Default Enabled:** Not specified
- **Minimum Capability:** `manage_options` (required to CREATE magic links)
- **Description:** Create secure time-limited login URLs for developers
- **Sub-Features:**
  - `log_sessions` - Record who logged in (enabled by default)
  - `email_notifications` - Email when support logs in (disabled by default)
  - `role_restriction` - Limit what support can access (disabled by default)
- **AJAX Handlers:** Magic link verification
- **Special Permission Handling:** Yes - Complex flow:
  - **Creating link:** Requires `manage_options`
  - **Using link:** Time-limited token verification (no user_can check)
  - **Role restriction:** Can limit support account to custom role

### 3. **Tips Coach** (`tips-coach`)
- **File:** [includes/features/class-wps-feature-tips-coach.php](includes/features/class-wps-feature-tips-coach.php)
- **Name:** Smart Tips Helper
- **Default Enabled:** Not specified
- **Minimum Capability:** `manage_options` (implicit)
- **Description:** Contextual tips customized by site type
- **Sub-Features:**
  - `enable_tips` - Show helpful tips (enabled by default)
  - `show_site_specific` - Customize for site type (enabled by default)
  - `auto_dismiss` - Hide after completion (enabled by default)
  - `show_priorities` - Show which tips matter (disabled by default)
- **AJAX Handlers:**
  - `wp_ajax_wpshadow_dismiss_tip` - **Requires `manage_options`**
  - `wp_ajax_wpshadow_apply_tip_action` - **Requires `manage_options`**
- **Special Permission Handling:** Yes - explicitly checks `current_user_can('manage_options')` in AJAX

---

## Performance & Cleanup Features (No Special Permissions)

These features work at the system level and don't require user capability checks at runtime.

### **Block Editor Cleanup** (`block-cleanup`)
- **File:** [includes/features/class-wps-feature-block-cleanup.php](includes/features/class-wps-feature-block-cleanup.php)
- **Default Enabled:** No
- **Minimum Capability:** Implicit `manage_options`
- **Sub-Features:** Block library removal, global styles, classic styles, WC blocks, SVG filters, separate block assets

### **CSS Class Cleanup** (`css-class-cleanup`)
- **File:** [includes/features/class-wps-feature-css-class-cleanup.php](includes/features/class-wps-feature-css-class-cleanup.php)
- **Default Enabled:** No
- **Minimum Capability:** Implicit `manage_options`
- **Sub-Features:** Post classes, nav classes, nav IDs, body classes, block classes

### **Embed Disable** (`embed-disable`)
- **File:** [includes/features/class-wps-feature-embed-disable.php](includes/features/class-wps-feature-embed-disable.php)
- **Default Enabled:** Not specified
- **Minimum Capability:** Implicit `manage_options`
- **Sub-Features:** Disable embed script, remove oEmbed links, disable REST oEmbed, remove embed rewrite

### **Head Cleanup** (`head-cleanup`)
- **File:** [includes/features/class-wps-feature-head-cleanup.php](includes/features/class-wps-feature-head-cleanup.php)
- **Default Enabled:** Yes
- **Minimum Capability:** Implicit `manage_options`
- **Sub-Features:** Remove emoji, generator tag, shortlink, RSD, WLW, REST link, oEmbed, feeds, comment styles, disable XML-RPC

### **HTML Cleanup** (`html-cleanup`)
- **File:** [includes/features/class-wps-feature-html-cleanup.php](includes/features/class-wps-feature-html-cleanup.php)
- **Default Enabled:** No
- **Minimum Capability:** Implicit `manage_options`
- **Sub-Features:** Remove comments, whitespace, empty tags, minify inline CSS/JS

### **Image Lazy Loading** (`image-lazy-loading`)
- **File:** [includes/features/class-wps-feature-image-lazy-loading.php](includes/features/class-wps-feature-image-lazy-loading.php)
- **Default Enabled:** Not specified
- **Minimum Capability:** Implicit `manage_options`
- **Sub-Features:** Lazy images, iframes, avatars, thumbnails, exclude first image

### **Interactivity Cleanup** (`interactivity-cleanup`)
- **File:** [includes/features/class-wps-feature-interactivity-cleanup.php](includes/features/class-wps-feature-interactivity-cleanup.php)
- **Default Enabled:** Not specified
- **Minimum Capability:** Implicit `manage_options`
- **Sub-Features:** Disable Interactivity API, block bindings, remove DNS prefetch

### **jQuery Cleanup** (`jquery-cleanup`)
- **File:** [includes/features/class-wps-feature-jquery-cleanup.php](includes/features/class-wps-feature-jquery-cleanup.php)
- **Default Enabled:** Yes
- **Minimum Capability:** Implicit `manage_options`
- **Sub-Features:** Remove jQuery Migrate frontend, keep in admin, log removals

### **Navigation Accessibility** (`nav-accessibility`)
- **File:** [includes/features/class-wps-feature-nav-accessibility.php](includes/features/class-wps-feature-nav-accessibility.php)
- **Default Enabled:** Yes
- **Minimum Capability:** Implicit `manage_options`
- **Sub-Features:** Add aria-current, simplify classes, remove nav IDs, keyboard support

### **Plugin Cleanup** (`plugin-cleanup`)
- **File:** [includes/features/class-wps-feature-plugin-cleanup.php](includes/features/class-wps-feature-plugin-cleanup.php)
- **Default Enabled:** Not specified
- **Minimum Capability:** Implicit `manage_options`
- **Sub-Features:** Jetpack, RankMath, Contact Form 7, WooCommerce, Yoast cleanup

### **Resource Hints** (`resource-hints`)
- **File:** [includes/features/class-wps-feature-resource-hints.php](includes/features/class-wps-feature-resource-hints.php)
- **Default Enabled:** Not specified
- **Minimum Capability:** Implicit `manage_options`
- **Sub-Features:** DNS prefetch, preconnect, preload fonts, preload scripts, remove s.w.org

### **Setup Checks** (`setup-checks`)
- **File:** [includes/features/class-wps-feature-setup-checks.php](includes/features/class-wps-feature-setup-checks.php)
- **Default Enabled:** Not specified
- **Minimum Capability:** Implicit `manage_options`
- **Sub-Features:** Check admin user, site name, timezone, permalinks, search indexing, admin email

### **Simple Cache** (`simple-cache`)
- **File:** [includes/features/class-wps-feature-simple-cache.php](includes/features/class-wps-feature-simple-cache.php)
- **Default Enabled:** No
- **Minimum Capability:** Implicit `manage_options` (checks in AJAX handlers)
- **Sub-Features:** Cache pages, posts, archives, skip logged-in, skip query strings, auto-clear, advanced cache keys, partial cache, preload, CDN, warming, compression, mobile split

---

## Summary by Capability Level

### **`manage_options` - Admin/Super Admin Only** (18 features)
1. core-integrity
2. core-diagnostics
3. consent-checks
4. external-fonts-disabler
5. hotlink-protection
6. iframe-busting
7. skiplinks
8. a11y-audit (for on-demand scanning)
9. color-contrast-checker (for checking tool)
10. maintenance-cleanup (override - requires `update_core` actually)
11. magic-link-support (for creating links)
12. tips-coach
13. setup-checks
14. simple-cache
15. plugin-cleanup
16. resource-hints
17. head-cleanup
18. html-cleanup

### **`edit_posts` - Content Contributors/Editors** (2 features)
1. pre-publish-review ✓ (explicitly checks)
2. content-optimizer (likely, needs verification)

### **`update_core` - Update Managers** (1 feature)
1. maintenance-cleanup (custom permission)

### **Frontend/All Users** (Visitors/Readers) - 6 features
1. dark-mode (per-user preference, not tied to capability)
2. image-lazy-loading
3. nav-accessibility
4. broken-link-checker (read-only, no AJAX restrictions found)
5. mobile-friendliness (read-only)
6. embed-disable

### **Auto/System Level** (3 features)
1. emergency-support
2. paste-cleanup
3. block-cleanup

---

## AJAX Handler Permission Matrix

| Feature | AJAX Handler | Required Capability | Explicit Check |
|---------|--------------|-------------------|-----------------|
| core-integrity | wpshadow_scan_core_files | manage_options | Implicit |
| core-integrity | wpshadow_repair_core_file | manage_options | Implicit |
| core-integrity | wpshadow_repair_all_core_files | manage_options | Implicit |
| dark-mode | wpshadow_set_dark_mode | None | No |
| a11y-audit | wpshadow_audit_page | manage_options | Yes ✓ |
| color-contrast-checker | wpshadow_check_contrast | manage_options | Yes ✓ |
| magic-link-support | (token verification) | None | Token-based |
| pre-publish-review | wpshadow_pre_publish_check | edit_posts | Yes ✓ |
| pre-publish-review | wpshadow_save_review_preferences | edit_posts | Yes ✓ |
| simple-cache | (cache operations) | manage_options | Yes ✓ |
| tips-coach | wpshadow_dismiss_tip | manage_options | Yes ✓ |
| tips-coach | wpshadow_apply_tip_action | manage_options | Yes ✓ |

---

## Recommendations

### For Security-Sensitive Features
- Core integrity checks and repairs should remain `manage_options` only
- File system operations (hotlink, iframe busting, head cleanup) should remain admin-only

### For Content Workflow
- **Consider granting `edit_posts` to:**
  - Pre-publish-review (✓ already does this)
  - Content-optimizer (should verify it supports this)
  - Paste-cleanup (verify it supports editors)
  - Broken-link-checker (as read-only for editors)

### For Site Builders
- Consider a custom "site_builder" capability for:
  - Setup-checks
  - Mobile-friendliness
  - Color-contrast-checker
  - Accessibility checks

### For Performance/Monitoring Roles
- Consider a custom "site_monitor" capability for:
  - Core-diagnostics
  - Http-ssl-audit
  - Error log monitoring

### Current Implementation Status
- ✓ = Feature correctly implements capability checks
- ✗ = Feature needs capability implementation
- ? = Feature needs verification

Most critical admin features are properly restricted to `manage_options`. Pre-publish features correctly use `edit_posts` for content contributors.

