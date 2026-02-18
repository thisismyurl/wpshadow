<?php
/**
 * Content Freshness Program Diagnostic
 *
 * Verifies site has program for updating old content to maintain
 * relevance and SEO performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since      1.6034.2326
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Freshness Program Diagnostic Class
 *
 * Analyzes content update patterns to detect systematic content
 * refresh strategy for maintaining relevance.
 *
 * **Why This Matters:**
 * - Updated content ranks 74% better
 * - Google favors fresh, current content
 * - Old content loses traffic over time
 * - Updates signal quality and accuracy
 * - Extends content lifecycle ROI
 *
 * **Freshness Strategy:**
 * - Regular content audits
 * - Systematic update schedule
 * - Updated facts and statistics
 * - New examples and screenshots
 * - Revised recommendations
 *
 * @since 1.6034.2326
 */
class Diagnostic_Updates_Old_Content extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'updates-old-content';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Freshness Program';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies site updates old content to maintain relevance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6034.2326
	 * @return array|null Finding array if no freshness program, null otherwise.
	 */
	public static function check() {
		// Get posts older than 1 year
		$one_year_ago = strtotime( '-1 year' );
		$old_posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'date_query'     => array(
					array(
						'before'    => date( 'Y-m-d', $one_year_ago ),
						'inclusive' => true,
					),
				),
			)
		);

		if ( count( $old_posts ) < 10 ) {
			return null; // Not enough old content to assess
		}

		// Count how many have been updated in last 6 months
		$six_months_ago = strtotime( '-6 months' );
		$updated_count = 0;

		foreach ( $old_posts as $post ) {
			$modified_time = strtotime( $post->post_modified );
			$publish_time = strtotime( $post->post_date );

			// Modified significantly after publication?
			if ( $modified_time > $six_months_ago && 
				 ( $modified_time - $publish_time ) > ( 7 * DAY_IN_SECONDS ) ) {
				$updated_count++;
			}
		}

		$update_percentage = ( $updated_count / count( $old_posts ) ) * 100;

		// 20%+ of old content updated = active freshness program
		if ( $update_percentage >= 20 ) {
			return null; // Content freshness program active
		}

		$severity = 'medium';
		$threat_level = 50;

		if ( $update_percentage < 5 ) {
			$severity = 'high';
			$threat_level = 65;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: update percentage, 2: old post count */
				__( 'No content freshness program detected (only %1$d%% of %2$d old posts updated recently). Updated content ranks 74%% better and extends content ROI.', 'wpshadow' ),
				round( $update_percentage ),
				count( $old_posts )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/content-freshness',
			'details'      => array(
				'old_posts_count'   => count( $old_posts ),
				'updated_count'     => $updated_count,
				'update_percentage' => round( $update_percentage, 1 ),
				'recommendation'    => __( 'Implement quarterly content audit and update schedule', 'wpshadow' ),
				'update_checklist'  => array(
					'Review top 20 performing posts quarterly',
					'Update statistics and facts',
					'Add new examples and screenshots',
					'Revise outdated recommendations',
					'Check and update internal links',
					'Refresh meta descriptions',
					'Update publish date after major revision',
				),
			),
		);
	}
}
