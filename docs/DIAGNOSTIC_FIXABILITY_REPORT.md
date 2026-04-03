# Diagnostic Fixability Report

**Date:** 2026-04-03  
**Project:** `wpshadow`

---

## Executive Summary

The plugin currently has a **real coverage gap** between what it can diagnose and what it can fix:

- **229** diagnostics are currently exposed by `Diagnostic_Registry`
- **71** have a **shipped automated treatment**
- **8** are **guidance-only** (manual steps returned by a treatment)
- **150** have **no treatment at all**

That means **158 diagnostics are not automatically fixable today**.

The biggest issue is **not detection quality** — it is that fixability is inconsistent and mostly implicit. Diagnostics are easy to add, but many do not declare a treatment path, rollback strategy, or even a clear “manual vs guided vs auto-fix” state.

---

## Evidence Used

This review is based on live code/runtime checks and the current registries:

- `includes/systems/core/class-diagnostic-base.php`
  - `get_available_treatments()` currently returns an empty array by default.
- `includes/systems/treatments/class-treatment-registry.php`
  - `apply_treatment()` returns **"No treatment is available for this finding."** when no mapping exists.
- `includes/systems/core/class-treatment-metadata.php`
  - runtime counts confirm **79 total treatments**, **71 shipped**, **8 guidance**, **66 reversible**.
- `includes/admin/class-file-write-registry.php`
  - shows the existing safe pattern for higher-risk fixes: preview, backup, review, apply.
- `includes/treatments/class-treatment-https-enabled.php`
  - good example of a **guidance-only** treatment for a hosting-level issue.

### Fresh runtime results

```text
TOTAL {"diagnostics":229,"shipped":71,"guidance":8,"none":150}
```

```text
{
  "total": 79,
  "shipped": 71,
  "guidance": 8,
  "reversible": 66,
  "by_risk": {
    "safe": 43,
    "moderate": 14,
    "high": 14,
    "guidance": 8
  }
}
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

| Family | Shipped | Guidance | No Treatment | Read on this gap |
|---|---:|---:|---:|---|
| `accessibility` | 0 | 0 | 12 | Mostly theme/content review; good for assisted fixes, weak for one-click fixes |
| `code-quality` | 5 | 0 | 2 | Low volume gap |
| `database` | 0 | 0 | 12 | **High ROI** for new safe cleanup treatments |
| `design` | 1 | 0 | 16 | Mostly content/theme decisions |
| `monitoring` | 0 | 0 | 10 | Mostly external service/ops setup |
| `performance` | 34 | 3 | 25 | Mixed: some strong opportunities, some hosting/plugin dependencies |
| `security` | 21 | 4 | 21 | Mixed: several strong auto-fix candidates remain |
| `seo` | 4 | 0 | 24 | Mostly plugin/content/configuration assisted fixes |
| `settings` | 5 | 0 | 23 | **Best short-term ROI** for new one-click fixes |
| `wordpress-health` | 0 | 1 | 1 | Mostly environment/hosting |
| `workflows` | 1 | 0 | 4 | Mostly cron/ops concerns |

---

## What Is Currently Unfixable

## A. Guidance-only diagnostics (8)
These *do* have treatments, but they are not automatic.

- `performance`
  - `database-version-supported`
  - `http2-or-http3-enabled`
  - `opcache-enabled`
- `security`
  - `database-prefix-intentional`
  - `https-enabled`
  - `ssl-certificate-valid`
  - `wp-config-location`
- `wordpress-health`
  - `php-version`

### What it would take
These are mostly **hosting / server / infrastructure** issues. To make them more fixable, WPShadow would need:

- hosting provider integrations or API connectors
- environment detection + tailored runbooks
- optional guided install flows (e.g. one-click plugin/helper setup)
- “assisted remediation” UI rather than pretending they can be auto-fixed from WordPress

> Recommendation: keep these as **guided** rather than trying to force them into one-click fixes.

---

## B. No-treatment diagnostics (150)

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

### 2) Settings (23) — **highest short-term win**
These are the most promising “convert to fixable” items because many are plain WordPress options or lightweight config changes:

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

This family alone could likely add **10–15 more shipped auto-fixes** with relatively modest engineering effort.

---

### 3) Database (12) — **best medium-term remediation investment**
All 12 are currently untreated:

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

This family could realistically add **8–10 more useful treatments** if WPShadow invests in preview/backup/rollback tooling.

---

### 4) Security (21) — mixed, but several good wins remain
The no-treatment security issues are:

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

### 5) Performance (25) — mixed opportunity, but plugin integration is the key
The no-treatment performance issues include:

`admin-ampdevmode-assets`, `active-plugin-count-reasonable`, `autoloaded-options`, `admin-scripts-in-head-blocking`, `cdn-for-static-assets`, `css-minification`, `caching-plugin-active`, `critical-css-strategy`, `critical-resources-preloaded`, `database-optimization`, `admin-excessive-inline-scripts`, `admin-excessive-inline-styles`, `extra-image-sizes-trimmed`, `font-loading`, `image-compression-pipeline-active`, `image-dimensions-not-set-causing-layout-shift`, `js-minification`, `implements-lazy-loading`, `webp-support`, `object-cache`, `page-cache-enabled`, `admin-protocol-relative-assets`, `responsive-images-enabled`, `script-debug-production`, `admin-unminified-plugin-assets`

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

### 6) SEO (24) — mostly assisted rather than automatic
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

- `design` — 16 untreated
- `monitoring` — 10 untreated
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
