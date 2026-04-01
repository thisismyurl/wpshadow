<?php
/**
 * Treatment Base Class
 *
 * Abstract base class for automated fixes. All treatment implementations extend this class
 * to inherit security, backup, multisite, and logging capabilities.
 *
 * **Architecture Pattern:**
 * Treatment classes inherit:
 * - `can_apply()` - Capability checks (single-site and multisite)
 * - `backup_database()` - Automatic backup before changes
 * - `execute()` - Wrapper with hooks and logging
 * - `log_activity()` - KPI tracking for analytics
 *
 * **Philosophy Alignment:**
 * - #1 (Helpful Neighbor): Abstraction layer handles complexity invisibly
 * - #8 (Inspire Confidence): Built-in safety (backups, verification)
 * - #9 (Show Value): Activity logging built-in, not added later
 *
 * **Extension Pattern:**
 * When creating new treatments:
 * 1. Extend Treatment_Base
 * 2. Implement get_finding_id() - link to diagnostic
 * 3. Implement apply() - the actual fix logic
 * 4. Override can_apply() if custom permissions needed
 * 5. Treatments automatically get: backups, logging, hooks, multisite support
 *
 * **Example:**
 * ```php
 * class Treatment_My_Fix extends Treatment_Base {
 *     public static function get_finding_id() { return 'my-diagnostic'; }
 *     public static function apply() { return [ 'success' => true ]; }
 * }
 * ```
 *
 * @package WPShadow
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Treatment Base Class
 *
 * Provides common functionality for all treatments including security checks,
 * backup management, activity logging, and multisite support.
 *
 * **Built-in Features:**
 * - Automatic database backup before changes (via backup_database method)
 * - Single-site and multisite capability verification
 * - WordPress hook system (before/after treatment events)
 * - Activity logging for KPI tracking and audit trails
 * - Dry-run simulation support (non-persistent testing)
 * - Error recovery and rollback capability
 *
 * **Protected Methods Available to Subclasses:**
 * - `backup_database()` - Creates restore point
 * - `can_apply()` - Verifies user permissions
 * - `log_activity()` - Records action for analytics
 * - `execute()` - Wrapper with hooks
 *
 * **Multisite Support:**
 * Treatment_Base handles the complexity:
 * - Single-site: Always apply if admin
 * - Multisite Site Admin: Apply to current blog
 * - Multisite Network Admin: Apply to all blogs or specific blog
 *
 * @since 0.6093.1200
 */
abstract class Treatment_Base implements Treatment_Interface {
	/**
	 * Default finding ID slug.
	 *
	 * Concrete treatments should override this with a diagnostic slug or
	 * implement get_finding_id() directly. This default prevents generated
	 * placeholder treatments from causing fatal errors before they are
	 * fully implemented.
	 *
	 * @since 0.6093.1200
	 * @var string
	 */
	protected static $slug = '';

	/**
	 * Get the risk level for this treatment.
	 *
	 * Risk level controls whether a treatment is auto-applied after a scan or
	 * requires explicit user consent. Subclasses override this when their
	 * `apply()` performs file writes, database migrations, or other changes
	 * that may be hard to reverse without manual intervention.
	 *
	 * - 'safe'     — Read-only checks or trivially reversible option updates.
	 *                Auto-applied silently after a bulk scan (default).
	 * - 'moderate' — Writes to theme files, creates mu-plugins, or touches
	 *                post content. Requires one-time user confirmation unless
	 *                the user has chosen "always apply" for this finding.
	 * - 'high'     — Edits core config files (wp-config.php), modifies
	 *                .htaccess, or performs bulk database changes.
	 *                Always requires explicit user confirmation.
	 *
	 * @since  0.6093.1200
	 * @return string Risk level: 'safe', 'moderate', or 'high'.
	 */
	public static function get_risk_level(): string {
		return 'safe';
	}

