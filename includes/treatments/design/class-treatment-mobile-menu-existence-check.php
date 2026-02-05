<?php
/**
 * Mobile Menu Existence Check Treatment
 *
 * Validates that a mobile-friendly navigation menu exists for screen widths <768px.
 *
 * @since   1.6033.1645
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Menu Existence Check Treatment Class
 *
 * Validates that a mobile-friendly navigation menu exists and is properly implemented
 * for screen widths <768px with keyboard and screen reader accessibility.
 *
 * @since 1.6033.1645
 */
class Treatment_Mobile_Menu_Existence_Check extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-menu-existence-check';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Menu Existence Check';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validate that a mobile-friendly navigation menu exists for screen widths <768px';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'navigation';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if any nav menu is registered
		$nav_menus = get_registered_nav_menus();
		if ( empty( $nav_menus ) ) {
			$issues[] = __( 'No navigation menus registered', 'wpshadow' );
		} else {
			// Check if at least one menu is assigned
			$menu_locations = get_nav_menu_locations();
			$has_assigned_menu = false;

			foreach ( $nav_menus as $location => $menu_name ) {
				if ( isset( $menu_locations[ $location ] ) && $menu_locations[ $location ] > 0 ) {
					$has_assigned_menu = true;
					break;
				}
			}

			if ( ! $has_assigned_menu ) {
				$issues[] = __( 'Navigation menus registered but none assigned to display locations', 'wpshadow' );
			}
		}

		// Check if mobile menu toggle button exists
		$has_mobile_menu_toggle = apply_filters( 'wpshadow_theme_has_mobile_menu_toggle', false );
		if ( ! $has_mobile_menu_toggle ) {
			$issues[] = __( 'Mobile menu toggle button not detected', 'wpshadow' );
		}

		// Check for responsive menu plugins
		$mobile_menu_plugins = array(
			'mobile-menu' => 'Mobile Menu',
			'responsive-menu' => 'Responsive Menu',
			'wp-mobile-menu' => 'WP Mobile Menu',
			'minimal-mobile-menu' => 'Minimal Mobile Menu',
		);

		$has_mobile_menu_plugin = false;
		foreach ( $mobile_menu_plugins as $plugin_slug => $plugin_name ) {
			if ( is_plugin_active( "$plugin_slug/$plugin_slug.php" ) ) {
				$has_mobile_menu_plugin = true;
				break;
			}
		}

		// Check if theme is mobile-responsive
		if ( ! $has_mobile_menu_plugin ) {
			$theme = wp_get_theme();
			$is_mobile_ready = $theme->is_child_theme() ? true : apply_filters( 'wpshadow_theme_is_mobile_ready', false );
			
			if ( ! $is_mobile_ready ) {
				$issues[] = __( 'Theme may not have built-in mobile menu support; no mobile menu plugin detected', 'wpshadow' );
			}
		}

		// Check for ARIA labels on menu
		$menu_aria_support = apply_filters( 'wpshadow_menu_has_aria_labels', false );
		if ( ! $menu_aria_support ) {
			$issues[] = __( 'Menu ARIA labels not confirmed for screen reader support', 'wpshadow' );
		}

		// Check for keyboard accessibility on menu
		$menu_keyboard_support = apply_filters( 'wpshadow_menu_supports_keyboard', false );
		if ( ! $menu_keyboard_support ) {
			$issues[] = __( 'Menu keyboard navigation support not confirmed', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-menu-existence-check',
			);
		}

		return null;
	}
}
