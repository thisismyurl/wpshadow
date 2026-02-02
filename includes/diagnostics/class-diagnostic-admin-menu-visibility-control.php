<?php
/**
 * Admin Menu Visibility Control
 *
 * Checks if admin menu items are properly restricted based on user capabilities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0633
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Admin Menu Visibility Control
 *
 * @since 1.26033.0633
 */
class Diagnostic_Admin_Menu_Visibility_Control extends Diagnostic_Base {

	protected static $slug = 'admin-menu-visibility-control';
	protected static $title = 'Admin Menu Visibility Control';
	protected static $description = 'Verifies menu items are restricted by user capabilities';
	protected static $family = 'admin-security';

	public static function check() {
		$issues = array();

		// Check for menu items that might be exposed
		global $menu;
		$menu_capability_issues = 0;

		if ( ! empty( $menu ) ) {
			foreach ( $menu as $item ) {
				// Check if menu item uses valid capability
				$capability = $item[1] ?? 'read';
				if ( 'read' === $capability || empty( $capability ) ) {
					$menu_capability_issues++;
				}
			}
		}

		if ( $menu_capability_issues > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of menu items */
				__( '%d menu item(s) use generic "read" capability - should be more specific', 'wpshadow' ),
				$menu_capability_issues
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-menu-visibility-control',
			);
		}

		return null;
	}
}
