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
	<div class="wrap wps-page-container">
		<?php
		wpshadow_render_page_header(
			__( 'WPShadow Privacy', 'wpshadow' ),
			__( 'Privacy and data handling information.', 'wpshadow' ),
			'dashicons-privacy'
		);
		?>
	</div>
	<?php
}
