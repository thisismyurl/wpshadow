# Priority-1 Tests Ready for Implementation
**Phase 4 - Strategic Diagnostics Phase**

## Summary
- **Total Priority-1 Stubs Created:** 165 tests
- **Categories:** 5 core business-critical areas
- **Philosophy Focus:** Commandments #1, #5, #7, #8, #9, #10, #11
- **Implementation Timeline:** 2-3 weeks for full rollout

---

## 1. AUDIT & ACTIVITY TRAIL (20 tests)
**Purpose:** Complete visibility into "who changed what when and why"  
**Philosophy:** #1 (Helpful neighbor - catch issues), #5 (Drive to KB), #10 (Privacy-first)  
**User Value:** "I know exactly who made that mistake"

### Stubs Created ✅
```
✅ audit-logging-enabled               - Is activity logging system enabled?
✅ audit-log-retention                 - How long are logs retained?
✅ audit-log-storage-location          - Are logs stored securely?
✅ audit-user-changes                  - Track user permission changes
✅ audit-settings-changes              - Track option/settings modifications
✅ audit-post-changes                  - Track post/page edits and deletions
✅ audit-image-uploads                 - Track image uploads with metadata
✅ audit-plugin-changes                - Track plugin install/activate/deactivate
✅ audit-theme-changes                 - Track theme changes
✅ audit-permission-changes            - Track capability modifications
✅ audit-export-tracked                - Are exports logged?
✅ audit-deletion-tracked              - Are deletions logged with recovery?
✅ audit-bulk-operations               - Are bulk actions tracked?
✅ audit-schedule-tracked              - Are scheduled events logged?
✅ audit-cron-execution                - Track cron job execution with args
✅ audit-external-api                  - Track external API calls
✅ audit-failed-login                  - Track failed login attempts
✅ audit-privilege-escalation          - Track attempted privilege escalation
✅ audit-orphaned-data                 - Find data without audit trail
✅ audit-restore-safety                - Can we restore from backups with audit?
```

**Implementation Notes:**
- Required: Activity log table in DB + audit table schema
- Consider: `wp_audit_logs` table with indexes on user_id, time, action
- Data collection: User ID, action, timestamp, before/after values
- Cleanup: Retention policy (e.g., 90 days default, user-configurable)
- Privacy: GDPR compliance - allow data export/deletion per user

---

## 2. WORDPRESS ECOSYSTEM HEALTH (40 tests)
**Purpose:** Single dashboard showing core, plugins, themes, DB health  
**Philosophy:** #7 (Ridiculously good), #8 (Inspire confidence), #9 (Show value)  
**User Value:** "Everything I need to know about my WordPress setup in one place"

### Category 2A: Core System Health (9 tests)
```
✅ core-updates-available             - Are WordPress updates available?
✅ core-auto-updates-enabled          - Is automatic updating active?
✅ core-security-patches              - Are critical security patches applied?
✅ core-permission-issues             - Are WordPress file permissions safe?
✅ core-disk-space                    - Is disk space running low?
✅ core-mysql-version                 - Is MySQL version modern?
✅ core-backups-recent                - Are backups recent (last 24h)?
✅ core-backup-tested                 - Have backups been tested?
✅ core-recovery-plan                 - Is there a documented recovery plan?
```

