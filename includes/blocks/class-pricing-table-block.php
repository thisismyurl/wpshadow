<?php
/**
 * Pricing Table Block
 *
 * Displays pricing plans with comparison features.
 *
 * @package    WPShadow
 * @subpackage Blocks
 * @since      1.6034.1510
 */

declare(strict_types=1);

namespace WPShadow\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pricing_Table_Block Class
 *
 * Creates customizable pricing tables with multiple plan options.
 *
 * @since 1.6034.1510
 */
class Pricing_Table_Block {

	/**
	 * Register the block.
	 *
	 * @since 1.6034.1510
	 * @return void
	 */
	public static function register() {
		register_block_type(
			'wpshadow/pricing-table',
			array(
				'attributes'      => self::get_attributes(),
				'render_callback' => array( __CLASS__, 'render' ),
			)
		);
	}

	/**
	 * Get block attributes.
	 *
	 * @since  1.6034.1510
	 * @return array Block attributes schema.
	 */
	private static function get_attributes() {
		return array(
			'plans'           => array(
				'type'    => 'array',
				'default' => array(
					array(
						'title'       => __( 'Basic', 'wpshadow' ),
						'price'       => '29',
						'period'      => __( 'per month', 'wpshadow' ),
						'features'    => array(
							__( '5 Projects', 'wpshadow' ),
							__( '10 GB Storage', 'wpshadow' ),
							__( 'Email Support', 'wpshadow' ),
						),
						'buttonText'  => __( 'Get Started', 'wpshadow' ),
						'buttonUrl'   => '#',
						'featured'    => false,
					),
					array(
						'title'       => __( 'Pro', 'wpshadow' ),
						'price'       => '79',
						'period'      => __( 'per month', 'wpshadow' ),
						'features'    => array(
							__( 'Unlimited Projects', 'wpshadow' ),
							__( '100 GB Storage', 'wpshadow' ),
							__( 'Priority Support', 'wpshadow' ),
							__( 'Advanced Analytics', 'wpshadow' ),
						),
						'buttonText'  => __( 'Get Started', 'wpshadow' ),
						'buttonUrl'   => '#',
						'featured'    => true,
					),
					array(
						'title'       => __( 'Enterprise', 'wpshadow' ),
						'price'       => '199',
						'period'      => __( 'per month', 'wpshadow' ),
						'features'    => array(
							__( 'Unlimited Everything', 'wpshadow' ),
							__( 'Unlimited Storage', 'wpshadow' ),
							__( '24/7 Phone Support', 'wpshadow' ),
							__( 'Custom Integrations', 'wpshadow' ),
							__( 'Dedicated Account Manager', 'wpshadow' ),
						),
						'buttonText'  => __( 'Contact Sales', 'wpshadow' ),
						'buttonUrl'   => '#',
						'featured'    => false,
					),
				),
			),
			'columns'         => array(
				'type'    => 'number',
				'default' => 3,
			),
			'currencySymbol'  => array(
				'type'    => 'string',
				'default' => '$',
			),
			'alignment'       => array(
				'type'    => 'string',
				'default' => 'center',
			),
			'backgroundColor' => array(
				'type'    => 'string',
				'default' => '#ffffff',
			),
		);
	}

	/**
	 * Render the block.
	 *
	 * @since  1.6034.1510
	 * @param  array $attributes Block attributes.
	 * @return string Rendered HTML.
	 */
	public static function render( $attributes ) {
		$plans           = $attributes['plans'] ?? array();
		$columns         = absint( $attributes['columns'] ?? 3 );
		$currency_symbol = sanitize_text_field( $attributes['currencySymbol'] ?? '$' );
		$alignment       = sanitize_text_field( $attributes['alignment'] ?? 'center' );

		if ( empty( $plans ) ) {
			return '';
		}

		ob_start();
		?>
		<div class="wpshadow-pricing-table wpshadow-pricing-<?php echo esc_attr( $columns ); ?>-col wpshadow-align-<?php echo esc_attr( $alignment ); ?>">
			<?php foreach ( $plans as $plan ) : ?>
				<?php
				$title       = sanitize_text_field( $plan['title'] ?? '' );
				$price       = sanitize_text_field( $plan['price'] ?? '0' );
				$period      = sanitize_text_field( $plan['period'] ?? '' );
				$features    = $plan['features'] ?? array();
				$button_text = sanitize_text_field( $plan['buttonText'] ?? __( 'Get Started', 'wpshadow' ) );
				$button_url  = esc_url( $plan['buttonUrl'] ?? '#' );
				$featured    = ! empty( $plan['featured'] );
				?>
				<div class="wpshadow-pricing-plan<?php echo $featured ? ' wpshadow-featured' : ''; ?>">
					<?php if ( $featured ) : ?>
						<span class="wpshadow-featured-badge"><?php esc_html_e( 'Popular', 'wpshadow' ); ?></span>
					<?php endif; ?>
					
					<h3 class="wpshadow-plan-title"><?php echo esc_html( $title ); ?></h3>
					
					<div class="wpshadow-plan-price">
						<span class="wpshadow-currency"><?php echo esc_html( $currency_symbol ); ?></span>
						<span class="wpshadow-amount"><?php echo esc_html( $price ); ?></span>
						<?php if ( $period ) : ?>
							<span class="wpshadow-period"><?php echo esc_html( $period ); ?></span>
						<?php endif; ?>
					</div>
					
					<ul class="wpshadow-plan-features">
						<?php foreach ( $features as $feature ) : ?>
							<li>
								<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
								<?php echo esc_html( $feature ); ?>
							</li>
						<?php endforeach; ?>
					</ul>
					
					<a href="<?php echo esc_url( $button_url ); ?>" class="wpshadow-plan-button">
						<?php echo esc_html( $button_text ); ?>
					</a>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}
