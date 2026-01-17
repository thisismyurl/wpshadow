<?php
/**
 * Favicon & Touch Icon Checker feature.
 *
 * Verifies that site icons display correctly across browsers and devices.
 * Checks for proper favicon formats, dimensions, and aspect ratios.
 *
 * @package WPShadow\CoreSupport
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
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'category'           => 'diagnostics',
				'icon'               => 'dashicons-format-image',
				'priority'           => 20,
			)
		);
	}

	/**
	 * Enable details page for this feature.
	 *
	 * @return bool
	 */
	public function has_details_page(): bool {
		return true;
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			// Clean up when disabled.
			$this->cleanup();
			return;
		}

		// Schedule daily check.
		if ( ! wp_next_scheduled( 'wpshadow_favicon_daily_check' ) ) {
			wp_schedule_event( time(), 'daily', 'wpshadow_favicon_daily_check' );
		}
		add_action( 'wpshadow_favicon_daily_check', array( $this, 'run_daily_check' ) );

		// Register Site Health test (uses cached results).
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		$this->log_activity( 'feature_initialized', 'Favicon Checker initialized', 'info' );
	}

	/**
	 * Clean up scheduled events and cache when feature is disabled.
	 *
	 * @return void
	 */
	private function cleanup(): void {
		$timestamp = wp_next_scheduled( 'wpshadow_favicon_daily_check' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'wpshadow_favicon_daily_check' );
		}
		delete_transient( 'wpshadow_favicon_check_results' );
	}

	/**
	 * Run daily favicon check and cache results.
	 *
	 * @return void
	 */
	public function run_daily_check(): void {
		$check = $this->check_icons();
		
		// Cache results for 24 hours.
		set_transient( 'wpshadow_favicon_check_results', $check, DAY_IN_SECONDS );
		
		// Log the check result.
		$log_message = sprintf(
			'Favicon check completed: %s',
			$check['status']
		);
		$this->log_activity( 'daily_check', $log_message, $check['status'] === 'ok' ? 'info' : 'warning' );
	}

	/**
	 * Check site icons and return diagnostic information.
	 * Uses cached results if available, otherwise performs fresh check.
	 *
	 * @param bool $force Force fresh check, bypassing cache.
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
	public function check_icons( bool $force = false ): array {
		// Check cache first unless forced.
		if ( ! $force ) {
			$cached = get_transient( 'wpshadow_favicon_check_results' );
			if ( false !== $cached && is_array( $cached ) ) {
				return $cached;
			}
		}

		// Perform fresh check.
		$result = $this->perform_icon_check();
		
		// Cache for 24 hours.
		set_transient( 'wpshadow_favicon_check_results', $result, DAY_IN_SECONDS );
		
		return $result;
	}

	/**
	 * Perform actual icon check (no caching).
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
	private function perform_icon_check(): array {
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
	 * Uses shared widget rendering functions from wps-widget-functions.php
	 * to ensure consistent HTML markup across all features.
	 *
	 * @return void
	 */
	public function render_widget(): void {
		$check = $this->check_icons();

		?>
		<div class="wpshadow-favicon-checker">
			<?php
			// Render status notice using shared function.
			WPSHADOW_render_widget_status_notice(
				$check['status'],
				$check['message']
			);

			// Render issues list if any exist.
			WPSHADOW_render_widget_list(
				__( 'Issues Found:', 'plugin-wpshadow' ),
				$check['issues'],
				'wpshadow-issues'
			);

			// Render recommendations list if any exist.
			WPSHADOW_render_widget_list(
				__( 'Recommendations:', 'plugin-wpshadow' ),
				$check['recommendations'],
				'wpshadow-recommendations'
			);

			// Render icon status table.
			?>
			<div class="wpshadow-icon-details">
				<?php
				WPSHADOW_render_widget_table_open( __( 'Icon Status:', 'plugin-wpshadow' ) );

				// WordPress Site Icon row.
				$site_icon_content = '';
				if ( $check['icons']['site_icon']['exists'] ) {
					$site_icon_content .= WPSHADOW_render_status_indicator(
						'active',
						__( 'Set', 'plugin-wpshadow' )
					);

					if ( $check['icons']['site_icon']['url'] ) {
						$site_icon_content .= sprintf(
							'<div style="margin-top: 5px;"><img src="%s" alt="%s" style="max-width: 64px; max-height: 64px; border: 1px solid #ddd;" /></div>',
							esc_url( $check['icons']['site_icon']['url'] ),
							esc_attr__( 'Site Icon', 'plugin-wpshadow' )
						);
					}

					if ( $check['icons']['site_icon']['dimensions'] ) {
						$dimensions = sprintf(
							/* translators: %1$d: width, %2$d: height */
							__( 'Dimensions: %1$dx%2$d', 'plugin-wpshadow' ),
							(int) $check['icons']['site_icon']['dimensions']['width'],
							(int) $check['icons']['site_icon']['dimensions']['height']
						);

						if ( $check['icons']['site_icon']['aspect_ratio'] ) {
							$dimensions .= ' (' . esc_html( $check['icons']['site_icon']['aspect_ratio'] ) . ')';
						}

						$site_icon_content .= WPSHADOW_render_metadata( $dimensions );
					}
				} else {
					$site_icon_content = WPSHADOW_render_status_indicator(
						'error',
						__( 'Not Set', 'plugin-wpshadow' )
					);
				}

				WPSHADOW_render_widget_table_row(
					__( 'WordPress Site Icon', 'plugin-wpshadow' ),
					$site_icon_content
				);

				// Legacy Favicon.ico row.
				$favicon_content = $check['icons']['favicon']['exists']
					? WPSHADOW_render_status_indicator( 'active', __( 'Present', 'plugin-wpshadow' ) )
					: WPSHADOW_render_status_indicator( 'inactive', __( 'Not Found', 'plugin-wpshadow' ) );

				WPSHADOW_render_widget_table_row(
					__( 'Legacy Favicon.ico', 'plugin-wpshadow' ),
					$favicon_content,
					true // Alternate row coloring
				);

				// Touch Icons row.
				$touch_icon_content = '';
				if ( ! empty( $check['icons']['touch_icons'] ) ) {
					$touch_icon_content .= WPSHADOW_render_status_indicator(
						'active',
						count( $check['icons']['touch_icons'] ) . ' ' . __( 'sizes', 'plugin-wpshadow' )
					);

					$sizes = array_map(
						function ( $icon ) {
							return $icon['sizes'];
						},
						$check['icons']['touch_icons']
					);

					$touch_icon_content .= WPSHADOW_render_metadata( implode( ', ', $sizes ) );
				} else {
					$touch_icon_content = WPSHADOW_render_status_indicator(
						'inactive',
						__( 'None', 'plugin-wpshadow' )
					);
				}

				WPSHADOW_render_widget_table_row(
					__( 'Generated Touch Icons', 'plugin-wpshadow' ),
					$touch_icon_content
				);

				WPSHADOW_render_widget_table_close();
				?>
			</div>

			<?php
			// Render action buttons.
			WPSHADOW_render_widget_actions(
				array(
					array(
						'label' => __( 'Set Site Icon', 'plugin-wpshadow' ),
						'url'   => admin_url( 'customize.php?autofocus[section]=title_tagline' ),
						'type'  => 'primary',
					),
					array(
						'label'  => __( 'Learn More About Favicons', 'plugin-wpshadow' ),
						'url'    => 'https://developers.google.com/search/docs/appearance/favicon-in-search',
						'target' => '_blank',
						'rel'    => 'noopener noreferrer',
						'type'   => 'secondary',
					),
				)
			);
			?>
		</div>
		<?php
	}
}
