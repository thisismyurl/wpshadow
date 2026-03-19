<?php
/**
 * AB Testing Program Diagnostic
 *
 * Tests for evidence of ongoing A/B testing program including testing
 * tools, optimization plugins, and conversion testing implementations.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AB Testing Program Diagnostic Class
 *
 * Verifies A/B testing and conversion optimization tools are implemented
 * to enable data-driven decision making.
 *
 * @since 1.6093.1200
 */
class Diagnostic_AB_Testing extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'runs_ab_tests';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'AB Testing Program';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies evidence of ongoing A/B testing program exists';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check for dedicated A/B testing plugins (40 points).
		$ab_plugins = array(
			'nelio-ab-testing/nelio-ab-testing.php'            => 'Nelio A/B Testing',
			'simple-page-tester/simple-page-tester.php'        => 'Simple Page Tester',
			'ab-tasty/ab-tasty.php'                            => 'AB Tasty',
			'optimizely/optimizely.php'                        => 'Optimizely',
		);

		$active_ab = array();
		foreach ( $ab_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_ab[]    = $plugin_name;
				$earned_points += 20; // Up to 40 points.
			}
		}

		if ( count( $active_ab ) > 0 ) {
			$stats['ab_testing_plugins'] = implode( ', ', $active_ab );
		} else {
			$issues[] = 'No A/B testing plugins detected';
		}

		// Check for Google Optimize integration (25 points).
		$optimize_plugins = array(
			'google-optimize/google-optimize.php'              => 'Google Optimize',
			'google-site-kit/google-site-kit.php'              => 'Google Site Kit',
		);

		$active_optimize = array();
		foreach ( $optimize_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_optimize[] = $plugin_name;
				$earned_points    += 13; // Up to 25 points.
			}
		}

		if ( count( $active_optimize ) > 0 ) {
			$stats['google_optimize_plugins'] = implode( ', ', $active_optimize );
		} else {
			$warnings[] = 'No Google Optimize integration detected';
		}

		// Check for conversion optimization plugins (20 points).
		$conversion_plugins = array(
			'optin-monster/optin-monster.php'                  => 'OptinMonster',
			'thrive-leads/thrive-leads.php'                    => 'Thrive Leads',
			'convertpro/convertpro.php'                        => 'Convert Pro',
			'elementor-pro/elementor-pro.php'                  => 'Elementor Pro',
		);

		$active_conversion = array();
		foreach ( $conversion_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_conversion[] = $plugin_name;
				$earned_points      += 7; // Up to 20 points.
			}
		}

		if ( count( $active_conversion ) > 0 ) {
			$stats['conversion_plugins'] = implode( ', ', $active_conversion );
		} else {
			$warnings[] = 'No conversion optimization plugins detected';
		}

		// Check for analytics with conversion tracking (15 points).
		$analytics_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'google-analytics-dashboard-for-wp/gadwp.php'        => 'ExactMetrics',
			'matomo/matomo.php'                                  => 'Matomo Analytics',
		);

		$active_analytics = array();
		foreach ( $analytics_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_analytics[] = $plugin_name;
				$earned_points     += 8; // Up to 15 points.
			}
		}

		if ( count( $active_analytics ) > 0 ) {
			$stats['analytics_plugins'] = implode( ', ', $active_analytics );
		}

		// Calculate score percentage.
		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;

		// Return finding if score is below 40%.
		if ( $score < 40 ) {
			$severity     = $score < 20 ? 'medium' : 'low';
			$threat_level = $score < 20 ? 35 : 25;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your A/B testing capability scored %s. Without systematic testing, you\'re making website changes based on opinions instead of data. A/B testing lets you test headlines, layouts, CTAs, and designs to see what actually converts better.', 'wpshadow' ),
					$score_text
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ab-testing-program',
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
