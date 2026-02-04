<?php
/**
 * Polyfill Detection Diagnostic
 *
 * Detects unnecessary polyfills for modern browsers.
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
 * Polyfill Detection Diagnostic
 *
 * Identifies unnecessary JavaScript polyfills for modern browsers.
 *
 * @since 1.6033.2115
 */
class Diagnostic_Polyfill_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'polyfill-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Polyfill Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects unnecessary JavaScript polyfills for modern browsers';

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

		// Common polyfills that may be unnecessary
		$polyfill_patterns = array(
			'polyfill'       => 'Generic polyfill',
			'babel-polyfill' => 'Babel polyfill',
			'core-js'        => 'Core-js polyfill',
			'regenerator'    => 'Regenerator runtime',
			'promise'        => 'Promise polyfill',
			'fetch'          => 'Fetch API polyfill',
			'intersection-observer' => 'IntersectionObserver polyfill',
		);

		$detected_polyfills = array();
		$polyfill_size      = 0;

		foreach ( $wp_scripts->registered as $handle => $script ) {
			if ( ! $wp_scripts->is_enqueued( $handle ) ) {
				continue;
			}

			// Check for polyfill patterns in handle or source
			foreach ( $polyfill_patterns as $pattern => $name ) {
				if ( strpos( $handle, $pattern ) !== false || 
				     ( isset( $script->src ) && strpos( $script->src, $pattern ) !== false ) ) {
					$detected_polyfills[ $pattern ] = $name;

					// Estimate size for local scripts
					if ( isset( $script->src ) && strpos( $script->src, home_url() ) !== false ) {
						$file_path = str_replace( home_url(), ABSPATH, $script->src );
						if ( file_exists( $file_path ) ) {
							$polyfill_size += filesize( $file_path );
						}
					}
				}
			}
		}

		// Convert size to KB
		$polyfill_size_kb = round( $polyfill_size / 1024, 2 );

		// Generate findings if polyfills detected
		if ( ! empty( $detected_polyfills ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number of polyfills, 2: size in KB */
					__( '%1$d JavaScript polyfills detected (%2$s KB). Consider conditional loading for legacy browsers only.', 'wpshadow' ),
					count( $detected_polyfills ),
					$polyfill_size_kb > 0 ? $polyfill_size_kb : 'unknown'
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/polyfill-detection',
				'meta'         => array(
					'detected_polyfills' => array_values( $detected_polyfills ),
					'polyfill_count'     => count( $detected_polyfills ),
					'estimated_size_kb'  => $polyfill_size_kb,
					'recommendation'     => 'Use polyfill.io or differential serving for modern vs legacy browsers',
					'impact_estimate'    => '15-30% JavaScript size reduction for modern browsers',
					'browser_support'    => 'Modern browsers (Chrome 90+, Firefox 88+, Safari 14+) need fewer polyfills',
					'solution'           => 'Implement @babel/preset-env with browserslist targeting',
				),
			);
		}

		return null;
	}
}
