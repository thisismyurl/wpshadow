<?php
/**
 * Call-to-Action Block
 *
 * Displays conversion-focused CTA sections.
 *
 * @package    WPShadow
 * @subpackage Blocks
 * @since      1.6034.1530
 */

declare(strict_types=1);

namespace WPShadow\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CTA_Block Class
 *
 * Creates eye-catching call-to-action blocks with buttons and forms.
 *
 * @since 1.6034.1530
 */
class CTA_Block {

	/**
	 * Register the block.
	 *
	 * @since 1.6034.1530
	 * @return void
	 */
	public static function register() {
		register_block_type(
			'wpshadow/cta-block',
			array(
				'attributes'      => self::get_attributes(),
				'render_callback' => array( __CLASS__, 'render' ),
			)
		);
	}

	/**
	 * Get block attributes.
	 *
	 * @since  1.6034.1530
	 * @return array Block attributes schema.
	 */
	private static function get_attributes() {
		return array(
			'title'           => array(
				'type'    => 'string',
				'default' => __( 'Ready to Get Started?', 'wpshadow' ),
			),
			'description'     => array(
				'type'    => 'string',
				'default' => __( 'Join thousands of satisfied customers using WPShadow to keep their WordPress sites secure and optimized.', 'wpshadow' ),
			),
			'primaryButton'   => array(
				'type'    => 'object',
				'default' => array(
					'text' => __( 'Get Started Free', 'wpshadow' ),
					'url'  => '#',
				),
			),
			'secondaryButton' => array(
				'type'    => 'object',
				'default' => array(
					'text' => __( 'Learn More', 'wpshadow' ),
					'url'  => '#',
				),
			),
			'layout'          => array(
				'type'    => 'string',
				'default' => 'centered', // centered, split, banner.
			),
			'backgroundColor' => array(
				'type'    => 'string',
				'default' => '#1e40af',
			),
			'textColor'       => array(
				'type'    => 'string',
				'default' => '#ffffff',
			),
			'showSecondary'   => array(
				'type'    => 'boolean',
				'default' => true,
			),
		);
	}

	/**
	 * Render the block.
	 *
	 * @since  1.6034.1530
	 * @param  array $attributes Block attributes.
	 * @return string Rendered HTML.
	 */
	public static function render( $attributes ) {
		$title            = wp_kses_post( $attributes['title'] ?? '' );
		$description      = wp_kses_post( $attributes['description'] ?? '' );
		$primary_button   = $attributes['primaryButton'] ?? array();
		$secondary_button = $attributes['secondaryButton'] ?? array();
		$layout           = sanitize_text_field( $attributes['layout'] ?? 'centered' );
		$bg_color         = sanitize_hex_color( $attributes['backgroundColor'] ?? '#1e40af' );
		$text_color       = sanitize_hex_color( $attributes['textColor'] ?? '#ffffff' );
		$show_secondary   = ! empty( $attributes['showSecondary'] );

		$primary_text = sanitize_text_field( $primary_button['text'] ?? __( 'Get Started', 'wpshadow' ) );
		$primary_url  = esc_url( $primary_button['url'] ?? '#' );

		$secondary_text = sanitize_text_field( $secondary_button['text'] ?? __( 'Learn More', 'wpshadow' ) );
		$secondary_url  = esc_url( $secondary_button['url'] ?? '#' );

		$inline_styles = sprintf(
			'background-color: %s; color: %s;',
			esc_attr( $bg_color ),
			esc_attr( $text_color )
		);

		ob_start();
		?>
		<div class="wpshadow-cta-block wpshadow-cta-<?php echo esc_attr( $layout ); ?>" style="<?php echo esc_attr( $inline_styles ); ?>">
			<div class="wpshadow-cta-content">
				<?php if ( $title ) : ?>
					<h2 class="wpshadow-cta-title"><?php echo $title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
				<?php endif; ?>
				
				<?php if ( $description ) : ?>
					<p class="wpshadow-cta-description"><?php echo $description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
				<?php endif; ?>
				
				<div class="wpshadow-cta-buttons">
					<a href="<?php echo esc_url( $primary_url ); ?>" class="wpshadow-cta-button wpshadow-primary">
						<?php echo esc_html( $primary_text ); ?>
					</a>
					
					<?php if ( $show_secondary ) : ?>
						<a href="<?php echo esc_url( $secondary_url ); ?>" class="wpshadow-cta-button wpshadow-secondary">
							<?php echo esc_html( $secondary_text ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
