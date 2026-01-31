<?php
/**
 * Fatal Error Protection Diagnostic
 *
 * Confirms WordPress recovery mode triggers on fatal error.
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
 * Diagnostic_Fatal_Error_Protection
 *
 * Verifies WordPress recovery mode is available to handle fatal errors.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Fatal_Error_Protection extends Diagnostic_Base {

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

		// Check if recovery mode function exists (WP 5.2+).
		if ( ! function_exists( 'wp_fatal_error_handler' ) ) {
			return array(
				'id'           => 'fatal-error-protection',
				'title'        => __( 'Fatal Error Protection Not Available', 'wpshadow' ),
				'description'  => __( 'WordPress recovery mode for fatal errors is not available. Your WordPress version may be outdated. Update WordPress 5.2+ to enable automatic recovery mode on fatal errors.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/fatal_error_protection',
				'meta'         => array(
					'recovery_mode_available' => false,
					'wp_version'             => get_bloginfo( 'version' ),
					'required_version'       => '5.2',
				),
			);
		}

		// Check if error logging is available.
		if ( ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) {
			return array(
				'id'           => 'fatal-error-protection',
				'title'        => __( 'Error Logging Not Enabled', 'wpshadow' ),
				'description'  => __( 'WP_DEBUG_LOG is not enabled. Even with recovery mode, PHP fatal errors may not be logged properly, making debugging difficult.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/fatal_error_protection',
				'meta'         => array(
					'wp_debug_log' => defined( 'WP_DEBUG_LOG' ) ? WP_DEBUG_LOG : false,
				),
			);
		}

		return null;
	}
}
