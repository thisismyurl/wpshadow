<?php
/**
 * No Peak Traffic Publishing Diagnostic
 *
 * Detects posts published at suboptimal times, missing maximum
 * audience reach opportunities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Publishing
 * @since      1.6034.2210
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Peak Traffic Publishing Diagnostic Class
 *
 * Analyzes publishing times to detect patterns that miss peak
 * audience engagement windows.
 *
 * **Why This Matters:**
 * - Publishing time affects initial engagement
 * - Peak times get 38% more initial traffic
 * - Better initial signals boost SEO ranking
 * - Social media algorithms favor peak times
 * - More shares, comments, interactions
 *
 * **Best Publishing Times (General):**
 * - Tuesday 10am (highest engagement)
 * - Wednesday 10am-11am
 * - Thursday 10am-11am
 * - Avoid: weekends, early mornings, late nights
 * - Test and optimize for your audience
 *
 * @since 1.6034.2210
 */
class Diagnostic_No_Peak_Traffic_Publishing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-peak-traffic-publishing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Peak Traffic Publishing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Posts aren\'t being published during peak audience times';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'publishing';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6034.2210
	 * @return array|null Finding array if poor timing detected, null otherwise.
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
			return null;
		}

		$peak_hour_count = 0;
		$off_peak_posts = array();

		foreach ( $posts as $post ) {
			$post_time = strtotime( $post->post_date );
			$hour = (int) date( 'G', $post_time );
			$day = date( 'w', $post_time ); // 0 = Sunday, 6 = Saturday

			// Peak hours: 9am-2pm (9-14), weekdays only
			$is_weekday = ( $day >= 1 && $day <= 5 );
			$is_peak_hour = ( $hour >= 9 && $hour <= 14 );

			if ( $is_weekday && $is_peak_hour ) {
				$peak_hour_count++;
			} else {
				$off_peak_posts[] = array(
					'id'      => $post->ID,
					'title'   => $post->post_title,
					'date'    => get_the_date( '', $post ),
					'time'    => get_the_time( '', $post ),
					'day'     => date( 'l', $post_time ),
					'hour'    => $hour,
				);
			}
		}

		$peak_percentage = ( $peak_hour_count / count( $posts ) ) * 100;

		// Issue if < 40% published during peak hours
		if ( $peak_percentage >= 40 ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: percentage published during peak times */
				__( 'Only %s%% of posts are published during peak engagement times (weekdays 9am-2pm). Schedule posts for better initial reach.', 'wpshadow' ),
				number_format_i18n( $peak_percentage, 1 )
			),
			'severity'     => 'low',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/publishing-timing',
			'details'      => array(
				'peak_hour_count'   => $peak_hour_count,
				'peak_percentage'   => round( $peak_percentage, 1 ),
				'off_peak_count'    => count( $off_peak_posts ),
				'sample_off_peak'   => array_slice( $off_peak_posts, 0, 10 ),
				'recommendation'    => 'Publish Tuesday-Thursday, 9am-2pm for best results',
			),
		);
	}
}
