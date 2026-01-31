<?php
/**
 * Admin Menu Customization Issues Diagnostic
 *
 * Checks if admin menu has performance issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Menu Customization Issues Diagnostic Class
 *
 * Detects admin menu performance problems.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Admin_Menu_Customization_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-menu-customization-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Admin Menu Customization Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks admin menu customization performance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $menu, $submenu;

		// Count menu items
		$menu_count = count( $menu );
		$submenu_count = 0;

		if ( ! empty( $submenu ) ) {
			foreach ( $submenu as $parent => $items ) {
				$submenu_count += count( $items );
			}
		}

		// Check for overly large menu structures
		if ( $menu_count > 30 || $submenu_count > 50 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( 'Admin menu has %d items with %d submenus. Large menu structures can slow down admin load time and confuse users.', 'wpshadow' ),
					absint( $menu_count ),
					absint( $submenu_count )
				),
				'severity'      => 'low',
				'threat_level'  => 25,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/admin-menu-customization-issues',
			);
		}

		return null;
	}
}
