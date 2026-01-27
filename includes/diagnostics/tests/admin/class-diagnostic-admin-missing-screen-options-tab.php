<?php
/**
 * Admin Missing Screen Options Tab Diagnostic
 *
 * Checks if screen options tab is missing from admin pages.
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
 * Admin Missing Screen Options Tab Diagnostic Class
 *
 * Detects when expected screen options tabs are missing.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Missing_Screen_Options_Tab extends Diagnostic_Base {

	protected static $slug = 'admin-missing-screen-options-tab';
	protected static $title = 'Missing Screen Options Tab';
	protected static $description = 'Checks if screen options are available where expected';
	protected static $family = 'admin';

	public static function check() {
		if ( ! is_admin() ) {
			return null;
		}

		if ( ! class_exists( 'WPShadow\Diagnostics\Helpers\Admin_Page_Scanner' ) ) {
			require_once WPSHADOW_PATH . 'includes/diagnostics/helpers/class-admin-page-scanner.php';
		}

		// Pages that should have screen options.
		$pages_to_check = array(
			'edit.php'    => 'Posts',
			'edit.php?post_type=page' => 'Pages',
		);

		$missing_options = array();

		foreach ( $pages_to_check as $page_slug => $page_name ) {
			$html = \WPShadow\Diagnostics\Helpers\Admin_Page_Scanner::capture_admin_page( $page_slug );
			
			if ( false === $html ) {
				continue;
			}

			// Check for screen options button.
			$has_screen_options = ( false !== strpos( $html, 'id="screen-options-link-wrap"' ) ||
			                        false !== strpos( $html, 'show-settings-link' ) );

			if ( ! $has_screen_options ) {
				$missing_options[] = $page_name;
			}
		}

		if ( ! empty( $missing_options ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Screen options are missing from: %s. This limits user customization of admin interface.', 'wpshadow' ),
					implode( ', ', $missing_options )
				),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
			);
		}

		return null;
	}
}
