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

		// Use WordPress native function to check for site icon.
		$has_site_icon = has_site_icon();

		// If no site icon set, check if WordPress is using default favicon.
		if ( ! $has_site_icon ) {
			// WordPress uses wp-admin/images/w-logo-blue.png as default favicon.
			// This is acceptable, so only flag if completely missing.
			$admin_images_path = ABSPATH . 'wp-admin/images/';
			$default_favicon_exists = file_exists( $admin_images_path . 'w-logo-blue.png' );

			if ( ! $default_favicon_exists ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Admin pages are missing the WordPress favicon. Neither a custom site icon nor the default WordPress favicon is available. This affects branding and browser tab identification.', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 15,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
				);
			}
		}

		return null; // Favicon present (either custom or default).
	}
}
