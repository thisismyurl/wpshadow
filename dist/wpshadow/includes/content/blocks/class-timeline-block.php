<?php
/**
 * Timeline Block
 *
 * Displays timeline/process visualization.
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
 * Timeline_Block Class
 *
 * Creates vertical or horizontal timelines for processes and histories.
 *
 * @since 0.6093.1200
 */
class Timeline_Block {

	/**
	 * Register the block.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register() {
		register_block_type(
			'wpshadow/timeline',
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
			'items'       => array(
				'type'    => 'array',
				'default' => array(
					array(
						'date'        => __( '2020', 'wpshadow' ),
						'title'       => __( 'Company Founded', 'wpshadow' ),
						'description' => __( 'Started with a mission to make WordPress security accessible to everyone.', 'wpshadow' ),
					),
					array(
						'date'        => __( '2022', 'wpshadow' ),
						'title'       => __( 'Major Milestone', 'wpshadow' ),
						'description' => __( 'Reached 10,000 active installations.', 'wpshadow' ),
					),
					array(
						'date'        => __( '2024', 'wpshadow' ),
						'title'       => __( 'Present Day', 'wpshadow' ),
						'description' => __( 'Continuing to innovate and serve our community.', 'wpshadow' ),
					),
				),
			),
			'layout'      => array(
				'type'    => 'string',
				'default' => 'vertical', // vertical, horizontal.
			),
			'accentColor' => array(
				'type'    => 'string',
				'default' => '#1e40af',
			),
			'alignment'   => array(
				'type'    => 'string',
				'default' => 'left', // left, center, alternating.
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
		$items        = $attributes['items'] ?? array();
		$layout       = sanitize_text_field( $attributes['layout'] ?? 'vertical' );
		$accent_color = sanitize_hex_color( $attributes['accentColor'] ?? '#1e40af' );
		$alignment    = sanitize_text_field( $attributes['alignment'] ?? 'left' );

		if ( empty( $items ) ) {
			return '';
		}

		ob_start();
		?>
		<div class="wpshadow-timeline wpshadow-timeline-<?php echo esc_attr( $layout ); ?> wpshadow-align-<?php echo esc_attr( $alignment ); ?>" style="--timeline-color: <?php echo esc_attr( $accent_color ); ?>;">
			<?php foreach ( $items as $index => $item ) : ?>
				<?php
				$date        = wp_kses_post( $item['date'] ?? '' );
				$title       = wp_kses_post( $item['title'] ?? '' );
				$description = wp_kses_post( $item['description'] ?? '' );
				$position    = ( 'alternating' === $alignment && 0 === $index % 2 ) ? 'right' : 'left';
				?>
				<div class="wpshadow-timeline-item wpshadow-position-<?php echo esc_attr( $position ); ?>">
					<div class="wpshadow-timeline-marker" aria-hidden="true"></div>
					<div class="wpshadow-timeline-content">
						<?php if ( $date ) : ?>
							<span class="wpshadow-timeline-date"><?php echo $date; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<?php endif; ?>
						<?php if ( $title ) : ?>
							<h3 class="wpshadow-timeline-title"><?php echo $title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h3>
						<?php endif; ?>
						<?php if ( $description ) : ?>
							<p class="wpshadow-timeline-description"><?php echo $description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}
