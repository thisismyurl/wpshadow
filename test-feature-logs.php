<?php
/**
 * Test script to generate sample feature logs.
 * 
 * Run this from WordPress admin to populate the feature log widget with sample data.
 * Visit: /wp-admin/admin.php?page=wpshadow-test-logs (after uncommenting the admin page registration)
 */

// Uncomment to register test page:
/*
add_action( 'admin_menu', function() {
	add_submenu_page(
		null, // Hidden page
		'Test Feature Logs',
		'Test Feature Logs',
		'manage_options',
		'wpshadow-test-logs',
		'wpshadow_test_logs_page'
	);
});
*/

function wpshadow_test_logs_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Insufficient permissions' );
	}
	
	if ( isset( $_GET['generate'] ) ) {
		wpshadow_generate_sample_logs();
		echo '<div class="notice notice-success"><p>Sample logs generated!</p></div>';
	}
	
	?>
	<div class="wrap">
		<h1>Test Feature Logs</h1>
		<p>This will generate sample log entries for the External Fonts Disabler feature.</p>
		<p><a href="?page=wpshadow-test-logs&generate=1" class="button button-primary">Generate Sample Logs</a></p>
		<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow&wpshadow_tab=features&feature=external-fonts-disabler' ) ); ?>" class="button">View Feature Page</a></p>
	</div>
	<?php
}

function wpshadow_generate_sample_logs() {
	require_once dirname( __FILE__ ) . '/includes/views/dashboard-renderer.php';
	
	$feature_id = 'external-fonts-disabler';
	$current_time = current_time( 'timestamp' );
	
	// Generate a series of realistic log entries
	$sample_logs = array(
		array(
			'action' => 'enabled',
			'message' => '',
			'offset' => 0, // Now
		),
		array(
			'action' => 'settings_updated',
			'message' => 'Advanced settings updated',
			'offset' => 300, // 5 minutes ago
		),
		array(
			'action' => 'sub_feature_enabled',
			'message' => 'Buffer cleanup activated',
			'offset' => 600, // 10 minutes ago
		),
		array(
			'action' => 'settings_updated',
			'message' => 'Whitelist modified',
			'offset' => 1800, // 30 minutes ago
		),
		array(
			'action' => 'disabled',
			'message' => '',
			'offset' => 3600, // 1 hour ago
		),
		array(
			'action' => 'enabled',
			'message' => '',
			'offset' => 7200, // 2 hours ago
		),
		array(
			'action' => 'sub_feature_disabled',
			'message' => 'Adobe Fonts blocking disabled',
			'offset' => 14400, // 4 hours ago
		),
		array(
			'action' => 'settings_updated',
			'message' => 'System font fallback configured',
			'offset' => 21600, // 6 hours ago
		),
		array(
			'action' => 'enabled',
			'message' => '',
			'offset' => 86400, // 1 day ago
		),
		array(
			'action' => 'disabled',
			'message' => '',
			'offset' => 172800, // 2 days ago
		),
		array(
			'action' => 'error',
			'message' => 'Failed to load whitelist configuration',
			'offset' => 259200, // 3 days ago
		),
		array(
			'action' => 'enabled',
			'message' => '',
			'offset' => 345600, // 4 days ago
		),
		array(
			'action' => 'action_performed',
			'message' => 'Feature initialized',
			'offset' => 432000, // 5 days ago
		),
	);
	
	foreach ( $sample_logs as $log_data ) {
		\WPShadow\CoreSupport\wpshadow_log_feature_activity(
			$feature_id,
			$log_data['action'],
			$log_data['message']
		);
		
		// Manually adjust timestamp to create a history
		$all_logs = get_option( 'wpshadow_feature_logs', array() );
		if ( ! empty( $all_logs[ $feature_id ] ) ) {
			$last_index = count( $all_logs[ $feature_id ] ) - 1;
			$all_logs[ $feature_id ][ $last_index ]['timestamp'] = $current_time - $log_data['offset'];
			update_option( 'wpshadow_feature_logs', $all_logs );
		}
	}
}
