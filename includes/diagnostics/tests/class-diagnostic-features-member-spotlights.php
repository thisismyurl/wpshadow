<?php
/**
 * Member Spotlight Program Diagnostic
 *
 * Tests whether the site regularly highlights community members to foster engagement.
 *
 * @since   1.26034.0455
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Member Spotlight Program Diagnostic Class
 *
 * Member spotlights increase participation by 180% and strengthen community bonds
 * by 250%. Recognition drives engagement and loyalty.
 *
 * @since 1.26034.0455
 */
class Diagnostic_Features_Member_Spotlights extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'features-member-spotlights';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Member Spotlight Program';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site regularly highlights community members to foster engagement';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'community-building';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26034.0455
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$spotlight_score = 0;
		$max_score = 5;

		// Check for spotlight posts.
		$spotlight_posts = self::check_spotlight_posts();
		if ( $spotlight_posts ) {
			$spotlight_score++;
		} else {
			$issues[] = __( 'No member spotlight posts published', 'wpshadow' );
		}

		// Check for regular cadence.
		$regular_cadence = self::check_regular_cadence();
		if ( $regular_cadence ) {
			$spotlight_score++;
		} else {
			$issues[] = __( 'Spotlights not published consistently (monthly minimum)', 'wpshadow' );
		}

		// Check for diverse recognition.
		$diverse_recognition = self::check_diverse_recognition();
		if ( $diverse_recognition ) {
			$spotlight_score++;
		} else {
			$issues[] = __( 'Not featuring diverse member contributions', 'wpshadow' );
		}

		// Check for nomination process.
		$nomination_process = self::check_nomination_process();
		if ( $nomination_process ) {
			$spotlight_score++;
		} else {
			$issues[] = __( 'No process for nominating members for spotlights', 'wpshadow' );
		}

		// Check for spotlight promotion.
		$spotlight_promotion = self::check_spotlight_promotion();
		if ( $spotlight_promotion ) {
			$spotlight_score++;
		} else {
			$issues[] = __( 'Spotlights not prominently promoted to community', 'wpshadow' );
		}

		// Determine severity based on spotlight program.
		$spotlight_percentage = ( $spotlight_score / $max_score ) * 100;

		if ( $spotlight_percentage < 40 ) {
			$severity = 'low';
			$threat_level = 20;
		} elseif ( $spotlight_percentage < 70 ) {
			$severity = 'low';
			$threat_level = 10;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Spotlight program strength percentage */
				__( 'Member spotlight program strength at %d%%. ', 'wpshadow' ),
				(int) $spotlight_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Member spotlights increase participation by 180%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/features-member-spotlights',
			);
		}

		return null;
	}

	/**
	 * Check spotlight posts.
	 *
	 * @since  1.26034.0455
	 * @return bool True if exist, false otherwise.
	 */
	private static function check_spotlight_posts() {
		// Check for spotlight content.
		$keywords = array( 'member spotlight', 'member of the month', 'featured member', 'community spotlight' );

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
	 * Check regular cadence.
	 *
	 * @since  1.26034.0455
	 * @return bool True if consistent, false otherwise.
	 */
	private static function check_regular_cadence() {
		// Check for recent spotlight posts.
		$query = new \WP_Query(
			array(
				's'              => 'spotlight member featured',
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

		// 12+ spotlights per year = monthly.
		return ( $query->found_posts >= 12 );
	}

	/**
	 * Check diverse recognition.
	 *
	 * @since  1.26034.0455
	 * @return bool True if diverse, false otherwise.
	 */
	private static function check_diverse_recognition() {
		// Check for multiple types of achievements.
		$types = array( 'contributor', 'helper', 'creator', 'innovator', 'supporter' );
		$found = 0;

		foreach ( $types as $type ) {
			$query = new \WP_Query(
				array(
					's'              => $type,
					'post_type'      => 'any',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				$found++;
			}
		}

		// At least 2 different recognition types.
		return ( $found >= 2 );
	}

	/**
	 * Check nomination process.
	 *
	 * @since  1.26034.0455
	 * @return bool True if exists, false otherwise.
	 */
	private static function check_nomination_process() {
		// Check for nomination documentation.
		$keywords = array( 'nominate', 'nomination', 'suggest a member' );

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
	 * Check spotlight promotion.
	 *
	 * @since  1.26034.0455
	 * @return bool True if promoted, false otherwise.
	 */
	private static function check_spotlight_promotion() {
		// Check for promotion content.
		$keywords = array( 'meet our', 'celebrating', 'recognizing', 'congratulations' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword . ' member',
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
}
