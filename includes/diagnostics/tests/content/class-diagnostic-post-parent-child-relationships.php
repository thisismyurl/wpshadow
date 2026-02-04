<?php
/**
 * Post Parent-Child Relationships Diagnostic
 *
 * Validates hierarchical post relationships (pages). Tests parent/child data integrity
 * and detects circular references or broken hierarchies.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Parent-Child Relationships Diagnostic Class
 *
 * Checks for issues in hierarchical post structures.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Post_Parent_Child_Relationships extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-parent-child-relationships';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Parent-Child Relationships';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates hierarchical post relationships and parent/child data integrity';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Get hierarchical post types.
		$hierarchical_types = get_post_types( array( 'hierarchical' => true ), 'names' );

		if ( empty( $hierarchical_types ) ) {
			return null; // No hierarchical types.
		}

		$type_placeholders = implode( ',', array_fill( 0, count( $hierarchical_types ), '%s' ) );

		// Check for posts with invalid parent IDs.
		$invalid_parents = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(p1.ID)
				FROM {$wpdb->posts} p1
				LEFT JOIN {$wpdb->posts} p2 ON p1.post_parent = p2.ID
				WHERE p1.post_parent > 0
				AND p2.ID IS NULL
				AND p1.post_type IN ($type_placeholders)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				...$hierarchical_types
			)
		);

		if ( $invalid_parents > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with invalid parents */
				__( '%d posts reference non-existent parent posts (broken hierarchy)', 'wpshadow' ),
				$invalid_parents
			);
		}

		// Check for circular references (child is parent of its own ancestor).
		$circular_references = array();
		$sample_posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_parent
				FROM {$wpdb->posts}
				WHERE post_parent > 0
				AND post_type IN ($type_placeholders)
				LIMIT 500", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				...$hierarchical_types
			),
			ARRAY_A
		);

		foreach ( $sample_posts as $post ) {
			$visited = array();
			$current_id = (int) $post['ID'];
			$parent_id = (int) $post['post_parent'];

			while ( $parent_id > 0 ) {
				if ( in_array( $parent_id, $visited, true ) || $parent_id === $current_id ) {
					$circular_references[] = $current_id;
					break;
				}

				$visited[] = $parent_id;

				$next_parent = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT post_parent FROM {$wpdb->posts} WHERE ID = %d",
						$parent_id
					)
				);

				if ( ! $next_parent ) {
					break;
				}

				$parent_id = (int) $next_parent;

				// Prevent infinite loops.
				if ( count( $visited ) > 50 ) {
					$circular_references[] = $current_id;
					break;
				}
			}
		}

		if ( ! empty( $circular_references ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with circular references */
				__( '%d posts have circular parent references (causes infinite loops)', 'wpshadow' ),
				count( array_unique( $circular_references ) )
			);
		}

		// Check for posts parented to wrong post type.
		$wrong_type_parents = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(p1.ID)
				FROM {$wpdb->posts} p1
				INNER JOIN {$wpdb->posts} p2 ON p1.post_parent = p2.ID
				WHERE p1.post_parent > 0
				AND p1.post_type != p2.post_type
				AND p1.post_type IN ($type_placeholders)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				...$hierarchical_types
			)
		);

		if ( $wrong_type_parents > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with wrong-type parents */
				__( '%d posts parented to different post types (hierarchy mismatch)', 'wpshadow' ),
				$wrong_type_parents
			);
		}

		// Check for excessively deep hierarchies.
		$deep_hierarchies = 0;
		foreach ( $sample_posts as $post ) {
			$depth = 0;
			$parent_id = (int) $post['post_parent'];

			while ( $parent_id > 0 && $depth < 20 ) {
				++$depth;
				$parent_id = (int) $wpdb->get_var(
					$wpdb->prepare(
						"SELECT post_parent FROM {$wpdb->posts} WHERE ID = %d",
						$parent_id
					)
				);
			}

			if ( $depth >= 10 ) {
				++$deep_hierarchies;
			}
		}

		if ( $deep_hierarchies > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of deeply nested posts */
				__( '%d posts nested 10+ levels deep (impacts performance and usability)', 'wpshadow' ),
				$deep_hierarchies
			);
		}

		// Check for orphaned child posts (parent is in trash).
		$orphaned_children = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(p1.ID)
				FROM {$wpdb->posts} p1
				INNER JOIN {$wpdb->posts} p2 ON p1.post_parent = p2.ID
				WHERE p1.post_parent > 0
				AND p1.post_status NOT IN ('trash', 'auto-draft')
				AND p2.post_status = 'trash'
				AND p1.post_type IN ($type_placeholders)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				...$hierarchical_types
			)
		);

		if ( $orphaned_children > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned children */
				__( '%d published posts have trashed parents (orphaned in hierarchy)', 'wpshadow' ),
				$orphaned_children
			);
		}

		// Check for menu_order conflicts in siblings.
		$menu_order_conflicts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_parent, menu_order, COUNT(*) as count
				FROM {$wpdb->posts}
				WHERE post_parent > 0
				AND post_status NOT IN ('trash', 'auto-draft')
				AND post_type IN ($type_placeholders)
				GROUP BY post_parent, menu_order
				HAVING count > 3
				LIMIT 20", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				...$hierarchical_types
			),
			ARRAY_A
		);

		if ( ! empty( $menu_order_conflicts ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of parent posts with menu_order conflicts */
				__( '%d parent posts have duplicate menu_order values in children (sort conflicts)', 'wpshadow' ),
				count( $menu_order_conflicts )
			);
		}

		// Check for posts with themselves as parent (direct circular reference).
		$self_parents = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE ID = post_parent
				AND post_type IN ($type_placeholders)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				...$hierarchical_types
			)
		);

		if ( $self_parents > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of self-parented posts */
				__( '%d posts are their own parent (critical hierarchy error)', 'wpshadow' ),
				$self_parents
			);
		}

		// Check for hierarchical capability issues.
		$user = wp_get_current_user();
		if ( $user && $user->ID > 0 ) {
			foreach ( $hierarchical_types as $post_type ) {
				$type_obj = get_post_type_object( $post_type );
				if ( $type_obj && ! current_user_can( $type_obj->cap->edit_posts ) ) {
					$issues[] = sprintf(
						/* translators: %s: post type name */
						__( 'Current user cannot edit %s (hierarchy management unavailable)', 'wpshadow' ),
						esc_html( $post_type )
					);
					break; // Only report once.
				}
			}
		}

		// Check for excessive top-level items (no parent).
		$top_level_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_parent = 0
				AND post_status = 'publish'
				AND post_type IN ($type_placeholders)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				...$hierarchical_types
			)
		);

		if ( $top_level_count > 200 ) {
			$issues[] = sprintf(
				/* translators: %d: number of top-level posts */
				__( '%d top-level hierarchical posts (consider organizing into parent pages)', 'wpshadow' ),
				$top_level_count
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/post-parent-child-relationships',
			);
		}

		return null;
	}
}
