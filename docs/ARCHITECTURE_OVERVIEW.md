# WPShadow Asset Consolidation - Architecture Overview

## Asset Structure

```
/assets/
├── css/
│   ├── design-system.css          [EXISTING] Design system utilities
│   ├── admin-pages.css             [NEW] Common admin page styles
│   ├── reports.css                 [NEW] Report builder/renderer styles
│   ├── guardian.css                [NEW] Guardian dashboard styles
│   └── [other existing CSS files]
│
└── js/
    ├── admin-pages.js              [NEW] Common admin functionality
    ├── reports.js                  [NEW] Report builder/renderer JS
    ├── guardian.js                 [NEW] Guardian dashboard JS
    └── [other existing JS files]
```

## Asset Dependency Graph

```
┌─────────────────────────────────────────────────────┐
│  jQuery (External WordPress Dependency)              │
└──────────────────┬──────────────────────────────────┘
                   │
     ┌─────────────┴─────────────┐
     │                           │
     ▼                           ▼
┌──────────────────┐   ┌──────────────────────┐
│ design-system.css│   │ admin-pages.css/js   │
│  (Variables)     │   │  (Common utilities)  │
└────────┬─────────┘   └──────────┬───────────┘
         │                        │
    ┌────┴───────────────────────┴────┐
    │                                 │
    ▼                                 ▼
┌──────────────────┐          ┌──────────────────┐
│  reports.css/js  │          │  guardian.css/js │
│  (Report pages)  │          │ (Guardian page)  │
└──────────────────┘          └──────────────────┘
```

## Page Hook Integration

```
┌─────────────────────────────────────────────────────────┐
│         admin_enqueue_scripts Hook (All Admin)          │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  WPShadow Pages (Filtered by hook name)                 │
│  ├─ ANY wpshadow hook                                   │
│  │  ├─ wpshadow-admin-pages.css                         │
│  │  └─ wpshadow-admin-pages.js                          │
│  │                                                       │
│  ├─ wpshadow-reports* hooks                             │
│  │  ├─ wpshadow-reports.css (depends: admin-pages)      │
│  │  └─ wpshadow-reports.js (depends: admin-pages)       │
│  │                                                       │
│  └─ wpshadow-guardian hooks                             │
│     ├─ wpshadow-guardian.css (depends: admin-pages)     │
│     └─ wpshadow-guardian.js (depends: admin-pages)      │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

## Module Architecture

### Level 0: Design System (Utilities)
```
design-system.css
├── CSS Variables (--wps-primary, --wps-space-4, etc.)
├── Grid utilities (auto-fit, gap-*)
├── Spacing utilities (mt, mb, gap-*)
├── Font utilities (font-size, font-weight)
├── Color utilities (text color, background)
└── Responsive design utilities
```

### Level 1: Common Admin (Foundation)
```
admin-pages.js + admin-pages.css
├── WPShadowAdmin Module
│   ├── Modal management
│   ├── Form handling
│   ├── AJAX operations
│   ├── Notifications
│   └── Utilities
├── Common Styles
│   ├── Page containers
│   ├── Cards and sections
│   ├── Forms
│   ├── Modals
│   └── Status badges
└── Localization
    ├── AJAX endpoint
    ├── Security nonces
    └── i18n strings
```

### Level 2: Feature-Specific Modules
```
Reports Module:
├── reports.js
│   ├── WPShadowReportBuilder
│   │   ├── Preset dates
│   │   ├── Date validation
│   │   ├── Form submission
│   │   └── Report generation
│   └── WPShadowReportDisplay
│       ├── Export (PDF, CSV)
│       ├── Email sending
│       ├── Print functionality
│       └── Social sharing
├── reports.css
│   ├── Report builder styling
│   ├── Report output styling
│   ├── Form styling
│   └── Table styling

Guardian Module:
├── guardian.js
│   ├── WPShadowGuardian
│   ├── Toggle switches
│   ├── Scan controls
│   ├── Issue actions
│   ├── Progress monitoring
│   └── Auto-refresh
├── guardian.css
│   ├── Dashboard styling
│   ├── Status indicators
│   ├── Issue cards
│   └── Toggle switches
```

## Data Flow Example: Modal Operation

```
User clicks button
        │
        ▼
HTML: <button data-modal-trigger="email-modal">
        │
        ▼
