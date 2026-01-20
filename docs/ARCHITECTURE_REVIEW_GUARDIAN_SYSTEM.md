# WPShadow Plugin Architecture Review

**Date:** January 19, 2026  
**Review Scope:** Current structure vs. Guardian System implementation requirements  
**Status:** Ready for Phase 1 Implementation

---

## 📊 Current Plugin Structure

### File Organization
```
Plugin Root: /workspaces/wpshadow
├── wpshadow.php (2,333 lines) - Main plugin file
├── includes/ (90 PHP files)
│   ├── core/ (11 files)
│   │   ├── class-wps-feature-registry.php
│   │   ├── class-wps-gamification.php
│   │   ├── class-wps-privacy-handler.php
│   │   ├── class-wps-session-manager.php
│   │   ├── class-wps-capabilities.php
│   │   ├── class-wps-help-content-api.php
│   │   ├── class-wps-notice-manager.php
│   │   ├── class-wps-settings-cache.php
│   │   ├── class-wps-router-guard.php
│   │   └── wps-capability-helpers.php
│   │
│   ├── features/ (41 files)
│   │   ├── class-wps-feature-abstract.php (488 lines - base class)
│   │   ├── 37 active features
│   │   └── class-wps-feature-core-diagnostics.php (primary monitoring)
│   │
│   ├── admin/ (14 files)
│   │   ├── class-wps-dashboard-registry.php
│   │   ├── class-wps-dashboard-widgets.php
│   │   ├── class-wps-tab-navigation.php
│   │   ├── class-wps-settings-ajax.php
│   │   ├── screens.php
│   │   └── assets.php
│   │
│   ├── helpers/ (7 files)
│   │   ├── class-wps-cache-helper.php
│   │   ├── wps-ajax-helpers.php
│   │   ├── wps-array-helpers.php
│   │   └── (others)
│   │
│   └── views/ (10+ templates)
│       ├── dashboard-renderer.php
│       ├── features.php
│       └── (other view files)
│
├── assets/ (CSS, JS, images)
├── docs/ (25+ markdown files)
└── vendor/ (dependencies)

Total: ~23MB, 90 PHP files
```

---

## ✅ What Already Exists (Foundation Strengths)

### 1. Feature Registry System ⭐
**File:** `includes/core/class-wps-feature-registry.php`

**Current Capabilities:**
- ✅ Auto-discovery of features from `/includes/features/`
- ✅ Site-level and network-level toggle storage (wp_options)
- ✅ Feature enable/disable with capability checks
- ✅ Sub-feature support (each feature can have sub-options)
- ✅ Multisite awareness (site_option for network level)
- ✅ Settings caching

**Ideal For:**
- Guardian System feature discovery
- Issue detection toggle system
- User preference management

### 2. Abstract Feature Class ⭐
**File:** `includes/features/class-wps-feature-abstract.php` (488 lines)

**Current Structure:**
```php
class WPSHADOW_Abstract_Feature implements WPSHADOW_Feature_Interface {
    - id, name, description
    - scope, hub, spoke (navigation)
    - default_enabled, license_level
    - minimum_capability (permission checks)
    - sub_features (array of options)
    - widget_group, widget_label, widget_description
    - icon, category, priority, aliases
}
```

**Methods Provided:**
- register() - Hook registration
- is_enabled() - Check if feature active
- is_sub_feature_enabled() - Check sub-option
- register_default_settings() - Store settings
- get_feature_setting() - Retrieve settings

**Perfect For:**
- Creating Guardian System feature
- Issue detection feature
- Reporting feature

### 3. Options-Based Storage ⭐
**Existing Patterns:**
- `wpshadow_feature_toggles` - Feature on/off state
- `wpshadow_feature_toggles_network` - Network level
- `wpshadow_capability_map` - Custom capabilities
- `wpshadow_gamification_stats` - Stats storage

**Ideal For:**
- Issue storage (`wpshadow_detected_issues`)
- Report history (`wpshadow_reports_history`)
- SaaS preferences (`wpshadow_saas_settings`)
- Email settings (`wpshadow_email_preferences`)