### Category 2B: Plugin Ecosystem (25 tests)
```
✅ plugin-count-analysis              - How many plugins installed?
✅ plugin-updates-pending             - How many need updating?
✅ plugin-security-updates            - Critical security updates available?
✅ plugin-abandoned                   - How many plugins abandoned 2+ years?
✅ plugin-conflicts-likely            - Likely conflicts based on hooks?
✅ plugin-performance-impact          - Rank plugins by slowness
✅ plugin-memory-footprint            - Memory usage by plugin
✅ plugin-database-bloat              - DB tables added per plugin
✅ plugin-beta-versions               - Running beta/development versions?
✅ plugin-activation-errors           - Are plugins failing to activate?
✅ plugin-deactivation-cleanup        - Do plugins clean up on deactivate?
✅ plugin-multisite-issues            - Network-specific conflicts?
✅ plugin-autoload-bloat              - Large autoload entries from plugins?
✅ plugin-rest-api-exposure           - Unnecessary REST API endpoints?
✅ plugin-nonce-security              - Missing nonce verification patterns?
✅ plugin-capability-checks           - Missing capability checks?
✅ plugin-sanitization-gaps           - Input not sanitized?
✅ plugin-escaping-gaps               - Output not escaped?
✅ plugin-sql-injection-risk          - Direct SQL without prepare?
✅ plugin-file-permissions            - Insecure file permissions?
✅ plugin-debug-mode-left-on          - Debug constants exposed?
✅ plugin-update-checking             - Plugin using deprecated update API?
✅ plugin-translation-outdated        - Language files missing/outdated?
✅ plugin-readme-current              - Plugin readme.txt accurate?
```

### Category 2C: Theme Analysis (10 tests)
```
✅ theme-updates-pending              - Theme needs updating?
✅ theme-child-theme-active           - Using child theme for customization?
✅ theme-direct-edits                 - Have you edited theme files?
✅ theme-deprecated-hooks             - Using deprecated theme hooks?
✅ theme-accessibility                - Basic accessibility checks passing?
✅ theme-responsiveness               - Mobile-friendly design detected?
✅ theme-javascript-conflicts         - Script conflicts detected?
✅ theme-css-conflicts                - CSS conflicts detected?
✅ theme-font-loading                 - Fonts loading efficiently?
✅ theme-unused-templates             - Unused template files taking space?
```

**Implementation Notes:**
- Dashboard: Single view showing all 40 checks with risk levels
- Performance: Cache results for 1 hour (expensive operations)
- Plugins: Use hooks to gather data from `wp-content/plugins/`
- Automation: Weekly email digest of critical issues

---

## 3. PERFORMANCE ATTRIBUTION (35 tests)
**Purpose:** Answer "Which plugin is making my site slow?"  
**Philosophy:** #7 (Ridiculously good), #9 (Show value), #11 (Talk-worthy)  
**User Value:** "I know exactly which plugin to disable to fix slowness"

### Category 3A: Per-Plugin Performance (20 tests)
```
✅ plugin-ttfb-impact                 - TTFB impact by plugin
✅ plugin-query-count                 - MySQL queries by plugin
✅ plugin-query-time                  - MySQL time by plugin
✅ plugin-memory-peak                 - Peak memory by plugin
✅ plugin-asset-weight                - CSS/JS size by plugin
✅ plugin-request-time                - Backend time by plugin
✅ plugin-autoload-size               - Autoload metadata by plugin
✅ plugin-cache-misses                - Cache hits/misses by plugin
✅ plugin-database-queries-slow       - Slow queries triggered by plugin
✅ plugin-n-plus-one                  - N+1 query patterns by plugin
✅ plugin-http-requests               - Outbound requests by plugin
✅ plugin-file-system-io              - File system calls by plugin
✅ plugin-css-specificity             - CSS specificity issues by plugin
✅ plugin-javascript-execution        - JS execution time by plugin
✅ plugin-database-writes             - Write operations by plugin
✅ plugin-transient-churn             - Transient create/delete by plugin
✅ plugin-cron-overhead               - Cron impact by plugin
✅ plugin-late-loading                - Late-loading scripts by plugin
✅ plugin-resource-headers            - Missing cache headers by plugin
✅ plugin-async-defer-missing         - Script async/defer issues by plugin
```

### Category 3B: Theme Performance (5 tests)
```
✅ theme-ttfb-impact                  - Theme TTFB contribution
✅ theme-render-blocking              - Theme blocks rendering
✅ theme-javascript-defer             - Theme script deferral status
✅ theme-font-loading-strategy        - Font loading optimization
✅ theme-critical-css                 - Critical CSS extracted?
```

