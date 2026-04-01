<?php
/**
 * Content Scheduling Not Implemented Diagnostic
 *
 * Checks if content scheduling is implemented.
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
 * Content Scheduling Not Implemented Diagnostic Class
 *
 * Detects missing content scheduling.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Content_Scheduling_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-scheduling-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Scheduling Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if content scheduling is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// WordPress has native scheduling built-in.
		// Check if users are actually using it.
		$scheduled_posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'future',
				'posts_per_page' => -1,
			)
		);

		$has_scheduled_posts = ! empty( $scheduled_posts );

		// If users are scheduling posts, feature is being used.
		if ( $has_scheduled_posts ) {
			return null;
		}

		// Check for editorial calendar plugins.
		$calendar_plugins = array(
			'editorial-calendar/editorial-calendar.php' => 'Editorial Calendar',
			'publishpress/publishpress.php'            => 'PublishPress',
			'wp-scheduled-posts/wp-scheduled-posts.php' => 'WP Scheduled Posts',
		);

		$calendar_plugin_detected = false;
		$calendar_plugin_name     = '';

		foreach ( $calendar_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$calendar_plugin_detected = true;
				$calendar_plugin_name     = $name;
				break;
			}
		}

		// Check if site publishes frequently (content-heavy site).
		$recent_posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 10,
				'date_query'     => array(
					array(
						'after' => '30 days ago',
					),
				),
			)
		);

		$posts_per_month = count( $recent_posts );

		// Only flag for content-heavy sites (10+ posts/month).
		if ( $posts_per_month >= 10 && ! $has_scheduled_posts && ! $calendar_plugin_detected ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: posts per month */
					__( 'Content scheduling underutilized. You publish %d posts/month but have no scheduled posts. Use WordPress native scheduling or Editorial Calendar plugin to plan content in advance, maintain consistent publishing, and work during your most productive hours.', 'wpshadow' ),
					$posts_per_month
				),
				'severity'    => 'low',
				'threat_level' => 10,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/content-scheduling?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'posts_per_month'     => $posts_per_month,
					'scheduled_posts'     => 0,
					'calendar_plugin'     => false,
					'recommendation'      => __( 'WordPress has built-in scheduling: Edit post → set "Publish" date/time → Click "Schedule". For visual planning: install Editorial Calendar (free, 300K+ installs) to see all posts in monthly calendar view, drag-and-drop to reschedule.', 'wpshadow' ),
					'scheduling_benefits' => array(
						'consistency' => 'Publish at optimal times even when away',
						'planning' => 'Write content in batches during productive hours',
						'workflow' => 'Plan editorial calendar weeks in advance',
						'timezone' => 'Schedule for audience timezone (publish 9am their time)',
					),
					'use_cases' => array(
						'vacation' => 'Schedule 2 weeks of posts before vacation',
						'optimal_timing' => 'Publish at 7am when audience is most active',
						'batch_writing' => 'Write 5 posts on Monday, schedule for whole week',
					),
					'native_wordpress' => array(
						'feature' => 'Built into WordPress since version 2.0',
						'how_to' => 'Edit post → Click "Publish" → Select future date/time → "Schedule"',
						'reliability' => 'Uses wp-cron (very reliable)',
					),
				),
			);
		}

		// No issues - low-volume site or already scheduling.
		return null;
	}
}
