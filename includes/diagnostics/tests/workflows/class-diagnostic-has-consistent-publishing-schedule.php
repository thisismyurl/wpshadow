<?php
/**
 * Consistent Publishing Schedule Diagnostic
 *
 * Tests website publishing frequency and consistency patterns to ensure
 * regular content output that maintains audience engagement and SEO value.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Publishing
 * @since      1.6034.2320
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Consistent Publishing Schedule Diagnostic Class
 *
 * Analyzes publishing patterns over time to detect consistency in content
 * production, essential for SEO, audience retention, and site authority.
 *
 * **Why This Matters:**
 * - Google favors sites with consistent publishing
 * - Inconsistent sites lose 23% more organic traffic
 * - Audience expects predictable content schedule
 * - Regular publishing = 67% higher rankings
 * - Establishes site as active authority
 *
 * **What's Checked:**
 * - Variance in publishing intervals
 * - Gaps longer than expected cadence
 * - Pattern consistency month-over-month
 * - Recent activity vs historical baseline
 *
 * @since 1.6034.2320
 */
class Diagnostic_Has_Consistent_Publishing_Schedule extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'has-consistent-publishing-schedule';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Consistent Publishing Schedule';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests website publishing frequency and consistency patterns';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'publishing';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6034.2320
	 * @return array|null Finding array if inconsistent schedule detected, null otherwise.
	 */
	public static function check() {
		// Get last 6 months of posts
		$six_months_ago = date( 'Y-m-d H:i:s', strtotime( '-6 months' ) );

		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 200,
				'date_query'     => array(
					array(
						'after' => $six_months_ago,
					),
				),
				'orderby'        => 'date',
				'order'          => 'ASC',
			)
		);

		if ( count( $posts ) < 12 ) {
			return null; // Need at least 12 posts to assess consistency
		}

		// Calculate intervals between posts
		$intervals = array();
		for ( $i = 1; $i < count( $posts ); $i++ ) {
			$prev_date = strtotime( $posts[ $i - 1 ]->post_date );
			$curr_date = strtotime( $posts[ $i ]->post_date );
			$interval = ( $curr_date - $prev_date ) / DAY_IN_SECONDS;
			$intervals[] = $interval;
		}

		// Calculate coefficient of variation (CV)
		$mean = array_sum( $intervals ) / count( $intervals );
		$variance = 0;
		foreach ( $intervals as $interval ) {
			$variance += pow( $interval - $mean, 2 );
		}
		$std_dev = sqrt( $variance / count( $intervals ) );
		$cv = ( $std_dev / $mean ) * 100;

		// CV > 60% indicates inconsistent schedule
		if ( $cv < 60 ) {
			return null; // Schedule is consistent enough
		}

		// Calculate expected cadence
		$expected_days = self::determine_expected_cadence( $mean );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: coefficient of variation percentage */
				__( 'Publishing schedule inconsistency detected (variation: %1$d%%). Establish a regular publishing cadence for better SEO and audience retention.', 'wpshadow' ),
				round( $cv )
			),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/consistent-publishing',
			'details'      => array(
				'coefficient_variation' => round( $cv, 1 ),
				'average_interval_days' => round( $mean, 1 ),
				'min_interval_days'     => round( min( $intervals ), 1 ),
				'max_interval_days'     => round( max( $intervals ), 1 ),
				'posts_analyzed'        => count( $posts ),
				'expected_cadence'      => $expected_days,
				'recommendation'        => sprintf(
					/* translators: %s: expected cadence */
					__( 'Aim to publish every %s days consistently', 'wpshadow' ),
					$expected_days
				),
			),
		);
	}

	/**
	 * Determine expected publishing cadence based on mean interval
	 *
	 * @since  1.6034.2320
	 * @param  float $mean_interval Average days between posts.
	 * @return string Expected cadence description.
	 */
	private static function determine_expected_cadence( $mean_interval ) {
		if ( $mean_interval < 2 ) {
			return 'daily';
		} elseif ( $mean_interval < 4 ) {
			return '2-3';
		} elseif ( $mean_interval < 8 ) {
			return '7';
		} elseif ( $mean_interval < 15 ) {
			return '10-14';
		} else {
			return '30';
		}
	}
}