admin-pages.js: initModals() [runs on doc ready]
        │
        ▼
jQuery click handler detects data-modal-trigger
        │
        ▼
WPShadowAdmin.openModal('email-modal') is called
        │
        ▼
admin-pages.css: .wps-modal.active { display: flex; }
        │
        ▼
Modal becomes visible on screen
```

## Data Flow Example: Report Generation

```
User fills form and clicks Generate
        │
        ▼
HTML: <form class="wps-report-builder">
        │
        ▼
reports.js: initFormSubmission() [runs on doc ready]
        │
        ▼
jQuery submit handler intercepts form
        │
        ▼
WPShadowReportBuilder.submitReportForm()
        │
        ├─ Validates form (validateForm)
        │
        ├─ Shows loading state (WPShadowAdmin.showSpinner)
        │
        └─ AJAX POST to admin-ajax.php
            ├─ action: 'wps_generate_report'
            ├─ nonce: wpshadowReportBuilder.nonce
            ├─ form data: type, date range, filters
            └─ Response: { success, html, message }
        │
        ▼
AJAX Success
        │
        ├─ Display report HTML
        │
        ├─ Update styled with reports.css classes
        │
        └─ Show success notice (WPShadowAdmin.showNotice)
```

## Data Flow Example: Guardian Scan

```
User clicks "Run Scan" button
        │
        ▼
HTML: <button data-scan-action="run">
        │
        ▼
guardian.js: initScanControls() [runs on doc ready]
        │
        ▼
jQuery click handler detected
        │
        ▼
WPShadowGuardian.handleScanAction('run')
        │
        ├─ AJAX POST: wps_guardian_scan_run
        │   └─ Response: { success, message }
        │
        ├─ If success, call monitorScanProgress()
        │
        └─ Start polling every 1 second:
            ├─ AJAX POST: wps_guardian_scan_progress
            │
            ├─ Get { progress: 45, status: "Checking..." }
            │
            ├─ Update progress bar (.wps-scan-progress-fill)
            │   CSS: width: 45% [animated]
            │
            └─ When progress >= 100:
                └─ Reload page after 2 seconds
```

## File Interaction Matrix

```
                │ admin │ reports │ guardian │ design-system │
────────────────┼───────┼─────────┼──────────┼────────────────┤
admin-pages.css │   X   │    ✓    │    ✓     │       ✓        │
admin-pages.js  │   X   │    ✓    │    ✓     │       -        │
reports.css     │   ✓   │    X    │    -     │       ✓        │
reports.js      │   ✓   │    X    │    -     │       -        │
guardian.css    │   ✓   │    -    │    X     │       ✓        │
guardian.js     │   ✓   │    -    │    X     │       -        │

X = Primary asset for module
✓ = Dependency/Used by module
- = Not used
```

## CSS Specificity Hierarchy

```
Level 1 (Lowest Specificity - Most Reusable)
├── Design System Utilities
│   ├── .mt-4, .mb-6, .gap-3
│   ├── .text-lg, .font-bold
│   ├── .bg-primary, .text-gray-600
│   └── CSS Variables: --wps-space-4, --wps-primary

Level 2 (Medium Specificity)
├── Component Classes
│   ├── .wps-page-container
│   ├── .wps-admin-card-container
│   ├── .wps-form-group-label
│   └── .wps-status-badge

Level 3 (High Specificity - Most Specific)
├── State & Modifier Classes
│   ├── .wps-modal.active
│   ├── .wps-preset-btn.selected
│   ├── .wps-report-card.error
│   ├── .wps-guardian-issue-card.critical
│   └── .wps-toggle-slider:checked

Level 4 (Highest Specificity)
├── Pseudo-element/State Selectors
│   ├── .wps-btn:hover { background-color: ... }
│   ├── .wps-report-table tbody tr:nth-child(even)
│   └── input[type="checkbox"] { ... }
```

## Module Lifecycle

```
┌──────────────────────────────────────────────────────┐
│ WordPress Admin Page Load (on wpshadow* hook)       │
└──────────────┬───────────────────────────────────────┘
               │
               ▼
