# Diagnostic Fixability Report

**Date:** 2026-04-05  
**Project:** `wpshadow`

---

## Executive Summary

The plugin still has a meaningful gap between what it can diagnose and what it can remediate directly, but the current code is materially ahead of the older snapshot this report used.

- **230** diagnostics are currently exposed by `Diagnostic_Registry::get_diagnostic_definitions()`.
- **101** executable treatment classes are currently exposed by `Treatment_Registry::get_all()`.
- Of those treatment entries, `Treatment_Metadata::get_counts()` reports **93 automated** and **8 guidance-only**.
- At the current diagnostic-to-treatment mapping level, **100** live diagnostics resolve to an executable treatment and **130** do not.

The main issue is still not detection quality. It is that fixability is uneven, and the diagnostic inventory continues to outpace the remediation layer.

---

## Evidence Used

This report is based on the current registries and runtime metadata:

- `includes/systems/diagnostics/class-diagnostic-registry.php`
  - `get_diagnostic_definitions()` returns the 230 display-ready diagnostics used by dashboard and settings surfaces.
- `includes/systems/treatments/class-treatment-registry.php`
  - `get_all()` currently returns 101 executable treatment classes.
- `includes/systems/core/class-treatment-metadata.php`
  - `get_counts()` currently reports 101 total entries, 93 automated, 8 guidance-only, and 86 reversible.
- `includes/admin/class-file-write-registry.php`
  - still shows the mature pattern for higher-risk fixes: preview, backup, review, apply, and rollback guidance.

### Fresh runtime results

```text
DIAGNOSTICS {"total":230}
TREATMENT_REGISTRY {"total":101}
TREATMENT_METADATA {"total":101,"shipped":93,"guidance":8,"reversible":86}
MAPPED_FIXABILITY {"diagnostics":230,"mapped":100,"unmapped":130}
```

---

## Why the Disconnect Exists

### 1. Diagnostics are easier to ship than treatments
A diagnostic only needs a `check()` implementation. A treatment needs:

- a stable `finding_id`
- `apply()` behavior
- `undo()` behavior or an explicit non-reversible model
- risk classification
- readiness state
- often UI/preview/backup flows for dangerous changes

That asymmetry has allowed diagnostics to scale faster than the remediation layer.

### 2. Fixability is not first-class metadata on diagnostics
Today, the plugin effectively answers **“can this be fixed?”** by checking whether a treatment exists and is production-ready. That is too indirect for the UI and too easy to drift out of sync.

### 3. Many findings are really three different kinds of work
The current system mixes together:

1. **True auto-fixes** — safe option updates, reversible cleanups, controlled file edits  
2. **Guided fixes** — hosting/server/plugin setup that WPShadow can explain but not perform  
3. **Human decisions** — copywriting, design, accessibility judgment, SEO strategy, legal/content completeness

These should not all be presented as if they are one step away from a button click.

### 4. Some of the existing fix architecture is coupled more tightly than it needs to be
For example, high-risk file-write treatments self-register through `File_Write_Registry`. That pattern is good, but it also means the treatment layer has stronger bootstrap dependencies than the diagnostic layer. The remediation side would benefit from a cleaner, more explicit contract.

---

## Coverage by Diagnostic Family

| Family | Mapped Treatments | Unmapped Diagnostics | Read on this gap |
|---|---:|---:|---|
| `accessibility` | 0 | 12 | Mostly theme/content review; good for assisted fixes, weak for one-click fixes |
| `code-quality` | 5 | 2 | Low volume gap |
| `database` | 2 | 10 | Good target for preview-first cleanup treatments |
| `design` | 3 | 14 | Mostly content/theme decisions |
| `monitoring` | 2 | 8 | Often external service or ops setup |
| `performance` | 40 | 23 | Mixed: some strong wins, some hosting/plugin dependencies |
| `security` | 27 | 19 | Mixed: several strong auto-fix candidates remain |
| `seo` | 7 | 21 | Mostly plugin/content/configuration assisted fixes |
| `settings` | 12 | 16 | Best short-term ROI for additional one-click fixes |
| `wordpress-health` | 1 | 1 | Mostly environment and hosting |
| `workflows` | 1 | 4 | Mostly cron and ops concerns |

---

## What Needs Attention Most

### Guidance-only treatment entries

