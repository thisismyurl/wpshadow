<?php
/**
 * Third-Party Script Impact Analysis Diagnostic
 *
 * Identifies external scripts (Google Analytics, Facebook Pixel, etc.) and estimates
 * their performance impact on page load time.
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
 * Diagnostic_Third_Party_Script_Impact Class
 *
 * Analyzes impact of external scripts on page performance.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Third_Party_Script_Impact extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'third-party-script-impact';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Third-Party Script Performance Impact';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes performance impact of external scripts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Performance impact threshold (milliseconds)
	 *
	 * @var int
	 */
	const IMPACT_THRESHOLD = 500;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if impact detected, null otherwise.
	 */
	public static function check() {
		$external_scripts = self::analyze_third_party_scripts();

		if ( empty( $external_scripts ) ) {
			// No external scripts found
			return null;
		}

		// Calculate total impact
		$total_impact = array_sum( array_column( $external_scripts, 'estimated_impact' ) );

		if ( $total_impact < 100 ) {
			// Impact is minimal
			return null;
		}

		$heavy_scripts = array_filter(
			$external_scripts,
			function( $script ) {
				return $script['estimated_impact'] > self::IMPACT_THRESHOLD;
			}
		);

		$severity = empty( $heavy_scripts ) ? 'low' : 'medium';
		if ( count( $heavy_scripts ) >= 2 || $total_impact > 1000 ) {
			$severity = 'high';
		}

		$threat_level = ( $severity === 'high' ) ? 60 : ( ( $severity === 'medium' ) ? 40 : 25 );

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %d: number of external scripts, %d: total impact in ms */
				__( 'Found %d external scripts adding approximately %dms to page load time.', 'wpshadow' ),
				count( $external_scripts ),
				(int) $total_impact
			),
			'severity'      => $severity,
			'threat_level'  => $threat_level,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/third-party-script-impact',
			'family'        => self::$family,
			'meta'          => array(
				'external_script_count'  => count( $external_scripts ),
				'total_estimated_impact' => (int) $total_impact,
				'external_scripts'       => array_slice( $external_scripts, 0, 10 ), // Show first 10
				'heavy_scripts'          => array_slice( $heavy_scripts, 0, 5 ), // Show top 5 heavy
				'optimization_strategies' => array(
					__( 'Load scripts asynchronously or defer them' ),
					__( 'Use script lazy loading plugins' ),
					__( 'Remove unused third-party scripts' ),
					__( 'Evaluate whether each script is necessary' ),
					__( 'Find lighter alternatives to heavy scripts' ),
					__( 'Consolidate multiple scripts into single request' ),
				),
			),
			'details'       => array(
				'issue'               => sprintf(
					/* translators: %d: number of scripts, %d: impact ms */
					__( '%d external scripts are impacting page performance by ~%dms.', 'wpshadow' ),
					count( $external_scripts ),
					(int) $total_impact
				),
				'impact_breakdown'    => array(
					'Google Analytics' => '30-50ms typical',
					'Facebook Pixel' => '50-100ms typical',
					'Google Ads' => '40-80ms typical',
					'HotJar' => '50-150ms typical',
					'Intercom' => '100-200ms typical',
					'Drift' => '80-150ms typical',
				),
				'optimization_order'  => array(
					'High Priority' => array(
						__( 'Remove completely unused scripts' ),
						__( 'Defer analytics to after user interaction' ),
						__( 'Lazy load chat/support widgets' ),
						__( 'Async load ads and tracking' ),
					),
					'Medium Priority' => array(
						__( 'Consolidate multiple trackers into one' ),
						__( 'Cache script responses where possible' ),
						__( 'Minify and compress where not already done' ),
					),
					'Low Priority' => array(
						__( 'Monitor but accept small performance cost' ),
						__( 'Use service workers for better caching' ),
					),
				),
				'common_scripts'      => array(
					'Google Analytics (gtag.js)' => __( '~40ms - Essential for most sites, consider deferring' ),
					'Facebook Pixel' => __( '~80ms - Useful for retargeting, defer or lazy load' ),
					'Google Ads (gtag)' => __( '~50ms - Required for ads, defer if not primary' ),
					'Hotjar' => __( '~100ms - Session recording, defer to after interaction' ),
					'Intercom' => __( '~150ms - Support widget, lazy load on user action' ),
					'Drift' => __( '~120ms - Chat widget, lazy load' ),
					'LinkedIn Pixel' => __( '~50ms - Retargeting, defer' ),
					'Twitter Pixel' => __( '~40ms - Retargeting, defer' ),
				),
			),
		);
	}

	/**
	 * Analyze third-party scripts and estimate their impact.
	 *
	 * @since  1.2601.2148
	 * @return array List of external scripts with impact estimates.
	 */
	private static function analyze_third_party_scripts() {
		$external_scripts = array();
		$home_url         = home_url();

		global $wp_scripts;

		if ( ! isset( $wp_scripts ) || ! isset( $wp_scripts->queue ) ) {
			return $external_scripts;
		}

		$script_impact_estimates = array(
			'google-analytics' => 40,
			'gtag'             => 40,
			'analytics'        => 40,
			'facebook-pixel'   => 80,
			'fbevents'         => 80,
			'google-ads'       => 50,
			'googleadservices' => 50,
			'hotjar'           => 100,
			'intercom'         => 150,
			'drift'            => 120,
			'linkedin'         => 50,
			'twitter'          => 40,
			'tiktok'           => 60,
			'pinterest'        => 50,
			'segment'          => 50,
		);

		foreach ( $wp_scripts->queue as $handle ) {
			$script = $wp_scripts->registered[ $handle ];

			if ( ! isset( $script->src ) || empty( $script->src ) ) {
				continue;
			}

			$src = $script->src;

			// Check if script is external
			if ( strpos( $src, home_url() ) === 0 ) {
				continue; // Local script
			}

			// Estimate impact based on domain
			$estimated_impact = 30; // Default estimate

			foreach ( $script_impact_estimates as $keyword => $impact ) {
				if ( stripos( $src, $keyword ) !== false || stripos( $handle, $keyword ) !== false ) {
					$estimated_impact = $impact;
					break;
				}
			}

			// Increase estimate if script is render-blocking
			if ( isset( $script->extra['group'] ) && 0 === $script->extra['group'] ) {
				$estimated_impact *= 1.5; // Render-blocking adds overhead
			}

			$external_scripts[] = array(
				'handle'            => $handle,
				'src'               => $src,
				'estimated_impact'  => (int) $estimated_impact,
				'is_async'          => isset( $script->extra['async'] ),
				'is_defer'          => isset( $script->extra['defer'] ),
				'is_render_blocking' => ! isset( $script->extra['async'] ) && ! isset( $script->extra['defer'] ),
			);
		}

		// Sort by impact (highest first)
		usort(
			$external_scripts,
			function( $a, $b ) {
				return $b['estimated_impact'] <=> $a['estimated_impact'];
			}
		);

		return $external_scripts;
	}
}
