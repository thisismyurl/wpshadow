<?php
/**
 * Direct script to add sample logs immediately.
 * Run: php -r "define('WP_USE_THEMES', false); require('./wp-load.php'); require('./wp-content/plugins/wpshadow/add-sample-logs-now.php');"
 */

// Check if running in WordPress context
if (!function_exists('get_option')) {
    die("This script must be run in WordPress context.\n");
}

echo "Adding sample feature logs...\n";

$feature_id = 'external-fonts-disabler';
$current_time = current_time('timestamp');

// Sample log entries
$sample_logs = array(
    array('action' => 'enabled', 'message' => '', 'offset' => 0),
    array('action' => 'settings_updated', 'message' => 'Advanced settings updated', 'offset' => 300),
    array('action' => 'sub_feature_enabled', 'message' => 'Buffer cleanup activated', 'offset' => 600),
    array('action' => 'settings_updated', 'message' => 'Whitelist modified', 'offset' => 1800),
    array('action' => 'disabled', 'message' => '', 'offset' => 3600),
    array('action' => 'enabled', 'message' => '', 'offset' => 7200),
    array('action' => 'sub_feature_disabled', 'message' => 'Adobe Fonts blocking disabled', 'offset' => 14400),
    array('action' => 'settings_updated', 'message' => 'System font fallback configured', 'offset' => 21600),
    array('action' => 'enabled', 'message' => '', 'offset' => 86400),
    array('action' => 'disabled', 'message' => '', 'offset' => 172800),
    array('action' => 'error', 'message' => 'Failed to load whitelist configuration', 'offset' => 259200),
    array('action' => 'enabled', 'message' => '', 'offset' => 345600),
    array('action' => 'action_performed', 'message' => 'Feature initialized', 'offset' => 432000),
);

// Get existing logs
$all_logs = get_option('wpshadow_feature_logs', array());
if (!isset($all_logs[$feature_id])) {
    $all_logs[$feature_id] = array();
}

// Get current user info
$current_user = wp_get_current_user();
$username = $current_user->user_login ?: 'admin';
$user_id = $current_user->ID ?: 1;

// Add sample entries
foreach ($sample_logs as $log_data) {
    $log_entry = array(
        'timestamp' => $current_time - $log_data['offset'],
        'action'    => $log_data['action'],
        'message'   => $log_data['message'],
        'user'      => $username,
        'user_id'   => $user_id,
        'metadata'  => array(),
    );
    
    $all_logs[$feature_id][] = $log_entry;
}

// Sort by timestamp descending
usort($all_logs[$feature_id], function($a, $b) {
    return $b['timestamp'] - $a['timestamp'];
});

// Keep only last 100 entries
$all_logs[$feature_id] = array_slice($all_logs[$feature_id], 0, 100);

// Save to database
$result = update_option('wpshadow_feature_logs', $all_logs);

if ($result) {
    echo "✓ Successfully added " . count($sample_logs) . " sample log entries!\n";
    echo "\nView them at:\n";
    echo "  → /wp-admin/admin.php?page=wpshadow&wpshadow_tab=features&feature=external-fonts-disabler\n";
} else {
    echo "✗ Failed to save logs to database.\n";
}
