<?php
/**
 * Time to Interactive (TTI) Diagnostic
 *
 * Measures Time to Interactive metric - when the page becomes fully interactive
 * and responds quickly to user input. Core Web Vital for user experience.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.6034.2152
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Time to Interactive Diagnostic Class
 *
 * Analyzes factors affecting Time to Interactive including JavaScript execution,
 * main thread blocking, and render-blocking resources. TTI is critical for
 * perceived performance and user engagement.
 *
 * **Why This Matters:**
 * - Google Lighthouse Core Web Vital
 * - 53% of mobile users abandon if page takes > 3s to become interactive
 * - Affects Google rankings (Page Experience update)
 * - Poor TTI = frustrated users, high bounce rates
 *
 * **What's Measured:**
 * - JavaScript bundle size and execution time
 * - Render-blocking resources
 * - Main thread work
 * - Third-party script impact
 *
 * **Target:** < 3.8 seconds on mobile, < 2.5 seconds ideal
 *
 * @since 1.6034.2152
 */
class Diagnostic_Time_To_Interactive_TTI extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'time-to-interactive-tti';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Time to Interactive (TTI)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes factors affecting Time to Interactive performance metric';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check
	 *
	 * Estimates TTI by analyzing:
	 * - Enqueued JavaScript size and count
	 * - Render-blocking scripts
	 * - jQuery and dependencies
	 * - Third-party scripts (analytics, ads)
	 *
	 * @since  1.6034.2152
	 * @return array|null Finding array if TTI likely poor, null if acceptable.
	 */
	public static function check() {
		global $wp_scripts;

		if ( ! $wp_scripts instanceof \WP_Scripts ) {
			return null;
		}

		$issues          = array();
		$total_js_size   = 0;
		$blocking_count  = 0;
		$third_party_count = 0;

		// Analyze enqueued scripts
		foreach ( $wp_scripts->queue as $handle ) {
			if ( ! isset( $wp_scripts->registered[ $handle ] ) ) {
				continue;
			}

			$script = $wp_scripts->registered[ $handle ];

			// Estimate size (rough approximation)
			if ( ! empty( $script->src ) ) {
				$src = $script->src;

				// Check if render-blocking (not deferred/async)
				if ( empty( $script->extra['defer'] ) && empty( $script->extra['async'] ) ) {
					$blocking_count++;
				}

				// Check for third-party scripts
				if ( strpos( $src, 'google' ) !== false || 
					 strpos( $src, 'facebook' ) !== false ||
					 strpos( $src, 'twitter' ) !== false ||
					 strpos( $src, 'analytics' ) !== false ) {
					$third_party_count++;
				}

				// Estimate total JS size (very rough)
				$total_js_size += 50; // Assume ~50KB per script
			}
		}

		$script_count = count( $wp_scripts->queue );

		// Flag if excessive JavaScript
		if ( $script_count > 15 ) {
			$issues[] = sprintf(
				/* translators: %d: number of scripts */
				__( '%d JavaScript files enqueued (recommended: < 10)', 'wpshadow' ),
				$script_count
			);
		}

		if ( $blocking_count > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of blocking scripts */
				__( '%d render-blocking scripts detected', 'wpshadow' ),
				$blocking_count
			);
		}

		if ( $third_party_count > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of third-party scripts */
				__( '%d third-party scripts slow down interactivity', 'wpshadow' ),
				$third_party_count
			);
		}

		// Check for jQuery (large, blocks interactivity)
		if ( in_array( 'jquery', $wp_scripts->queue, true ) ) {
			$issues[] = __( 'jQuery loaded (consider modern vanilla JavaScript)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of issues */
				__( '%d issue(s) detected that negatively impact Time to Interactive. Your page may feel slow and unresponsive to users.', 'wpshadow' ),
				count( $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/performance-time-to-interactive',
			'details'      => array(
				'issues'            => $issues,
				'script_count'      => $script_count,
				'blocking_count'    => $blocking_count,
				'third_party_count' => $third_party_count,
				'target'            => '< 3.8s on mobile',
			),
		);
	}
}
