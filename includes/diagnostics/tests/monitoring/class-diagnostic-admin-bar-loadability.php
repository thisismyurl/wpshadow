<?php
/**
 * Admin Bar Loadability Diagnostic
 *
 * Confirms admin bar assets load without errors when shown.
 *
 * @since   1.2601.2112
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Admin_Bar_Loadability
 *
 * Checks that admin bar scripts/styles are registered when the bar should display.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Admin_Bar_Loadability extends Diagnostic_Base {

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2112
	 * @return array|null Finding array if issues detected, null otherwise.
	 */
	public static function check() {
		if ( ! is_admin() ) {
			return null;
		}

		if ( ! is_user_logged_in() || ! is_admin_bar_showing() ) {
			return null; // Admin bar not expected.
		}

		// Ensure scripts/styles were enqueued.
		if ( ! did_action( 'wp_enqueue_scripts' ) && ! did_action( 'admin_enqueue_scripts' ) ) {
			return null; // Cannot verify before enqueue.
		}

		$missing = array();

		if ( ! wp_script_is( 'admin-bar', 'enqueued' ) && ! wp_script_is( 'admin-bar', 'registered' ) ) {
			$missing[] = 'admin-bar-script';
		}

		if ( ! wp_style_is( 'admin-bar', 'enqueued' ) && ! wp_style_is( 'admin-bar', 'registered' ) ) {
			$missing[] = 'admin-bar-style';
		}

		if ( ! empty( $missing ) ) {
			return array(
				'id'           => 'admin-bar-loadability',
				'title'        => __( 'Admin Bar Assets Not Loaded', 'wpshadow' ),
				'description'  => __( 'Admin bar is enabled but its scripts/styles were not enqueued. This can break editor menus and toolbar actions.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin_bar_loadability',
				'meta'         => array(
					'missing_assets' => $missing,
				),
			);
		}

		return null;
	}
}
