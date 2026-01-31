<?php
/**
 * Heatmap and Session Recording Diagnostic
 *
 * Verifies heatmap/session recording tools installed to understand
 * how users actually interact with the site.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Heatmap_Session_Recording Class
 *
 * Verifies heatmap/session recording tools.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Heatmap_Session_Recording extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'heatmap-session-recording';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Heatmap and Session Recording';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies heatmap/session recording tools';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if tools missing, null otherwise.
	 */
	public static function check() {
		$heatmap_status = self::check_heatmap_tools();

		if ( $heatmap_status['is_installed'] ) {
			return null; // Heatmap tools present
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No heatmap/session recording tools. Analytics show WHAT users do, but not WHY. See exactly where users click, scroll, rage-click. Find UX issues causing drop-offs.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/heatmaps',
			'family'       => self::$family,
			'meta'         => array(
				'tool_detected' => $heatmap_status['method'],
			),
			'details'      => array(
				'why_heatmaps_matter'          => array(
					__( 'See where users actually click (not where you think)' ),
					__( 'Identify ignored CTAs (no clicks = redesign needed)' ),
					__( 'Find rage clicks (rapid clicks = frustration)' ),
					__( 'Scroll depth: How far down page users read' ),
					__( 'Form analytics: Which fields cause abandonment' ),
				),
				'heatmap_types'                => array(
					'Click Maps' => __( 'Where users click (all elements, not just links)' ),
					'Move Maps' => __( 'Mouse movement patterns (attention indicators)' ),
					'Scroll Maps' => __( 'How far users scroll (% reaching bottom)' ),
					'Attention Maps' => __( 'Where users spend most time looking' ),
				),
				'session_recording_benefits'   => array(
					__( 'Watch real user sessions (anonymous)' ),
					__( 'See bugs users encounter' ),
					__( 'Understand checkout abandonment reasons' ),
					__( 'Identify confusing navigation' ),
					__( 'Share recordings with team for discussion' ),
				),
				'popular_heatmap_tools'        => array(
					'Hotjar' => array(
						'Free: 35 sessions/day',
						'Paid: $39+/month',
						'Features: Heatmaps, recordings, surveys, feedback',
					),
					'Microsoft Clarity' => array(
						'Free: Unlimited',
						'Features: Heatmaps, recordings, insights',
						'Best for: Budget-conscious',
					),
					'Crazy Egg' => array(
						'Price: $29+/month',
						'Features: Heatmaps, scrollmaps, A/B testing',
					),
					'Lucky Orange' => array(
						'Price: $10+/month',
						'Features: Heatmaps, recordings, live chat',
					),
				),
				'wordpress_integration'        => array(
					'Hotjar' => array(
						'Plugin: Insert Headers and Footers',
						'Paste: Hotjar tracking code in header',
						'Or: Manual in header.php',
					),
					'Microsoft Clarity' => array(
						'Plugin: Site Kit by Google',
						'Or: Clarity WordPress plugin (unofficial)',
						'Setup: Connect Clarity account',
					),
					'Crazy Egg' => array(
						'Plugin: Crazy Egg Heatmaps',
						'Setup: Enter API key',
						'Auto-installs tracking',
					),
				),
				'analyzing_heatmap_data'       => array(
					'Homepage Analysis' => array(
						'Question: Do users see CTA above fold?',
						'Check: Scroll map shows 50%+ reach CTA',
						'Action: Move CTA up if low visibility',
					),
					'Product Page Analysis' => array(
						'Question: Do users click "Add to Cart"?',
						'Check: Click map shows high engagement',
						'Action: Enlarge button if low clicks',
					),
					'Form Analysis' => array(
						'Question: Which field causes abandonment?',
						'Check: Recordings show exit point',
						'Action: Remove/simplify problematic field',
					),
				),
				'privacy_considerations'       => array(
					__( 'GDPR: Get consent for recordings in EU' ),
					__( 'Mask sensitive data: Credit cards, passwords' ),
					__( 'Cookie consent: Required before tracking' ),
					__( 'Privacy policy: Disclose use of recordings' ),
					__( 'Retention: Don\'t store recordings indefinitely' ),
				),
			),
		);
	}

	/**
	 * Check heatmap tools.
	 *
	 * @since  1.2601.2148
	 * @return array Heatmap tool status.
	 */
	private static function check_heatmap_tools() {
		// Check for Hotjar
		$homepage = wp_remote_get( home_url() );
		if ( ! is_wp_error( $homepage ) ) {
			$body = wp_remote_retrieve_body( $homepage );
			if ( strpos( $body, 'hotjar' ) !== false ) {
				return array(
					'is_installed' => true,
					'method'       => 'Hotjar',
				);
			}
			if ( strpos( $body, 'clarity.ms' ) !== false ) {
				return array(
					'is_installed' => true,
					'method'       => 'Microsoft Clarity',
				);
			}
			if ( strpos( $body, 'crazyegg' ) !== false ) {
				return array(
					'is_installed' => true,
					'method'       => 'Crazy Egg',
				);
			}
		}

		return array(
			'is_installed' => false,
			'method'       => 'Not detected',
		);
	}
}
