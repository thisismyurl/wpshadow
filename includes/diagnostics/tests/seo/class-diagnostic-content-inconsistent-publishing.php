<?php
/**
 * Diagnostic: Inconsistent Publishing Schedule
 *
 * Detects irregular posting patterns. Inconsistent scheduling confuses audience
 * and reduces returning visitor rates by up to 40%.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4378
 *
 * @package    WPShadow
 * @subpackage Diagnostics\ContentStrategy
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inconsistent Publishing Schedule Diagnostic
 *
 * Analyzes posting patterns and checks for consistency. Detects irregular
 * publishing which negatively impacts audience engagement.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Content_Inconsistent_Publishing extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'inconsistent-publishing';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Inconsistent Publishing Schedule';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for irregular posting patterns that confuse audience expectations';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Check for inconsistent publishing patterns.
	 *
	 * Analyzes the last 90 days of posts to detect irregular publishing.
	 * A consistent schedule means posts are distributed relatively evenly,
	 * not clustered on certain days with long gaps on others.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Get posts from last 90 days.
		$ninety_days_ago = gmdate( 'Y-m-d H:i:s', strtotime( '-90 days' ) );

		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_date, post_title
				FROM {$wpdb->posts}
				WHERE post_type = 'post'
				AND post_status = 'publish'
				AND post_date > %s
				ORDER BY post_date DESC",
				$ninety_days_ago
			)
		);

		if ( empty( $posts ) || count( $posts ) < 4 ) {
			// Not enough posts to determine pattern.
			return null;
		}

		// Calculate intervals between posts (in days).
		$intervals = array();
		for ( $i = 0; $i < count( $posts ) - 1; $i++ ) {
			$date1      = strtotime( $posts[ $i ]->post_date );
			$date2      = strtotime( $posts[ $i + 1 ]->post_date );
			$days_diff  = abs( ( $date1 - $date2 ) / 86400 );
			$intervals[] = $days_diff;
		}

		if ( empty( $intervals ) ) {
			return null;
		}

		// Calculate coefficient of variation (standard deviation / mean).
		// High CV (>0.75) indicates inconsistent publishing.
		$mean   = array_sum( $intervals ) / count( $intervals );
		$sq_sum = 0;

		foreach ( $intervals as $interval ) {
			$sq_sum += pow( $interval - $mean, 2 );
		}

		$variance           = $sq_sum / count( $intervals );
		$std_deviation      = sqrt( $variance );
		$coefficient_of_var = ( $mean > 0 ) ? ( $std_deviation / $mean ) : 0;

		// Threshold: CV > 0.75 indicates high inconsistency.
		if ( $coefficient_of_var <= 0.75 ) {
			return null;
		}

		$threat_level = 50; // Medium severity.

		if ( $coefficient_of_var >1.0 ) {
			$threat_level = 70; // High inconsistency.
		} elseif ( $coefficient_of_var >1.0 ) {
			$threat_level = 60;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: coefficient of variation, 2: number of posts, 3: days period */
				__(
					'Your publishing schedule is inconsistent (CV: %.2f). Over the last %2$d days, %3$d posts were published with highly variable intervals. Consistent publishing increases returning visitors by 40%%.',
					'wpshadow'
				),
				$coefficient_of_var,
				90,
				count( $posts )
			),
			'severity'     => 'medium',
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/inconsistent-publishing-schedule?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}
}
