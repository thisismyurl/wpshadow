<?php
/**
 * Email Report UI Entry
 *
 * Canonical UI entry point for the email deliverability report.
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

require WPSHADOW_PATH . 'includes/views/reports/email-report.php';