### Category 3C: WordPress Core (10 tests)
```
✅ core-query-count-total             - Total queries per request
✅ core-query-time-total              - Total query time per request
✅ core-memory-used-percent           - Memory used of limit
✅ core-ttfb-baseline                 - Baseline TTFB without plugins
✅ core-response-time-total           - Total backend time
✅ core-autoload-size-total           - Total autoload data
✅ core-asset-count-total             - Total CSS/JS files
✅ core-asset-size-total              - Total CSS/JS size
✅ core-homepage-requests             - Request count on homepage
✅ core-homepage-load-time            - Load time on homepage
```

**Implementation Notes:**
- Profiling: Use XDebug profiler or custom hooks to track per-plugin
- Storage: Store baseline + per-plugin measurements in options
- Comparison: Show % impact (plugin TTFB vs. baseline TTFB)
- Audit: Optional logging of all measurements for trend analysis
- Real User Monitoring: Optional RUM data collection (with consent)

---

## 4. BUSINESS IMPACT & REVENUE (25 tests)
**Purpose:** Show business metrics that matter for decision-making  
**Philosophy:** #9 (Show value), #10 (Privacy-first with external services)  
**User Value:** "I know the $ impact of each change I make"

### Category 4A: E-Commerce (5 tests)
```
✅ ecommerce-conversion-rate          - What % of visitors buy?
✅ ecommerce-avg-order-value          - What's the average order?
✅ ecommerce-revenue-trend            - Is revenue growing?
✅ ecommerce-cart-abandonment-rate    - How many carts left?
✅ ecommerce-revenue-lost-to-abandonment - $ cost of abandonment
```

### Category 4B: Leads & Growth (3 tests)
```
✅ lead-generation-rate               - How many leads/month?
✅ lead-quality-score                 - Lead scoring (hot/warm/cold)?
✅ lead-to-customer-conversion        - What % convert to customer?
```

### Category 4C: Revenue Metrics (3 tests)
```
✅ revenue-per-visitor                - Average $ per visitor
✅ monthly-revenue-impact             - Monthly revenue trend
✅ revenue-per-traffic-source         - Which channels most profitable?
```

### Category 4D: Cost Analysis (5 tests)
```
✅ cost-per-acquisition-trend         - CAC trending up/down?
✅ marketing-spend-efficiency         - ROI on ad spend?
✅ traffic-cost-hosted                - Cost of hosting for traffic volume
✅ development-cost-justification     - Dev time ROI?
✅ maintenance-cost-vs-revenue        - Maintenance overhead % of revenue?
```

### Category 4E: Performance as $ (4 tests)
```
✅ page-speed-correlation-to-revenue  - Speed improvements = sales?
✅ uptime-correlation-to-revenue      - Downtime cost $ estimate?
✅ plugin-slowdown-cost               - $ cost of slow plugin?
✅ downtime-cost                      - $ cost of outage per hour?
```

### Category 4F: Market Position (3 tests)
```
✅ seo-traffic-value                  - $ value of organic traffic?
✅ search-visibility-trend            - Visibility growing/shrinking?
✅ qualified-traffic-percent          - % of traffic from target audience?
```

**Implementation Notes:**
- Data Sources: Google Analytics (with consent), WooCommerce, Stripe, Jetpack Stats
- Privacy: Consent banner REQUIRED before collecting any external data
- Attribution: Show how each metric connects to business goal
- Trends: 30-day, 90-day, YTD views
- Recommendations: "If you increase conversion by 1%, revenue increases by $X"

---

## 5. COMPLIANCE & LEGAL RISK (40 tests)
**Purpose:** Ensure legal compliance across all regulations  
**Philosophy:** #10 (Privacy-first), show value via risk reduction  
**User Value:** "I'm compliant and sleep well at night"

