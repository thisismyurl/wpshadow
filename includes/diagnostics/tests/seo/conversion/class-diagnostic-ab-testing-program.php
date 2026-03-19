<?php
/**
 * AB Testing Program Active Diagnostic
 *
 * Tests for regular A/B testing on key pages to optimize
 * conversion rates and user experience.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AB Testing Program Active Diagnostic Class
 *
 * Evaluates whether the site has active A/B testing
 * infrastructure and experimentation programs.
 *
 * @since 1.6093.1200
 */
class Diagnostic_AB_Testing_Program extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'maintains-testing-program';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'AB Testing Program Active';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for regular A/B testing on key pages';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'conversion';

	/**
	 * Run the AB testing program diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if AB testing issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for A/B testing plugins.
		$ab_testing_plugins = array(
			'nelio-ab-testing/nelio-ab-testing.php'        => 'Nelio AB Testing',
			'simple-page-tester/spt_main.php'              => 'Simple Page Tester',
		);

		$active_ab_plugins = array();
		foreach ( $ab_testing_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_ab_plugins[] = $name;
			}
		}

		$stats['active_ab_plugins'] = $active_ab_plugins;
		$has_ab_plugin = ! empty( $active_ab_plugins );

		// Check homepage for A/B testing scripts.
		$homepage_url = home_url( '/' );
		$response = wp_remote_get( $homepage_url, array(
			'timeout' => 10,
			'sslverify' => false,
		) );

		$has_google_optimize = false;
		$has_optimizely = false;
		$has_vwo = false;
		$has_ab_tasty = false;
		$has_convert = false;

		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$html = wp_remote_retrieve_body( $response );

			// Check for Google Optimize.
			if ( preg_match( '/optimize\.google\.com|gtag.*optimize/i', $html ) ) {
				$has_google_optimize = true;
			}

			// Check for Optimizely.
			if ( preg_match( '/optimizely\.com\/js/i', $html ) ) {
				$has_optimizely = true;
			}

			// Check for VWO (Visual Website Optimizer).
			if ( preg_match( '/visualwebsiteoptimizer\.com|dev\.visualwebsiteoptimizer\.com/i', $html ) ) {
				$has_vwo = true;
			}

			// Check for AB Tasty.
			if ( preg_match( '/abtasty\.com/i', $html ) ) {
				$has_ab_tasty = true;
			}

			// Check for Convert.
			if ( preg_match( '/convert\.com\/js/i', $html ) ) {
				$has_convert = true;
			}
		}

		$stats['has_google_optimize'] = $has_google_optimize;
		$stats['has_optimizely'] = $has_optimizely;
		$stats['has_vwo'] = $has_vwo;
		$stats['has_ab_tasty'] = $has_ab_tasty;
		$stats['has_convert'] = $has_convert;

		$has_ab_service = $has_google_optimize || $has_optimizely || $has_vwo || $has_ab_tasty || $has_convert;

		// Check for Google Analytics (required for A/B testing).
		$has_google_analytics = false;
		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$html = wp_remote_retrieve_body( $response );
			if ( preg_match( '/gtag\(|ga\(|google-analytics\.com\/analytics\.js/i', $html ) ) {
				$has_google_analytics = true;
			}
		}

		$stats['has_google_analytics'] = $has_google_analytics;

		// Check for analytics plugins with A/B testing support.
		$analytics_plugins = array(
			'google-site-kit/google-site-kit.php'          => 'Site Kit',
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
		);

		$active_analytics_plugins = array();
		foreach ( $analytics_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_analytics_plugins[] = $name;
			}
		}

		$stats['active_analytics_plugins'] = $active_analytics_plugins;

		// Check for conversion tracking.
		$has_conversion_tracking = false;
		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$html = wp_remote_retrieve_body( $response );
			if ( preg_match( '/fbq\(|facebook\.com\/tr|google.*\/collect|googleadservices\.com/i', $html ) ) {
				$has_conversion_tracking = true;
			}
		}

		$stats['has_conversion_tracking'] = $has_conversion_tracking;

		// Check for form plugins (common A/B test target).
		$form_plugins = array(
			'contact-form-7/wp-contact-form-7.php',
			'wpforms-lite/wpforms.php',
			'ninja-forms/ninja-forms.php',
			'gravityforms/gravityforms.php',
		);

		$has_forms = false;
		foreach ( $form_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_forms = true;
				break;
			}
		}

		$stats['has_forms'] = $has_forms;

		// Check for heatmap/user behavior tracking (complements A/B testing).
		$has_heatmap = false;
		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$html = wp_remote_retrieve_body( $response );
			$heatmap_services = array( 'hotjar\.com', 'crazyegg\.com', 'mouseflow\.com', 'luckyorange\.com' );
			foreach ( $heatmap_services as $service ) {
				if ( preg_match( '/' . $service . '/i', $html ) ) {
					$has_heatmap = true;
					break;
				}
			}
		}

		$stats['has_heatmap'] = $has_heatmap;

		// Check for landing page builders (common for A/B testing).
		$landing_page_plugins = array(
			'elementor/elementor.php',
			'beaver-builder-lite-version/fl-builder.php',
			'wp-landing-kit/wp-landing-kit.php',
		);

		$has_landing_page_builder = false;
		foreach ( $landing_page_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_landing_page_builder = true;
				break;
			}
		}

		$stats['has_landing_page_builder'] = $has_landing_page_builder;

		// Check for call-to-action (CTA) plugins.
		$cta_plugins = array(
			'wordpress-calls-to-action/cta.php',
		);

		$has_cta_plugin = false;
		foreach ( $cta_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_cta_plugin = true;
				break;
			}
		}

		$stats['has_cta_plugin'] = $has_cta_plugin;

		// Calculate A/B testing infrastructure score.
		$testing_features = 0;
		$total_features = 8;

		if ( $has_ab_plugin || $has_ab_service ) { $testing_features++; }
		if ( $has_google_analytics ) { $testing_features++; }
		if ( $has_conversion_tracking ) { $testing_features++; }
		if ( $has_forms ) { $testing_features++; }
		if ( $has_heatmap ) { $testing_features++; }
		if ( $has_landing_page_builder ) { $testing_features++; }
		if ( ! empty( $active_analytics_plugins ) ) { $testing_features++; }
		if ( $has_cta_plugin ) { $testing_features++; }

		$stats['ab_testing_score'] = round( ( $testing_features / $total_features ) * 100, 1 );

		// Evaluate issues.
		if ( ! $has_ab_plugin && ! $has_ab_service ) {
			$issues[] = __( 'No A/B testing infrastructure detected - install Google Optimize, Optimizely, or VWO', 'wpshadow' );
		}

		if ( ! $has_google_analytics ) {
			$issues[] = __( 'Google Analytics not detected - critical for tracking A/B test results', 'wpshadow' );
		}

		if ( ! $has_conversion_tracking ) {
			$warnings[] = __( 'No conversion tracking detected - implement goal tracking for A/B tests', 'wpshadow' );
		}

		if ( ! $has_heatmap ) {
			$warnings[] = __( 'No heatmap/behavior tracking - complements A/B testing with user behavior data', 'wpshadow' );
		}

		if ( ! $has_landing_page_builder ) {
			$warnings[] = __( 'No landing page builder - consider Elementor for easy page variant creation', 'wpshadow' );
		}

		if ( ! $has_forms ) {
			$warnings[] = __( 'No form plugin detected - forms are common A/B testing targets', 'wpshadow' );
		}

		if ( empty( $active_analytics_plugins ) ) {
			$warnings[] = __( 'No analytics plugin active - consider Site Kit or MonsterInsights', 'wpshadow' );
		}

		if ( $stats['ab_testing_score'] < 50 ) {
			$issues[] = sprintf(
				/* translators: %s: score percentage */
				__( 'A/B testing infrastructure score is low (%s%%) - build out testing capability', 'wpshadow' ),
				$stats['ab_testing_score']
			);
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'A/B testing program has issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ab-testing',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'A/B testing program has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ab-testing',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // A/B testing program is active.
	}
}
