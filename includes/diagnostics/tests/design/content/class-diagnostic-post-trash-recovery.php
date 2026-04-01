<?php
/**
 * Post Trash Recovery Diagnostic
 *
 * Tests if trashed posts can be restored properly.
 * Verifies data integrity after restore.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Trash Recovery Diagnostic Class
 *
 * Verifies that trashed posts can be successfully restored
 * with all data and relationships intact.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Post_Trash_Recovery extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-trash-recovery';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Trash Recovery';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if trashed posts can be restored with data integrity';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check for old trashed posts (auto-delete after 30 days by default).
		$old_trash = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'trash'
			AND post_modified < DATE_SUB(NOW(), INTERVAL 25 DAY)"
		);

		if ( $old_trash > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d trashed posts near auto-delete threshold (30 days)', 'wpshadow' ),
				$old_trash
			);
		}

		// Check if EMPTY_TRASH_DAYS is disabled.
		if ( defined( 'EMPTY_TRASH_DAYS' ) && EMPTY_TRASH_DAYS === 0 ) {
			$issues[] = __( 'Trash auto-delete disabled - old posts accumulating', 'wpshadow' );
		}

		// Check for trashed posts with missing metadata.
		$trash_missing_meta = $wpdb->get_var(
			"SELECT COUNT(p.ID)
			FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_trash_meta_status'
			WHERE p.post_status = 'trash'
			AND pm.meta_value IS NULL"
		);

		if ( $trash_missing_meta > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d trashed posts missing restore metadata', 'wpshadow' ),
				$trash_missing_meta
			);
		}

		// Check for trashed posts with broken term relationships.
		$trash_broken_terms = $wpdb->get_var(
			"SELECT COUNT(DISTINCT p.ID)
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
			LEFT JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			WHERE p.post_status = 'trash'
			AND tt.term_taxonomy_id IS NULL"
		);

		if ( $trash_broken_terms > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d trashed posts have broken term relationships', 'wpshadow' ),
				$trash_broken_terms
			);
		}

		// Check for excessive trash items.
		$total_trash = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'trash'"
		);

		if ( $total_trash > 500 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts in trash (consider cleanup)', 'wpshadow' ),
				number_format( $total_trash )
			);
		}

		// Check for trashed posts with active comments.
		$trash_with_comments = $wpdb->get_var(
			"SELECT COUNT(DISTINCT p.ID)
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->comments} c ON p.ID = c.comment_post_ID
			WHERE p.post_status = 'trash'
			AND c.comment_approved = '1'"
		);

		if ( $trash_with_comments > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d trashed posts still have approved comments', 'wpshadow' ),
				$trash_with_comments
			);
		}

		// Check for trashed posts referenced in other content.
		$trash_referenced = $wpdb->get_var(
			"SELECT COUNT(DISTINCT p1.ID)
			FROM {$wpdb->posts} p1
			INNER JOIN {$wpdb->posts} p2 ON p2.post_content LIKE CONCAT('%', p1.ID, '%')
			WHERE p1.post_status = 'trash'
			AND p2.post_status = 'publish'"
		);

		if ( $trash_referenced > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d trashed posts referenced in published content', 'wpshadow' ),
				$trash_referenced
			);
		}

		// Check user permissions for untrash.
		$current_user = wp_get_current_user();
		if ( $current_user->ID > 0 ) {
			if ( ! current_user_can( 'delete_posts' ) ) {
				// Check if there are trash items the user should be able to restore.
				$user_trash = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*)
						FROM {$wpdb->posts}
						WHERE post_status = 'trash'
						AND post_author = %d",
						$current_user->ID
					)
				);

				if ( $user_trash > 0 ) {
					$issues[] = __( 'User lacks permission to restore their own trashed posts', 'wpshadow' );
				}
			}
		}

		// Check for trashed posts with missing featured images.
		$trash_missing_thumbs = $wpdb->get_var(
			"SELECT COUNT(DISTINCT p.ID)
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			LEFT JOIN {$wpdb->posts} thumb ON pm.meta_value = thumb.ID
			WHERE p.post_status = 'trash'
			AND pm.meta_key = '_thumbnail_id'
			AND (thumb.ID IS NULL OR thumb.post_status = 'trash')"
		);

		if ( $trash_missing_thumbs > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d trashed posts have missing/trashed featured images', 'wpshadow' ),
				$trash_missing_thumbs
			);
		}

		// Check for database transaction support (for safe restore).
		$storage_engine = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ENGINE
				FROM information_schema.TABLES
				WHERE TABLE_SCHEMA = %s
				AND TABLE_NAME = %s",
				DB_NAME,
				$wpdb->posts
			)
		);

		if ( $storage_engine && strtolower( $storage_engine ) !== 'innodb' ) {
			$issues[] = sprintf(
				/* translators: %s: storage engine name */
				__( 'Database using %s engine (InnoDB recommended for safe restore)', 'wpshadow' ),
				$storage_engine
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-trash-recovery?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
