<?php

/**
 * Activity History Tool Page
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'read' ) ) {
	wp_die( 'Insufficient permissions.' );
}

// Include the activity history view
require WPSHADOW_PATH . 'includes/views/activity-history.php';
