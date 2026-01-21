# WPShadow Guardian System - Phase 1 GitHub Issues

**Repository:** thisismyurl/wpshadow  
**Epic:** WPShadow Guardian System - Phase 1 Foundation  
**Timeline:** Weeks 1-4  
**Effort:** ~40-50 development hours

---

## Phase 1 Overview

Phase 1 implements the foundation of the Guardian System: detecting WordPress issues, storing them, displaying in a dashboard, and sending email reports. This phase creates the core framework that all future phases build upon.

**Phase 1 Deliverables:**
- ✅ Core issue detection framework
- ✅ 5 initial issue detectors
- ✅ Admin Reports dashboard
- ✅ Weekly email notification system
- ✅ Snooze/dismiss functionality
- ✅ Auto-fix for 3 common issues
- ✅ Documentation link system
- ✅ Guardian feature wrapper

---

## Issue 1.1: Create Core Issue Detection Framework

**Title:** [PHASE-1] Create core issue detection framework  
**Type:** Epic  
**Priority:** 🔴 Critical (Blocks all other Phase 1 work)  
**Estimated Hours:** 6

**Description:**
Create the foundational architecture that all issue detectors build upon. This defines how issues are defined, detected, and stored.

**Acceptance Criteria:**
- [ ] Create `includes/core/class-wps-issue-detection.php` (~200 lines)
  - Base class that all detectors extend
  - Methods: `detect()`, `get_severity()`, `is_fixable()`, `get_documentation_links()`
  - Proper error handling and logging
- [ ] Create `includes/core/class-wps-issue-registry.php` (~100 lines)
  - Singleton to manage all detectors
  - Methods: `register_detector()`, `get_detectors()`, `detect_all_issues()`
  - Auto-discovers detectors in `includes/detectors/`
- [ ] Define issue data structure:
  ```php
  [
    'id' => 'ssl_not_configured',
    'name' => 'SSL/HTTPS Not Configured',
    'description' => '...',
    'severity' => 'critical',
    'fixable' => false,
    'documentation' => ['wordpress.org' => '...', 'wpshadow.com' => '...'],
    'detected_at' => 1234567890,
    'last_check' => 1234567890,
  ]
  ```
- [ ] Severity levels: critical, high, medium, low
- [ ] Unit tests for both classes (>90% coverage)
- [ ] No PHP errors/warnings
- [ ] Follows WPShadow coding standards
- [ ] PHPDoc blocks on all methods

**Technical Details:**
- Both classes extend `WPSHADOW_Abstract_Feature` or use singleton pattern
- Registry uses WordPress hooks: `wpshadow_register_issue_detectors`
- Detectors stored in `wp_options`: `wpshadow_issue_definitions`
- All detection uses WordPress native functions (no custom SQL initially)

**Dependencies:** None (foundation)  
**Dependent Issues:** 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 1.8

**Subtasks:**
- [ ] 1.1.1 - Create issue detection base class
- [ ] 1.1.2 - Create issue registry singleton
- [ ] 1.1.3 - Write unit tests
- [ ] 1.1.4 - Add PHPDoc and inline comments

---

## Issue 1.2: Create Issue Repository & Storage System

**Title:** [PHASE-1] Create issue repository and storage layer  
**Type:** Feature  
**Priority:** 🔴 Critical (Needed by reporting)  
**Estimated Hours:** 4

**Description:**
Build the persistence layer for storing detected issues and historical snapshots using WordPress options table.

**Acceptance Criteria:**
- [ ] Create `includes/core/class-wps-issue-repository.php` (~150 lines)
  - Methods: `store_issue()`, `get_issues()`, `delete_issue()`, `get_history()`
  - CRUD operations for current and historical issues
- [ ] Current issues stored in: `wpshadow_detected_issues`
  ```php
  [
    'ssl_not_configured' => [
      'detected_at' => 1234567890,
      'severity' => 'critical',
      'can_auto_fix' => false,
    ],
    'site_description_empty' => [...],
  ]
  ```
- [ ] Historical snapshots stored: `wpshadow_report_[YYYYMMDD]`
  - One option per day with that day's snapshot
  - Snapshot includes: timestamp, issue count, severity breakdown, all issues
- [ ] Multisite support:
  - Site-level: stored in that site's options
  - Network level: stored in network options (future)
- [ ] Automatic cleanup:
  - Delete snapshots >90 days old
  - Archive >30 days old to summary
- [ ] Performance:
  - Snapshots are JSON (gzipped if large)
  - Queries optimized
  - No N+1 operations
- [ ] Unit tests (>90% coverage)
- [ ] All data JSON-serializable

**Storage Schema Detail:**
```php
// Current detection (wp_options)
'wpshadow_detected_issues' => serialize([
  'ssl_not_configured' => [
    'id' => 'ssl_not_configured',
    'detected_at' => 1705700000,
    'severity' => 'critical',
    'name' => 'SSL/HTTPS Not Configured',
  ],
  // ... more issues
])

// Daily snapshot (wp_options)
'wpshadow_report_20250117' => serialize([
  'timestamp' => 1705699200,
  'total_issues' => 3,
  'severity_breakdown' => [
    'critical' => 1,
    'high' => 2,
    'medium' => 0,
    'low' => 0,
  ],
  'issues' => [...all issues at that time],
])
```

