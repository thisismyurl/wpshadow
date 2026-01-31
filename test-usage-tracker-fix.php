<?php
/**
 * Test script to validate Usage_Tracker fix
 *
 * This validates that the track_activity method signature matches the hook it's registered to.
 */

// Load WordPress and WPShadow
require_once '/home/sailmar1/public_html/wpshadow/wp-load.php';

// Import the class
use WPShadow\Analytics\Usage_Tracker;
use WPShadow\Core\Activity_Logger;

// Check the method signature using reflection
$reflection = new ReflectionMethod( Usage_Tracker::class, 'track_activity' );
$params = $reflection->getParameters();

echo "=== Usage_Tracker::track_activity() Validation ===\n\n";
echo "Parameter Count: " . count( $params ) . "\n";

foreach ( $params as $param ) {
	echo "  - \$" . $param->getName();
	if ( $param->hasType() ) {
		echo " (" . $param->getType() . ")";
	}
	echo "\n";
}

echo "\n=== Activity_Logger Activity Structure ===\n\n";

// Create a test activity to see the structure
$test_activity = array(
	'id'        => uniqid( 'activity_', true ),
	'action'    => 'user_login',
	'details'   => 'Test user logged in',
	'category'  => 'admin',
	'metadata'  => array( 'user_id' => 1 ),
	'user_id'   => get_current_user_id(),
	'user_name' => wp_get_current_user()->display_name,
	'timestamp' => current_time( 'timestamp' ),
	'date'      => current_time( 'mysql' ),
);

echo "Activity Array Keys:\n";
foreach ( $test_activity as $key => $value ) {
	echo "  - $key: " . ( is_array( $value ) ? 'array' : gettype( $value ) ) . "\n";
}

echo "\n=== Test: Calling track_activity with array ===\n\n";

try {
	// Simulate what the hook does
	Usage_Tracker::track_activity( $test_activity );
	echo "✓ SUCCESS: track_activity() accepted single array parameter\n";
} catch ( ArgumentCountError $e ) {
	echo "✗ FAILED: " . $e->getMessage() . "\n";
} catch ( Exception $e ) {
	echo "✓ No argument count error (other exception: " . $e->getMessage() . ")\n";
}

echo "\nDone!\n";
?>
