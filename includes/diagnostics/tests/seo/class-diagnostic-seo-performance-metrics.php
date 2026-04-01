<?php
/**
 * SEO Performance Metrics Diagnostic
 *
 * Tests if SEO metrics are tracked and reported through
 * analytics tools and SEO monitoring platforms.
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
 * SEO Performance Metrics Diagnostic Class
 *
 * Evaluates whether the site has proper SEO tracking and
 * analytics implementation for measuring SEO performance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_SEO_Performance_Metrics extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tracks-seo-metrics';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SEO Performance Metrics Tracking';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if SEO metrics are tracked and reported';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the SEO performance metrics diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if SEO metrics tracking issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for analytics plugins.
		$analytics_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'google-site-kit/google-site-kit.php'                => 'Site Kit by Google',
			'ga-google-analytics/ga-google-analytics.php'        => 'GA Google Analytics',
			'google-analytics-dashboard-for-wp/gadwp.php'        => 'ExactMetrics',
		);

		$active_analytics_plugin = null;
		foreach ( $analytics_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_analytics_plugin = $name;
				break;
			}
		}

		$stats['analytics_plugin'] = $active_analytics_plugin;

		// Check for SEO plugins with tracking features.
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php'                   => 'Yoast SEO',
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'All in One SEO',
			'rank-math-seo/rank-math-seo.php'            => 'Rank Math',
			'seopress/seopress.php'                      => 'SEOPress',
		);

		$active_seo_plugin = null;
		foreach ( $seo_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_seo_plugin = $name;
				break;
			}
		}

		$stats['seo_plugin'] = $active_seo_plugin;

		// Check for Google Analytics tracking code.
		$homepage_url = home_url( '/' );
		$response = wp_remote_get( $homepage_url, array(
			'timeout' => 10,
			'sslverify' => false,
		) );

		$has_google_analytics = false;
		$has_google_tag_manager = false;
		$has_search_console = false;

		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$html = wp_remote_retrieve_body( $response );

			// Check for Google Analytics (GA4 or Universal Analytics).
			if ( preg_match( '/gtag\(|ga\(|google-analytics\.com\/analytics\.js|googletagmanager\.com\/gtag\/js/i', $html ) ) {
				$has_google_analytics = true;
			}

			// Check for Google Tag Manager.
			if ( preg_match( '/googletagmanager\.com\/gtm\.js|GTM-[A-Z0-9]+/i', $html ) ) {
				$has_google_tag_manager = true;
			}

			// Check for Google Search Console verification.
			if ( preg_match( '/google-site-verification/i', $html ) ) {
				$has_search_console = true;
			}
		}

		$stats['has_google_analytics'] = $has_google_analytics;
		$stats['has_google_tag_manager'] = $has_google_tag_manager;
		$stats['has_search_console'] = $has_search_console;

		// Check for XML sitemap (important for tracking indexation).
		$sitemap_urls = array(
			home_url( '/sitemap.xml' ),
			home_url( '/sitemap_index.xml' ),
			home_url( '/wp-sitemap.xml' ), // WordPress core sitemap.
		);

		$has_sitemap = false;
		foreach ( $sitemap_urls as $sitemap_url ) {
			$response = wp_remote_head( $sitemap_url, array(
				'timeout' => 5,
				'sslverify' => false,
			) );

			if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
				$has_sitemap = true;
				break;
			}
		}

		$stats['has_sitemap'] = $has_sitemap;

		// Check for robots.txt.
		$robots_url = home_url( '/robots.txt' );
		$response = wp_remote_head( $robots_url, array(
			'timeout' => 5,
			'sslverify' => false,
		) );

		$has_robots_txt = false;
		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$has_robots_txt = true;
		}

		$stats['has_robots_txt'] = $has_robots_txt;

		// Check for rank tracking plugins.
		$rank_tracking_plugins = array(
			'wincher/wincher.php'                        => 'Wincher',
			'se-ranking/se-ranking.php'                  => 'SE Ranking',
		);

		$has_rank_tracking = false;
		foreach ( $rank_tracking_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_rank_tracking = true;
				break;
			}
		}

		$stats['has_rank_tracking'] = $has_rank_tracking;

		// Check for heatmap/user tracking.
		$has_heatmap = false;
		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$html = wp_remote_retrieve_body( wp_remote_get( $homepage_url ) );

			$heatmap_services = array(
				'hotjar\.com',
				'crazyegg\.com',
				'mouseflow\.com',
				'inspectlet\.com',
				'luckyorange\.com',
			);

			foreach ( $heatmap_services as $service ) {
				if ( preg_match( '/' . $service . '/i', $html ) ) {
					$has_heatmap = true;
					break;
				}
			}
		}

		$stats['has_heatmap'] = $has_heatmap;

		// Check if site is blocking search engines.
		$blog_public = get_option( 'blog_public', 1 );
		$stats['search_engines_allowed'] = ( 1 === (int) $blog_public );

		if ( ! $stats['search_engines_allowed'] ) {
			$issues[] = __( 'Site is blocking search engines - SEO metrics cannot be tracked', 'wpshadow' );
		}

		// Check for conversion tracking.
		$has_conversion_tracking = false;
		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$html = wp_remote_retrieve_body( wp_remote_get( $homepage_url ) );

			// Check for Facebook Pixel, Google Ads conversion, etc.
			if ( preg_match( '/fbq\(|facebook\.com\/tr|google-analytics\.com.*\/collect|googleadservices\.com|google\.com\/ads/i', $html ) ) {
				$has_conversion_tracking = true;
			}
		}

		$stats['has_conversion_tracking'] = $has_conversion_tracking;

		// Calculate tracking completeness score.
		$tracking_features = 0;
		$total_features = 8;

		if ( $active_analytics_plugin ) { $tracking_features++; }
		if ( $has_google_analytics ) { $tracking_features++; }
		if ( $has_search_console ) { $tracking_features++; }
		if ( $has_sitemap ) { $tracking_features++; }
		if ( $has_robots_txt ) { $tracking_features++; }
		if ( $active_seo_plugin ) { $tracking_features++; }
		if ( $has_conversion_tracking ) { $tracking_features++; }
		if ( $stats['search_engines_allowed'] ) { $tracking_features++; }

		$stats['seo_tracking_score'] = round( ( $tracking_features / $total_features ) * 100, 1 );

		// Evaluate issues.
		if ( ! $has_google_analytics && ! $active_analytics_plugin ) {
			$issues[] = __( 'No analytics tracking detected - install Google Analytics or similar', 'wpshadow' );
		}

		if ( ! $has_search_console ) {
			$issues[] = __( 'Google Search Console not verified - critical for tracking search performance', 'wpshadow' );
		}

		if ( ! $has_sitemap ) {
			$issues[] = __( 'No XML sitemap detected - search engines cannot discover all pages', 'wpshadow' );
		}

		if ( ! $active_seo_plugin ) {
			$warnings[] = __( 'No SEO plugin active - consider Yoast SEO or Rank Math for better tracking', 'wpshadow' );
		}

		if ( ! $has_robots_txt ) {
			$warnings[] = __( 'No robots.txt file detected - add to control search engine crawling', 'wpshadow' );
		}

		if ( ! $has_rank_tracking ) {
			$warnings[] = __( 'No rank tracking detected - consider monitoring keyword rankings', 'wpshadow' );
		}

		if ( ! $has_heatmap ) {
			$warnings[] = __( 'No user behavior tracking (heatmaps) detected - consider Hotjar or Crazy Egg', 'wpshadow' );
		}

		if ( ! $has_conversion_tracking ) {
			$warnings[] = __( 'No conversion tracking detected - implement goal tracking in analytics', 'wpshadow' );
		}

		if ( $has_google_analytics && ! $has_google_tag_manager ) {
			$warnings[] = __( 'Google Tag Manager not detected - consider for easier tracking management', 'wpshadow' );
		}

		if ( $stats['seo_tracking_score'] < 50 ) {
			$issues[] = sprintf(
				/* translators: %s: score percentage */
				__( 'SEO tracking score is low (%s%%) - implement more tracking tools', 'wpshadow' ),
				$stats['seo_tracking_score']
			);
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'SEO metrics tracking has issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/seo-metrics-tracking?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
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
				'description'  => __( 'SEO metrics tracking has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/seo-metrics-tracking?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // SEO metrics tracking is well implemented.
	}
}
