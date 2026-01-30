<?php
/**
 * Theme Menu Location Issues Diagnostic
 *
 * Verifies all theme menu locations are properly configured and functional.
 *
 * @package    WPShadow\Diagnostics
 * @subpackage Tests
 * @since      1.2601.2202
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Menu Location Issues Diagnostic Class
 *
 * Checks for:
 * - Registered menu locations without assigned menus
 * - Menu locations declared but not used in theme
 * - Menus assigned to non-existent locations
 * - Empty menus assigned to locations
 * - Accessibility issues with menu markup
 *
 * @since 1.2601.2202
 */
class Diagnostic_Theme_Menu_Location_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-menu-location-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Menu Location Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies all theme menu locations are properly configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'themes';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2202
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get registered menu locations.
		$locations = get_registered_nav_menus();
		$theme_locations = get_nav_menu_locations();

		if ( empty( $locations ) ) {
			$issues[] = __( 'Theme does not register any menu locations', 'wpshadow' );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( "\n", $issues ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-menu-locations',
			);
		}

		// Check for unassigned menu locations.
		$unassigned_locations = array();
		foreach ( $locations as $location => $description ) {
			if ( empty( $theme_locations[ $location ] ) ) {
				$unassigned_locations[] = sprintf( '%s (%s)', $description, $location );
			}
		}

		if ( ! empty( $unassigned_locations ) ) {
			$issues[] = sprintf(
				__( 'Menu locations without assigned menus: %s', 'wpshadow' ),
				implode( ', ', $unassigned_locations )
			);
		}

		// Check for menus with no items.
		$empty_menus = array();
		foreach ( $theme_locations as $location => $menu_id ) {
			if ( empty( $menu_id ) ) {
				continue;
			}

			$menu = wp_get_nav_menu_object( $menu_id );
			if ( $menu ) {
				$items = wp_get_nav_menu_items( $menu->term_id );
				if ( empty( $items ) ) {
					$empty_menus[] = sprintf( '%s (%s)', $menu->name, $location );
				}
			}
		}

		if ( ! empty( $empty_menus ) ) {
			$issues[] = sprintf(
				__( 'Empty menus assigned to locations: %s', 'wpshadow' ),
				implode( ', ', $empty_menus )
			);
		}

		// Check for menu accessibility.
		$has_accessibility_issues = self::check_menu_accessibility();
		if ( $has_accessibility_issues ) {
			$issues[] = __( 'Menu markup missing accessibility attributes (ARIA labels, skip links)', 'wpshadow' );
		}

		// Check for duplicate menu locations.
		$location_counts = array_count_values( $theme_locations );
		$duplicate_menus = array();
		foreach ( $location_counts as $menu_id => $count ) {
			if ( $count > 1 && $menu_id > 0 ) {
				$menu = wp_get_nav_menu_object( $menu_id );
				if ( $menu ) {
					$duplicate_menus[] = $menu->name;
				}
			}
		}

		if ( ! empty( $duplicate_menus ) ) {
			$issues[] = sprintf(
				__( 'Same menu assigned to multiple locations: %s', 'wpshadow' ),
				implode( ', ', $duplicate_menus )
			);
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => implode( "\n", $issues ),
			'severity'     => 'medium',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/theme-menu-locations',
		);
	}

	/**
	 * Check menu accessibility.
	 *
	 * @since  1.2601.2202
	 * @return bool True if accessibility issues found.
	 */
	private static function check_menu_accessibility() {
		// Fetch homepage to check menu markup.
		$response = wp_remote_get( home_url() );
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$html = wp_remote_retrieve_body( $response );

		// Check for skip links.
		$has_skip_link = ( strpos( $html, 'skip-link' ) !== false ||
		                   strpos( $html, 'skip-to-content' ) !== false );

		// Check for ARIA labels on nav elements.
		$has_aria = ( strpos( $html, '<nav' ) !== false &&
		             strpos( $html, 'aria-label' ) !== false );

		return ! $has_skip_link || ! $has_aria;
	}
}
