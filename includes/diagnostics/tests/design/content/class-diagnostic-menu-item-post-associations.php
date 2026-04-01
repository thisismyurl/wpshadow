<?php
/**
 * Menu Item Post Associations Diagnostic
 *
 * Validates menu items correctly link to posts/pages. Tests menu-post relationship
 * integrity and detects broken menu item references.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menu Item Post Associations Diagnostic Class
 *
 * Checks for broken menu item to post/page associations.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Menu_Item_Post_Associations extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'menu-item-post-associations';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Menu Item Post Associations';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates menu items correctly link to posts/pages without broken references';

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

		// Get all nav menu items.
		$menu_items = $wpdb->get_results(
			"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'nav_menu_item' AND post_status = 'publish'",
			ARRAY_A
		);

		if ( empty( $menu_items ) ) {
			return null; // No menu items.
		}

		// Check for menu items with broken post references.
		$broken_post_refs = $wpdb->get_results(
			"SELECT p.ID, pm.meta_value as object_id
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			LEFT JOIN {$wpdb->postmeta} pm_type ON p.ID = pm_type.post_id AND pm_type.meta_key = '_menu_item_type'
			LEFT JOIN {$wpdb->posts} target ON pm.meta_value = target.ID
			WHERE p.post_type = 'nav_menu_item'
			AND p.post_status = 'publish'
			AND pm.meta_key = '_menu_item_object_id'
			AND pm_type.meta_value IN ('post', 'page', 'post_type')
			AND target.ID IS NULL
			AND pm.meta_value != '0'
			AND pm.meta_value != ''",
			ARRAY_A
		);

		if ( ! empty( $broken_post_refs ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of broken menu items */
				__( '%d menu items link to deleted posts/pages (broken links)', 'wpshadow' ),
				count( $broken_post_refs )
			);
		}

		// Check for menu items pointing to trashed posts.
		$trashed_post_refs = $wpdb->get_var(
			"SELECT COUNT(DISTINCT p.ID)
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			INNER JOIN {$wpdb->postmeta} pm_type ON p.ID = pm_type.post_id AND pm_type.meta_key = '_menu_item_type'
			INNER JOIN {$wpdb->posts} target ON pm.meta_value = target.ID
			WHERE p.post_type = 'nav_menu_item'
			AND p.post_status = 'publish'
			AND pm.meta_key = '_menu_item_object_id'
			AND pm_type.meta_value IN ('post', 'page')
			AND target.post_status = 'trash'"
		);

		if ( $trashed_post_refs > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of menu items with trashed links */
				__( '%d menu items link to trashed posts (will show errors)', 'wpshadow' ),
				$trashed_post_refs
			);
		}

		// Check for menu items with draft posts.
		$draft_post_refs = $wpdb->get_var(
			"SELECT COUNT(DISTINCT p.ID)
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			INNER JOIN {$wpdb->postmeta} pm_type ON p.ID = pm_type.post_id AND pm_type.meta_key = '_menu_item_type'
			INNER JOIN {$wpdb->posts} target ON pm.meta_value = target.ID
			WHERE p.post_type = 'nav_menu_item'
			AND p.post_status = 'publish'
			AND pm.meta_key = '_menu_item_object_id'
			AND pm_type.meta_value IN ('post', 'page')
			AND target.post_status = 'draft'"
		);

		if ( $draft_post_refs > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of menu items with draft links */
				__( '%d menu items link to draft posts (not publicly accessible)', 'wpshadow' ),
				$draft_post_refs
			);
		}

		// Check for menu items with missing type meta.
		$missing_type_meta = $wpdb->get_var(
			"SELECT COUNT(p.ID)
			FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_menu_item_type'
			WHERE p.post_type = 'nav_menu_item'
			AND p.post_status = 'publish'
			AND pm.meta_id IS NULL"
		);

		if ( $missing_type_meta > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of menu items without type */
				__( '%d menu items missing type metadata (will not function)', 'wpshadow' ),
				$missing_type_meta
			);
		}

		// Check for menu items with missing object_id meta.
		$missing_object_id = $wpdb->get_var(
			"SELECT COUNT(p.ID)
			FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_menu_item_object_id'
			INNER JOIN {$wpdb->postmeta} pm_type ON p.ID = pm_type.post_id AND pm_type.meta_key = '_menu_item_type'
			WHERE p.post_type = 'nav_menu_item'
			AND p.post_status = 'publish'
			AND pm_type.meta_value IN ('post', 'page', 'post_type')
			AND pm.meta_id IS NULL"
		);

		if ( $missing_object_id > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of menu items without object ID */
				__( '%d post-type menu items missing object ID (broken links)', 'wpshadow' ),
				$missing_object_id
			);
		}

		// Check for duplicate menu items pointing to same post.
		$duplicate_menu_items = $wpdb->get_results(
			"SELECT pm.meta_value as object_id, COUNT(*) as count
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE p.post_type = 'nav_menu_item'
			AND p.post_status = 'publish'
			AND pm.meta_key = '_menu_item_object_id'
			AND pm.meta_value != '0'
			GROUP BY pm.meta_value
			HAVING count > 3",
			ARRAY_A
		);

		if ( ! empty( $duplicate_menu_items ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with duplicate menu items */
				__( '%d posts appear in menus 4+ times (potential duplication)', 'wpshadow' ),
				count( $duplicate_menu_items )
			);
		}

		// Check for orphaned menu items (not assigned to any menu).
		$orphaned_menu_items = $wpdb->get_var(
			"SELECT COUNT(p.ID)
			FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
			LEFT JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'nav_menu'
			WHERE p.post_type = 'nav_menu_item'
			AND p.post_status = 'publish'
			AND tt.term_taxonomy_id IS NULL"
		);

		if ( $orphaned_menu_items > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned menu items */
				__( '%d menu items not assigned to any menu (database bloat)', 'wpshadow' ),
				$orphaned_menu_items
			);
		}

		// Check for menu items with invalid parent references.
		$invalid_parent_refs = $wpdb->get_var(
			"SELECT COUNT(p1.ID)
			FROM {$wpdb->posts} p1
			INNER JOIN {$wpdb->postmeta} pm ON p1.ID = pm.post_id
			LEFT JOIN {$wpdb->posts} p2 ON pm.meta_value = p2.ID
			WHERE p1.post_type = 'nav_menu_item'
			AND p1.post_status = 'publish'
			AND pm.meta_key = '_menu_item_menu_item_parent'
			AND pm.meta_value != '0'
			AND p2.ID IS NULL"
		);

		if ( $invalid_parent_refs > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of menu items with invalid parents */
				__( '%d menu items reference non-existent parent items (broken hierarchy)', 'wpshadow' ),
				$invalid_parent_refs
			);
		}

		// Check for menu items with URL but missing title.
		$missing_titles = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_type = 'nav_menu_item'
			AND post_status = 'publish'
			AND (post_title = '' OR post_title IS NULL)"
		);

		if ( $missing_titles > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of menu items without titles */
				__( '%d menu items have no title (will display incorrectly)', 'wpshadow' ),
				$missing_titles
			);
		}

		// Check for excessive menu items in single menu.
		$menu_sizes = $wpdb->get_results(
			"SELECT tt.term_id, COUNT(*) as item_count
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
			INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			WHERE p.post_type = 'nav_menu_item'
			AND p.post_status = 'publish'
			AND tt.taxonomy = 'nav_menu'
			GROUP BY tt.term_id
			HAVING item_count > 100",
			ARRAY_A
		);

		if ( ! empty( $menu_sizes ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of menus with excessive items */
				__( '%d menus have 100+ items (impacts performance and usability)', 'wpshadow' ),
				count( $menu_sizes )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/menu-item-post-associations?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
