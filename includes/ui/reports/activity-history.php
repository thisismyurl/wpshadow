<?php
/**
 * Activity History Report
 *
 * Displays comprehensive activity log with filtering and export.
 *
 * @package WPShadow
 * @subpackage Reports
 * @since 1.602.0000
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Views\Tool_View_Base;

require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';

// Verify access
Tool_View_Base::verify_access( 'read' );

// Include the activity history view
require WPSHADOW_PATH . 'includes/views/activity-history.php';

