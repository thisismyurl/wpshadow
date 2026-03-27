<?php
/**
 * SEO Report UI Entry
 *
 * Canonical UI entry point for the SEO report.
 * Delegates rendering to the standardized report implementation.
 *
 * @package    WPShadow
 * @subpackage Reports
 * @since      1.6093.1200
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require WPSHADOW_PATH . 'includes/views/reports/seo-report.php';
