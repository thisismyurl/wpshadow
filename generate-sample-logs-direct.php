<?php
/**
 * Direct script to generate sample feature logs.
 * 
 * Include this file in WordPress to generate sample logs.
 * Example: Add to your theme's functions.php temporarily:
 *   include_once( ABSPATH . '../wpshadow/generate-sample-logs-direct.php' );
 */

// Only run once
if ( defined( 'WPSHADOW_TEST_LOGS_GENERATED' ) ) {
	return;
}
define( 'WPSHADOW_TEST_LOGS_GENERATED', true );

// Generate sample logs on admin_init
add_action( 'admin_init', function() {
	// Check if we've already generated logs (to avoid duplicates)
	$generated = get_transient( 'wpshadow_sample_logs_generated' );
	if ( $generated ) {
		return;
	}
	
	// Mark as generated (expires in 1 hour in case you want to regenerate)
	set_transient( 'wpshadow_sample_logs_generated', true, HOUR_IN_SECONDS );
	
	// Load the logging functions
	if ( ! function_exists( '\WPShadow\CoreSupport\wpshadow_log_feature_activity' ) ) {
		return;
	}
	
	$feature_id = 'external-fonts-disabler';
	$current_time = current_time( 'timestamp' );
	
	// Generate a series of realistic log entries
	$sample_logs = array(
		array( 'action' => 'enabled', 'message' => '', 'offset' => 0 ),
		array( 'action' => 'settings_updated', 'message' => 'Advanced settings updated', 'offset' => 300 ),
		array( 'action' => 'sub_feature_enabled', 'message' => 'Buffer cleanup activated', 'offset' => 600 ),
		array( 'action' => 'settings_updated', 'message' => 'Whitelist modified', 'offset' => 1800 ),
		array( 'action' => 'disabled', 'message' => '', 'offset' => 3600 ),
		array( 'action' => 'enabled', 'message' => '', 'offset' => 7200 ),
		array( 'action' => 'sub_feature_disabled', 'message' => 'Adobe Fonts blocking disabled', 'offset' => 14400 ),
		array( 'action' => 'settings_updated', 'message' => 'System font fallback configured', 'offset' => 21600 ),
		array( 'action' => 'enabled', 'message' => '', 'offset' => 86400 ),
		array( 'action' => 'disabled', 'message' => '', 'offset' => 172800 ),
		array( 'action' => 'error', 'message' => 'Failed to load whitelist configuration', 'offset' => 259200 ),
		array( 'action' => 'enabled', 'message' => '', 'offset' => 345600 ),
		array( 'action' => 'action_performed', 'message' => 'Feature initialized', 'offset' => 432000 ),
	);
	
	// Create logs with backdated timestamps
	$all_logs = get_option( 'wpshadow_feature_logs', array() );
	if ( ! isset( $all_logs[ $feature_id ] ) ) {
		$all_logs[ $feature_id ] = array();
	}
	
	$current_user = wp_get_current_user();
	
	foreach ( $sample_logs as $log_data ) {
		$log_entry = array(
			'timestamp' => $current_time - $log_data['offset'],
			'action'    => $log_data['action'],
			'message'   => $log_data['message'],
			'user'      => $current_user->user_login ?? 'admin',
			'user_id'   => $current_user->ID ?? 1,
			'metadata'  => array(),
		);
		
		$all_logs[ $feature_id ][] = $log_entry;
	}
	
	// Sort by timestamp descending
	usort( $all_logs[ $feature_id ], function( $a, $b ) {
		return $b['timestamp'] - $a['timestamp'];
	});
	
	// Keep only last 100 entries
	$all_logs[ $feature_id ] = array_slice( $all_logs[ $feature_id ], 0, 100 );
	
	update_option( 'wpshadow_feature_logs', $all_logs );
	
	// Show admin notice
	add_action( 'admin_notices', function() {
		?>
		<div class="notice notice-success is-dismissible">
			<p><strong>WPShadow:</strong> Sample feature logs generated! <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow&wpshadow_tab=features&feature=external-fonts-disabler' ) ); ?>">View Feature Log →</a></p>
		</div>
		<?php
	});
}, 99 );
