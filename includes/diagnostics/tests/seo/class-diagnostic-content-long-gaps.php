<?php
/**
 * Diagnostic: Long Content Gaps
 *
 * Detects gaps of 30+ days between posts. Long gaps cause 15-40% traffic loss
 * and significantly damage SEO rankings.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4381
 *
 * @package    WPShadow
 * @subpackage Diagnostics\ContentStrategy
 * @since      1.6034.1440
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Long Content Gaps Diagnostic
 *
 * Analyzes posting history to identify extended gaps (30+ days) between posts.
 * Such gaps have severe negative impact on traffic and engagement.
 *
 * @since 1.6034.1440
 */
class Diagnostic_Content_Long_Gaps extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'long-content-gaps';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Long Content Gaps';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects extended periods without new content that damage traffic and rankings';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Check for long gaps between posts.
	 *
	 * Analyzes the last 6 months of posts to find gaps of 30+ days.
	 * Research shows such gaps cause 15-40% traffic loss.
	 *
	 * @since  1.6034.1440
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Get posts from last 6 months.
		$six_months_ago = gmdate( 'Y-m-d H:i:s', strtotime( '-180 days' ) );
		
		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_date, post_title
				FROM {$wpdb->posts}
				WHERE post_type = 'post'
				AND post_status = 'publish'
				AND post_date > %s
				ORDER BY post_date DESC",
				$six_months_ago
			)
		);

		if ( count( $posts ) < 2 ) {
			// Not enough posts to check gaps.
			return null;
		}

		// Find longest gap.
		$longest_gap     = 0;
		$longest_gap_end = null;
		$gaps_over_30    = 0;
		
		for ( $i = 0; $i < count( $posts ) - 1; $i++ ) {
			$date1     = strtotime( $posts[ $i ]->post_date );
			$date2     = strtotime( $posts[ $i + 1 ]->post_date );
			$days_diff = abs( ( $date1 - $date2 ) / 86400 );
			
			if ( $days_diff > $longest_gap ) {
				$longest_gap     = $days_diff;
				$longest_gap_end = $posts[ $i ]->post_date;
			}
			
			if ( $days_diff >= 30 ) {
				$gaps_over_30++;
			}
		}

		// Threshold: 30+ day gap.
		if ( $longest_gap < 30 ) {
			return null;
		}

		// Determine threat level based on gap length.
		$threat_level = 70; // Default: high.
		
		if ( $longest_gap >= 90 ) {
			$threat_level = 85; // 3+ months is critical.
		} elseif ( $longest_gap >= 60 ) {
			$threat_level = 80; // 2+ months is very high.
		} elseif ( $longest_gap >= 45 ) {
			$threat_level = 75; // 45+ days is high.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: longest gap in days, 2: number of gaps over 30 days, 3: date of longest gap */
				__(
					'Detected content gap of %1$d days (ending %3$s). Found %2$d gaps of 30+ days in the last 6 months. Content gaps of this length cause 15-40%% traffic loss and significantly damage SEO rankings.',
					'wpshadow'
				),
				(int) $longest_gap,
				$gaps_over_30,
				gmdate( 'Y-m-d', strtotime( $longest_gap_end ) )
			),
			'severity'     => 'critical',
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/long-content-gaps',
		);
	}
}
