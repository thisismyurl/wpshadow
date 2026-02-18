<?php
/**
 * Post Revision Bloat Diagnostic
 *
 * Checks if post revisions are accumulating excessively.
 * Measures revision counts per post and database impact.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since      1.6033.1324
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Revision Bloat Diagnostic Class
 *
 * Detects excessive post revision accumulation that can
 * bloat the database and slow down queries.
 *
 * @since 1.6033.1324
 */
class Diagnostic_Post_Revision_Bloat extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-revision-bloat';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Revision Bloat';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if post revisions are accumulating excessively';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.1324
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Count total revisions.
		$total_revisions = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_type = 'revision'"
		);

		// Count active posts.
		$active_posts = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status IN ('publish', 'draft', 'pending', 'private')
			AND post_type IN ('post', 'page')"
		);

		// Calculate average revisions per post.
		$avg_revisions = $active_posts > 0 ? ( $total_revisions / $active_posts ) : 0;

		if ( $avg_revisions > 20 ) {
			$issues[] = sprintf(
				/* translators: %d: average number of revisions */
				__( 'Average of %d revisions per post (excessive)', 'wpshadow' ),
				round( $avg_revisions )
			);
		}

		// Find posts with excessive individual revision counts.
		$max_revisions_per_post = $wpdb->get_var(
			"SELECT COUNT(*) as rev_count
			FROM {$wpdb->posts}
			WHERE post_type = 'revision'
			GROUP BY post_parent
			ORDER BY rev_count DESC
			LIMIT 1"
		);

		if ( $max_revisions_per_post > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: number of revisions */
				__( 'Some posts have over %d revisions', 'wpshadow' ),
				$max_revisions_per_post
			);
		}

		// Check if revision limits are disabled.
		if ( ! defined( 'WP_POST_REVISIONS' ) ) {
			$issues[] = __( 'No revision limit set (WP_POST_REVISIONS undefined)', 'wpshadow' );
		} elseif ( WP_POST_REVISIONS === true ) {
			$issues[] = __( 'Unlimited revisions enabled (WP_POST_REVISIONS = true)', 'wpshadow' );
		}

		// Calculate database space used by revisions.
		$revision_size = $wpdb->get_var(
			"SELECT SUM(LENGTH(post_content) + LENGTH(post_title))
			FROM {$wpdb->posts}
			WHERE post_type = 'revision'"
		);

		if ( $revision_size > 10485760 ) { // 10MB.
			$size_mb = round( $revision_size / 1048576, 2 );
			$issues[] = sprintf(
				/* translators: %s: size in megabytes */
				__( 'Revisions consuming %sMB of database space', 'wpshadow' ),
				$size_mb
			);
		}

		// Check for autosave revisions that should be cleaned up.
		$old_autosaves = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_type = 'revision'
				AND post_name LIKE %s
				AND post_modified < DATE_SUB(NOW(), INTERVAL 7 DAY)",
				'%-autosave-%'
			)
		);

		if ( $old_autosaves > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: number of old autosaves */
				__( '%d old autosave revisions should be cleaned', 'wpshadow' ),
				$old_autosaves
			);
		}

		if ( ! empty( $issues ) ) {
			$severity     = $avg_revisions > 50 ? 'high' : 'medium';
			$threat_level = $avg_revisions > 50 ? 60 : 50;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-revision-bloat',
			);
		}

		return null;
	}
}
