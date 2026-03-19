<?php
/**
 * External Link Tracking Not Implemented Diagnostic
 *
 * Checks if external links are tracked.
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
 * External Link Tracking Not Implemented Diagnostic Class
 *
 * Detects missing external link tracking.
 *
 * @since 1.6093.1200
 */
class Diagnostic_External_Link_Tracking_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'external-link-tracking-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'External Link Tracking Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if external links are tracked';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for analytics plugins that track external links.
		$analytics_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'google-analytics-dashboard-for-wp/gadwp.php'        => 'ExactMetrics',
			'ga-google-analytics/ga-google-analytics.php'        => 'GA Google Analytics',
		);

		$analytics_detected = false;
		$analytics_name     = '';

		foreach ( $analytics_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$analytics_detected = true;
				$analytics_name     = $name;
				break;
			}
		}

		// Check for Google Analytics manually added.
		$has_ga_code = false;
		if ( function_exists( 'wp_head' ) ) {
			ob_start();
			do_action( 'wp_head' );
			$head_content = ob_get_clean();
			
			if ( strpos( $head_content, 'gtag' ) !== false || strpos( $head_content, 'ga(' ) !== false || strpos( $head_content, 'analytics.js' ) !== false ) {
				$has_ga_code = true;
			}
		}

		// Count external links in recent posts.
		$recent_posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 10,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$external_link_count = 0;
		$site_url            = home_url();

		foreach ( $recent_posts as $post ) {
			preg_match_all( '/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $post->post_content, $matches );
			if ( ! empty( $matches[1] ) ) {
				foreach ( $matches[1] as $url ) {
					// Check if external.
					if ( strpos( $url, 'http' ) === 0 && strpos( $url, $site_url ) === false ) {
						$external_link_count++;
					}
				}
			}
		}

		// If site has external links but no tracking.
		if ( $external_link_count > 5 && ! $analytics_detected && ! $has_ga_code ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: number of external links */
					__( 'External link tracking not implemented. Found %d external links in recent posts, but no analytics tracking. You can\'t measure which external links users click, limiting insights into user behavior and affiliate link performance. Install MonsterInsights or ExactMetrics for automatic outbound link tracking.', 'wpshadow' ),
					$external_link_count
				),
				'severity'    => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/external-link-tracking',
				'details'     => array(
					'external_link_count' => $external_link_count,
					'analytics_detected'  => false,
					'has_ga_code'         => false,
					'recommendation'      => __( 'Install MonsterInsights (free, 3M+ installs) or ExactMetrics. Both include automatic outbound link tracking in Google Analytics. Tracks clicks on external links, downloads, affiliate links. No code needed.', 'wpshadow' ),
					'tracking_benefits'   => array(
						'user_behavior' => 'Understand what resources users find valuable',
						'affiliate_links' => 'Measure affiliate link click-through rates',
						'partnerships' => 'Show partners referral traffic data',
						'content_strategy' => 'Identify which external sources users prefer',
					),
					'use_cases'           => array(
						'affiliate_marketing' => 'Track which affiliate links convert',
						'resource_pages' => 'Measure which tools/resources get clicked',
						'partnerships' => 'Report referral traffic to partners',
						'user_research' => 'Learn what external content interests users',
					),
					'implementation'      => array(
						'easy' => 'MonsterInsights auto-tracks external links',
						'manual' => 'Add gtag("event") on link clicks',
						'reports' => 'View in Google Analytics: Events → Outbound Links',
					),
				),
			);
		}

		// No issues - tracking implemented or no external links.
		return null;
	}
}
