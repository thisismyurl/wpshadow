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

		// Pages that should typically have screen options.
		$pages_to_check = array(
			'edit' => 'Posts',
			'edit-page' => 'Pages',
			'edit-comments' => 'Comments',
			'plugins' => 'Plugins',
		);

		$missing_options = array();

		// Check if screen options are registered for these screen IDs.
		foreach ( $pages_to_check as $screen_id => $page_name ) {
			// Temporarily set the current screen to check.
			set_current_screen( $screen_id );
			$screen = get_current_screen();

			if ( ! $screen ) {
				continue;
			}

			// Check if screen has options or columns.
			$has_options = false;

			// Check for columns (most common screen option).
			if ( method_exists( $screen, 'get_columns' ) ) {
				$columns = $screen->get_columns();
				if ( ! empty( $columns ) ) {
					$has_options = true;
				}
			}

			// Check for per_page option.
			if ( ! $has_options && method_exists( $screen, 'get_option' ) ) {
				$per_page = $screen->get_option( 'per_page' );
				if ( ! empty( $per_page ) ) {
					$has_options = true;
				}
			}

			if ( ! $has_options ) {
				$missing_options[] = $page_name;
			}
		}

		if ( ! empty( $missing_options ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Screen options are missing or not properly registered for: %s. This limits user customization of admin interface.', 'wpshadow' ),
					implode( ', ', $missing_options )
				),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
			);
		}

		return null; // Screen options properly registered.
	}
}
