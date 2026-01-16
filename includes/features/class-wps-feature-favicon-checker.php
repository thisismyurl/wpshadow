<?php
/**
 * Favicon & Touch Icon Checker feature.
 *
 * Verifies that site icons display correctly across browsers and devices.
 * Checks for proper favicon formats, dimensions, and aspect ratios.
 *
 * @package WPSHADOW_SUPPORT
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Favicon & Touch Icon Checker feature class.
 */
class WPSHADOW_Feature_Favicon_Checker extends WPSHADOW_Abstract_Feature {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'wpshadow_favicon_checker',
				'name'               => __( 'Favicon & Touch Icon Checker', 'plugin-wpshadow' ),
				'description'        => __( 'Verifies that your site icon displays correctly across all major browsers, desktop tabs, and mobile home screens. Google Search results also rely on stable, properly formatted (1:1 aspect ratio) favicons for site identity.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'version'            => '1.0.0',
				'default_enabled'    => true,
				'widget_group'       => 'diagnostics',
				'widget_label'       => __( 'Site Identity & SEO', 'plugin-wpshadow' ),
				'widget_description' => __( 'Site icon and identity features', 'plugin-wpshadow' ),
				'category'           => 'diagnostics',
				'icon'               => 'dashicons-format-image',
				'priority'           => 20,
			)
		);
	}

	/**
	 * Check site icons and return diagnostic information.
	 *
	 * @return array{
	 *     status: string,
	 *     message: string,
	 *     issues: array<string>,
	 *     recommendations: array<string>,
	 *     icons: array{
	 *         site_icon: array{exists: bool, url: string|null, dimensions: array{width: int, height: int}|null, aspect_ratio: string|null},
	 *         favicon: array{exists: bool, url: string|null},
	 *         touch_icons: array<array{rel: string, sizes: string|null, url: string}>
	 *     }
	 * }
	 */
	public function check_icons(): array {
		$result = array(
			'status'          => 'ok',
			'message'         => __( 'All site icons are properly configured.', 'plugin-wpshadow' ),
			'issues'          => array(),
			'recommendations' => array(),
			'icons'           => array(
				'site_icon'   => array(
					'exists'       => false,
					'url'          => null,
					'dimensions'   => null,
					'aspect_ratio' => null,
				),
				'favicon'     => array(
					'exists' => false,
					'url'    => null,
				),
				'touch_icons' => array(),
			),
		);

		// Check WordPress Site Icon.
		$site_icon_id = get_option( 'site_icon' );
		if ( $site_icon_id ) {
			$icon_url = wp_get_attachment_image_url( (int) $site_icon_id, 'full' );
			if ( $icon_url ) {
				$result['icons']['site_icon']['exists'] = true;
				$result['icons']['site_icon']['url']    = $icon_url;

				// Get image dimensions.
				$image_meta = wp_get_attachment_metadata( (int) $site_icon_id );
				if ( $image_meta && isset( $image_meta['width'], $image_meta['height'] ) ) {
					$width  = (int) $image_meta['width'];
					$height = (int) $image_meta['height'];

					$result['icons']['site_icon']['dimensions'] = array(
						'width'  => $width,
						'height' => $height,
					);

					// Check aspect ratio.
					if ( $width !== $height ) {
						$result['issues'][] = sprintf(
							/* translators: %1$d: width, %2$d: height */
							__( 'Site icon has non-square dimensions (%1$dx%2$d). Icons should have a 1:1 aspect ratio for best display across browsers and devices.', 'plugin-wpshadow' ),
							$width,
							$height
						);
						$result['recommendations'][] = __( 'Upload a square image (e.g., 512x512 pixels) as your site icon for optimal display.', 'plugin-wpshadow' );
						$result['status']            = 'warning';
					} else {
						$result['icons']['site_icon']['aspect_ratio'] = '1:1';
					}

					// Check minimum recommended size.
					if ( $width < 512 || $height < 512 ) {
						$result['issues'][] = sprintf(
							/* translators: %1$d: width, %2$d: height */
							__( 'Site icon dimensions (%1$dx%2$d) are below the recommended minimum of 512x512 pixels.', 'plugin-wpshadow' ),
							$width,
							$height
						);
						$result['recommendations'][] = __( 'For best quality, use a site icon that is at least 512x512 pixels. WordPress will automatically generate smaller sizes.', 'plugin-wpshadow' );
						if ( 'ok' === $result['status'] ) {
							$result['status'] = 'warning';
						}
					}
				}
			}
		} else {
			$result['issues'][]          = __( 'No site icon is set in WordPress.', 'plugin-wpshadow' );
			$result['recommendations'][] = __( 'Set a site icon in Appearance → Customize → Site Identity → Site Icon.', 'plugin-wpshadow' );
			$result['status']            = 'error';
		}

		// Check for legacy favicon.ico in root directory.
		$favicon_exists = file_exists( ABSPATH . 'favicon.ico' );
		if ( $favicon_exists ) {
			$result['icons']['favicon']['exists'] = true;
			$result['icons']['favicon']['url']    = home_url( '/favicon.ico' );
		}

		// Check for touch icon meta tags in head.
		$touch_icons = $this->detect_touch_icons();
		if ( ! empty( $touch_icons ) ) {
			$result['icons']['touch_icons'] = $touch_icons;
		}

		// Add recommendations if only legacy favicon is present.
		if ( ! $site_icon_id && $favicon_exists ) {
			$result['recommendations'][] = __( 'While a legacy favicon.ico exists, setting a Site Icon in WordPress provides better cross-platform support and automatic generation of all required icon sizes.', 'plugin-wpshadow' );
		}

		// Final status message.
		if ( 'error' === $result['status'] ) {
			$result['message'] = __( 'Site icon configuration needs attention.', 'plugin-wpshadow' );
		} elseif ( 'warning' === $result['status'] ) {
			$result['message'] = __( 'Site icon is present but could be improved.', 'plugin-wpshadow' );
		}

		return $result;
	}

	/**
	 * Detect touch icon meta tags from the site's head.
	 *
	 * @return array<array{rel: string, sizes: string|null, url: string}>
	 */
	private function detect_touch_icons(): array {
		$touch_icons = array();

		// WordPress doesn't provide a direct API to scan head tags, so we check common patterns.
		// Check if WordPress core is generating these via site_icon.
		$site_icon_id = get_option( 'site_icon' );
		if ( $site_icon_id ) {
			// WordPress generates these sizes: 32x32, 192x192, 180x180, 270x270.
			$common_sizes = array( 32, 192, 180, 270 );
			foreach ( $common_sizes as $size ) {
				$icon_url = wp_get_attachment_image_url( (int) $site_icon_id, array( $size, $size ) );
				if ( $icon_url ) {
					$rel = 'icon';
					if ( 180 === $size ) {
						$rel = 'apple-touch-icon';
					}
					$touch_icons[] = array(
						'rel'   => $rel,
						'sizes' => $size . 'x' . $size,
						'url'   => $icon_url,
					);
				}
			}
		}

		return $touch_icons;
	}

	/**
	 * Render the dashboard widget content.
	 *
	 * @return void
	 */
	public function render_widget(): void {
		$check = $this->check_icons();

		$status_class = 'notice-success';
		$status_icon  = '✓';
		if ( 'warning' === $check['status'] ) {
			$status_class = 'notice-warning';
			$status_icon  = '⚠';
		} elseif ( 'error' === $check['status'] ) {
			$status_class = 'notice-error';
			$status_icon  = '✕';
		}

		?>
		<div class="wpshadow-favicon-checker">
			<div class="notice <?php echo esc_attr( $status_class ); ?> inline" style="margin: 0 0 15px 0; padding: 8px 12px;">
				<p style="margin: 0;">
					<strong><?php echo esc_html( $status_icon ); ?> <?php echo esc_html( $check['message'] ); ?></strong>
				</p>
			</div>

			<?php if ( ! empty( $check['issues'] ) ) : ?>
				<div class="wpshadow-issues" style="margin-bottom: 15px;">
					<h4 style="margin-top: 0;"><?php esc_html_e( 'Issues Found:', 'plugin-wpshadow' ); ?></h4>
					<ul style="margin-left: 20px; list-style: disc;">
						<?php foreach ( $check['issues'] as $issue ) : ?>
							<li><?php echo esc_html( $issue ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $check['recommendations'] ) ) : ?>
				<div class="wpshadow-recommendations" style="margin-bottom: 15px;">
					<h4 style="margin-top: 0;"><?php esc_html_e( 'Recommendations:', 'plugin-wpshadow' ); ?></h4>
					<ul style="margin-left: 20px; list-style: disc;">
						<?php foreach ( $check['recommendations'] as $recommendation ) : ?>
							<li><?php echo esc_html( $recommendation ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<div class="wpshadow-icon-details">
				<h4 style="margin-top: 0;"><?php esc_html_e( 'Icon Status:', 'plugin-wpshadow' ); ?></h4>
				
				<table class="widefat" style="border: 1px solid #ccd0d4;">
					<tbody>
						<tr>
							<td style="padding: 8px; width: 40%;"><strong><?php esc_html_e( 'WordPress Site Icon', 'plugin-wpshadow' ); ?></strong></td>
							<td style="padding: 8px;">
								<?php if ( $check['icons']['site_icon']['exists'] ) : ?>
									<span style="color: #46b450;">✓ <?php esc_html_e( 'Set', 'plugin-wpshadow' ); ?></span>
									<?php if ( $check['icons']['site_icon']['url'] ) : ?>
										<div style="margin-top: 5px;">
											<img src="<?php echo esc_url( $check['icons']['site_icon']['url'] ); ?>" alt="<?php esc_attr_e( 'Site Icon', 'plugin-wpshadow' ); ?>" style="max-width: 64px; max-height: 64px; border: 1px solid #ddd;" />
										</div>
									<?php endif; ?>
									<?php if ( $check['icons']['site_icon']['dimensions'] ) : ?>
										<div style="margin-top: 5px; font-size: 0.9em; color: #666;">
											<?php
											printf(
												/* translators: %1$d: width, %2$d: height */
												esc_html__( 'Dimensions: %1$dx%2$d', 'plugin-wpshadow' ),
												(int) $check['icons']['site_icon']['dimensions']['width'],
												(int) $check['icons']['site_icon']['dimensions']['height']
											);
											?>
											<?php if ( $check['icons']['site_icon']['aspect_ratio'] ) : ?>
												(<?php echo esc_html( $check['icons']['site_icon']['aspect_ratio'] ); ?>)
											<?php endif; ?>
										</div>
									<?php endif; ?>
								<?php else : ?>
									<span style="color: #dc3232;">✕ <?php esc_html_e( 'Not Set', 'plugin-wpshadow' ); ?></span>
								<?php endif; ?>
							</td>
						</tr>
						<tr style="background-color: #f9f9f9;">
							<td style="padding: 8px;"><strong><?php esc_html_e( 'Legacy Favicon.ico', 'plugin-wpshadow' ); ?></strong></td>
							<td style="padding: 8px;">
								<?php if ( $check['icons']['favicon']['exists'] ) : ?>
									<span style="color: #46b450;">✓ <?php esc_html_e( 'Present', 'plugin-wpshadow' ); ?></span>
								<?php else : ?>
									<span style="color: #666;">— <?php esc_html_e( 'Not Found', 'plugin-wpshadow' ); ?></span>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td style="padding: 8px;"><strong><?php esc_html_e( 'Generated Touch Icons', 'plugin-wpshadow' ); ?></strong></td>
							<td style="padding: 8px;">
								<?php if ( ! empty( $check['icons']['touch_icons'] ) ) : ?>
									<span style="color: #46b450;">✓ <?php echo esc_html( count( $check['icons']['touch_icons'] ) ); ?> <?php esc_html_e( 'sizes', 'plugin-wpshadow' ); ?></span>
									<div style="margin-top: 5px; font-size: 0.9em; color: #666;">
										<?php
										$sizes = array_map(
											function ( $icon ) {
												return $icon['sizes'];
											},
											$check['icons']['touch_icons']
										);
										echo esc_html( implode( ', ', $sizes ) );
										?>
									</div>
								<?php else : ?>
									<span style="color: #666;">— <?php esc_html_e( 'None', 'plugin-wpshadow' ); ?></span>
								<?php endif; ?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="wpshadow-quick-actions" style="margin-top: 15px;">
				<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=title_tagline' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Set Site Icon', 'plugin-wpshadow' ); ?>
				</a>
				<a href="https://developers.google.com/search/docs/appearance/favicon-in-search" target="_blank" rel="noopener noreferrer" class="button button-secondary">
					<?php esc_html_e( 'Learn More About Favicons', 'plugin-wpshadow' ); ?>
				</a>
			</div>
		</div>
		<?php
	}
}
