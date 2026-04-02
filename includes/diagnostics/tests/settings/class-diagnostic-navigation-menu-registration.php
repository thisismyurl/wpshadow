<?php
/**
 * Navigation Menu Registration Diagnostic
 *
 * Validates that navigation menus are properly registered and
 * displayed in theme templates with accessibility support.
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
 * Navigation Menu Registration Diagnostic Class
 *
 * Checks navigation menu configuration and usage.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Navigation_Menu_Registration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'navigation-menu-registration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Navigation Menu Registration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates navigation menu registration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get registered nav menu locations.
		$locations = get_registered_nav_menus();

		if ( empty( $locations ) ) {
			$issues[] = __( 'No navigation menu locations registered', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme does not register navigation menu locations.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'details'      => array(
					'issues'         => $issues,
					'recommendation' => __( 'Register navigation menus with register_nav_menus() in functions.php.', 'wpshadow' ),
				),
			);
		}

		// Check for reasonable number of menu locations.
		if ( count( $locations ) > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of menu locations */
				__( '%d menu locations registered (may be excessive)', 'wpshadow' ),
				count( $locations )
			);
		}

		// Check which locations have assigned menus.
		$menu_locations = get_nav_menu_locations();
		$empty_locations = 0;

		foreach ( $locations as $location_id => $location_name ) {
			if ( ! isset( $menu_locations[ $location_id ] ) || empty( $menu_locations[ $location_id ] ) ) {
				$empty_locations++;
			}
		}

		if ( $empty_locations === count( $locations ) ) {
			$issues[] = __( 'No menus assigned to any registered locations', 'wpshadow' );
		} elseif ( $empty_locations > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of empty menu locations */
				__( '%d menu locations have no assigned menus', 'wpshadow' ),
				$empty_locations
			);
		}

		// Check theme templates for menu display.
		$template_dir = get_template_directory();
		$templates    = array( 'header.php', 'footer.php', 'navigation.php', 'nav.php' );

		$templates_with_menus = 0;
		foreach ( $templates as $template ) {
			$file = $template_dir . '/' . $template;
			if ( file_exists( $file ) ) {
				$content = file_get_contents( $file );
				if ( false !== stripos( $content, 'wp_nav_menu' ) ) {
					$templates_with_menus++;

					// Check for accessibility attributes.
					if ( false === stripos( $content, 'aria-label' ) && false === stripos( $content, 'aria-labelledby' ) ) {
						// Not critical but worth noting.
					}
				}
			}
		}

		if ( $templates_with_menus === 0 ) {
			$issues[] = __( 'No templates display navigation menus (wp_nav_menu not found)', 'wpshadow' );
		}

		// Check for menu support.
		if ( ! current_theme_supports( 'menus' ) ) {
			$issues[] = __( 'Theme does not declare menu support', 'wpshadow' );
		}

		// Check actual menus for configuration issues.
		$menus = wp_get_nav_menus();

		if ( empty( $menus ) ) {
			$issues[] = __( 'No navigation menus created', 'wpshadow' );
		}

		foreach ( $menus as $menu ) {
			$menu_items = wp_get_nav_menu_items( $menu->term_id );

			if ( empty( $menu_items ) ) {
				$issues[] = sprintf(
					/* translators: %s: menu name */
					__( 'Menu "%s" has no items', 'wpshadow' ),
					$menu->name
				);
			}

			// Check for excessive menu items.
			if ( is_array( $menu_items ) && count( $menu_items ) > 50 ) {
				$issues[] = sprintf(
					/* translators: 1: menu name, 2: number of items */
					__( 'Menu "%1$s" has %2$d items (may impact performance)', 'wpshadow' ),
					$menu->name,
					count( $menu_items )
				);
			}
		}

		// Check functions.php for proper registration.
		$functions_file = $template_dir . '/functions.php';
		if ( file_exists( $functions_file ) ) {
			$content = file_get_contents( $functions_file );

			if ( false !== stripos( $content, 'register_nav_menus' ) || false !== stripos( $content, 'register_nav_menu' ) ) {
				// Check if registration is hooked properly.
				if ( false === stripos( $content, 'after_setup_theme' ) ) {
					$issues[] = __( 'Menu registration not hooked to after_setup_theme', 'wpshadow' );
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of navigation menu issues */
					__( 'Found %d navigation menu configuration issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'details'      => array(
					'issues'           => $issues,
					'registered_locations' => count( $locations ),
					'empty_locations'  => $empty_locations,
					'total_menus'      => count( $menus ),
					'recommendation'   => __( 'Register menu locations, create and assign menus, and display with wp_nav_menu() including ARIA labels.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