`Treatment_Metadata` still tracks 8 guidance-only treatment entries. In the current runtime snapshot, those guidance entries are not attached to the 230 live diagnostic IDs exposed by `Diagnostic_Registry::get_diagnostic_definitions()`. They should either be wired back into diagnostic flows intentionally or documented as standalone/manual treatment surfaces.

### Unmapped diagnostics (130)

The largest remediation gap is still the 130 diagnostics that have no current treatment mapping.

### 1) Accessibility (12)
**All 12 currently have no treatment path:**

`button-text-specific`, `focus-outline-preserved`, `form-error-messaging`, `lang-attribute-correct`, `heading-structure-reviewable`, `image-alt-process`, `underlines-or-link-distinction`, `nav-menu-accessible-name`, `motion-reduction`, `search-form-accessible-name`, `skip-link-present`, `viewport-meta`

### What it would take
These are usually **theme or content changes**, not simple option flips:

- insert or patch theme markup safely
- add CSS overrides for focus states / reduced motion / link distinction
- inspect and rewrite post content or template output
- generate admin review diffs before applying

### Best path forward
- Promote these to **assisted code fixes** using the existing file-write review flow
- Auto-generate proposed snippets for:
  - `skip-link-present`
  - `viewport-meta`
  - `search-form-accessible-name`
  - `nav-menu-accessible-name`
  - `motion-reduction`
  - `focus-outline-preserved`
- Keep **content-writing** issues manual:
  - `button-text-specific`
  - `image-alt-process`
  - `heading-structure-reviewable`

---

### 2) Settings (16) — **highest short-term win**
These remain the most promising “convert to fixable” items because many are plain WordPress options or lightweight config changes:

`admin-email-deliverable`, `admin-email-domain-match`, `comment-policy-intentional`, `comment-spam-backlog`, `cookie-consent-plugin-active`, `date-time-format-intentional`, `discussion-defaults`, `front-page`, `legal-pages-linked-footer`, `mail-sender`, `maintenance-mode-off`, `media-sizes`, `media-year-month-folders-enabled`, `posts-per-page-optimized`, `registration-setting-intentional`, `smtp`, `site-language-intentional`, `site-title-tagline-intentional`, `site-urls-correctly`, `timezone`, `trash-auto-empty-configured`, `update-services-intentional`, `upload-size-configured`

### What it would take
A generic **Settings Treatment** framework could cover many of these:

- preflight validation
- current vs proposed value preview
- reversible `update_option()` changes
- confirmation text for policy-sensitive items
- optional guided plugin install for `smtp` / `cookie-consent-plugin-active`

### Likely candidates for true one-click fixes
- `timezone`
- `front-page`
- `discussion-defaults`
- `posts-per-page-optimized`
- `media-year-month-folders-enabled`
- `trash-auto-empty-configured`
- `site-language-intentional`
- `site-title-tagline-intentional`
- `maintenance-mode-off`
- `update-services-intentional`

This family alone could likely add **8–12 more shipped auto-fixes** with relatively modest engineering effort.

---

### 3) Database (10) — **best medium-term remediation investment**
Ten database diagnostics are still untreated:

`tables-without-primary-key`, `auto-draft-accumulation`, `wp-options-autoload-size`, `duplicate-post-meta-keys`, `myisam-tables-detected`, `orphaned-user-meta`, `stale-sessions-cleared`, `user-table-large`, `woocommerce-session-table-size`, `wp-options-row-count-reasonable`, `post-meta-bloat-detected`, `user-meta-bloat-detected`

### What it would take
To make these safely fixable, WPShadow needs a **database maintenance framework** with:

- dry-run SQL previews
- row counts and impact estimates before execution
- table/row backups or snapshot export before deletion
- scoped cleanup rules (never blanket-delete without allowlists)
- undo support where practical, and explicit irreversible warnings where not

### Strong candidates for implementation
- `auto-draft-accumulation`
- `orphaned-user-meta`
- `stale-sessions-cleared`
- `woocommerce-session-table-size`
- `wp-options-row-count-reasonable`
- `post-meta-bloat-detected`
- `user-meta-bloat-detected`

This family could realistically add **6–8 more useful treatments** if WPShadow invests in preview/backup/rollback tooling.

---

### 4) Security (19) — mixed, but several good wins remain
The remaining no-treatment security issues are:

