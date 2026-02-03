<?php
/**
 * Statistics Counter Block
 *
 * Displays animated statistics and counters.
 *
 * @package    WPShadow
 * @subpackage Blocks
 * @since      1.6034.1610
 */

declare(strict_types=1);

namespace WPShadow\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stats_Counter_Block Class
 *
 * Creates animated counters for showcasing statistics and achievements.
 *
 * @since 1.6034.1610
 */
class Stats_Counter_Block {

	/**
	 * Register the block.
	 *
	 * @since 1.6034.1610
	 * @return void
	 */
	public static function register() {
		register_block_type(
			'wpshadow/stats-counter',
			array(
				'attributes'      => self::get_attributes(),
				'render_callback' => array( __CLASS__, 'render' ),
			)
		);
	}

	/**
	 * Get block attributes.
	 *
	 * @since  1.6034.1610
	 * @return array Block attributes schema.
	 */
	private static function get_attributes() {
		return array(
			'stats'           => array(
				'type'    => 'array',
				'default' => array(
					array(
						'number' => '500',
						'suffix' => '+',
						'label'  => __( 'Happy Clients', 'wpshadow' ),
						'icon'   => 'smiley',
					),
					array(
						'number' => '10',
						'suffix' => 'K+',
						'label'  => __( 'Active Installs', 'wpshadow' ),
						'icon'   => 'download',
					),
					array(
						'number' => '99',
						'suffix' => '%',
						'label'  => __( 'Satisfaction Rate', 'wpshadow' ),
						'icon'   => 'star-filled',
					),
				),
			),
			'columns'         => array(
				'type'    => 'number',
				'default' => 3,
			),
			'animateOnScroll' => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'duration'        => array(
				'type'    => 'number',
				'default' => 2000, // Milliseconds.
			),
			'counterColor'    => array(
				'type'    => 'string',
				'default' => '#1e40af',
			),
		);
	}

	/**
	 * Render the block.
	 *
	 * @since  1.6034.1610
	 * @param  array $attributes Block attributes.
	 * @return string Rendered HTML.
	 */
	public static function render( $attributes ) {
		$stats            = $attributes['stats'] ?? array();
		$columns          = absint( $attributes['columns'] ?? 3 );
		$animate_on_scroll = ! empty( $attributes['animateOnScroll'] );
		$duration         = absint( $attributes['duration'] ?? 2000 );
		$counter_color    = sanitize_hex_color( $attributes['counterColor'] ?? '#1e40af' );

		if ( empty( $stats ) ) {
			return '';
		}

		ob_start();
		?>
		<div 
			class="wpshadow-stats-counter wpshadow-stats-<?php echo esc_attr( $columns ); ?>-col" 
			data-animate="<?php echo esc_attr( $animate_on_scroll ? '1' : '0' ); ?>"
			data-duration="<?php echo esc_attr( $duration ); ?>"
			style="--counter-color: <?php echo esc_attr( $counter_color ); ?>;"
		>
			<?php foreach ( $stats as $stat ) : ?>
				<?php
				$number = sanitize_text_field( $stat['number'] ?? '0' );
				$suffix = sanitize_text_field( $stat['suffix'] ?? '' );
				$label  = wp_kses_post( $stat['label'] ?? '' );
				$icon   = sanitize_text_field( $stat['icon'] ?? '' );
				?>
				<div class="wpshadow-stat-item">
					<?php if ( $icon ) : ?>
						<div class="wpshadow-stat-icon">
							<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>" aria-hidden="true"></span>
						</div>
					<?php endif; ?>
					
					<div class="wpshadow-stat-number">
						<span class="wpshadow-counter" data-target="<?php echo esc_attr( $number ); ?>">0</span><?php echo esc_html( $suffix ); ?>
					</div>
					
					<?php if ( $label ) : ?>
						<div class="wpshadow-stat-label"><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}
