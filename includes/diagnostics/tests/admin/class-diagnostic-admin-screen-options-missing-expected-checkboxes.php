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

		// Test on Posts page which should have column checkboxes.
		set_current_screen( 'edit' );
		$screen = get_current_screen();

		if ( ! $screen ) {
			return null; // Cannot determine screen.
		}

		// Get available columns.
		$columns = $screen->get_columns();

		if ( empty( $columns ) ) {
			// No columns registered means no checkboxes expected - this is normal for some screens.
			return null;
		}

		// Get hidden columns for current user.
		$hidden = get_user_option( 'manage' . $screen->id . 'columnshidden' );

		// If columns exist but user has no hidden columns option set, checkboxes may not be rendering.
		// However, this could just mean user hasn't customized yet. Let's check if render method exists.
		if ( ! method_exists( $screen, 'render_screen_options' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Screen options rendering method is missing. Columns are registered but users cannot access checkboxes to show/hide them.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
			);
		}

		// Try to render screen options to verify checkboxes are generated.
		ob_start();
		try {
			$screen->render_screen_options();
			$output = ob_get_clean();

			// Check if output contains column checkboxes.
			$checkbox_count = substr_count( $output, 'type="checkbox"' );

			if ( count( $columns ) > 0 && $checkbox_count === 0 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						__( 'Screen has %d columns registered but screen options panel contains no checkboxes. Users cannot customize column visibility.', 'wpshadow' ),
						count( $columns )
					),
					'severity'     => 'medium',
					'threat_level' => 25,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
					'meta'         => array(
						'expected_columns' => count( $columns ),
						'checkbox_count'   => $checkbox_count,
					),
				);
			}
		} catch ( \Exception $e ) {
			ob_end_clean();
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Error rendering screen options: %s. Users cannot access column checkboxes.', 'wpshadow' ),
					esc_html( $e->getMessage() )
				),
				'severity'     => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
			);
		}

		return null; // Screen options checkboxes are present and functional.
	}
}
