<?php
/**
 * Progress Bar Block
 *
 * Displays progress bars and skill meters.
 *
 * @package    WPShadow
 * @subpackage Blocks
 * @since      1.6034.1700
 */

declare(strict_types=1);

namespace WPShadow\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Progress_Bar_Block Class
 *
 * Creates animated progress bars and skill meters.
 *
 * @since 1.6034.1700
 */
class Progress_Bar_Block {

	/**
	 * Register the block.
	 *
	 * @since 1.6034.1700
	 * @return void
	 */
	public static function register() {
		register_block_type(
			'wpshadow/progress-bar',
			array(
				'attributes'      => self::get_attributes(),
				'render_callback' => array( __CLASS__, 'render' ),
			)
		);
	}

	/**
	 * Get block attributes.
	 *
	 * @since  1.6034.1700
	 * @return array Block attributes schema.
	 */
	private static function get_attributes() {
		return array(
			'bars'            => array(
				'type'    => 'array',
				'default' => array(
					array(
						'label'      => __( 'WordPress', 'wpshadow' ),
						'percentage' => 95,
					),
					array(
						'label'      => __( 'PHP', 'wpshadow' ),
						'percentage' => 90,
					),
				),
			),
			'style'           => array(
				'type'    => 'string',
				'default' => 'standard', // standard, striped, animated.
			),
			'showPercentage'  => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'animateOnScroll' => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'barColor'        => array(
				'type'    => 'string',
				'default' => '#1e40af',
			),
			'height'          => array(
				'type'    => 'number',
				'default' => 20, // Pixels.
			),
		);
	}

	/**
	 * Render the block.
	 *
	 * @since  1.6034.1700
	 * @param  array $attributes Block attributes.
	 * @return string Rendered HTML.
	 */
	public static function render( $attributes ) {
		$bars             = $attributes['bars'] ?? array();
		$style            = sanitize_text_field( $attributes['style'] ?? 'standard' );
		$show_percentage  = ! empty( $attributes['showPercentage'] );
		$animate_on_scroll = ! empty( $attributes['animateOnScroll'] );
		$bar_color        = sanitize_hex_color( $attributes['barColor'] ?? '#1e40af' );
		$height           = absint( $attributes['height'] ?? 20 );

		if ( empty( $bars ) ) {
			return '';
		}

		ob_start();
		?>
		<div 
			class="wpshadow-progress-bars wpshadow-style-<?php echo esc_attr( $style ); ?>" 
			data-animate="<?php echo esc_attr( $animate_on_scroll ? '1' : '0' ); ?>"
			style="--bar-color: <?php echo esc_attr( $bar_color ); ?>; --bar-height: <?php echo esc_attr( $height ); ?>px;"
		>
			<?php foreach ( $bars as $bar ) : ?>
				<?php
				$label      = wp_kses_post( $bar['label'] ?? '' );
				$percentage = absint( $bar['percentage'] ?? 0 );
				$percentage = min( 100, max( 0, $percentage ) ); // Clamp 0-100.
				?>
				<div class="wpshadow-progress-item">
					<div class="wpshadow-progress-header">
						<?php if ( $label ) : ?>
							<span class="wpshadow-progress-label"><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<?php endif; ?>
						<?php if ( $show_percentage ) : ?>
							<span class="wpshadow-progress-percentage"><?php echo esc_html( $percentage ); ?>%</span>
						<?php endif; ?>
					</div>
					<div class="wpshadow-progress-track">
						<div 
							class="wpshadow-progress-fill" 
							data-percentage="<?php echo esc_attr( $percentage ); ?>"
							style="width: <?php echo esc_attr( $animate_on_scroll ? '0' : $percentage ); ?>%;"
							role="progressbar"
							aria-valuenow="<?php echo esc_attr( $percentage ); ?>"
							aria-valuemin="0"
							aria-valuemax="100"
							aria-label="<?php echo esc_attr( wp_strip_all_tags( $label ) ); ?>"
						></div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}
