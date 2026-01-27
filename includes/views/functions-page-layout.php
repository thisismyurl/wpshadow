<?php
/**
 * Page Header/Footer Helper Functions
 *
 * Provides convenient functions for rendering consistent page headers and footers.
 *
 * @package    WPShadow
 * @subpackage Views
 * @since      1.2601.211827
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render page header with title, subtitle, and version tag.
 *
 * @since  1.2601.211827
 * @param  string $title       Page title (required)
 * @param  string $subtitle    Page subtitle/description (optional)
 * @param  string $icon_class  Dashicons CSS class (optional, e.g., 'dashicons-admin-settings')
 * @param  string $icon_color  Icon color value (optional, default: var(--wps-primary))
 * @return void
 *
 * @example
 * wpshadow_render_page_header(
 *     'Settings',
 *     'Configure plugin settings',
 *     'dashicons-admin-settings',
 *     'var(--wps-primary)'
 * );
 */
function wpshadow_render_page_header( $title = '', $subtitle = '', $icon_class = '', $icon_color = 'var(--wps-primary)' ) {
	// Extract variables for the template
	extract(
		array(
			'title'      => $title,
			'subtitle'   => $subtitle,
			'icon_class' => $icon_class,
			'icon_color' => $icon_color,
		),
		EXTR_SKIP
	);

	// Load the header template
	include WPSHADOW_PATH . 'includes/views/page-header.php';
}

/**
 * Render page footer (closing container).
 *
 * @since  1.2601.211827
 * @return void
 */
function wpshadow_render_page_footer() {
	include WPSHADOW_PATH . 'includes/views/page-footer.php';
}
