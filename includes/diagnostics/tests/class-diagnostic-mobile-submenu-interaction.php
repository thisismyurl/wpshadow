<?php
/**
 * Mobile Submenu Interaction Diagnostic
 *
 * Validates that dropdown/submenu items use tap/click instead of hover-only disclosure on mobile.
 *
 * @since   1.26033.1645
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Submenu Interaction Diagnostic Class
 *
 * Validates that dropdown/submenu items use tap/click instead of hover-only disclosure,
 * ensuring full navigation access on touch devices.
 *
 * @since 1.26033.1645
 */
class Diagnostic_Mobile_Submenu_Interaction extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-submenu-interaction';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Submenu Interaction';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validate dropdown/submenu items use tap/click instead of hover-only disclosure on mobile';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'navigation';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if menu exists
		if ( ! has_nav_menu( 'primary' ) ) {
			$issues[] = __( 'Primary menu not found', 'wpshadow' );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-submenu-interaction',
			);
		}

		// Get the primary menu
		$menu = wp_get_nav_menu_object( get_nav_menu_locations()['primary'] ?? null );
		if ( ! $menu ) {
			$issues[] = __( 'Primary menu not properly configured', 'wpshadow' );
		}

		// Check if menu supports submenus
		$menu_items = wp_get_nav_menu_items( $menu->term_id ?? 0 );
		if ( $menu_items ) {
			$has_submenus = false;
			foreach ( $menu_items as $item ) {
				if ( isset( $item->menu_item_parent ) && $item->menu_item_parent > 0 ) {
					$has_submenus = true;
					break;
				}
			}

			if ( $has_submenus ) {
				// Check for touch-friendly menu plugins
				$menu_plugins = array(
					'mobile-menu' => 'Mobile Menu',
					'responsive-menu' => 'Responsive Menu',
				);

				$has_mobile_menu_plugin = false;
				foreach ( $menu_plugins as $plugin_slug => $plugin_name ) {
					if ( is_plugin_active( "$plugin_slug/$plugin_slug.php" ) ) {
						$has_mobile_menu_plugin = true;
						break;
					}
				}

				if ( ! $has_mobile_menu_plugin ) {
					// Check if theme has mobile menu support
					$supports_mobile_menu = apply_filters( 'wpshadow_theme_supports_mobile_menu', false );
					if ( ! $supports_mobile_menu ) {
						$issues[] = __( 'Submenus detected but mobile tap-to-expand support not confirmed', 'wpshadow' );
					}
				}
			}
		}

		// Check for hover-only CSS
		global $wp_styles;
		if ( isset( $wp_styles ) && is_object( $wp_styles ) ) {
			foreach ( $wp_styles->registered as $handle => $obj ) {
				if ( strpos( $obj->src ?? '', 'menu' ) !== false || strpos( $obj->src ?? '', 'nav' ) !== false ) {
					// Check if handle includes mobile-specific styles
					if ( strpos( $handle, 'mobile' ) === false && strpos( $handle, 'responsive' ) === false ) {
						$issues[] = sprintf(
							/* translators: %s: CSS handle */
							__( 'Menu CSS (%s) may not include mobile/touch interaction support', 'wpshadow' ),
							$handle
						);
					}
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-submenu-interaction',
			);
		}

		return null;
	}
}
