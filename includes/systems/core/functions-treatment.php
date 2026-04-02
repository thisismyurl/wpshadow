<?php
/**
 * Treatment Helper Functions
 *
 * Global convenience functions for treatment operations used throughout the plugin.
 * These wrap Treatment_Registry and Treatment_Base functionality in simpler,
 * more accessible functions.
 *
 * **When to Use These Functions:**
 * - `wpshadow_attempt_autofix()` - Manually trigger a specific treatment
 * - `wpshadow_get_treatment()` - Load a treatment instance for inspection
 * - `wpshadow_is_treatment_enabled()` - Check if user has disabled this fix
 * - `wpshadow_can_apply_treatment()` - Permission check before UI display
 *
 * **Real-World Usage:**
 * ```php
 * // In dashboard: offer fix button only if treatment available
 * if ( wpshadow_can_apply_treatment( 'database-cleanup' ) ) {
 *     // Show "Fix Now" button
 * }
 *
 * // When user clicks button: apply the fix
 * $result = wpshadow_attempt_autofix( 'database-cleanup', $dry_run = true );
 * // Show results to user...
 * ```
 *
 * **Philosophy Alignment:**
 * - #1 (Helpful Neighbor): Simple functions hide complexity
 * - #7 (Ridiculously Good): Low barrier to use treatments in custom code
 *
 * @package WPShadow
}


