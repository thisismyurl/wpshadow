<?php
/**
 * Admin Menu Items Missing Icons Diagnostic
 *
 * Detects admin menu items that don't have icons defined.
 * All menu items should have icons for better visual navigation and UX consistency.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Admin
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Menu Items Missing Icons Diagnostic Class
 *
 * Scans WordPress admin menu items for missing icons,
 * which impacts visual consistency and user experience.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Menu_Items_Missing_Icons extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-menu-items-missing-icons';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Menu Items Missing Icons';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects admin menu items without icons';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only run in admin context.
		if ( ! is_admin() ) {
			return null;
		}

		global $menu;

		$missing_icon_items = array();

		// Check main menu items.
		if ( ! empty( $menu ) && is_array( $menu ) ) {
			foreach ( $menu as $menu_item ) {
				if ( empty( $menu_item ) || ! is_array( $menu_item ) ) {
					continue;
				}

				// Menu item structure: [0] = title, [1] = capability, [2] = slug, [3] = page_title, [4] = classes, [5] = id, [6] = icon.
				$title = isset( $menu_item[0] ) ? wp_strip_all_tags( $menu_item[0] ) : '';
				$slug  = isset( $menu_item[2] ) ? $menu_item[2] : '';
				$icon  = isset( $menu_item[6] ) ? $menu_item[6] : '';

				// Skip separators (they don't need icons).
				if ( strpos( $slug, 'separator' ) !== false ) {
					continue;
				}

				// Skip empty titles (invalid menu items).
				if ( empty( $title ) ) {
					continue;
				}

				// Check if icon is missing or explicitly set to 'none'.
				if ( empty( $icon ) || $icon === 'none' || $icon === 'div' ) {
					$missing_icon_items[] = array(
						'title' => $title,
						'slug'  => $slug,
						'icon'  => $icon,
					);
				}
			}
		}

		// If items missing icons found, return finding.
		if ( ! empty( $missing_icon_items ) ) {
			$item_count = count( $missing_icon_items );

			// Build detailed description.
			$items_list = '';
			$max_items_to_show = 5;
			$shown_items = array_slice( $missing_icon_items, 0, $max_items_to_show );

			foreach ( $shown_items as $item ) {
				$items_list .= sprintf(
					"\n- %s (slug: %s)",
					esc_html( $item['title'] ),
					esc_html( $item['slug'] )
				);
			}

			if ( $item_count > $max_items_to_show ) {
				$items_list .= sprintf(
					/* translators: %d: number of additional items */
					__( "\n...and %d more items", 'wpshadow' ),
					$item_count - $max_items_to_show
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number of menu items, 2: list of affected items */
					__(
						'Found %1$d admin menu item(s) without icons. Menu icons improve visual navigation and help users quickly identify sections. Consider adding Dashicons to these menu items for better UX consistency.%2$s',
						'wpshadow'
					),
					$item_count,
					$items_list
				),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-menu-items-missing-icons',
				'meta'         => array(
					'item_count'     => $item_count,
					'affected_items' => $missing_icon_items,
				),
			);
		}

		return null; // All menu items have icons.
	}
}
