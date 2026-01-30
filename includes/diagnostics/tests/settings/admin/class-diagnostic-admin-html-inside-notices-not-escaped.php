<?php
/**
 * Admin HTML Inside Notices Not Escaped Diagnostic
 *
 * Checks if HTML inside admin notices is properly escaped.
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
 * Admin HTML Inside Notices Not Escaped Diagnostic Class
 *
 * Detects potentially unsafe HTML in admin notices.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Html_Inside_Notices_Not_Escaped extends Diagnostic_Base {

	protected static $slug = 'admin-html-inside-notices-not-escaped';
	protected static $title = 'HTML Inside Notices Not Escaped';
	protected static $description = 'Checks if HTML in notices is properly sanitized';
	protected static $family = 'admin';

	public static function check() {
		if ( ! is_admin() ) {
			return null;
		}

		if ( ! class_exists( 'WPShadow\Diagnostics\Helpers\Admin_Page_Scanner' ) ) {
			require_once WPSHADOW_PATH . 'includes/diagnostics/helpers/class-admin-page-scanner.php';
		}

		$html = \WPShadow\Diagnostics\Helpers\Admin_Page_Scanner::capture_admin_page( 'index.php' );
		
		if ( false === $html ) {
			return null;
		}

		// Find notices with potentially unsafe HTML.
		preg_match_all( '/<div[^>]*class=["\'][^"\']*notice[^"\']*["\'][^>]*>(.*?)<\/div>/is', $html, $matches );
		
		$unsafe_notices = 0;
		
		foreach ( $matches[1] as $content ) {
			// Check for inline scripts or events.
			if ( preg_match( '/<script[^>]*>/i', $content ) ||
			     preg_match( '/on\w+\s*=\s*["\'][^"\']*["\']/', $content ) ) {
				$unsafe_notices++;
			}
		}

		if ( $unsafe_notices > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Found %d notice(s) with potentially unsafe HTML content. This could be a security risk.', 'wpshadow' ),
					$unsafe_notices
				),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
			);
		}

		return null;
	}
}
