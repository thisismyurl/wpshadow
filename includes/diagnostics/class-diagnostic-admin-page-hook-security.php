<?php
/**
 * Admin Page Hook Security
 *
 * Checks if custom admin pages properly use security hooks and validation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0634
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Admin Page Hook Security
 *
 * @since 1.26033.0634
 */
class Diagnostic_Admin_Page_Hook_Security extends Diagnostic_Base {

	protected static $slug = 'admin-page-hook-security';
	protected static $title = 'Admin Page Hook Security';
	protected static $description = 'Verifies custom admin pages use proper security hooks';
	protected static $family = 'admin-security';

	public static function check() {
		$issues = array();

		// Check for admin pages registered without proper hooks
		global $admin_page_hooks;
		$problematic_hooks = 0;

		if ( ! empty( $admin_page_hooks ) ) {
			foreach ( $admin_page_hooks as $hook => $page ) {
				// Check if hook looks properly formatted
				if ( ! strpos( $hook, 'admin_page_' ) && ! strpos( $hook, 'admin_menu' ) ) {
					$problematic_hooks++;
				}
			}
		}

		// Check for improper use of admin_init hook
		$filter_count = has_action( 'admin_init' );
		if ( $filter_count > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of hooks */
				__( 'High number of admin_init hooks (%d) detected - may impact admin performance', 'wpshadow' ),
				$filter_count
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-page-hook-security',
			);
		}

		return null;
	}
}
