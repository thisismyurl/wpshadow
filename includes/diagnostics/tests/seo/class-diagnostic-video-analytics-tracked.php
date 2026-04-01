<?php
/**
 * Video Analytics Tracked Diagnostic
 *
 * Tests whether the site tracks and reviews video analytics regularly (watch time, retention, engagement).
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Video Analytics Tracked Diagnostic Class
 *
 * Data-driven video optimization increases performance by 85%. Regular analytics
 * review is essential for understanding what resonates with your audience.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Video_Analytics_Tracked extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'video-analytics-tracked';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Video Analytics Tracked';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site tracks and reviews video analytics regularly (watch time, retention, engagement)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'video-marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$analytics_score = 0;
		$max_score = 6;

		// Check for analytics platform.
		$analytics_platform = self::check_analytics_platform();
		if ( $analytics_platform ) {
			$analytics_score++;
		} else {
			$issues[] = __( 'No video analytics platform configured', 'wpshadow' );
		}

		// Check for watch time tracking.
		$watch_time = self::check_watch_time_tracking();
		if ( $watch_time ) {
			$analytics_score++;
		} else {
			$issues[] = __( 'Not tracking video watch time or session duration', 'wpshadow' );
		}

		// Check for retention analysis.
		$retention_analysis = self::check_retention_analysis();
		if ( $retention_analysis ) {
			$analytics_score++;
		} else {
			$issues[] = __( 'No audience retention analysis performed', 'wpshadow' );
		}

		// Check for engagement metrics.
		$engagement_metrics = self::check_engagement_metrics();
		if ( $engagement_metrics ) {
			$analytics_score++;
		} else {
			$issues[] = __( 'Not tracking likes, comments, shares', 'wpshadow' );
		}

		// Check for traffic sources.
		$traffic_sources = self::check_traffic_sources();
		if ( $traffic_sources ) {
			$analytics_score++;
		} else {
			$issues[] = __( 'Not analyzing video traffic sources', 'wpshadow' );
		}

		// Check for regular reporting.
		$regular_reporting = self::check_regular_reporting();
		if ( $regular_reporting ) {
			$analytics_score++;
		} else {
			$issues[] = __( 'No regular video performance reports or reviews', 'wpshadow' );
		}

		// Determine severity based on analytics tracking.
		$analytics_percentage = ( $analytics_score / $max_score ) * 100;

		if ( $analytics_percentage < 35 ) {
			$severity = 'low';
			$threat_level = 30;
		} elseif ( $analytics_percentage < 60 ) {
			$severity = 'low';
			$threat_level = 20;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Analytics tracking percentage */
				__( 'Video analytics tracking at %d%%. ', 'wpshadow' ),
				(int) $analytics_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Data-driven optimization increases performance by 85%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/video-analytics-tracked?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}

	/**
	 * Check analytics platform.
	 *
	 * @since 0.6093.1200
	 * @return bool True if configured, false otherwise.
	 */
	private static function check_analytics_platform() {
		// Check for YouTube (built-in analytics).
		$youtube = new \WP_Query(
			array(
				's'              => 'youtube.com/watch',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		if ( $youtube->have_posts() ) {
			return true;
		}

		// Check for analytics plugins.
		if ( is_plugin_active( 'google-analytics-for-wordpress/googleanalytics.php' ) ||
			 is_plugin_active( 'google-site-kit/google-site-kit.php' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check watch time tracking.
	 *
	 * @since 0.6093.1200
	 * @return bool True if tracked, false otherwise.
	 */
	private static function check_watch_time_tracking() {
		// Check for watch time references.
		$keywords = array( 'watch time', 'view duration', 'session length', 'average view' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => 'any',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check retention analysis.
	 *
	 * @since 0.6093.1200
	 * @return bool True if analyzed, false otherwise.
	 */
	private static function check_retention_analysis() {
		// Check for retention references.
		$keywords = array( 'retention', 'drop-off', 'audience retention', 'engagement graph' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => 'any',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check engagement metrics.
	 *
	 * @since 0.6093.1200
	 * @return bool True if tracked, false otherwise.
	 */
	private static function check_engagement_metrics() {
		// Check for engagement references.
		$keywords = array( 'likes', 'comments', 'shares', 'engagement rate' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword . ' video',
					'post_type'      => 'any',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check traffic sources.
	 *
	 * @since 0.6093.1200
	 * @return bool True if analyzed, false otherwise.
	 */
	private static function check_traffic_sources() {
		// Check for traffic source references.
		$keywords = array( 'traffic source', 'discovery', 'search', 'suggested videos', 'external' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword . ' video',
					'post_type'      => 'any',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check regular reporting.
	 *
	 * @since 0.6093.1200
	 * @return bool True if reporting, false otherwise.
	 */
	private static function check_regular_reporting() {
		// Check for recent analytics posts.
		$query = new \WP_Query(
			array(
				's'              => 'analytics report performance metrics',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
				'date_query'     => array(
					array(
						'after' => '3 months ago',
					),
				),
			)
		);

		return $query->have_posts();
	}
}
