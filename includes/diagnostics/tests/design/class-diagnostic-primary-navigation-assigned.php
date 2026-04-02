<?php
/**
 * Primary Navigation Assigned Diagnostic
 *
 * Checks whether the primary/main navigation menu location registered by
 * the active theme has a menu assigned to it.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Primary_Navigation_Assigned Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Primary_Navigation_Assigned extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'primary-navigation-assigned';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Primary Navigation Assigned';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the primary or main navigation menu location registered by the active theme has a menu assigned, ensuring visitors can navigate the site.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'design';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * Use has_nav_menu on registered locations.
	 *
	 * TODO Fix Plan:
	 * Fix by creating/assigning primary menu.
	 *
	 * Constraints:
	 * - Must be testable using built-in WordPress functions or PHP checks.
	 * - Must be fixable via hooks/filters/settings/DB/PHP/server setting.
	 * - Must not modify WordPress core files.
	 * - Must improve performance, security, or site success.
	 *
	 * @since  0.6093.1200
	 * @return array|null Return finding array when issue exists, null when healthy.
	 */
	public static function check() {
		// Get all registered navigation menu locations for the active theme.
		$locations = get_registered_nav_menus();

		if ( empty( $locations ) ) {
			return null; // Theme does not register nav menu locations — cannot check.
		}

		// Check if a primary/main nav location has a menu assigned.
		$primary_keys = array( 'primary', 'main', 'header', 'top', 'main-menu', 'header-menu', 'primary-menu' );

		$primary_location     = null;
		$primary_location_key = null;

		foreach ( $primary_keys as $key ) {
			if ( isset( $locations[ $key ] ) ) {
				$primary_location     = $locations[ $key ];
				$primary_location_key = $key;
				break;
			}
		}

		if ( null === $primary_location_key ) {
			// Fall back to the first registered location.
			$primary_location_key = array_key_first( $locations );
			$primary_location     = $locations[ $primary_location_key ];
		}

		if ( has_nav_menu( $primary_location_key ) ) {
			return null; // Primary navigation is assigned.
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: navigation location label */
				__( 'No menu has been assigned to the "%s" navigation location. Without a primary navigation menu, visitors cannot easily navigate the site, which increases bounce rates and harms usability. Create a menu and assign it under Appearance → Menus.', 'wpshadow' ),
				$primary_location
			),
			'severity'     => 'medium',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/primary-navigation-assigned?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'location_key'   => $primary_location_key,
				'location_label' => $primary_location,
				'menu_assigned'  => false,
			),
		);
	}
}
