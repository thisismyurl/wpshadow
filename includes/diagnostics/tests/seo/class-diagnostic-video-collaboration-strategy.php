<?php
/**
 * Video Collaboration Strategy Diagnostic
 *
 * Tests whether the site collaborates with other creators quarterly to expand reach.
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
 * Video Collaboration Strategy Diagnostic Class
 *
 * Creator collaborations increase reach by 250% and subscribers by 180%.
 * Quarterly partnerships are essential for growth and cross-pollination.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Video_Collaboration_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'video-collaboration-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Video Collaboration Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site collaborates with other creators quarterly to expand reach';

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
		$collab_score = 0;
		$max_score = 5;

		// Check for collaboration content.
		$collab_content = self::check_collaboration_content();
		if ( $collab_content ) {
			$collab_score++;
		} else {
			$issues[] = __( 'No creator collaboration videos published', 'wpshadow' );
		}

		// Check for guest appearances.
		$guest_appearances = self::check_guest_appearances();
		if ( $guest_appearances ) {
			$collab_score++;
		} else {
			$issues[] = __( 'Not featuring guest creators or experts', 'wpshadow' );
		}

		// Check for cross-promotion.
		$cross_promotion = self::check_cross_promotion();
		if ( $cross_promotion ) {
			$collab_score++;
		} else {
			$issues[] = __( 'No cross-promotion with other channels', 'wpshadow' );
		}

		// Check for consistent partnerships.
		$consistent_partnerships = self::check_consistent_partnerships();
		if ( $consistent_partnerships ) {
			$collab_score++;
		} else {
			$issues[] = __( 'Collaborations not happening quarterly (minimum 4/year)', 'wpshadow' );
		}

		// Check for strategic targeting.
		$strategic_targeting = self::check_strategic_targeting();
		if ( $strategic_targeting ) {
			$collab_score++;
		} else {
			$issues[] = __( 'Not targeting creators with complementary audiences', 'wpshadow' );
		}

		// Determine severity based on collaboration activity.
		$collab_percentage = ( $collab_score / $max_score ) * 100;

		if ( $collab_percentage < 40 ) {
			$severity = 'low';
			$threat_level = 25;
		} elseif ( $collab_percentage < 70 ) {
			$severity = 'low';
			$threat_level = 15;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Collaboration activity percentage */
				__( 'Video collaboration activity at %d%%. ', 'wpshadow' ),
				(int) $collab_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Creator collaborations increase reach by 250%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/video-collaboration-strategy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}

	/**
	 * Check collaboration content.
	 *
	 * @since 0.6093.1200
	 * @return bool True if content exists, false otherwise.
	 */
	private static function check_collaboration_content() {
		// Check for collaboration references.
		$keywords = array( 'collaboration', 'collab', 'featuring', 'with @', 'guest' );

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
	 * Check guest appearances.
	 *
	 * @since 0.6093.1200
	 * @return bool True if guests featured, false otherwise.
	 */
	private static function check_guest_appearances() {
		// Check for guest references.
		$keywords = array( 'guest', 'interview', 'expert', 'special guest' );

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
	 * Check cross-promotion.
	 *
	 * @since 0.6093.1200
	 * @return bool True if promoted, false otherwise.
	 */
	private static function check_cross_promotion() {
		// Check for channel shoutouts.
		$keywords = array( 'check out', 'shoutout', 'recommend', 'channel' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword . ' @',
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
	 * Check consistent partnerships.
	 *
	 * @since 0.6093.1200
	 * @return bool True if consistent, false otherwise.
	 */
	private static function check_consistent_partnerships() {
		// Check for recent collaboration posts.
		$query = new \WP_Query(
			array(
				's'              => 'collaboration collab guest featuring',
				'post_type'      => 'any',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
				'date_query'     => array(
					array(
						'after' => '1 year ago',
					),
				),
			)
		);

		// 4+ collabs per year = quarterly.
		return ( $query->found_posts >= 4 );
	}

	/**
	 * Check strategic targeting.
	 *
	 * @since 0.6093.1200
	 * @return bool True if strategic, false otherwise.
	 */
	private static function check_strategic_targeting() {
		// Check for targeting references.
		$query = new \WP_Query(
			array(
				's'              => 'audience similar complementary niche',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}
}
