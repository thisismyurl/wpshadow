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

		// Check if the admin bar global is properly initialized.
		global $wp_admin_bar;

		// If admin bar should be showing but the global doesn't exist or isn't initialized, there's an issue.
		if ( ! isset( $wp_admin_bar ) || ! is_object( $wp_admin_bar ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'The WordPress admin bar ($wp_admin_bar) is not properly initialized despite being enabled. This may indicate a theme or plugin conflict preventing the admin bar from loading.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
			);
		}

		// Check if the admin bar has the necessary methods (indicates proper initialization).
		if ( ! method_exists( $wp_admin_bar, 'render' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'The WordPress admin bar object exists but is not properly instantiated. This may indicate a theme or plugin is overriding the admin bar incorrectly.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
			);
		}

		return null; // Admin bar is properly initialized.
	}
}
