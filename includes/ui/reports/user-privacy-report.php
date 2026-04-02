<?php
/**
 * User Privacy Report UI Entry
 *
 * Canonical UI entry point for the user privacy report.
 * Delegates rendering to the existing report implementation.
 *
 * @package    WPShadow
 * @subpackage Reports
 * @since 1.6093.1200
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require WPSHADOW_PATH . 'includes/views/reports/user-privacy-report.php';
