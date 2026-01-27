<?php
/**
 * Admin Broken Screen Options Toggle Diagnostic
 *
 * Checks if screen options toggle is broken or non-functional.
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
 * Admin Broken Screen Options Toggle Diagnostic Class
 *
 * Detects broken screen options toggle functionality.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Broken_Screen_Options_Toggle extends Diagnostic_Base {

	protected static $slug = 'admin-broken-screen-options-toggle';
	protected static $title = 'Broken Screen Options Toggle';
	protected static $description = 'Checks if screen options toggle is functional';
	protected static $family = 'admin';

	public static function check() {
		if ( ! is_admin() ) {
			return null;
		}

		if ( ! class_exists( 'WPShadow\Diagnostics\Helpers\Admin_Page_Scanner' ) ) {
			require_once WPSHADOW_PATH . 'includes/diagnostics/helpers/class-admin-page-scanner.php';
		}

		$html = \WPShadow\Diagnostics\Helpers\Admin_Page_Scanner::capture_admin_page( 'edit.php' );
		
		if ( false === $html ) {
			return null;
		}

		// Check for screen options link without corresponding panel.
		$has_link = ( false !== strpos( $html, 'id="screen-options-link-wrap"' ) );
		$has_panel = ( false !== strpos( $html, 'id="screen-meta"' ) ||
		               false !== strpos( $html, 'id="screen-options-wrap"' ) );

		if ( $has_link && ! $has_panel ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Screen options link exists but the panel is missing. Users cannot customize the admin interface.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
			);
		}

		return null;
	}
}
