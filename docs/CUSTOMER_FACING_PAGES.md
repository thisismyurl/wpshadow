# WPShadow Customer-Facing Pages Audit

**Date:** February 4, 2026  
**Version:** 1.7035.1500  
**Status:** Complete Inventory

> **This document provides a comprehensive list of all pages visible to users in the WPShadow plugin, including their URLs, purposes, and access requirements.**

---

## Navigation Structure

WPShadow uses a hierarchical menu structure with a top-level menu and multiple submenus. All pages are accessible under the "WPShadow" admin menu (dashicons-shield-alt icon).

---

## Main Menu Pages

### 1. Dashboard (Main Landing Page)
- **Page Name:** Dashboard
- **URL:** `/wp-admin/admin.php?page=wpshadow`
- **Access Level:** `read` (all logged-in users)
- **Purpose:** Primary landing page showing site health overview, key metrics, recent activities, and quick actions
- **Features:**
  - Health status gauges (Security, Performance, Privacy, SEO)
  - Recent findings and issues
  - Quick scan buttons
  - Activity feed
  - Setup wizard (for new users)
  - KPI tracking widgets

---

### 2. Findings (Kanban Board)
- **Page Name:** Findings
- **URL:** `/wp-admin/admin.php?page=wpshadow-findings`
- **Access Level:** `read` (all logged-in users)
- **Purpose:** Interactive task board for organizing your site's to-do list (like a digital sticky note board). Manage all site issues and action items in one place
- **Features:**
  - Drag-and-drop issue management
  - Status columns: To Do, In Progress, Done
  - Issue filtering by category/severity
  - Batch operations (approve, dismiss, fix)
  - Family-based grouping

---

### 3. Guardian (Diagnostic Monitor)
- **Page Name:** WPShadow Guardian
- **URL:** `/wp-admin/admin.php?page=wpshadow-guardian`
- **Access Level:** `read` (all logged-in users)
- **Purpose:** Your site's health monitoring system (like a fitness tracker for your website). Runs health checks, security scans, and performance diagnostics right on your WordPress site
- **What It Does:**
  - Real-time monitoring of site health
  - Automated diagnostic checks (all run locally)
  - Treatment recommendations
  - Activity tracking and logging
  - Integration with WPShadow Cloud for Cloud Guardian diagnostics (optional, requires account)
- **Tabs:**
  - **Dashboard** (`?tab=dashboard`) - Guardian overview and diagnostic status
  - **Diagnostics** (`?tab=diagnostics`) - View all local diagnostic results
  - **Cloud** (`?tab=cloud`) - Access Cloud Guardian diagnostics (requires WPShadow Cloud account)
  - **History** (`?tab=history`) - Past diagnostic results
  - **Settings** (`?tab=settings`) - Guardian preferences
- **Features:**
  - Local diagnostics (always free, no tokens needed)
  - Cloud Guardian diagnostics (external scanning, requires account)
  - Scan history and comparisons
  - Treatment recommendations

---

### 4. Automations (Workflow Builder)
- **Page Name:** Automations
- **URL:** `/wp-admin/admin.php?page=wpshadow-automations`
- **Access Level:** `read` (all logged-in users)
- **Purpose:** Build automatic helpers for your site (like setting your coffee maker to start every morning). Create workflows that handle routine maintenance tasks without you lifting a finger
- **Features:**
  - Drag-and-drop workflow creation
  - Trigger conditions (schedule, event-based)
  - Action library (fix issues, send emails, run diagnostics)
  - Workflow templates and recipes
  - Workflow history and logs
  - Test mode (dry run)

---

### 5. Reports (Analytics & Insights)
- **Page Name:** Reports
- **URL:** `/wp-admin/admin.php?page=wpshadow-reports`
- **Access Level:** `manage_options` (administrators only)
- **Purpose:** See how your site is doing over time (like a health report card). Track improvements, spot patterns, and share progress with your team
- **Features:**
  - Executive summary reports
  - Performance metrics over time
  - Security audit reports
  - Export reports (PDF, CSV)
  - Scheduled report delivery
  - Visual comparison reports
  - Site DNA analysis

---

