<?php
/**
 * Content No Content Pruning Strategy Diagnostic
 *
 * Detects lack of a strategy to prune underperforming content.
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
 * Content No Content Pruning Strategy Diagnostic Class
 *
 * 200+ posts with <10 visits/month dilute authority. Pruning can boost
 * rankings by reducing index bloat and improving topical focus.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Content_No_Content_Pruning extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-no-content-pruning';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Content Pruning Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects lack of a plan to prune or consolidate underperforming posts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for pruning strategy documentation.
		$has_strategy = apply_filters( 'wpshadow_has_content_pruning_strategy', false );
		if ( ! $has_strategy ) {
			$issues[] = __( 'No documented content pruning strategy found', 'wpshadow' );
		}

		// Check for high volume of low-traffic posts.
		$low_traffic_posts = apply_filters( 'wpshadow_low_traffic_post_count', 0 );
		if ( $low_traffic_posts >= 200 ) {
			$issues[] = __( '200+ posts with fewer than 10 visits/month dilute authority', 'wpshadow' );
		}

		// Check for content audit recency.
		$last_audit_days = apply_filters( 'wpshadow_days_since_content_audit', 0 );
		if ( $last_audit_days > 365 && $last_audit_days > 0 ) {
			$issues[] = __( 'Content audit is more than 12 months old; schedule a pruning review', 'wpshadow' );
		}

		// Check for consolidation opportunities.
		$has_duplicates = apply_filters( 'wpshadow_has_overlapping_topics_for_pruning', false );
		if ( $has_duplicates ) {
			$issues[] = __( 'Overlapping posts should be merged to strengthen topic authority', 'wpshadow' );
		}

		// Check for index bloat signals.
		$index_bloat = apply_filters( 'wpshadow_has_index_bloat_signals', false );
		if ( $index_bloat ) {
			$issues[] = __( 'Index bloat detected; pruning can boost rankings by ~25%', 'wpshadow' );
		}

		// Check for redirect plan when pruning.
		$has_redirect_plan = apply_filters( 'wpshadow_has_pruning_redirect_plan', false );
		if ( ! $has_redirect_plan ) {
			$issues[] = __( 'Define a redirect plan when pruning to preserve link equity', 'wpshadow' );
		}

		// Check for KPI tracking.
		$tracks_pruning_kpis = apply_filters( 'wpshadow_tracks_pruning_kpis', false );
		if ( ! $tracks_pruning_kpis ) {
			$issues[] = __( 'Track pruning KPIs (traffic uplift, impressions, crawl budget) after cleanup', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-no-content-pruning?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
