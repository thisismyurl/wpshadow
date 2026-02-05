<?php
/**
 * Time to Interactive (TTI) Treatment
 *
 * Measures Time to Interactive for Core Web Vitals.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.2054
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Time to Interactive Treatment Class
 *
 * Measures factors affecting TTI. TTI is when the page becomes
 * fully interactive and responsive to user input.
 *
 * @since 1.6033.2054
 */
class Treatment_Time_To_Interactive extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'time-to-interactive';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Time to Interactive (TTI)';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Measures Time to Interactive (Core Web Vital)';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Checks factors affecting TTI:
	 * - JavaScript execution time
	 * - Main thread work
	 * - Network requests
	 *
	 * Thresholds:
	 * - Good: <3.8s
	 * - Needs Improvement: 3.8-7.3s
	 * - Poor: >7.3s
	 *
	 * @since  1.6033.2054
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$score  = 0;
		
		global $wp_scripts;
		
		// Count total JavaScript files
		$js_count = 0;
		$js_size  = 0;
		
		if ( $wp_scripts && isset( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				$script = $wp_scripts->registered[ $handle ] ?? null;
				if ( $script && isset( $script->src ) ) {
					$js_count++;
					
					// Try to get file size
					$local_path = str_replace( site_url(), ABSPATH, $script->src );
					if ( file_exists( $local_path ) ) {
						$js_size += filesize( $local_path );
					}
				}
			}
		}
		
		// Check for excessive JavaScript
		if ( $js_count > 15 ) {
			$issues[] = sprintf(
				/* translators: %d: number of JavaScript files */
				__( '%d JavaScript files (should be <10)', 'wpshadow' ),
				$js_count
			);
			$score += 25;
		}
		
		if ( $js_size > 500000 ) { // 500KB
			$issues[] = sprintf(
				/* translators: %s: JavaScript total size */
				__( 'JavaScript size %s (should be <300KB)', 'wpshadow' ),
				size_format( $js_size )
			);
			$score += 30;
		}
		
		// Check for jQuery Migrate
		if ( wp_script_is( 'jquery-migrate', 'enqueued' ) ) {
			$issues[] = __( 'jQuery Migrate loaded (adds ~10KB + execution time)', 'wpshadow' );
			$score += 15;
		}
		
		// Check for long tasks indicators
		$active_plugins = get_option( 'active_plugins', array() );
		if ( count( $active_plugins ) > 20 ) {
			$issues[] = sprintf(
				/* translators: %d: number of active plugins */
				__( '%d active plugins (increases JavaScript execution)', 'wpshadow' ),
				count( $active_plugins )
			);
			$score += 20;
		}
		
		// Check for defer/async usage
		$deferred_scripts = 0;
		if ( $wp_scripts && isset( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				$script = $wp_scripts->registered[ $handle ] ?? null;
				if ( $script && ( ! empty( $script->extra['defer'] ) || ! empty( $script->extra['async'] ) ) ) {
					$deferred_scripts++;
				}
			}
		}
		
		$defer_ratio = $js_count > 0 ? ( $deferred_scripts / $js_count ) : 0;
		if ( $defer_ratio < 0.5 && $js_count > 5 ) {
			$issues[] = sprintf(
				/* translators: 1: percentage of deferred scripts, 2: total scripts */
				__( 'Only %d%% of scripts deferred/async (%d/%d)', 'wpshadow' ),
				round( $defer_ratio * 100 ),
				$deferred_scripts,
				$js_count
			);
			$score += 25;
		}
		
		// If significant issues found
		if ( $score > 40 ) {
			$severity = 'medium';
			if ( $score > 70 ) {
				$severity = 'high';
			}
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: list of TTI issues */
					__( 'Factors affecting Time to Interactive: %s. TTI measures when page becomes fully interactive, affecting user experience and Core Web Vitals score.', 'wpshadow' ),
					implode( '; ', $issues )
				),
				'severity'     => $severity,
				'threat_level' => min( 100, $score ),
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/time-to-interactive',
				'meta'         => array(
					'js_count'          => $js_count,
					'js_size_bytes'     => $js_size,
					'js_size_formatted' => size_format( $js_size ),
					'deferred_scripts'  => $deferred_scripts,
					'defer_ratio'       => round( $defer_ratio * 100 ) . '%',
					'active_plugins'    => count( $active_plugins ),
					'jquery_migrate'    => wp_script_is( 'jquery-migrate', 'enqueued' ),
					'score'             => $score,
					'good_threshold'    => '3.8s',
					'poor_threshold'    => '7.3s',
				),
			);
		}
		
		return null;
	}
}