┌──────────────────────────────────────────────────────┐
│ 1. Enqueue Phase (admin_enqueue_scripts)            │
│    ├─ wp_enqueue_style('wpshadow-admin-pages')     │
│    ├─ wp_enqueue_script('wpshadow-admin-pages')    │
│    ├─ wp_localize_script() → wpshadowAdmin object  │
│    └─ [Conditionally enqueue reports/guardian]    │
└──────────────┬───────────────────────────────────────┘
               │
               ▼
┌──────────────────────────────────────────────────────┐
│ 2. Browser Downloads & Parses                       │
│    ├─ CSS loaded and applied                        │
│    ├─ JavaScript parsed (not executed yet)          │
│    └─ wpshadowAdmin object created from localize   │
└──────────────┬───────────────────────────────────────┘
               │
               ▼
┌──────────────────────────────────────────────────────┐
│ 3. DOM Ready (jQuery $(document).ready())           │
│    ├─ WPShadowAdmin.init() runs                     │
│    │  ├─ initModals() - Setup modal handlers       │
│    │  ├─ initFormHandlers() - Setup form AJAX      │
│    │  ├─ initAjaxHandlers() - Setup [data-action]  │
│    │  ├─ initToggles() - Setup toggle handlers     │
│    │  └─ Exported: window.WPShadowAdmin            │
│    │                                               │
│    ├─ [Module-specific init if present]            │
│    │  ├─ WPShadowReportBuilder.init()             │
│    │  └─ WPShadowGuardian.init()                  │
│    │                                               │
│    └─ page.js (if any) can now call WPShadowAdmin  │
└──────────────┬───────────────────────────────────────┘
               │
               ▼
┌──────────────────────────────────────────────────────┐
│ 4. User Interaction Phase                           │
│    ├─ All event handlers ready                      │
│    ├─ AJAX endpoints accessible                     │
│    ├─ Modals functional                             │
│    └─ Forms responsive                              │
└──────────────────────────────────────────────────────┘
```

## CSS Loading Strategy

```
First Paint:
1. Design system CSS loaded (variables, base styles)
2. admin-pages.css loaded (layout, containers)
3. Page renders with basic styling

Interactive:
4. JavaScript loads and parses
5. jQuery ready event triggers
6. Module-specific CSS (reports.css, guardian.css) applied
7. Event handlers attached

User Interaction:
8. User clicks button/form
9. Styles transition smoothly (hover, active states)
10. JavaScript responds to user input
11. AJAX requests fire
12. New content styled with appropriate classes
```

## Security Model

```
┌────────────────────────────────────────┐
│ WordPress Admin (Authenticated User)   │
└────────────────┬───────────────────────┘
                 │
                 ▼
        ┌────────────────────┐
        │ Asset Enqueue      │
        │ (Backend)          │
        └────────┬───────────┘
                 │
    ┌────────────┼────────────┐
    │            │            │
    ▼            ▼            ▼
┌─────────┐ ┌──────────┐ ┌────────┐
│CSS Files│ │JS Module │ │Nonce   │
│(safe)   │ │Objects   │ │(token) │
└─────────┘ └──────────┘ └───┬────┘
                              │
                ┌─────────────┴──────┐
                │                    │
                ▼                    ▼
        ┌──────────────────────────────────────┐
        │ Client-Side AJAX Request             │
        ├──────────────────────────────────────┤
        │ POST /wp-admin/admin-ajax.php        │
        │ ├─ action: 'wps_generate_report'   │
        │ ├─ nonce: 'a1b2c3d4...'            │
        │ └─ data: {...}                      │
        └──────────┬───────────────────────────┘
                   │
                   ▼
        ┌──────────────────────────────────────┐
        │ WordPress Nonce Verification         │
        ├──────────────────────────────────────┤
        │ ✓ Nonce matches server token        │
        │ ✓ Action string matches             │
        │ ✓ User is authenticated             │
        │ ✓ User has capabilities             │
        └──────────┬───────────────────────────┘
                   │
         ┌─────────┴──────────┐
         │                    │
    ✓ Valid          ✗ Invalid
         │                    │
         ▼                    ▼
    ┌─────────────┐    ┌──────────────┐
    │ Process     │    │ Return Error │
    │ Request     │    │ 403 Forbidden│
    └─────────────┘    └──────────────┘
```

This architecture ensures:
- Modular, maintainable code
- Reusable components across pages
- Clean separation of concerns
- Secure AJAX operations
- Progressive enhancement
- Responsive design
- Content Security Policy compliance
