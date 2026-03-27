<?php
/**
 * Countdown Timer Block
 *
 * Displays countdown timers for events and promotions.
 *
 * @package    WPShadow
 * @subpackage Blocks
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Countdown_Timer_Block Class
 *
 * Creates countdown timers with timezone support.
 *
 * @since 1.6093.1200
 */
class Countdown_Timer_Block {

	/**
	 * Register the block.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register() {
		register_block_type(
			'wpshadow/countdown-timer',
			array(
				'attributes'      => self::get_attributes(),
				'render_callback' => array( __CLASS__, 'render' ),
			)
		);
	}

	/**
	 * Get block attributes.
	 *
	 * @since 1.6093.1200
	 * @return array Block attributes schema.
	 */
	private static function get_attributes() {
		return array(
			'targetDate'    => array(
				'type'    => 'string',
				'default' => '',
			),
			'title'         => array(
				'type'    => 'string',
				'default' => __( 'Countdown to Event', 'wpshadow' ),
			),
			'showLabels'    => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'expiredText'   => array(
				'type'    => 'string',
				'default' => __( 'Event has started!', 'wpshadow' ),
			),
			'style'         => array(
				'type'    => 'string',
				'default' => 'boxes', // boxes, inline, minimal.
			),
			'accentColor'   => array(
				'type'    => 'string',
				'default' => '#1e40af',
			),
		);
	}

	/**
	 * Render the block.
	 *
	 * @since 1.6093.1200
	 * @param  array $attributes Block attributes.
	 * @return string Rendered HTML.
	 */
	public static function render( $attributes ) {
		$target_date  = sanitize_text_field( $attributes['targetDate'] ?? '' );
		$title        = wp_kses_post( $attributes['title'] ?? '' );
		$show_labels  = ! empty( $attributes['showLabels'] );
		$expired_text = wp_kses_post( $attributes['expiredText'] ?? __( 'Event has started!', 'wpshadow' ) );
		$style        = sanitize_text_field( $attributes['style'] ?? 'boxes' );
		$accent_color = sanitize_hex_color( $attributes['accentColor'] ?? '#1e40af' );

		if ( empty( $target_date ) ) {
			return '<div class="wpshadow-countdown-placeholder">' . esc_html__( 'Please set a target date.', 'wpshadow' ) . '</div>';
		}

		$unique_id = 'wpshadow-countdown-' . wp_rand( 1000, 9999 );

		ob_start();
		?>
		<div 
			class="wpshadow-countdown wpshadow-style-<?php echo esc_attr( $style ); ?>" 
			id="<?php echo esc_attr( $unique_id ); ?>"
			data-target-date="<?php echo esc_attr( $target_date ); ?>"
			data-expired-text="<?php echo esc_attr( $expired_text ); ?>"
			style="--accent-color: <?php echo esc_attr( $accent_color ); ?>;"
		>
			<?php if ( $title ) : ?>
				<h3 class="wpshadow-countdown-title"><?php echo $title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h3>
			<?php endif; ?>
			
			<div class="wpshadow-countdown-timer">
				<div class="wpshadow-countdown-unit">
					<span class="wpshadow-countdown-number" data-unit="days">00</span>
					<?php if ( $show_labels ) : ?>
						<span class="wpshadow-countdown-label"><?php esc_html_e( 'Days', 'wpshadow' ); ?></span>
					<?php endif; ?>
				</div>
				<div class="wpshadow-countdown-unit">
					<span class="wpshadow-countdown-number" data-unit="hours">00</span>
					<?php if ( $show_labels ) : ?>
						<span class="wpshadow-countdown-label"><?php esc_html_e( 'Hours', 'wpshadow' ); ?></span>
					<?php endif; ?>
				</div>
				<div class="wpshadow-countdown-unit">
					<span class="wpshadow-countdown-number" data-unit="minutes">00</span>
					<?php if ( $show_labels ) : ?>
						<span class="wpshadow-countdown-label"><?php esc_html_e( 'Minutes', 'wpshadow' ); ?></span>
					<?php endif; ?>
				</div>
				<div class="wpshadow-countdown-unit">
					<span class="wpshadow-countdown-number" data-unit="seconds">00</span>
					<?php if ( $show_labels ) : ?>
						<span class="wpshadow-countdown-label"><?php esc_html_e( 'Seconds', 'wpshadow' ); ?></span>
					<?php endif; ?>
				</div>
			</div>
			
			<div class="wpshadow-countdown-expired" style="display: none;">
				<?php echo $expired_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
