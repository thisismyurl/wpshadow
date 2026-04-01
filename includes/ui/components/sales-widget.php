<?php
/**
 * Reusable Sales Widget for WPShadow
 *
 * @package WPShadow
 * @subpackage Views
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the WPShadow Pro sales widget.
 *
 * @param array $args {
 *     Optional. Array of arguments.
 *
 *     @type string $title      Widget title. Default 'Upgrade to WPShadow Pro'.
 *     @type string $description Widget description.
 *     @type array  $features   Array of feature strings to display.
 *     @type string $cta_text   Call-to-action button text. Default 'Learn More About WPShadow Pro'.
 *     @type string $cta_url    Call-to-action URL. Default 'https://wpshadow.com/pro'.
 *     @type string $icon       Dashicon class. Default 'dashicons-star-filled'.
 *     @type string $style      Widget style: 'default', 'compact', 'minimal'. Default 'default'.
 * }
 * @return void
 */
function wpshadow_render_sales_widget( $args = array() ) {
	$defaults = array(
		'title'       => __( 'Upgrade to WPShadow Pro', 'wpshadow' ),
		'description' => __( 'Get advanced features and priority support.', 'wpshadow' ),
		'features'    => array(),
		'cta_text'    => __( 'Learn More About WPShadow Pro', 'wpshadow' ),
		'cta_url'     => 'https://wpshadow.com/pro',
		'icon'        => 'dashicons-star-filled',
		'style'       => 'default',
	);

	$args = wp_parse_args( $args, $defaults );
	?>
	<div class="wpshadow-sales-widget wpshadow-sales-widget--<?php echo esc_attr( $args['style'] ); ?> wps-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 24px; margin-top: 24px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
		<div style="display: flex; align-items: flex-start; gap: 20px;">
			<div style="flex-shrink: 0;">
				<span class="dashicons <?php echo esc_attr( $args['icon'] ); ?>" style="font-size: 48px; width: 48px; height: 48px; color: rgba(255,255,255,0.9);"></span>
			</div>
			<div style="flex: 1;">
				<h3 style="margin: 0 0 8px 0; color: white; font-size: 20px;">
					<?php echo esc_html( $args['title'] ); ?>
				</h3>
				<?php if ( ! empty( $args['description'] ) ) : ?>
					<p style="margin: 0 0 16px 0; color: rgba(255,255,255,0.95); font-size: 14px;">
						<?php echo esc_html( $args['description'] ); ?>
					</p>
				<?php endif; ?>

				<?php if ( ! empty( $args['features'] ) ) : ?>
					<ul style="list-style: none; margin: 0 0 20px 0; padding: 0;">
						<?php foreach ( $args['features'] as $feature ) : ?>
							<li style="margin-bottom: 8px; padding-left: 24px; position: relative; color: rgba(255,255,255,0.95); font-size: 14px;">
								<span class="dashicons dashicons-yes" style="position: absolute; left: 0; top: 0; font-size: 18px; color: #4ade80;"></span>
								<?php echo esc_html( $feature ); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<a href="<?php echo esc_url( $args['cta_url'] ); ?>"
				   class="button button-primary button-hero"
				   target="_blank"
				   rel="noopener noreferrer"
				   style="background: white; color: #667eea; border-color: white; box-shadow: 0 2px 4px rgba(0,0,0,0.2); text-shadow: none; font-weight: 600;">
					<?php echo esc_html( $args['cta_text'] ); ?>
					<span class="dashicons dashicons-external" style="font-size: 16px; margin-top: 4px; margin-left: 4px;"></span>
				</a>
			</div>
		</div>
	</div>
	<?php
}
