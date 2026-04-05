<?php
/**
 * WPShadow Uninstall Handler
 *
 * Handles plugin uninstall including:
 * - More detailed exit interview
 * - Optional diagnostic data submission
 * - Contact permission for follow-up
 * - Clean up plugin data
 *
 * Philosophy:
 * - Commandment #10 (Beyond Pure) - Privacy first
 * - Commandment #1 (Helpful Neighbor) - Respectful data handling
 *
 * @package WPShadow
 * @since 0.6095
 */

// If uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Clean up plugin data based on settings
 *
 * Check if user wants to keep data (for potential reinstall)
 */
$keep_data = get_option( 'wpshadow_keep_data_on_uninstall', false );

// Always clear scheduled events during uninstall, even when site data is kept.
$cron_hooks = array(
	'wpshadow_run_automated_fixes',
	'wpshadow_run_data_cleanup',
	'wpshadow_send_scheduled_reports',
	'wpshadow_run_automatic_diagnostic_scan',
	'wpshadow_run_scheduled_backup',
	'wpshadow_scheduled_deep_scan',
	'wpshadow_run_offpeak_operations',
	'wpshadow_run_overnight_fixes',
	'wpshadow_hourly_cleanup',
);

foreach ( $cron_hooks as $hook ) {
	wp_clear_scheduled_hook( $hook );
}

if ( ! $keep_data ) {
	global $wpdb;
	$option_like = $wpdb->esc_like( 'wpshadow_' ) . '%';
	$transient_like = $wpdb->esc_like( '_transient_wpshadow_' ) . '%';
	$transient_timeout_like = $wpdb->esc_like( '_transient_timeout_wpshadow_' ) . '%';

	// Remove plugin options
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $option_like ) );

	// Remove user meta
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s", $option_like ) );

	// No custom WPShadow tables are maintained.

	// Clear transients
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			$transient_like,
			$transient_timeout_like
		)
	);
}

// Uninstall analytics submission removed.
