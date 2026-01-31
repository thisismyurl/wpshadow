<?php
/**
 * Admin Notices Positioned Incorrectly Via CSS Diagnostic
 *
 * Checks if admin notices have incorrect CSS positioning.
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
 * Admin Notices Positioned Incorrectly Via CSS Diagnostic Class
 *
 * Detects notices with absolute or fixed positioning.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Notices_Positioned_Incorrectly_Via_Css extends Diagnostic_Base {

	protected static $slug = 'admin-notices-positioned-incorrectly-via-css';
	protected static $title = 'Admin Notices Positioned Incorrectly Via CSS';
	protected static $description = 'Checks if notices have improper CSS positioning';
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

		// Find notices with positioning styles.
		preg_match_all( '/<div[^>]*class=["\'][^"\']*notice[^"\']*["\'][^>]*style=["\']([^"\']*)["\'][^>]*>/is', $html, $matches );
		
		$incorrectly_positioned = 0;
		
		foreach ( $matches[1] as $style ) {
			if ( preg_match( '/position\s*:\s*(absolute|fixed)/i', $style ) ) {
				$incorrectly_positioned++;
			}
		}

		if ( $incorrectly_positioned > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Found %d notice(s) with absolute or fixed positioning. This can cause notices to overlap content.', 'wpshadow' ),
					$incorrectly_positioned
				),
				'severity'     => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
			);
		}

		return null;
	}
}
