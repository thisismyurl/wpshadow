<?php
/**
 * Content No Scheduled Future Content Diagnostic
 *
 * Detects absence of scheduled content indicating lack of planning.
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
 * Content No Scheduled Future Content Diagnostic Class
 *
 * Detects absence of scheduled content which indicates lack of planning and
 * increases risk of publishing inconsistency.
 *
 * @since 1.6033.1645
 */
class Diagnostic_Content_No_Scheduled_Posts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-no-scheduled-posts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Scheduled Future Content';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detect absence of scheduled content indicating lack of planning';

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

		// Check if there are any scheduled posts
		$has_scheduled = apply_filters( 'wpshadow_has_scheduled_posts', false );
		if ( ! $has_scheduled ) {
			$issues[] = __( 'No scheduled posts found; scheduling maintains consistency during busy periods', 'wpshadow' );
		}

		// Check content buffer status
		$buffer_days = apply_filters( 'wpshadow_content_buffer_days', 0 );
		if ( $buffer_days < 14 ) {
			$issues[] = sprintf(
				/* translators: %d: days of buffer */
				__( 'Content buffer is only %d days; aim for 2-4 week minimum scheduled buffer', 'wpshadow' ),
				$buffer_days
			);
		}

		// Check for batching practice
		$batches_content = apply_filters( 'wpshadow_practices_content_batching', false );
		if ( ! $batches_content ) {
			$issues[] = __( 'Batch content creation (write 3-4 posts at once) is more efficient than daily writing', 'wpshadow' );
		}

		// Check for planning visibility
		$planned_ahead = apply_filters( 'wpshadow_content_planned_in_advance', false );
		if ( ! $planned_ahead ) {
			$issues[] = __( 'Advance planning improves quality; scheduling posts ensures consistency', 'wpshadow' );
		}

		// Check for schedule consistency
		$publishing_pattern = apply_filters( 'wpshadow_has_consistent_publishing_pattern', false );
		if ( ! $publishing_pattern ) {
			$issues[] = __( 'Scheduling enables consistent publishing schedule without stress', 'wpshadow' );
		}

		// Check for campaign alignment
		$campaigns_aligned = apply_filters( 'wpshadow_scheduling_aligned_with_campaigns', false );
		if ( ! $campaigns_aligned ) {
			$issues[] = __( 'Scheduled posts can be aligned with marketing campaigns for better ROI', 'wpshadow' );
		}

		// Check for team coordination benefit
		$team_benefit = apply_filters( 'wpshadow_team_coordination_with_scheduling', false );
		if ( ! $team_benefit ) {
			$issues[] = __( 'Scheduling improves team coordination and visibility of content calendar', 'wpshadow' );
		}

		// Check publishing gap risk
		$gap_risk = apply_filters( 'wpshadow_at_risk_publishing_gaps', false );
		if ( $gap_risk ) {
			$issues[] = __( 'Without scheduled content, unexpected busy periods create publishing gaps', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-no-scheduled-posts',
			);
		}

		return null;
	}
}
