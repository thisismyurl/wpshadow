<?php
/**
 * No Content Performance Tracking Diagnostic
 *
 * Tests whether content performance is being tracked. Not tracking which
 * content performs leads to blind content strategy.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5003.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_No_Performance_Tracking Class
 *
 * Detects when sites lack analytics integration to track content performance.
 * Data-driven content strategy requires knowing what works and what doesn't.
 *
 * @since 1.5003.1200
 */
class Diagnostic_No_Performance_Tracking extends Diagnostic_Base {

	protected static $slug = 'no-performance-tracking';
	protected static $title = 'No Content Performance Tracking';
	protected static $description = 'Tests whether content performance is being tracked';
	protected static $family = 'analytics';

	public static function check() {
		$score          = 0;
		$max_score      = 4;
		$score_details  = array();
		$recommendations = array();

		// Check for analytics plugins.
		$analytics_plugins = array(
			'google-site-kit/google-site-kit.php',
			'google-analytics-for-wordpress/googleanalytics.php',
			'ga-google-analytics/ga-google-analytics.php',
			'matomo/matomo.php',
		);

		$has_analytics = false;
		foreach ( $analytics_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_analytics = true;
				$score += 2;
				$score_details[] = __( '✓ Analytics plugin active', 'wpshadow' );
				break;
			}
		}

		if ( ! $has_analytics ) {
			$score_details[]   = __( '✗ No analytics plugin detected', 'wpshadow' );
			$recommendations[] = __( 'Install Google Site Kit or MonsterInsights for analytics integration', 'wpshadow' );
		}

		// Check for popular post plugins (indicates performance tracking).
		if ( is_plugin_active( 'wordpress-popular-posts/wordpress-popular-posts.php' ) ) {
			++$score;
			$score_details[] = __( '✓ Popular posts plugin active (tracks views)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No popular posts tracking', 'wpshadow' );
			$recommendations[] = __( 'Install WordPress Popular Posts to track content performance', 'wpshadow' );
		}

		// Check for post views meta (indicates tracking).
		$posts_with_views = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'     => 'views',
						'compare' => 'EXISTS',
					),
					array(
						'key'     => 'post_views_count',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		if ( ! empty( $posts_with_views ) ) {
			++$score;
			$score_details[] = __( '✓ Post view counts being tracked', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No view count tracking detected', 'wpshadow' );
			$recommendations[] = __( 'Enable view tracking to identify top performers', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage >= 70 ) {
			return null;
		}

		$severity = 'medium';
		$threat_level = 35;

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Performance tracking score: %d%%. You can\'t improve what you don\'t measure. Sites tracking content performance (pageviews, time on page, conversions) make data-driven decisions and see 60%% better ROI. Track: top pages, traffic sources, user behavior, conversion paths. Use insights to double down on what works.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/content-performance-tracking',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Performance tracking reveals which content drives traffic, engagement, and conversions, enabling data-driven content strategy.', 'wpshadow' ),
		);
	}
}
