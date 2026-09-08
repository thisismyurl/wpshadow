# Developer Hooks Reference for Shadow by Christopher Ross

**Last Updated:** June 14, 2026
**Audience:** developers extending or integrating with Shadow
**Purpose:** document the actions and filters Shadow fires so integrators can extend behaviour without forking the plugin

---

## What the hook system is for

Shadow runs a pipeline: it discovers diagnostics, runs them, records findings,
and (where a treatment exists) offers to apply a fix. Almost every step in that
pipeline fires a WordPress action or passes its data through a filter. That lets
you:

- suppress, rewrite, or annotate a diagnostic finding before it is stored
- gate whether a treatment is allowed to run on a given site
- adjust environment policy, rate limits, and scan scope
- react to lifecycle events (scan finished, finding resolved, cache flushed)
  for logging, notifications, or reporting

You extend Shadow the same way you extend WordPress core: `add_filter()` to
change a value, `add_action()` to react to an event.

## Naming convention

Every hook Shadow fires is prefixed `wpshadow_`. If a hook name does
not start with that prefix, Shadow did not fire it — it belongs to core or
another plugin. A small number of hooks are **dynamic**: the option name is
appended to the base name (for example
`wpshadow_setting_updated_{$option}`).

## Stability note

These hooks are the plugin's public extension surface. Most carry a `@since`
tag in source (`0.6095`, `0.7055`, …). Shadow is in public beta, so signatures
can still change between beta releases; pin the version you integrated against
and re-check this document when you upgrade. Hook **names** are treated as the
most stable part of the contract — parameters may gain trailing arguments
before names change.

A note on `$class_name` / `$class` parameters: these are fully-qualified PHP
class names (for example `WPShadow\Diagnostics\Some_Check`). Match on
them with care, since refactors can rename a class without renaming its hook.

---

## Filters

### Diagnostics — discovery and execution

| Filter | Filtered value | Parameters | Use this to… |
|---|---|---|---|
| `wpshadow_diagnostic_definitions` | `array` of diagnostic definition arrays | `$definitions` (array) | Add, remove, or reorder the diagnostics the UI and CLI list. |
| `wpshadow_diagnostic_class` | `string\|null` resolved class | `$resolved` (string\|null), `$diagnostic_id` (string, sanitized) | Map a custom identifier to a diagnostic class, or override resolution. |
| `wpshadow_diagnostic_file_map` | `array<string, array{file,family}>` | `$map` (array) | Add or relocate diagnostic source files in the discovery map. |
| `wpshadow_diagnostic_enabled` | `bool` enabled flag | `$enabled` (bool), `$class`/`$qualified` (string FQCN) | Force a diagnostic on or off regardless of the saved option. |
| `wpshadow_diagnostic_metadata` | `array<string, array>` slug → metadata | `$map` (array) | Override per-diagnostic metadata (`confidence`, `is_core`, `auto_fix_safe`, `notes`); deep-merged per key. |
| `wpshadow_diagnostic_result_ttl` | `int` seconds | `$ttl` (int), `$class_name` (string FQCN) | Tune how long a diagnostic result is cached (clamped 5 min–30 days). |
| `wpshadow_diagnostic_result` | `array\|null` finding | `$finding` (array\|null), `$class` (string), `$slug` (string) | Suppress (`return null`), rewrite, or annotate a finding before it is stored or shown. |
| `wpshadow_pre_run_diagnostic` | `array\|null` precomputed result | `$pre_result` (array\|null), `$diagnostic_id` (string), `$class_name` (string\|null), `$force` (bool) | Short-circuit a single diagnostic run by returning a result array. |
| `wpshadow_diagnostic_run_result` | `array` result payload | `$result` (array), `$diagnostic_id` (string), `$class_name` (string), `$force` (bool) | Rewrite the final per-diagnostic run result. |
| `wpshadow_pre_run_scan` | `array\|null` precomputed result | `$pre_result` (array\|null), `$force_diagnostics` (bool) | Short-circuit a full scan by returning a result array. |
| `wpshadow_scan_result` | `array` scan result | `$result` (array), `$force_diagnostics` (bool) | Rewrite the final full-scan result payload. |

