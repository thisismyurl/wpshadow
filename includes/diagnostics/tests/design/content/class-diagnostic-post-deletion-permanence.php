<?php
/**
 * Post Deletion Permanence Diagnostic
 *
 * Ensures permanently deleted posts are removed from database.
 * Checks for orphaned data and incomplete deletions.
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
 * Post Deletion Permanence Diagnostic Class
 *
 * Verifies that permanently deleted posts are completely removed
 * with no orphaned data remaining in the database.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Post_Deletion_Permanence extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-deletion-permanence';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Deletion Permanence';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensures deleted posts are fully removed with no orphaned data';

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

		// Check for orphaned postmeta (no parent post).
		$orphaned_postmeta = $wpdb->get_var(
			"SELECT COUNT(pm.meta_id)
			FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE p.ID IS NULL"
		);

		if ( $orphaned_postmeta > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: number of records */
				__( '%d orphaned postmeta records (deleted posts not cleaned up)', 'wpshadow' ),
				number_format( $orphaned_postmeta )
			);
		}

		// Check for orphaned term relationships.
		$orphaned_terms = $wpdb->get_var(
			"SELECT COUNT(tr.object_id)
			FROM {$wpdb->term_relationships} tr
			LEFT JOIN {$wpdb->posts} p ON tr.object_id = p.ID
			WHERE p.ID IS NULL"
		);

		if ( $orphaned_terms > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: number of records */
				__( '%d orphaned term relationships (deleted posts)', 'wpshadow' ),
				number_format( $orphaned_terms )
			);
		}

		// Check for orphaned comments.
		$orphaned_comments = $wpdb->get_var(
			"SELECT COUNT(c.comment_ID)
			FROM {$wpdb->comments} c
			LEFT JOIN {$wpdb->posts} p ON c.comment_post_ID = p.ID
			WHERE p.ID IS NULL"
		);

		if ( $orphaned_comments > 20 ) {
			$issues[] = sprintf(
				/* translators: %d: number of comments */
				__( '%d orphaned comments (parent posts deleted)', 'wpshadow' ),
				number_format( $orphaned_comments )
			);
		}

		// Check for orphaned revisions.
		$orphaned_revisions = $wpdb->get_var(
			"SELECT COUNT(r.ID)
			FROM {$wpdb->posts} r
			LEFT JOIN {$wpdb->posts} p ON r.post_parent = p.ID
			WHERE r.post_type = 'revision'
			AND r.post_parent > 0
			AND p.ID IS NULL"
		);

		if ( $orphaned_revisions > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: number of revisions */
				__( '%d orphaned revisions (parent posts deleted)', 'wpshadow' ),
				number_format( $orphaned_revisions )
			);
		}

		// Check for orphaned attachments (featured images, etc).
		$orphaned_attachments = $wpdb->get_var(
			"SELECT COUNT(a.ID)
			FROM {$wpdb->posts} a
			LEFT JOIN {$wpdb->posts} p ON a.post_parent = p.ID
			WHERE a.post_type = 'attachment'
			AND a.post_parent > 0
			AND p.ID IS NULL"
		);

		if ( $orphaned_attachments > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: number of attachments */
				__( '%d orphaned attachments (parent posts deleted)', 'wpshadow' ),
				number_format( $orphaned_attachments )
			);
		}

		// Check for delete_posts capability issues.
		$roles = wp_roles();
		$roles_without_delete = array();
		foreach ( $roles->roles as $role_name => $role_info ) {
			if ( in_array( $role_name, array( 'editor', 'administrator' ), true ) ) {
				if ( ! isset( $role_info['capabilities']['delete_posts'] ) ||
				     ! $role_info['capabilities']['delete_posts'] ) {
					$roles_without_delete[] = $role_name;
				}
			}
		}

		if ( ! empty( $roles_without_delete ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated role names */
				__( 'Roles missing delete_posts capability: %s', 'wpshadow' ),
				implode( ', ', $roles_without_delete )
			);
		}

		// Check for trash items that should be auto-deleted.
		if ( defined( 'EMPTY_TRASH_DAYS' ) && EMPTY_TRASH_DAYS > 0 ) {
			$overdue_trash = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*)
					FROM {$wpdb->posts}
					WHERE post_status = 'trash'
					AND post_modified < DATE_SUB(NOW(), INTERVAL %d DAY)",
					EMPTY_TRASH_DAYS + 1
				)
			);

			if ( $overdue_trash > 10 ) {
				$issues[] = sprintf(
					/* translators: %d: number of posts */
					__( '%d trash items past auto-delete date (cron may not be working)', 'wpshadow' ),
					$overdue_trash
				);
			}
		}

		// Check for deleted post IDs still referenced in options.
		$page_on_front = get_option( 'page_on_front' );
		$page_for_posts = get_option( 'page_for_posts' );

		if ( $page_on_front > 0 ) {
			$front_exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts} WHERE ID = %d",
					$page_on_front
				)
			);
			if ( ! $front_exists ) {
				$issues[] = __( 'Homepage set to deleted post', 'wpshadow' );
			}
		}

		if ( $page_for_posts > 0 ) {
			$posts_page_exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts} WHERE ID = %d",
					$page_for_posts
				)
			);
			if ( ! $posts_page_exists ) {
				$issues[] = __( 'Posts page set to deleted post', 'wpshadow' );
			}
		}

		// Check for post references in widgets/menus.
		$nav_menu_items = $wpdb->get_var(
			"SELECT COUNT(DISTINCT pm.post_id)
			FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} target ON pm.meta_value = target.ID
			WHERE pm.meta_key = '_menu_item_object_id'
			AND target.ID IS NULL
			AND pm.meta_value != '0'"
		);

		if ( $nav_menu_items > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of menu items */
				__( '%d menu items link to deleted posts', 'wpshadow' ),
				$nav_menu_items
			);
		}

		// Check database integrity.
		$posts_table_check = $wpdb->query( "CHECK TABLE {$wpdb->posts}" );
		if ( false === $posts_table_check ) {
			$issues[] = __( 'Posts table may have integrity issues', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-deletion-permanence?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
