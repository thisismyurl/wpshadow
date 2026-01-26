<?php
/**
 * Admin Menu Items With Broken Links Diagnostic
 *
 * Detects admin menu items that have broken or invalid URLs.
 * Broken menu links can lead to 404 errors and poor user experience.
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
 * Admin Menu Items With Broken Links Diagnostic Class
 *
 * Validates that admin menu items point to accessible pages
 * and don't result in 404 errors or invalid URLs.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Menu_Items_With_Broken_Links extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-menu-items-with-broken-links';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Menu Items With Broken Links';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects admin menu items with broken or invalid URLs';

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

		global $menu, $submenu;

		$broken_items = array();

		// Check main menu items.
		if ( ! empty( $menu ) && is_array( $menu ) ) {
			foreach ( $menu as $menu_item ) {
				if ( empty( $menu_item ) || ! is_array( $menu_item ) ) {
					continue;
				}

				// Menu item structure: [0] = title, [1] = capability, [2] = slug, [3] = page_title.
				$title = isset( $menu_item[0] ) ? wp_strip_all_tags( $menu_item[0] ) : '';
				$slug  = isset( $menu_item[2] ) ? $menu_item[2] : '';

				// Skip separators.
				if ( strpos( $slug, 'separator' ) !== false || empty( $title ) ) {
					continue;
				}

				// Check if the menu slug is potentially broken.
				if ( self::is_potentially_broken_link( $slug ) ) {
					$broken_items[] = array(
						'title' => $title,
						'slug'  => $slug,
						'type'  => 'main',
						'issue' => self::get_link_issue( $slug ),
					);
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

					// Submenu structure: [0] = title, [1] = capability, [2] = slug.
					$title = isset( $submenu_item[0] ) ? wp_strip_all_tags( $submenu_item[0] ) : '';
					$slug  = isset( $submenu_item[2] ) ? $submenu_item[2] : '';

					if ( empty( $title ) || empty( $slug ) ) {
						continue;
					}

					// Check if the submenu slug is potentially broken.
					if ( self::is_potentially_broken_link( $slug ) ) {
						$broken_items[] = array(
							'title'  => $title,
							'slug'   => $slug,
							'parent' => $parent_slug,
							'type'   => 'sub',
							'issue'  => self::get_link_issue( $slug ),
						);
					}
				}
			}
		}

		// If broken items found, return finding.
		if ( ! empty( $broken_items ) ) {
			$item_count = count( $broken_items );

			// Build detailed description.
			$items_list = '';
			$max_items_to_show = 5;
			$shown_items = array_slice( $broken_items, 0, $max_items_to_show );

			foreach ( $shown_items as $item ) {
				$type_label = $item['type'] === 'main' ? __( 'Main Menu', 'wpshadow' ) : __( 'Submenu', 'wpshadow' );
				$items_list .= sprintf(
					"\n- %s [%s] - %s",
					esc_html( $item['title'] ),
					$type_label,
					esc_html( $item['issue'] )
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
						'Found %1$d admin menu item(s) with potentially broken links. These may result in 404 errors or inaccessible pages. This typically happens when plugins are deactivated but leave menu items behind, or when menu slugs contain invalid characters.%2$s',
						'wpshadow'
					),
					$item_count,
					$items_list
				),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-menu-items-with-broken-links',
				'meta'         => array(
					'item_count'     => $item_count,
					'affected_items' => $broken_items,
				),
			);
		}

		return null; // No broken menu links detected.
	}

	/**
	 * Check if a menu slug is potentially broken.
	 *
	 * @since  1.2601.2148
	 * @param  string $slug Menu slug to check.
	 * @return bool True if potentially broken, false otherwise.
	 */
	private static function is_potentially_broken_link( string $slug ): bool {
		// Empty slug is definitely broken.
		if ( empty( $slug ) ) {
			return true;
		}

		// External URLs in menu (unusual but not broken).
		if ( preg_match( '/^https?:\/\//i', $slug ) ) {
			return false;
		}

		// Check for invalid characters that might cause issues.
		if ( preg_match( '/[<>"\']/', $slug ) ) {
			return true;
		}

		// Check if it's a .php file that doesn't exist.
		if ( strpos( $slug, '.php' ) !== false ) {
			// Extract just the filename (remove query string).
			$file_part = strtok( $slug, '?' );
			
			// Check common admin file locations.
			$potential_paths = array(
				ABSPATH . 'wp-admin/' . $file_part,
				WP_PLUGIN_DIR . '/' . dirname( dirname( $file_part ) ) . '/' . $file_part,
			);

			$file_exists = false;
			foreach ( $potential_paths as $path ) {
				if ( file_exists( $path ) ) {
					$file_exists = true;
					break;
				}
			}

			// If .php file mentioned but doesn't exist, it's likely broken.
			if ( ! $file_exists ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get a description of the link issue.
	 *
	 * @since  1.2601.2148
	 * @param  string $slug Menu slug.
	 * @return string Issue description.
	 */
	private static function get_link_issue( string $slug ): string {
		if ( empty( $slug ) ) {
			return __( 'Empty slug', 'wpshadow' );
		}

		if ( preg_match( '/[<>"\']/', $slug ) ) {
			return __( 'Contains invalid characters', 'wpshadow' );
		}

		if ( strpos( $slug, '.php' ) !== false ) {
			return __( 'PHP file not found', 'wpshadow' );
		}

		return __( 'Unknown issue', 'wpshadow' );
	}
}
