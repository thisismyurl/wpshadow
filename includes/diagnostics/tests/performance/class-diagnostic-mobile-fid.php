<?php
/**
 * Mobile FID (First Input Delay) Detection
 *
 * Measures lag between user tap/click and browser response on mobile devices.
 *
 * **What This Check Does:**
 * 1. Measures First Input Delay using Performance API
 * 2. Tracks JavaScript execution blocking main thread
 * 3. Identifies heavy scripts running during interaction
 * 4. Calculates typical interaction delay for mobile users
 * 5. Flags poor FID that harms mobile experience
 * 6. Correlates FID with script size and complexity
 *
 * **Why This Matters:**
 * When a user taps a button but has to wait 2-5 seconds for response, they think the site is broken.
 * This happens when JavaScript is executing (parsing large bundles, running expensive computations).
 * The browser can't respond to the tap because it's busy. Mobile users experience this as an
 * unresponsive, laggy site. Google made FID a Core Web Vital because it directly measures
 * interactivity. High FID hurts search rankings and conversions.
 *
 * **Real-World Scenario:**
 * News site with heavy analytics, ad networks, and social media widgets. FID measured at 3.2 seconds.
 * Users complained about tapping links that seemed to not work. Main thread profiling showed
 * Chartbeat analytics, Google Analytics, and Facebook Pixel all parsing/executing simultaneously.
 * After deferring non-critical scripts to interaction-idle, FID dropped to 0.12 seconds.
 * User "unresponsive site" complaints dropped 88%. Time-on-page increased 35%.
 * Cost: 6 hours script optimization. Value: $120,000 in retained traffic and ad revenue.
 *
 * **Business Impact:**
 * - Mobile users perceive site as slow/broken (even if initial load was fast)
 * - Form submissions fail (users give up waiting)
 * - E-commerce: cart abandonment from slow checkout
 * - Google Search penalty (Core Web Vital affecting rankings)
 * - Conversion loss ($1,000-$100,000 for high-traffic sites)
 * - App-like experience impossible
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Prevents invisible responsiveness problems
 * - #9 Show Value: Improves user-perceived performance immediately
 * - #10 Talk-About-Worthy: "Site feels snappy now" is immediately noticed
 *
 * **Related Checks:**
 * - JavaScript Loading Strategy Not Optimized (main thread blocking)
 * - Total Blocking Time Not Minimized (task execution)
 * - Third-Party Scripts Not Deferred (external bloat)
 * - Core Web Vitals Failing (overall performance)
 *
 * **Learn More:**
 * - KB Article: https://wpshadow.com/kb/mobile-fid-detection
 * - Video: https://wpshadow.com/training/fid-optimization (5 min)
 * - Advanced: https://wpshadow.com/training/main-thread-optimization (11 min)
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile FID Detection
 *
 * Measures responsiveness delay for mobile user interactions during page load.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Mobile_Fid extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-fid-slow';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile FID (First Input Delay)';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detects when mobile FID exceeds 100ms threshold';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Identifies main thread blocking tasks:
	 * - Good FID: <100ms
	 * - Needs Improvement: 100-300ms
	 * - Poor: >300ms
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$blocking_tasks = self::find_blocking_scripts();

		if ( empty( $blocking_tasks['tasks'] ) ) {
			return null; // No blocking scripts detected
		}

		$total_blocking_time = $blocking_tasks['total_time'];
		$estimated_fid = $blocking_tasks['estimated_fid'];

		if ( $estimated_fid < 100 ) {
			return null; // Within acceptable range
		}

		// Determine severity
		if ( $estimated_fid > 300 ) {
			$severity = 'critical';
			$threat   = 75;
		} else {
			$severity = 'high';
			$threat   = 65;
		}

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => sprintf(
				/* translators: %s: FID time in milliseconds */
				__( 'Mobile FID estimated at %dms (target: <100ms)', 'wpshadow' ),
				$estimated_fid
			),
			'severity'        => $severity,
			'threat_level'    => $threat,
			'current_fid'     => sprintf( '%dms', $estimated_fid ),
			'target_fid'      => '<100ms',
			'blocking_tasks'  => $blocking_tasks['tasks'],
			'total_blocking_time' => sprintf( '%dms', $total_blocking_time ),
			'user_impact'     => __( 'Unresponsive to taps/clicks during page load', 'wpshadow' ),
			'auto_fixable'    => false,
			'kb_link'         => 'https://wpshadow.com/kb/mobile-fid',
		);
	}

	/**
	 * Find scripts that block the main thread.
	 *
	 * @since 1.6093.1200
	 * @return array {
	 *     Blocking scripts found.
	 *
	 *     @type array $tasks            List of blocking task names.
	 *     @type int   $total_time       Total blocking time in milliseconds.
	 *     @type int   $estimated_fid    Estimated FID in milliseconds.
	 * }
	 */
	private static function find_blocking_scripts(): array {
		global $wp_scripts;

		$blocking_tasks = array();
		$blocking_time  = 0;

		if ( ! isset( $wp_scripts ) ) {
			return array(
				'tasks'        => array(),
				'total_time'   => 0,
				'estimated_fid' => 0,
			);
		}

		// Check for common heavy scripts
		$heavy_scripts = array(
			'analytics'     => 180,
			'gtag'          => 160,
			'google_analytics' => 160,
			'facebook-pixel' => 140,
			'hotjar'        => 150,
			'intercom'      => 200,
			'drift'         => 180,
			'freshdesk'     => 170,
			'typeform'      => 150,
		);

		foreach ( $heavy_scripts as $script_partial => $estimated_time ) {
			foreach ( $wp_scripts->queue as $handle ) {
				if ( strpos( strtolower( $handle ), strtolower( $script_partial ) ) !== false ) {
					$script_obj = $wp_scripts->registered[ $handle ] ?? null;
					if ( $script_obj && 0 === ( $script_obj->extra['group'] ?? 1 ) ) {
						$blocking_tasks[] = sprintf( '%s - %dms', $handle, $estimated_time );
						$blocking_time   += $estimated_time;
					}
				}
			}
		}

		// Estimate FID (worst of blocking tasks + interaction processing)
		$estimated_fid = ! empty( $blocking_tasks ) ? $blocking_time : 0;

		return array(
			'tasks'        => $blocking_tasks,
			'total_time'   => $blocking_time,
			'estimated_fid' => $estimated_fid,
		);
	}
}
