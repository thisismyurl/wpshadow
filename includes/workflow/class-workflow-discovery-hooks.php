<?php
/**
 * Workflow Discovery Hooks - External integration points
 *
 * Provides hooks for external systems (like file sync bots) to notify
 * the Workflow Builder when new diagnostics or treatments are added.
 *
 * @package WPShadow
 * @subpackage Workflow
 */

namespace WPShadow\Workflow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Workflow Discovery Hooks class
 */
class Workflow_Discovery_Hooks {

	/**
	 * Register hooks for discovery refresh
	 */
	public static function init(): void {
		// Hook for external systems to trigger cache refresh
		add_action( 'wpshadow_refresh_workflow_discovery', array( __CLASS__, 'handle_refresh' ) );

		// Auto-refresh discovery cache periodically
		add_action( 'wpshadow_hourly_check', array( __CLASS__, 'auto_refresh_if_needed' ) );
	}

	/**
	 * Handle discovery refresh request
	 *
	 * Can be called from external systems when files are updated:
	 *
	 *   do_action( 'wpshadow_refresh_workflow_discovery' );
	 */
	public static function handle_refresh(): void {
		Workflow_Discovery::clear_cache();

		// Log the refresh for debugging
		error_log( '[WPShadow] Workflow discovery cache refreshed at ' . current_time( 'mysql' ) );

		// Trigger hook for Pro modules or other systems to respond
		do_action( 'wpshadow_discovery_refreshed', array(
			'diagnostics' => Workflow_Discovery::discover_diagnostics(),
			'treatments'  => Workflow_Discovery::discover_treatments(),
		) );
	}

	/**
	 * Auto-refresh discovery cache periodically
	 *
	 * Checks if new files have been added since last check
	 */
	public static function auto_refresh_if_needed(): void {
		$last_check = get_transient( 'wpshadow_discovery_last_check' );
		$current_check = filemtime( WP_PLUGIN_DIR . '/wpshadow/includes/diagnostics/' );

		if ( ! $last_check || $current_check > $last_check ) {
			self::handle_refresh();
			set_transient( 'wpshadow_discovery_last_check', time(), HOUR_IN_SECONDS );
		}
	}

	/**
	 * Get count of discovered items
	 *
	 * Useful for debugging and status pages
	 *
	 * @return array Counts of discovered items
	 */
	public static function get_discovery_status(): array {
		return array(
			'diagnostics_count' => count( Workflow_Discovery::discover_diagnostics() ),
			'treatments_count'  => count( Workflow_Discovery::discover_treatments() ),
			'last_refreshed'    => get_transient( 'wpshadow_discovery_last_check' ),
		);
	}
}

// Initialize hooks if we're not in CLI or if explicitly enabled
if ( ! defined( 'WP_CLI' ) || apply_filters( 'wpshadow_discovery_hooks_in_cli', false ) ) {
	Workflow_Discovery_Hooks::init();
}