**Dependencies:** Issue 1.1  
**Dependent Issues:** 1.4, 1.5

**Subtasks:**
- [ ] 1.2.1 - Create repository class with CRUD operations
- [ ] 1.2.2 - Implement daily snapshot functionality
- [ ] 1.2.3 - Create cleanup/archival routine
- [ ] 1.2.4 - Write tests with multisite scenarios

---

## Issue 1.3: Implement 5 Core Issue Detectors

**Title:** [PHASE-1] Implement 5 core issue detectors  
**Type:** Feature  
**Priority:** 🔴 Critical (Phase 1 MVP)  
**Estimated Hours:** 8

**Description:**
Implement detection logic for 5 highest-priority WordPress issues that affect site health and security.

**Issue 1: SSL/HTTPS Not Configured**
- File: `includes/detectors/class-wps-detector-ssl-configuration.php`
- Detection: Check if `is_ssl()` returns false
- Severity: Critical
- Auto-fixable: No (requires manual setup)
- Documentation: Link to SSL setup guide

**Issue 2: Site Description Empty**
- File: `includes/detectors/class-wps-detector-site-description.php`
- Detection: `get_option('blogdescription')` is empty or default
- Severity: Low
- Auto-fixable: Yes
- Documentation: Why site description matters

**Issue 3: Permalinks Not Configured (Still Plain)**
- File: `includes/detectors/class-wps-detector-permalinks.php`
- Detection: `get_option('permalink_structure')` is empty
- Severity: Medium
- Auto-fixable: Yes
- Documentation: Permalink setup guide

**Issue 4: No Backup Plugin Detected**
- File: `includes/detectors/class-wps-detector-backup-plugin.php`
- Detection: Check if any backup plugin is active (check 3-5 common ones)
- Severity: High
- Auto-fixable: No
- Documentation: Recommended backup plugins

**Issue 5: PHP Memory Limit Too Low**
- File: `includes/detectors/class-wps-detector-memory-limit.php`
- Detection: `WP_MEMORY_LIMIT` < 64MB
- Severity: Medium
- Auto-fixable: No
- Documentation: How to increase memory limit

**Acceptance Criteria (All Detectors):**
- [ ] Each detector extends `WPSHADOW_Issue_Detection`
- [ ] `detect()` method returns bool (issue found?)
- [ ] Each detector has test coverage (>95% accuracy)
- [ ] False positive rate <5%
- [ ] All use WordPress native functions (no custom queries)
- [ ] Documentation links included for each
- [ ] Detectors auto-register in registry
- [ ] No performance impact (under 100ms per detector)
- [ ] Proper logging/debugging info
- [ ] PHPDoc on all methods

**Detector Pattern:**
```php
class WPSHADOW_Detector_SSL_Configuration extends WPSHADOW_Issue_Detection {
    protected string $id = 'ssl_not_configured';
    protected string $name = 'SSL/HTTPS Not Configured';
    
    public function detect(): bool {
        return !is_ssl();
    }
    
    public function get_severity(): string {
        return 'critical';
    }
    
    public function is_fixable(): bool {
        return false;
    }
    
    public function get_documentation_links(): array {
        return [
            'wordpress.org' => 'https://...',
            'wpshadow.com' => 'https://...',
            'learn' => 'https://...',
        ];
    }
}
```

**Dependencies:** Issue 1.1  
**Dependent Issues:** 1.4, 1.5, 1.7

**Subtasks:**
- [ ] 1.3.1 - Implement SSL detector
- [ ] 1.3.2 - Implement site description detector
- [ ] 1.3.3 - Implement permalinks detector
- [ ] 1.3.4 - Implement backup plugin detector
- [ ] 1.3.5 - Implement memory limit detector
- [ ] 1.3.6 - Write tests and verify accuracy

---

## Issue 1.4: Create Reports Dashboard Tab

**Title:** [PHASE-1] Create Reports tab in admin dashboard  
**Type:** Feature  
**Priority:** 🔴 Critical (Core UI)  
**Estimated Hours:** 8

**Description:**
Add Reports tab to WPShadow admin showing current and historical issues with actionable controls.

**Acceptance Criteria:**
- [ ] Create `includes/admin/class-wps-reports-page.php` (~250 lines)
- [ ] Create Reports tab in main WPShadow navigation
- [ ] Display all current issues grouped by severity
- [ ] For each issue show:
  - Icon (🔴 critical, 🟠 high, 🟡 medium, 🟢 low)
  - Issue name and brief description
  - "Detected at" timestamp
  - Action buttons: [Fix Now] [Learn More] [Snooze ▼] [Dismiss]
- [ ] Issues sortable by: Severity, Name, Detected Time
- [ ] Filters available:
  - By severity (checkboxes: Critical, High, Medium, Low)
  - By fixability (Fixable, Not Fixable)
  - Search by name/description
