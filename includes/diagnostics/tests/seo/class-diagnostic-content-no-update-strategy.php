<?php
/**
 * Diagnostic: No Content Update Strategy
 *
 * Detects lack of content refresh strategy. Updating existing posts is 4-8x
 * more efficient than creating new content.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4385
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
 * No Content Update Strategy Diagnostic
 *
 * Checks if existing content is being refreshed. Regular updates are more
 * efficient than new content for maintaining rankings.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Content_No_Update_Strategy extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-update-strategy';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Content Update Strategy';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects lack of content refresh strategy that limits SEO efficiency';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Check for content update activity.
	 *
	 * Analyzes if posts are being updated. Checks if <5% of older posts have
	 * been updated in the last year, indicating no refresh strategy.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Get posts older than 1 year.
		$one_year_ago = gmdate( 'Y-m-d H:i:s', strtotime( '-365 days' ) );

		$old_posts_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_type = 'post'
				AND post_status = 'publish'
				AND post_date < %s",
				$one_year_ago
			)
		);

		if ( $old_posts_count < 20 ) {
			// Not enough old posts to evaluate update strategy.
			return null;
		}

		// Count how many of those old posts were modified in last year.
		$updated_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_type = 'post'
				AND post_status = 'publish'
				AND post_date < %s
				AND post_modified > %s
				AND post_modified != post_date",
				$one_year_ago,
				$one_year_ago
			)
		);

		// Calculate percentage of old posts that were updated.
		$update_percentage = ( $old_posts_count > 0 ) ? ( $updated_count / $old_posts_count ) * 100 : 0;

		// Threshold: <5% updated = no update strategy.
		if ( $update_percentage >= 5 ) {
			return null;
		}

		$threat_level = 75; // High severity - this is a major efficiency opportunity.

		if ( $update_percentage < 2 ) {
			$threat_level = 80; // Very low update rate.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: update percentage, 2: updated count, 3: old posts count */
				__(
					'Only %.1f%% of older posts (%2$d of %3$d) were updated in the last year. Updating existing content is 4-8x more efficient than creating new posts. Implement a content refresh strategy to maximize SEO ROI.',
					'wpshadow'
				),
				$update_percentage,
				$updated_count,
				$old_posts_count
			),
			'severity'     => 'critical',
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/content-update-strategy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}
}
