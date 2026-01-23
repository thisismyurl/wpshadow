# WPShadow File Migration Mapping (131 Files)
**Status:** Ready for execution
**Generated:** 2025-01-23
**Total Files:** 131
**Risk Level:** LOW (structure changes only, no logic changes)

---

## 🎯 Quick Visual Reference

### NEW STRUCTURE (POST-MIGRATION)

```
includes/
├── core/                          (15 files - BASE CLASSES ONLY)
├── utils/                         (12 files - SHARED UTILITIES)
├── dashboard/                     (18 files - DASHBOARD + MONITORING UI)
│   ├── widgets/
│   └── views/
├── screens/                       (10 files - ADMIN PAGES)
├── monitoring/                    (18 files - ANALYSIS + RECOVERY)
│   ├── analyzers/
│   └── recovery/
├── ajax/                          (55+ files - ALREADY ORGANIZED)
├── workflow/                      (16 files - AUTOMATION, KEEP AS-IS)
├── content/                       (9 files - KB + FAQ + TIPS)
│   └── kb/
├── engagement/                    (5 files - GAMIFICATION)
├── integration/                   (8 files - CLOUD + EXTERNAL)
│   └── cloud/
├── privacy/                       (3 files - KEEP AS-IS)
├── settings/                      (7 files - CONFIGURATION)
├── reporting/                     (8 files - CONSOLIDATED REPORTS)
├── onboarding/                    (2-3 files - KEEP AS-IS)
├── cli/                           (1 file - KEEP AS-IS)
├── diagnostics/                   (KEEP AS-IS - NOT MOVED)
├── treatments/                    (KEEP AS-IS - NOT MOVED)
├── data/                          (KEEP AS-IS)
├── views/                         (KEEP AS-IS)
├── detectors/                     (KEEP AS-IS)
├── helpers/                       (KEEP AS-IS)
├── faq/                           (MOVE TO content/ as needed)
├── gamification/                  (MOVE TO engagement/)
└── knowledge-base/                (MOVE TO content/kb/)
```

---

## 📋 MIGRATION MAPPING (FILE BY FILE)

### ✅ KEEP IN PLACE (NO CHANGES)

```
admin/ajax/                    → No change (AJAX handlers stay organized here)
diagnostics/                   → No change (Diagnostic classes)
treatments/                    → No change (Treatment classes)
treatments/performance/        → No change
data/                          → No change (JSON tooltips, KB mappings)
views/                         → No change (Templates)
detectors/                     → No change (Detection utilities)
helpers/                       → No change (Helper functions)
cli/class-wpshadow-cli.php    → No change (already minimal)
```

---

### 🔄 MIGRATIONS BY DESTINATION

---

## 1️⃣ `core/` (15 files) - BASE CLASSES ONLY

