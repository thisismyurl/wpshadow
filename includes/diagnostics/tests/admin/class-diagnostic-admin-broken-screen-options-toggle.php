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

		// Test on Posts page which should have screen options.
		set_current_screen( 'edit' );
		$screen = get_current_screen();

		if ( ! $screen ) {
			return null; // Cannot determine screen.
		}

		// Check if screen object has render_screen_meta method (handles screen options).
		if ( ! method_exists( $screen, 'render_screen_meta' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Screen options rendering method is missing from WP_Screen class. This indicates a core WordPress issue or conflict. Users cannot access screen options.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
			);
		}

		// Check if columns are registered but screen options don't work.
		$columns = $screen->get_columns();
		
		if ( ! empty( $columns ) ) {
			// Columns exist, which means screen options should be functional.
			// Check if the screen options can be rendered.
			ob_start();
			try {
				$screen->render_screen_meta();
				$output = ob_get_clean();

				// If render_screen_meta produces no output but columns exist, toggle is broken.
				if ( empty( $output ) ) {
					return array(
						'id'           => self::$slug,
						'title'        => self::$title,
						'description'  => __( 'Screen options are registered but the toggle/panel does not render properly. Users cannot customize the admin interface despite options being available.', 'wpshadow' ),
						'severity'     => 'medium',
						'threat_level' => 30,
						'auto_fixable' => false,
						'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
					);
				}
			} catch ( \Exception $e ) {
				ob_end_clean();
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						__( 'Screen options toggle threw an error: %s. This prevents users from customizing the admin interface.', 'wpshadow' ),
						esc_html( $e->getMessage() )
					),
					'severity'     => 'high',
					'threat_level' => 40,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
				);
			}
		}

		return null; // Screen options toggle is functional.
	}
}