### Diagnostics — readiness gating

| Filter | Filtered value | Parameters | Use this to… |
|---|---|---|---|
| `wpshadow_include_beta_diagnostics` | `bool` (default `false`) | _(none)_ | Opt beta-readiness diagnostics into discovery. |
| `wpshadow_include_planned_diagnostics` | `bool` (default `false`) | _(none)_ | Opt planned-readiness diagnostics into discovery. |
| `wpshadow_allowed_diagnostic_readiness_states` | `array<int,string>` of states | `$allowed` (array) | Override which readiness states (`production`/`beta`/`planned`) diagnostics may run in. |
| `wpshadow_diagnostic_readiness_state` | `string` state | `$state` (string), `$class_name` (string), `$file_path` (string) | Override the computed readiness state of one diagnostic. |

### Treatments — resolution and gating

| Filter | Filtered value | Parameters | Use this to… |
|---|---|---|---|
| `wpshadow_treatment_class` | `string\|null` class | `$treatment_class` (string\|null), `$finding_id` (string, sanitized) | Map a finding to a custom treatment class, or override resolution. |
| `wpshadow_treatment_definitions` | `array` of treatment definitions | `$definitions` (array) | Add, remove, or reorder the executable treatments listed. |
| `wpshadow_treatment_enabled` | `bool` enabled flag | `$enabled` (bool), `$class`/`$class_name`/`$treatment_class` (string FQCN) | Force a treatment on or off regardless of the disabled-classes option. |
| `wpshadow_can_apply_treatment` | `bool` can-apply flag | `$can_apply` (bool), `$finding_id` (string), `$treatment_class` (string FQCN) | Block or allow whether a treatment may be applied right now. |
| `wpshadow_pre_attempt_autofix` | `array\|null` precomputed result | `$pre_result` (array\|null), `$finding_id` (string), `$dry_run` (bool) | Short-circuit a treatment attempt by returning a result array. |
| `wpshadow_attempt_autofix_result` | `array` result payload | `$result` (array), `$finding_id` (string), `$dry_run` (bool) | Rewrite the final top-level treatment result. |
| `wpshadow_treatment_result` | `array` apply result | `$result` (array), `$class` (string FQCN), `$finding_id` (string) | Rewrite the result of a treatment's `apply()`/dry-run flow. |
| `wpshadow_treatment_undo_result` | `array` undo result | `$result` (array), `$class` (string FQCN), `$finding_id` (string) | Rewrite the result of a treatment's `undo()` flow. |
| `wpshadow_treatment_metadata` | `array<string, array>` sparse override map (default `[]`) | _(none)_ | Override per-treatment metadata (e.g. `risk_level`) by finding slug; deep-merged. |

### Treatments — readiness gating

| Filter | Filtered value | Parameters | Use this to… |
|---|---|---|---|
| `wpshadow_allow_fallback_treatment` | `bool` (default `false`) | `$class_name` (string FQCN) | Expose a treatment that lacks both `apply()` and `undo()`. |
| `wpshadow_treatment_ready` | `bool` ready flag | `$default_ready` (bool), `$class_name` (string), `$state` (string), `$allowed` (array) | Override whether a treatment is exposed as executable. |
| `wpshadow_include_beta_treatments` | `bool` (default `false`) | _(none)_ | Opt beta-readiness treatments into the registry. |
| `wpshadow_include_planned_treatments` | `bool` (default `false`) | _(none)_ | Opt planned-readiness treatments into the registry. |
| `wpshadow_allowed_treatment_readiness_states` | `array<int,string>` of states | `$allowed` (array) | Override which readiness states treatments may run in. |
| `wpshadow_treatment_readiness_state` | `string` state | `$state` (string), `$class_name` (string) | Override the computed readiness state of one treatment. |

