<?php
/**
 * Before/After Slider Block
 *
 * Displays before/after comparison with draggable slider.
 *
 * @package    WPShadow
 * @subpackage Blocks
 * @since      1.6034.1600
 */

declare(strict_types=1);

namespace WPShadow\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Before_After_Block Class
 *
 * Creates interactive before/after comparison sliders.
 *
 * @since 1.6034.1600
 */
class Before_After_Block {

	/**
	 * Register the block.
	 *
	 * @since 1.6034.1600
	 * @return void
	 */
	public static function register() {
		register_block_type(
			'wpshadow/before-after',
			array(
				'attributes'      => self::get_attributes(),
				'render_callback' => array( __CLASS__, 'render' ),
			)
		);
	}

	/**
	 * Get block attributes.
	 *
	 * @since  1.6034.1600
	 * @return array Block attributes schema.
	 */
	private static function get_attributes() {
		return array(
			'beforeImage'   => array(
				'type'    => 'object',
				'default' => array(
					'url' => '',
					'alt' => __( 'Before', 'wpshadow' ),
				),
			),
			'afterImage'    => array(
				'type'    => 'object',
				'default' => array(
					'url' => '',
					'alt' => __( 'After', 'wpshadow' ),
				),
			),
			'beforeLabel'   => array(
				'type'    => 'string',
				'default' => __( 'Before', 'wpshadow' ),
			),
			'afterLabel'    => array(
				'type'    => 'string',
				'default' => __( 'After', 'wpshadow' ),
			),
			'initialOffset' => array(
				'type'    => 'number',
				'default' => 50, // Percentage.
			),
			'orientation'   => array(
				'type'    => 'string',
				'default' => 'horizontal', // horizontal, vertical.
			),
			'showLabels'    => array(
				'type'    => 'boolean',
				'default' => true,
			),
		);
	}

	/**
	 * Render the block.
	 *
	 * @since  1.6034.1600
	 * @param  array $attributes Block attributes.
	 * @return string Rendered HTML.
	 */
	public static function render( $attributes ) {
		$before_image    = $attributes['beforeImage'] ?? array();
		$after_image     = $attributes['afterImage'] ?? array();
		$before_label    = sanitize_text_field( $attributes['beforeLabel'] ?? __( 'Before', 'wpshadow' ) );
		$after_label     = sanitize_text_field( $attributes['afterLabel'] ?? __( 'After', 'wpshadow' ) );
		$initial_offset  = absint( $attributes['initialOffset'] ?? 50 );
		$orientation     = sanitize_text_field( $attributes['orientation'] ?? 'horizontal' );
		$show_labels     = ! empty( $attributes['showLabels'] );

		$before_url = esc_url( $before_image['url'] ?? '' );
		$after_url  = esc_url( $after_image['url'] ?? '' );
		$before_alt = esc_attr( $before_image['alt'] ?? __( 'Before', 'wpshadow' ) );
		$after_alt  = esc_attr( $after_image['alt'] ?? __( 'After', 'wpshadow' ) );

		if ( empty( $before_url ) || empty( $after_url ) ) {
			return '<div class="wpshadow-before-after-placeholder">' . esc_html__( 'Please select before and after images.', 'wpshadow' ) . '</div>';
		}

		$unique_id = 'wpshadow-ba-' . wp_rand( 1000, 9999 );

		ob_start();
		?>
		<div 
			class="wpshadow-before-after wpshadow-orientation-<?php echo esc_attr( $orientation ); ?>" 
			id="<?php echo esc_attr( $unique_id ); ?>"
			data-initial-offset="<?php echo esc_attr( $initial_offset ); ?>"
			data-orientation="<?php echo esc_attr( $orientation ); ?>"
		>
			<div class="wpshadow-ba-container">
				<div class="wpshadow-ba-before">
					<img src="<?php echo esc_url( $before_url ); ?>" alt="<?php echo esc_attr( $before_alt ); ?>" />
					<?php if ( $show_labels ) : ?>
						<span class="wpshadow-ba-label wpshadow-ba-label-before"><?php echo esc_html( $before_label ); ?></span>
					<?php endif; ?>
				</div>
				<div class="wpshadow-ba-after" style="<?php echo 'horizontal' === $orientation ? 'clip-path: inset(0 0 0 ' . esc_attr( $initial_offset ) . '%);' : ''; ?>">
					<img src="<?php echo esc_url( $after_url ); ?>" alt="<?php echo esc_attr( $after_alt ); ?>" />
					<?php if ( $show_labels ) : ?>
						<span class="wpshadow-ba-label wpshadow-ba-label-after"><?php echo esc_html( $after_label ); ?></span>
					<?php endif; ?>
				</div>
				<div 
					class="wpshadow-ba-slider" 
					style="<?php echo 'horizontal' === $orientation ? 'left: ' . esc_attr( $initial_offset ) . '%;' : 'top: ' . esc_attr( $initial_offset ) . '%;'; ?>"
					role="slider"
					aria-label="<?php esc_attr_e( 'Drag to compare before and after', 'wpshadow' ); ?>"
					aria-valuenow="<?php echo esc_attr( $initial_offset ); ?>"
					aria-valuemin="0"
					aria-valuemax="100"
					tabindex="0"
				>
					<div class="wpshadow-ba-slider-button" aria-hidden="true">
						<span class="dashicons dashicons-leftright"></span>
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