### 4. Admin Dashboard System ⭐
**Files:** 
- `includes/admin/class-wps-dashboard-registry.php`
- `includes/admin/class-wps-dashboard-widgets.php`
- `includes/admin/class-wps-tab-navigation.php`

**Current Capabilities:**
- ✅ Tab-based navigation system (hub/spoke model)
- ✅ Dashboard widget registry
- ✅ Widget grouping and organization
- ✅ Multisite admin support
- ✅ Screen options and help system

**Can Be Used For:**
- "Reports" tab creation
- Dashboard widget display
- Navigation integration

### 5. AJAX Infrastructure ⭐
**Files:**
- `includes/admin/class-wps-settings-ajax.php`
- `includes/helpers/wps-ajax-helpers.php`
- Global AJAX handlers in `wpshadow.php`

**Current Pattern:**
- Nonce verification
- Capability checks
- Error handling
- JSON responses

**Perfect For:**
- Issue snooze AJAX
- Issue dismiss AJAX
- Auto-fix AJAX
- Issue detection trigger

### 6. Gamification System ⭐
**File:** `includes/core/class-wps-gamification.php` (515 lines)

**Current:**
- 10 achievement badges
- Daily achievement checks
- Dashboard widget
- Options-based storage

**Integration Point:**
- Can award badges for issues detected/fixed
- Can reward users for preventive actions

### 7. Privacy & GDPR Framework ⭐
**File:** `includes/core/class-wps-privacy-handler.php` (391 lines)

**Current:**
- WordPress privacy hooks integration
- Exporters & erasers defined
- GDPR/CCPA/LGPD compliance

**For Guardian System:**
- Can export issue history
- Can erase issue records on request
- Already compliant for data collection

### 8. Tips Coach Feature ⭐
**File:** `includes/features/class-wps-feature-tips-coach.php` (730 lines)

**Current:**
- Site-specific tips
- Troubleshooting suggestions
- Video walkthrough library
- AJAX endpoints for tips

**Integration Point:**
- Can surface issues as tips
- Can link to learning resources
- Can show tutorials for fixes

---

## ⚠️ What's Missing (Gaps to Address)

### 1. Issue Detection Framework ❌
**Missing:** Centralized issue detection system

**Needed:**
- `includes/core/class-wps-issue-detection.php`
- `includes/core/class-wps-issue-registry.php`
- Issue categories (Critical/High/Medium/Low)
- Issue templates (name, description, why it matters)
- Detection methods per issue
- Severity classification logic

**Impact:** Core to Guardian System

---

### 2. Predictive Analysis Engine ❌
**Missing:** Forward-looking issue prediction

**Needed:**
- `includes/core/class-wps-predictive-analyzer.php`
- `includes/core/class-wps-trend-tracker.php`
- `includes/core/class-wps-confidence-scorer.php`
- Historical trend storage
- Forecast logic (month-ahead prediction)
- Confidence scoring (0-100%)

**Impact:** Differentiator from basic monitoring

---

### 3. Issue Repository & Storage ❌
**Missing:** Persistent issue history system

**Needed:**
- Issue detection results storage
- Historical issue snapshots
- Issue status tracking (open/resolved/snoozed)
- User dismissal tracking
- Report generation from snapshots

**Storage Strategy:**
- Use `wp_options` table as per user preference
- Store as JSON: `wpshadow_detected_issues_[timestamp]`
- Keep 90 days history
- Archive old reports to option: `wpshadow_reports_archive`

---

### 4. Reports Tab/Page ❌
**Missing:** New admin page for Reports

**Needed:**
- `includes/admin/class-wps-reports-page.php`
- `includes/views/reports-page.php`
- `assets/js/reports-page.js`
- `assets/css/reports-page.css`
- Dashboard registration in tab system
- Filter/search interface
- Export to PDF functionality
- Historical comparison UI

**Integration:** Should hook into existing dashboard registry

---

### 5. Email Reporting System ❌
**Missing:** Email digest generation & sending

**Needed:**
- `includes/core/class-wps-email-reporter.php`
- Email template for TLDR digest
- Scheduled sending (weekly by default)
- User permission/subscription management
- Email preference settings
- Unsubscribe functionality
- Multisite email routing

