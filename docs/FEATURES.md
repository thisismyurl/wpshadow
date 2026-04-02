# WPShadow Feature & Component Inventory

**Last Updated:** Current
**Plugin Scope:** Dashboard, Diagnostics, Treatments, Core Infrastructure

---

## 📊 Executive Summary

- **388 Diagnostics** across 10 categories
- **Treatment Framework** (registry, base class, interface — extensible)
- **25 AJAX Handlers** (diagnostic, treatment, dashboard, settings)
- **10 Diagnostic Categories**
- **1 Dashboard Interface** (real-time, extensible)
- **100% Free**

---

## 🏗️ Core System Architecture

All core classes are located in `includes/systems/core/`.

### Registry & Bootstrap
- **Abstract_Registry** — Registry pattern base for diagnostics and treatments
- **Bootstrap_Autoloader** — PSR-4 class autoloading
- **Hooks_Initializer** — Auto-discovers and registers hook subscribers
- **Plugin_Bootstrap** — Plugin entry point and initialization

### AJAX Infrastructure
- **AJAX_Handler_Base** — Secure AJAX endpoint foundation (nonce, capabilities, sanitization)
- **AJAX_Router** — Request routing for all AJAX handlers

### Caching & Performance
- **Cache_Manager** — Multi-layer caching system
- **Dashboard_Cache** — Dashboard-specific caching layer
- **Query_Batch_Optimizer** — Batches database queries to reduce load

### Database
- **Database_Migrator** — Schema migrations and upgrades
- **Database_Indexes** — Index management for performance

### Diagnostics Infrastructure
- **Diagnostic_Base** — Abstract base class all diagnostics extend
- **Category_Metadata** — Category labels, descriptions, and icons
- **functions-category-metadata.php** — Category helper functions

### Treatments Infrastructure
- **Treatment_Base** — Abstract base class all treatments extend
- **Treatment_Interface** — Contract all treatments implement
- **functions-treatment.php** — Treatment helper functions

### Findings & Status
- **Finding_Status_Manager** — Manages finding state (open, dismissed, resolved)
- **KPI_Tracker** — Tracks and records impact metrics

### Settings & Options
- **Settings_Registry** — Centralized settings registration
- **Options_Manager** — WordPress options wrapper with caching

### Security & Validation
- **Security_Validator** — Input sanitization and capability checks
- **Rate_Limiter** — Rate limiting for AJAX endpoints
- **External_Request_Guard** — Guards against unexpected external requests
- **Form_Param_Helper** — Form input helpers

### Admin UI Infrastructure
- **Menu_Manager** — Registers admin menu items
- **Admin_Asset_Registry** — Enqueues admin CSS and JS
- **Hook_Subscriber_Base** — Base for auto-discovered hook subscribers
- **Error_Handler** — Centralized error handling and logging

### Activity & Analytics
- **Activity_Logger** — System-wide activity event logging
- **Trend_Chart** — Generates trend chart data for the dashboard

### Utilities
- **UTM_Link_Manager** — Adds UTM parameters to outbound links

---

## 🔍 Diagnostics (211 Total)

Located in `includes/diagnostics/tests/`.

### Categories

| Category | Count | Description |
|----------|-------|-------------|
| **Accessibility** | 12 | WCAG compliance, screen readers, keyboard navigation |
| **Code Quality** | 7 | Code standards and best practices |
| **Database** | 10 | Database health, indexes, and optimization |
| **Design** | 16 | UI/UX and visual quality checks |
| **Monitoring** | 8 | System monitoring and availability |
| **Performance** | 51 | Speed, caching, Core Web Vitals, query optimization |
| **Security** | 44 | WordPress hardening, permissions, user roles |
| **SEO** | 28 | Meta tags, schema, sitemaps, content optimization |
| **Settings** | 28 | Configuration and WordPress settings checks |
| **WordPress Health** | 2 | WordPress core health integration |
| **Workflows** | 5 | WordPress workflow and process checks |
| **Total** | **211** | |

### New Settings Diagnostics (WordPress Settings Optimisation)