### 6. Settings
- **Page Name:** Settings
- **URL:** `/wp-admin/admin.php?page=wpshadow-settings`
- **Access Level:** `manage_options` (administrators only)
- **Purpose:** Adjust how WPShadow works for your needs (like customizing your phone's settings)

#### Settings Tabs:

##### 6.1 General Settings
- **URL:** `/wp-admin/admin.php?page=wpshadow-settings&tab=general`
- **Purpose:** General plugin behavior and cache settings
- **Options:**
  - Result caching (on/off, duration)
  - Visual comparison dimensions
  - Default behavior preferences

##### 6.2 Privacy Settings
- **URL:** `/wp-admin/admin.php?page=wpshadow-settings&tab=privacy`
- **Purpose:** Privacy and data collection preferences
- **Options:**
  - Telemetry opt-in/opt-out
  - Data retention policies
  - Privacy-first features
  - GDPR compliance settings

##### 6.3 Notifications Settings
- **URL:** `/wp-admin/admin.php?page=wpshadow-settings&tab=notifications`
- **Purpose:** Email notification preferences
- **Options:**
  - Enable/disable email notifications
  - Notification threshold (critical, high, medium, low)
  - Digest mode
  - Email recipients
  - Notification rules

##### 6.4 Import/Export Settings
- **URL:** `/wp-admin/admin.php?page=wpshadow-settings&tab=import-export`
- **Purpose:** Backup and restore plugin configuration
- **Features:**
  - Export settings as JSON
  - Import settings from JSON
  - Cloud sync (for registered users)
  - Automatic backup before imports
  - Settings backup history

##### 6.5 Advanced Settings
- **URL:** `/wp-admin/admin.php?page=wpshadow-settings&tab=advanced`
- **Purpose:** Power user configuration options
- **Options:**
  - Debug mode
  - Error logging
  - Performance tweaks
  - Experimental features
  - API configurations

---

### 7. Post Types (Custom Post Type Manager)
- **Page Name:** Post Types
- **URL:** `/wp-admin/admin.php?page=wpshadow-post-types`
- **Access Level:** `manage_options` (administrators only)
- **Purpose:** Manage your site's specialized content (like organizing different file types on your computer). Handle FAQs, knowledge base articles, glossary terms, and learning materials
- **Features:**
  - FAQ management
  - Knowledge Base articles
  - Glossary terms
  - Learning content
  - Course management

---

### 8. Utilities (Advanced Tools)
- **Page Name:** Utilities
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities`
- **Access Level:** `read` (all logged-in users)
- **Purpose:** Your site's toolbox (like the utility drawer in your kitchen). Handy tools for managing backups, testing speed, checking security, and more

#### Utility Tools (by Category):

##### Site Management Tools

**8.1 WPShadow Cache**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=simple-cache`
- **Purpose:** Manage plugin caching and clear cache when needed

**8.2 Vault Light (Backup)**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=vault-light`
- **Purpose:** Basic backup and restore functionality (free version)

**8.3 Site Cloner**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=site-cloner`
- **Purpose:** Clone site to staging environment

**8.4 Code Snippets**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=code-snippets`
- **Purpose:** Manage custom PHP code snippets

**8.5 Plugin Conflict Detector**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=plugin-conflict`
- **Purpose:** Identify plugin conflicts and compatibility issues

**8.6 Bulk Find & Replace**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=bulk-find-replace`
- **Purpose:** Search and replace across database

**8.7 Regenerate Thumbnails**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=regenerate-thumbnails`
- **Purpose:** Regenerate image thumbnails and sizes

**8.8 Update Safety**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=update-safety`
- **Purpose:** Safe update management with rollback capability

**8.9 Safe Mode**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=safe-mode`
- **Purpose:** Troubleshooting mode to disable features temporarily

**8.10 Timezone Alignment**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=timezone-alignment`
- **Purpose:** Detect and fix timezone mismatches

**8.11 Email Test**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=email-test`
- **Purpose:** Test WordPress email functionality

**8.12 Magic Link Support**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=magic-link-support`
- **Purpose:** Generate secure one-time login links for support

##### Cloud-Powered Tools (Require WPShadow Account)

**8.13 Uptime Monitor**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=uptime-monitor`
- **Purpose:** External monitoring pings site every 5 minutes to detect downtime

**8.14 SSL Certificate Monitor**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=ssl-monitor`
- **Purpose:** Monitor SSL certificate expiration and validity

**8.15 Domain Expiration Monitor**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=domain-monitor`
- **Purpose:** Track domain registration expiration with WHOIS monitoring

**8.16 Blacklist Monitor**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=blacklist-monitor`
- **Purpose:** Monitor if site is blacklisted by security providers

**8.17 DDoS Detection**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=ddos-detection`
- **Purpose:** Detect and alert on DDoS attack patterns

**8.18 External Malware Scanner**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=external-malware-scanner`
- **Purpose:** Independent security audit from external servers

**8.19 External Link Checker**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=external-link-checker`
- **Purpose:** Validate external links for broken URLs

**8.20 AI Content Optimizer**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=ai-content-optimizer`
- **Purpose:** AI-powered content analysis for readability, SEO, and accessibility

**8.21 AI Image Alt Text Generator**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=ai-image-alt`
- **Purpose:** Automatically generate WCAG-compliant alt text for images

**8.22 AI Spam Detection**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=ai-spam-detection`
- **Purpose:** Advanced ML-powered spam and malicious comment detection

**8.23 AI Translation**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=ai-translation`
- **Purpose:** Auto-translate content to multiple languages

**8.24 AI Writing Assistant**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=ai-writing-assistant`
- **Purpose:** AI-powered writing suggestions and improvements

**8.25 AI Chatbot**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=ai-chatbot`
- **Purpose:** Customer support chatbot powered by AI

##### Analysis & Reporting Tools

**8.26 Quick Scan**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=quick-scan`
- **Purpose:** Fast diagnostic scan of site health

**8.27 Deep Scan**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=deep-scan`
- **Purpose:** Comprehensive deep scan of all site components

**8.28 Mobile Friendliness**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=mobile-friendliness`
- **Purpose:** Analyze mobile responsiveness and usability

**8.29 Accessibility Audit**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=a11y-audit`
- **Purpose:** WCAG compliance check and accessibility testing

**8.30 Broken Links Checker**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=broken-links`
- **Purpose:** Find and report broken internal links

**8.31 Visual Comparisons**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=visual-comparisons`
- **Purpose:** Visual regression testing with before/after screenshots

**8.32 Customization Audit**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=customization-audit`
- **Purpose:** Audit theme and plugin customizations

**8.33 Global Performance**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=global-performance`
- **Purpose:** Test site speed from multiple global locations

**8.34 Asset Impact**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=asset-impact`
- **Purpose:** Analyze CSS/JS impact on page load time

**8.35 404 Monitor**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=404-monitor`
- **Purpose:** Track and report 404 errors

**8.36 Keyword Tracker**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=keyword-tracker`
- **Purpose:** Monitor keyword rankings and SEO performance

##### Other Tools

**8.37 Privacy Dashboard**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=privacy-dashboard`
- **Purpose:** Privacy compliance overview and tools

**8.38 Dark Mode**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=dark-mode`
- **Purpose:** Enable/disable dark mode for admin interface

**8.39 Tips Coach**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=tips-coach`
- **Purpose:** Contextual tips and guidance system

**8.40 Activity History**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=activity-history`
- **Purpose:** Complete audit log of all WPShadow actions

**8.41 Kanban Report**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=kanban-report`
- **Purpose:** Export Kanban board status as report

**8.42 Cloud Registration**
- **URL:** `/wp-admin/admin.php?page=wpshadow-utilities&tab=cloud-registration`
- **Purpose:** Register for WPShadow cloud services

---

### 9. Academy (Learning Platform)
- **Page Name:** WPShadow Academy
- **URL:** `/wp-admin/admin.php?page=wpshadow-academy`
- **Access Level:** `manage_options` (administrators only)
- **Purpose:** Educational content and training resources
- **Features:**
  - WordPress security courses
  - Performance optimization training
  - GDPR/Privacy compliance education
  - SEO best practices
  - Plugin development tutorials
  - Accessibility training
  - Video tutorials
  - KB article library
  - Learning paths
  - Course progress tracking

---

### 10. Achievements (Gamification)
- **Page Name:** Achievements
- **URL:** `/wp-admin/admin.php?page=wpshadow-achievements`
- **Access Level:** `read` (all logged-in users)
- **Purpose:** View earned achievements and progress
- **Features:**
  - Badge collection display
  - Achievement milestones
  - Progress tracking
  - Unlockable rewards
  - Leaderboard access
  - Point system

#### 10.1 Leaderboard
- **URL:** `/wp-admin/admin.php?page=wpshadow-leaderboard`
- **Purpose:** Compare progress with other users/sites

#### 10.2 Rewards
- **URL:** `/wp-admin/admin.php?page=wpshadow-rewards`
- **Purpose:** View and redeem earned rewards

---

### 11. Help & Documentation
- **Page Name:** Help
- **URL:** `/wp-admin/admin.php?page=wpshadow-help`
- **Access Level:** `read` (all logged-in users)
- **Purpose:** Access help documentation and support resources
- **Features:**
  - Getting started guide
  - FAQ section
  - Troubleshooting guides
  - KB article search
  - Video tutorials
  - Emergency support form
  - Feature request submission
  - Bug report form

---

## Hidden/Special Pages

### Account Registration
- **Page Name:** WPShadow Account
- **URL:** `/wp-admin/admin.php?page=wpshadow-account`
- **Access Level:** `manage_options` (administrators only)
- **Purpose:** Register for WPShadow cloud services
- **Features:**
  - Account creation
  - Service activation
  - Token management
  - Billing information
  - Service status

### Health History
- **Page Name:** Health History
- **URL:** `/wp-admin/admin.php?page=wpshadow-health-history`
- **Access Level:** `read` (all logged-in users)
- **Purpose:** View historical health scores and trends
- **Features:**
  - Health score timeline
  - Issue resolution history
  - Performance trends
  - Comparison charts

### Vault (Pro Feature)
- **Page Name:** Vault
- **URL:** `/wp-admin/admin.php?page=wpshadow-vault`
- **Access Level:** `manage_options` (administrators only)
- **Purpose:** Advanced backup and disaster recovery (Pro addon)
- **Note:** Only available with wpshadow-pro-vault plugin

### Privacy Dashboard (Standalone)
- **Page Name:** Privacy
- **URL:** `/wp-admin/admin.php?page=wpshadow-privacy`
- **Access Level:** `read` (all logged-in users)
- **Purpose:** Privacy compliance dashboard
- **Features:**
  - GDPR compliance status
  - Privacy policy generator
  - Cookie consent management
  - Data retention controls

### Exit Followups (Administrative)
- **Page Name:** Exit Followups
- **URL:** `/wp-admin/admin.php?page=wpshadow-exit-followups`
- **Access Level:** `manage_options` (administrators only)
- **Purpose:** Follow up with users who deactivated plugin
- **Note:** Redirects to dashboard in current version

### Auto Deploy (Developer Feature)
- **Page Name:** Auto Deploy
- **URL:** `/wp-admin/admin.php?page=wpshadow-auto-deploy`
- **Access Level:** `manage_options` (administrators only)
- **Purpose:** Automated deployment configuration
- **Note:** Developer/agency feature

### Analytics Dashboard
- **Page Name:** Analytics
- **URL:** `/wp-admin/admin.php?page=wpshadow-analytics`
- **Access Level:** `manage_options` (administrators only)
- **Purpose:** CPT analytics and content performance metrics

---

## WordPress Site Health Integration

WPShadow integrates with WordPress native Site Health:

### Site Health Integration
- **Page Name:** Site Health
- **URL:** `/wp-admin/site-health.php`
- **Purpose:** Native WordPress health check with WPShadow findings
- **Features:**
  - WPShadow findings appear as Site Health tests
  - Direct links to fix issues
  - Consistent health scoring
  - Native WordPress interface

---

## Dashboard Widgets

WPShadow adds several widgets to the WordPress Dashboard (`/wp-admin/`):

### WPShadow Overview Widget
- **Purpose:** Quick site health snapshot on main dashboard
- **Features:**
  - Current health score
  - Critical issues count
  - Quick action buttons
  - Recent activity

### Health History Widget
- **Purpose:** Health trends over time
- **Features:**
  - Mini chart of health scores
  - Link to full history page

### Impact Widget
- **Purpose:** Show value delivered by WPShadow
- **Features:**
  - Time saved
  - Issues resolved
  - Performance improvements

### Vault Badge (if Pro installed)
- **Purpose:** Backup status indicator
- **Features:**
  - Last backup timestamp
  - Quick backup button
  - Storage usage

### Token Balance Widget (WPShadow Cloud)
- **Purpose:** Show WPShadow Cloud service token balance for Cloud Guardian diagnostics
- **Features:**
  - Current token count
  - Token usage history
  - Purchase/refill link
- **Note:** Local Guardian diagnostics are always free and don't use tokens

---

## User Flows & Common Paths

### New User Onboarding
1. `/wp-admin/admin.php?page=wpshadow` (Dashboard with setup wizard)
2. First activation welcome screen
3. Quick scan prompts
4. Guided tour of features

### Fixing Site Issues
1. Dashboard → View findings
2. `/wp-admin/admin.php?page=wpshadow-findings` (Kanban board)
3. Click issue → Review details
4. Apply treatment or dismiss
5. Return to dashboard

### Running Guardian Diagnostics

**Local Guardian (Always Free):**
1. Dashboard → Run diagnostics button
2. `/wp-admin/admin.php?page=wpshadow-guardian&tab=diagnostics`
3. All diagnostics run on your server
4. Results appear immediately in findings page

**Cloud Guardian (Requires WPShadow Cloud Account):**
1. Dashboard → Cloud scan button
2. `/wp-admin/admin.php?page=wpshadow-guardian&tab=cloud`
3. Requires tokens or free tier quota (100/month free)
4. External diagnostics that can't run locally
5. Results appear in findings page and sync to cloud

### Creating Workflow
1. `/wp-admin/admin.php?page=wpshadow-automations`
2. Click "New Workflow"
3. Visual workflow builder
4. Configure triggers and actions
5. Test workflow (dry run)
6. Activate workflow

### Exporting Settings
1. `/wp-admin/admin.php?page=wpshadow-settings&tab=import-export`
2. Click "Export Settings"
3. Download JSON file
4. (Optional) Sync to cloud if registered

---

## Access Level Summary

| Capability | Page Access |
|------------|-------------|
| `read` | Dashboard, Findings, Guardian, Automations, Utilities, Achievements, Help, Health History |
| `manage_options` | Reports, Settings (all tabs), Post Types, Academy, Account, Vault, Privacy Dashboard, Exit Followups, Auto Deploy, Analytics |
| Public (unauthenticated) | None - all pages require WordPress login |

---

## URL Patterns

### Base URLs
- **Main plugin pages:** `/wp-admin/admin.php?page=wpshadow-{page}`
- **Settings tabs:** `/wp-admin/admin.php?page=wpshadow-settings&tab={tab}`
- **Utility tools:** `/wp-admin/admin.php?page=wpshadow-utilities&tab={tool}`
- **Guardian tabs:** `/wp-admin/admin.php?page=wpshadow-guardian&tab={tab}`

### Legacy Redirects (Handled Automatically)
- `wpshadow-guardian-reports` → `wpshadow-reports`
- `wpshadow-guardian-notifications` → `wpshadow-settings&tab=notifications`
- `wpshadow-scan-settings` → `wpshadow-settings&tab=scan-settings`
- `wpshadow-tools` → `wpshadow-utilities`
- `wpshadow-exit-followups` → `wpshadow` (dashboard)

---

## Page Count Summary

| Category | Count |
|----------|-------|
| Main Menu Pages | 11 |
| Settings Tabs | 5 |
| Utility Tools | 42+ |
| Special/Hidden Pages | 6 |
| Dashboard Widgets | 5 |
| **Total Customer-Facing Pages** | **69+** |

---

## Key Takeaways

1. **Primary Navigation:** 11 main menu items under "WPShadow" menu
2. **Settings:** 5 distinct settings tabs (General, Privacy, Notifications, Import/Export, Advanced)
3. **Utilities:** 42+ tools organized by category (site management, cloud tools, analysis)
4. **Access Control:** Two-tier access (`read` for viewers, `manage_options` for admins)
5. **Integration:** Native WordPress Site Health integration
6. **Cloud Services:** Many features require WPShadow account (free tier available)
7. **Gamification:** Achievement system with leaderboard and rewards
8. **Learning:** Built-in academy with courses and documentation

---

## Notes for Developers

- All page callbacks use function names like `wpshadow_render_{page_name}`
- Settings page tabs use class-based rendering: `{Class_Name}::render()`
- Utility tools load from `/includes/ui/tools/{tool-name}.php`
- Menu registration centralized in `class-menu-manager.php`
- Legacy URL redirects handled in `Menu_Manager::handle_legacy_redirects()`
- All pages require user authentication (no public pages)
- Nonce verification required for all form submissions
- AJAX endpoints prefixed with `wpshadow_` action

---

**Document Maintained By:** WPShadow Development Team  
**Last Updated:** February 4, 2026  
**Next Review:** May 4, 2026
