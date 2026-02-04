<?php
/**
 * Inconsistent Publishing Schedule Diagnostic
 *
 * Detects irregular publishing patterns that confuse audiences and
 * hurt SEO performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Publishing
 * @since      1.6034.2205
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inconsistent Publishing Schedule Diagnostic Class
 *
 * Analyzes publishing patterns to detect inconsistency that affects
 * audience expectations and search engine crawling frequency.
 *
 * **Why This Matters:**
 * - Audiences expect consistent content
 * - Search engines crawl on predictable schedules
 * - Inconsistency = 23% lower organic traffic
 * - Subscribers unsubscribe from irregular blogs
 * - Predictability builds trust
 *
 * **Publishing Schedule Options:**
 * - Daily (high-volume sites)
 * - 3x/week (active blogs)
 * - Weekly (most common)
 * - Bi-weekly (minimum for growth)
 * - Monthly (retention only)
 *
 * @since 1.6034.2205
 */
class Diagnostic_Inconsistent_Publishing_Schedule extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'inconsistent-publishing-schedule';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Inconsistent Publishing Schedule';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Irregular publishing patterns hurt SEO and audience retention';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'publishing';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6034.2205
	 * @return array|null Finding array if schedule inconsistent, null otherwise.
	 */
	public static function check() {
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( count( $posts ) < 10 ) {
			return null; // Need sufficient history
		}

		// Calculate intervals between posts
		$intervals = array();
		for ( $i = 0; $i < count( $posts ) - 1; $i++ ) {
			$post1_date = strtotime( $posts[ $i ]->post_date );
			$post2_date = strtotime( $posts[ $i + 1 ]->post_date );
			$interval_days = ( $post1_date - $post2_date ) / DAY_IN_SECONDS;
			$intervals[] = $interval_days;
		}

		// Calculate coefficient of variation
		$avg_interval = array_sum( $intervals ) / count( $intervals );
		$std_dev = self::calculate_std_dev( $intervals );
		$coefficient_variation = ( $std_dev / $avg_interval ) * 100;

		// CV > 70% indicates high inconsistency
		if ( $coefficient_variation < 70 ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: average interval */
				__( 'Publishing schedule varies widely (avg interval: %s days). Establish consistent publishing cadence.', 'wpshadow' ),
				number_format_i18n( $avg_interval, 1 )
			),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/publishing-schedule',
			'details'      => array(
				'average_interval_days'   => round( $avg_interval, 1 ),
				'std_deviation'           => round( $std_dev, 1 ),
				'coefficient_variation'   => round( $coefficient_variation, 1 ),
				'min_interval'            => round( min( $intervals ), 1 ),
				'max_interval'            => round( max( $intervals ), 1 ),
			),
		);
	}

	/**
	 * Calculate standard deviation
	 *
	 * @since  1.6034.2205
	 * @param  array $values Array of numeric values.
	 * @return float Standard deviation.
	 */
	private static function calculate_std_dev( $values ) {
		$avg = array_sum( $values ) / count( $values );
		$sum_squares = 0;

		foreach ( $values as $value ) {
			$sum_squares += pow( $value - $avg, 2 );
		}

		return sqrt( $sum_squares / count( $values ) );
	}
}
