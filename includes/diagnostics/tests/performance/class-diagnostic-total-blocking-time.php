<?php
/**
 * Total Blocking Time (TBT) Diagnostic
 *
 * Measures Total Blocking Time for Core Web Vitals.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2059
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Total Blocking Time Diagnostic Class
 *
 * Measures factors affecting TBT (Total Blocking Time).
 * TBT measures responsiveness during page load.
 *
 * @since 1.6033.2059
 */
class Diagnostic_Total_Blocking_Time extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'total-blocking-time';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Total Blocking Time (TBT)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures Total Blocking Time (Core Web Vital)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks factors affecting TBT:
	 * - JavaScript execution time
	 * - Long tasks (>50ms)
	 * - Main thread blocking
	 *
	 * Thresholds:
	 * - Good: <200ms
	 * - Needs Improvement: 200-600ms
	 * - Poor: >600ms
	 *
	 * @since  1.6033.2059
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$score  = 0;
		
		global $wp_scripts;
		
		// Analyze JavaScript that could cause long tasks
		$js_files          = array();
		$total_js_size     = 0;
		$heavy_libraries   = array();
		$blocking_scripts  = 0;
		
		if ( $wp_scripts && isset( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				$script = $wp_scripts->registered[ $handle ] ?? null;
				if ( ! $script || ! isset( $script->src ) ) {
					continue;
				}
				
				// Check if blocking
				if ( empty( $script->extra['defer'] ) && empty( $script->extra['async'] ) ) {
					$blocking_scripts++;
				}
				
				// Get file size for local scripts
				$local_path = str_replace( site_url(), ABSPATH, $script->src );
				if ( file_exists( $local_path ) ) {
					$file_size = filesize( $local_path );
					$total_js_size += $file_size;
					
					// Large JavaScript files (>100KB) likely cause long tasks
					if ( $file_size > 102400 ) {
						$js_files[] = array(
							'handle' => $handle,
							'size'   => $file_size,
						);
					}
				}
				
				// Check for known heavy libraries
				$src_lower = strtolower( $script->src );
				if ( strpos( $src_lower, 'jquery' ) !== false && strpos( $src_lower, 'migrate' ) !== false ) {
					$heavy_libraries[] = 'jQuery Migrate';
				} elseif ( strpos( $src_lower, 'moment' ) !== false ) {
					$heavy_libraries[] = 'Moment.js';
				} elseif ( strpos( $src_lower, 'lodash' ) !== false ) {
					$heavy_libraries[] = 'Lodash';
				}
			}
		}
		
		// Large JavaScript files cause long tasks
		if ( count( $js_files ) > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of large JS files */
				__( '%d large JavaScript files (>100KB each)', 'wpshadow' ),
				count( $js_files )
			);
			$score += 30;
		}
		
		// Total JS size indicator
		if ( $total_js_size > 500000 ) { // 500KB
			$issues[] = sprintf(
				/* translators: %s: total JS size */
				__( 'Total JavaScript size %s (should be <300KB)', 'wpshadow' ),
				size_format( $total_js_size )
			);
			$score += 25;
		}
		
		// Heavy libraries
		if ( ! empty( $heavy_libraries ) ) {
			$issues[] = sprintf(
				/* translators: %s: list of heavy libraries */
				__( 'Heavy libraries loaded: %s', 'wpshadow' ),
				implode( ', ', $heavy_libraries )
			);
			$score += 20;
		}
		
		// Blocking scripts in head
		if ( $blocking_scripts > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of blocking scripts */
				__( '%d blocking scripts prevent parallel processing', 'wpshadow' ),
				$blocking_scripts
			);
			$score += 25;
		}
		
		// Check for code splitting
		$has_code_splitting = false;
		if ( $wp_scripts && isset( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				$script = $wp_scripts->registered[ $handle ] ?? null;
				if ( $script && isset( $script->src ) ) {
					if ( strpos( $script->src, '.chunk.' ) !== false || 
					     strpos( $script->src, 'vendor' ) !== false ) {
						$has_code_splitting = true;
						break;
					}
				}
			}
		}
		
		if ( ! $has_code_splitting && $total_js_size > 300000 ) {
			$issues[] = __( 'No code splitting detected for large JavaScript bundles', 'wpshadow' );
			$score += 20;
		}
		
		// Check for unused JavaScript (common TBT issue)
		$active_plugins = get_option( 'active_plugins', array() );
		if ( count( $active_plugins ) > 25 ) {
			$issues[] = sprintf(
				/* translators: %d: number of plugins */
				__( '%d plugins may load unnecessary JavaScript', 'wpshadow' ),
				count( $active_plugins )
			);
			$score += 15;
		}
		
		// If significant issues found
		if ( $score > 40 ) {
			$severity = 'medium';
			if ( $score > 60 ) {
				$severity = 'high';
			}
			if ( $score > 80 ) {
				$severity = 'critical';
			}
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: list of TBT issues */
					__( 'Factors affecting Total Blocking Time (Core Web Vital): %s. TBT measures main thread blocking, affecting page responsiveness during load.', 'wpshadow' ),
					implode( '; ', $issues )
				),
				'severity'     => $severity,
				'threat_level' => min( 100, $score ),
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/total-blocking-time',
				'meta'         => array(
					'total_js_size'        => $total_js_size,
					'total_js_formatted'   => size_format( $total_js_size ),
					'large_js_files'       => count( $js_files ),
					'blocking_scripts'     => $blocking_scripts,
					'heavy_libraries'      => $heavy_libraries,
					'has_code_splitting'   => $has_code_splitting,
					'active_plugins'       => count( $active_plugins ),
					'score'                => $score,
					'good_threshold'       => '200ms',
					'poor_threshold'       => '600ms',
					'optimization_tips'    => 'Defer non-critical JS, code split, remove unused code',
				),
			);
		}
		
		return null;
	}
}
