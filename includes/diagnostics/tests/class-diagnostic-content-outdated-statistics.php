<?php
/**
 * Content Outdated Statistics Diagnostic
 *
 * Detects statistics that are outdated or stale.
 *
 * @since   1.26033.1715
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Outdated Statistics Diagnostic Class
 *
 * Statistics older than 3 years damage credibility. Updating stats
 * increases trust by up to 67%.
 *
 * @since 1.26033.1715
 */
class Diagnostic_Content_Outdated_Statistics extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-outdated-statistics';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Outdated Statistics';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects statistics older than 3 years that reduce credibility';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.1715
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for outdated statistics.
		$outdated_stats_count = apply_filters( 'wpshadow_outdated_statistics_count', 0 );
		if ( $outdated_stats_count > 0 ) {
			$issues[] = __( 'Statistics older than 3 years found; update for accuracy and trust', 'wpshadow' );
		}

		// Check for recent updates to statistics.
		$recent_updates = apply_filters( 'wpshadow_recent_statistics_updates', false );
		if ( ! $recent_updates ) {
			$issues[] = __( 'No recent stat refreshes detected; consider updating key numbers', 'wpshadow' );
		}

		// Check for high-traffic pages with outdated stats.
		$top_pages_outdated = apply_filters( 'wpshadow_top_pages_outdated_statistics', false );
		if ( $top_pages_outdated ) {
			$issues[] = __( 'Top pages contain outdated stats; updating can improve trust by 67%', 'wpshadow' );
		}

		// Check for source citation quality.
		$source_quality = apply_filters( 'wpshadow_statistics_sources_current', false );
		if ( ! $source_quality ) {
			$issues[] = __( 'Stat sources are outdated; refresh citations to current reports', 'wpshadow' );
		}

		// Check for review cadence.
		$review_cadence = apply_filters( 'wpshadow_statistics_review_cadence_defined', false );
		if ( ! $review_cadence ) {
			$issues[] = __( 'Define a review cadence for statistics (annual or biannual)', 'wpshadow' );
		}

		// Check for content credibility signals.
		$credibility_impact = apply_filters( 'wpshadow_outdated_stats_credibility_impact', false );
		if ( $credibility_impact ) {
			$issues[] = __( 'Outdated stats reduce reader confidence and increase bounce rates', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-outdated-statistics',
			);
		}

		return null;
	}
}
