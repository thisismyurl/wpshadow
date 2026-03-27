<?php
/**
 * Help card: Support
 *
 * @package WPShadow
 */

use WPShadow\Core\UTM_Link_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wpshadow_render_help_support_card_body' ) ) {
	/**
	 * Render the support card body actions.
	 *
	 * @return void
	 */
	function wpshadow_render_help_support_card_body() {
		?>
		<div class="wps-flex wps-gap-3">
			<a href="https://github.com/thisismyurl/wpshadow/issues?utm_source=wpshadow&utm_medium=plugin&utm_campaign=help_page&utm_content=contact_support" target="_blank" rel="noopener noreferrer" class="wps-btn wps-btn--primary">
				<span class="dashicons dashicons-admin-comments"></span>
				<?php esc_html_e( 'Contact Support', 'wpshadow' ); ?>
			</a>
			<a href="https://wpshadow.com/academy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=help_page&utm_content=academy_cta" target="_blank" rel="noopener noreferrer" class="wps-btn wps-btn--secondary">
				<span class="dashicons dashicons-video-alt2"></span>
				<?php esc_html_e( 'Online Training', 'wpshadow' ); ?>
			</a>
			<a href="<?php echo esc_url( UTM_Link_Manager::kb_link( '', 'help_page' ) ); ?>" target="_blank" rel="noopener noreferrer" class="wps-btn wps-btn--secondary">
				<span class="dashicons dashicons-book"></span>
				<?php esc_html_e( 'Knowledge Base', 'wpshadow' ); ?>
			</a>
		</div>
		<?php
	}
}

return array(
	'id'            => 'support',
	'order'         => 90,
	'section'       => 'support',
	'width'         => 'full',
	'title'         => __( 'Need More Help?', 'wpshadow' ),
	'title_tag'     => 'h2',
	'description'   => __( 'Access our knowledge base, training videos, and community support.', 'wpshadow' ),
	'icon'          => 'dashicons-sos',
	'card_class'    => 'wps-mt-8',
	'body_callback' => 'wpshadow_render_help_support_card_body',
);
