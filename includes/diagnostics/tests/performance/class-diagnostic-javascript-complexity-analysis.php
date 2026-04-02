<?php
/**
 * JavaScript Complexity Analysis Diagnostic
 *
 * Analyzes JavaScript code complexity and maintainability.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * JavaScript Complexity Analysis Diagnostic
 *
 * Evaluates JavaScript code complexity and identifies optimization opportunities.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Javascript_Complexity_Analysis extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'javascript-complexity-analysis';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'JavaScript Complexity Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes JavaScript code complexity and maintainability';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_scripts;

		if ( ! isset( $wp_scripts->registered ) ) {
			return null;
		}

		$total_size       = 0;
		$script_count     = 0;
		$minified_count   = 0;
		$local_scripts    = array();

		foreach ( $wp_scripts->registered as $handle => $script ) {
			if ( ! $wp_scripts->query( $handle ) ) {
				continue;
			}

			if ( ! isset( $script->src ) || empty( $script->src ) ) {
				continue;
			}

			$script_count++;

			// Check if script is local
			if ( is_string( $script->src ) && strpos( $script->src, home_url() ) !== false ) {
				$file_path = str_replace( home_url(), ABSPATH, $script->src );
				$file_path = str_replace( array( 'http://', 'https://' ), '', $file_path );

				if ( file_exists( $file_path ) ) {
					$file_size = filesize( $file_path );
					$total_size += $file_size;

					// Check if minified (contains .min.js)
					if ( strpos( $file_path, '.min.js' ) !== false ) {
						$minified_count++;
					}

					// Analyze large scripts
					if ( $file_size > 50000 ) { // > 50KB
						$local_scripts[] = array(
							'handle'  => $handle,
							'size_kb' => round( $file_size / 1024, 2 ),
							'path'    => basename( $file_path ),
						);
					}
				}
			}
		}

		// Convert total size to KB
		$total_size_kb = round( $total_size / 1024, 2 );

		// Calculate average size
		$avg_size_kb = $script_count > 0 ? round( $total_size_kb / $script_count, 2 ) : 0;

		// Check for unminified scripts
		$unminified_count = $script_count - $minified_count;

		if ( $unminified_count > 3 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number of unminified scripts, 2: total scripts */
					__( '%1$d of %2$d scripts are not minified. Minification reduces file size and improves load time.', 'wpshadow' ),
					$unminified_count,
					$script_count
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/javascript-complexity-analysis',
				'meta'         => array(
					'total_scripts'     => $script_count,
					'unminified_count'  => $unminified_count,
					'minified_count'    => $minified_count,
					'total_size_kb'     => $total_size_kb,
					'avg_size_kb'       => $avg_size_kb,
					'recommendation'    => 'Use Autoptimize or build tools to minify JavaScript',
					'impact_estimate'   => '15-25% file size reduction',
					'minification_savings' => round( $total_size_kb * 0.20, 2 ) . ' KB',
				),
			);
		}

		// Check for large individual scripts
		if ( ! empty( $local_scripts ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of large scripts */
					__( '%d JavaScript files exceed 50 KB. Consider code splitting or lazy loading.', 'wpshadow' ),
					count( $local_scripts )
				),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/javascript-complexity-analysis',
				'meta'         => array(
					'large_scripts'    => $local_scripts,
					'large_script_count' => count( $local_scripts ),
					'recommendation'   => 'Implement code splitting or lazy loading for large scripts',
					'impact_estimate'  => '100-300ms faster initial load',
				),
			);
		}

		return null;
	}
}
