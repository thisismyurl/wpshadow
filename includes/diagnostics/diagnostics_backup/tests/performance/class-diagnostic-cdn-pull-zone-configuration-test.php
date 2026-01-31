<?php
/**
 * CDN Pull Zone Configuration Test Diagnostic
 *
 * Validates CDN is properly configured for static asset delivery.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CDN Pull Zone Configuration Test Class
 *
 * Tests whether CDN is properly configured.
 *
 * @since 1.26028.1905
 */
class Diagnostic_CDN_Pull_Zone_Configuration_Test extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cdn-pull-zone-configuration-test';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CDN Pull Zone Configuration Test';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates CDN is properly configured for static asset delivery';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if CDN is configured.
		$cdn_config = self::detect_cdn_configuration();
		
		if ( ! $cdn_config['cdn_detected'] ) {
			// No CDN is not necessarily an issue for low-traffic sites.
			return null;
		}

		$issues = array();

		// CDN is configured, check if it's working properly.
		if ( ! $cdn_config['urls_rewritten'] ) {
			$issues[] = __( 'CDN configured but URLs not being rewritten (static assets still served from origin)', 'wpshadow' );
		}

		// Check if CDN is actually serving files.
		if ( $cdn_config['urls_rewritten'] && ! self::cdn_serving_files( $cdn_config['cdn_url'] ) ) {
			$issues[] = __( 'CDN URLs found but CDN not responding (assets may be broken)', 'wpshadow' );
		}

		// Check for SSL mismatch.
		if ( is_ssl() && $cdn_config['cdn_url'] && 0 === strpos( $cdn_config['cdn_url'], 'http://' ) ) {
			$issues[] = __( 'Site uses HTTPS but CDN uses HTTP (mixed content warning)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cdn-pull-zone-configuration-test',
				'meta'         => array(
					'cdn_detected'    => $cdn_config['cdn_detected'],
					'cdn_plugin'      => $cdn_config['cdn_plugin'],
					'cdn_url'         => $cdn_config['cdn_url'],
					'urls_rewritten'  => $cdn_config['urls_rewritten'],
					'issues_found'    => count( $issues ),
				),
			);
		}

		return null;
	}

	/**
	 * Detect CDN configuration.
	 *
	 * @since  1.26028.1905
	 * @return array CDN configuration details.
	 */
	private static function detect_cdn_configuration() {
		$config = array(
			'cdn_detected'   => false,
			'cdn_plugin'     => false,
			'cdn_url'        => '',
			'urls_rewritten' => false,
		);

		// Check for common CDN plugins.
		$cdn_plugins = array(
			'wp-rocket/wp-rocket.php'              => array(
				'option' => 'wp_rocket_settings',
				'key'    => 'cdn',
			),
			'w3-total-cache/w3-total-cache.php'    => array(
				'option' => 'w3tc_cdn.configuration',
				'key'    => 'enabled',
			),
			'wp-fastest-cache/wpFastestCache.php'  => array(
				'option' => 'WpFastestCache',
				'key'    => 'wpFastestCacheCDN',
			),
			'cdn-enabler/cdn-enabler.php'          => array(
				'option' => 'cdn_enabler',
				'key'    => 'url',
			),
		);

		foreach ( $cdn_plugins as $plugin => $settings ) {
			if ( is_plugin_active( $plugin ) ) {
				$config['cdn_detected'] = true;
				$config['cdn_plugin'] = basename( dirname( $plugin ) );

				// Try to get CDN URL from plugin settings.
				$plugin_settings = get_option( $settings['option'] );
				if ( is_array( $plugin_settings ) && isset( $plugin_settings[ $settings['key'] ] ) ) {
					if ( is_string( $plugin_settings[ $settings['key'] ] ) && ! empty( $plugin_settings[ $settings['key'] ] ) ) {
						$config['cdn_url'] = $plugin_settings[ $settings['key'] ];
					}
				}
				break;
			}
		}

		// Check for Jetpack Site Accelerator.
		if ( class_exists( 'Jetpack' ) && method_exists( 'Jetpack', 'is_module_active' ) ) {
			if ( Jetpack::is_module_active( 'photon' ) ) {
				$config['cdn_detected'] = true;
				$config['cdn_plugin'] = 'Jetpack Photon';
				$config['cdn_url'] = 'i0.wp.com';
			}
		}

		// Check if CDN URLs are actually being used in output.
		if ( $config['cdn_url'] ) {
			$config['urls_rewritten'] = self::check_cdn_urls_in_output( $config['cdn_url'] );
		}

		return $config;
	}

	/**
	 * Check if CDN URLs appear in page output.
	 *
	 * @since  1.26028.1905
	 * @param  string $cdn_url CDN URL to check for.
	 * @return bool True if CDN URLs found in output.
	 */
	private static function check_cdn_urls_in_output( $cdn_url ) {
		global $wp_scripts, $wp_styles;

		// Check if any enqueued scripts use CDN URL.
		if ( ! empty( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $script ) {
				if ( ! empty( $script->src ) && false !== strpos( $script->src, $cdn_url ) ) {
					return true;
				}
			}
		}

		// Check if any enqueued styles use CDN URL.
		if ( ! empty( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $style ) {
				if ( ! empty( $style->src ) && false !== strpos( $style->src, $cdn_url ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Test if CDN is actually serving files.
	 *
	 * @since  1.26028.1905
	 * @param  string $cdn_url CDN URL to test.
	 * @return bool True if CDN is responding.
	 */
	private static function cdn_serving_files( $cdn_url ) {
		if ( empty( $cdn_url ) ) {
			return false;
		}

		// Construct a test URL.
		$test_url = $cdn_url;
		if ( ! preg_match( '#^https?://#', $test_url ) ) {
			$test_url = ( is_ssl() ? 'https://' : 'http://' ) . $test_url;
		}

		// Try to fetch from CDN.
		$response = wp_remote_head( $test_url, array( 'timeout' => 5 ) );
		
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		return $status_code >= 200 && $status_code < 400;
	}
}
