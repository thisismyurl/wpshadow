<?php
/**
 * Icon Box Block
 *
 * Displays icon-based feature boxes.
 *
 * @package    WPShadow
 * @subpackage Blocks
 * @since      1.6034.1540
 */

declare(strict_types=1);

namespace WPShadow\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Icon_Box_Block Class
 *
 * Creates icon boxes for highlighting features and benefits.
 *
 * @since 1.6034.1540
 */
class Icon_Box_Block {

	/**
	 * Register the block.
	 *
	 * @since 1.6034.1540
	 * @return void
	 */
	public static function register() {
		register_block_type(
			'wpshadow/icon-box',
			array(
				'attributes'      => self::get_attributes(),
				'render_callback' => array( __CLASS__, 'render' ),
			)
		);
	}

	/**
	 * Get block attributes.
	 *
	 * @since  1.6034.1540
	 * @return array Block attributes schema.
	 */
	private static function get_attributes() {
		return array(
			'icon'            => array(
				'type'    => 'string',
				'default' => 'shield-alt',
			),
			'title'           => array(
				'type'    => 'string',
				'default' => __( 'Feature Title', 'wpshadow' ),
			),
			'description'     => array(
				'type'    => 'string',
				'default' => __( 'Describe your feature or benefit here.', 'wpshadow' ),
			),
			'linkUrl'         => array(
				'type'    => 'string',
				'default' => '',
			),
			'linkText'        => array(
				'type'    => 'string',
				'default' => __( 'Learn More', 'wpshadow' ),
			),
			'iconColor'       => array(
				'type'    => 'string',
				'default' => '#1e40af',
			),
			'iconBackground'  => array(
				'type'    => 'string',
				'default' => '#eff6ff',
			),
			'alignment'       => array(
				'type'    => 'string',
				'default' => 'center', // left, center.
			),
			'iconPosition'    => array(
				'type'    => 'string',
				'default' => 'top', // top, left.
			),
		);
	}

	/**
	 * Render the block.
	 *
	 * @since  1.6034.1540
	 * @param  array $attributes Block attributes.
	 * @return string Rendered HTML.
	 */
	public static function render( $attributes ) {
		$icon            = sanitize_text_field( $attributes['icon'] ?? 'shield-alt' );
		$title           = wp_kses_post( $attributes['title'] ?? '' );
		$description     = wp_kses_post( $attributes['description'] ?? '' );
		$link_url        = esc_url( $attributes['linkUrl'] ?? '' );
		$link_text       = sanitize_text_field( $attributes['linkText'] ?? __( 'Learn More', 'wpshadow' ) );
		$icon_color      = sanitize_hex_color( $attributes['iconColor'] ?? '#1e40af' );
		$icon_background = sanitize_hex_color( $attributes['iconBackground'] ?? '#eff6ff' );
		$alignment       = sanitize_text_field( $attributes['alignment'] ?? 'center' );
		$icon_position   = sanitize_text_field( $attributes['iconPosition'] ?? 'top' );

		$icon_styles = sprintf(
			'color: %s; background-color: %s;',
			esc_attr( $icon_color ),
			esc_attr( $icon_background )
		);

		ob_start();
		?>
		<div class="wpshadow-icon-box wpshadow-align-<?php echo esc_attr( $alignment ); ?> wpshadow-icon-<?php echo esc_attr( $icon_position ); ?>">
			<div class="wpshadow-icon-wrapper" style="<?php echo esc_attr( $icon_styles ); ?>">
				<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>" aria-hidden="true"></span>
			</div>
			
			<div class="wpshadow-icon-content">
				<?php if ( $title ) : ?>
					<h3 class="wpshadow-icon-title"><?php echo $title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h3>
				<?php endif; ?>
				
				<?php if ( $description ) : ?>
					<p class="wpshadow-icon-description"><?php echo $description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
				<?php endif; ?>
				
				<?php if ( $link_url ) : ?>
					<a href="<?php echo esc_url( $link_url ); ?>" class="wpshadow-icon-link">
						<?php echo esc_html( $link_text ); ?>
						<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
					</a>
				<?php endif; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