- [ ] [Refresh Now] button for manual scan
- [ ] [PDF Export] button for current report
- [ ] [Show History] link to historical reports section
- [ ] Responsive design (mobile-friendly)
- [ ] Load time <2 seconds
- [ ] No JavaScript console errors
- [ ] Accessibility: WCAG AA compliant

**UI Template:**
```
┌──────────────────────────────────────────────────┐
│ ⚙️ Guardian Reports                  [Refresh] [PDF] │
├──────────────────────────────────────────────────┤
│ Last scanned: Today at 2:30 PM  (3 issues)      │
├─ Filter ─────────────────────────────────────────┤
│ ☑ Critical  ☑ High  ☑ Medium  ☑ Low            │
│ 🔍 Search: [_____________]  [Apply]             │
├──────────────────────────────────────────────────┤
│                                                  │
│ 🔴 CRITICAL (1)                                 │
│ ┌─────────────────────────────────────────────┐ │
│ │ SSL/HTTPS Not Configured                    │ │
│ │ Your site is not using HTTPS, which can...  │ │
│ │ Detected: 3 hours ago                       │ │
│ │ [Fix Now] [Learn More] [Snooze ▼] [Dismiss]│ │
│ └─────────────────────────────────────────────┘ │
│                                                  │
│ 🟠 HIGH (2)                                     │
│ ┌─────────────────────────────────────────────┐ │
│ │ No Backup Plugin Detected                   │ │
│ │ Your site has no backup solution...         │ │
│ │ [Learn More] [Snooze ▼] [Dismiss]           │ │
│ └─────────────────────────────────────────────┘ │
│                                                  │
└──────────────────────────────────────────────────┘
```

**Files to Create:**
- `includes/admin/class-wps-reports-page.php` (main page class)
- `assets/css/reports-dashboard.css` (styling)
- `assets/js/reports-dashboard.js` (filtering, sorting)
- `includes/views/reports-dashboard-template.php` (HTML template)

**Dependencies:** Issue 1.1, 1.2, 1.3  
**Dependent Issues:** 1.5, 1.6, 1.7

**Subtasks:**
- [ ] 1.4.1 - Register Reports tab in dashboard navigation
- [ ] 1.4.2 - Create reports page template with HTML
- [ ] 1.4.3 - Create filtering and sorting UI
- [ ] 1.4.4 - Create CSS and responsive design
- [ ] 1.4.5 - Add accessibility attributes

---

## Issue 1.5: Implement Email Digest System

**Title:** [PHASE-1] Implement weekly email notification system  
**Type:** Feature  
**Priority:** 🔴 Critical (Key deliverable)  
**Estimated Hours:** 8

**Description:**
Build email notification system that sends weekly TLDR digest of critical/high issues to site admin.

**Acceptance Criteria:**
- [ ] Create `includes/core/class-wps-email-reporter.php` (~200 lines)
  - Methods: `queue_report()`, `send_report()`, `format_email_body()`
  - Handle scheduling and throttling
- [ ] Email Settings UI in admin (Guardian settings tab):
  - [ ] "Enable Email Reports" toggle (default: ON)
  - [ ] Frequency dropdown: Daily / Weekly (default) / Monthly / Disabled
  - [ ] Email recipient field (pre-filled with admin email)
  - [ ] "Test Email" button sends sample
- [ ] Email template (`includes/emails/weekly-report.php`):
  - [ ] Subject: `[WPShadow] Your Weekly Site Report - X Issues`
  - [ ] Greeting with site name
  - [ ] Critical issues section (always shown)
  - [ ] High issues section (if frequency allows)
  - [ ] Summary statistics
  - [ ] [View Full Report] link
  - [ ] [Manage Preferences] link
  - [ ] [Unsubscribe] link
  - [ ] Professional HTML + plain text fallback
- [ ] Scheduling:
  - [ ] Use WordPress scheduled events (wp_schedule_event)
  - [ ] Default: Weekly on Monday 8:00 AM (site timezone)
  - [ ] Only send if new issues since last report
  - [ ] Handle missed schedules gracefully
- [ ] Email content:
  - [ ] Only include issues not snoozed/dismissed
  - [ ] Show severity icon + name + brief description
  - [ ] Include action links: [Fix] [Learn] [Snooze] [Dismiss]
  - [ ] Include confidence score (later phases)
- [ ] Opt-in requirement:
  - [ ] First-run wizard prompts for email preference
  - [ ] "Don't Email Me" option must be visible
  - [ ] Store preference: `wpshadow_email_reports_enabled`
- [ ] Unsubscribe:
  - [ ] Unsubscribe link uses one-time token (nonce)
  - [ ] Takes user to "Manage Preferences" page
  - [ ] "Never Email Me Again" button works
- [ ] Testing:
  - [ ] Unit tests for email generation
  - [ ] Test with multisite (each site gets own email)
  - [ ] Verify no sending to non-existent recipients

