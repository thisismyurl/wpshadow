<?php
/**
 * Mobile JavaScript Bundle Size Detection
 *
 * Detects JavaScript bundles too large for mobile networks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile JavaScript Bundle Size Detection
 *
 * Monitors total JavaScript size and identifies unused code that
 * should be split or removed for faster mobile loading.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Mobile_JS_Bundle_Size extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-js-bundle-size';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile JavaScript Bundle Size Detection';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detects JavaScript bundles too large for mobile';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$bundle_info = self::analyze_js_bundle();

		if ( empty( $bundle_info['total_size'] ) || $bundle_info['total_size'] < 250 * 1024 ) {
			return null; // Bundle within acceptable size
		}

		$threat = 70;
		if ( $bundle_info['total_size'] > 500 * 1024 ) {
			$threat = 85; // Critical - very large
		}

		return array(
			'id'                     => self::$slug,
			'title'                  => self::$title,
			'description'            => sprintf(
				/* translators: %s: bundle size in KB */
				__( 'Total JavaScript bundle is %s KB (>250KB recommended)', 'wpshadow' ),
				round( $bundle_info['total_size'] / 1024, 1 )
			),
			'severity'               => 'high',
			'threat_level'           => $threat,
			'total_js_size'          => $bundle_info['total_size'],
			'total_js_size_formatted' => size_format( $bundle_info['total_size'], 1 ),
			'script_count'           => $bundle_info['script_count'] ?? 0,
			'largest_scripts'        => array_slice( $bundle_info['scripts'] ?? array(), 0, 5 ),
			'optimization_potential' => round( ( $bundle_info['total_size'] - 250 * 1024 ) / 1024, 1 ) . ' KB savings possible',
			'user_impact'            => __( 'Large bundles slow download on 3G (11s delay)', 'wpshadow' ),
			'auto_fixable'           => false,
			'kb_link'                => 'https://wpshadow.com/kb/js-bundle-size?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}

	/**
	 * Analyze JavaScript bundle size.
	 *
	 * @since 0.6093.1200
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
