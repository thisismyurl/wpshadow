<?php
/**
 * Tree Shaking Detection Diagnostic
 *
 * Detects dead code elimination and tree-shaking implementation.
 *
 * @since   1.6033.2115
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tree Shaking Detection Diagnostic
 *
 * Analyzes JavaScript for tree-shaking and dead code elimination.
 *
 * @since 1.6033.2115
 */
class Diagnostic_Tree_Shaking_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tree-shaking-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tree Shaking Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects JavaScript tree-shaking and dead code elimination';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2115
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_scripts;

		if ( ! isset( $wp_scripts->registered ) ) {
			return null;
		}

		// Check for common libraries that benefit from tree-shaking
		$large_libraries = array(
			'lodash'  => false,
			'moment'  => false,
			'axios'   => false,
			'gsap'    => false,
			'chart'   => false,
		);

		$detected_libraries = array();
		$total_scripts      = 0;

		foreach ( $wp_scripts->registered as $handle => $script ) {
			if ( ! $wp_scripts->query( $handle ) ) {
				continue;
			}

			$total_scripts++;

			// Check for library presence
			foreach ( $large_libraries as $library => $detected ) {
				if ( is_string( $handle ) && strpos( $handle, $library ) !== false || 
				     ( isset( $script->src ) && is_string( $script->src ) && strpos( $script->src, $library ) !== false ) ) {
					$large_libraries[ $library ] = true;
					$detected_libraries[]         = $library;
				}
			}
		}

		// Check for ES modules support (indicates tree-shaking capability)
		$has_esm_support = false;
		foreach ( $wp_scripts->registered as $handle => $script ) {
			if ( isset( $script->extra['type'] ) && $script->extra['type'] === 'module' ) {
				$has_esm_support = true;
				break;
			}
		}

		// Generate findings if large libraries detected without tree-shaking
		if ( ! empty( $detected_libraries ) && ! $has_esm_support ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: comma-separated list of libraries */
					__( 'Large JavaScript libraries detected (%s) without ES module tree-shaking. Consider using modular imports.', 'wpshadow' ),
					implode( ', ', $detected_libraries )
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/tree-shaking-detection',
				'meta'         => array(
					'detected_libraries' => $detected_libraries,
					'library_count'      => count( $detected_libraries ),
					'has_esm_support'    => $has_esm_support,
					'total_scripts'      => $total_scripts,
					'recommendation'     => 'Use ES6 imports with webpack/rollup for tree-shaking',
					'impact_estimate'    => '20-40% bundle size reduction potential',
					'typical_savings'    => array(
						'lodash'  => '~50 KB → ~10 KB with tree-shaking',
						'moment'  => '~67 KB → ~15 KB with date-fns',
						'axios'   => '~13 KB (minimal tree-shaking benefit)',
					),
				),
			);
		}

		return null;
	}
}