**Email Template Structure:**
```
Subject: [WPShadow] Your Weekly Site Report - 3 Issues

Hi Admin,

Your WordPress site had some activity this week. Here's what we found:

🔴 CRITICAL ISSUES (1)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
• SSL/HTTPS Not Configured
  Your site is not using HTTPS. This is a critical security issue...
  [🔧 Fix This] [📖 Learn More] [🤐 Snooze]

🟠 HIGH PRIORITY (2)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
• No Backup Plugin Detected
  No backup solution is protecting your site...
  [📖 Learn More] [🤐 Snooze]

📊 SUMMARY
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Total Issues: 3
Critical: 1 | High: 2 | Medium: 0 | Low: 0
Last Scanned: Monday, Jan 17 @ 2:30 PM

[View Your Full Report] | [Manage Preferences] | [Unsubscribe]
```

**Dependencies:** Issue 1.1, 1.2, 1.3  
**Dependent Issues:** 1.11

**Subtasks:**
- [ ] 1.5.1 - Create email reporter class and scheduling
- [ ] 1.5.2 - Create email settings UI in admin
- [ ] 1.5.3 - Create email templates (HTML + text)
- [ ] 1.5.4 - Create unsubscribe handler
- [ ] 1.5.5 - Write tests and verify sending

---

## Issue 1.6: Implement Snooze & Dismissal System

**Title:** [PHASE-1] Implement issue snoozing and dismissal  
**Type:** Feature  
**Priority:** 🟠 High (Reduces alert fatigue)  
**Estimated Hours:** 5

**Description:**
Allow users to temporarily snooze or permanently dismiss issues they don't want to see.

**Acceptance Criteria:**
- [ ] Create `includes/core/class-wps-issue-control.php` (~150 lines)
  - Methods: `snooze_issue()`, `dismiss_issue()`, `is_snoozed()`, `is_dismissed()`
  - Proper capability checks
- [ ] Snooze functionality:
  - [ ] Duration options: 1 Hour / 1 Day / 1 Week / 1 Month / Forever
  - [ ] Apply to: This issue / All issues of this type
  - [ ] Store in: `wpshadow_snoozed_issues` (with expiration timestamp)
  - [ ] Hide snoozed issues from dashboard and email
  - [ ] Auto-unsnoze when duration expires
- [ ] Dismissal functionality:
  - [ ] "Don't show again" checkbox in UI
  - [ ] Store in: `wpshadow_dismissed_issues` (array of IDs)
  - [ ] Never show dismissed issues unless re-enabled
  - [ ] Admin can manage dismissed list
- [ ] AJAX endpoints:
  - [ ] `wpshadow_snooze_issue` - POST, verify nonce
  - [ ] `wpshadow_dismiss_issue` - POST, verify nonce
  - [ ] Both check `manage_options` capability
- [ ] Snooze expiration cleanup:
  - [ ] Background task checks expired snoozes daily
  - [ ] Remove expired entries from option
- [ ] Settings UI for re-enabling dismissed:
  - [ ] Guardian settings tab shows list of dismissed
  - [ ] "Re-enable" button next to each dismissed issue
  - [ ] "Re-enable All" button
- [ ] Multisite aware:
  - [ ] Each site's snoozes/dismissals separate
  - [ ] No mixing across sites

**Storage Schema:**
```php
// Snoozed issues
'wpshadow_snoozed_issues' => [
    'ssl_not_configured' => [
        'type' => 'specific',  // specific or category
        'until' => 1705786200,  // Unix timestamp (1 week from now)
    ],
    'memory_limit_low' => [
        'type' => 'forever',
        'until' => null,  // null means forever
    ],
]

// Dismissed issues
'wpshadow_dismissed_issues' => [
    'site_description_empty',
    'xml_sitemap_disabled',
]
```

**Dependencies:** Issue 1.1, 1.2, 1.4  
**Dependent Issues:** 1.10

**Subtasks:**
- [ ] 1.6.1 - Create issue control class with snooze/dismiss logic
- [ ] 1.6.2 - Create AJAX endpoints with security checks
- [ ] 1.6.3 - Create snooze UI in dashboard (modals)
- [ ] 1.6.4 - Create management UI for dismissed issues
- [ ] 1.6.5 - Create expiration cleanup routine

---

## Issue 1.7: Implement Auto-Fix System (3 Issues)

**Title:** [PHASE-1] Implement auto-fix for 3 common issues  
**Type:** Feature  
**Priority:** 🟠 High (User delight)  
**Estimated Hours:** 6

**Description:**
Create framework for automatic resolution of 3 common configuration issues with user confirmation.

**Auto-Fixable Issues:**

**Issue 1: Enable Pretty Permalinks**
- Issue ID: `permalinks_plain`
- Fix action: Set option `permalink_structure` to `/%postname%/`
- Requires capability: `manage_options`
- Success message: "Permalinks configured successfully"
- Revert: Provide URL to re-enable plain permalinks if needed

**Issue 2: Add Site Description**
- Issue ID: `site_description_empty`
- Fix action: Set option `blogdescription` to suggested text
- Requires capability: `manage_options`
- Suggestion template: "A WordPress site about [site name]"
- Allow user to customize text before confirming
- Success message: "Site description updated"

