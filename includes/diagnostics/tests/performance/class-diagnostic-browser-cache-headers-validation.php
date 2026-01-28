<?php
/**
 * Browser Cache Headers Validation Diagnostic
 *
 * Checks if proper Cache-Control headers are set for static assets.
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
 * Browser Cache Headers Validation Class
 *
 * Tests whether proper Cache-Control headers are set for static assets.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Browser_Cache_Headers_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'browser-cache-headers-validation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Browser Cache Headers Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if proper Cache-Control headers are set for static assets';

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
		$issues = array();
		$test_results = array();

		// Test various asset types.
		$asset_tests = array(
			'css'   => self::test_asset_caching( 'css' ),
			'js'    => self::test_asset_caching( 'js' ),
			'image' => self::test_asset_caching( 'image' ),
		);

		foreach ( $asset_tests as $type => $result ) {
			$test_results[ $type ] = $result;
			
			if ( ! $result['has_cache_headers'] ) {
				$issues[] = sprintf(
					/* translators: %s: asset type */
					__( 'No cache headers found for %s files', 'wpshadow' ),
					$type
				);
			} elseif ( $result['cache_duration'] < 604800 ) { // Less than 1 week.
				$issues[] = sprintf(
					/* translators: %s: asset type */
					__( 'Cache headers for %s files too short (recommend 1 year for versioned assets)', 'wpshadow' ),
					$type
				);
			}

			if ( ! $result['has_cache_busting'] ) {
				$issues[] = sprintf(
					/* translators: %s: asset type */
					__( 'No cache busting detected for %s files (query string or ETag)', 'wpshadow' ),
					$type
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/browser-cache-headers-validation',
				'meta'         => array(
					'test_results' => $test_results,
					'issues_found' => count( $issues ),
				),
			);
		}

		return null;
	}

	/**
	 * Test cache headers for a specific asset type.
	 *
	 * @since  1.26028.1905
	 * @param  string $type Asset type (css, js, image).
	 * @return array Test results.
	 */
	private static function test_asset_caching( $type ) {
		$result = array(
			'has_cache_headers'  => false,
			'cache_duration'     => 0,
			'has_cache_busting'  => false,
			'test_url'           => '',
		);

		// Find a sample asset to test.
		$test_url = self::find_sample_asset( $type );
		if ( ! $test_url ) {
			return $result;
		}

		$result['test_url'] = $test_url;

		// Use WordPress HTTP API to get headers.
		$response = wp_remote_head( $test_url, array( 'timeout' => 5 ) );
		
		if ( is_wp_error( $response ) ) {
			return $result;
		}

		$headers = wp_remote_retrieve_headers( $response );

		// Check for Cache-Control header.
		if ( isset( $headers['cache-control'] ) ) {
			$result['has_cache_headers'] = true;
			
			// Parse max-age from Cache-Control.
			if ( preg_match( '/max-age=(\d+)/', $headers['cache-control'], $matches ) ) {
				$result['cache_duration'] = (int) $matches[1];
			}
		}

		// Check for Expires header as fallback.
		if ( ! $result['has_cache_headers'] && isset( $headers['expires'] ) ) {
			$result['has_cache_headers'] = true;
			$expires_time = strtotime( $headers['expires'] );
			if ( $expires_time ) {
				$result['cache_duration'] = $expires_time - time();
			}
		}

		// Check for cache busting (query string or ETag).
		if ( false !== strpos( $test_url, '?ver=' ) || false !== strpos( $test_url, '&ver=' ) ) {
			$result['has_cache_busting'] = true;
		} elseif ( isset( $headers['etag'] ) ) {
			$result['has_cache_busting'] = true;
		}

		return $result;
	}

	/**
	 * Find a sample asset URL to test.
	 *
	 * @since  1.26028.1905
	 * @param  string $type Asset type (css, js, image).
	 * @return string|false Asset URL or false if not found.
	 */
	private static function find_sample_asset( $type ) {
		global $wp_scripts, $wp_styles;

		switch ( $type ) {
			case 'css':
				if ( ! empty( $wp_styles->registered ) ) {
					foreach ( $wp_styles->registered as $handle => $style ) {
						if ( ! empty( $style->src ) && false === strpos( $style->src, 'fonts.googleapis.com' ) ) {
							$url = $style->src;
							// Convert relative to absolute.
							if ( 0 === strpos( $url, '/' ) ) {
								$url = site_url( $url );
							}
							return $url;
						}
					}
				}
				break;

			case 'js':
				if ( ! empty( $wp_scripts->registered ) ) {
					foreach ( $wp_scripts->registered as $handle => $script ) {
						if ( ! empty( $script->src ) && false === strpos( $script->src, 'cdn' ) ) {
							$url = $script->src;
							// Convert relative to absolute.
							if ( 0 === strpos( $url, '/' ) ) {
								$url = site_url( $url );
							}
							return $url;
						}
					}
				}
				break;

			case 'image':
				// Try to find theme screenshot or logo.
				$theme = wp_get_theme();
				$screenshot = $theme->get_screenshot();
				if ( $screenshot ) {
					return $screenshot;
				}

				// Try custom logo.
				$custom_logo_id = get_theme_mod( 'custom_logo' );
				if ( $custom_logo_id ) {
					$logo_url = wp_get_attachment_image_src( $custom_logo_id, 'full' );
					if ( $logo_url ) {
						return $logo_url[0];
					}
				}

				// Fallback to WordPress logo.
				return admin_url( 'images/wordpress-logo.png' );
		}

		return false;
	}
}
