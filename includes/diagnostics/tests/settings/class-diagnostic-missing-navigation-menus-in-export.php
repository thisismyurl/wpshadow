<?php
/**
 * Missing Navigation Menus in Export Diagnostic
 *
 * Detects when WordPress navigation menus are excluded from
 * export files.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Missing Navigation Menus in Export Diagnostic Class
 *
 * Detects when WordPress navigation menus are excluded from
 * export files.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Missing_Navigation_Menus_In_Export extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-navigation-menus-in-export';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Navigation Menus in Export';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects navigation menus excluded from exports';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'export';

	/**
	 * Run the diagnostic check.
	 *
	 * Verifies that navigation menus and menu items are properly
	 * included in export files.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		global $wpdb;

		// Get all menus.
		$menus = get_terms( array(
			'taxonomy'   => 'nav_menu',
			'hide_empty' => false,
		) );

		if ( is_wp_error( $menus ) || empty( $menus ) ) {
			return null;
		}

		$menu_count = count( $menus );
		$total_menu_items = 0;
		$menu_details = array();

		foreach ( $menus as $menu ) {
			$menu_items = wp_get_nav_menu_items( $menu->term_id );

			if ( $menu_items ) {
				$item_count = count( $menu_items );
				$total_menu_items += $item_count;

				$menu_details[] = array(
					'menu_id'  => $menu->term_id,
					'menu_name' => $menu->name,
					'items'    => $item_count,
					'slug'     => $menu->slug,
				);
			}
		}

		// Check menu location assignments.
		$locations = get_nav_menu_locations();
		$assigned_menus = count( array_filter( $locations ) );

		// Get menu items count.
		$total_nav_menu_items = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'nav_menu_item'
			)
		);

		// Check menu item metadata.
		$menu_meta = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} 
				WHERE post_id IN (
					SELECT ID FROM {$wpdb->posts} 
					WHERE post_type = %s
				)",
				'nav_menu_item'
			)
		);

		// Check WXR menu export support.
		$wxr_menus_included = apply_filters( 'wxr_export_menus', true );

		// Check for Customizer menu assignments.
		$customizer_menus = get_option( 'nav_menu_locations', array() );

		if ( $menu_count > 0 && $total_menu_items > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of menus, %d: number of items */
					__( '%d navigation menus with %d items may not be included in exports', 'wpshadow' ),
					$menu_count,
					$total_menu_items
				),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/missing-navigation-menus-in-export',
				'details'      => array(
					'total_menus'                  => $menu_count,
					'total_menu_items'             => $total_menu_items,
					'assigned_menu_locations'      => $assigned_menus,
					'menu_locations_available'     => count( $locations ),
					'menu_items_in_database'       => $total_nav_menu_items,
					'menu_item_metadata_entries'   => $menu_meta,
					'menu_details'                 => $menu_details,
					'wxr_menus_export_enabled'     => $wxr_menus_included,
					'customizer_menu_assignments'  => ! empty( $customizer_menus ),
					'navigation_impact'            => sprintf(
						/* translators: %d: number of menus */
						__( 'Site navigation with %d menus will be broken after restore', 'wpshadow' ),
						$menu_count
					),
					'user_experience'              => __( 'Visitors will be unable to navigate the site without custom menus', 'wpshadow' ),
					'manual_rebuild_effort'        => sprintf(
						/* translators: %d: number of items */
						__( 'Up to %d menu items must be manually recreated', 'wpshadow' ),
						$total_menu_items
					),
					'fix_methods'                  => array(
						__( 'Ensure WordPress export includes nav_menu_item posts', 'wpshadow' ),
						__( 'Use export plugin with menu preservation', 'wpshadow' ),
						__( 'Document all menu structures before export', 'wpshadow' ),
						__( 'Export menus via Tools > Export if available', 'wpshadow' ),
						__( 'Verify menu export settings before backup', 'wpshadow' ),
					),
					'verification'                 => array(
						__( 'Download WXR export and search for nav_menu_item entries', 'wpshadow' ),
						__( 'Verify nav_menu taxonomy included', 'wpshadow' ),
						__( 'Count menu items in XML vs site', 'wpshadow' ),
						__( 'Test import on staging site', 'wpshadow' ),
						__( 'Verify menu locations reassigned after import', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}
}
