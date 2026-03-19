<?php
/**
 * No Content Update Strategy Diagnostic
 *
 * Detects lack of content refresh strategy, missing opportunities
 * to revitalize old content for continued SEO value.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Content Update Strategy Diagnostic Class
 *
 * Analyzes content update patterns to detect stale content that
 * needs refreshing to maintain SEO rankings and relevance.
 *
 * **Why This Matters:**
 * - Google favors fresh, updated content
 * - Old content loses rankings over time
 * - Outdated info hurts credibility
 * - Updating is 5x faster than creating new
 * - Updated content gets ranking boost
 *
 * **Content Update Best Practices:**
 * - Review content every 6-12 months
 * - Update statistics and data
 * - Add new sections
 * - Improve readability
 * - Update images and examples
 * - Check and fix broken links
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Content_Update_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-content-update-strategy';

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
	protected static $description = 'Old content isn\'t being refreshed, losing SEO value over time';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if no update strategy, null otherwise.
	 */
	public static function check() {
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( count( $posts ) < 20 ) {
			return null; // Need sufficient content history
		}

		$old_never_updated = array();
		$one_year_ago = strtotime( '-1 year' );

		foreach ( $posts as $post ) {
			$publish_date = strtotime( $post->post_date );
			$modified_date = strtotime( $post->post_modified );

			// Check if post is > 1 year old
			if ( $publish_date < $one_year_ago ) {
				// Check if it has been updated (modified date significantly different from publish date)
				$days_between = ( $modified_date - $publish_date ) / DAY_IN_SECONDS;

				if ( $days_between < 7 ) {
					// Never meaningfully updated
					$old_never_updated[] = array(
						'id'           => $post->ID,
						'title'        => $post->post_title,
						'published'    => get_the_date( '', $post ),
						'age_days'     => round( ( time() - $publish_date ) / DAY_IN_SECONDS ),
						'last_updated' => get_the_modified_date( '', $post ),
					);
				}
			}
		}

		if ( empty( $old_never_updated ) ) {
			return null; // Content is being updated
		}

		$percentage = ( count( $old_never_updated ) / count( $posts ) ) * 100;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of old posts */
				__( '%d old post(s) have never been updated. Implement content refresh strategy to maintain SEO rankings.', 'wpshadow' ),
				count( $old_never_updated )
			),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/content-update-strategy',
			'details'      => array(
				'old_never_updated_count' => count( $old_never_updated ),
				'percentage_of_content'   => round( $percentage, 1 ),
				'sample_posts'            => array_slice( $old_never_updated, 0, 10 ),
				'recommendation'          => 'Review and update content every 6-12 months',
			),
		);
	}
}