**Issue 3: Optimize Database**
- Issue ID: `database_not_optimized`
- Fix action: Run `OPTIMIZE TABLE` on wp_posts, wp_postmeta, wp_options
- Requires capability: `manage_options`
- Show estimated cleanup size before confirming
- Success message: "Database optimized (X KB freed)"

**Acceptance Criteria:**
- [ ] Create `includes/core/class-wps-issue-autofix.php` (~200 lines)
  - Methods: `can_fix_issue()`, `apply_fix()`, `rollback_fix()`, `get_fix_details()`
- [ ] Before applying any fix:
  - [ ] Show confirmation modal to user
  - [ ] Display what will be changed (clearly)
  - [ ] Get explicit user consent
  - [ ] Verify user has required capability
- [ ] After fix attempt:
  - [ ] Log success/failure with details
  - [ ] Update issue status to resolved
  - [ ] Show clear success/error message
  - [ ] Re-run detector to verify fix worked
- [ ] Fixes are reversible:
  - [ ] Store original values before changes
  - [ ] Provide "Undo" option for 1 hour after fix
  - [ ] Store undo data in `wpshadow_fix_rollback_[issue_id]`
- [ ] AJAX endpoint: `wpshadow_trigger_fix`
  - [ ] Verify nonce
  - [ ] Check capability
  - [ ] Validate issue exists
  - [ ] Apply fix and return result
- [ ] No fixes without explicit user action
- [ ] All changes logged to error log

**Fix Confirmation UI:**
```
┌─────────────────────────────────────────────────┐
│ Ready to Fix: Enable Pretty Permalinks          │
├─────────────────────────────────────────────────┤
│ This will change:                               │
│                                                 │
│ Permalink Structure                             │
│   Before: /?p=123                               │
│   After:  /sample-post/                         │
│                                                 │
│ This change can be undone for 1 hour.          │
│                                                 │
│ [✓ Yes, Fix This] [✗ Cancel]                   │
└─────────────────────────────────────────────────┘
```

**Dependencies:** Issue 1.1, 1.4  
**Dependent Issues:** None

**Subtasks:**
- [ ] 1.7.1 - Create AutoFix class structure
- [ ] 1.7.2 - Implement permalink fix
- [ ] 1.7.3 - Implement site description fix
- [ ] 1.7.4 - Implement database optimization fix
- [ ] 1.7.5 - Create confirmation UI and AJAX handler
- [ ] 1.7.6 - Write tests for each fix

---

## Issue 1.8: Create Documentation Link System

**Title:** [PHASE-1] Implement three-tier documentation routing  
**Type:** Feature  
**Priority:** 🟠 High (User education)  
**Estimated Hours:** 4

**Description:**
Create system routing users to appropriate documentation: WordPress.org → wpshadow.com → Learning modules.

**Acceptance Criteria:**
- [ ] Documentation registry for each issue:
  - [ ] Tier 1: Link to wordpress.org (authoritative)
  - [ ] Tier 2: Link to wpshadow.com doc (user-friendly)
  - [ ] Tier 3: Link to interactive learning module
  - [ ] Brief description of each resource
- [ ] Links visible in three places:
  - [ ] Dashboard reports (each issue card)
  - [ ] Email reports (as action links)
  - [ ] Issue details modal/page
- [ ] Pro/SaaS mention (light touch):
  - [ ] Optional "By the way" section
  - [ ] "WPShadow Pro can handle this automatically"
  - [ ] Non-pushy, informational tone
- [ ] Link validation:
  - [ ] All links tested and valid
  - [ ] 404s caught before display
  - [ ] Links open in new tab
  - [ ] Track clicks for analytics (later)
- [ ] UI integration:
  - [ ] [📖 Learn More] button in dashboard
  - [ ] Expand to show 3 documentation tiers
  - [ ] Icons for each tier (official, guide, interactive)

**Documentation Registry:**
```php
'ssl_not_configured' => [
    'tier_1' => [
        'label' => 'Official WordPress Guide',
        'url' => 'https://wordpress.org/documentation/article/...',
        'icon' => '🌟',
    ],
    'tier_2' => [
        'label' => 'WPShadow Learning',
        'url' => 'https://wpshadow.com/learn/ssl-configuration/',
        'icon' => '📖',
    ],
    'tier_3' => [
        'label' => 'Interactive Module',
        'url' => 'https://wpshadow.com/modules/ssl-setup/',
        'icon' => '🎓',
    ],
    'pro_mention' => [
        'show' => true,
        'text' => 'WPShadow Pro can configure SSL for you automatically.',
        'link' => 'https://wpshadow.com/pro/',
    ],
]
```

**Dependencies:** Issue 1.3  
**Dependent Issues:** None (standalone)

**Subtasks:**
- [ ] 1.8.1 - Create documentation registry structure
- [ ] 1.8.2 - Add links to all 5 initial detectors
- [ ] 1.8.3 - Create UI component for displaying links
- [ ] 1.8.4 - Verify all links are valid
- [ ] 1.8.5 - Test link opening and tracking

---

## Issue 1.9: Create Guardian Feature Class