`admin-account-count-minimized`, `application-passwords-intentional`, `auto-update-policy-reviewed`, `backup-files-not-public`, `db-credentials-not-exposed`, `default-admin-username-removed`, `plugin-auto-updates`, `plugins-updated`, `privacy-policy-links-visible`, `rest-api-sensitive-routes-protected`, `spam-protection-enabled`, `file-editor-disabled`, `themes-updated`, `two-factor-admin-enabled`, `unused-plugins-removed`, `unused-themes-removed`, `user-enumeration-reduced`, `core-updated`, `xmlrpc-policy-intentional`, `readme-html-protected`, `wp-content-write-scope-minimized`

### What it would take
This family splits into three buckets:

#### Easy / moderate auto-fix candidates
- `plugin-auto-updates`
- `file-editor-disabled`
- `unused-plugins-removed`
- `unused-themes-removed`
- `privacy-policy-links-visible`
- `spam-protection-enabled` (guided plugin setup)

#### Medium / risky
- `user-enumeration-reduced`
- `readme-html-protected`
- `backup-files-not-public`
- `rest-api-sensitive-routes-protected`
- `wp-content-write-scope-minimized`

These usually require `.htaccess`, nginx, or `wp-config.php` changes — a good match for the existing file-write review system.

#### Keep guided/manual
- `core-updated`, `plugins-updated`, `themes-updated`
- `default-admin-username-removed`
- `admin-account-count-minimized`
- `two-factor-admin-enabled`

These affect live users, login flows, or account ownership and should remain confirmation-heavy or guided.

---

### 5) Performance (23) — mixed opportunity, but plugin integration is the key
The remaining no-treatment performance issues include:

`admin-ampdevmode-assets`, `active-plugin-count-reasonable`, `autoloaded-options`, `admin-scripts-in-head-blocking`, `cdn-for-static-assets`, `css-minification`, `caching-plugin-active`, `critical-css-strategy`, `critical-resources-preloaded`, `database-optimization`, `admin-excessive-inline-scripts`, `admin-excessive-inline-styles`, `extra-image-sizes-trimmed`, `font-loading`, `image-compression-pipeline-active`, `image-dimensions-not-set-causing-layout-shift`, `js-minification`, `webp-support`, `object-cache`, `page-cache-enabled`, `admin-protocol-relative-assets`, `responsive-images-enabled`, `script-debug-production`, `admin-unminified-plugin-assets`

### What it would take
These are not all the same problem:

- some need **plugin configuration** (`page-cache-enabled`, `object-cache`, `css-minification`, `js-minification`)
- some need **theme/frontend code changes** (`critical-css-strategy`, `font-loading`, image dimensions, lazy loading)
- some need **developer cleanup** inside the plugin/admin UI itself (`admin-excessive-inline-scripts`, `admin-excessive-inline-styles`, `admin-unminified-plugin-assets`)
- some need **infrastructure** (`cdn-for-static-assets`)

### Best path forward
Build **integration adapters** for the most common stack combinations:

- LiteSpeed Cache
- WP Rocket
- Autoptimize
- BunnyCDN / Cloudflare
- image optimization plugins

That would turn several of these from “no treatment” into “guided setup” or “semi-automatic configuration”.

---

### 6) SEO (21) — mostly assisted rather than automatic
The untreated SEO diagnostics are:

`author-archives-intentional`, `canonical-urls`, `category-strategy`, `custom-404-strategy-present`, `document-title-format`, `homepage-has-one-h1`, `homepage-meta`, `meta-descriptions-managed`, `meta-titles-managed`, `noindex-policy`, `open-graph-defaults-set`, `organization-schema`, `permalink-structure-meaningful`, `redirect-management`, `robots-policy`, `seo-plugin-config-intentional`, `schema-basics`, `search-engine-visibility-intentional`, `search-page-indexing`, `site-icon`, `social-profile-links`, `tag-archives-intentional`, `twitter-card`, `xml-sitemap-enabled`

### What it would take
Most of these are not good candidates for one-click automation because they involve:

- SEO plugin selection/configuration
- copywriting and metadata decisions
- theme markup or schema output changes
- site strategy choices (archives, indexing, taxonomy use)

### Recommended model
Treat these as **assisted fixes**:

- generate recommended settings for supported SEO plugins
- draft metadata templates for review
- offer code snippets / schema JSON-LD blocks for approval
- show exactly which plugin setting screens need updating

---

### 7) Design, Monitoring, Workflows, Code Quality
These groups are mostly about site completeness, operations, or editorial choices:

- `design` — 14 untreated
- `monitoring` — 8 untreated
- `workflows` — 4 untreated
- `code-quality` — 2 untreated
- `wordpress-health` — 1 untreated (`site-health-criticals-addressed`)

