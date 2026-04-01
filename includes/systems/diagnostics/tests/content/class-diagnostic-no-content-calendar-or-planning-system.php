<?php
/**
 * Content Calendar and Planning System Diagnostic
 *
 * Detects when a content calendar or planning system is not implemented
 * for strategic content management and consistency.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Content Calendar or Planning System
 *
 * Checks whether a content calendar or planning system
 * is in place for strategic content management.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Content_Calendar_Or_Planning_System extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-content-calendar-planning-system';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Calendar & Planning System';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether a content calendar is in place';

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
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for calendar/planning plugins
		$has_calendar_plugin = is_plugin_active( 'wordpress-web-publishing-calendar/pwpc.php' ) ||
			is_plugin_active( 'coschedule-editorial-calendar/coschedule-editorial-calendar.php' ) ||
			is_plugin_active( 'editorial-calendar/editorial-calendar.php' );

		// Check for custom calendar system
		$has_custom_calendar = get_option( 'wpshadow_content_calendar_system' );

		// Check post frequency (less than 4 per month = inconsistent)
		$args = array(
			'post_type'      => 'post',
			'posts_per_page' => 100,
			'post_status'    => 'publish',
			'date_query'     => array(
				array(
					'after'  => '30 days ago',
					'before' => 'today',
				),
			),
		);
		$recent_posts = get_posts( $args );
		$post_frequency = count( $recent_posts );

		if ( ! $has_calendar_plugin && ! $has_custom_calendar && $post_frequency < 4 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re publishing fewer than 4 posts per month and don\'t have a content calendar, which suggests content is reactive rather than strategic. A content calendar helps you plan content around seasons, launches, and customer needs months in advance. Without planning, content becomes inconsistent and reactive. With a calendar, you can coordinate SEO topics, social posts, email campaigns, and sales support. Planned content typically performs 3-5x better than reactive content.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'post_frequency' => $post_frequency,
				'business_impact' => array(
					'metric'         => 'Content Consistency & Performance',
					'potential_gain' => '3-5x better content performance',
					'roi_explanation' => 'Planned content aligns with business goals, SEO strategy, and customer needs, resulting in much better engagement and ROI.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/content-calendar-planning?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