**Title:** [PHASE-1] Create main Guardian feature wrapper  
**Type:** Feature  
**Priority:** 🔴 Critical (Integration point)  
**Estimated Hours:** 4

**Description:**
Create the Guardian feature that ties all Phase 1 components into a cohesive system.

**Acceptance Criteria:**
- [ ] Create `includes/features/class-wps-feature-guardian.php` (~150 lines)
- [ ] Extends `WPSHADOW_Abstract_Feature`
- [ ] Feature metadata:
  - [ ] ID: `guardian`
  - [ ] Name: "WordPress Guardian"
  - [ ] Description: "Proactively detects WordPress issues and alerts you before problems occur"
  - [ ] Category: "monitoring"
  - [ ] Icon: 🛡️
- [ ] Sub-features defined:
  - [ ] `detect_issues` (default: enabled) - Enable/disable detection
  - [ ] `email_reports` (default: enabled) - Send email digests
  - [ ] `dashboard_reports` (default: enabled) - Show dashboard reports
  - [ ] `auto_fix` (default: enabled) - Allow automatic fixes
- [ ] Initialization in `register()` method:
  - [ ] Initialize issue registry
  - [ ] Register all 5 detectors
  - [ ] Initialize repository
  - [ ] Hook email scheduler
  - [ ] Register admin pages and AJAX handlers
- [ ] Dashboard widget integration:
  - [ ] Widget shows: "X issues detected"
  - [ ] Red/orange/yellow/green status indicator
  - [ ] Quick links to dashboard and settings
- [ ] Capability requirements: `manage_options`
- [ ] Auto-discovered by feature registry

**Feature Structure:**
```php
class WPSHADOW_Feature_Guardian extends WPSHADOW_Abstract_Feature {
    protected string $id = 'guardian';
    protected string $name = 'WordPress Guardian';
    
    public function register(): void {
        // Initialize all components
        $this->init_detection_registry();
        $this->register_all_detectors();
        $this->init_repository();
        $this->setup_scheduler();
        $this->register_admin_pages();
        $this->register_ajax_handlers();
    }
    
    // Helper methods to initialize each component
}
```

**Dependencies:** All other Phase 1 issues (1.1-1.8)  
**Dependent Issues:** 1.10, 1.11, 1.12

**Subtasks:**
- [ ] 1.9.1 - Create Guardian feature class structure
- [ ] 1.9.2 - Define sub-features and settings
- [ ] 1.9.3 - Implement component initialization
- [ ] 1.9.4 - Create dashboard widget
- [ ] 1.9.5 - Test auto-discovery and activation

---

## Issue 1.10: Create AJAX Handlers for Dashboard

**Title:** [PHASE-1] Create AJAX endpoints for reports dashboard  
**Type:** Feature  
**Priority:** 🟠 High (Dashboard functionality)  
**Estimated Hours:** 5

**Description:**
Implement AJAX endpoints for real-time dashboard actions (snooze, dismiss, fix, refresh).

**AJAX Endpoints (All POST):**

**1. `wpshadow_load_issues`**
- Purpose: Load current detected issues with filtering
- Parameters: `security`, `filter[severity]`, `filter[fixable]`, `search`
- Returns: JSON array of issues
- Caching: 5-minute transient cache
- Response time target: <200ms

**2. `wpshadow_snooze_issue`**
- Purpose: Snooze a specific issue
- Parameters: `security`, `issue_id`, `duration` (seconds or 'forever')
- Returns: `{success: true, message: "..."}` or `{success: false, error: "..."}`
- Capability: `manage_options`

**3. `wpshadow_dismiss_issue`**
- Purpose: Permanently dismiss an issue
- Parameters: `security`, `issue_id`
- Returns: `{success: true}` or `{success: false, error: "..."}`
- Capability: `manage_options`

**4. `wpshadow_trigger_fix`**
- Purpose: Apply auto-fix to an issue
- Parameters: `security`, `issue_id`, `confirmed` (bool)
- Returns: `{success: true, message: "Fixed!"}` or `{success: false, error: "..."}`
- Capability: `manage_options`
- Requires `confirmed: true` to actually apply

**5. `wpshadow_refresh_issues`**
- Purpose: Run detection immediately
- Parameters: `security`
- Returns: `{success: true, found: 3}` or `{success: false}`
- Capability: `manage_options`
- Runs detection asynchronously

**6. `wpshadow_get_history`**
- Purpose: Load historical reports
- Parameters: `security`, `date_from`, `date_to`
- Returns: JSON array of daily snapshots
- Caching: No cache (user requested)