### Treatment runtime — login throttle and comment rate limiting

These filters tune the guardrails the active treatments install on the login
and comment forms. All four return integers (seconds or counts).

| Filter | Filtered value | Default | Use this to… |
|---|---|---|---|
| `wpshadow_login_throttle_window` | `int` seconds | `15 * MINUTE_IN_SECONDS` | Set the sliding window over which failed logins are counted. |
| `wpshadow_login_throttle_limit` | `int` attempts | `5` | Set how many failed logins trigger a lockout. |
| `wpshadow_login_lockout_duration` | `int` seconds | `HOUR_IN_SECONDS` | Set how long a locked-out IP stays locked. |
| `wpshadow_comment_rate_limit` | `int` comments | `3` | Set how many comments an IP may post per window. |
| `wpshadow_comment_rate_window` | `int` seconds | `5 * MINUTE_IN_SECONDS` | Set the sliding window for comment rate limiting. |

### Environment policy

| Filter | Filtered value | Parameters | Use this to… |
|---|---|---|---|
| `wpshadow_environment` | `string` environment id | `$environment` (string) | Override detected environment (`production`/`staging`/`development`/`local`) when heuristics are wrong. |
| `wpshadow_environment_policy` | `array` policy config | `$policy` (array), `$environment` (string) | Adjust the per-environment policy (`readiness_states`, `confidence_min`, `auto_fix`, `include_beta`, `include_planned`, `schedule`). |
| `wpshadow_core_pages_release_datetime` | `string` datetime | `'2026-04-30 23:59:59'` | Override the gate date controlling when the Findings/Guardian/Automations pages appear. |

### AJAX, rate limiting, and assets

| Filter | Filtered value | Parameters | Use this to… |
|---|---|---|---|
| `wpshadow_bypass_rate_limit_admin` | `bool` (default `true`) | _(none)_ | Decide whether `manage_options` users bypass AJAX rate limiting. |
| `wpshadow_rate_limits` | `array` limit config (`limit`, `window`) | `$limits` (array), `$type` (string) | Tune the request limit and window for an action type. |
| `wpshadow_deep_scan_batch_size` | `int` batch size | _(none)_ | Set how many diagnostics run per deep-scan batch (floored at 1). |
| `wpshadow_enqueue_frontend_assets` | `bool` (default `false`) | _(none)_ | Opt into front-end asset enqueueing. |

### Reporting and readiness inventory

| Filter | Filtered value | Parameters | Use this to… |
|---|---|---|---|
| `wpshadow_pre_readiness_inventory` | `array\|null` precomputed inventory | _(none)_ | Short-circuit inventory generation by returning an array. |
| `wpshadow_readiness_inventory` | `array` inventory payload | `$inventory` (array) | Rewrite the final readiness inventory. |
| `wpshadow_activity_entry` | `array` activity payload | `$activity` (array), `$action` (string), `$details` (string), `$category` (string), `$metadata` (array) | Rewrite or enrich a log entry before it is persisted. |

### UI and settings

| Filter | Filtered value | Parameters | Use this to… |
|---|---|---|---|
| `wpshadow_show_page_activities` | `bool` (default `false`) | `$context` (string), `$limit` (int), `$report_slug` (string) | Decide whether the per-page activity panel renders. |
| `wpshadow_setting_label` | `string` display label | `$label` (string), `$option` (string) | Override the human-friendly label shown for a setting. |

---

## Actions

### Diagnostics lifecycle

