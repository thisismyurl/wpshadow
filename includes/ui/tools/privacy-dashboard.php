<?php
/**
 * Privacy Dashboard Utility
 *
 * Renders the Privacy Dashboard directly from the Utilities page.
 *
 * @package WPShadow
 * @since 1.6093.1200
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load the Privacy Dashboard Page class if not already loaded
if ( ! class_exists( 'WPShadow\Admin\Privacy_Dashboard_Page' ) ) {
	require_once WPSHADOW_PATH . 'includes/admin/class-privacy-dashboard-page.php';
}

// Render the privacy dashboard
\WPShadow\Admin\Privacy_Dashboard_Page::render_page();
