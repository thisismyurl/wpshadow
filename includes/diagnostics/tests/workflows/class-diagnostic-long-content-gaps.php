<?php
/**
 * Long Content Gaps Diagnostic
 *
 * Detects long periods without new content publication, indicating
 * inconsistent publishing schedule.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Publishing
 * @since      1.6034.2204
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Long Content Gaps Diagnostic Class
 *
 * Monitors publishing frequency to detect gaps that hurt SEO rankings,
 * audience retention, and site authority.
 *
 * **Why This Matters:**
 * - Google favors sites with fresh content
 * - Gaps > 30 days hurt search rankings
 * - Audience loses interest and forgets you
 * - Inconsistency signals abandoned site
 * - Social media algorithms punish inactivity
 *
 * **Publishing Frequency Standards:**
 * - Minimum: 1 post per month (to stay active)
 * - Good: 1 post per week
 * - Great: 2-3 posts per week
 * - Enterprise: Daily posts
 *
 * @since 1.6034.2204
 */
class Diagnostic_Long_Content_Gaps extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'long-content-gaps';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Long Content Gaps';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Long periods without new content hurt SEO and audience retention';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'publishing';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6034.2204
	 * @return array|null Finding array if long gaps detected, null otherwise.
	 */
	public static function check() {
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( count( $posts ) < 5 ) {
			return null; // Need sufficient history
		}

		$gaps = array();
		for ( $i = 0; $i < count( $posts ) - 1; $i++ ) {
			$post1_date = strtotime( $posts[ $i ]->post_date );
			$post2_date = strtotime( $posts[ $i + 1 ]->post_date );
			$gap_days = ( $post1_date - $post2_date ) / DAY_IN_SECONDS;

			if ( $gap_days > 30 ) {
				$gaps[] = array(
					'post1_id'    => $posts[ $i ]->ID,
					'post1_title' => $posts[ $i ]->post_title,
					'post1_date'  => get_the_date( '', $posts[ $i ] ),
					'post2_id'    => $posts[ $i + 1 ]->ID,
					'post2_title' => $posts[ $i + 1 ]->post_title,
					'post2_date'  => get_the_date( '', $posts[ $i + 1 ] ),
					'gap_days'    => round( $gap_days ),
				);
			}
		}

		if ( empty( $gaps ) ) {
			return null;
		}

		$max_gap = max( array_column( $gaps, 'gap_days' ) );
		$avg_gap = array_sum( array_column( $gaps, 'gap_days' ) ) / count( $gaps );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: number of gaps, 2: max gap days */
				__( '%1$d publishing gap(s) > 30 days detected (longest: %2$d days). Maintain consistent publishing for better SEO.', 'wpshadow' ),
				count( $gaps ),
				$max_gap
			),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/publishing-consistency',
			'details'      => array(
				'gaps_count'      => count( $gaps ),
				'max_gap_days'    => $max_gap,
				'average_gap_days' => round( $avg_gap ),
				'gaps'            => array_slice( $gaps, 0, 10 ),
			),
		);
	}
}