| Action | Parameters | Use this to… |
|---|---|---|
| `wpshadow_diagnostic_skipped_disabled` | `$class` (string), `$slug` (string) | React when a diagnostic is skipped because it is disabled. |
| `wpshadow_diagnostic_skipped_schedule` | `$class` (string), `$slug` (string) | React when a diagnostic is skipped because it is not yet due. |
| `wpshadow_before_diagnostic_check` | `$class` (string), `$slug` (string) | Run setup before a diagnostic's `check()`. |
| `wpshadow_after_diagnostic_check` | `$class` (string), `$slug` (string), `$finding` (array\|null) | React to a completed diagnostic and its finding. |
| `wpshadow_before_run_diagnostic` | `$diagnostic_id` (string), `$class_name` (string), `$force` (bool) | React before a top-level single-diagnostic run. |
| `wpshadow_after_run_diagnostic` | `$diagnostic_id` (string), `$class_name` (string), `$force` (bool), `$result` (array) | React after a top-level single-diagnostic run. |
| `wpshadow_before_run_scan` | `$force_diagnostics` (bool) | React before a full scan starts. |
| `wpshadow_after_run_scan` | `$result` (array), `$force_diagnostics` (bool) | React after a full scan completes. |
| `wpshadow_diagnostics_completed` | _(none)_ | React when the scan-frequency manager finishes a scan cycle. |
| `wpshadow_readiness_inventory_generated` | `$inventory` (array) | React when the readiness inventory has been built. |

### Treatments lifecycle

| Action | Parameters | Use this to… |
|---|---|---|
| `wpshadow_before_attempt_autofix` | `$finding_id` (string), `$dry_run` (bool) | React before a top-level treatment attempt begins. |
| `wpshadow_after_attempt_autofix` | `$finding_id` (string), `$dry_run` (bool), `$result` (array) | React after a top-level treatment attempt completes. |
| `wpshadow_before_treatment_apply` | `$class` (string), `$finding_id` (string), `$dry_run` (bool) | Run setup before a treatment's `apply()`. |
| `wpshadow_after_treatment_apply` | `$class` (string), `$finding_id` (string), `$result` (array) | React after a treatment is applied. |
| `wpshadow_before_treatment_undo` | `$class` (string), `$finding_id` (string) | Run setup before a treatment is undone. |
| `wpshadow_after_treatment_undo` | `$class` (string), `$finding_id` (string), `$result` (array) | React after a treatment is undone. |

### Reporting and KPIs

| Action | Parameters | Use this to… |
|---|---|---|
| `wpshadow_finding_detected` | `$finding_id` (string), `$severity` (string) | React when a finding is detected (KPI tracking). |
| `wpshadow_finding_resolved` | `$finding_id` (string), `$resolution_type` (string) | React when a finding is marked resolved (`fixed`/`ignored`/`delegated`). |
| `wpshadow_finding_status_changed` | `$finding_id` (string), `$status` (string), `$old_status` (string\|null) | React when a finding's status changes. |
| `wpshadow_treatment_kpi_recorded` | `$treatment_id` (string), `$time_saved_minutes` (int) | React when a treatment is recorded in KPI tracking. |
| `wpshadow_diagnostic_kpi_recorded` | `$diagnostic_id` (string), `$success` (bool) | React when a diagnostic run is recorded in KPI tracking. |
| `wpshadow_activity_logged` | `$activity` (array) | React after an activity entry is persisted. |
| `wpshadow_internal_error_logged` | `$message` (string), `$metadata` (array) | React when Shadow logs an internal error. |

### Environment policy and security

| Action | Parameters | Use this to… |
|---|---|---|
| `wpshadow_rate_limit_exceeded` | `$action` (string), `$user_id` (int), `$ip_address` (string), `$count` (int) | React when an AJAX rate limit is exceeded (alerting, logging). |

### Settings

| Action | Parameters | Use this to… |
|---|---|---|
| `wpshadow_setting_updated` | `$option` (string), `$old_value` (mixed), `$value` (mixed) | React to any Shadow setting change. |
| `wpshadow_setting_updated_{$option}` | `$old_value` (mixed), `$value` (mixed) | React to one specific Shadow setting change (dynamic hook name). |
| `wpshadow_setting_added` | `$option` (string), `$value` (mixed) | React when a Shadow setting is first added. |
| `wpshadow_setting_added_{$option}` | `$value` (mixed) | React when one specific Shadow setting is added (dynamic hook name). |

