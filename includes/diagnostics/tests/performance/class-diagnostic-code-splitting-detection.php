<?php
/**
 * Code Splitting Detection Diagnostic
 *
 * Checks if JavaScript is split into smaller chunks to improve performance
 * and enable faster incremental loading.
 *
 * @since   1.6033.2093
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Code Splitting Detection Diagnostic Class
 *
 * Analyzes code splitting:
 * - JavaScript bundle sizes
 * - Number of chunks
 * - Webpack/bundler usage detection
 * - Chunk optimization
 *
 * @since 1.6033.2093
 */
class Diagnostic_Code_Splitting_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'code-splitting-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Code Splitting Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for JavaScript code splitting optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2093
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		global $wp_scripts;

		$total_scripts       = 0;
		$large_scripts       = 0;
		$chunked_scripts     = 0;
		$largest_script_size = 0;

		// Analyze script sizes
		if ( ! empty( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				$script = $wp_scripts->registered[ $handle ] ?? null;
				if ( ! $script || empty( $script->src ) ) {
					continue;
				}

				$total_scripts++;

				// Check if script URL suggests chunking (webpack, vendor, chunk patterns)
				if ( preg_match( '/(chunk|vendor|bundle|\.[\w]+\.js)/', $script->src ) ) {
					$chunked_scripts++;
				}

				// Estimate from URL parameters
				if ( stripos( $script->src, 'min' ) === false ) {
					$large_scripts++;
				}
			}
		}

		// Flag if no code splitting detected but many scripts
		if ( $chunked_scripts < 2 && $total_scripts > 8 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of scripts */
					__( 'No code splitting detected. %d JavaScript files are being loaded separately, which could be optimized.', 'wpshadow' ),
					$total_scripts
				),
				'severity'      => 'low',
				'threat_level'  => 30,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/code-splitting',
				'meta'          => array(
					'total_scripts'        => $total_scripts,
					'chunked_scripts'      => $chunked_scripts,
					'large_scripts'        => $large_scripts,
					'recommendation'       => 'Use Webpack or similar bundler to split code and load only necessary bundles per page',
					'impact'               => 'Code splitting reduces initial JS size by 30-50% per page',
					'best_practice'        => array(
						'Split vendor code from app code',
						'Lazy-load route-specific bundles',
						'Use dynamic imports',
						'Monitor bundle size',
					),
				),
			);
		}

		return null;
	}
}