The following 10 diagnostics were added to the **Settings** family. Each is verifiable via `get_option()`, PHP constants, or the WP term API — no external HTTP requests required.

| Slug | Class | What It Checks |
|------|-------|----------------|
| `site-language-intentional` | `Diagnostic_Site_Language_Intentional` | `WPLANG` / `get_locale()` — whether the site language was explicitly set to match the business audience. |
| `posts-per-page-optimized` | `Diagnostic_Posts_Per_Page_Optimized` | `posts_per_page` — flags values outside the 3–20 range that hurt performance or SEO crawlability. |
| `date-time-format-intentional` | `Diagnostic_Date_Time_Format_Intentional` | `date_format` / `time_format` cross-referenced against the site locale — catches US date format on non-US sites. |
| `update-services-intentional` | `Diagnostic_Update_Services_Intentional` | `ping_sites` — flags non-empty ping list on sites that publish few or no blog posts. |
| `comments-auto-close-old-posts` | `Diagnostic_Comments_Auto_Close_Old_Posts` | `close_comments_for_old_posts` / `close_comments_days_old` — ensures old posts don't remain a permanent spam surface. |
| `default-category-renamed` | `Diagnostic_Default_Category_Renamed` | Default category term name via `get_term(get_option('default_category'))` — flags the literal "Uncategorized" name. |
| `trash-auto-empty-configured` | `Diagnostic_Trash_Auto_Empty_Configured` | `EMPTY_TRASH_DAYS` constant — flags 0 (never auto-empty) or values > 90 days. |
| `admin-email-domain-match` | `Diagnostic_Admin_Email_Domain_Match` | `admin_email` domain — flags free consumer providers (gmail, hotmail, yahoo, etc.). |
| `site-charset-utf8` | `Diagnostic_Site_Charset_Utf8` | `blog_charset` — flags any value that is not UTF-8. |
| `media-year-month-folders-enabled` | `Diagnostic_Media_Year_Month_Folders_Enabled` | `uploads_use_yearmonth_folders` — flags when disabled, leaving all uploads in a flat directory. |

### Diagnostic Infrastructure

- `includes/systems/diagnostics/class-diagnostic-registry.php` — Discovers and registers all diagnostic tests
- `includes/diagnostics/helpers/class-diagnostic-request-helper.php` — HTTP request helpers for diagnostics
- `Diagnostic_Base` (in core) — All tests extend this base class

### How Diagnostics Work

Each diagnostic:
1. Extends `Diagnostic_Base`
2. Implements a `run()` method that returns pass/fail/warning
3. Is discovered and registered via `Diagnostic_Registry`
4. Produces findings stored and displayed in the dashboard
5. Optionally links to a knowledge base article for education

---

## 💊 Treatments Framework

Located in `includes/systems/treatments/` and `includes/systems/core/`.

The treatment system provides extensible infrastructure for auto-fix capabilities.
The framework is fully operational; specific treatment implementations can be added as modules.

### Treatment Infrastructure
- **Treatment_Registry** — Discovers and registers all treatment classes
- **Treatment_Base** (core) — Abstract base all treatments extend
- **Treatment_Interface** (core + treatments) — Contract defining `run()`, `can_run()`, `undo()`
- **functions-treatment.php** (core) — Helper functions for treatment execution

### AJAX Integration
- **class-ajax-toggle-treatment.php** — Enable/disable individual treatments
- **class-ajax-treatments-list.php** — List available treatments
- **dry-run-treatment-handler.php** — Preview treatment effect before applying
- **post-scan-treatments-handler.php** — Queue recommended treatments after a scan

---

## 🎛️ Dashboard

### Asset Management
- `includes/systems/dashboard/class-asset-manager.php` — Loads dashboard CSS and JS conditionally

### Page Views (`includes/ui/views/`)
- **dashboard-page.php** — Main dashboard page template
- **menu-stubs.php** — Admin menu stubs
- **functions-page-layout.php** — Page structure helpers
- **functions-card.php** — Card component helpers

### Components (`includes/ui/components/`)
- **page-activities.php** — Activity feed component