**Acceptance Criteria:**
- [ ] All endpoints verify WordPress nonce
- [ ] All endpoints verify user capability (`manage_options`)
- [ ] All endpoints validate input parameters (sanitize/validate)
- [ ] All endpoints return proper JSON format
- [ ] All endpoints handle errors gracefully
- [ ] All endpoints log actions (for auditing)
- [ ] No sensitive data exposed in responses
- [ ] Performance: <500ms response time each
- [ ] Support multisite (each site's data separate)

**Response Format:**
```php
// Success response
{
  'success': true,
  'message': 'Issue snoozed for 7 days',
  'data': { /* context-specific */ }
}

// Error response
{
  'success': false,
  'error': 'User does not have permission',
  'error_code': 'insufficient_capability'
}
```

**Dependencies:** Issue 1.4, 1.6, 1.7, 1.9  
**Dependent Issues:** 1.11, 1.12

**Subtasks:**
- [ ] 1.10.1 - Create AJAX helper functions
- [ ] 1.10.2 - Implement all 6 endpoints
- [ ] 1.10.3 - Add security (nonce, capability) checks
- [ ] 1.10.4 - Add input validation
- [ ] 1.10.5 - Write tests for each endpoint

---

## Issue 1.11: Create First-Run User Experience

**Title:** [PHASE-1] Implement first-run setup and welcome flow  
**Type:** Feature  
**Priority:** 🟠 High (User onboarding)  
**Estimated Hours:** 4

**Description:**
Create welcoming first-run experience that explains Guardian and prompts for initial setup.

**Acceptance Criteria:**
- [ ] First-run detection:
  - [ ] Check if `wpshadow_guardian_first_run_complete` option exists
  - [ ] Show welcome modal on first plugin page visit after activation
  - [ ] Only show once (set flag after completion)
- [ ] Welcome modal content:
  - [ ] Title: "Welcome to WPShadow Guardian! 🛡️"
  - [ ] Subtitle: "Your WordPress health watchdog"
  - [ ] Brief explanation (2-3 sentences)
  - [ ] Icon/illustration
- [ ] Setup options:
  - [ ] Option 1: "Full Scan Now" - Run complete detection immediately
    - [ ] Show progress/spinner
    - [ ] Display results after completion
  - [ ] Option 2: "Smart Scan" (recommended) - Run in background
    - [ ] Explain it will scan in off-hours
    - [ ] Don't block UI
  - [ ] Option 3: "Skip for Now" - Dismiss modal
- [ ] Email preference prompt:
  - [ ] "Would you like to receive weekly email reports?"
  - [ ] Toggle: Yes / No (default: Yes)
  - [ ] Link: "Learn about email options"
- [ ] Completion:
  - [ ] Show summary of what was found
  - [ ] Link to full Reports dashboard
  - [ ] Set `wpshadow_guardian_first_run_complete` timestamp
- [ ] Modal styling:
  - [ ] Professional design
  - [ ] Responsive on mobile
  - [ ] Non-intrusive (not blocking all work)

**Modal Flow:**
```
[Modal] Welcome to WPShadow Guardian!
├─ Explanation
├─ Option 1: Full Scan Now
├─ Option 2: Smart Scan [RECOMMENDED]
├─ Option 3: Skip for Now
└─ Email preference toggle

Then either:
[Scanning...] → [Results] → [Complete]
or
[Scheduled] → [Continue] → [Complete]
```

**Dependencies:** Issue 1.9  
**Dependent Issues:** 1.12

**Subtasks:**
- [ ] 1.11.1 - Create welcome modal template
- [ ] 1.11.2 - Implement first-run detection
- [ ] 1.11.3 - Create scan trigger logic
- [ ] 1.11.4 - Create email preference UI
- [ ] 1.11.5 - Write tests

---

## Issue 1.12: Phase 1 Testing & Documentation

**Title:** [PHASE-1] Testing, documentation, and code review  
**Type:** Quality Assurance  
**Priority:** 🔴 Critical (Ship quality)  
**Estimated Hours:** 8

**Description:**
Comprehensive testing, documentation, and code review to ship Phase 1 with confidence.

**Acceptance Criteria:**

**Testing:**
- [ ] Unit tests written for all core classes
  - [ ] Target: >80% code coverage
  - [ ] Run: `phpunit --coverage-text`
  - [ ] All tests passing
- [ ] Integration tests:
  - [ ] Test full issue detection flow (detect → store → display → email)
  - [ ] Test multisite functionality
  - [ ] Test feature enable/disable
- [ ] Manual testing checklist:
  - [ ] Guardian feature activates without errors
  - [ ] All 5 detectors run and find test issues
  - [ ] Dashboard displays issues correctly
  - [ ] Filters work (severity, fixable)
  - [ ] Search works
  - [ ] Snooze button works
  - [ ] Dismiss button works
  - [ ] Fix button works (for fixable issues)
  - [ ] Email sends correctly
  - [ ] Unsubscribe link works
  - [ ] No JavaScript errors in console
  - [ ] Mobile responsive
- [ ] Performance testing:
  - [ ] Detection completes <30 seconds
  - [ ] Dashboard loads <2 seconds
  - [ ] Email generation <10 seconds
  - [ ] AJAX responses <500ms
- [ ] Security testing:
  - [ ] Nonce verification works
  - [ ] Capability checks work
  - [ ] XSS prevention verified
  - [ ] SQL injection prevention verified
  - [ ] CSRF protection verified
- [ ] Multisite testing:
  - [ ] Each site sees only its own issues
  - [ ] Email goes to correct site admin
  - [ ] Network admin cannot access other sites

**Documentation:**
- [ ] User documentation (wpshadow.com):
  - [ ] Overview of Guardian feature
  - [ ] How to read the Reports dashboard
  - [ ] How to snooze/dismiss issues
  - [ ] Email preferences and unsubscribe
  - [ ] FAQ: Common questions
- [ ] Developer documentation:
  - [ ] Code comments in all classes
  - [ ] PHPDoc on all methods
  - [ ] Detector implementation guide
  - [ ] Adding new detectors (tutorial)
- [ ] Release notes:
  - [ ] What's new in Phase 1
  - [ ] Breaking changes (if any)
  - [ ] Known issues/limitations
  - [ ] Performance impact

**Code Quality:**
- [ ] PHP linting (PHPCS):
  - [ ] Run: `phpcs includes/`
  - [ ] All errors fixed
  - [ ] No warnings
- [ ] PHP version check:
  - [ ] Tested on PHP 8.1+
  - [ ] No deprecated functions
- [ ] Security review:
  - [ ] Manual code review completed
  - [ ] No hardcoded credentials
  - [ ] Error messages don't leak info
- [ ] File structure:
  - [ ] All files in correct locations
  - [ ] Naming conventions followed
  - [ ] No unused imports

**Subtasks:**
- [ ] 1.12.1 - Write unit tests (all classes)
- [ ] 1.12.2 - Write integration tests
- [ ] 1.12.3 - Perform manual testing checklist
- [ ] 1.12.4 - Write user documentation
- [ ] 1.12.5 - Write developer documentation
- [ ] 1.12.6 - Code review and fixes
- [ ] 1.12.7 - Performance testing and optimization
- [ ] 1.12.8 - Prepare release notes

---

## Issue Dependencies & Timeline

**Week 1:**
- [ ] Issue 1.1 - Issue Detection Framework (4 days)
- [ ] Issue 1.2 - Repository & Storage (2 days)

**Week 2:**
- [ ] Issue 1.3 - 5 Detectors (4 days)
- [ ] Issue 1.8 - Documentation Links (1 day)

**Week 3:**
- [ ] Issue 1.4 - Reports Dashboard (4 days)
- [ ] Issue 1.5 - Email System (3 days)

**Week 4:**
- [ ] Issue 1.6 - Snooze/Dismiss (2 days)
- [ ] Issue 1.7 - Auto-Fix (2 days)
- [ ] Issue 1.9 - Guardian Feature (2 days)
- [ ] Issue 1.10 - AJAX Handlers (2 days)

**Week 4 (Cont.) + Week 5:**
- [ ] Issue 1.11 - First-Run UX (2 days)
- [ ] Issue 1.12 - Testing & Docs (4 days)

**Parallelizable Work:**
- Issues 1.4, 1.5, 1.6, 1.7 can work in parallel (all depend on 1.1-1.3)
- Issues 1.8 can start during Week 2

**Critical Path:**
`1.1 → 1.2 → 1.3 → {1.4, 1.5, 1.6, 1.7} → 1.9 → 1.10 → 1.11 → 1.12`

---

## Acceptance Criteria Summary

| Phase 1 Goal | Acceptance Criteria | Owner |
|--------------|-------------------|-------|
| Core framework | Issues 1.1-1.2 complete, unit tests pass | TBD |
| Initial detection | 5 detectors working, >95% accuracy | TBD |
| Dashboard MVP | Display issues, filters, <2s load | TBD |
| Email reports | Send weekly, unsubscribe works | TBD |
| User controls | Snooze/dismiss functional | TBD |
| Auto-fix | 3 issues auto-fixable with confirmation | TBD |
| Feature wrapper | Guardian feature active, all parts working | TBD |
| Testing complete | >80% coverage, manual checklist done | TBD |
| Ready to ship | All issues closed, documentation done | TBD |

---

## How to Use This Document

1. **Create Epic in GitHub:**
   - Title: "[EPIC] WPShadow Guardian System - Phase 1"
   - Add label: `phase-1`, `epic`
   - Estimated hours: 40-50

2. **Create Issues:**
   - Copy each Issue 1.X section as new GitHub issue
   - Add acceptance criteria as checklist
   - Link to this document
   - Assign labels: `phase-1`, `guardian`, `[PHASE-1]`

3. **Organize by Dependencies:**
   - Use GitHub milestone for "Phase 1"
   - Create project board with columns: Backlog, In Progress, In Review, Done
   - Link dependent issues

4. **Track Progress:**
   - Update issue status weekly
   - Mark completed issues as they're done
   - Report blockers in comments
   - Link PRs to issues

5. **Next Phase Planning:**
   - After Phase 1 complete, create Phase 2 issues from [GUARDIAN_GITHUB_ISSUES_PHASE2.md]
   - Link Phase 2 issues to Phase 1 Epic

---

**Document Status: Ready for GitHub Issue Creation**

This comprehensive Phase 1 specification includes 12 major issues covering ~40-50 hours of development time, structured with clear acceptance criteria, technical details, and implementation guidance.

**Next Step:** Create GitHub issues from this template, then proceed to Phase 2-4 planning.
