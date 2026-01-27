<?php
/**
 * Admin Heavy SVG Injected Into Menu Items Diagnostic
 *
 * Detects when large SVG markup is directly injected into WordPress admin menu items
 * instead of using proper icon methods (Dashicons, data URIs, or external icon fonts).
 * This can bloat HTML and impact admin performance.
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
 * Admin Heavy SVG Injected Into Menu Items Diagnostic Class
 *
 * Scans WordPress admin menu items for directly embedded SVG markup
 * that exceeds reasonable size thresholds.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Heavy_SVG_Injected_Into_Menu_Items extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-heavy-svg-injected-into-menu-items';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Heavy SVG Injected Into Menu Items';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects large SVG markup directly injected into admin menu items';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Size threshold for "heavy" SVG (in bytes)
	 *
	 * @var int
	 */
	const SVG_SIZE_THRESHOLD = 1024; // 1KB

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

		global $menu, $submenu;

		$heavy_svg_items = array();
		$total_svg_size = 0;

		// Check main menu items.
		if ( ! empty( $menu ) && is_array( $menu ) ) {
			foreach ( $menu as $menu_item ) {
				if ( empty( $menu_item ) || ! is_array( $menu_item ) ) {
					continue;
				}

				// Menu item structure: [0] = title, [1] = capability, [2] = slug, [3] = page_title, [4] = classes, [5] = id, [6] = icon.
				$icon = isset( $menu_item[6] ) ? $menu_item[6] : '';
				
				if ( empty( $icon ) ) {
					continue;
				}

				// Check if icon contains SVG markup.
				if ( self::contains_svg( $icon ) ) {
					$svg_size = strlen( $icon );
					
					if ( $svg_size > self::SVG_SIZE_THRESHOLD ) {
						$total_svg_size += $svg_size;
						$heavy_svg_items[] = array(
							'title' => isset( $menu_item[0] ) ? wp_strip_all_tags( $menu_item[0] ) : __( 'Unknown Menu Item', 'wpshadow' ),
							'slug'  => isset( $menu_item[2] ) ? $menu_item[2] : '',
							'size'  => $svg_size,
							'type'  => 'main',
						);
					}
				}
			}
		}

		// Check submenu items.
		if ( ! empty( $submenu ) && is_array( $submenu ) ) {
			foreach ( $submenu as $parent_slug => $submenu_items ) {
				if ( empty( $submenu_items ) || ! is_array( $submenu_items ) ) {
					continue;
				}

				foreach ( $submenu_items as $submenu_item ) {
					if ( empty( $submenu_item ) || ! is_array( $submenu_item ) ) {
						continue;
					}

					// Submenu items can have icons too (though less common).
					// Structure: [0] = title, [1] = capability, [2] = slug, [3] = page_title.
					$title = isset( $submenu_item[0] ) ? $submenu_item[0] : '';
					
					if ( empty( $title ) ) {
						continue;
					}

					// Check if title contains SVG markup (sometimes plugins inject it here).
					if ( self::contains_svg( $title ) ) {
						$svg_size = strlen( $title );
						
						if ( $svg_size > self::SVG_SIZE_THRESHOLD ) {
							$total_svg_size += $svg_size;
							$heavy_svg_items[] = array(
								'title'  => wp_strip_all_tags( $title ),
								'slug'   => isset( $submenu_item[2] ) ? $submenu_item[2] : '',
								'parent' => $parent_slug,
								'size'   => $svg_size,
								'type'   => 'sub',
							);
						}
					}
				}
			}
		}

		// If heavy SVG items found, return finding.
		if ( ! empty( $heavy_svg_items ) ) {
			$item_count = count( $heavy_svg_items );
			$total_kb = round( $total_svg_size / 1024, 2 );

			// Build detailed description.
			$items_list = '';
			$max_items_to_show = 5;
			$shown_items = array_slice( $heavy_svg_items, 0, $max_items_to_show );

			foreach ( $shown_items as $item ) {
				$size_kb = round( $item['size'] / 1024, 2 );
				$items_list .= sprintf(
					"\n- %s (%s KB) [%s]",
					esc_html( $item['title'] ),
					$size_kb,
					$item['type'] === 'main' ? __( 'Main Menu', 'wpshadow' ) : __( 'Submenu', 'wpshadow' )
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
					/* translators: 1: number of menu items, 2: total size in KB, 3: list of affected items */
					__(
						'Found %1$d admin menu item(s) with heavy SVG markup directly injected (total %2$s KB). This bloats the HTML on every admin page load and can impact performance. Consider using Dashicons, data URIs, or external icon fonts instead.%3$s',
						'wpshadow'
					),
					$item_count,
					$total_kb,
					$items_list
				),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-heavy-svg-injected-into-menu-items',
				'meta'         => array(
					'item_count'     => $item_count,
					'total_size'     => $total_svg_size,
					'affected_items' => $heavy_svg_items,
				),
			);
		}

		return null; // No heavy SVG injection detected.
	}

	/**
	 * Check if string contains SVG markup.
	 *
	 * @since  1.2601.2148
	 * @param  string $content Content to check.
	 * @return bool True if contains SVG, false otherwise.
	 */
	private static function contains_svg( string $content ): bool {
		// Check for SVG opening tags.
		return ( false !== stripos( $content, '<svg' ) );
	}
}
