<?php
/**
 * Privacy Page Module for WPShadow
 *
 * Privacy page rendering.
 *
 * @package WPShadow
 * @subpackage Admin
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render privacy page.
 *
 * @return void
 */
function wpshadow_render_privacy_page() {
	if ( ! current_user_can( 'read' ) ) {
		wp_die( 'Insufficient permissions.' );
	}

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'WPShadow Privacy', 'wpshadow' ); ?></h1>
		<p><?php esc_html_e( 'Privacy and data handling information.', 'wpshadow' ); ?></p>
	</div>
	<?php
}