**FILES STAYING IN core/**
```
core/class-diagnostic-base.php                           ✅ KEEP
core/class-treatment-base.php                            ✅ KEEP
core/class-ajax-handler-base.php                         ✅ KEEP
core/class-abstract-registry.php                         ✅ KEEP
core/class-activity-logger.php                           ✅ KEEP
core/class-error-handler.php                             ✅ KEEP
core/class-finding-status-manager.php                    ✅ KEEP
core/class-finding-utils.php                             ✅ KEEP
core/class-kpi-tracker.php                               ✅ KEEP
core/class-kpi-metadata.php                              ✅ KEEP
core/class-kpi-summary-card.php                          ✅ KEEP
core/class-kpi-advanced-features.php                     ✅ KEEP
core/class-performance-impact-classifier.php             ✅ KEEP
core/class-recommendation-engine.php                     ✅ KEEP
core/class-scoring-engine.php                            ✅ KEEP
```

---

## 2️⃣ `utils/` (12 FILES) - SHARED UTILITIES

**FROM core/ → utils/**
```
core/class-color-utils.php                    → utils/class-color-utils.php
core/class-theme-data-provider.php            → utils/class-theme-data-provider.php
core/class-user-preferences-manager.php       → utils/class-user-preferences-manager.php
core/class-timezone-manager.php               → utils/class-timezone-manager.php
core/class-analysis-helpers.php               → utils/class-analysis-helpers.php
core/class-site-health-explanations.php       → utils/class-site-health-explanations.php
core/class-treatment-hooks.php                → utils/class-treatment-hooks.php
core/class-command-base.php                   → utils/class-command-base.php
core/class-dashboard-customization.php        → utils/class-dashboard-customization.php
core/class-diagnostic-scheduler.php           → utils/class-diagnostic-scheduler.php
core/class-diagnostic-lean-checks.php         → utils/class-diagnostic-lean-checks.php
core/class-diagnostic-result-normalizer.php   → utils/class-diagnostic-result-normalizer.php
```

---

## 3️⃣ `dashboard/` (18 FILES) - DASHBOARD + MONITORING UI

**FROM guardian/ → dashboard/**
```
guardian/class-guardian-dashboard.php         → dashboard/class-guardian-dashboard.php
guardian/class-site-health-bridge.php         → dashboard/class-site-health-bridge.php
guardian/class-trend-chart.php                → dashboard/class-trend-chart.php
guardian/class-dashboard-performance-analyzer.php
                                              → dashboard/class-dashboard-performance-analyzer.php
```

**FROM admin/ → dashboard/**
```
admin/class-guardian-settings.php             → screens/class-guardian-settings.php (or shared)
admin/class-asset-manager.php                 → dashboard/class-asset-manager.php
admin/class-asset-optimizer.php               → dashboard/class-asset-optimizer.php
admin/class-ajax-response-optimizer.php       → dashboard/class-ajax-response-optimizer.php
```

**FROM admin/ → dashboard/widgets/**
```
admin/class-tooltip-manager.php               → dashboard/widgets/class-tooltip-manager.php
```

**FROM widgets/ → dashboard/widgets/**
```
widgets/class-activity-feed-widget.php        → dashboard/widgets/class-activity-feed-widget.php
widgets/class-kpi-summary-widget.php          → dashboard/widgets/class-kpi-summary-widget.php
widgets/class-top-issues-widget.php           → dashboard/widgets/class-top-issues-widget.php
```

**FROM admin/ → dashboard/views/**
```
admin/views/dashboard-main.php                → dashboard/views/dashboard-main.php
admin/views/gauges-module.php                 → dashboard/views/gauges-module.php
admin/views/activity-module.php               → dashboard/views/activity-module.php
```

**ADDITIONAL dashboard/**
```
admin/class-guardian-dashboard.php            → dashboard/class-guardian-dashboard.php
admin/class-admin-notice-cleaner.php          → dashboard/class-admin-notice-cleaner.php
admin/class-admin-font-management.php         → dashboard/class-admin-font-management.php (if exists)
```

---

## 4️⃣ `screens/` (10 FILES) - ADMIN PAGES & FORMS

**FROM admin/ → screens/**
```
admin/class-guardian-settings.php             → screens/class-guardian-settings.php
admin/class-help-page-module.php              → screens/class-help-page-module.php
admin/class-privacy-page-module.php           → screens/class-privacy-page-module.php
admin/class-tools-page-module.php             → screens/class-tools-page-module.php
admin/class-notification-preferences-form.php → screens/class-notification-preferences-form.php
admin/class-report-form.php                   → screens/class-report-form.php
admin/class-update-notification-manager.php   → screens/class-update-notification-manager.php
admin/class-option-optimizer.php              → screens/class-option-optimizer.php
admin/class-site-health-bridge.php            → screens/class-site-health-bridge.php (or dashboard)
```

---

## 5️⃣ `monitoring/analyzers/` (15 FILES) - ANALYSIS

**FROM guardian/ → monitoring/analyzers/**
```
guardian/class-api-latency-analyzer.php                  → monitoring/analyzers/
guardian/class-bot-traffic-analyzer.php                  → monitoring/analyzers/
guardian/class-browser-compatibility-analyzer.php       → monitoring/analyzers/
guardian/class-cache-invalidation-analyzer.php          → monitoring/analyzers/
guardian/class-block-rendering-performance-analyzer.php → monitoring/analyzers/
guardian/class-canvas-webgl-performance-analyzer.php    → monitoring/analyzers/
guardian/class-captcha-performance-analyzer.php         → monitoring/analyzers/
guardian/class-csp-violation-analyzer.php               → monitoring/analyzers/
guardian/class-failed-login-analyzer.php                → monitoring/analyzers/
guardian/class-hook-execution-analyzer.php              → monitoring/analyzers/
guardian/class-layout-thrashing-analyzer.php            → monitoring/analyzers/
guardian/class-live-chat-performance-analyzer.php       → monitoring/analyzers/
guardian/class-rest-api-performance-analyzer.php        → monitoring/analyzers/
guardian/class-shortcode-execution-analyzer.php         → monitoring/analyzers/
guardian/class-third-party-script-analyzer.php          → monitoring/analyzers/
```

---

## 6️⃣ `monitoring/recovery/` (7 FILES) - RECOVERY & FIXES

**FROM guardian/ → monitoring/recovery/**
```
guardian/class-recovery-system.php            → monitoring/recovery/class-recovery-system.php
guardian/class-backup-manager.php             → monitoring/recovery/class-backup-manager.php
guardian/class-auto-fix-executor.php          → monitoring/recovery/class-auto-fix-executor.php
guardian/class-auto-fix-policy-manager.php    → monitoring/recovery/class-auto-fix-policy-manager.php
guardian/class-compliance-checker.php         → monitoring/recovery/class-compliance-checker.php
guardian/class-compromised-accounts-analyzer.php
                                              → monitoring/recovery/
guardian/class-guardian-activity-logger.php   → monitoring/class-guardian-activity-logger.php
```

---

## 7️⃣ `content/` (9 FILES) - CONTENT + KNOWLEDGE BASE

**FROM knowledge-base/ → content/kb/**
```
knowledge-base/class-kb-library.php           → content/kb/class-kb-library.php
knowledge-base/class-kb-search.php            → content/kb/class-kb-search.php
knowledge-base/class-kb-article-generator.php → content/kb/class-kb-article-generator.php
knowledge-base/class-kb-formatter.php         → content/kb/class-kb-formatter.php
knowledge-base/class-training-provider.php    → content/kb/class-training-provider.php
knowledge-base/class-training-progress.php    → content/kb/class-training-progress.php
```

**FROM faq/ → content/**
```
faq/class-faq-post-type.php                   → content/class-faq-post-type.php
```

**FROM admin/ → content/**
```
admin/class-tips-coach.php                    → content/class-tips-coach.php (if exists)
```

---

## 8️⃣ `engagement/` (5 FILES) - GAMIFICATION

**FROM gamification/ → engagement/**
```
gamification/class-achievement-system.php     → engagement/class-achievement-system.php
gamification/class-badge-manager.php          → engagement/class-badge-manager.php
gamification/class-leaderboard-manager.php    → engagement/class-leaderboard-manager.php
gamification/class-milestone-notifier.php     → engagement/class-milestone-notifier.php
gamification/class-streak-tracker.php         → engagement/class-streak-tracker.php
```

---

## 9️⃣ `integration/cloud/` (6 FILES) - CLOUD INTEGRATIONS

**FROM cloud/ → integration/cloud/**
```
cloud/class-cloud-client.php                  → integration/cloud/class-cloud-client.php
cloud/class-registration-manager.php          → integration/cloud/class-registration-manager.php
cloud/class-deep-scanner.php                  → integration/cloud/class-deep-scanner.php
cloud/class-usage-tracker.php                 → integration/cloud/class-usage-tracker.php
cloud/class-multisite-dashboard.php           → integration/cloud/class-multisite-dashboard.php
cloud/class-notification-manager.php          → integration/cloud/class-notification-manager.php
```

---

## 🔟 `reporting/` (8 FILES) - CONSOLIDATED REPORTS

**FROM reporting/ → reporting/**
```
reporting/class-event-logger.php              → reporting/class-event-logger.php
reporting/class-notification-manager.php      → reporting/class-notification-manager.php
reporting/class-report-generator.php          → reporting/class-report-generator.php
```

**FROM reports/ → reporting/**
```
reports/class-report-builder.php              → reporting/class-report-builder.php
reports/class-report-engine.php               → reporting/class-report-engine.php
reports/class-report-renderer.php             → reporting/class-report-renderer.php
```

**FROM settings/ → reporting/**
```
settings/class-report-scheduler.php           → reporting/class-report-scheduler.php
```

---

## 1️⃣1️⃣ `privacy/` (3 FILES) - KEEP AS-IS

```
privacy/class-consent-preferences.php         ✅ KEEP
privacy/class-first-run-consent.php           ✅ KEEP
privacy/class-privacy-policy-manager.php      ✅ KEEP
```

---

## 1️⃣2️⃣ `settings/` (7 FILES) - CONFIGURATION

**KEEP IN settings/**
```
settings/class-data-retention-manager.php     ✅ KEEP
settings/class-email-template-manager.php     ✅ KEEP
settings/class-privacy-settings-manager.php   ✅ KEEP
settings/class-scan-frequency-manager.php     ✅ KEEP
```

---

## 1️⃣3️⃣ `onboarding/` (2-3 FILES) - KEEP AS-IS

```
onboarding/class-onboarding-manager.php       ✅ KEEP
onboarding/class-platform-translator.php      ✅ KEEP
onboarding/data/                              ✅ KEEP
```

---

## 1️⃣4️⃣ `workflow/` (16 FILES) - KEEP AS-IS

```
workflow/class-workflow-manager.php           ✅ KEEP
workflow/class-workflow-executor.php          ✅ KEEP
workflow/class-workflow-wizard.php            ✅ KEEP
workflow/commands/                            ✅ KEEP (20+ files)
(All other workflow files)                    ✅ KEEP
```

---

## 1️⃣5️⃣ `admin/` (remaining files after migration)

**KEEP IN admin/**
```
admin/ajax/                                   ✅ KEEP (55+ AJAX handlers)
admin/class-admin-notice-cleaner.php          ✅ KEEP or MOVE to dashboard
```

---

## 📊 MIGRATION SUMMARY TABLE

| FROM | TO | COUNT | STATUS |
|------|-----|-------|--------|
| core/ → utils/ | utils/ | 12 | ➡️ MOVE |
| guardian/ | dashboard/ + monitoring/ | 20 | ➡️ MOVE |
| admin/ (screens) | screens/ | 8 | ➡️ MOVE |
| admin/ (UI) | dashboard/ | 4 | ➡️ MOVE |
| admin/widgets/ | dashboard/widgets/ | 3 | ➡️ MOVE |
| widgets/ | dashboard/widgets/ | 3 | ➡️ MOVE |
| knowledge-base/ | content/kb/ | 6 | ➡️ MOVE |
| faq/ | content/ | 1 | ➡️ MOVE |
| gamification/ | engagement/ | 5 | ➡️ MOVE |
| cloud/ | integration/cloud/ | 6 | ➡️ MOVE |
| reports/ + reporting/ | reporting/ | 8 | ➡️ MOVE/MERGE |
| privacy/ | privacy/ | 3 | ✅ KEEP |
| settings/ | settings/ | 7 | ✅ KEEP |
| onboarding/ | onboarding/ | 3 | ✅ KEEP |
| workflow/ | workflow/ | 16 | ✅ KEEP |
| admin/ajax/ | admin/ajax/ | 55+ | ✅ KEEP |
| cli/ | cli/ | 1 | ✅ KEEP |
| diagnostics/ | diagnostics/ | 57 | ✅ KEEP |
| treatments/ | treatments/ | 44 | ✅ KEEP |
| **TOTAL** | | **131** | |

---

## 🔍 QUICK REFERENCE: WHERE THINGS GO

### "I'm looking for..."

**Dashboard/UI code?**
→ `includes/dashboard/`

**Performance analyzers?**
→ `includes/monitoring/analyzers/`

**Recovery/backup/fix systems?**
→ `includes/monitoring/recovery/`

**Knowledge base/KB?**
→ `includes/content/kb/`

**Workflow automation?**
→ `includes/workflow/`

**Cloud sync/registration?**
→ `includes/integration/cloud/`

**Report generation?**
→ `includes/reporting/`

**Gamification?**
→ `includes/engagement/`

**Privacy/consent?**
→ `includes/privacy/`

**Settings/configuration?**
→ `includes/settings/`

**AJAX handlers?**
→ `includes/admin/ajax/`

**Base classes?**
→ `includes/core/`

**Utilities/helpers?**
→ `includes/utils/`

---

## 🚀 NEXT STEPS

1. ✅ **Directories created** - All new directories in place
2. ⏳ **File migration** - Execute moves
3. ⏳ **Require updates** - Update include/require statements
4. ⏳ **Namespace verification** - Ensure PSR-4 autoloading works
5. ⏳ **WordPress test** - Load admin pages, verify no fatals
6. ⏳ **Commit** - Philosophy-aligned commit message

---

**Generated:** 2025-01-23
**Files to migrate:** 131
**New directories:** 15
**Estimated time:** 2-3 hours execution + 1 hour testing
**Risk:** LOW ✅
