<?php
/**
 * Admin Duplicate Admin Menu Entries Diagnostic
 *
 * Detects duplicate admin menu entries that can confuse users
 * and indicate plugin conflicts or improper registration.
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
 * Admin Duplicate Admin Menu Entries Diagnostic Class
 *
 * Scans the WordPress admin menu for duplicate entries
 * that may cause confusion or navigation issues.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Duplicate_Admin_Menu_Entries extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-duplicate-admin-menu-entries';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Duplicate Admin Menu Entries';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects duplicate entries in the WordPress admin menu';

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

		$duplicates = array();

		// Check main menu for duplicates.
		$main_menu_titles = array();
		$main_menu_slugs = array();

		if ( ! empty( $menu ) && is_array( $menu ) ) {
			foreach ( $menu as $position => $menu_item ) {
				if ( empty( $menu_item ) || ! is_array( $menu_item ) ) {
					continue;
				}

				$title = isset( $menu_item[0] ) ? wp_strip_all_tags( $menu_item[0] ) : '';
				$slug  = isset( $menu_item[2] ) ? $menu_item[2] : '';

				// Skip separators and empty items.
				if ( strpos( $slug, 'separator' ) !== false || empty( $title ) ) {
					continue;
				}

				// Check for duplicate titles.
				if ( isset( $main_menu_titles[ $title ] ) ) {
					$duplicates[] = array(
						'title'     => $title,
						'slug'      => $slug,
						'type'      => 'main',
						'duplicate' => 'title',
						'positions' => array( $main_menu_titles[ $title ], $position ),
					);
				} else {
					$main_menu_titles[ $title ] = $position;
				}

				// Check for duplicate slugs (more serious issue).
				if ( isset( $main_menu_slugs[ $slug ] ) ) {
					$duplicates[] = array(
						'title'     => $title,
						'slug'      => $slug,
						'type'      => 'main',
						'duplicate' => 'slug',
						'positions' => array( $main_menu_slugs[ $slug ], $position ),
					);
				} else {
					$main_menu_slugs[ $slug ] = $position;
				}
			}
		}

		// Check submenus for duplicates within each parent.
		if ( ! empty( $submenu ) && is_array( $submenu ) ) {
			foreach ( $submenu as $parent_slug => $submenu_items ) {
				if ( empty( $submenu_items ) || ! is_array( $submenu_items ) ) {
					continue;
				}

				$submenu_titles = array();
				$submenu_slugs = array();

				foreach ( $submenu_items as $position => $submenu_item ) {
					if ( empty( $submenu_item ) || ! is_array( $submenu_item ) ) {
						continue;
					}

					$title = isset( $submenu_item[0] ) ? wp_strip_all_tags( $submenu_item[0] ) : '';
					$slug  = isset( $submenu_item[2] ) ? $submenu_item[2] : '';

					if ( empty( $title ) || empty( $slug ) ) {
						continue;
					}

					// Check for duplicate titles within this submenu.
					if ( isset( $submenu_titles[ $title ] ) ) {
						$duplicates[] = array(
							'title'     => $title,
							'slug'      => $slug,
							'parent'    => $parent_slug,
							'type'      => 'sub',
							'duplicate' => 'title',
							'positions' => array( $submenu_titles[ $title ], $position ),
						);
					} else {
						$submenu_titles[ $title ] = $position;
					}

					// Check for duplicate slugs within this submenu.
					if ( isset( $submenu_slugs[ $slug ] ) ) {
						$duplicates[] = array(
							'title'     => $title,
							'slug'      => $slug,
							'parent'    => $parent_slug,
							'type'      => 'sub',
							'duplicate' => 'slug',
							'positions' => array( $submenu_slugs[ $slug ], $position ),
						);
					} else {
						$submenu_slugs[ $slug ] = $position;
					}
				}
			}
		}

		// If duplicates found, return finding.
		if ( ! empty( $duplicates ) ) {
			$duplicate_count = count( $duplicates );

			// Build detailed description.
			$items_list = '';
			$max_items_to_show = 5;
			$shown_items = array_slice( $duplicates, 0, $max_items_to_show );

			foreach ( $shown_items as $item ) {
				$type_label = $item['type'] === 'main' ? __( 'Main Menu', 'wpshadow' ) : __( 'Submenu', 'wpshadow' );
				$dup_type = $item['duplicate'] === 'slug' ? __( 'slug', 'wpshadow' ) : __( 'title', 'wpshadow' );
				
				$items_list .= sprintf(
					"\n- %s [%s] - Duplicate %s",
					esc_html( $item['title'] ),
					$type_label,
					$dup_type
				);

				if ( isset( $item['parent'] ) ) {
					$items_list .= sprintf(
						' (%s: %s)',
						__( 'parent', 'wpshadow' ),
						esc_html( $item['parent'] )
					);
				}
			}

			if ( $duplicate_count > $max_items_to_show ) {
				$items_list .= sprintf(
					/* translators: %d: number of additional items */
					__( "\n...and %d more duplicates", 'wpshadow' ),
					$duplicate_count - $max_items_to_show
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number of duplicates, 2: list of affected items */
					__(
						'Found %1$d duplicate admin menu entry/entries. This typically occurs when multiple plugins register the same menu item, or when a plugin incorrectly registers menu items multiple times. Duplicates can confuse users and indicate plugin conflicts.%2$s',
						'wpshadow'
					),
					$duplicate_count,
					$items_list
				),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-duplicate-admin-menu-entries',
				'meta'         => array(
					'duplicate_count' => $duplicate_count,
					'duplicates'      => $duplicates,
				),
			);
		}

		return null; // No duplicate menu entries detected.
	}
}
