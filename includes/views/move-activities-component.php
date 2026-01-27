<?php
/**
 * Move page-activities component from temp location to proper directory
 * 
 * This is a temporary bootstrap script to handle the missing components directory
 */
if ( ! defined( 'ABSPATH' ) ) {
exit;
}

$temp_file = WP_PLUGIN_DIR . '/wpshadow/includes/views/page-activities-temp.php';
$target_dir = WP_PLUGIN_DIR . '/wpshadow/includes/views/components';
$target_file = $target_dir . '/page-activities.php';

// Create directory if needed
if ( ! is_dir( $target_dir ) ) {
@mkdir( $target_dir, 0755, true );
}

// Move file if it exists
if ( file_exists( $temp_file ) && ! file_exists( $target_file ) ) {
@rename( $temp_file, $target_file );
}

// Now load the component if it exists
if ( file_exists( $target_file ) ) {
require_once $target_file;
}
