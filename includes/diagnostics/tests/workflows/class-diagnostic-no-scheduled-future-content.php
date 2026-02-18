<?php
/**
 * No Scheduled Future Content Diagnostic
 *
 * Detects lack of content scheduling, indicating reactive rather
 * than strategic content planning.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Publishing
 * @since      1.6034.2212
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Scheduled Future Content Diagnostic Class
 *
 * Checks for scheduled posts to assess content planning maturity
 * and strategic publishing approach.
 *
 * **Why This Matters:**
 * - Scheduled content indicates planning and strategy
 * - Consistent publishing maintains audience engagement
 * - Reduces last-minute content scrambles
 * - Enables batch content creation
 * - Better work-life balance for creators
 *
 * **Content Scheduling Benefits:**
 * - Consistent publishing schedule
 * - Time for proper editing and review
 * - Strategic timing optimization
 * - Team coordination for campaigns
 * - Buffer for unexpected events
 *
 * @since 1.6034.2212
 */
class Diagnostic_No_Scheduled_Future_Content extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-scheduled-future-content';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Scheduled Future Content';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'No scheduled posts detected, indicating reactive rather than strategic planning';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'publishing';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6034.2212
	 * @return array|null Finding array if no scheduled content, null otherwise.
	 */
	public static function check() {
		// Check for scheduled posts
		$scheduled_posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'future',
				'posts_per_page' => 10,
			)
		);

		if ( ! empty( $scheduled_posts ) ) {
			return null; // Scheduling is being used
		}

		// Check recent publishing history
		$recent_posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 10,
			)
		);

		if ( empty( $recent_posts ) ) {
			return null; // No content to schedule
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No scheduled posts found. Plan and schedule content in advance for consistent publishing and better strategy.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/content-scheduling',
			'details'      => array(
				'message'        => 'Start scheduling content 1-2 weeks in advance',
				'benefits'       => array(
					'Consistent publishing schedule',
					'Better content quality (time for review)',
					'Reduced last-minute stress',
					'Strategic timing optimization',
					'Team coordination',
				),
				'recommendation' => 'Create content calendar and batch-create posts',
			),
		);
	}
}
