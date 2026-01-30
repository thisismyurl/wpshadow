<?php
/**
 * Diagnostic: Menu Registration Status
 *
 * Validates theme properly registers menu locations.
 * Broken menu registration prevents navigation setup.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Themes
 * @since      1.26028.1858
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Menu_Registration_Status
 *
 * Tests theme menu registration.
 *
 * @since 1.26028.1858
 */
class Diagnostic_Menu_Registration_Status extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'menu-registration-status';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Menu Registration Status';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates theme properly registers menu locations';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'themes';

	/**
	 * Check menu registration status.
	 *
	 * @since  1.26028.1858
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Get registered menu locations.
		$locations = get_registered_nav_menus();

		// If no menu locations registered, flag as issue.
		if ( empty( $locations ) ) {
			// Check if theme supports menus at all.
			$theme_supports_menus = current_theme_supports( 'menus' );

			if ( ! $theme_supports_menus ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Theme does not register any menu locations. Users cannot create and assign menus. Add register_nav_menus() or add_theme_support(\'menus\') in theme functions.php.', 'wpshadow' ),
					'severity'     => 'high',
					'threat_level' => 70,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/menu-registration-status',
					'meta'         => array(
						'registered_locations' => 0,
						'theme_supports_menus' => false,
						'recommendation'       => 'Add menu support to theme',
					),
				);
			} else {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Theme supports menus but no menu locations are registered. Add register_nav_menus() to theme functions.php to define menu locations.', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 60,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/menu-registration-status',
					'meta'         => array(
						'registered_locations' => 0,
						'theme_supports_menus' => true,
						'recommendation'       => 'Register menu locations',
					),
				);
			}
		}

		// Check if menus are assigned to locations.
		$nav_menu_locations = get_nav_menu_locations();
		$unassigned_locations = array();

		foreach ( $locations as $location_key => $location_name ) {
			if ( ! isset( $nav_menu_locations[ $location_key ] ) || ! $nav_menu_locations[ $location_key ] ) {
				$unassigned_locations[] = $location_name;
			}
		}

		// If all locations are unassigned, inform user.
		if ( count( $unassigned_locations ) === count( $locations ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: Number of locations, 2: Location names */
					__( 'Theme registers %1$d menu location(s) but none have menus assigned: %2$s. Create and assign menus in Appearance → Menus.', 'wpshadow' ),
					count( $locations ),
					implode( ', ', $unassigned_locations )
				),
				'severity'     => 'info',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/menu-registration-status',
				'meta'         => array(
					'registered_locations'  => count( $locations ),
					'unassigned_locations'  => $unassigned_locations,
					'theme_supports_menus'  => true,
					'recommendation'        => 'Assign menus to locations',
				),
			);
		}

		// Check for fallback handling.
		$has_fallback = self::check_fallback_handling();

		if ( ! $has_fallback && ! empty( $unassigned_locations ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Location names */
					__( 'Some menu locations are unassigned (%s) and theme has no fallback menu handling. Users will see no navigation. Add fallback_cb parameter to wp_nav_menu() calls.', 'wpshadow' ),
					implode( ', ', $unassigned_locations )
				),
				'severity'     => 'low',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/menu-registration-status',
				'meta'         => array(
					'registered_locations'  => count( $locations ),
					'unassigned_locations'  => $unassigned_locations,
					'has_fallback'          => false,
					'recommendation'        => 'Add menu fallback handling',
				),
			);
		}

		// Menu registration is properly configured.
		return null;
	}

	/**
	 * Check if theme has fallback handling for empty menus.
	 *
	 * @since  1.26028.1858
	 * @return bool True if fallback detected, false otherwise.
	 */
	private static function check_fallback_handling() {
		// This is difficult to detect programmatically without parsing theme files.
		// We can check if wp_page_menu is being used (common fallback).

		// Get current theme.
		$theme = wp_get_theme();

		// Check if theme is a well-known framework with built-in fallbacks.
		$frameworks_with_fallbacks = array(
			'twentytwentyfour',
			'twentytwentythree',
			'twentytwentytwo',
			'twentytwentyone',
			'twentytwenty',
			'generatepress',
			'astra',
			'kadence',
			'blocksy',
		);

		$template = strtolower( $theme->get_template() );

		if ( in_array( $template, $frameworks_with_fallbacks, true ) ) {
			return true;
		}

		// Assume block themes have fallback handling.
		if ( wp_is_block_theme() ) {
			return true;
		}

		// Can't reliably detect, return false to be safe.
		return false;
	}
}