**Storage:**
- `wpshadow_email_preferences` - User opt-in
- `wpshadow_email_last_sent` - Last send timestamp
- `wpshadow_email_frequency` - Weekly/Monthly/etc

---

### 6. Snoozing & Dismissal System ❌
**Missing:** User control over issue visibility

**Needed:**
- `includes/core/class-wps-issue-control.php`
- Snooze logic (specific/by-type/all)
- Snooze duration handling
- Dismissal permanent storage
- AJAX endpoints for snooze/dismiss

**Storage:**
- `wpshadow_snoozed_issues` - Active snoozes
- `wpshadow_dismissed_issues` - User has opted out

---

### 7. Auto-Fix System ❌
**Missing:** Automated issue resolution framework

**Needed:**
- `includes/core/class-wps-issue-autofix.php`
- Auto-fix handlers per fixable issue
- Permission/capability checks per fix
- User confirmation flow
- Fix result logging
- Rollback capability

**Issues Fixable (Initial 3):**
- Enable pretty permalinks
- Add site description
- Optimize database

---

### 8. Documentation Link Strategy ❌
**Missing:** Unified documentation routing

**Needed:**
- `includes/core/class-wps-documentation-router.php`
- Links to WordPress.org docs (primary)
- Links to wpshadow.com free docs (secondary)
- Links to learning modules (tertiary)
- "By the way" mention of Pro features (non-pushy)

---

### 9. SaaS Integration Framework ❌
**Missing:** Cloud features integration layer

**Needed:**
- `includes/core/class-wps-saas-connector.php`
- `includes/core/class-wps-ai-suggestions.php`
- `includes/core/class-wps-token-manager.php`
- User registration detection
- Token usage tracking
- Privacy/GDPR sign-off modal
- Cloud API communication

**Storage:**
- `wpshadow_saas_settings` - User preference
- `wpshadow_saas_user_id` - Linked wpshadow.com account
- `wpshadow_saas_tokens_used` - Monthly token count
- `wpshadow_saas_privacy_agreed` - GDPR sign-off

---

### 10. Scheduled Issue Scanning ❌
**Missing:** Background job for issue detection

**Needed:**
- Scheduled WP-Cron or Action Scheduler integration
- Daily issue scan
- Incremental vs. full scan options
- Off-hours scheduling option
- Scan progress tracking
- Error recovery for long scans

---

### 11. Issue Categories (15+) ❌
**Missing:** Comprehensive issue definitions

**Needed:**
- Issue catalog with 15+ initial categories
- Detection logic for each issue
- Why/impact explanation
- Fix availability mapping
- WordPress.org link per issue
- wpshadow.com doc per issue

**Issues to Define:**
1. SSL/HTTPS not configured
2. No backup plugin configured
3. Core/plugins need updates
4. PHP version below recommended
5. File uploads disabled
6. Memory limit too low
7. Database not optimized
8. Plugin conflicts detected
9. Missing caching layer
10. Too many plugins active
11. Site description missing
12. Permalinks not configured
13. XML sitemap not generated
14. No analytics configured
15. Favicon missing

---

### 12. Multisite Dashboard ❌
**Missing:** Network admin view of all sites' issues

**Needed:**
- Network admin dashboard showing all sites
- Drill-down to individual site issues
- Aggregated issue trends
- Compare across sites

---

## 🔧 Architectural Decisions

### 1. Storage Strategy: wp_options (Confirmed)
**Rationale:**
- User preference stated: "whenever possible use existing tables"
- wp_options supports any data structure
- WordPress handles multisite scoping automatically
- Familiar to WordPress developers
- No schema migrations needed

**Implementation:**
```php
// Single issue detection result
wpshadow_detected_issues = array(
    'ssl_not_configured' => array(
        'detected_at' => time(),
        'severity' => 'critical',
        'can_fix' => false,
        'is_snoozed' => false,
        'is_dismissed' => false,
    ),
    'memory_limit_low' => array(...),
)

// Historical snapshot
wpshadow_report_2025_01_19 = array(
    'timestamp' => 1705680000,
    'total_issues' => 5,
    'critical' => 1,
    'high' => 2,
    'medium' => 2,
    'low' => 0,
    'issues' => [...], // Full issue list
)

// Email settings
wpshadow_email_preferences = array(
    'enabled' => true,
    'frequency' => 'weekly',
    'opt_in_date' => time(),
    'send_critical' => true,
    'send_high' => true,
    'send_medium' => false,
)
```

