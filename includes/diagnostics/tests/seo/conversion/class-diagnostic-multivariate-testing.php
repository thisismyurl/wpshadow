<?php
/**
 * Multivariate Testing Diagnostic
 *
 * Tests for complex multivariate testing experiments that test
 * multiple variables simultaneously.
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
 * Multivariate Testing Diagnostic Class
 *
 * Evaluates whether the site has multivariate testing
 * capability for complex optimization experiments.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Multivariate_Testing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'runs-multivariate-tests';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multivariate Testing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for complex multivariate testing experiments';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'conversion';

	/**
	 * Run the multivariate testing diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if multivariate testing issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check homepage for MVT-capable platforms.
		$homepage_url = home_url( '/' );
		$response = wp_remote_get( $homepage_url, array(
			'timeout' => 10,
			'sslverify' => false,
		) );

		$has_google_optimize_mvt = false;
		$has_optimizely = false;
		$has_vwo = false;
		$has_ab_tasty = false;
		$has_convert = false;

		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$html = wp_remote_retrieve_body( $response );

			// Google Optimize supports MVT.
			if ( preg_match( '/optimize\.google\.com|gtag.*optimize/i', $html ) ) {
				$has_google_optimize_mvt = true;
			}

			// Optimizely supports MVT.
			if ( preg_match( '/optimizely\.com\/js/i', $html ) ) {
				$has_optimizely = true;
			}

			// VWO supports MVT.
			if ( preg_match( '/visualwebsiteoptimizer\.com/i', $html ) ) {
				$has_vwo = true;
			}

			// AB Tasty supports MVT.
			if ( preg_match( '/abtasty\.com/i', $html ) ) {
				$has_ab_tasty = true;
			}

			// Convert supports MVT.
			if ( preg_match( '/convert\.com\/js/i', $html ) ) {
				$has_convert = true;
			}
		}

		$stats['has_google_optimize_mvt'] = $has_google_optimize_mvt;
		$stats['has_optimizely'] = $has_optimizely;
		$stats['has_vwo'] = $has_vwo;
		$stats['has_ab_tasty'] = $has_ab_tasty;
		$stats['has_convert'] = $has_convert;

		$has_mvt_platform = $has_google_optimize_mvt || $has_optimizely || $has_vwo || $has_ab_tasty || $has_convert;

		// Check for Google Analytics (required for MVT tracking).
		$has_google_analytics = false;
		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$html = wp_remote_retrieve_body( $response );
			if ( preg_match( '/gtag\(|ga\(|google-analytics\.com\/analytics\.js/i', $html ) ) {
				$has_google_analytics = true;
			}
		}

		$stats['has_google_analytics'] = $has_google_analytics;

		// Check for sufficient traffic (MVT requires more traffic than A/B).
		// This is a proxy check - we'll look for analytics and caching.
		$has_traffic_infrastructure = false;
		if ( is_plugin_active( 'google-site-kit/google-site-kit.php' ) ||
			 is_plugin_active( 'google-analytics-for-wordpress/googleanalytics.php' ) ) {
			$has_traffic_infrastructure = true;
		}

		$stats['has_traffic_infrastructure'] = $has_traffic_infrastructure;

		// Check for heatmaps (important for understanding MVT results).
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

		// Check for page builder (needed to create variants).
		$page_builders = array(
			'elementor/elementor.php'                      => 'Elementor',
			'beaver-builder-lite-version/fl-builder.php'   => 'Beaver Builder',
			'wp-bakery-visual-composer/plugin.php'         => 'WPBakery',
		);

		$has_page_builder = false;
		$active_page_builders = array();
		foreach ( $page_builders as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_page_builder = true;
				$active_page_builders[] = $name;
			}
		}

		$stats['has_page_builder'] = $has_page_builder;
		$stats['active_page_builders'] = $active_page_builders;

		// Check for conversion tracking (required to measure MVT success).
		$has_conversion_tracking = false;
		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$html = wp_remote_retrieve_body( $response );
			if ( preg_match( '/fbq\(|google.*\/collect|googleadservices\.com/i', $html ) ) {
				$has_conversion_tracking = true;
			}
		}

		$stats['has_conversion_tracking'] = $has_conversion_tracking;

		// Check for personalization (advanced MVT use case).
		$personalization_plugins = array(
			'if-so/if-so.php',
		);

		$has_personalization = false;
		foreach ( $personalization_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_personalization = true;
				break;
			}
		}

		$stats['has_personalization'] = $has_personalization;

		// Check for sufficient content to test.
		$post_count = wp_count_posts( 'post' );
		$page_count = wp_count_posts( 'page' );
		$total_content = ( $post_count->publish ?? 0 ) + ( $page_count->publish ?? 0 );

		$stats['total_content'] = $total_content;
		$has_sufficient_content = $total_content >= 20;

		// Calculate MVT readiness score.
		$mvt_features = 0;
		$total_features = 7;

		if ( $has_mvt_platform ) { $mvt_features++; }
		if ( $has_google_analytics ) { $mvt_features++; }
		if ( $has_traffic_infrastructure ) { $mvt_features++; }
		if ( $has_heatmap ) { $mvt_features++; }
		if ( $has_page_builder ) { $mvt_features++; }
		if ( $has_conversion_tracking ) { $mvt_features++; }
		if ( $has_sufficient_content ) { $mvt_features++; }

		$stats['mvt_readiness_score'] = round( ( $mvt_features / $total_features ) * 100, 1 );

		// Evaluate issues.
		if ( ! $has_mvt_platform ) {
			$issues[] = __( 'No MVT-capable platform detected - install Google Optimize, Optimizely, or VWO', 'wpshadow' );
		}

		if ( ! $has_google_analytics ) {
			$issues[] = __( 'Google Analytics not detected - critical for tracking MVT results', 'wpshadow' );
		}

		if ( ! $has_traffic_infrastructure ) {
			$warnings[] = __( 'No advanced analytics infrastructure - MVT requires significant traffic', 'wpshadow' );
		}

		if ( ! $has_heatmap ) {
			$warnings[] = __( 'No heatmap tracking - important for understanding MVT variant performance', 'wpshadow' );
		}

		if ( ! $has_page_builder ) {
			$warnings[] = __( 'No page builder detected - creating MVT variants is easier with visual builders', 'wpshadow' );
		}

		if ( ! $has_conversion_tracking ) {
			$warnings[] = __( 'No conversion tracking - critical for measuring MVT success', 'wpshadow' );
		}

		if ( ! $has_sufficient_content ) {
			$warnings[] = sprintf(
				/* translators: %d: number of pages/posts */
				__( 'Only %d pages/posts - MVT works best with more content to test', 'wpshadow' ),
				$total_content
			);
		}

		if ( ! $has_personalization ) {
			$warnings[] = __( 'No personalization plugin - advanced MVT can segment by user attributes', 'wpshadow' );
		}

		if ( $stats['mvt_readiness_score'] < 50 ) {
			$issues[] = sprintf(
				/* translators: %s: score percentage */
				__( 'MVT readiness score is low (%s%%) - MVT requires significant infrastructure', 'wpshadow' ),
				$stats['mvt_readiness_score']
			);
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Multivariate testing has issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/multivariate-testing',
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
				'description'  => __( 'Multivariate testing has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/multivariate-testing',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // MVT capability is present.
	}
}
