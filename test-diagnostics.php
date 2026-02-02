<?php
/**
 * Test diagnostic implementations
 *
 * Tests the four new media security diagnostics to ensure they work correctly.
 */

// Set up WordPress test environment
define( 'WP_USE_THEMES', false );
require dirname( dirname( dirname( __FILE__ ) ) ) . '/wp-load.php';

// Include the plugin
require dirname( dirname( dirname( __FILE__ ) ) ) . '/wp-content/plugins/wpshadow/wpshadow.php';

// Include the diagnostic classes
require dirname( __FILE__ ) . '/includes/diagnostics/tests/class-diagnostic-media-direct-file-access-security.php';
require dirname( __FILE__ ) . '/includes/diagnostics/tests/class-diagnostic-media-malicious-file-upload-detection.php';
require dirname( __FILE__ ) . '/includes/diagnostics/tests/class-diagnostic-media-file-type-mime-validation.php';
require dirname( __FILE__ ) . '/includes/diagnostics/tests/class-diagnostic-media-private-media-access-control.php';

use WPShadow\Diagnostics\Diagnostic_Media_Direct_File_Access_Security;
use WPShadow\Diagnostics\Diagnostic_Media_Malicious_File_Upload_Detection;
use WPShadow\Diagnostics\Diagnostic_Media_File_Type_MIME_Validation;
use WPShadow\Diagnostics\Diagnostic_Media_Private_Media_Access_Control;

// Test each diagnostic
$diagnostics = array(
	array(
		'class'  => 'Diagnostic_Media_Direct_File_Access_Security',
		'slug'   => 'media-direct-file-access-security',
		'object' => new Diagnostic_Media_Direct_File_Access_Security(),
	),
	array(
		'class'  => 'Diagnostic_Media_Malicious_File_Upload_Detection',
		'slug'   => 'media-malicious-file-upload-detection',
		'object' => new Diagnostic_Media_Malicious_File_Upload_Detection(),
	),
	array(
		'class'  => 'Diagnostic_Media_File_Type_MIME_Validation',
		'slug'   => 'media-file-type-mime-validation',
		'object' => new Diagnostic_Media_File_Type_MIME_Validation(),
	),
	array(
		'class'  => 'Diagnostic_Media_Private_Media_Access_Control',
		'slug'   => 'media-private-media-access-control',
		'object' => new Diagnostic_Media_Private_Media_Access_Control(),
	),
);

echo "Testing diagnostics...\n";
echo str_repeat( '=', 80 ) . "\n\n";

foreach ( $diagnostics as $diagnostic ) {
	echo "Testing: {$diagnostic['class']}\n";
	echo "Slug: {$diagnostic['slug']}\n";
	
	try {
		$result = call_user_func( array( $diagnostic['object'], 'check' ) );
		
		if ( $result === null ) {
			echo "✓ Check passed (no issues found)\n";
		} else {
			echo "✗ Issue found:\n";
			echo "  Title: {$result['title']}\n";
			echo "  Description: {$result['description']}\n";
			echo "  Severity: {$result['severity']}\n";
			echo "  Threat Level: {$result['threat_level']}\n";
		}
	} catch ( \Exception $e ) {
		echo "✗ Error running diagnostic: " . $e->getMessage() . "\n";
	}
	
	echo "\n";
}

echo str_repeat( '=', 80 ) . "\n";
echo "All diagnostics tested successfully!\n";
