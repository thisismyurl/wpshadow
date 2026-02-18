<?php
/**
 * Content No Update Strategy Diagnostic
 *
 * Identifies sites without content update strategy.
 *
 * @since   1.6033.1645
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content No Update Strategy Diagnostic Class
 *
 * Identifies sites that only publish new content without updating existing posts,
 * missing significant SEO and value opportunities.
 *
 * @since 1.6033.1645
 */
class Diagnostic_Content_No_Updates_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-no-updates-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Content Update Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identify sites without content updates missing SEO and value opportunities';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if posts are being updated
		$posts_updated = apply_filters( 'wpshadow_posts_being_updated', false );
		if ( ! $posts_updated ) {
			$issues[] = __( 'No posts have been updated in 12 months; updates are 4-8x more efficient than new content', 'wpshadow' );
		}

		// Check percentage of posts updated
		$update_percentage = apply_filters( 'wpshadow_percentage_posts_updated_12m', 0 );
		if ( $update_percentage < 5 ) {
			$issues[] = sprintf(
				/* translators: %d: percentage */
				__( 'Only %d%% of posts updated in last year; aim for minimum 10-15%% annual update rate', 'wpshadow' ),
				$update_percentage
			);
		}

		// Check for content decay
		$content_decay = apply_filters( 'wpshadow_has_detected_content_decay', false );
		if ( $content_decay ) {
			$issues[] = __( 'Content decay detected; outdated statistics, screenshots, examples hurt rankings', 'wpshadow' );
		}

		// Check for top performer updates
		$top_performers_updated = apply_filters( 'wpshadow_top_traffic_posts_recently_updated', false );
		if ( ! $top_performers_updated ) {
			$issues[] = __( 'Top 10 traffic posts should be updated first; can increase traffic 50-100%', 'wpshadow' );
		}

		// Check for update visibility
		$update_dated = apply_filters( 'wpshadow_posts_have_update_dates', false );
		if ( ! $update_dated ) {
			$issues[] = __( 'Add \"Last Updated: [date]\" to posts; Google rewards content freshness signals', 'wpshadow' );
		}

		// Check update frequency analysis
		$systematic_updates = apply_filters( 'wpshadow_has_systematic_update_schedule', false );
		if ( ! $systematic_updates ) {
			$issues[] = __( 'Implement quarterly content review cycle; update top performers systematically', 'wpshadow' );
		}

		// Check for old post status
		$old_posts_count = apply_filters( 'wpshadow_old_posts_without_recent_updates', 0 );
		if ( $old_posts_count > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: count of old posts */
				__( '%d posts haven\'t been updated in 18+ months; refresh oldest posts first', 'wpshadow' ),
				$old_posts_count
			);
		}

		// Check for ROI understanding
		$roi_improvement = apply_filters( 'wpshadow_content_updates_improve_roi', false );
		if ( ! $roi_improvement ) {
			$issues[] = __( 'Content updates often outperform new posts; Backlinko increased traffic 111% by updating 10 posts', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-no-updates-strategy',
			);
		}

		return null;
	}
}
