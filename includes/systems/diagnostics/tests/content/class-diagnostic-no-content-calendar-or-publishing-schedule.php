<?php
/**
 * No Content Calendar or Publishing Schedule Diagnostic
 *
 * Detects when content publishing is not scheduled,
 * causing inconsistent audience engagement.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Content Calendar or Publishing Schedule
 *
 * Checks whether content publication is scheduled
 * consistently with a content calendar.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Content_Calendar_Or_Publishing_Schedule extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-content-calendar-schedule';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Calendar & Publishing Schedule';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether content publishing is scheduled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for scheduled posts
		$scheduled_posts = get_posts( array(
			'post_type'      => 'post',
			'post_status'    => 'future',
			'posts_per_page' => 1,
		) );

		// Check recent publishing consistency
		$recent_posts = get_posts( array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 10,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );

		$has_calendar = ! empty( $scheduled_posts );

		if ( ! $has_calendar && count( $recent_posts ) < 5 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not scheduling content consistently, which means inconsistent audience engagement. Content calendars help: plan topics weeks ahead, publish consistently (same day/time), coordinate campaigns across channels, never run out of ideas. Consistent publishing trains audiences to expect content. Companies publishing 2+ times per week get 3x more traffic than those publishing less. Even small sites benefit from weekly schedules.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Content Consistency & Audience Growth',
					'potential_gain' => '+3x traffic from consistent publishing',
					'roi_explanation' => 'Publishing 2+ times per week consistently generates 3x more traffic than sporadic posting.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/content-calendar-schedule',
			);
		}

		return null;
	}
}
