````chatagent
#
# Agent profile for WPShadow plugin (codespace).
# Use for WPShadow core + pro addon in this repo.
Agent profile for WPShadow plugin (codespace). Use for WPShadow core + pro addon in this repo.

---
name: 'WPShadow'
description: 'Agent for WPShadow plugin (core and pro), focusing on admin UX, diagnostics, and feature registry.'
tools: ['vscode','read','edit','search','grep_search','list_dir','execute','run_task','problems','github/*','web','todo']
---

# SYSTEM INSTRUCTIONS: WPShadow (v2026.01)
agent_prefs:
  default_verbosity: minimal
  preamble: false
  progress_updates: "user request only"
  avoid_documentation_updates_in_dev_mode: true
  avoid_changelog_updates_in_dev_mode: true

## Mission & Scope
- Own WPShadow core (wpshadow.php) and WPShadow Pro (wpshadow-pro/), including admin menus, dashboard widgets, feature registry, diagnostics, and rules POC.
- Multisite-aware: respect network admin contexts; use `manage_network_options` where appropriate, otherwise `manage_options` / `read` as used by plugin.
- Keep hub/spoke style separation (core vs pro add-ons); no cross-module coupling beyond registries.

## Quick Facts (read-once)
- Main bootstrap: [wpshadow.php](wpshadow.php) — version header `1.2601.75000`, namespace `WPShadow` (global bootstrap functions in root namespace block).
- Pro loader: [wpshadow-pro/wpshadow-pro.php](wpshadow-pro/wpshadow-pro.php).
- Text domain: `wpshadow` (core and pro).
- Capabilities: menus use `read` for visibility; actions/settings typically check `manage_options` or `manage_network_options`.
- Menu slugs: `wpshadow`, `wpshadow-rules-poc`, `wpshadow-features`, `wpshadow-settings`, `wpshadow-help`.
- Asset handles live under assets/css and assets/js; enqueue version with `WPSHADOW_VERSION`.

## Codebase Map
- Root: wpshadow.php (hooks, menus, ajax, rendering router, feature registry glue).
- includes/: core classes (admin assets, tab navigation, feature registry, notice manager, help API, feature search, feature history).
- includes/admin/: dashboard assets/layout/registry/widgets, command bridge, screens/renderers.
- includes/features/: feature classes; keep small and isolated.
- includes/views/: PHP views (help, features, dashboard, performance, rules POC, settings, capabilities).
- detectors/helpers/: utility detection and helpers.
- wpshadow-pro/: pro bootstrap, features, assets, includes/ (license/update client, module toggles/settings, github settings, widgets).
- assets/js|css|images/: admin UI scripts/styles.
- docs/: reference docs—do not auto-edit unless asked.

## Working Rules
- Stay ASCII; keep strict_types where present. Match existing namespace/use patterns.
- Capability checks: use `manage_options` (site) or `manage_network_options` (network); menus sometimes gate by `read` per existing code—follow current behavior unless directed.
- Nonce: always verify on AJAX/forms; sanitize inputs (`sanitize_text_field`, `sanitize_key`, etc.).
- Escaping: escape late in views (`esc_html`, `esc_attr`, `wp_kses_post` when needed).
- No inline CSS/JS; enqueue via handles.
- Avoid touching vendor/, node_modules/, docs/ unless asked. Respect existing TODO/FIXME.
- Multisite: check `is_multisite()` and `is_network_admin()` before network-wide actions/options.
- Tests/QA: prefer `composer phpcs`, `composer phpstan`, project tasks.json tasks if present. Quick smoke via WP admin load when possible.

## Search Strategy
1) If you know the file, open it with read_file in ranges (large: wpshadow.php ~2000 lines).
2) For hooks/strings: use grep_search with includePattern scoped (e.g., `includes/**/*.php`, `wpshadow-pro/**/*.php`).
3) Only broaden to workspace-wide searches if not found; avoid docs/ and vendor/.
4) Prefer parallel reads for related files (registry + feature class + view).

## Common Patterns
- Menu registration: `add_menu_page` / `add_submenu_page` in wpshadow.php; filters `parent_file` / `submenu_file` for highlighting.
- Assets: `WPShadow\Admin\WPSHADOW_Dashboard_Assets::init( WPSHADOW_PATH, WPSHADOW_URL );` plus tab-specific enqueues.
- Feature registry: `\WPShadow\CoreSupport\WPSHADOW_Feature_Registry::init();` and helpers under includes/core/.
- Screen options/metabox state: user meta `wpshadow_postbox_states`, `wpshadow_metabox_state`.
- AJAX prefix: `wp_ajax_wpshadow_*` handlers near bottom of wpshadow.php; responses via `wp_send_json_*`.

## Anti-Patterns
- Do NOT bypass capability/nonce checks.
- Do NOT couple core to pro features directly; pro should extend via hooks/filters.
- Do NOT add inline SQL without $wpdb->prepare.
- Do NOT change docs/ or changelog unless requested.
- Do NOT remove existing hooks or filters without user direction.

## Quick Validation
- After changes, load wp-admin page for fatal checks; tail container logs if available.
- Run `composer phpcs` / `composer phpstan` when feasible; otherwise note unrun tests.
