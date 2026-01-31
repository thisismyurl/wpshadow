<?php
/**
 * Admin Duplicate Admin Bars Diagnostic
 *
 * Checks if plugins are adding duplicate admin bars.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Admin
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Duplicate Admin Bars Diagnostic Class
 *
 * Detects when multiple admin bars are present in the DOM.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Duplicate_Admin_Bars extends Diagnostic_Base {

	protected static $slug = 'admin-duplicate-admin-bars';
	protected static $title = 'Duplicate Admin Bars';
	protected static $description = 'Checks if plugins are adding duplicate admin bars';
	protected static $family = 'admin';

	public static function check() {
		if ( ! is_admin() ) {
			return null;
		}

		if ( ! is_admin_bar_showing() ) {
			return null;
		}

		if ( ! class_exists( 'WPShadow\Diagnostics\Helpers\Admin_Page_Scanner' ) ) {
			require_once WPSHADOW_PATH . 'includes/diagnostics/helpers/class-admin-page-scanner.php';
		}

		$html = \WPShadow\Diagnostics\Helpers\Admin_Page_Scanner::capture_admin_page( 'index.php' );
		
		if ( false === $html ) {
			return null;
		}

		// Count admin bar elements.
		$admin_bar_count = preg_match_all( '/id=(["\'])wpadminbar\1/i', $html, $matches );

		if ( $admin_bar_count > 1 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Found %d admin bar elements. Multiple admin bars cause visual glitches and layout issues.', 'wpshadow' ),
					$admin_bar_count
				),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
			);
		}

		return null;
	}
}
