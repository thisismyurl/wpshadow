<?php
/**
 * Diagnostics Fix Rate Report UI Entry
 *
 * Canonical UI entry point for the diagnostics fix rate report.
 * Delegates rendering to the existing report implementation.
 *
 * @package    WPShadow
 * @subpackage Reports
 * @since      1.7056.1200
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require WPSHADOW_PATH . 'includes/views/reports/diagnostics-fix-rate.php';
