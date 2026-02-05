<?php
/**
 * Theme Menu Location Treatment
 *
 * Detects issues with theme's registered menu locations and navigation.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.5049.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Menu Location Treatment Class
 *
 * Checks if theme properly registers menu locations and if assigned menus exist.
 *
 * @since 1.5049.1200
 */
class Treatment_Theme_Menu_Location_Issues extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-menu-location-issues';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Menu Location Issues';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for theme menu registration and assignment issues';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.5049.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme = wp_get_theme();
		$issues = array();

		// Get registered menu locations.
		$locations = get_registered_nav_menus();

		if ( empty( $locations ) ) {
			$issues[] = __( 'Theme does not register any menu locations', 'wpshadow' );
		} else {
			// Check if menus are assigned to locations.
			$menu_locations = get_nav_menu_locations();
			$unassigned_locations = array();

			foreach ( $locations as $location => $description ) {
				if ( ! isset( $menu_locations[ $location ] ) || ! $menu_locations[ $location ] ) {
					$unassigned_locations[] = $description;
				}
			}

			if ( count( $unassigned_locations ) === count( $locations ) ) {
				$issues[] = __( 'No menus assigned to any theme locations', 'wpshadow' );
			} elseif ( ! empty( $unassigned_locations ) ) {
				$issues[] = sprintf(
					/* translators: %d: number of unassigned locations */
					_n(
						'%d menu location has no assigned menu',
						'%d menu locations have no assigned menus',
						count( $unassigned_locations ),
						'wpshadow'
					),
					count( $unassigned_locations )
				);
			}

			// Check for common location names.
			$common_locations = array( 'primary', 'header', 'main', 'top', 'footer' );
			$has_common = false;
			foreach ( array_keys( $locations ) as $loc ) {
				if ( in_array( $loc, $common_locations, true ) ) {
					$has_common = true;
					break;
				}
			}

			if ( ! $has_common ) {
				$issues[] = __( 'Theme menu locations use non-standard naming conventions', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: comma-separated list of issues */
					__( 'Theme menu issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'    => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'details'     => array(
					'theme'               => $theme->get( 'Name' ),
					'registered_locations' => $locations,
					'issues'              => $issues,
				),
				'kb_link'     => 'https://wpshadow.com/kb/theme-menu-location-issues',
			);
		}

		return null;
	}
}