### 2. Feature Integration: As New Guardian Feature
**Approach:**
- Create `class-wps-feature-guardian.php` as the core feature
- Guardian feature includes:
  - Issue detection engine
  - Reporting system
  - Email notifications
  - Dashboard integration

**Sub-Features:**
```php
'guardian' => array(
    'detect_issues' => true,      // Enable/disable detection
    'email_reports' => true,      // Enable/disable email
    'dashboard_reports' => true,  // Enable/disable dashboard
    'auto_fix_enabled' => true,   // Enable/disable auto-fix
)
```

### 3. Issue Detection Hooks
**Pattern:**
```php
// Detection happens via:
apply_filters( 'wpshadow_detect_issues', array $issues )
do_action( 'wpshadow_after_detect_issues', array $issues )

// Per-issue:
apply_filters( 'wpshadow_detect_{$issue_id}', bool $detected, array $context )
```

### 4. SaaS Requirement: Only Registered Users
**Implementation:**
```php
// wpshadow.com registration required for:
- AI suggestions
- SaaS features
- Access to Mailpoet newsletters
- Access to Sensei LMS training

// Stored in option:
wpshadow_saas_user = array(
    'user_id' => 12345, // From wpshadow.com
    'email' => 'admin@example.com',
    'registered_at' => time(),
    'registration_url' => 'https://wpshadow.com',
    'site_url' => 'https://example.com',
)
```

---

## 📋 Phase 1 Implementation Files Checklist

### Files to Create (Phase 1):
```
includes/core/
├── [ ] class-wps-issue-detection.php
├── [ ] class-wps-issue-registry.php
├── [ ] class-wps-issue-repository.php
├── [ ] class-wps-reporting-engine.php

includes/features/
├── [ ] class-wps-feature-guardian.php

includes/admin/
├── [ ] class-wps-reports-page.php
├── [ ] class-wps-reports-handler.php

includes/views/
├── [ ] reports-dashboard.php
├── [ ] reports-filters.php
├── [ ] reports-history.php

assets/js/
├── [ ] reports-page.js

assets/css/
├── [ ] reports-page.css
```

### Files to Modify:
```
[ ] wpshadow.php - Initialize Guardian feature
[ ] includes/admin/screens.php - Add Reports tab help
[ ] includes/admin/class-wps-tab-navigation.php - Register Reports tab
```

---

## 🎯 Success Criteria (Phase 1)

- ✅ Issue detection framework complete
- ✅ At least 5 core issues detectable
- ✅ Dashboard Reports tab functional
- ✅ Email system working with weekly digest
- ✅ Snooze/dismiss system operational
- ✅ Auto-fix working for 3 issues
- ✅ Documentation links functional
- ✅ PHP syntax validation passes
- ✅ No errors in WordPress debug logs
- ✅ All settings stored in wp_options

---

## 📌 Notes for Implementation

### Multisite Considerations
- Use `get_option()` for current site issues
- Use `get_site_option()` for network-wide settings
- Network admin can see all sites' issues
- Each site has independent issue snapshots

### Performance Considerations
- Issue detection runs daily (not on every page load)
- Cache issue detection results
- Store only last 90 days of history
- Archive older reports to single option
- Lazy-load historical reports

### Security Considerations
- Require `manage_options` capability for all issue/report access
- Sanitize/validate all user input
- Nonce verification on AJAX
- No data exposure outside wp-admin

### User Experience
- First-run full scan with chunking option
- Clear, positive tone in issue descriptions
- "You can do it!" attitude toward solutions
- Easy dismissal/snoozing
- Don't overwhelm with too many issues at once

---

## Next Steps

This architecture review confirms:
1. ✅ Strong foundation exists (Feature Registry, Abstract Class, Options Storage)
2. ✅ Admin dashboard system ready for Reports tab
3. ✅ No schema migrations needed (wp_options sufficient)
4. ✅ Integration points clear (hooks, filters, AJAX pattern)

**Ready to create GitHub issues for Phase 1 implementation.**
