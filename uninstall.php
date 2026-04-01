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
 * - Commandment #10 (Beyond Pure) - Privacy first, explicit consent
 * - Commandment #1 (Helpful Neighbor) - Respectful data handling
 *
 * @package WPShadow
 * @since 0.6093.1200
 */

// If uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Store uninstall flag for exit interview
 *
 * Since we can't show a modal during uninstall (it's a background process),
 * we'll store data for a potential exit interview redirect.
 * The interview will be shown via a redirect URL if user opts in.
 */
update_option( 'wpshadow_uninstall_timestamp', time() );

/**
 * Clean up plugin data based on settings
 *
 * Check if user wants to keep data (for potential reinstall)
 */
$keep_data = get_option( 'wpshadow_keep_data_on_uninstall', false );

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

	// Clear scheduled events
	$cron_hooks = array(
		'wpshadow_run_overnight_fixes',
		'wpshadow_run_automated_fixes',
		'wpshadow_run_data_cleanup',
		'wpshadow_run_automatic_diagnostic_scan',
		'wpshadow_send_scheduled_reports',
		'wpshadow_run_offpeak_operations',
	);

	foreach ( $cron_hooks as $hook ) {
		$timestamp = wp_next_scheduled( $hook );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, $hook );
		}
	}

	// Clear transients
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			$transient_like,
			$transient_timeout_like
		)
	);
}

/**
 * Log uninstall event for analytics (if consent was given)
 *
 * This only sends data if user previously consented to analytics.
 */
$analytics_consent = get_option( 'wpshadow_consent_analytics', false );

if ( $analytics_consent ) {
	global $wpdb;

	// Prepare minimal diagnostic data
	$diagnostic_data = array(
		'plugin_version' => get_option( 'wpshadow_version', 'unknown' ),
		'wp_version'     => get_bloginfo( 'version' ),
		'php_version'    => PHP_VERSION,
		'mysql_version'  => $wpdb->db_version(),
		'site_language'  => get_locale(),
		'multisite'      => is_multisite(),
		'active_plugins' => count( get_option( 'active_plugins', array() ) ),
		'uninstall_date' => current_time( 'mysql' ),
	);

	// Only send if network is available, consent is explicit, and endpoint is configured
	// This is a fire-and-forget request, won't block uninstall
	$analytics_endpoint = apply_filters( 'wpshadow_analytics_endpoint', '' );

	if ( ! empty( $analytics_endpoint ) && function_exists( 'wp_remote_post' ) ) {
		wp_remote_post(
			$analytics_endpoint,
			array(
				'timeout'  => 5,
				'blocking' => false, // Non-blocking
				'body'     => wp_json_encode( $diagnostic_data ),
				'headers'  => array(
					'Content-Type' => 'application/json',
				),
			)
		);
	}
}
