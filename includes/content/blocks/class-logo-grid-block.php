<?php
/**
 * Logo Grid Block
 *
 * Displays client/partner logos in grid or carousel format.
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
 * Logo_Grid_Block Class
 *
 * Creates logo grids/carousels for social proof.
 *
 * @since 1.6093.1200
 */
class Logo_Grid_Block {

	/**
	 * Register the block.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register() {
		register_block_type(
			'wpshadow/logo-grid',
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
			'logos'       => array(
				'type'    => 'array',
				'default' => array(),
			),
			'columns'     => array(
				'type'    => 'number',
				'default' => 4,
			),
			'layout'      => array(
				'type'    => 'string',
				'default' => 'grid', // grid, carousel.
			),
			'grayscale'   => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'autoplay'    => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'speed'       => array(
				'type'    => 'number',
				'default' => 3000, // Milliseconds.
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
		$logos     = $attributes['logos'] ?? array();
		$columns   = absint( $attributes['columns'] ?? 4 );
		$layout    = sanitize_text_field( $attributes['layout'] ?? 'grid' );
		$grayscale = ! empty( $attributes['grayscale'] );
		$autoplay  = ! empty( $attributes['autoplay'] );
		$speed     = absint( $attributes['speed'] ?? 3000 );

		if ( empty( $logos ) ) {
			return '<div class="wpshadow-logo-grid-placeholder">' . esc_html__( 'Please add logos to display.', 'wpshadow' ) . '</div>';
		}

		$unique_id = 'wpshadow-logos-' . wp_rand( 1000, 9999 );

		ob_start();
		?>
		<div 
			class="wpshadow-logo-grid wpshadow-layout-<?php echo esc_attr( $layout ); ?> wpshadow-cols-<?php echo esc_attr( $columns ); ?><?php echo $grayscale ? ' wpshadow-grayscale' : ''; ?>" 
			id="<?php echo esc_attr( $unique_id ); ?>"
			data-autoplay="<?php echo esc_attr( $autoplay ? '1' : '0' ); ?>"
			data-speed="<?php echo esc_attr( $speed ); ?>"
		>
			<div class="wpshadow-logo-container">
				<?php foreach ( $logos as $logo ) : ?>
					<?php
					$image_url = esc_url( $logo['url'] ?? '' );
					$image_alt = esc_attr( $logo['alt'] ?? '' );
					$link_url  = esc_url( $logo['link'] ?? '' );

					if ( empty( $image_url ) ) {
						continue;
					}
					?>
					<div class="wpshadow-logo-item">
						<?php if ( $link_url ) : ?>
							<a href="<?php echo esc_url( $link_url ); ?>" target="_blank" rel="noopener noreferrer">
								<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" />
							</a>
						<?php else : ?>
							<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" />
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