### Admin Classes (`includes/admin/`)
- **class-ajax-dashboard-cache.php** — Dashboard cache invalidation
- **class-dashboard-glance-problems.php** — WordPress "At a Glance" integration
- **class-dashboard-integrations.php** — WordPress dashboard widget integration
- **class-site-health-bridge.php** — WordPress Site Health API integration

### Admin Pages (`includes/admin/pages/`)
- **class-scan-frequency-manager.php** — Configure how often scans run

---

## 🔌 AJAX Handlers (25)

Located in `includes/admin/ajax/`.

### Diagnostic Handlers
- **class-ajax-diagnostics-list.php** — List all available diagnostics
- **class-ajax-diagnostics-status.php** — Get current diagnostic run status
- **class-ajax-run-family-diagnostics.php** — Run all diagnostics in a category
- **class-ajax-run-single-diagnostic.php** — Run one diagnostic
- **class-ajax-set-diagnostic-frequency.php** — Set how often a diagnostic runs
- **class-ajax-toggle-diagnostic.php** — Enable or disable a diagnostic
- **class-ajax-last-family-results.php** — Get last results for a category

### Scan Handlers
- **first-scan-handler.php** — Trigger the initial site scan
- **deep-scan-handler.php** — Trigger a comprehensive scan
- **heartbeat-diagnostics-handler.php** — Keep scan status alive via WP Heartbeat

### Finding Handlers
- **autofix-finding-handler.php** — Apply an auto-fix to a finding
- **apply-family-fix-handler.php** — Apply fixes to all findings in a category
- **change-finding-status-handler.php** — Change finding status (open/dismissed/resolved)
- **dismiss-finding-handler.php** — Dismiss a finding
- **get-finding-family-handler.php** — Get findings grouped by family/category
- **dismiss-scan-notice-handler.php** — Dismiss a scan result notice

### Treatment Handlers
- **class-ajax-toggle-treatment.php** — Enable or disable a treatment
- **class-ajax-treatments-list.php** — List available treatments
- **dry-run-treatment-handler.php** — Preview a treatment before applying
- **post-scan-treatments-handler.php** — Suggest treatments after scan

### Dashboard Handlers
- **get-dashboard-data-handler.php** — Fetch data for the dashboard
- **save-dashboard-prefs-handler.php** — Save user dashboard preferences

### Settings & Activity Handlers
- **class-save-setting-handler.php** — Save a plugin setting
- **class-get-activities-handler.php** — Fetch activity log entries

### Loader
- **ajax-handlers-loader.php** — Registers all AJAX handlers

---

## 🎨 Frontend Assets

### CSS (`assets/css/`)
- **gauges.css** — Gauge/meter UI components
- **utilities-consolidated.css** — Shared utility classes
- **wpshadow-dashboard-fullscreen.css** — Fullscreen dashboard layout
- **wpshadow-modal.css** — Modal dialog styles

### JavaScript (`assets/js/`)
- **admin-pages.js** — General admin page behaviors
- **data-retention-manager.js** — Data retention settings UI
- **design-system.js** — Design system initialization
- **form-controls.js** — Form control behaviors
- **page-activities.js** — Activity feed UI
- **wpshadow-ajax-helper.js** — Shared AJAX utilities
- **wpshadow-dashboard-realtime.js** — Real-time dashboard updates
- **wpshadow-modal.js** — Modal dialog controller

---

## 🔐 Security

All AJAX endpoints enforce:
- WordPress nonce verification
- Capability checks (`manage_options` or specific caps)
- Rate limiting via `Rate_Limiter`
- Input sanitization via `Security_Validator`
- SQL prepared statements throughout

---

## 🚀 Architecture Patterns

- **PSR-4 autoloading** via `Bootstrap_Autoloader`
- **Hook subscriber auto-discovery** via `Hook_Subscriber_Base` + `Hooks_Initializer`
- **Registry pattern** for diagnostics and treatments (extensible by third parties)
- **Multi-layer caching** (transients + object cache + dashboard cache)
- **Query batching** to minimize database load
- **Abstract base classes** for diagnostics and treatments (consistent API)

---

*This document reflects the current state of the plugin codebase. All listed files and classes actively exist.*
