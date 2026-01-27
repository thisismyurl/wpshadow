<?php
/**
 * Admin Screen Options Missing Expected Checkboxes Diagnostic
 *
 * Checks if screen options are missing expected checkboxes.
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
 * Admin Screen Options Missing Expected Checkboxes Diagnostic Class
 *
 * Detects when screen options panels are missing expected checkboxes.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Screen_Options_Missing_Expected_Checkboxes extends Diagnostic_Base {

	protected static $slug = 'admin-screen-options-missing-expected-checkboxes';
	protected static $title = 'Screen Options Missing Expected Checkboxes';
	protected static $description = 'Checks if screen options have expected column controls';
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

		// Check for screen options panel.
		if ( false === strpos( $html, 'id="screen-meta"' ) && 
		     false === strpos( $html, 'id="screen-options-wrap"' ) ) {
			return null; // No screen options at all.
		}

		// Extract screen options panel.
		if ( preg_match( '/<div[^>]*id=["\']screen-meta["\'][^>]*>(.*?)<\/div>/is', $html, $matches ) ) {
			$panel_html = $matches[1];
			
			// Check for checkboxes.
			$checkbox_count = preg_match_all( '/<input[^>]*type=["\']checkbox["\'][^>]*>/', $panel_html, $checkbox_matches );
			
			if ( $checkbox_count === 0 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Screen options panel exists but contains no checkboxes. Users cannot customize column visibility.', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 25,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
				);
			}
		}

		return null;
	}
}
