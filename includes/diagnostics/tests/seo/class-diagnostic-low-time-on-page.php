<?php
/**
 * Diagnostic: Low Time on Page
 *
 * Detects pages with <30 seconds average time on page, indicating content
 * not engaging or misleading title/meta description.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7030.1446
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Low Time on Page Diagnostic Class
 *
 * Checks for pages with very low engagement time via analytics data.
 *
 * Detection methods:
 * - Analytics plugin integration
 * - Time on page meta data
 * - Engagement metrics
 *
 * @since 1.7030.1446
 */
class Diagnostic_Low_Time_On_Page extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'low-time-on-page';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Low Time on Page';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects pages with <30 seconds average time, indicating poor engagement';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-engagement';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (3 points):
	 * - 2 points: Analytics plugin installed
	 * - 1 point: Time on page tracking exists
	 * - 0 points if low time detected
	 *
	 * @since  1.7030.1446
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score         = 0;
		$max_score     = 3;
		$has_analytics = false;
		$low_time_data = array();

		// Check for analytics plugins.
		$analytics_plugins = array(
			'google-site-kit/google-site-kit.php'           => 'Google Site Kit',
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'matomo/matomo.php'                             => 'Matomo',
		);

		foreach ( $analytics_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score        += 2;
				$has_analytics = true;
				break;
			}
		}

		// Check for time on page meta.
		global $wpdb;
		$time_meta = $wpdb->get_results(
			"SELECT post_id, meta_value 
			FROM {$wpdb->postmeta} 
			WHERE meta_key LIKE '%time%page%' 
			OR meta_key LIKE '%avg_time%'
			OR meta_key LIKE '%duration%'
			LIMIT 10"
		);

		if ( ! empty( $time_meta ) ) {
			$score++;
			foreach ( $time_meta as $meta ) {
				$value = maybe_unserialize( $meta->meta_value );
				if ( is_numeric( $value ) && $value < 30 && $value > 0 ) {
					$post = get_post( $meta->post_id );
					if ( $post && 'post' === $post->post_type ) {
						$low_time_data[] = array(
							'post_id'     => $post->ID,
							'title'       => $post->post_title,
							'avg_seconds' => intval( $value ),
							'url'         => get_permalink( $post->ID ),
						);
					}
				}
			}
		}

		// If no analytics, check post length as proxy.
		if ( ! $has_analytics ) {
			$posts = get_posts(
				array(
					'post_type'      => 'post',
					'posts_per_page' => 20,
					'orderby'        => 'date',
					'order'          => 'DESC',
				)
			);

			$short_posts = 0;
			foreach ( $posts as $post ) {
				$word_count = str_word_count( wp_strip_all_tags( $post->post_content ) );
				// <300 words = likely <30 seconds reading time.
				if ( $word_count < 300 ) {
					$short_posts++;
				}
			}

			if ( $short_posts > ( count( $posts ) * 0.4 ) ) {
				// 40%+ posts are very short.
				$score = 0;
			}
		}

		// If we found low time pages.
		if ( ! empty( $low_time_data ) ) {
			$score = 0;
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.7 ) ) {
			return null;
		}

		// Build finding message.
		if ( ! $has_analytics && empty( $low_time_data ) ) {
			$message = __( 'No analytics plugin detected to measure time on page. Over 40% of recent posts are very short (<300 words)', 'wpshadow' );
		} else {
			$message = sprintf(
				/* translators: %d: number of pages with low time */
				_n(
					'Found %d page with <30 seconds average time',
					'Found %d pages with <30 seconds average time',
					count( $low_time_data ),
					'wpshadow'
				),
				count( $low_time_data )
			);
		}

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %s: issue message */
				__( '%s. Time on page <30 seconds suggests: misleading title/description, content doesn\'t match search intent, poor formatting/readability, intrusive ads/popups, slow page load. Average blog post reading time: 3-4 minutes. Target: >2 minutes for educational content, >1 minute for news. Dwell time is a ranking factor.', 'wpshadow' ),
				$message
			),
			'severity'      => 'critical',
			'threat_level'  => 60,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/low-time-on-page',
			'problem_pages' => $low_time_data,
			'recommendation' => __( 'Install analytics to track time on page. Review low-time pages: ensure title/meta match content, improve formatting (headings, lists, images), enhance introduction, reduce distractions, add engaging media.', 'wpshadow' ),
		);
	}
}
