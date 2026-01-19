<?php declare(strict_types=1);
/**
 * Feature: Favicon & Touch Icon Checker
 *
 * Verifies that site icons display correctly across browsers and devices.
 * Checks for proper favicon formats, dimensions, and aspect ratios.
 *
 * @package    WPShadow\CoreSupport
 * @subpackage Features
 * @since      1.2601.75000
 */

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Favicon & Touch Icon Checker feature class.
 */
final class WPSHADOW_Feature_Favicon_Checker extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'favicon-checker',
				'name'               => __( 'Favicon & Touch Icon Checker', 'wpshadow' ),
				'description'        => __( 'Verifies that your site icon displays correctly across all major browsers, desktop tabs, and mobile home screens.', 'wpshadow' ),
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

	public function has_details_page(): bool {
		return true;
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			$this->cleanup();
			return;
		}

		if ( ! wp_next_scheduled( 'wpshadow_favicon_daily_check' ) ) {
			wp_schedule_event( time(), 'daily', 'wpshadow_favicon_daily_check' );
		}
		add_action( 'wpshadow_favicon_daily_check', array( $this, 'run_daily_check' ) );
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		$this->log_activity( 'feature_initialized', 'Favicon Checker initialized', 'info' );
	}

	/**
	 * Clean up scheduled events and cache when feature is disabled.
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
	 */
	public function run_daily_check(): void {
		$check = $this->check_icons();

		set_transient( 'wpshadow_favicon_check_results', $check, DAY_IN_SECONDS );

		$log_message = sprintf( 'Favicon check completed: %s', $check['status'] );
		$this->log_activity( 'daily_check', $log_message, 'ok' === $check['status'] ? 'info' : 'warning' );
	}

	/**
	 * Check site icons and return diagnostic information.
	 * Uses cached results if available, otherwise performs fresh check.
	 *
	 * @param bool $force Force fresh check, bypassing cache.
	 * @return array<string, mixed>
	 */
	public function check_icons( bool $force = false ): array {
		if ( ! $force ) {
			$cached = get_transient( 'wpshadow_favicon_check_results' );
			if ( false !== $cached && is_array( $cached ) ) {
				return $cached;
			}
		}

		$result = $this->perform_icon_check();

		set_transient( 'wpshadow_favicon_check_results', $result, DAY_IN_SECONDS );

		return $result;
	}

	/**
	 * Perform actual icon check (no caching).
	 *
	 * @return array<string, mixed>
	 */
	private function perform_icon_check(): array {
		$result = array(
			'status'          => 'ok',
			'message'         => __( 'All site icons are properly configured.', 'wpshadow' ),
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

		$site_icon_id = get_option( 'site_icon' );
		if ( $site_icon_id ) {
			$icon_url = wp_get_attachment_image_url( (int) $site_icon_id, 'full' );
			if ( $icon_url ) {
				$result['icons']['site_icon']['exists'] = true;
				$result['icons']['site_icon']['url']    = $icon_url;

				$image_meta = wp_get_attachment_metadata( (int) $site_icon_id );
				if ( $image_meta && isset( $image_meta['width'], $image_meta['height'] ) ) {
					$width  = (int) $image_meta['width'];
					$height = (int) $image_meta['height'];

					$result['icons']['site_icon']['dimensions'] = array(
						'width'  => $width,
						'height' => $height,
					);

					if ( $width !== $height ) {
						$result['issues'][] = sprintf(
							/* translators: %1$d: width, %2$d: height */
							__( 'Site icon has non-square dimensions (%1$dx%2$d). Icons should have a 1:1 aspect ratio for best display.', 'wpshadow' ),
							$width,
							$height
						);
						$result['recommendations'][] = __( 'Upload a square image (e.g., 512x512 pixels) as your site icon.', 'wpshadow' );
						$result['status'] = 'warning';
					} else {
						$result['icons']['site_icon']['aspect_ratio'] = '1:1';
					}

					if ( $width < 512 || $height < 512 ) {
						$result['issues'][] = sprintf(
							/* translators: %1$d: width, %2$d: height */
							__( 'Site icon dimensions (%1$dx%2$d) are below the recommended minimum of 512x512 pixels.', 'wpshadow' ),
							$width,
							$height
						);
						$result['recommendations'][] = __( 'For best quality, use a site icon that is at least 512x512 pixels.', 'wpshadow' );
						if ( 'ok' === $result['status'] ) {
							$result['status'] = 'warning';
						}
					}
				}
			}
		} else {
			$result['issues'][]          = __( 'No site icon is set in WordPress.', 'wpshadow' );
			$result['recommendations'][] = __( 'Set a site icon in Appearance → Customize → Site Identity → Site Icon.', 'wpshadow' );
			$result['status']            = 'error';
		}

		$favicon_exists = file_exists( ABSPATH . 'favicon.ico' );
		if ( $favicon_exists ) {
			$result['icons']['favicon']['exists'] = true;
			$result['icons']['favicon']['url']    = home_url( '/favicon.ico' );
		}

		$touch_icons = $this->detect_touch_icons();
		if ( ! empty( $touch_icons ) ) {
			$result['icons']['touch_icons'] = $touch_icons;
		}

		if ( ! $site_icon_id && $favicon_exists ) {
			$result['recommendations'][] = __( 'While a legacy favicon.ico exists, setting a Site Icon in WordPress provides better cross-platform support.', 'wpshadow' );
		}

		if ( 'error' === $result['status'] ) {
			$result['message'] = __( 'Site icon configuration needs attention.', 'wpshadow' );
		} elseif ( 'warning' === $result['status'] ) {
			$result['message'] = __( 'Site icon is present but could be improved.', 'wpshadow' );
		}

		return $result;
	}

	/**
	 * Detect touch icon meta tags from the site's head.
	 *
	 * @return array<array<string, mixed>>
	 */
	private function detect_touch_icons(): array {
		$touch_icons = array();

		$site_icon_id = get_option( 'site_icon' );
		if ( $site_icon_id ) {
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
	 * Register Site Health test.
	 *
	 * @param array<string, mixed> $tests Site Health tests.
	 * @return array<string, mixed>
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['favicon_checker'] = array(
			'label' => __( 'Favicon Status', 'wpshadow' ),
			'test'  => array( $this, 'site_health_test_callback' ),
		);

		return $tests;
	}

	/**
	 * Site Health test callback.
	 *
	 * @return array<string, mixed>
	 */
	public function site_health_test_callback(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Favicon Status', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Diagnostics', 'wpshadow' ),
					'color' => 'gray',
				),
				'description' => sprintf( '<p>%s</p>', __( 'Favicon monitoring is disabled.', 'wpshadow' ) ),
				'test'        => 'favicon_checker',
			);
		}

		$check = $this->check_icons();

		$status_map = array(
			'ok'      => 'good',
			'warning' => 'recommended',
			'error'   => 'critical',
		);

		$status = $status_map[ $check['status'] ] ?? 'recommended';

		return array(
			'label'       => __( 'Favicon Status', 'wpshadow' ),
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'Diagnostics', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf( '<p>%s</p>', $check['message'] ),
			'test'        => 'favicon_checker',
		);
	}
}