### Category 5A: GDPR (12 tests)
```
✅ gdpr-privacy-policy-exists         - Privacy policy present?
✅ gdpr-privacy-policy-current        - Policy updated < 1 year?
✅ gdpr-cookies-disclosed             - All cookies disclosed?
✅ gdpr-consent-tool-active           - Consent banner active?
✅ gdpr-consent-before-tracking       - Tracking only with consent?
✅ gdpr-data-retention-policy         - Retention policy documented?
✅ gdpr-data-deletion-capability      - Can users request deletion?
✅ gdpr-data-portability              - Can users export data?
✅ gdpr-contact-info-visible          - Privacy contact available?
✅ gdpr-third-party-vendors-disclosed - Third parties listed?
✅ gdpr-breach-notification-plan      - Breach response documented?
✅ gdpr-dpia-completed                - Data Protection Impact Assessment done?
```

### Category 5B: CCPA (8 tests)
```
✅ ccpa-privacy-policy-exists         - Privacy policy present?
✅ ccpa-opt-out-available             - "Do Not Sell" link present?
✅ ccpa-consumer-rights-disclosed     - Rights explained clearly?
✅ ccpa-data-inventory-complete       - Know what data collected?
✅ ccpa-third-party-sales-disclosed   - Disclosed data sales to 3rd party?
✅ ccpa-retention-policy-documented   - Retention schedule documented?
✅ ccpa-sale-opt-out-working         - Opt-out mechanism functioning?
✅ ccpa-vendor-contracts-signed       - Service agreements in place?
```

### Category 5C: Industry-Specific (6 tests)
```
✅ hipaa-pii-encryption               - PII encrypted if healthcare data?
✅ pci-dss-compliance                 - If processing cards, PCI compliant?
✅ coppa-compliance                   - COPPA compliance if children users?
✅ ferpa-compliance                   - FERPA compliance if education data?
✅ sox-compliance                     - SOX compliance if public company?
✅ finra-compliance                   - FINRA compliance if financial data?
```

### Category 5D: Data Security (8 tests)
```
✅ https-everywhere                   - All pages HTTPS?
✅ tls-version-modern                 - TLS 1.2+ enabled?
✅ certificate-valid                  - SSL cert currently valid?
✅ certificate-trusted                - Cert from trusted CA?
✅ sensitive-data-encrypted-rest      - PII encrypted in database?
✅ sensitive-data-encrypted-transit   - PII encrypted in transit?
✅ encryption-key-management          - Keys rotated regularly?
✅ database-password-strength         - DB password meets requirements?
```

### Category 5E: Legal Documentation (6 tests)
```
✅ terms-of-service-exists            - ToS available to users?
✅ liability-insurance-documented     - Business insured?
✅ accessible-compliance              - WCAG 2.1 AA compliance?
✅ user-consent-tracking              - All consent logged?
✅ data-retention-enforcement         - Deleted data verified gone?
✅ audit-trail-comprehensive          - Can prove compliance audit?
```

**Implementation Notes:**
- Compliance Scanner: Per-regulation checklist
- Remediation: Link to KB article for each failing check
- Documentation: Help users build compliance artifacts
- Automation: Auto-detect privacy plugins, forms, etc.
- Risk Rating: High/Medium/Low per regulation

---

## Implementation Order (Recommended)

### Week 1-2: Foundation Tests (Easiest)
1. Audit Trail tests (local data only, no external services)
2. WordPress Ecosystem tests (already have APIs)
3. Core compliance tests (policy checking, no external APIs)

### Week 2-3: Performance & Business
4. Performance Attribution tests (requires profiling setup)
5. Business Impact tests (requires external service scaffolds)

### Week 3+: External Integrations
6. Create permission request UI for external services
7. Implement Google Analytics integration
8. Implement WooCommerce/Stripe integration

---

## Next Action

To start implementation:

1. Pick one category (suggest: **Audit Trail** - 20 tests, highest user value)
2. Implement one test fully (e.g., `audit-logging-enabled`)
3. Create database schema if needed
4. Add KB article and training video
5. Test on staging with multisite
6. Repeat for other tests in category

**Estimated effort per test:** 1-2 hours (30 min implementation, 30 min testing, 30 min KB/training)

---

*Philosophy: Every metric serves a business purpose. Every test drives user confidence and adoption. Every integration respects privacy through consent.*
