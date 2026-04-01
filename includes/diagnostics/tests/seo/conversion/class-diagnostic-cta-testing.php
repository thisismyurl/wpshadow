<?php
/**
 * Call-to-Action Testing Diagnostic
 *
 * Tests if CTA elements are regularly tested and optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CTA Testing Diagnostic Class
 *
 * Evaluates whether call-to-action elements are being tested and optimized.
 * Checks for CTA plugins, A/B testing tools, button optimization, and variation testing.
 *
 * @since 0.6093.1200
 */
class Diagnostic_CTA_Testing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tests_call_to_action';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CTA Testing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if CTA elements are regularly tested and optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 *
	 */
	protected static $family = 'conversion';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$stats         = array();
		$issues        = array();
		$warnings      = array();
		$score         = 0;
		$total_points  = 0;
		$earned_points = 0;

		// Check for CTA/button builder plugins.
		$cta_plugins = array(
			'wp-call-button/call-button.php'             => 'WP Call Button',
			'thrive-leads/thrive-leads.php'              => 'Thrive Leads',
			'maxbuttons/maxbuttons.php'                  => 'MaxButtons',
			'wordpress-calls-to-action/cta.php'          => 'WordPress Calls to Action',
			'convertpro/convertpro.php'                  => 'Convert Pro',
			'buttonizer-multifunctional-button/buttonizer.php' => 'Buttonizer',
			'call-now-button/call-now-button.php'        => 'Call Now Button',
		);

		$active_cta_plugins = array();
		foreach ( $cta_plugins as $plugin => $name ) {
			$total_points += 10;
			if ( is_plugin_active( $plugin ) ) {
				$active_cta_plugins[] = $name;
				$earned_points       += 10;
			}
		}

		$stats['cta_plugins'] = array(
			'found' => count( $active_cta_plugins ),
			'list'  => $active_cta_plugins,
		);

		if ( empty( $active_cta_plugins ) ) {
			$warnings[] = __( 'No dedicated CTA management plugins detected', 'wpshadow' );
		}

		// Check for A/B testing tools.
		$ab_testing_tools = array(
			'google-optimize' => 'Google Optimize',
			'optimizely'      => 'Optimizely',
			'vwo'             => 'VWO',
			'ab-tasty'        => 'AB Tasty',
			'convert'         => 'Convert',
		);

		$active_ab_tools = array();
		foreach ( $ab_testing_tools as $handle => $name ) {
			$total_points += 15;
			if ( wp_script_is( $handle, 'enqueued' ) || wp_script_is( $handle, 'registered' ) ) {
				$active_ab_tools[] = $name;
				$earned_points    += 15;
			}
		}

		$stats['ab_testing_tools'] = array(
			'found' => count( $active_ab_tools ),
			'list'  => $active_ab_tools,
		);

		if ( empty( $active_ab_tools ) ) {
			$issues[] = __( 'No A/B testing tools detected for CTA optimization', 'wpshadow' );
		}

		// Check for heatmap/click tracking tools.
		$heatmap_tools = array(
			'hotjar-async' => 'Hotjar',
			'crazyegg'     => 'CrazyEgg',
			'mouseflow'    => 'Mouseflow',
			'clicktale'    => 'ClickTale',
		);

		$active_heatmap_tools = array();
		foreach ( $heatmap_tools as $handle => $name ) {
			$total_points += 10;
			if ( wp_script_is( $handle, 'enqueued' ) || wp_script_is( $handle, 'registered' ) ) {
				$active_heatmap_tools[] = $name;
				$earned_points         += 10;
			}
		}

		$stats['heatmap_tools'] = array(
			'found' => count( $active_heatmap_tools ),
			'list'  => $active_heatmap_tools,
		);

		if ( empty( $active_heatmap_tools ) ) {
			$warnings[] = __( 'No heatmap or click tracking tools detected', 'wpshadow' );
		}

		// Check for CTA variation testing.
		$total_points += 15;
		$cta_variations = get_posts(
			array(
				'post_type'      => array( 'post', 'page', 'cta' ),
				'posts_per_page' => -1,
				'post_status'    => 'any',
				's'              => 'cta test',
			)
		);

		if ( ! empty( $cta_variations ) ) {
			$earned_points += 15;
			$stats['cta_variations'] = count( $cta_variations );
		} else {
			$stats['cta_variations'] = 0;
			$warnings[] = __( 'No CTA variation tests found', 'wpshadow' );
		}

		// Check for analytics tracking.
		$total_points += 15;
		if ( wp_script_is( 'google-analytics', 'enqueued' ) ||
			 wp_script_is( 'gtag', 'enqueued' ) ||
			 is_plugin_active( 'google-analytics-for-wordpress/googleanalytics.php' ) ||
			 is_plugin_active( 'google-site-kit/google-site-kit.php' ) ) {
			$earned_points += 15;
			$stats['analytics_enabled'] = true;
		} else {
			$stats['analytics_enabled'] = false;
			$issues[] = __( 'No analytics platform detected for CTA tracking', 'wpshadow' );
		}

		// Check for conversion tracking.
		$total_points += 10;
		$conversion_tracking = array(
			'gtag',
			'fbq',
			'linkedin-insight',
			'google-tag-manager',
		);

		$active_conversion_tracking = array();
		foreach ( $conversion_tracking as $handle ) {
			if ( wp_script_is( $handle, 'enqueued' ) || wp_script_is( $handle, 'registered' ) ) {
				$active_conversion_tracking[] = $handle;
			}
		}

		if ( ! empty( $active_conversion_tracking ) ) {
			$earned_points += 10;
		}

		$stats['conversion_tracking'] = array(
			'found' => count( $active_conversion_tracking ),
			'list'  => $active_conversion_tracking,
		);

		// Check for popup/modal testing.
		$total_points += 10;
		$popup_plugins = array(
			'popup-maker/popup-maker.php'       => 'Popup Maker',
			'convertpro/convertpro.php'         => 'Convert Pro',
			'optinmonster/optin-monster-wp-api.php' => 'OptinMonster',
		);

		$active_popup_tools = array();
		foreach ( $popup_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_popup_tools[] = $name;
			}
		}

		if ( ! empty( $active_popup_tools ) ) {
			$earned_points += 10;
		}

		$stats['popup_tools'] = array(
			'found' => count( $active_popup_tools ),
			'list'  => $active_popup_tools,
		);

		// Calculate final score.
		if ( $total_points > 0 ) {
			$score = round( ( $earned_points / $total_points ) * 100 );
		}

		$stats['score']         = $score;
		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;

		// Determine severity.
		$severity     = 'medium';
		$threat_level = 40;

		if ( $score < 30 ) {
			$severity     = 'high';
			$threat_level = 55;
		} elseif ( $score > 70 ) {
			$severity     = 'low';
			$threat_level = 25;
		}

		// Return finding if CTA testing is insufficient.
		if ( $score < 50 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: testing score percentage */
					__( 'CTA testing infrastructure score: %d%%. Regular testing of call-to-action elements can significantly improve conversion rates.', 'wpshadow' ),
					$score
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cta-testing?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		return null;
	}
}
