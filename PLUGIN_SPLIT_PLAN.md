# Plugin Split Classification

## Feature Breakdown: 56 Total Features (29 Free + 27 Paid)

### FREE FEATURES (License Level 1-2) - 29 Features

#### Level 1 (Core Free) - 28 features
1. block-css-cleanup
2. broken-link-checker
3. color-contrast-checker
4. core-diagnostics
5. core-integrity
6. cron-test
7. css-class-cleanup
8. email-test
9. embed-disable
10. favicon-checker
11. google-fonts-disabler
12. head-cleanup
13. http-ssl-audit
14. iframe-busting
15. image-lazy-loading
16. interactivity-cleanup
17. jquery-cleanup
18. loopback-test
19. maintenance-cleanup
20. mobile-friendliness
21. mysql-diagnostics
22. open-graph-previewer
23. php-info
24. plugin-cleanup
25. resource-hints
26. seo-validator
27. skiplinks
28. tips-coach

#### Level 2 (Extended Free) - 1 feature
1. a11y-audit

### PAID FEATURES (License Level 3+) - 27 Features

#### Level 3 (Business) - 11 features
1. asset-minification
2. brute-force-protection
3. cdn-integration
4. conditional-loading
5. critical-css
6. database-cleanup
7. hardening
8. image-optimizer
9. page-cache
10. script-deferral
11. script-optimizer

#### Level 4 (Professional) - 10 features
1. conflict-sandbox
2. firewall
3. malware-scanner
4. performance-alerts
5. troubleshooting-mode
6. uptime-monitor
7. visual-regression
8. vulnerability-watch
9. weekly-performance-report
10. (1 more - check L4 count)

#### Level 5 (Enterprise) - 6 features
1. auto-rollback
2. customization-audit
3. image-smart-focus
4. smart-recommendations
5. traffic-monitor
6. two-factor-auth
7. vault-audit

---

## Plugin Structure Plan

### wpshadow.php (Core/Free)
- All 29 free features
- All core classes and helpers
- Dashboard system
- Module infrastructure (no module code)
- No license requirements
- Can run standalone

### wpshadow-pro.php (Pro Extension)
- All 27 paid features
- License verification
- Requires wpshadow.php to be active
- Extends dashboard/admin UI
- Pro modules support
- Hooks into core for feature registration

---

## Core Components (Both Plugins)

### Shared Classes (in wpshadow.php, used by both)
- `includes/abstracts/class-wps-feature-abstract.php`
- `includes/abstracts/class-wps-feature-validator.php`
- `includes/abstracts/interface-wps-feature.php`
- `includes/class-wps-feature-registry.php`
- `includes/class-wps-dashboard-widgets.php`
- `includes/class-wps-dashboard-layout.php`
- Dashboard-related admin classes
- Helper functions in `includes/`

### Free-Only Classes (in wpshadow.php)
- All 29 free feature files
- Core functionality classes

### Pro-Only Classes (in wpshadow-pro.php)
- All 27 paid feature files
- License management class
- Pro admin enhancements
- Pro module loaders

---

## Hook Strategy for Pro Extension

### Action Hook: `wpshadow_pro_loaded`
- Fired after wpshadow-pro initializes
- Used to register Pro features

### Filter Hook: `wpshadow_dashboard_sections`
- Allows Pro to add its own dashboard sections

### Action Hook: `wpshadow_register_pro_features`
- Used to register paid features from Pro plugin

### Filter Hook: `wpshadow_rest_api_controllers`
- Allows Pro to register Pro-specific REST API endpoints
