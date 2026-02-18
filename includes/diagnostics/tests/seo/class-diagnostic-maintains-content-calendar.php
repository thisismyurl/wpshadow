<?php
/**
 * Content Calendar Exists Diagnostic
 *
 * Verifies that site maintains a content calendar for strategic planning
 * and consistent content production.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since      1.6034.2321
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Calendar Diagnostic Class
 *
 * Checks for evidence of content calendar usage by examining scheduled
 * posts, draft patterns, and editorial planning signals.
 *
 * **Why This Matters:**
 * - Content calendars increase productivity by 60%
 * - Strategic planning beats reactive posting
 * - Scheduled content maintains consistency
 * - Editorial planning improves quality
 * - Team coordination requires calendaring
 *
 * **Indicators of Calendar Usage:**
 * - Scheduled future posts exist
 * - Draft posts in pipeline
 * - Regular publishing intervals
 * - Editorial plugins installed
 * - Content planning tools active
 *
 * @since 1.6034.2321
 */
class Diagnostic_Maintains_Content_Calendar extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'maintains-content-calendar';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Calendar Exists';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies site maintains content calendar for strategic planning';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6034.2321
	 * @return array|null Finding array if no calendar detected, null otherwise.
	 */
	public static function check() {
		$calendar_score = 0;
		$evidence = array();

		// Check 1: Scheduled posts (strongest indicator)
		$scheduled_count = self::count_scheduled_posts();
		if ( $scheduled_count > 0 ) {
			$calendar_score += 40;
			$evidence[] = sprintf(
				/* translators: %d: number of scheduled posts */
				__( '%d scheduled post(s) found', 'wpshadow' ),
				$scheduled_count
			);
		}

		// Check 2: Editorial calendar plugins
		if ( self::has_editorial_plugin() ) {
			$calendar_score += 30;
			$evidence[] = __( 'Editorial calendar plugin installed', 'wpshadow' );
		}

		// Check 3: Draft pipeline
		$draft_count = self::count_draft_posts();
		if ( $draft_count >= 3 ) {
			$calendar_score += 20;
			$evidence[] = sprintf(
				/* translators: %d: number of drafts */
				__( '%d draft posts in pipeline', 'wpshadow' ),
				$draft_count
			);
		}

		// Check 4: Regular publishing pattern
		if ( self::has_regular_pattern() ) {
			$calendar_score += 10;
			$evidence[] = __( 'Regular publishing pattern detected', 'wpshadow' );
		}

		// Score >= 50 indicates calendar usage
		if ( $calendar_score >= 50 ) {
			return null; // Calendar is being used
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No content calendar detected. Strategic content planning increases productivity by 60% and improves content quality.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/content-calendar',
			'details'      => array(
				'calendar_score'   => $calendar_score,
				'evidence_found'   => $evidence,
				'recommendation'   => __( 'Install Editorial Calendar plugin or use WordPress scheduling', 'wpshadow' ),
				'benefits'         => array(
					'60% increase in content productivity',
					'Better content quality through planning',
					'Team coordination and collaboration',
					'Consistent publishing schedule',
					'Strategic content alignment',
				),
			),
		);
	}

	/**
	 * Count scheduled future posts
	 *
	 * @since  1.6034.2321
	 * @return int Number of scheduled posts.
	 */
	private static function count_scheduled_posts() {
		$scheduled = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'future',
				'posts_per_page' => -1,
			)
		);

		return count( $scheduled );
	}

	/**
	 * Check for editorial calendar plugins
	 *
	 * @since  1.6034.2321
	 * @return bool True if editorial plugin found.
	 */
	private static function has_editorial_plugin() {
		$editorial_plugins = array(
			'editorial-calendar/editorial-calendar.php',
			'edit-flow/edit_flow.php',
			'publishpress/publishpress.php',
			'coschedule-by-todaymade/coschedule.php',
		);

		foreach ( $editorial_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Count draft posts
	 *
	 * @since  1.6034.2321
	 * @return int Number of draft posts.
	 */
	private static function count_draft_posts() {
		$drafts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'draft',
				'posts_per_page' => -1,
			)
		);

		return count( $drafts );
	}

	/**
	 * Check for regular publishing pattern
	 *
	 * @since  1.6034.2321
	 * @return bool True if regular pattern detected.
	 */
	private static function has_regular_pattern() {
		$recent_posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 10,
			)
		);

		if ( count( $recent_posts ) < 5 ) {
			return false;
		}

		// Check if posts are published on consistent days
		$day_counts = array();
		foreach ( $recent_posts as $post ) {
			$day = date( 'w', strtotime( $post->post_date ) );
			$day_counts[ $day ] = ( $day_counts[ $day ] ?? 0 ) + 1;
		}

		// If any day has 40%+ of posts, there's a pattern
		$max_count = max( $day_counts );
		return ( $max_count / count( $recent_posts ) ) >= 0.4;
	}
}
