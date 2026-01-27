<?php
/**
 * Admin Missing Admin Bar Element Diagnostic
 *
 * Checks if the admin bar element is present in DOM.
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
 * Admin Missing Admin Bar Element Diagnostic Class
 *
 * Detects when the WordPress admin bar (#wpadminbar) is missing from admin pages.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Missing_Admin_Bar_Element extends Diagnostic_Base {

	protected static $slug = 'admin-missing-admin-bar-element';
	protected static $title = 'Missing Admin Bar Element';
	protected static $description = 'Checks if the admin bar is present in admin pages';
	protected static $family = 'admin';

	public static function check() {
		if ( ! is_admin() ) {
			return null;
		}

		// Skip if admin bar is disabled for this user.
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

		// Check for admin bar element.
		$has_admin_bar = ( false !== strpos( $html, 'id="wpadminbar"' ) );

		if ( ! $has_admin_bar ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'The WordPress admin bar (#wpadminbar) is missing from admin pages. This may indicate a theme or plugin conflict.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
			);
		}

		return null;
	}
}
