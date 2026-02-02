<?php
/**
 * Post Orphan Detection Diagnostic
 *
 * Detects orphaned posts with invalid parent IDs. Tests for broken hierarchies
 * and identifies posts pointing to non-existent parents.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Orphan Detection Diagnostic Class
 *
 * Checks for orphaned posts with invalid parent references.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Post_Orphan_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-orphan-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Orphan Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects orphaned posts with invalid parent IDs and broken hierarchies';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Get hierarchical post types.
		$hierarchical_types = get_post_types( array( 'hierarchical' => true ), 'names' );
		
		if ( empty( $hierarchical_types ) ) {
			return null; // No hierarchical post types.
		}

		$type_placeholders = implode( ', ', array_fill( 0, count( $hierarchical_types ), '%s' ) );

		// Check for orphaned posts (parent ID doesn't exist).
		$orphaned_posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p1.ID, p1.post_type, p1.post_parent, p1.post_title
				FROM {$wpdb->posts} p1
				LEFT JOIN {$wpdb->posts} p2 ON p1.post_parent = p2.ID
				WHERE p1.post_parent > 0
				AND p2.ID IS NULL
				AND p1.post_type IN ({$type_placeholders})
				AND p1.post_status NOT IN ('trash', 'auto-draft')
				LIMIT 50",
				...$hierarchical_types
			),
			ARRAY_A
		);

		if ( ! empty( $orphaned_posts ) ) {
			$total_orphaned = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(p1.ID)
					FROM {$wpdb->posts} p1
					LEFT JOIN {$wpdb->posts} p2 ON p1.post_parent = p2.ID
					WHERE p1.post_parent > 0
					AND p2.ID IS NULL
					AND p1.post_type IN ({$type_placeholders})
					AND p1.post_status NOT IN ('trash', 'auto-draft')",
					...$hierarchical_types
				)
			);

			$issues[] = sprintf(
				/* translators: %d: number of orphaned posts */
				__( '%d posts reference non-existent parent posts (broken hierarchy)', 'wpshadow' ),
				$total_orphaned
			);
		}

		// Check for posts with trashed parents.
		$trashed_parent_posts = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(p1.ID)
				FROM {$wpdb->posts} p1
				INNER JOIN {$wpdb->posts} p2 ON p1.post_parent = p2.ID
				WHERE p1.post_parent > 0
				AND p2.post_status = 'trash'
				AND p1.post_type IN ({$type_placeholders})
				AND p1.post_status NOT IN ('trash', 'auto-draft')",
				...$hierarchical_types
			)
		);

		if ( $trashed_parent_posts > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with trashed parents */
				__( '%d posts have trashed parents (will be displayed incorrectly)', 'wpshadow' ),
				$trashed_parent_posts
			);
		}

		// Check for posts with draft parents.
		$draft_parent_posts = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(p1.ID)
				FROM {$wpdb->posts} p1
				INNER JOIN {$wpdb->posts} p2 ON p1.post_parent = p2.ID
				WHERE p1.post_parent > 0
				AND p2.post_status = 'draft'
				AND p1.post_type IN ({$type_placeholders})
				AND p1.post_status = 'publish'",
				...$hierarchical_types
			)
		);

		if ( $draft_parent_posts > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with draft parents */
				__( '%d published posts have draft parents (hierarchy issues)', 'wpshadow' ),
				$draft_parent_posts
			);
		}

		// Check for self-referential parents (post is its own parent).
		$self_parent_posts = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(ID)
				FROM {$wpdb->posts}
				WHERE post_parent = ID
				AND post_type IN ({$type_placeholders})
				AND post_status NOT IN ('trash', 'auto-draft')",
				...$hierarchical_types
			)
		);

		if ( $self_parent_posts > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of self-referential posts */
				__( '%d posts set as their own parent (invalid hierarchy)', 'wpshadow' ),
				$self_parent_posts
			);
		}

		// Check for posts referencing parents of wrong post type.
		$wrong_type_parents = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(p1.ID)
				FROM {$wpdb->posts} p1
				INNER JOIN {$wpdb->posts} p2 ON p1.post_parent = p2.ID
				WHERE p1.post_parent > 0
				AND p1.post_type != p2.post_type
				AND p1.post_type IN ({$type_placeholders})
				AND p1.post_status NOT IN ('trash', 'auto-draft')",
				...$hierarchical_types
			)
		);

		if ( $wrong_type_parents > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with wrong-type parents */
				__( '%d posts reference parents of different post type (data inconsistency)', 'wpshadow' ),
				$wrong_type_parents
			);
		}

		// Check for deeply nested hierarchies (potential performance issue).
		$deep_hierarchies = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p1.ID, p1.post_type, p1.post_title
				FROM {$wpdb->posts} p1
				WHERE p1.post_type IN ({$type_placeholders})
				AND p1.post_status NOT IN ('trash', 'auto-draft')
				AND p1.post_parent > 0
				LIMIT 100",
				...$hierarchical_types
			),
			ARRAY_A
		);

		$excessive_depth = 0;
		foreach ( $deep_hierarchies as $post ) {
			$depth = 0;
			$current_parent = (int) $post['ID'];
			$visited = array();

			while ( $current_parent > 0 && $depth < 20 ) {
				if ( isset( $visited[ $current_parent ] ) ) {
					break; // Circular reference detected.
				}

				$visited[ $current_parent ] = true;
				$current_parent = (int) $wpdb->get_var(
					$wpdb->prepare(
						"SELECT post_parent FROM {$wpdb->posts} WHERE ID = %d",
						$current_parent
					)
				);
				++$depth;
			}

			if ( $depth > 10 ) {
				++$excessive_depth;
			}
		}

		if ( $excessive_depth > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of deeply nested posts */
				__( '%d posts nested 10+ levels deep (may impact performance)', 'wpshadow' ),
				$excessive_depth
			);
		}

		// Check for posts with invalid parent IDs (negative or extreme values).
		$invalid_parent_ids = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(ID)
				FROM {$wpdb->posts}
				WHERE (post_parent < 0 OR post_parent > 4294967295)
				AND post_type IN ({$type_placeholders})
				AND post_status NOT IN ('trash', 'auto-draft')",
				...$hierarchical_types
			)
		);

		if ( $invalid_parent_ids > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with invalid parent IDs */
				__( '%d posts have invalid parent IDs (database corruption)', 'wpshadow' ),
				$invalid_parent_ids
			);
		}

		// Check for attachment orphans (attachments with deleted post_parent).
		$orphaned_attachments = $wpdb->get_var(
			"SELECT COUNT(p1.ID)
			FROM {$wpdb->posts} p1
			LEFT JOIN {$wpdb->posts} p2 ON p1.post_parent = p2.ID
			WHERE p1.post_type = 'attachment'
			AND p1.post_parent > 0
			AND p2.ID IS NULL"
		);

		if ( $orphaned_attachments > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned attachments */
				__( '%d attachments reference deleted parent posts (media library clutter)', 'wpshadow' ),
				$orphaned_attachments
			);
		}

		// Check for menu_order inconsistencies (gaps or duplicates).
		foreach ( $hierarchical_types as $post_type ) {
			$menu_order_issues = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT post_parent, menu_order, COUNT(*) as duplicate_count
					FROM {$wpdb->posts}
					WHERE post_type = %s
					AND post_status = 'publish'
					GROUP BY post_parent, menu_order
					HAVING duplicate_count > 1
					LIMIT 10",
					$post_type
				),
				ARRAY_A
			);

			if ( ! empty( $menu_order_issues ) ) {
				$issues[] = sprintf(
					/* translators: %s: post type */
					__( 'Post type "%s" has duplicate menu_order values (display order conflicts)', 'wpshadow' ),
					esc_html( $post_type )
				);
				break; // Only report once.
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/post-orphan-detection',
			);
		}

		return null;
	}
}
