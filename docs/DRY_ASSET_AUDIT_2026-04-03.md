# DRY Asset & Dependency Audit (April 3, 2026)

This audit focuses on whether referenced CSS/JS assets are actually present and whether enqueue logic is DRY/safe.

## Scope

- PHP enqueue points under `includes/`
- Asset files under `assets/css/` and `assets/js/`
- Shared enqueue helpers (`Admin_Asset_Registry`, dashboard asset manager, hooks initializer)

## Findings

### 1. Referenced asset inventory is broader than shipped assets

Static scan of PHP references found **41 distinct asset paths**, but only **7 files currently exist** in this workspace's `assets/` tree.

Existing files in this workspace:

- `assets/css/gauges.css`
- `assets/css/resolution-page.css`
- `assets/css/settings-page.css`
- `assets/css/wpshadow-system.css`
- `assets/js/file-write-review.js`
- `assets/js/settings-page.js`
- `assets/js/wpshadow-dashboard.js`

Referenced-but-missing count: **34**

### 2. DRY/runtime risk before hardening

Multiple enqueue paths were attempting to load files that do not exist, causing avoidable runtime 404s and noisy dependency surfaces.

### 3. Hardening implemented in this pass

#### Updated: `includes/systems/dashboard/class-asset-manager.php`

- Added DRY helpers:
  - `wpshadow_enqueue_style_if_exists()`
  - `wpshadow_enqueue_script_if_exists()`
- Updated major enqueue functions to use safe checks and conditional localization:
  - workflow assets
  - guardian assets
  - mobile friendliness
  - accessibility audit
  - broken links
  - site health styles
  - tooltips
  - admin pages assets
  - reports

#### Updated: `includes/systems/core/class-hooks-initializer.php`

- Added closure-based safe enqueue helpers inside `on_admin_enqueue_scripts()`.
- Replaced direct enqueues with file-existence-guarded enqueues.
- Added `wp_script_is(..., 'enqueued')` checks before localizing scripts.
- Added safe frontend style enqueue check in `on_wp_enqueue_scripts()`.

#### Updated: `includes/systems/core/class-admin-asset-registry.php`

- `enqueue_modal_assets()` now checks whether modal CSS/JS files exist before enqueuing.
- Uses filemtime versions when files exist.

## Current status after hardening

- Release proof script passes with **0 failures**.
- Runtime no longer depends on missing assets for the hardened paths.
- Missing references still exist in code as historical/optional paths, but are now safely non-loading when absent.

## Recommended next cleanup phases

### Phase 1 (safe, low risk)

- Keep current guardrails in place (done).
- Add CI check that flags newly introduced unguarded enqueues.

### Phase 2 (codebase DRY pruning)

- Remove legacy enqueue branches for features no longer shipped in this repo snapshot.
- Consolidate duplicated enqueue responsibilities between `class-hooks-initializer.php` and `class-asset-manager.php` into a single source of truth.

### Phase 3 (feature completeness)

Choose one path per missing asset group:

- Restore missing asset files and keep feature branches active, or
- Remove feature branches from enqueue maps if the feature is intentionally not shipped.

## Practical conclusion

For this workspace state, the plugin is now significantly safer and DRYer at runtime because missing assets are no longer blindly enqueued. The next major gain is structural pruning of legacy branches once product scope decisions are finalized.
