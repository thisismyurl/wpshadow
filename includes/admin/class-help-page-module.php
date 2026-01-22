<?php
/**
 * Help Page Module for WPShadow
 *
 * Help page rendering.
 *
 * @package WPShadow
 * @subpackage Admin
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get help catalog.
 *
 * @return array Help items.
 */
function wpshadow_get_help_catalog() {
	return array();
}

/**
 * Render help page.
 *
 * @return void
 */
function wpshadow_render_help() {
	if ( ! current_user_can( 'read' ) ) {
		wp_die( 'Insufficient permissions.' );
	}

	$catalog = wpshadow_get_help_catalog();

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'WPShadow Help', 'wpshadow' ); ?></h1>
		<p><?php esc_html_e( 'Get help and learn more about WPShadow features.', 'wpshadow' ); ?></p>
	</div>
	<?php
}