### Caching and queries

| Action | Parameters | Use this to… |
|---|---|---|
| `wpshadow_cache_hit_object` | `$key` (string), `$group` (string) | React to an object-cache hit. |
| `wpshadow_cache_hit_transient` | `$key` (string) | React to a transient-cache hit. |
| `wpshadow_cache_set` | `$key` (string), `$value` (mixed), `$expire` (int), `$group` (string) | React when a cache value is written. |
| `wpshadow_cache_delete` | `$key` (string), `$group` (string) | React when a cache value is deleted. |
| `wpshadow_cache_flushed` | _(none)_ | React when all Shadow cache is flushed. |
| `wpshadow_dashboard_cache_invalidated` | _(none)_ | React when the dashboard caches are invalidated. |
| `wpshadow_query_executed` | `$key` (string), `$result` (mixed), `$query` (string) | React after a batched query executes. |

### Bootstrap and lifecycle

| Action | Parameters | Use this to… |
|---|---|---|
| `wpshadow_autoloader_complete` | _(none)_ | React once the bootstrap autoloader has loaded all classes. |
| `wpshadow_core_initialized` | _(none)_ | React once core initialization is complete. |
| `wpshadow_core_loaded` | _(none)_ | Register external add-ons after the hooks initializer has loaded core. |
| `wpshadow_load_pro_features` | _(none)_ | Hook point for a separate pro add-on plugin to load. |
| `wpshadow_after_page_header` | _(none)_ | Render UI immediately after an admin page header. |

---

## Extending Shadow

These examples use hooks verified against this codebase. Each shows the correct
callback signature — match the argument count you accept to the parameters the
hook passes.

### 1. Suppress a specific diagnostic finding

Stop a finding from being stored or displayed by returning `null` from the
result filter. Here we drop one diagnostic's finding while leaving every other
diagnostic untouched.

```php
add_filter(
	'wpshadow_diagnostic_result',
	function ( $finding, $class, $slug ) {
		// $finding is an array when there is an issue, null when the check passed.
		if ( 'debug-mode-enabled' === $slug ) {
			return null; // Treat this finding as resolved everywhere in the UI.
		}

		return $finding;
	},
	10,
	3
);
```

### 2. Gate whether a treatment may be applied

`wpshadow_can_apply_treatment` decides, per request, whether a fix can
run. Pair it with your own capability or environment check — for example, only
allow fixes on staging.

```php
add_filter(
	'wpshadow_can_apply_treatment',
	function ( $can_apply, $finding_id, $treatment_class ) {
		// Never apply Shadow's automated fixes in production.
		if ( 'production' === wp_get_environment_type() ) {
			return false;
		}

		return $can_apply;
	},
	10,
	3
);
```

### 3. Adjust environment-policy behaviour

Override the policy for the active environment — here we turn off automated
fixes on staging while keeping beta diagnostics visible. The policy array keys
are documented above under *Environment policy*.

```php
add_filter(
	'wpshadow_environment_policy',
	function ( $policy, $environment ) {
		if ( 'staging' === $environment ) {
			$policy['auto_fix'] = false;
		}

		return $policy;
	},
	10,
	2
);
```

### Bonus: tighten the login throttle

A one-argument filter is enough when the hook passes a single value. Here we cut
the failed-login allowance from five attempts to three.

```php
add_filter(
	'wpshadow_login_throttle_limit',
	function () {
		return 3;
	}
);
```

---

## Related documents

- [CORE_PHILOSOPHY.md](CORE_PHILOSOPHY.md) — how the diagnostic/treatment pipeline thinks
- [DIAGNOSTICS_REGISTRY.md](DIAGNOSTICS_REGISTRY.md) — the shipped diagnostics these hooks fire around
- [ENVIRONMENT_POLICIES.md](ENVIRONMENT_POLICIES.md) — the per-environment policy these filters adjust
