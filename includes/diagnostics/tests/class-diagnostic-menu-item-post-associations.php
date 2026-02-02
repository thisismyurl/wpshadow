<?php
/**
 * Menu Item Post Associations Diagnostic
 *
 * Checks if menu items properly reference existing posts.
 *
 * @since   1.26033.0800
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Menu_Item_Post_Associations Class
 *
 * Validates menu item to post relationships.
 *
 * @since 1.26033.0800
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
	protected static $description = 'Verifies menu items reference valid posts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0800
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check for menu items pointing to non-existent posts
		$broken_menu_items = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} pm
			WHERE pm.meta_key = '_menu_item_object_id'
			AND pm.meta_value != '0'
			AND CAST(pm.meta_value AS UNSIGNED) > 0
			AND NOT EXISTS (SELECT 1 FROM {$wpdb->posts} p WHERE p.ID = CAST(pm.meta_value AS UNSIGNED))"
		);

		if ( intval( $broken_menu_items ) > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of broken menu items */
					__( 'Found %d menu items pointing to deleted or non-existent posts. These will show as broken links in your menus.', 'wpshadow' ),
					intval( $broken_menu_items )
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/menu-item-post-associations',
			);
		}

		return null; // Menu item associations are valid
	}
}
