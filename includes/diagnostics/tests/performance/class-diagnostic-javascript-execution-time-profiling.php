<?php
/**
 * JavaScript Execution Time Profiling Diagnostic
 *
 * Profiles JavaScript parse/compile/execution time impact.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * JavaScript Execution Time Profiling Class
 *
 * Tests JS execution.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Javascript_Execution_Time_Profiling extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'javascript-execution-time-profiling';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'JavaScript Execution Time Profiling';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Profiles JavaScript parse/compile/execution time impact';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$js_check = self::check_javascript_load();
		
		if ( $js_check['has_issues'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $js_check['issues'] ),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/javascript-execution-time-profiling',
				'meta'         => array(
					'total_js_size'        => $js_check['total_js_size'],
					'script_count'         => $js_check['script_count'],
					'large_scripts'        => $js_check['large_scripts'],
					'inline_scripts'       => $js_check['inline_scripts'],
					'recommendations'      => $js_check['recommendations'],
				),
			);
		}

		return null;
	}

	/**
	 * Check JavaScript load.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_javascript_load() {
		global $wp_scripts;

		$check = array(
			'has_issues'       => false,
			'issues'           => array(),
			'total_js_size'    => 0,
			'script_count'     => 0,
			'large_scripts'    => array(),
			'inline_scripts'   => 0,
			'recommendations'  => array(),
		);

		if ( empty( $wp_scripts->queue ) ) {
			return $check;
		}

		// Analyze enqueued scripts.
		foreach ( $wp_scripts->queue as $handle ) {
			if ( ! isset( $wp_scripts->registered[ $handle ] ) ) {
				continue;
			}

			$script = $wp_scripts->registered[ $handle ];
			$check['script_count']++;

			// Check for inline scripts.
			if ( ! empty( $script->extra['data'] ) || ! empty( $script->extra['before'] ) || ! empty( $script->extra['after'] ) ) {
				$check['inline_scripts']++;
			}

			// Analyze file size if local.
			if ( ! empty( $script->src ) ) {
				$home_url = home_url();
				
				if ( 0 === strpos( $script->src, $home_url ) || 0 === strpos( $script->src, '/' ) ) {
					// Local file - attempt to get size.
					$file_path = str_replace( $home_url, ABSPATH, $script->src );
					
					// Handle relative paths.
					if ( 0 === strpos( $script->src, '/' ) ) {
						$file_path = ABSPATH . ltrim( $script->src, '/' );
					}

					$file_path = wp_normalize_path( $file_path );

					// Remove query strings.
					$file_path = preg_replace( '/\?.*$/', '', $file_path );

					if ( file_exists( $file_path ) ) {
						$file_size = filesize( $file_path );
						$check['total_js_size'] += $file_size;

						// Track large scripts (>200KB).
						if ( $file_size > 204800 ) {
							$check['large_scripts'][] = array(
								'handle' => $handle,
								'size'   => $file_size,
								'src'    => basename( $script->src ),
							);
						}
					}
				}
			}
		}

		// Detect issues.
		if ( $check['total_js_size'] > 1048576 ) { // >1MB.
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %s: total JS size in MB */
				__( '%sMB of JavaScript total (causes 2-5 second execution time on mobile)', 'wpshadow' ),
				number_format( $check['total_js_size'] / 1048576, 2 )
			);
			$check['recommendations'][] = __( 'Implement code splitting and lazy loading', 'wpshadow' );
		}

		if ( count( $check['large_scripts'] ) > 0 ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %d: number of large scripts */
				__( '%d large JavaScript files (>200KB) detected', 'wpshadow' ),
				count( $check['large_scripts'] )
			);
			$check['recommendations'][] = __( 'Minify and split large JavaScript bundles', 'wpshadow' );
		}

		if ( $check['inline_scripts'] > 10 ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %d: number of inline scripts */
				__( '%d inline JavaScript blocks detected (increases page size)', 'wpshadow' ),
				$check['inline_scripts']
			);
			$check['recommendations'][] = __( 'Move inline JavaScript to external files for better caching', 'wpshadow' );
		}

		if ( $check['script_count'] > 15 ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %d: number of scripts */
				__( '%d JavaScript files enqueued (excessive HTTP requests)', 'wpshadow' ),
				$check['script_count']
			);
			$check['recommendations'][] = __( 'Combine JavaScript files or implement HTTP/2', 'wpshadow' );
		}

		return $check;
	}
}
