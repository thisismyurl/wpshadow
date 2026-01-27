<?php
/**
 * Admin Missing WordPress Admin Favicon Diagnostic
 *
 * Checks if WordPress admin is missing its favicon.
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
 * Admin Missing WordPress Admin Favicon Diagnostic Class
 *
 * Detects when admin pages are missing the default WordPress favicon.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Missing_WordPress_Admin_Favicon extends Diagnostic_Base {

	protected static $slug = 'admin-missing-wordpress-admin-favicon';
	protected static $title = 'Missing WordPress Admin Favicon';
	protected static $description = 'Checks if admin pages have the WordPress favicon';
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

		// Check for favicon link tag.
		$has_favicon = ( false !== strpos( $html, 'rel="icon"' ) || 
		                 false !== strpos( $html, 'rel="shortcut icon"' ) );

		if ( ! $has_favicon ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Admin pages are missing the WordPress favicon. This affects branding and browser tab identification.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
			);
		}

		return null;
	}
}