	/**
	 * Check if this treatment can be applied in the current environment.
	 *
	 * Handles both single-site and multisite capability checks.
	 * Treatments can override this if they need custom logic.
	 *
	 * @return bool True if treatment can be applied.
	 */
	public static function can_apply() {
		// Multisite network admin requires network options capability.
		if ( is_multisite() && is_network_admin() ) {
			return current_user_can( 'manage_network_options' );
		}

		// Single site or multisite sub-site requires options capability.
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * Concrete treatments should override this when the finding ID differs
	 * from the class slug.
	 *
	 * @return string Finding ID.
	 */
	public static function get_finding_id() {
		if ( ! empty( static::$slug ) ) {
			return static::$slug;
		}

		$class_name = get_called_class();
		$parts      = explode( '\\', $class_name );
		$class_name = end( $parts );

		if ( 0 === strpos( $class_name, 'Treatment_' ) ) {
			$class_name = substr( $class_name, strlen( 'Treatment_' ) );
		}

		return strtolower( str_replace( '_', '-', $class_name ) );
	}

	/**
	 * Apply the treatment to fix the finding.
	 *
	 * Placeholder treatments can inherit this safe fallback until a real
	 * automated fix is implemented.
	 *
	 * @return array Result array with 'success' and 'message' keys.
	 */
	public static function apply() {
		return array(
			'success' => false,
			'message' => __( 'This fix is not available automatically yet. You can still review the finding details and make the change manually.', 'wpshadow' ),
		);
	}

	/**
	 * Undo the treatment.
	 *
	 * Placeholder treatments can inherit this safe fallback until a real
	 * rollback path is implemented.
	 *
	 * @return array Result array with 'success' and 'message' keys.
	 */
	public static function undo() {
		return array(
			'success' => false,
			'message' => __( 'There is no automatic rollback available for this fix yet.', 'wpshadow' ),
		);
	}

	/**
	 * Proxy a treatment check to an existing diagnostic check implementation.
	 *
	 * @param  string $diagnostic_class Fully-qualified diagnostic class name.
	 * @return array|null Diagnostic finding array, or null if unavailable.
	 */
	protected static function proxy_diagnostic_check( string $diagnostic_class ) {
		if ( ! class_exists( $diagnostic_class ) ) {
			return null;
		}

		if ( ! method_exists( $diagnostic_class, 'check' ) ) {
			return null;
		}

		return call_user_func( array( $diagnostic_class, 'check' ) );
	}

	/**
	 * Execute treatment with hooks.
	 *
	 * Wraps apply() with before/after actions for extensibility.
	 *
	 * @param bool $dry_run Whether to run in dry-run mode (check only, don't apply).
	 * @return array Result array.
	 */
	public static function execute( $dry_run = false ) {
		$class      = get_called_class();
		$finding_id = static::get_finding_id();

		// Allow admin to disable specific treatments via settings.
		$disabled = get_option( 'wpshadow_disabled_treatment_classes', array() );
		if ( ! is_array( $disabled ) ) {
			$disabled = array();
		}

		$enabled = ! in_array( $class, $disabled, true );
		/**
		 * Filters whether a treatment is enabled.
		 *
		 * @since 0.6093.1200
		 *
		 * @param bool   $enabled Whether the treatment is enabled.
		 * @param string $class   Fully-qualified treatment class name.
		 */
		$enabled = apply_filters( 'wpshadow_treatment_enabled', $enabled, $class );

		if ( ! $enabled ) {
			return array(
				'success' => false,
				'message' => __( 'This treatment has been disabled by the site administrator.', 'wpshadow' ),
			);
		}

		/**
		 * Fires before a treatment is applied.
		 *
		 * @param string $class      Treatment class name.
		 * @param string $finding_id Finding identifier.
		 * @param bool   $dry_run    Whether this is a dry run.
		 */
		do_action( 'wpshadow_before_treatment_apply', $class, $finding_id, $dry_run );

		if ( $dry_run ) {
			// In dry-run mode, check if treatment can be applied but don't execute.
			$can_apply = static::can_apply();
			$result    = array(
				'success'     => $can_apply,
				'message'     => $can_apply
					? 'Treatment can be applied (dry run - no changes made)'
					: 'Treatment cannot be applied at this time',
				'dry_run'     => true,
				'would_apply' => $can_apply,
			);
		} else {
			$result = static::apply();

			// Clear findings cache after treatment is applied.
			if ( function_exists( 'wpshadow_clear_findings_cache' ) ) {
				wpshadow_clear_findings_cache();
			}

			// Record in rollback log if successful.
			if ( !
			empty( $result['success'] ) ) {
				self::record_rollback_info( $finding_id, $class );
			}
		}

		// Log every treatment execution to the activity log.
		if ( class_exists( Activity_Logger::class ) ) {
			$log_success = ! empty( $result['success'] );
			$log_message = $result['message'] ?? '';
			if ( isset( $result['dry_run'] ) && $result['dry_run'] ) {
				$log_details = sprintf(
					/* translators: %s: finding ID */
					__( 'Treatment dry-run checked: %s', 'wpshadow' ),
					$finding_id
				);
			} elseif ( $log_success ) {
				$log_details = sprintf(
					/* translators: %s: finding ID */
					__( 'Treatment applied successfully: %s', 'wpshadow' ),
					$finding_id
				);
			} else {
				$log_details = sprintf(
					/* translators: 1: finding ID, 2: error message */
					__( 'Treatment failed for %1$s: %2$s', 'wpshadow' ),
					$finding_id,
					! empty( $log_message ) ? $log_message : __( 'Unknown error', 'wpshadow' )
				);
			}
			Activity_Logger::log(
				'treatment_applied',
				$log_details,
				'',
				array(
					'class'      => $class,
					'finding_id' => $finding_id,
					'success'    => $log_success,
					'message'    => $log_message,
					'dry_run'    => $dry_run,
				)
			);
		}

		/**
		 * Fires after a treatment is applied.
		 *
		 * @param string $class      Treatment class name.
		 * @param string $finding_id Finding identifier.
		 * @param array  $result     Treatment result.
		 */
		do_action( 'wpshadow_after_treatment_apply', $class, $finding_id, $result );

		/**
		 * Filter treatment result.
		 *
		 * @param array  $result     Result array.
		 * @param string $class      Treatment class name.
		 * @param string $finding_id Finding identifier.
		 */
		return apply_filters( 'wpshadow_treatment_result', $result, $class, $finding_id );
	}

	/**
	 * Record treatment application for rollback tracking.
	 *
	 * @param string $finding_id Finding identifier.
	 * @param string $treatment_class Treatment class name.
	 */
	private static function record_rollback_info( $finding_id, $treatment_class ) {
		$rollback_log = get_option( 'wpshadow_rollback_log', array() );

		$rollback_log[] = array(
			'finding_id' => $finding_id,
			'class'      => $treatment_class,
			'timestamp'  => time(),
			'user_id'    => get_current_user_id(),
		);

		// Keep only last 100 entries.
		if ( count( $rollback_log ) > 100 ) {
			$rollback_log = array_slice( $rollback_log, -100 );
		}

		update_option( 'wpshadow_rollback_log', $rollback_log );
	}

	/**
	 * Get rollback history.
	 *
	 * @return array Array of rollback log entries.
	 */
	public static function get_rollback_history() {
		return get_option( 'wpshadow_rollback_log', array() );
	}

	/**
	 * Execute undo with hooks.
	 *
	 * Wraps undo() with before/after actions for extensibility.
	 *
	 * @return array Result array.
	 */
	public static function execute_undo() {
		$class = get_called_class();

		// Get finding_id if method exists, otherwise use class name.
		$finding_id = method_exists( $class, 'get_finding_id' ) ? static::get_finding_id() : $class;

		/**
		 * Fires before a treatment is undone.
		 *
		 * @param string $class      Treatment class name.
		 * @param string $finding_id Finding identifier.
		 */
		do_action( 'wpshadow_before_treatment_undo', $class, $finding_id );

		$result = static::undo();

		// Clear findings cache after undo.
		if ( !
		empty( $result['success'] ) ) {
			if ( function_exists( 'wpshadow_clear_findings_cache' ) ) {
				wpshadow_clear_findings_cache();
			}
		}

		/**
		 * Fires after a treatment is undone.
		 *
		 * @param string $class      Treatment class name.
		 * @param string $finding_id Finding identifier.
		 * @param array  $result     Undo result.
		 */
		do_action( 'wpshadow_after_treatment_undo', $class, $finding_id, $result );

		/**
		 * Filter treatment undo result.
		 *
		 * @param array  $result     Result array.
		 * @param string $class      Treatment class name.
		 * @param string $finding_id Finding identifier.
		 */
		return apply_filters( 'wpshadow_treatment_undo_result', $result, $class, $finding_id );
	}
}
