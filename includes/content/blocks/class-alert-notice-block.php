<?php
/**
 * Alert Notice Block
 *
 * Displays styled alert and notice messages.
 *
 * @package    WPShadow
 * @subpackage Blocks
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Alert_Notice_Block Class
 *
 * Creates customizable alert/notice boxes.
 *
 * @since 0.6093.1200
 */
class Alert_Notice_Block {

	/**
	 * Register the block.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register() {
		register_block_type(
			'wpshadow/alert-notice',
			array(
				'attributes'      => self::get_attributes(),
				'render_callback' => array( __CLASS__, 'render' ),
			)
		);
	}

	/**
	 * Get block attributes.
	 *
	 * @since 0.6093.1200
	 * @return array Block attributes schema.
	 */
	private static function get_attributes() {
		return array(
			'type'        => array(
				'type'    => 'string',
				'default' => 'info', // info, success, warning, error.
			),
			'title'       => array(
				'type'    => 'string',
				'default' => '',
			),
			'content'     => array(
				'type'    => 'string',
				'default' => __( 'This is an alert message.', 'wpshadow' ),
			),
			'dismissible' => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'showIcon'    => array(
				'type'    => 'boolean',
				'default' => true,
			),
		);
	}

	/**
	 * Render the block.
	 *
	 * @since 0.6093.1200
	 * @param  array $attributes Block attributes.
	 * @return string Rendered HTML.
	 */
	public static function render( $attributes ) {
		$type        = sanitize_text_field( $attributes['type'] ?? 'info' );
		$title       = wp_kses_post( $attributes['title'] ?? '' );
		$content     = wp_kses_post( $attributes['content'] ?? '' );
		$dismissible = ! empty( $attributes['dismissible'] );
		$show_icon   = ! empty( $attributes['showIcon'] );

		// Map types to dashicons.
		$icons = array(
			'info'    => 'info-outline',
			'success' => 'yes-alt',
			'warning' => 'warning',
			'error'   => 'dismiss',
		);

		$icon = $icons[ $type ] ?? 'info-outline';

		ob_start();
		?>
		<div class="wpshadow-alert wpshadow-alert-<?php echo esc_attr( $type ); ?><?php echo $dismissible ? ' wpshadow-dismissible' : ''; ?>" role="alert">
			<?php if ( $show_icon ) : ?>
				<span class="wpshadow-alert-icon dashicons dashicons-<?php echo esc_attr( $icon ); ?>" aria-hidden="true"></span>
			<?php endif; ?>

			<div class="wpshadow-alert-content">
				<?php if ( $title ) : ?>
					<strong class="wpshadow-alert-title"><?php echo $title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
				<?php endif; ?>
				<div class="wpshadow-alert-message"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			</div>

			<?php if ( $dismissible ) : ?>
				<button type="button" class="wpshadow-alert-dismiss" aria-label="<?php esc_attr_e( 'Dismiss alert', 'wpshadow' ); ?>">
					<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
				</button>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}
