<?php
/**
 * Publishing Workflow Gaps
 *
 * Checks if editorial calendar or publishing schedule is being maintained
 * consistently. Detects gaps in content publication patterns.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since      1.6028.1048
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Publishing Workflow Gaps Diagnostic Class
 *
 * Analyzes publishing patterns to identify inconsistent content production.
 *
 * @since 1.6028.1048
 */
class Diagnostic_Publishing_Workflow_Gaps extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'publishing-workflow-gaps';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Publishing Workflow Gaps';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if publishing schedule is maintained consistently';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1048
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_publishing_workflow_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$analysis = self::analyze_publishing_pattern();

		if ( ! $analysis['has_gaps'] ) {
			set_transient( $cache_key, null, 7 * DAY_IN_SECONDS );
			return null;
		}

		$finding = array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Publishing schedule shows significant gaps, indicating inconsistent content workflow.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/publishing-workflow',
			'meta'         => array(
				'longest_gap_days'    => $analysis['longest_gap_days'],
				'average_gap_days'    => $analysis['average_gap_days'],
				'posts_last_30_days'  => $analysis['recent_count'],
				'posts_previous_month' => $analysis['previous_count'],
			),
			'details'      => array(
				sprintf(
					/* translators: %d: number of days */
					__( 'Longest gap between posts: %d days', 'wpshadow' ),
					$analysis['longest_gap_days']
				),
				sprintf(
					/* translators: %d: number of posts */
					__( 'Published %d posts in last 30 days', 'wpshadow' ),
					$analysis['recent_count']
				),
			),
			'recommendation' => __( 'Establish a consistent publishing schedule with editorial calendar.', 'wpshadow' ),
		);

		set_transient( $cache_key, $finding, 7 * DAY_IN_SECONDS );
		return $finding;
	}

	/**
	 * Analyze publishing pattern.
	 *
	 * @since  1.6028.1048
	 * @return array Analysis results.
	 */
	private static function analyze_publishing_pattern() {
		global $wpdb;

		// Get recent post dates.
		$post_dates = $wpdb->get_col(
			"SELECT post_date
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type = 'post'
			ORDER BY post_date DESC
			LIMIT 60"
		);

		if ( count( $post_dates ) < 5 ) {
			return array( 'has_gaps' => false );
		}

		// Calculate gaps between posts.
		$gaps           = array();
		$longest_gap    = 0;
		$recent_count   = 0;
		$previous_count = 0;
		$thirty_days_ago = date( 'Y-m-d H:i:s', strtotime( '-30 days' ) );
		$sixty_days_ago  = date( 'Y-m-d H:i:s', strtotime( '-60 days' ) );

		for ( $i = 0; $i < count( $post_dates ) - 1; $i++ ) {
			$date1 = strtotime( $post_dates[ $i ] );
			$date2 = strtotime( $post_dates[ $i + 1 ] );
			$gap   = abs( $date1 - $date2 ) / DAY_IN_SECONDS;

			$gaps[]      = $gap;
			$longest_gap = max( $longest_gap, $gap );

			// Count posts by period.
			if ( $post_dates[ $i ] >= $thirty_days_ago ) {
				$recent_count++;
			} elseif ( $post_dates[ $i ] >= $sixty_days_ago ) {
				$previous_count++;
			}
		}

		$average_gap = ! empty( $gaps ) ? array_sum( $gaps ) / count( $gaps ) : 0;

		// Consider gaps significant if longest gap > 14 days OR average > 7 days.
		$has_gaps = ( $longest_gap > 14 ) || ( $average_gap > 7 );

		return array(
			'has_gaps'         => $has_gaps,
			'longest_gap_days' => (int) round( $longest_gap ),
			'average_gap_days' => round( $average_gap, 1 ),
			'recent_count'     => $recent_count,
			'previous_count'   => $previous_count,
		);
	}
}
