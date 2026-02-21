<?php
/**
 * Mobile JavaScript Bundle Size Detection
 *
 * Detects JavaScript bundles too large for mobile networks.
 *
 * @package    WPShadow
 * @subpackage Treatments\Performance
 * @since      1.602.1600
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile JavaScript Bundle Size Detection
 *
 * Monitors total JavaScript size and identifies unused code that
 * should be split or removed for faster mobile loading.
 *
 * @since 1.602.1600
 */
class Treatment_Mobile_JS_Bundle_Size extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-js-bundle-size';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile JavaScript Bundle Size Detection';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Detects JavaScript bundles too large for mobile';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.602.1600
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_JS_Bundle_Size' );
	}

	/**
	 * Analyze JavaScript bundle size.
	 *
	 * @since  1.602.1600
	 * @return array Bundle information.
	 */
	private static function analyze_js_bundle(): array {
		global $wp_scripts;

		$info = array(
			'total_size'   => 0,
			'script_count' => 0,
			'scripts'      => array(),
		);

		if ( ! isset( $wp_scripts ) || ! is_object( $wp_scripts ) ) {
			return $info;
		}

		$scripts = array();

		foreach ( $wp_scripts->queue as $handle ) {
			if ( isset( $wp_scripts->registered[ $handle ] ) ) {
				$script = $wp_scripts->registered[ $handle ];
				if ( empty( $script->src ) ) {
					continue;
				}

				$src = $script->src;

				// Handle relative URLs
				if ( 0 === strpos( $src, '/' ) ) {
					$src = ABSPATH . substr( $src, 1 );
				} elseif ( 0 === strpos( $src, 'http' ) ) {
					// Skip external scripts - estimate size
					if ( strpos( $src, home_url() ) !== false ) {
						$src = str_replace( home_url(), ABSPATH, $src );
					} else {
						// Estimate external script size (average 30KB)
						$info['total_size'] += 30 * 1024;
						$scripts[ $handle ] = 30 * 1024;
						continue;
					}
				}

				// Check local file
				if ( file_exists( $src ) ) {
					$size = filesize( $src );
					$info['total_size'] += $size;
					$scripts[ $handle ] = $size;
				}
			}
		}

		// Sort by size descending
		arsort( $scripts );

		$info['script_count'] = count( $scripts );
		$info['scripts']       = array_map(
			function ( $handle, $size ) {
				return array(
					'handle'     => $handle,
					'size'       => $size,
					'size_kb'    => round( $size / 1024, 1 ),
				);
			},
			array_keys( $scripts ),
			array_values( $scripts )
		);

		return $info;
	}
}