These should generally be split into:

- **guided setup** (analytics, backups, cron, logging, monitoring)
- **content completeness** (about page, contact page, footer menu, social links)
- **manual editorial cleanup** (sample content, placeholder media)

### Full inventory for these families

#### `design` (16)
`about-page-published`, `child-theme-active`, `contact-page-has-form`, `contact-page-published`, `copyright-year-current`, `custom-logo-set`, `draft-pages-accumulation`, `footer-menu`, `homepage-displays-intentional`, `homepage-page-published`, `mobile-menu`, `posts-page-published`, `primary-navigation-assigned`, `posts-have-featured-images`, `search-enabled-intentional`, `terms-of-service-page`

#### `monitoring` (10)
`404-monitoring`, `analytics-installed-intentional`, `application-health-checks-registered`, `backups-automated`, `error-logging`, `fatal-error-handler-enabled`, `php-extensions-required`, `scheduled-posts-not-stuck`, `system-cron-production`, `wp-cron-reliable`

#### `workflows` (4)
`cron-health`, `external-cron`, `system-cron-offload`, `cron-traffic-dependence`

#### `code-quality` (2)
`demo-media-removed`, `sample-content-removed`

These are important diagnostics, but they should not be treated as “broken one-click fixes” when the real missing piece is a guided workflow.

---

## What It Would Take to Make More of the Plugin Truly Fixable

## Phase 1 — Fix the product model (high impact, low effort)
**Goal:** make the disconnect visible and intentional.

### Add explicit fixability metadata to each diagnostic
Each diagnostic should declare something like:

- `auto` — safe one-click fix exists
- `review` — fix exists but needs preview/approval
- `guided` — WPShadow can walk the user through it
- `assisted` — WPShadow can generate draft code/content/settings
- `manual` — human judgment required

Also add:

- `rollback_strategy`
- `required_capability`
- `estimated_effort`
- `target_surface` (`wordpress-option`, `plugin-config`, `theme-file`, `content`, `server`, `database`)

**Impact:** immediately removes the “disconnect” feeling in the UI.

---

## Phase 2 — Expand the easy wins first (best ROI)
**Target families:** `settings`, parts of `security`, parts of `database`

### Recommended next batch of real shipped treatments
1. `timezone`
2. `front-page`
3. `discussion-defaults`
4. `posts-per-page-optimized`
5. `trash-auto-empty-configured`
6. `plugin-auto-updates`
7. `file-editor-disabled`
8. `unused-plugins-removed`
9. `unused-themes-removed`
10. `auto-draft-accumulation`
11. `orphaned-user-meta`
12. `stale-sessions-cleared`

**Expected gain:** roughly **20–30 additional diagnostics** could become materially fixable or review-fixable in this phase.

---

## Phase 3 — Add a proper “assisted remediation” layer
This is the missing middle between diagnosis and one-click fixes.

### For theme/content issues
Add:
- generated CSS patches
- proposed template diffs
- draft copy replacements
- preview + approve + write flow using the existing file-review pattern

### For plugin/integration issues
Add:
- recommended plugin installer/setup flows
- supported adapters for caching, SEO, SMTP, cookie consent, image optimization
- plugin-specific setting writers with rollback

**This phase is how accessibility, performance, and SEO become meaningfully more fixable without pretending that all of them are safe one-click actions.**

---

## Recommended Priority Order

### Highest ROI
1. `settings` family
2. `database` family
3. easy `security` toggles / cleanup items

### Best for assisted remediation
4. `performance` family
5. `accessibility` family
6. `seo` family

### Keep primarily guided/manual
7. `monitoring`
8. `workflows`
9. hosting/server-only security & performance checks

---

## Bottom Line

The main disconnect is real, but it is fixable.

### Today
- WPShadow is strong at **finding** issues
- only part of the catalog is strong at **remediating** them

### Short-term recommendation
Invest first in:
- **settings treatments**
- **database cleanup treatments**
- **security/config toggles**
- **explicit fixability metadata in the UI**

### Longer-term recommendation
Add an **assisted remediation layer** so the plugin can safely bridge the gap for accessibility, SEO, design, and performance issues that will never be good “blind one-click fixes.”

If you do that, the plugin will feel much more coherent: every diagnostic will clearly say whether it is **auto-fixable, review-fixable, guided, assisted, or manual**, and the current disconnect will largely disappear.
