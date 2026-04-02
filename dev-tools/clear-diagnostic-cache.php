<?php
// Load WordPress
require_once dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) . '/wp-load.php';

// Clear the diagnostic file map transient
delete_transient( 'wpshadow_diagnostic_file_map' );

// Rebuild the map by calling the registry
if ( class_exists( '\WPShadow\Diagnostics\Diagnostic_Registry' ) ) {
	\WPShadow\Diagnostics\Diagnostic_Registry::get_diagnostic_file_map();
	echo "Diagnostic file map cache cleared and rebuilt.\n";
} else {
	echo "WPShadow not loaded yet.\n";
}
?>
